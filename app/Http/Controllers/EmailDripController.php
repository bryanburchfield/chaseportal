<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidEmailDripCampaign;
use App\Http\Requests\ValidSmtpServer;
use App\Models\EmailDripCampaign;
use App\Models\SmtpServer;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Swift_Mailer;
use Swift_SmtpTransport;
use Swift_TransportException;

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
            'smtp_servers' => $this->getSmtpServers(),
            'email_drip_campaigns' => $this->getDripCampaigns(),
            'campaigns' => $this->getAllCampaigns(),
            'templates' => $this->getTemplates(),
        ];

        return view('tools.email_drip.index')->with($data);
    }

    public function addSmtpServer(ValidSmtpServer $request)
    {
        $smtp_server = new SmtpServer($request->all());

        $smtp_server->user_id = Auth::User()->id;
        $smtp_server->group_id = Auth::User()->group_id;

        $smtp_server->save();

        return ['status' => 'success'];
    }

    public function updateSmtpServer(ValidSmtpServer $request)
    {
        $smtp_server = $this->findSmtpServer($request->id);

        $smtp_server->fill($request->all());
        $smtp_server->user_id = Auth::User()->id;

        $smtp_server->save();

        return ['status' => 'success'];
    }

    public function deleteSmtpServer(Request $request)
    {
        $smtp_server = $this->findSmtpServer($request->id);

        // check for campaigns
        if ($smtp_server->emailDripCampaigns->count()) {
            $error = ValidationException::withMessages([
                'error' => ['This server is in use by one or more campaigns'],
            ]);
            throw $error;
        }

        $smtp_server->delete();

        return ['status' => 'success'];
    }

    public function getSmtpServer(Request $request)
    {
        return $this->findSmtpServer($request->id);
    }

    private function findSmtpServer($id)
    {
        return SmtpServer::where('id', $id)
            ->where('group_id', Auth::User()->group_id)
            ->firstOrFail();
    }

    /**
     * Servers configured for this group
     * 
     * @return mixed 
     */
    private function getSmtpServers()
    {
        return SmtpServer::where('group_id', Auth::User()->group_id)
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

    private function findEmailDripCampaign($id)
    {
        return EmailDripCampaign::where('id', $id)
            ->where('group_id', Auth::User()->group_id)
            ->firstOrFail();
    }

    public function testConnection(ValidSmtpServer $request)
    {
        // see if we can connect to server
        try {
            // $transport = (new Swift_SmtpTransport($request->host, $request->port, 'tls'))
            $transport = (new Swift_SmtpTransport($request->host, $request->port, 'tls'))
                ->setUsername($request->username)
                ->setPassword($request->password);

            $mailer = new Swift_Mailer($transport);
            $mailer->getTransport()->start();
            return [
                'status' => 'success',
                'message' => 'Connected Successfuly',
            ];
        } catch (Swift_TransportException $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        } catch (\Exception $e) {
            $error = ValidationException::withMessages([
                'error' => [$e->getMessage()],
            ]);
            throw $error;
        }
    }

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

    public function deleteEmailDripCampaign(Request $request)
    {
        $email_campaign = EmailDripCampaign::findOrFail($request->id);
        $email_campaign->delete();

        return ['status' => 'success'];
    }

    private function getCampaigns()
    {
        return ['campaigns' => array_values($this->getAllCampaigns())];
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

    public function toggleEmailDripCampaign(Request $request)
    {
        $email_drip_campaign = $this->findEmailDripCampaign($request->id);

        $email_drip_campaign->active = !$email_drip_campaign->active;
        $email_drip_campaign->save();

        return ['status' => 'success'];
    }
}
