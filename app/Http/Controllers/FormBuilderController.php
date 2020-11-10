<?php

namespace App\Http\Controllers;

use App\Models\Dialer;
use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function index()
    {
        // Convert db list to value->value
        $dbs = [];
        foreach ($this->dbs() as $dialer_numb => $db) {
            $dbs[$db] = $db;
        }

        $jsfile[] = 'formbuilder.js';
        $page['menuitem'] = 'form_builder';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
            'dbs' => $dbs,
        ];

        return view('tools.form_builder')->with($data);
    }

    private function dbs()
    {
        $dbs = ['' => trans('general.select_one')];

        foreach (Dialer::orderBy('dialer_numb')->get() as $dialer) {
            $dbs[$dialer->reporting_db] = $dialer->reporting_db;
        }

        return $dbs;
    }
}
