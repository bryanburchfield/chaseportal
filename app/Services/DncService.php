<?php

namespace App\Services;

use App\Includes\PowerImportAPI;
use App\Models\Dialer;
use App\Models\DncFile;
use App\Models\User;

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
        foreach ($dnc_file->dncFileDetails as $dnc_file_detail) {
            $this->insertDnc($dnc_file_detail);
            $dnc_file_detail->save();
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
        foreach ($dnc_file->dncFileDetails as $dnc_file_detail) {
            $this->reverseDnc($dnc_file_detail);
            $dnc_file_detail->save();
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
        // No need to insert if it failed on load
        if ($dnc_file_detail->succeeded === 0) {
            return;
        }

        $result = $this->api->InsertDncNumber($this->group_id, $dnc_file_detail->phone);

        $dnc_file_detail->processed_at = now();

        if ($result === false) {
            $dnc_file_detail->succeeded = false;
            $dnc_file_detail->error = $this->api->GetLastError();
        } else {
            $dnc_file_detail->succeeded = true;
            $dnc_file_detail->error = null;
        }
    }

    /**
     * Delete a DNC record from SQL Server db
     * 
     * @param mixed $dnc_file_detail 
     * @return void 
     */
    private function reverseDnc($dnc_file_detail)
    {
        // No need to reverse if it failed original insert
        if ($dnc_file_detail->succeeded !== 1) {
            return;
        }

        $result = $this->api->DeleteDncNumber($this->group_id, $dnc_file_detail->phone);

        $dnc_file_detail->processed_at = now();

        if ($result === false) {
            $dnc_file_detail->succeeded = false;
            $dnc_file_detail->error = $this->api->GetLastError();
        } else {
            $dnc_file_detail->succeeded = true;
            $dnc_file_detail->error = null;
        }
    }
}
