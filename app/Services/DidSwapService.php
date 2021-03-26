<?php

namespace App\Services;

use App\Models\AreaCode;
use App\Traits\PhoneTraits;
use Exception;
use GuzzleHttp\Client;

class DidSwapService
{
    use PhoneTraits;

    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function swapNumber($phone, $dialer_numb, $group_id)
    {
        // try to replace with same NPA
        list($replaced_by, $swap_error) = $this->swapNumberNpa($phone, $dialer_numb, $group_id);

        if (empty($replaced_by)) {

            // Find area code record
            $npa = substr($this->formatPhoneTenDigits($phone), 0, 3);
            $areaCode = AreaCode::find($npa);

            if ($areaCode) {
                // get list of nearby same state npas
                $alternates = $areaCode->alternateNpas();

                // loop through till swap succeeds or errors
                foreach ($alternates as $alternate) {
                    list($replaced_by, $swap_error) = $this->swapNumberNpa($phone, $dialer_numb, $group_id, $alternate->npa);
                    if (!empty($replaced_by)) {
                        break;
                    }
                }
            }
        }

        return [$replaced_by, $swap_error];
    }

    private function swapNumberNpa($phone, $dialer_numb, $group_id, $npa = null)
    {
        echo "Swapping $phone $npa\n";

        $error = null;
        $replaced_by = null;

        try {
            $response = $this->client->get(
                'https://billing.chasedatacorp.com/DID.aspx',
                [
                    'query' => [
                        'Token' => config('chasedata.did_token'),
                        'Server' => 'dialer-' . sprintf('%02d', $dialer_numb, 2),
                        'Number' => $this->formatPhoneTenDigits($phone),
                        'GroupId' => $group_id,
                        'Action' => 'swap',
                        'NPA' => $npa
                    ]
                ]
            );
        } catch (Exception $e) {
            $error = 'Swap API failed: ' . $e->getMessage();
        }

        if (empty($error)) {
            try {
                $body = json_decode($response->getBody()->getContents());

                if (isset($body->NewDID)) {
                    if (!empty($body->NewDID)) {
                        $replaced_by = $this->formatPhoneElevenDigits($body->NewDID);
                    } else {
                        $error = 'No replacement available';
                    }
                }
                if (!empty($body->Error)) {
                    $error = $body->Error;
                }
            } catch (Exception $e) {
                $error = 'Could not swap number: ' . $e->getMessage();
            }
        }

        // truncate error just in case
        if (!empty($error)) {
            $error = substr($error, 0, 190);
        }

        return [$replaced_by, $error];
    }
}
