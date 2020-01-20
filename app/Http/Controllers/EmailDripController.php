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
            'providers' => $this->getProviders(),
            'email_service_providers' => $this->espIndex(),
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
        return EmailServiceProvider::where('group_id', Auth::User()->group_id)
            ->orderby('name')
            ->get();

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
