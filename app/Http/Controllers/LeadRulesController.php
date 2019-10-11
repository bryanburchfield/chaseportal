<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\LeadRule;

class LeadRulesController extends Controller
{

    public function index(Request $request)
    {

    	$page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'user' => Auth::user(),
            'page' => $page
        ];

    	return view('dashboards.tools')->with($data);
    }
}
