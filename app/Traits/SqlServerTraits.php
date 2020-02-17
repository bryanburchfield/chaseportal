<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SqlServerTraits
{
    protected $db;

    private function runSql($sql, $bind, $db = null)
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
                $db = Auth::user()->db;
            } else {
                $db = $this->db;
            }
        }

        config(['database.connections.sqlsrv.database' => $db]);
    }

    private function runMultiSql($sql, $bind)
    {
        if (Auth::check()) {
            $db = Auth::user()->db;
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

    private function yieldSql($sql, $bind)
    {
        if (Auth::check()) {
            $db = Auth::user()->db;
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

    private function defaultLeadFields()
    {
        return [
            'ClientId' => 'string',
            'FirstName' => 'string',
            'LastName' => 'string',
            'PrimaryPhone' => 'phone',
            'Address' => 'string',
            'City' => 'string',
            'State' => 'string',
            'ZipCode' => 'string',
            'Notes' => 'text',
            'Campaign' => 'string',
            'Subcampaign' => 'string',
        ];
    }
}
