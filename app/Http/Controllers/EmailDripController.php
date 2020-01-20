<?php

namespace App\Http\Controllers;

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
        ];

        return view('tools.email_drip.index')->with($data);
    }

    public function editDrip(Request $request)
    {
        # code...
    }

    /**
     * Email Service Providers index
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function espIndex()
    {
        $email_service_providers = EmailServiceProvider::where('group_id', Auth::User()->group_id)
            ->orderby('name')
            ->get();

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
            'providers' => $this->getProviders(),
            'email_service_providers' => $email_service_providers,
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

    /**
     * Get Providers
     * 
     * Returns a collection of supported ESPs
     * May need to tableize this later and add other fields
     * @return string[] 
     */
    private function getProviders()
    {
        return [
            'postmark',
        ];
    }
}
