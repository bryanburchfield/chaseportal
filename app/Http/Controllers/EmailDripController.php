<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailServiceProvider;
use App\Models\EmailServiceProvider;
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
            'providers' => $this->getProviders(),
            'email_service_providers' => $this->userProviders(),
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
    private function userProviders()
    {
        return EmailServiceProvider::where('group_id', Auth::User()->group_id)
            ->orderby('name')
            ->get();
    }

    public function addProvider(ValidEmailServiceProvider $request)
    {
        $email_service_provider = new EmailServiceProvider($request->all());

        $email_service_provider->group_id = Auth::User()->group_id;
        $email_service_provider->user_id = Auth::User()->id;

        $email_service_provider->save();

        return ['status' => 'success'];
    }

    public function uploadTemplate(Request $request)
    {
        # code...
    }

    /**
     * Supported ESPs
     * 
     * @return string[] 
     */
    private function getProviders()
    {
        return [
            'Postmark',
        ];
    }
}
