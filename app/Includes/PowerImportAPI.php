<?php

namespace App\Includes;

/*
    Power Import API
    version 1.0

    - Schnorr digital signature scheme
    - Curl call

    Copyright (c) ChaseData Corp, 2009-2016.
*/

class PowerImportAPI
{
    // Crypto system parameters
    private $p;
    private $q;
    private $b;

    // Secret key.
    private $a;

    private $server = "";
    private $error  = "";
    public $response = "";

    public function __construct($ip)
    {
        $this->a = config('services.powerapi.a');
        $this->b = config('services.powerapi.b');
        $this->p = config('services.powerapi.p');
        $this->q = config('services.powerapi.q');

        $this->server = $ip;

        $v = bcpowmod($this->b, $this->a, $this->p);
        $v = $this->modInverse($v, $this->p);
        if ($v < 0)
            $v = bcadd($v, $this->p);
    }

    //
    //  -------------------------- PRIVATE FUNCTIONS ---------------------------
    //

    // Returns inverse number.
    // Based on extended Euclid algorithm.
    private function modInverse($a, $b)
    {
        $x = "0";
        $y = "1";
        $lastx = "1";
        $lasty = "0";
        while ($b != "0") {
            $q = bcdiv($a, $b);

            $temp = $b;
            $b = bcmod($a, $b);
            $a = $temp;

            $temp = $x;
            $t = bcmul($q, $x);
            $x = bcsub($lastx, $t);
            $lastx = $temp;

            $temp = $y;
            $t = bcmul($q, $y);
            $y = bcsub($lasty, $t);
            $lasty = $temp;
        }

        return $lastx;
    }

    // Converts Hex numbers to Decimal for computation.
    private function HexToDecString($hex)
    {
        $result = "";
        for ($i = 0; $i < strlen($hex); $i++)
            $result .= hexdec($hex[$i]);

        return $result;
    }

    // returns decimal hash for computation
    private function MakeDecHash($value)
    {
        return $this->HexToDecString(md5($value));
    }

    // Sign message using random key.
    private function sign($message)
    {
        $r = rand(1, $this->q - 1);
        $x = bcpowmod($this->b, $r, $this->p);

        // digital signature
        $e = $this->MakeDecHash($message . $x);
        $y = bcmod(bcadd($r, bcmul($this->a, $e)), $this->q);

        return array("e" => $e, "y" => $y);
    }

    private function SetErrorAndExit($msg)
    {
        $this->response = "";
        $this->error = $msg;
        return false;
    }

    private function SendData($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->server . "/CommandsProcessor.aspx?tm=" . time());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0');

        $body   = curl_exec($ch);

        $errnum = curl_errno($ch);
        $errmsg = curl_error($ch);

        $info   = curl_getinfo($ch);
        curl_close($ch);

        if ($info["http_code"] != "200") {
            $errnum = $info["http_code"];
            $errmsg = $body;
        }

        if ($errnum != 0) {
            $this->response = "";
            return $this->SetErrorAndExit("CURL error[" . $errnum . "]: " . $errmsg);
        }

