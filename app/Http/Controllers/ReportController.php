<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ReportController extends Controller
{
	// use DashTraits;

    public function index(Request $request)
    {
        $view = 'reports.' . $request->report;
        if (!view()->exists($view)) {
            abort(404, $view . " not found");
        }

        $jsfile = [];
        $cssfile = [];

        ///// add campaigns here
        $campaigns = [
        	'Camp1'=>'camp1',
        	'Camp2'=>'camp2',
        	'Camp3'=>'camp3',
        	'Camp4'=>'camp4',
        	'Camp5'=>'camp5',
        	'Camp6'=>'camp6'
        ];

        $inbound_sources = [
        	'source1'=>'source1',
        	'source2'=>'source2',
        	'source3'=>'source3'
        ];

        $rep = [
        	'rep1'=>'rep1',
        	'rep2'=>'rep2',
        	'rep3'=>'rep3'
        ];

        $call_status = [
        	'rep1'=>'rep1',
        	'rep2'=>'rep2',
        	'rep3'=>'rep3'
        ];

        $data = [
            'jsfile' => $jsfile,
            'cssfile' => $cssfile,
            'campaigns' => $campaigns,
            'inbound_sources' => $inbound_sources,
            'rep' => $rep,
            'call_status' => $call_status
        ];

        return view($view)->with($data);
    }

  
}