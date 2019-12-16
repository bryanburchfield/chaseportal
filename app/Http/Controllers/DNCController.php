<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DNCController extends Controller
{
    public function index()
    {
        $page['menuitem'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
        ];

        return view('tools.dnc_importer')->with($data);
    }
}
