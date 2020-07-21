<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailServiceProvider;
use App\Models\EmailServiceProvider;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaybookEmailProviderController extends Controller
{
    /**
     * Email Serice Providers index
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function index()
    {
        $page = [
            'menuitem' => 'playbook',
            'sidenav' => 'main',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_email_providers.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', 'https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'email_service_providers' => $this->getEmailServiceProviders(),
            'provider_types' => EmailServiceProvider::providerTypes(),
        ];

        return view('playbook.email_service_providers')->with($data);
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

    /**
     * Find Email Serivce Provider by ID
     * 
     * @param mixed $id 
     * @return mixed 
     */
    private function findEmailServiceProvider($id)
    {
        return EmailServiceProvider::where('id', $id)
            ->where('group_id', Auth::User()->group_id)
            ->firstOrFail();
    }

    /**
     * Return list of custom properties for a provider type
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getProviderProperties(Request $request)
    {
        return EmailServiceProvider::providerProperties($request->provider_type);
    }

    /**
     * Return an Email Serivce Provider (ajax)
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getEmailServiceProvider(Request $request)
    {
        return $this->findEmailServiceProvider($request->id);
    }

    /**
     * Add an Email Serivce Provider
     * 
     * @param ValidEmailServiceProvider $request 
     * @return string[] 
     */
    public function addEmailServiceProvider(ValidEmailServiceProvider $request)
    {
        $email_service_provider = new EmailServiceProvider($request->all());

        $email_service_provider->user_id = Auth::User()->id;
        $email_service_provider->group_id = Auth::User()->group_id;

        $email_service_provider->save();

        return ['status' => 'success'];
    }

    /**
     * Update an Email Serivce Provider
     * 
     * @param ValidEmailServiceProvider $request 
     * @return string[] 
     */
    public function updateEmailServiceProvider(ValidEmailServiceProvider $request)
    {
        $email_service_provider = $this->findEmailServiceProvider($request->id);

        $email_service_provider->fill($request->all());
        $email_service_provider->user_id = Auth::User()->id;

        $email_service_provider->save();

        return ['status' => 'success'];
    }

    /**
     * Delete an Email Serivce Provider
     * 
     * @param Request $request 
     * @return string[] 
     * @throws HttpResponseException 
     */
    public function deleteEmailServiceProvider(Request $request)
    {
        $email_service_provider = $this->findEmailServiceProvider($request->id);

        $email_service_provider->delete();

        return ['status' => 'success'];
    }

    /**
     * Test SMTP server connection
     * 
     * @param ValidEmailServiceProvider $request 
     * @return mixed 
     */
    public function testConnection(ValidEmailServiceProvider $request)
    {
        $email_service_provider = new EmailServiceProvider($request->all());

        $class = $email_service_provider->providerClassName();

        $email_service_provider = new $class($email_service_provider);

        return $email_service_provider->testConnection();
    }
}
