<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailServiceProvider;
use App\Models\Campaign;
use App\Models\EmailServiceProvider;
use App\Services\EmailDripService;
use App\Traits\CampaignTraits;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlaybookController extends Controller
{
    use CampaignTraits;

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
     * Return all string fields of the Custom Table tied to a campaign
     * 
     * @param Request $request 
     * @return array|mixed 
     */
    public function getTableFields(Request $request)
    {
        $campaign = new Campaign(['CampaignName' => $request->campaign, 'GroupId' => Auth::user()->group_id]);

        return $campaign->customTableFields();
    }

    /**
     * Get Subcampaigns (ajax)
     * 
     * @param Request $request 
     * @return array[] 
     */
    public function getSubcampaigns(Request $request)
    {
        $results = $this->getAllSubcampaignsWithNone($request->campaign);

        return ['subcampaigns' => $results];
    }

    /**
     * Append "!!none!!" to the list of subcampaigns
     * 
     * @param mixed $campaign 
     * @return mixed 
     */
    private function getAllSubcampaignsWithNone($campaign)
    {
        $results = $this->getAllSubcampaigns($campaign);
        $results = ['!!none!!' => trans('tools.no_subcampaign')] + $results;

        return $results;
    }

    /**
     * Return list of operators for filters
     * 
     * @param bool $detail 
     * @return array 
     */
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
}
