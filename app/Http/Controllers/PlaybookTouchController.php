<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidPlaybookAction;
use App\Http\Requests\ValidPlaybookEmailAction;
use App\Http\Requests\ValidPlaybookLeadAction;
use App\Http\Requests\ValidPlaybookSmsAction;
use App\Models\Campaign;
use App\Models\Dispo;
use App\Models\EmailServiceProvider;
use App\Models\PlaybookAction;
use App\Models\PlaybookEmailAction;
use App\Models\PlaybookLeadAction;
use App\Models\PlaybookSmsAction;
use App\Models\SmsFromNumber;
use App\Models\Script;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlaybookTouchController extends Controller
{
    public function index()
    {
    	$page = [
            'menuitem' => 'playbook',
            'menu' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_actions.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
        ];

        return view('tools.playbook.touches')->with($data);
    }
}