        $this->response = $body;
        return true;
    }

    //
    //  -------------------------- PUBLIC FUNCTIONS ---------------------------
    //

    // Retrieve last error if ImportData return "false"
    public function GetLastError()
    {
        return $this->error;
    }

    // Retrieve response
    public function GetResponse()
    {
        return $this->response;
    }

    // Call to import new DNC number into DB
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function InsertDncNumber($groupid, $number)
    {
        $data = array(
            "[phone]" => $number,
        );
        return $this->CustomOperation($data, $groupid, "ImportDnc");
    }

    // Call to remove existing DNC number from DB
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function DeleteDncNumber($groupid, $number)
    {
        $data = array(
            "[phone]" => $number,
        );
        return $this->CustomOperation($data, $groupid, "RemoveDnc");
    }

    // Call to update data in Power DB
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function UpdateDataByLeadId($dataArray, $groupid, $campaign, $subcampaign, $identifier)
    {
        return $this->UpdateLeadEx($dataArray, $groupid, $campaign, $subcampaign, "LeadId", $identifier);
    }

    // Call to update data in Power DB
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function UpdateDataByClientId($dataArray, $groupid, $campaign, $subcampaign, $identifier)
    {
        return $this->UpdateLeadEx($dataArray, $groupid, $campaign, $subcampaign, "ClientId", $identifier);
    }

    // Call to update data in Power DB
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function UpdateDataByPhone($dataArray, $groupid, $campaign, $subcampaign, $identifier)
    {
        return $this->UpdateLeadEx($dataArray, $groupid, $campaign, $subcampaign, "Phone", $identifier);
    }

    // Call to insert data in Power DB
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function ImportData($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck)
    {
        return $this->InsertLeadEx($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck, "ImportLead");
    }

    // Call to insert hot lead data in Power DB
    // and dial this lead right away
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function InsertHotLead($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck)
    {
        return $this->InsertLeadEx($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck, "InsertHotLead");
    }

    // Call to insert hot lead data in Power DB
    // and dial this lead right away with timezone check
    // "true" if succeeded, otherwise "false"
    // if failed - call "GetLastError" for explanations
    public function InsertHotLeadWithTimezoneCheck($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck)
    {
        return $this->InsertLeadEx($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck, "InsertHotLeadTimezone");
    }

    private function CustomOperation($dataArray, $groupid, $action)
    {
        if (!is_array($dataArray))
            return $this->SetErrorAndExit("Input data should be an array");

        if ($groupid == "")
            return $this->SetErrorAndExit("GroupId should be specified");

        if (
            isset($dataArray["[action]"]) ||
            isset($dataArray["[e]"]) ||
            isset($dataArray["[y]"]) ||
            isset($dataArray["[groupid]"])
        )
            return $this->SetErrorAndExit("'[groupid]' is the reserved word. Remove any of them from parameters list");


        // make hash from the main fields
        // and sign it for verification
        $hash = "";
        foreach ($dataArray as $key => $value) {
            $hash .= $this->MakeDecHash($key . $value);
        }

        $signature = $this->sign($hash);

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        ) {
            // try to re-sign again.
            $signature = $this->sign($hash);
        }

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        )
            return $this->SetErrorAndExit("Signing message error. Please, contact support");

        $dataArray["[action]"]   = $action;
        $dataArray["[e]"]        = $signature["e"];
        $dataArray["[y]"]        = $signature["y"];
        $dataArray["[groupid]"] = $groupid;

        return $this->SendData($dataArray);
    }

    private function InsertLeadEx($dataArray, $groupid, $campaign, $subcampaign, $dialDups, $dialNonCallables, $duplicatesCheck, $action)
    {
        if (!is_array($dataArray))
            return $this->SetErrorAndExit("Input data should be an array");

        if ($groupid == "")
            return $this->SetErrorAndExit("GroupId should be specified");

        if ($campaign == "")
            return $this->SetErrorAndExit("Campaign name should be specified");

        if (empty($dataArray))
            return $this->SetErrorAndExit("No data to import");

        if (
            isset($dataArray["[action]"]) ||
            isset($dataArray["[e]"]) ||
            isset($dataArray["[y]"]) ||
            isset($dataArray["[campaign]"]) ||
            isset($dataArray["[subcampaign]"]) ||
            isset($dataArray["[groupid]"])
        )
            return $this->SetErrorAndExit("'[groupid]', '[campaign]', '[subcampaign]', '[action]', '[e]', '[y]' are the reserved words. Remove any of them from parameters list");


        // append extended fields
        if (isset($dataArray["[extended]"]) && is_array($dataArray["[extended]"])) {
            foreach ($dataArray["[extended]"] as $key => $value)
                $dataArray['#extended#' . $key] = $value;

            unset($dataArray["[extended]"]);
        }

        // make hash from the main fields
        // and sign it for verification
        $hash = "";
        foreach ($dataArray as $key => $value) {
            $hash .= $this->MakeDecHash($key . $value);
        }

        $signature = $this->sign($hash);

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        ) {
            // try to re-sign again.
            $signature = $this->sign($hash);
        }

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        )
            return $this->SetErrorAndExit("Signing message error. Please, contact support");

        $dataArray["[action]"]   = $action;
        $dataArray["[e]"]        = $signature["e"];
        $dataArray["[y]"]        = $signature["y"];
        $dataArray["[groupid]"] = $groupid;
        $dataArray["[campaign]"] = $campaign;
        $dataArray["[subcampaign]"] = $subcampaign;
        $dataArray["[AllowDialingDups]"] = $dialDups;
        $dataArray["[DialNonCallables]"] = $dialNonCallables;

        if ($duplicatesCheck != -1)
            $dataArray["[DuplicatesCheck]"] = $duplicatesCheck;

        return $this->SendData($dataArray);
    }

    private function UpdateLeadEx($dataArray, $groupid, $campaign, $subcampaign, $searchField, $identifier)
    {
        if (!is_array($dataArray))
            return $this->SetErrorAndExit("Input data should be an array");

        if ($groupid == "")
            return $this->SetErrorAndExit("GroupId should be specified");

        if (empty($dataArray))
            return $this->SetErrorAndExit("No data to update");

        if (
            isset($dataArray["[action]"]) ||
            isset($dataArray["[e]"]) ||
            isset($dataArray["[y]"]) ||
            isset($dataArray["[identifier]"]) ||
            isset($dataArray["[searchField]"]) ||
            isset($dataArray["[subcampaign]"]) ||
            isset($dataArray["[campaign]"]) ||
            isset($dataArray["[groupid]"])
        )
            return $this->SetErrorAndExit("'[groupid]', '[campaign]', '[subcampaign]', '[identifier]', '[searchField]', '[action]', '[e]', '[y]' are the reserved words. Remove any of them from parameters list");

        // append extended fields
        if (isset($dataArray["[extended]"]) && is_array($dataArray["[extended]"])) {
            foreach ($dataArray["[extended]"] as $key => $value)
                $dataArray['#extended#' . $key] = $value;

            unset($dataArray["[extended]"]);
        }

        // make hash from the main fields
        // and sign it for verification
        $hash = "";
        foreach ($dataArray as $key => $value) {
            $hash .= $this->MakeDecHash($key . $value);
        }

        $signature = $this->sign($hash);

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        ) {
            // try to re-sign again.
            $signature = $this->sign($hash);
        }

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        )
            return $this->SetErrorAndExit("Signing message error. Please, contact support");

        $dataArray["[action]"]   = "UpdateLead";
        $dataArray["[e]"]        = $signature["e"];
        $dataArray["[y]"]        = $signature["y"];
        $dataArray["[groupid]"] = $groupid;
        $dataArray["[campaign]"] = $campaign;
        $dataArray["[subcampaign]"] = $subcampaign;
        $dataArray["[identifier]"] = $identifier;
        $dataArray["[searchField]"] = $searchField;

        return $this->SendData($dataArray);
    }

    public function LeadOperation($leadId, $disposition, $action)
    {
        // make hash from the main fields
        // and sign it for verification
        $signature = $this->sign($this->MakeDecHash("[disposition]" . $disposition));

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        ) {
            return $this->SetErrorAndExit("Signing message error. Please, contact support");
        }

        $dataArray["[action]"]   = $action;
        $dataArray["[e]"]        = $signature["e"];
        $dataArray["[y]"]        = $signature["y"];
        $dataArray["[groupid]"] = -999; // not being used
        $dataArray["[identifier]"] = $leadId;
        $dataArray["[disposition]"] = $disposition;

        return $this->SendData($dataArray);
    }

    public function AgentDialListOperation($action, $agent, $list, $phone)
    {
        // make hash from the main fields
        // and sign it for verification
        $signature = $this->sign($this->MakeDecHash("[list]" . $list));
        $signature = $this->sign($this->MakeDecHash("[phone]" . $phone));

        if (
            $signature["e"] == "" || $signature["e"] == "0" ||
            $signature["y"] == "" || $signature["y"] == "0"
        ) {
            return $this->SetErrorAndExit("Signing message error. Please, contact support");
        }

        $dataArray["[action]"]   = $action;
        $dataArray["[e]"]        = $signature["e"];
        $dataArray["[y]"]        = $signature["y"];
        $dataArray["[groupid]"] = -999; // not being used
        $dataArray["[identifier]"] = $agent;
        $dataArray["[list]"] = $list;
        $dataArray["[phone]"] = $phone;

        return $this->SendData($dataArray);
    }
}
