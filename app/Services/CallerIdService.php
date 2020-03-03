<?php

namespace App\Services;

use App\Mail\CallerIdMail;
use App\Models\Dialer;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class CallerIdService
{
    use SqlServerTraits;
    use TimeTraits;

    private $group_id;
    private $dialer_numb;
    private $email_to;
    private $startdate;
    private $enddate;

    public function __construct($group_id = null)
    {
        $this->group_id = $group_id;

        if ($this->group_id !== null) {
            $this->setGroup();
        }
    }

    private function setGroup()
    {
        // hard-coded recips
        $canned = [
            235773  => [
                'dialer_numb' => 26,
                'email_to' => 'g.sandoval@chasedatacorp.com',
            ],
        ];

        $this->dialer_numb = $canned[$this->group_id]['dialer_numb'];
        $this->email_to = $canned[$this->group_id]['email_to'];
    }

    public static function execute($group_id = null)
    {
        $caller_id_service = new CallerIdService($group_id);

        $caller_id_service->runReport();
    }

    public function runReport()
    {
        $results = $this->runQuery();

        if (!empty($results)) {
            $pdf = $this->makePdf($results);
            $this->emailReport($pdf);
        }
    }

    private function runQuery()
    {
        $this->enddate = Carbon::parse('midnight');
        $this->startdate = $this->enddate->copy()->subDay(30);

        $bind = [];

        $sql = "SELECT GroupID, GroupName, CallerId, SUM(cnt) Dials FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('id', 7)->get() as $i => $dialer) {

            if ($this->dialer_numb !== null && $dialer->dialer_numb != $this->dialer_numb) {
                continue;
            }

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();

            $sql .= " $union SELECT DR.GroupId, G.GroupName, DR.CallerId, COUNT(*) cnt FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                WHERE DR.Date >= :startdate$i and DR.Date < :enddate$i
                AND DR.CallerId != ''";

            if ($this->group_id !== null) {
                $bind['group_id'] = $this->group_id;
                $sql .= " AND DR.GroupId = :group_id";
            }

            $sql .= "
                GROUP BY DR.GroupId, GroupName, CallerId
                HAVING COUNT(*) >= 5500
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, CallerId
            HAVING SUM(cnt) >= 5500
            ORDER BY GroupName, Dials desc";

        return $this->runSql($sql, $bind);
    }

    private function makePdf($results)
    {
        $pagesize = 29;
        $totrows = count($results);

        $totpages = floor($totrows / $pagesize);
        $totpages += floor($totrows / $pagesize) == ($totrows / $pagesize) ? 0 : 1;

        $headers = [
            'GroupID',
            'GroupName',
            'CallerID',
            'Dials in Last 30 Days',
        ];

        $pdf = new PDF();

        for ($i = 1; $i <= $totpages; $i++) {
            $data = $this->arrayData(array_slice($results, ($i - 1) * $pagesize, $pagesize));

            // format numbers
            foreach ($data as &$row) {
                $row[3] = number_format($row[3]);
            }

            $pdf->AddPage('L', 'Legal');
            $pdf->FancyTable($headers, $data);
        }

        return $pdf->Output('S');
    }

    private function arrayData($array)
    {
        $data = [];

        foreach ($array as $rec) {
            $data[] = array_values($rec);
        }

        return $data;
    }

    private function emailReport($pdf)
    {
        if ($this->email_to === null) {
            $to = 'jonathan.gryczka@chasedatacorp.com';
            $cc = 'ahmed@chasedatacorp.com';
        } else {
            $to = $this->email_to;
            $cc = 'bryan.burchfield@chasedatacorp.com';

            // $cc = [
            //     'jonathan.gryczka@chasedatacorp.com',
            //     'ahmed@chasedatacorp.com',
            // ];
        }

        // email report
        $message = [
            'subject' => 'Caller ID Report',
            'pdf' => base64_encode($pdf),
            'url' => url('/') . '/',
            'startdate' => $this->startdate->toFormattedDateString(),
            'enddate' => $this->enddate->toFormattedDateString(),
        ];

        Mail::to($to)
            ->cc($cc)
            ->send(new CallerIdMail($message));
    }
}
