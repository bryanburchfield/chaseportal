<?php

namespace App\Traits;

use Maatwebsite\Excel\Facades\Excel;
use App\Services\PDF;
use App\Exports\ReportExport;

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
}
