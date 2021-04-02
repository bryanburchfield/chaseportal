<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function index()
    {	

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
