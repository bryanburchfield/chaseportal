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

    public static function execute()
    {
        $caller_id_service = new CallerIdService;

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
        $enddate = Carbon::parse('midnight');
        $startdate = $enddate->copy()->subDay();

        $bind = [];

        $sql = "SELECT GroupID, GroupName, CallerId, SUM(cnt) Dials FROM (";

        $union = '';
        // foreach (Dialer::all() as $i => $dialer) {
        foreach (Dialer::where('id', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $startdate->toDateTimeString();
            $bind['enddate' . $i] = $enddate->toDateTimeString();

            $sql .= " $union SELECT DR.GroupId, G.GroupName, DR.CallerId, COUNT(*) cnt FROM " .
                '[' . $dialer->reporting_db . ']' . ".[dbo].[DialingResults] DR
                INNER JOIN " . '[' . $dialer->reporting_db . ']' .
                ".[dbo].[Groups] G on G.GroupId = DR.GroupId
                WHERE DR.Date >= :startdate$i and DR.Date < :enddate$i
                AND DR.CallerId != ''
                GROUP BY DR.GroupId, GroupName, CallerId
                HAVING COUNT(*) >= 5000
                ";

            $union = 'UNION ALL';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, CallerId
            HAVING SUM(cnt) >= 5000
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
            'Count',
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
        // email report
        $message = [
            'to' => 'bryan.burchfield@chasedatacorp.com',
            'subject' => 'Caller ID Report',
            'pdf' => base64_encode($pdf),
            'url' => url('/') . '/',
            'date' => Carbon::parse('yesterday midnight')->toFormattedDateString(),
        ];
        $this->sendEmail($message);
    }

    private function sendEmail($message)
    {
        Mail::to($message['to'])
            ->send(new CallerIdMail($message));
    }
}
