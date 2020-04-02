<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaybookActionController extends Controller
{
    private $jsfile;

    public function __construct()
    {
        $this->jsfile = [
            "playbook_actions.js",
        ];
    }

    /**
     * Playbook campaigns index
     * 
     * @return View|Factory 
     */
    public function index()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => $this->jsfile,
            'group_id' => Auth::user()->group_id,
        ];

        return view('tools.playbook.actions')->with($data);
    }
}
