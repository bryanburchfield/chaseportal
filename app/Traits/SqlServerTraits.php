<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait SqlServerTraits
{
    protected $db;

    private function runSql($sql, $bind)
    {
        if (Auth::check()) {
            $db = Auth::user()->db;
        } else {
            $db = $this->db;
        }
        config(['database.connections.sqlsrv.database' => $db]);

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
}
