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

        return view('tools.email_drip')->with($data);
    }

    public function editDrip(Request $request)
    {
        # code...
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
