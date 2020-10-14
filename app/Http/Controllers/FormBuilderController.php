<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function index()
    {
        $jsfile[] = 'formbuilder.js';
        $page['menuitem'] = 'form_builder';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
        ];

        return view('tools.form_builder')->with($data);
    }
}
