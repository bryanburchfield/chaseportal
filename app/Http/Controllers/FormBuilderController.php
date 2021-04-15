<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormBuilderController extends Controller
{
    public function portal_form_builder()
    {	

    	$page = [
    	    'menuitem' => 'form_builder',
    	    'sidenav' => 'tools',
    	    'type' => 'other',
    	];

    	$data=[
    		'page' => $page,
    		'cssfile'=> ['codemirror.css'],
    		'jsfile' => ['jquery-ui.min-1.10.js', 'portal_form_builder.js', 'codemirror.min.js', 'formatting.js'],
    	];
    	return view('tools.portal_form_builder')->with($data);
    }
}
