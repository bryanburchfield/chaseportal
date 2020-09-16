<?php

namespace App\Services\Reports;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\ReportTraits;
use Illuminate\Support\Carbon;

class EcovermeLeadExport
{
    use ReportTraits;

    public function __construct()
    {
        $this->initilaizeParams();

        $this->params['reportName'] = 'reports.ecoverme_lead_export';
        $this->params['call_statuses'] = [];
        $this->params['columns'] = [
            'id' => 'id',
            'FirstName' => 'FirstName',
            'LastName' => 'LastName',
            'Address' => 'Address',
            'City' => 'City',
            'State' => 'State',
            'ZipCode' => 'ZipCode',
            'PrimaryPhone' => 'PrimaryPhone',
            'SecondaryPhone' => 'SecondaryPhone',
            'Rep' => 'Rep',
            'CallStatus' => 'CallStatus',
            'Date' => 'Date',
            'Campaign' => 'Campaign',
            'Attempt' => 'Attempt',
            'WasDialed' => 'WasDialed',
            'LastUpdated' => 'LastUpdated',
            'Notes' => 'Notes',
            'Subcampaign' => 'Subcampaign',
            'CallType' => 'CallType',
            'FullName' => 'FullName',
            'HobbyColor' => 'HobbyColor',
            'TobaccoUse' => 'TobaccoUse',
            'AgeGender' => 'AgeGender',
            'DateOfBirth' => 'DateOfBirth',
            'DoctorCityState' => 'DoctorCityState',
            'SocialSecurityNumber' => 'SocialSecurityNumber',
            'StateCoOfBirth' => 'StateCoOfBirth',
            'LeadOwner' => 'LeadOwner',
            'AppStatus' => 'AppStatus',
            'HeightWeight' => 'HeightWeight',
            'HealthConditions' => 'HealthConditions',
            'CurrentPastInsurance' => 'CurrentPastInsurance',
            'PremiumChosen' => 'PremiumChosen',
            'FaceAmount' => 'FaceAmount',
            'PrimaryBeneficiary' => 'PrimaryBeneficiary',
            'Phone2' => 'Phone2',
            'P2Type' => 'P2Type',
            'Email' => 'Email',
            'DraftDate' => 'DraftDate',
            'NameOnAccount' => 'NameOnAccount',
            'BankCityStateType' => 'BankCityStateType',
            'RoutingCCNumber' => 'RoutingCCNumber',
            'AccountNumberCCV' => 'AccountNumberCCV',
            'EnrollerPolicyNumber' => 'EnrollerPolicyNumber',
            'Closer' => 'Closer',
            'Lead_Provider' => 'Lead_Provider',
            'Date_Of_Lead' => 'Date_Of_Lead',
            'Old_List' => 'Old_List',
            'Old_Disposition' => 'Old_Disposition',
            'MiddleInitial' => 'MiddleInitial',
            'BeneficiaryRelationship' => 'BeneficiaryRelationship',
            'RecordingOldLeadData' => 'RecordingOldLeadData',
            'ContingentRelation' => 'ContingentRelation',
            'LastModifiedBy' => 'LastModifiedBy',
            'CountryCode' => 'CountryCode',
            'PhoneType1' => 'PhoneType1',
            'PhoneType2' => 'PhoneType2',
            'PolicyType' => 'PolicyType',
        ];
    }

    public function getFilters()
    {
        $filters = [
            'call_statuses' => $this->getAllCallStatuses(),
            'db_list' => Auth::user()->getDatabaseArray(),
        ];

        return $filters;
    }

    public function getInfo()
    {
        return [
            'columns' => [],
            'paragraphs' => 1,
        ];
    }

    private function executeReport($all = false)
    {
        list($sql, $bind) = $this->makeQuery($all);

        $results = $this->runSql($sql, $bind);

        if (empty($results)) {
            $this->params['totrows'] = 0;
            $this->params['totpages'] = 1;
            $this->params['curpage'] = 1;
        } else {
            $this->params['totrows'] = $results[0]['totRows'];

            foreach ($results as &$rec) {
                $rec = $this->processRow($rec);
            }
            $this->params['totpages'] = floor($this->params['totrows'] / $this->params['pagesize']);
            $this->params['totpages'] += floor($this->params['totrows'] / $this->params['pagesize']) == ($this->params['totrows'] / $this->params['pagesize']) ? 0 : 1;
        }

        return $results;
    }

    public function processRow($rec)
    {
        $tz = Auth::user()->iana_tz;

        // remove totRows col
        array_pop($rec);

        // Convert dates to local
        $rec['Date'] = Carbon::parse($rec['Date'])->tz($tz)->isoFormat('L LT');

        if (!empty($rec['LastUpdated'])) {
            $rec['LastUpdated'] = Carbon::parse($rec['LastUpdated'])->tz($tz)->isoFormat('L LT');
        }

        return $rec;
    }

