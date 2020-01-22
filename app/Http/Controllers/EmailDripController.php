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

<<<<<<< HEAD
=======
    public function addProvider(ValidEmailServiceProvider $request)
    {
        $email_service_provider = new EmailServiceProvider($request->all());

        $email_service_provider->group_id = Auth::User()->group_id;
        $email_service_provider->user_id = Auth::User()->id;

        $email_service_provider->save();

        return ['status' => 'success'];
    }

    public function deleteProvider(Request $request){
        EmailServiceProvider::findOrFail($request->id)->delete();
    }

>>>>>>> 02f196a2113ee6ab3d1717fd513fd298321e20d7
    public function testConnection(Request $request)
    {
        return [
            'status' => 'error',
            'message' => 'Test Error Message',
        ];
    }
}
