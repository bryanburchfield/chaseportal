<?php

namespace App\Http\Controllers;

use App\Models\SmtpServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailDripController extends Controller
{
    /**
     * Email Drip Campaign index
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function index()
    {

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'smtp_servers' => $this->getSmtpServers(),
        ];

        return view('tools.email_drip.index')->with($data);
    }

    public function editDrip(Request $request)
    {
        # code...
    }

    function addSmtpServer(Request $request)
    {
        # code...
    }

    function deleteSmtpServer(Request $request)
    {
        // check not in use first
    }

    /**
     * Providers configured for this user
     * 
     * @return mixed 
     */
    private function getSmtpServers()
    {
        return SmtpServer::where('group_id', Auth::User()->group_id)
            ->orderby('name')
            ->get();
    }

    public function testConnection(Request $request)
    {
        // see if we can connect to server

        return [
            'status' => 'error',
            'message' => 'Test Error Message',
        ];
    }
}
