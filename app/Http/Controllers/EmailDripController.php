<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailDripCampaign;
use App\Http\Requests\ValidEmailServiceProvider;
use App\Models\AdvancedTable;
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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class EmailDripController extends Controller
{
    // Directory where Email Service Providers live
    // This is in the service class too!
    const ESP_DIR = 'Interfaces/EmailServiceProvider';

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

        // create blank drip campaign so shared blade doesn't throw errors
        $email_drip_campaign = new EmailDripCampaign();

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'email_service_providers' => $this->getEmailServiceProviders(),
            'email_drip_campaigns' => $this->getDripCampaigns(),
            'email_drip_campaign' => $email_drip_campaign,
            'email_fields' => [],
            'provider_types' => $this->getProviderTypes(),
            'campaigns' => $this->getAllCampaigns(),
            'templates' => $this->getTemplates(),
        ];

        return view('tools.email_drip.index')->with($data);
    }

    public function updateFilters(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->email_drip_campaign_id);

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'group_id' => Auth::user()->group_id,
            'email_drip_campaign' => $email_drip_campaign,
            'operators' => $this->getOperators(),
            'filter_fields' => $this->getFilterFields($email_drip_campaign),
            'operators' => $this->getOperators(),
        ];

        return view('tools.email_drip.update_filters')->with($data);
    }

    /**
     * Edit a drip campaign
     * 
     * @param Request $request 
     * @return mixed 
     */
    public function editEmailDripCampaign(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->id);

        $campaign_request = new Request(['campaign' => $email_drip_campaign->campaign]);

        $page = [
            'menuitem' => 'tools',
            'type' => 'other',
        ];
        $data = [
            'page' => $page,
            'email_drip_campaign' => $email_drip_campaign,
            'email_service_providers' => $this->getEmailServiceProviders(),
            'email_fields' => $this->getTableFields($campaign_request),
            'campaigns' => $this->getAllCampaigns(),
            'subcampaigns' => $this->getAllSubcampaignsWithNone($campaign_request),
            'templates' => $this->getTemplates(),
        ];

        return view('tools.email_drip.edit_campaign')->with($data);
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
                'error' => [trans('tools.provider_in_use')],
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
        // create array with "" if subcamp 'none' was selected
        if (is_array($request->subcampaign) && empty($request->subcamp)) {
            $request->merge(['subcampaign' => ['']]);
        }

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
        $email_drip_campaign = $this->findEmailDripCampaign($request->id);

        $email_drip_campaign->fill($request->all());
        $email_drip_campaign->user_id = Auth::User()->id;

        $email_drip_campaign->save();

        return redirect()->action('EmailDripController@index');
    }

    /**
     * Delete an Email Drip Campaign
     * 
     * @param Request $request 
     * @return string[] 
     */
    public function deleteEmailDripCampaign(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->id);
        $email_drip_campaign->delete();

        return ['status' => 'success'];
    }

    /**
     * Get Subcampaigns (ajax)
     * 
     * @param Request $request 
     * @return array[] 
     */
    public function getAllSubcampaignsWithNone(Request $request)
    {
        $results = $this->getAllSubcampaigns($request->campaign);
        $results = ['!!none!!' => trans('tools.no_subcampaign')] + $results;

        return ['subcampaigns' => $results];
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
    public function getFilterFields(EmailDripCampaign $email_drip_campaign)
    {
        $request = new Request(['campaign' => $email_drip_campaign->campaign]);

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
            ->where('Name', 'like', 'email[_]%')
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

        // Toggle active
        $email_drip_campaign->active = !$email_drip_campaign->active;

        // If activating, reset run dates
        if ($email_drip_campaign->active == 1) {
            $email_drip_campaign->last_run_from = now();
            $email_drip_campaign->last_run_to = $email_drip_campaign->last_run_from;
        }

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
        $class = 'App\\' . str_replace('/', '\\', self::ESP_DIR) . '\\' .
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

    public function saveFilters(Request $request)
    {
        // 404's if they spoofed the wrong campaign id
        $email_drip_campaign = $this->findEmailDripCampaign($request->email_drip_campaign_id);

        if ($request->has('filters')) {

            // validate them all before doing any db stuff
            foreach ($request->filters as $i => $array) {

                // this will throw an exception if anything fails validation
                $this->performValidateFilter(
                    $email_drip_campaign,
                    $array['field'],
                    $array['operator'],
                    $array['value'],
                    $i + 1
                );
            }
        }

        // All good!  Delete existing filters
        $email_drip_campaign->emailDripCampaignFilters()->delete();

        if ($request->has('filters')) {
            // Insert each filter
            foreach ($request->filters as $array) {

                $filter = [];
                $filter['field'] = $array['field'];
                $filter['operator'] = $array['operator'];
                $filter['value'] = $array['value'];

                $email_drip_campaign_filter = new EmailDripCampaignFilter($filter);
                $email_drip_campaign_filter->email_drip_campaign_id = $email_drip_campaign->id;
                $email_drip_campaign_filter->save();
            }
        }

        return ['status' => 'success'];
    }

    public function getOperators($detail = false)
    {
        $mathops_detail = [
            '=' => [
                'description' => trans('tools.equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '!=' => [
                'description' => trans('tools.not_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '<' => [
                'description' => trans('tools.less_than'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '>' => [
                'description' => trans('tools.greater_than'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '<=' => [
                'description' => trans('tools.less_than_or_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            '>=' => [
                'description' => trans('tools.greater_than_or_equals'),
                'allow_nulls' => false,
                'value_type' => 'string',
            ],
            'blank' => [
                'description' => trans('tools.is_blank'),
                'allow_nulls' => true,
                'value_type' => null,
            ],
            'not_blank' => [
                'description' => trans('tools.is_not_blank'),
                'allow_nulls' => true,
                'value_type' => null,
            ],
        ];

        $dateops_detail = [
            'days_ago' => [
                'description' => trans('tools.days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            'days_from_now' => [
                'description' => trans('tools.days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '<_days_ago' => [
                'description' => trans('tools.less_than_days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '>_days_ago' => [
                'description' => trans('tools.greater_than_days_ago'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '<_days_from_now' => [
                'description' => trans('tools.less_than_days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
            '>_days_from_now' => [
                'description' => trans('tools.greater_than_days_from_now'),
                'allow_nulls' => false,
                'value_type' => 'integer',
            ],
        ];

        // there's a better way to do this
        if ($detail) {
            $mathops = $mathops_detail;
            $dateops = $dateops_detail;
        } else {
            $mathops = [];
            $dateops = [];
            foreach ($mathops_detail as $key => $array) {
                $mathops[$key] = $array['description'];
            }
            foreach ($dateops_detail as $key => $array) {
                $dateops[$key] = $array['description'];
            }
        }

        return [
            'integer' => $mathops,
            'string' => $mathops,
            'date' => array_merge($mathops, $dateops),
            'text' => $mathops,
            'phone' => $mathops,
        ];
    }

    /**
     * Return the Custom Table ID tied to a dialer campaign
     * 
     * @param mixed $campaign 
     * @return int|mixed 
     */
    public function getCustomTableId($campaign)
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

    public function getCustomTableName($table_id)
    {
        if ($table_id == -1) {
            return null;
        }

        $advanced_table = AdvancedTable::find($table_id);

        return $advanced_table->TableName;
    }

    public function deleteFilter(Request $request)
    {
        $campaign_filter = EmailDripCampaignFilter::findOrFail($request->id);
        $campaign_filter->delete();
        return ['status' => 'success'];
    }

    public function validateFilter(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->email_drip_campaign_id);
        list($field, $operator, $value) = $request->filters;

        return $this->performValidateFilter($email_drip_campaign, $field, $operator, $value);
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
        $files = collect(File::allFiles(app_path(self::ESP_DIR)));

        $provider_types = [];

        foreach ($files as $file) {
            $provider_type = Str::snake(substr($file->getFilename(), 0, -4));
            $class = 'App\\' . str_replace('/', '\\', self::ESP_DIR) . '\\' .
                Str::studly($provider_type);
            $provider_types[$provider_type] = $class::description();
        };

        return $provider_types;
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

    private function performValidateFilter(EmailDripCampaign $email_drip_campaign, $field, $operator, $value, $line = 0)
    {
        $fields = $this->getFilterFields($email_drip_campaign);
        $operators = $this->getOperators(true);

        if ($line) {
            $line_numb = ' ' . trans('tools.on_line') . ' ' . $line;
        } else {
            $line_numb = '';
        }

        // check the field is valid
        if (!in_array($field, array_keys($fields))) {
            $error = ValidationException::withMessages([
                'error' => [trans('tools.invalid_field') . $line_numb],
            ]);
            throw $error;
        }

        // find operators for that type
        $operators = $operators[$fields[$field]];

        // see that it's valid
        if (!in_array($operator, array_keys($operators))) {
            $error = ValidationException::withMessages([
                'error' => [trans('tools.invalid_operator') . $line_numb],
            ]);
            throw $error;
        }

        // get details of operator
        $operator = $operators[$operator];

        // check if value is empty when not allowed to be
        if (!$operator['allow_nulls'] && empty($value)) {
            $error = ValidationException::withMessages([
                'error' => [trans('tools.value_required') . $line_numb],
            ]);
            throw $error;
        }

        // check if value is integer
        if ($operator['value_type'] == 'integer' && !is_numeric($value)) {
            $error = ValidationException::withMessages([
                'error' => [trans('tools.value_must_be_numeric') . $line_numb],
            ]);
            throw $error;
        }

        return ['status' => 'success'];
    }
}
