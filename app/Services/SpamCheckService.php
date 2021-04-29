<?php

namespace App\Services;

use App\Models\Api;
use App\Models\PhoneFlag;
use App\Models\SpamCheckBatch;
use App\Models\SpamCheckBatchDetail;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as Twilio;

class SpamCheckService
{
    // For Twilio  (icehook)
    private $twilio;
    private $icehookScore = 80;          // min score to be considered spam

    // For Truespam 
    private $truespam;
    private $truespamScore = 40;          // min score to be considered spam
    private $truespamLimitRequests = 600;
    private $truespamLimitSeconds = 60;

    public function __construct()
    {
        $this->twilio = new Twilio(
            config('twilio.spam_sid'),
            config('twilio.spam_token')
        );

        $this->truespam = new Client([
            'base_uri' => 'https://chasedata-csp-useast1.truecnam.net',
            // 'base_uri' => 'https://chasedata-csp-uswest2.truecnam.net',   // alternate
            'defaults' => [
                'query' => [
                    'username' => config('truespam.key'),
                    'password' => config('truespam.password'),
                    'resp_type' => 'extended',
                    'resp_format' => 'json',
                ]
            ]
        ]);
    }

    public function __destruct()
    {
        // Delete old api transaction records to keep the table slim

        // Truespam
        try {
            $api = Api::where('name', 'Truespam')->first();
            $api->trimTransactions(Carbon::parse(time() - $this->truespamLimitSeconds));
        } catch (Exception $e) {
            Log::error('Truespam trim error: ' . $e->getMessage());
        }
    }

    public function processFile(SpamCheckBatch $spamCheckBatch)
    {
        if (!empty($spamCheckBatch->proccess_started_at)) {
            abort(404);
        }

        foreach ($spamCheckBatch->spamCheckBatchDetails->all() as $detail) {
            if ($detail->succeeded && !$detail->checked) {
                $detail->flags = $this->checkNumber($detail->phone);
                $detail->flagged = !empty($detail->flags);
                $detail->checked = 1;

                $detail->save();
            }
        }

        $spamCheckBatch->processed_at = now();
        $spamCheckBatch->save();
    }

    public function checkNumber($phone, $check_all = false)
    {
        $phone = $this->formatPhone($phone);

        // See if already checked on same calendar date
        $spam_check_batch_detail = SpamCheckBatchDetail::where('phone', $phone)
            ->whereDate('created_at', today())
            ->where('checked', 1)
            ->latest()
            ->first();

        if ($spam_check_batch_detail) {
            return $spam_check_batch_detail->flags;
        }

        // Check the phone_flags table too
        $phone_flag = PhoneFlag::where('phone', $phone)
            ->whereDate('run_date', today())
            ->where('checked', 1)
            ->latest('id')
            ->first();

        if ($phone_flag) {
            return $phone_flag->flags;
        }

        $flags = null;

        // check from cheapest to most expensive

        if (empty($flags) || $check_all) {
            if ($this->checkTruespam($phone)) {
                $flags .= ',Truespam';
            }
        }

        if (empty($flags) || $check_all) {
            if ($this->checkNomorobo($phone)) {
                $flags .= ',Nomorobo';
            }
        }

        if (empty($flags) || $check_all) {
            if ($this->checkIcehook($phone)) {
                $flags .= ',Icehook';
            }
        }

        if (!empty($flags)) {
            $flags = substr($flags, 1);
        }

        return $flags;
    }

    private function formatPhone($phone)
    {
        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        // Add leading '1' if 10 digits
        if (strlen($phone) == 10) {
            $phone = '1' . $phone;
        }

        return $phone;
    }

    private function checkTruespam($phone)
    {
        if (!$this->waitToSend('Truespam', $this->truespamLimitRequests, $this->truespamLimitSeconds)) {
            return false;
        }

        $query = ['query' => ['calling_number' => $phone]];

        try {
            $response = $this->truespam->get(
                '/api/v1',
                array_merge_recursive($query, $this->truespam->getConfig('defaults'))
            );
        } catch (Exception $e) {
            Log::error('TrueSpam error: ' . $e->getMessage());
            return false;
        }

        // check response
        $body = $this->objectToArray(json_decode($response->getBody()->getContents()));

        if ((int)Arr::get(
            $body,
            'err'
        ) !== 0) {
            Log::error('Truspam error: ' . Arr::get($body, 'error_msg'));
            return false;
        }

        $spamScore = (int)Arr::get($body, 'spam_score');

        return $spamScore >= $this->truespamScore;
    }

    private function checkNomorobo($phone)
    {
        try {
            $result = $this->twilio->lookups->v1->phoneNumbers('+' . $phone)->fetch(['addOns' => ['nomorobo_spamscore']]);
        } catch (Exception $e) {
            Log::error('Twilio lookup failed: ' . $e->getMessage());
            return false;
        }

        $result = $this->objectToArray($result->addOns);

        $nomoroboData = Arr::get($result, 'results.nomorobo_spamscore');

        if (Arr::get(
            $nomoroboData,
            'status'
        ) == 'failed') {
            Log::error('Nomorobo error: ' . Arr::get($nomoroboData, 'message'));
            return false;
        }

        $spamScore = (int)Arr::get($nomoroboData, 'result.score');

        return $spamScore >= 1;
    }

    private function checkIcehook($phone)
    {
        try {
            $result = $this->twilio->lookups->v1->phoneNumbers('+' . $phone)->fetch(['addOns' => ['icehook_scout']]);
        } catch (Exception $e) {
            Log::error('Twilio lookup failed: ' . $e->getMessage());
            return false;
        }

        $result = $this->objectToArray($result->addOns);

        $icehookData = Arr::get($result, 'results.icehook_scout');

        if (Arr::get(
            $icehookData,
            'status'
        ) == 'failed') {
            Log::error('Icehook error: ' . Arr::get($icehookData, 'message'));
            return false;
        }

        $spamScore = (int)Arr::get($icehookData, 'result.risk_level');

        return $spamScore >= $this->icehookScore;
    }

    private function objectToArray($obj)
    {
        return @json_decode(json_encode($obj), true);
    }

    private function waitToSend($api_name, $limitRequests, $limitSeconds)
    {
        DB::beginTransaction();

        try {
            // Lock api table (try 5 times (minutes) then bail)
            $i = 0;
            while ($i < 5) {
                $i++;
                try {
                    $api = Api::where('name', $api_name)->lockForUpdate()->first();
                    break;  // exit while loop
                } catch (Exception $e) {
                    if ($i >= 5) {
                        DB::rollBack();
                        Log::error($api_name . ': gave up trying to get lock');
                        return false;
                    }
                    Log::info($api_name . ': lock timeout');
                }
            }

            if (!$api) {
                DB::rollBack();
                Log::error('API ' . $api_name . ' not found');
                return false;
            }

            // Check if we're up against the API rate limit
            $i = 0;
            while (!$this->readyToSend($api, $limitRequests, $limitSeconds)) {
                // check for infinite loop
                if ($i++ > ($limitSeconds + 1)) {
                    Log::error($api_name . ' abort infinite loop waiting for rate limit');
                    return false;
                }
                sleep(1);
            }

            // Insert api transaction
            $api->add_transaction();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($api_name . ' ' . $e->getMessage());
            return false;
        }

        DB::commit();

        return true;
    }

    private function readyToSend(API $api, $limitRequests, $limitSeconds)
    {
        // count recent requests
        // $api->refresh();

        if ($api->transactions_since(Carbon::parse(time() - $limitSeconds)) >= $limitRequests) {
            return false;
        }

        return true;
    }
}
