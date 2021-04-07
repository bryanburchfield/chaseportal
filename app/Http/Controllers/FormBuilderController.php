<?php

namespace App\Http\Controllers;

use App\Models\Dialer;
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
            'dbs' => $this->dbs(),
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

    public function portal_form_builder(){
    	$page = [
    	    'menuitem' => 'form_builder',
    	    'sidenav' => 'tools',
    	    'type' => 'other',
    	];

    	$data=[
    		'page' => $page,
    		'jsfile' => ['portal_form_builder.js'],
    		'cssfile'=>['//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css']
    	];
    	return view('tools.portal_form_builder')->with($data);
    }
}
