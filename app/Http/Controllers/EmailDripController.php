<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailDripCampaign;
use App\Http\Requests\ValidEmailServiceProvider;
use App\Interfaces\EmailServiceProvider\Smtp;
use App\Models\EmailDripCampaign;
use App\Models\EmailServiceProvider;
use App\Services\EmailDripService;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EmailDripController extends Controller
{
    use SqlServerTraits;
    use CampaignTraits;

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
            'email_service_providers' => $this->getEmailServiceProviders(),
            'email_drip_campaigns' => $this->getDripCampaigns(),
            'campaigns' => $this->getAllCampaigns(),
            'templates' => $this->getTemplates(),
        ];

        return view('tools.email_drip.index')->with($data);
    }

    /**
     * Add an SMTP Server
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
     * Update an SMTP Server
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
     * Delete an SMTP Server
     * 
     * @param Request $request 
     * @return string[] 
     * @throws ValidationException 
     */
    public function deleteEmailServiceProvider(Request $request)
    {
        $email_service_provider = $this->findEmailServiceProvider($request->id);

        // check for campaigns
        if ($email_service_provider->emailDripCampaigns->count()) {
            $error = ValidationException::withMessages([
                'error' => ['This server is in use by one or more campaigns'],
            ]);
            throw $error;
        }

        $email_service_provider->delete();

        return ['status' => 'success'];
    }

    /**
     * Return an SMTP Server (ajax)
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getEmailServiceProvider(Request $request)
    {
        return $this->findEmailServiceProvider($request->id);
    }

    /**
     * Find SMTP server by ID
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
     * Servers configured for this group
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
     * List of drip campaingns for this group
     * 
     * @return mixed 
     */
    private function getDripCampaigns()
    {
        return EmailDripCampaign::where('group_id', Auth::User()->group_id)
            ->orderBy('name')
            ->get();
    }

    /**
     * Find an Email Drip Campaign by id
     * 
     * @param mixed $id 
     * @return mixed 
     */
    private function findEmailDripCampaign($id)
    {
        return EmailDripCampaign::where('id', $id)
            ->where('group_id', Auth::User()->group_id)
            ->firstOrFail();
    }

    /**
     * Test SMTP server connection
     * 
     * @param ValidEmailServiceProvider $request 
     * @return string[] 
     * @throws ValidationException 
     */
    public function testConnection(ValidEmailServiceProvider $request)
    {
        // Convert request class to model class
        $email_drip_service = new EmailDripService(new EmailServiceProvider($request->all()));

        return $email_drip_service->testConnection();
    }

    /**
     * Add an Email Drip Campaign
     * 
     * @param ValidEmailDripCampaign $request 
     * @return string[] 
     */
    public function addEmailDripCampaign(ValidEmailDripCampaign $request)
    // public function addEmailDripCampaign(Request $request)
    {
        // Log::debug($request->all());
        // die();

        $email_drip_campaign = new EmailDripCampaign($request->all());

        $email_drip_campaign->user_id = Auth::User()->id;
        $email_drip_campaign->group_id = Auth::User()->group_id;

        $email_drip_campaign->save();

        return ['status' => 'success'];
    }

    /**
     * Update Drip Campaign 
     * 
     * @param ValidEmailDripCampaign $request 
     * @return string[] 
     */
    public function updateEmailDripCampaign(ValidEmailDripCampaign $request)
    {

        $email_drip_campaign = EmailDripCampaign::findOrFail($request->id);

        $email_drip_campaign->fill($request->all());
        $email_drip_campaign->user_id = Auth::User()->id;

        $email_drip_campaign->save();

        return ['status' => 'success'];
    }

    /**
     * Delete an Email Drip Campaign
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deleteEmailDripCampaign(Request $request)
    {
        $email_campaign = EmailDripCampaign::findOrFail($request->id);
        $email_campaign->delete();

        return ['status' => 'success'];
    }

    /**
     * Return list of dialer campaigns
     * 
     * @return array[] 
     * @throws InvalidArgumentException 
     */
    private function getCampaigns()
    {
        return ['campaigns' => array_values($this->getAllCampaigns())];
    }

    /**
     * Return drip campaign
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getEmailDripCampaign(Request $request)
    {
        return EmailDripCampaign::findOrFail($request->id);
    }

    /**
     * Get Subcampaigns (ajax)
     * 
     * @param Request $request 
     * @return array[] 
     */
    public function getSubcampaigns(Request $request)
    {
        $results = $this->getAllSubcampaigns($request->campaign);

        return ['subcampaigns' => array_values($results)];
    }

    /**
     * Return all string fields of the Custom Table tied to a campaign
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function getTableFields(Request $request)
    {
        $table_id = $this->getCustomTableId($request->campaign);

        if ($table_id == -1) {
            return [];
        }

        $sql = "SELECT FieldName, [Description]
            FROM AdvancedTableFields
            WHERE AdvancedTable = :table_id
            AND FieldType = 2";

        $results = resultsToList($this->runSql($sql, ['table_id' => $table_id]));

        // Add field name to desc
        foreach ($results as $field => &$description) {
            $description = '[' . $field . '] ' . $description;
        }

        return $results;
    }

    /**
     * Return the Custom Table ID tied to a dialer campaign
     * 
     * @param mixed $campaign 
     * @return int|mixed 
     */
    private function getCustomTableId($campaign)
    {
        $sql = "SELECT AdvancedTable
            FROM Campaigns
            WHERE GroupId = :group_id
            AND CampaignName = :campaign";

        $bind = [
            'group_id' => Auth::User()->group_id,
            'campaign' => $campaign,
        ];

        $results = $this->runSql($sql, $bind);

        if (!isset($results[0]['AdvancedTable'])) {
            return -1;
        }

        return $results[0]['AdvancedTable'];
    }

    public function getTemplates()
    {

        // return defined templates for this group_id
        return [
            11 => 'Template 11',
            15 => 'Template 15',
            35 => 'Template 35',
        ];
    }

    /**
     * Toggle an Email Drip Campaign active/inactive
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function toggleEmailDripCampaign(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->id);

        $email_drip_campaign->active = !$email_drip_campaign->active;
        $email_drip_campaign->save();

        return ['status' => 'success'];
    }

    public function getProperties(Request $request)
    {
        $class = Str::studly($request->provider_type);

        return $class::getProperties();
    }
}
