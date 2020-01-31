<?php

namespace App\Traits;

use Maatwebsite\Excel\Facades\Excel;
use App\Services\PDF;
use App\Exports\ReportExport;
use App\Mail\ReportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

trait ReportExportTraits
{
    private function increaseLimits()
    {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '1G');
    }

    public function pdfExport($request)
    {
        $this->increaseLimits();

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

    public function htmlExport(Request $request)
    {
        $this->increaseLimits();

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
        $this->increaseLimits();

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
        $tz = Auth::user()->iana_tz;
        $reportName = trans($this->params['reportName']);

        // default date range if report requires it
        if (isset($this->params['fromdate'])) {
            $fromdate = Carbon::parse('midnight yesterday', $tz)->tz('UTC');
            $todate = Carbon::parse('midnight today', $tz)->tz('UTC');
            $request->request->add(['fromdate' => $fromdate]);
            $request->request->add(['todate' => $todate]);
        }

        $pdf = $this->pdfExport($request);

        // Bail if there's no report to be sent
        if (empty($pdf)) {
            return;
        }

        if (isset($this->params['fromdate'])) {
            $daterange = trans('reports.from') . ': ' .
                $fromdate->tz($tz)->isoFormat('lll') . ' ' .
                trans('reports.to') . ' ' .
                $todate->tz($tz)->isoFormat('lll') . "\n";
        } else {
            $daterange = trans('reports.as_of') . ': ' .
                Carbon::parse()->tz($tz)->isoFormat('lll') . "\n";
        }

        // store pdf to temp file
        $filename = tempnam(sys_get_temp_dir(), 'report_');
        $fp = fopen($filename, 'w');
        fwrite($fp, $pdf);
        fclose($fp);

        // email report
        $message = [
            'to' => $request->email,
            'subject' => $reportName,
            'reportName' => $reportName,
            'daterange' => $daterange,
            'pdf' => $filename,
            'url' => url('/') . '/',
            'settings' => url('/dashboards/automatedreports'),
        ];
        $this->sendEmail($message);

        unlink($filename);
    }

    private function sendEmail($message)
    {
        Mail::to($message['to'])
            ->send(new ReportMail($message));
    }
}
