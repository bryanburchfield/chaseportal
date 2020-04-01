<?php

namespace App\Http\Controllers;

use App\Models\EmailServiceProvider;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PlaybookController extends Controller
{
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
            'group_id' => Auth::user()->group_id,
            'email_service_providers' => $this->getEmailServiceProviders(),
            'email_fields' => [],
            'campaigns' => $this->getAllCampaigns(),
            'subcampaigns' => [],
        ];

        return view('tools.playbook.campaigns')->with($data);
    }

    /**
     * Playbook Filters index
     * 
     * @return View|Factory 
     */
    public function FilterIndex()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
        ];

        return view('tools.playbook.filters')->with($data);
    }

    /**
     * Playbook Actions index
     * 
     * @return View|Factory 
     */
    public function ActionIndex()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'campaigns' => $this->getAllCampaigns(),
        ];

        return view('tools.playbook.actions')->with($data);
    }

    /**
     * Email Serice Providers index
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function EmailServiceProviderIndex()
    {
        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'email_service_providers' => $this->getEmailServiceProviders(),
            'provider_types' => EmailServiceProvider::providerTypes(),
        ];

        return view('tools.playbook.email_service_providers')->with($data);
    }

    /**
     * Providers configured for this group
     * 
     * @return mixed 
     */
    private function getEmailServiceProviders()
    {
        return EmailServiceProvider::where('group_id', Auth::User()->group_id)
            ->orderBy('name')
            ->get();
    }
}
