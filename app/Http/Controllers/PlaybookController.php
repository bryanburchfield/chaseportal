<?php

namespace App\Http\Controllers;

use App\Models\EmailServiceProvider;
use App\Models\Script;
use App\Traits\CampaignTraits;
use App\Traits\SqlServerTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PlaybookController extends Controller
{
    // Directory where Email Service Providers live
    // This is in the service class too!
    const ESP_DIR = 'Interfaces/EmailServiceProvider';

    use CampaignTraits;
    use SqlServerTraits;

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
     * Email Drip Campaign index
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
            'provider_types' => $this->getProviderTypes(),
        ];

        return view('tools.playbook.email_service_providers')->with($data);
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
}
