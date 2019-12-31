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

    public function processFile(DncFile $dnc_file)
    {
        foreach ($dnc_file->dncFileDetails as $dnc_file_detail) {
            $this->insertDnc($dnc_file_detail);
            $dnc_file_detail->save();
        }

        $dnc_file->processed_at = now();
        $dnc_file->save();
    }

    public function reverseFile(DncFile $dnc_file)
    {
        foreach ($dnc_file->dncFileDetails as $dnc_file_detail) {
            $this->reverseDnc($dnc_file_detail);
            $dnc_file_detail->save();
        }

        $dnc_file->reversed_at = now();
        $dnc_file->save();
    }

    private function insertDnc($dnc_file_detail)
    {
        // No need to insert if it failed on load
        if ($dnc_file_detail->succeeded === 0) {
            return;
        }

        echo "Inserting DNC: " . $dnc_file_detail->phone .
            " for group " . $this->group_id .
            "\n";

        // $result = $this->api->InsertDncNumber($this->group_id, $dnc_file_detail->phone);
        $result = $this->testingResult();

        echo "Done\n";

        $dnc_file_detail->processed_at = now();

        if ($result === false) {
            $dnc_file_detail->succeeded = false;
            $dnc_file_detail->error = $this->api->GetLastError();
        } else {
            $dnc_file_detail->succeeded = true;
            $dnc_file_detail->error = null;
        }
    }

    private function reverseDnc($dnc_file_detail)
    {
        // No need to reverse if it failed original insert
        if ($dnc_file_detail->succeeded !== 1) {
            return;
        }

        echo "Reversing DNC: " . $dnc_file_detail->phone .
            " for group " . $this->group_id .
            "\n";

        // $result = $this->api->DeleteDncNumber($this->group_id, $dnc_file_detail->phone);
        $result = $this->testingResult();

        echo "Done\n";

        $dnc_file_detail->processed_at = now();

        if ($result === false) {
            $dnc_file_detail->succeeded = false;
            $dnc_file_detail->error = $this->api->GetLastError();
        } else {
            $dnc_file_detail->succeeded = true;
            $dnc_file_detail->error = null;
        }
    }

    // delete this function after testing is done
    private function testingResult()
    {
        return rand(0, 1) == 0 ? false : true;
    }
}
