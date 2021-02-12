<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SqlServerTraits
{
    protected $db;

    private function runSql($sql, $bind = [], $db = null)
    {
        $this->setSqlServer($db);

        try {
            $results = DB::connection('sqlsrv')->select(DB::raw($sql), $bind);
        } catch (\Exception $e) {
            $results = [];
        }

        if (count($results)) {
            // convert array of objects to array of arrays
            $results = json_decode(json_encode($results), true);
        }

        return $results;
    }

    public function setSqlServer($db = null)
    {
        if ($db === null) {
            if (Auth::check()) {
                $db = Auth::user()->dialer->reporting_db;
            } else {
                $db = $this->db;
            }
        }

        config(['database.connections.sqlsrv.database' => $db]);
    }

    private function runMultiSql($sql, $bind = [])
    {
        if (Auth::check()) {
            $db = Auth::user()->dialer->reporting_db;
        } else {
            $db = $this->db;
        }
        config(['database.connections.sqlsrv.database' => $db]);

        $pdo = DB::connection('sqlsrv')->getPdo();
        $stmt = $pdo->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL]);

        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();

        $result = [];

        do {
            $result[] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } while ($stmt->nextRowset());

        return $result;
    }

    private function yieldSql($sql, $bind = [])
    {
        if (Auth::check()) {
            $db = Auth::user()->dialer->reporting_db;
        } else {
            $db = $this->db;
        }
        config(['database.connections.sqlsrv.database' => $db]);

        try {
            foreach (DB::connection('sqlsrv')->cursor(DB::raw($sql), $bind) as $rec) {
                // convert array of objects to array of arrays
                $rec = json_decode(json_encode($rec), true);
                yield ($rec);
            }
        } catch (\Exception $e) {
            yield [];
        }
    }

    private function add1ToPhone($phone)
    {
        return $this->trimPhone($phone, false);
    }

    private function strip1FromPhone($phone)
    {
        return $this->trimPhone($phone, true);
    }

    private function trimPhone($phone, $strip1)
    {
        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        if ($strip1) {
            // Strip leading '1' if 11 digits
            if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
                $phone = substr($phone, 1);
            }
        } else {
            // Add leading '1' if 10 digits
            if (strlen($phone) == 10) {
                $phone = '1' . $phone;
            }
        }

        return $phone;
    }

    public function systemCodes()
    {
        // goofy hard-coded list of call statuses used for calculating connects
        return [
            'CR_BAD_NUMBER',
            'CR_BUSY',
            'CR_CEPT',
            'CR_CNCT/CON_CAD',
            'CR_CNCT/CON_PAMD',
            'CR_CNCT/CON_PVD',
            'CR_DISCONNECTED',
            'CR_DROPPED',
            'CR_ERROR',
            'CR_FAILED',
            'CR_FAXTONE',
            'CR_HANGUP',
            'CR_NOANS',
            'CR_NORB',
            'CR_UNFINISHED',
            'Inbound Transfer',
            'Inbound Voicemail',
            'PARKED',
            'SYS_CALLBACK',
            'Skip',
            'TRANSFERRED',
            'UNFINISHED',
            'UNKNOWN',
        ];
    }

    public function systemCodeList()
    {
        return "'" . implode("','", $this->systemCodes()) . "'";
    }
}