    public function makeQuery($all)
    {
        $this->setHeadings();

        list($fromDate, $toDate) = $this->dateRange($this->params['fromdate'], $this->params['todate']);

        // convert to datetime strings
        $startDate = $fromDate->format('Y-m-d H:i:s');
        $endDate = $toDate->format('Y-m-d H:i:s');

        $tz =  Auth::user()->tz;

        $join = '';
        $bind = [
            'group_id' =>  Auth::user()->group_id,
            'startdate' => $startDate,
            'enddate' => $endDate,
        ];

        $sql = "SET NOCOUNT ON;";

        if (!empty($this->params['call_statuses']) && $this->params['call_statuses'] != '*') {
            $call_statuses = str_replace("'", "''", implode('!#!', $this->params['call_statuses']));
            $bind['call_statuses'] = $call_statuses;

            $sql .= "
            CREATE TABLE #SelectedCallStatus(CallStatusName varchar(50) Primary Key);
            INSERT INTO #SelectedCallStatus SELECT DISTINCT [value] from dbo.SPLIT(:call_statuses, '!#!');";

            $join = "INNER JOIN #SelectedCallStatus CS on CS.CallStatusName = L.CallStatus";
        }

        $sql .= "
        SELECT
            L.[id],
            L.[FirstName],
            L.[LastName],
            L.[Address],
            L.[City],
            L.[State],
            L.[ZipCode],
            L.[PrimaryPhone],
            L.[SecondaryPhone],
            L.[Rep],
            L.[CallStatus],
            L.[Date],
            L.[Campaign],
            L.[Attempt],
            L.[WasDialed],
            L.[LastUpdated],
            L.[Notes],
            L.[Subcampaign],
            L.[CallType],
            L.[FullName],
            A.[HobbyColor],
            A.[TobaccoUse],
            A.[AgeGender],
            A.[DateOfBirth],
            A.[DoctorCityState],
            A.[SocialSecurityNumber],
            A.[StateCoOfBirth],
            A.[LeadOwner],
            A.[AppStatus],
            A.[HeightWeight],
            A.[HealthConditions],
            A.[CurrentPastInsurance],
            A.[PremiumChosen],
            A.[FaceAmount],
            A.[PrimaryBeneficiary],
            A.[Phone2],
            A.[P2Type],
            A.[Email],
            A.[DraftDate],
            A.[NameOnAccount],
            A.[BankCityStateType],
            A.[RoutingCCNumber],
            A.[AccountNumberCCV],
            A.[EnrollerPolicyNumber],
            A.[Closer],
            A.[Lead_Provider],
            A.[Date_Of_Lead],
            A.[Old_List],
            A.[Old_Disposition],
            A.[MiddleInitial],
            A.[BeneficiaryRelationship],
            A.[RecordingOldLeadData],
            A.[ContingentRelation],
            A.[LastModifiedBy],
            A.[CountryCode],
            A.[PhoneType1],
            A.[PhoneType2],
            A.[PolicyType],
            totRows = COUNT(*) OVER()
        FROM Leads L
        INNER JOIN [ADVANCED_Ecover Fields] A ON A.LeadId = L.IdGuid
        $join
        WHERE L.GroupId = :group_id
        AND TRY_CAST(A.[Date_Of_Lead] as Date) BETWEEN :startdate AND :enddate";

        // Check params
        if (!empty($this->params['orderby']) && is_array($this->params['orderby'])) {
            $sort = '';
            foreach ($this->params['orderby'] as $col => $dir) {
                $sort .= ",$col $dir";
            }
            $sql .= ' ORDER BY ' . substr($sort, 1);
        } else {
            $sql .= ' ORDER BY [id]';
        }

        if (!$all) {
            $offset = ($this->params['curpage'] - 1) * $this->params['pagesize'];
            $sql .= " OFFSET $offset ROWS FETCH NEXT " . $this->params['pagesize'] . " ROWS ONLY";
        }

        return [$sql, $bind];
    }

    private function processInput(Request $request)
    {
        // Get vals from session if not set (for exports)
        $request = $this->getSessionParams($request);

        // Check page filters
        $this->checkPageFilters($request);

        // Check report filters
        $this->checkDateRangeFilters($request);

        if (!empty($request->call_statuses)) {
            $this->params['call_statuses'] = $request->call_statuses;
        }

        // Save params to session
        $this->saveSessionParams();

        return $this->errors;
    }
}
