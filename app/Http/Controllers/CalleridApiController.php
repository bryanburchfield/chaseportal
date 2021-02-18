<?php

namespace App\Http\Controllers;

use App\Models\CalleridResult;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalleridApiController extends Controller
{
    // hard-code client tokens
    protected $clients = [
        '5fec6c40fd245a243c32b3db49013d45' => 'Hiya',
        'ae2b1fca515949e5d54fb22b8ed95575' => 'Testing',
    ];

    public function post(Request $request)
    {
        $clientid = $request->input('token');

        if (!isset($this->clients[$clientid])) {
            abort(404);
        }

        try {
            $calleridResult = new CalleridResult([
                'client' => $this->clients[$clientid],
                'ip' => $request->ip(),
                'raw_phone' => $request->input('phone'),
                'phone' => $this->formatPhone($request->input('phone')),
                'result' => $request->input('result'),
            ]);
        } catch (Exception $e) {
            $err = $e->getMessage();
            Log::error('Error creating CalleridResult: ' . $err);
            return ['Error' => 'System error, please contact support'];
        }

        // Check for errors
        $errs = $this->errorCheck($calleridResult);

        if (!empty($errs)) {
            return ['Error' => $errs];
        }

        try {
            $calleridResult->save();
        } catch (Exception $e) {
            $err = $e->getMessage();
            Log::error('Error saving CalleridResult: ' . $err);
            return ['Error' => 'System error, please contact support'];
        }

        return ['Status' => 'OK'];
    }

    private function errorCheck($calleridResult)
    {
        $errs = [];

        if (empty($calleridResult->raw_phone)) {
            $errs[] = 'Phone missing';
        }
        if (empty($calleridResult->result)) {
            $errs[] = 'Result missing';
        }

        if (!empty($errs)) {
            return implode(', ', $errs);
        }

        if (strlen($calleridResult->raw_phone) > 20) {
            $errs[] = 'Phone too large';
        }

        if (strlen($calleridResult->result) > 191) {
            $errs[] = 'Result too large';
        }

        if (!empty($errs)) {
            return implode(', ', $errs);
        }

        if (strlen($calleridResult->phone) != 11) {
            $errs[] = 'Invalid phone';
        }

        return implode(', ', $errs);
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
}
