<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use View;

class FormBuilderController extends Controller
{
    public function portalFormBuilder()
    {	

    	$page = [
    	    'menuitem' => 'form_builder',
    	    'sidenav' => 'tools',
    	    'type' => 'other',
    	];

    	$data=[
    		'page' => $page,
    		'override_app_cssfile'=> ['codemirror.css'],
    		'jsfile' => ['jquery-ui.min-1.10.js', 'portal_form_builder.js', 'codemirror.min.js', 'formatting.js'],
    	];
    	return view('tools.portal_form_builder')->with($data);
    }

    public function getFormElement(Request $request)
    {
        return View::make('tools.formbuilder_options.' .$request->type)->render();
    }
}
