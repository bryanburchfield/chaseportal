<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DialerController extends Controller
{
    public function index()
    {
    	$page['menuitem'] = 'server_status';
    	$page['type'] = 'page';
    	$data = [
    	    'page' => $page,
    	];

    	return view('tools.server_status')->with($data);
    }
}
