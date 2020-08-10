<?php

namespace App\Services;

use App\Includes\PowerImportAPI;
use App\Models\Dialer;
use App\Models\DncFile;
use App\Models\User;
use Illuminate\Support\Facades\App;

class DncService
{
    protected $api;
    protected $group_id;

    public function __construct($user_id)
    {
        $user = User::findOrFail($user_id);
        $dialer = Dialer::where('reporting_db', $user->db)->firstOrFail();

        $this->group_id = $user->group_id;
        $this->api = new PowerImportAPI('http://' . $dialer->dialer_fqdn . '/PowerStudio/WebAPI');
    }

    /**
     * Process a DNC File
     * 
     * @param DncFile $dnc_file 
     * @return void 
     */
    public function processFile(DncFile $dnc_file)
    {
        if ($dnc_file->action == 'add') {
            $dnc_file->dncFileDetails->each(function ($dnc_file_detail) {
                $this->insertDnc($dnc_file_detail);
            });
        } else {
            $dnc_file->dncFileDetails->each(function ($dnc_file_detail) {
                $this->deleteDnc($dnc_file_detail);
            });
        }

        $dnc_file->processed_at = now();
        $dnc_file->save();
    }

    /**
     * Reverse a DNC File
     * 
     * @param DncFile $dnc_file 
     * @return void 
     */
    public function reverseFile(DncFile $dnc_file)
    {
        if ($dnc_file->action == 'add') {
            $dnc_file->dncFileDetails->each(function ($dnc_file_detail) {
                $this->deleteDnc($dnc_file_detail);
            });
        } else {
            $dnc_file->dncFileDetails->each(function ($dnc_file_detail) {
                $this->insertDnc($dnc_file_detail);
            });
        }

        $dnc_file->reversed_at = now();
        $dnc_file->save();
    }

    /**
     * Insert a DNC record into SQL Server db
     * 
     * @param mixed $dnc_file_detail 
     * @return void 
     */
    private function insertDnc($dnc_file_detail)
    {
        // No need to insert if it failed on load, or reverse if it failed insert
        if ($dnc_file_detail->succeeded === 0) {
            return;
        }

        $this->executeApi('InsertDncNumber', $dnc_file_detail);
    }

    /**
     * Delete a DNC record from SQL Server db
     * 
     * @param mixed $dnc_file_detail 
     * @return void 
     */
    private function deleteDnc($dnc_file_detail)
    {
        // No need to delete if it failed on load, or reverse if it failed delete
        if ($dnc_file_detail->succeeded === 0) {
            return;
        }

        $this->executeApi('DeleteDncNumber', $dnc_file_detail);
    }

    /**
     * Execute API
     * 
     * @param mixed $function 
     * @param mixed $dnc_file_detail 
     * @return void 
     */
    private function executeApi($function, $dnc_file_detail)
    {
        // Don't submit if local (testing)
        if (App::environment('local')) {
            $result = true;
        } else {
            $result = $this->api->$function($this->group_id, $dnc_file_detail->phone);
        }

        $dnc_file_detail->processed_at = now();

        if ($result === false) {
            $dnc_file_detail->succeeded = false;
            $dnc_file_detail->error = $this->api->GetLastError();
        } else {
            $dnc_file_detail->succeeded = true;
            $dnc_file_detail->error = null;
        }
        $dnc_file_detail->save();
    }
}
