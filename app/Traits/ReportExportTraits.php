<?php

namespace App\Traits;

use Maatwebsite\Excel\Facades\Excel;
use App\Services\PDF;
use App\Exports\ReportExport;
use App\Mail\ReportMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

trait ReportExportTraits
{
    public function pdfExport($request)
    {
        ini_set('max_execution_time', 600);

        $this->params['pagesize'] = 29;

        $results = $this->getResults($request);

        // check for errors
        if (is_object($results)) {
            return null;
        }

        $headers = array_values($this->params['columns']);

        $pdf = new PDF();

        for ($i = 1; $i <= $this->params['totpages']; $i++) {
            // Grab the page we want from results
            $data = $this->arrayData(array_slice($results, ($i - 1) * $this->params['pagesize'], $this->params['pagesize']));
            $pdf->AddPage('L', 'Legal');
            $pdf->FancyTable($headers, $data);
        }

        if (empty($request->email)) {
            $pdf->Output();
            exit;
        } else {
            return $pdf->Output('S');
        }
    }

    public function htmlExport($request)
    {
        ini_set('max_execution_time', 600);

        $results = $this->getResults($request);

        // check for errors
        if (is_object($results)) {
            return null;
        }

        return view('reports.export')
            ->with([
                'params' => $this->params,
                'results' => $results,
            ]);
    }

    public function csvExport($request)
    {
        return $this->doExport($request, 'csv');
    }

    public function xlsExport($request)
    {
        return $this->doExport($request, 'xls');
    }

    private function doExport($request, $format)
    {
        ini_set('max_execution_time', 600);

        $results = $this->getResults($request);

        // check for errors
        if (is_object($results)) {
            return null;
        }

        array_unshift($results, array_values($this->params['columns']));

        $export = new ReportExport($results);

        // need to figure this out why this doesn't work
        // $class = '\Maatwebsite\Excel\Excel::' . strtoupper($format);
        // return Excel::download($export, 'report.' . $format, $class);

        switch ($format) {
            case 'csv':
                return Excel::download($export, 'report.' . $format, \Maatwebsite\Excel\Excel::CSV);
            case 'xls':
                return Excel::download($export, 'report.' . $format, \Maatwebsite\Excel\Excel::XLS);
            case 'xlsx':
                return Excel::download($export, 'report.' . $format, \Maatwebsite\Excel\Excel::XLSX);
            default:
                return $results;
        }
    }

    public function emailReport($request)
    {
        $pdf = $this->pdfExport($request);

        $reportName = $this->params['reportName'];

        // default date range if report reqs it and not already set
        if (isset($this->params['fromdate'])) {
            $this->params['fromdate'] = date("m/d/Y", strtotime('-1 day'));
            $this->params['todate'] = date("m/d/Y");
        }

        if (isset($this->params['fromdate'])) {
            $daterange = "Date Range: " . date('m/d/Y g:i:s A', strtotime($this->params['fromdate'])) .
                " to " . date('m/d/Y g:i:s A', strtotime($this->params['todate'])) . "\n";
        } else {
            $now = utcToLocal((new \DateTime()), Auth::user()->tz)->format('m/d/Y h:i:s A');
            $daterange = "As of: $now\n";
        }

        // email report
        $message = [
            'to' => $request->email,
            'subject' => $reportName,
            'reportName' => $reportName,
            'daterange' => $daterange,
            'url' => url('/') . '/dashboards/',
            'pdf' => $pdf,
        ];
        $this->sendEmail($message);
    }

    private function sendEmail($message)
    {
        Mail::to($message['to'])
            ->send(new ReportMail($message));
    }
}
