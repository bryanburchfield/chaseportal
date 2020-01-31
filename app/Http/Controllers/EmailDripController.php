<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailDripCampaign;
use App\Http\Requests\ValidEmailServiceProvider;
use App\Models\EmailDripCampaign;
use App\Models\EmailDripCampaignFilter;
use App\Models\EmailServiceProvider;
use App\Models\Script;
use App\Services\EmailDripService;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EmailDripController extends Controller
{
    // Directory where Email Service Providers live
    // This is in the service class too!
    const ESP_DIR = 'Interfaces\\EmailServiceProvider';

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
            'provider_types' => $this->getProviderTypes(),
            'campaigns' => $this->getAllCampaigns(),
            'templates' => $this->getTemplates(),
            'operators' => $this->getOperators(),
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
    {
        $email_drip_campaign = new EmailDripCampaign($request->all());

        $email_drip_campaign->user_id = Auth::User()->id;
        $email_drip_campaign->group_id = Auth::User()->group_id;

        $email_drip_campaign->save();

        return [
            'status' => 'success',
            'email_drip_campaign_id' => $email_drip_campaign->id,
        ];
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

        return [
            'status' => 'success',
            'email_drip_campaign_id' => $email_drip_campaign->id,
        ];
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

        $sql = "SELECT FieldName, [Type]
            FROM AdvancedTableFields
            INNER JOIN FieldTypes ON FieldTypes.id = AdvancedTableFields.FieldType
            WHERE AdvancedTable = :table_id";

        $results = resultsToList($this->runSql($sql, ['table_id' => $table_id]));

        return $results;
    }

    /**
     * Return list of merge fields
     * 
     * @param Request $request 
     * @return array 
     */
    public function getFilterFields(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->id);
        $request->merge(['campaign' => $email_drip_campaign->campaign]);

        return $this->defaultLeadFields() +
            $this->getExtraLeadfields() +
            $this->getTableFields($request);
    }

    /**
     * Return list of templates named 'email_*'
     * 
     * @return mixed 
     */
    public function getTemplates()
    {
        // Set sqlsrv database
        config(['database.connections.sqlsrv.database' => Auth::user()->db]);

        // Find SQL Server for templates named "email_*"
        return Script::where('GroupId', Auth::User()->group_id)
            ->where('Name', 'like', 'email_%')
            ->whereNotNull('HtmlContent')
            ->where('HtmlContent', '!=', '')
            ->get();
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

    /**
     * Return list of custom properties for a provider type
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getProperties(Request $request)
    {
        // full path the class so we don't have to import it
        $class = 'App\\' . self::ESP_DIR . '\\' .
            Str::studly($request->provider_type);

        return $class::properties();
    }

    /**
     * Return all filters for a Drip Campaign
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function getFilters(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->email_drip_campaign_id);

        return $email_drip_campaign->emailDripCampaignFilters;
    }

    public function updateFilters(Request $request)
    {
        if ($request->has('filters')) {
            foreach ($request->filters as $filter) {
                $email_drip_campaign_filter = new EmailDripCampaignFilter($filter);
                $email_drip_campaign_filter->email_drip_campaign_id = $request->email_drip_campaign_id;
                $email_drip_campaign_filter->save();
            }
        }

        return ['status' => 'success'];
    }

    public function getOperators()
    {
        $mathops = [
            '=' => trans('tools.equals'),
            '!=' => trans('tools.not_equals'),
            '<' => trans('tools.less_than'),
            '>' => trans('tools.greater_than'),
            '<=' => trans('tools.less_than_or_equals'),
            '>=' => trans('tools.greater_than_or_equals'),
            'blank' => trans('tools.is_blank'),
            'not_blank' => trans('tools.is_not_blank'),
        ];

        $dateops = [
            'days_ago' => trans('tools.days_ago'),
            'days_from_now' => trans('tools.days_from_now'),
            '<_days_ago' => trans('tools.less_than_days_ago'),
            '>_days_ago' => trans('tools.greater_than_days_ago'),
            '<_days_from_now' => trans('tools.less_than_days_from_now'),
            '>_days_from_now' => trans('tools.greater_than_days_from_now'),
        ];

        return [
            'integer' => $mathops,
            'string' => $mathops,
            'date' => array_merge($mathops, $dateops),
            'text' => $mathops,
            'phone' => $mathops,
        ];
    }

    /////////////  Private functions ////////////////

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
     * Return list of provider types
     * 
     * @return Collection 
     */
    private function getProviderTypes()
    {
        // Look in the directory for provider interfaces
        $models = collect(File::allFiles(app_path(self::ESP_DIR)));

        return $models->map(function ($file) {
            return Str::snake(substr($file->getFilename(), 0, -4));
        });
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

    private function getExtraLeadfields()
    {
        return [
            'CallStatus' => 'string',
            'Date' => 'date',
            'Attempt' => 'integer',
            'WasDialed' => 'integer',
            'LastUpdated' => 'date',
        ];
    }
}
