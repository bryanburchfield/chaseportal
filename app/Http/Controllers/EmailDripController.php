<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailDripTemplate;
use App\Http\Requests\ValidEmailServiceProvider;
use App\Models\EmailDripTemplate;
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

    public function addTemplate(ValidEmailDripTemplate $request)
    {
        $email_drip_template = new EmailDripTemplate($request->all());

        $email_drip_template->group_id = Auth::User()->group_id;
        $email_drip_template->user_id = Auth::User()->id;

        // upload 'email_tempate' file into 'body'
        // ?????

        $email_drip_template->save();

        return ['status' => 'success'];
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
