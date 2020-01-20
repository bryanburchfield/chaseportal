<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailDripController extends Controller
{
    public function index()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
        ];

        return view('tools.email_drip.index')->with($data);
    }

    public function editDrip(Request $request)
    {
        # code...
    }

    public function espIndex()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
        ];

        return view('tools.email_drip.esp_index')->with($data);
    }

    public function templateIndex()
    {
        # code...
    }

    public function uploadTemplate(Request $request)
    {
        # code...
    }
}
