<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Services\PDF;

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

        $response = $pdf->Output('S');

        return Response::make($response, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="report.pdf"',
        ]);


        // if (empty($email)) {
        //     $pdf->Output();
        //     exit;
        // } else {
        //     return $pdf->Output('S');
        // }
    }

    public function csvExport($request)
    {
        $results = $this->getResults($request);

        return $results;



        // check for errors
        if (is_object($results)) {
            return null;
        }
    }

    public function xlsExport($request)
    {
        $results = $this->getResults($request);

        // check for errors
        if (is_object($results)) {
            return null;
        }
    }

    public function htmlExport($request)
    {
        $results = $this->getResults($request);

        // check for errors
        if (is_object($results)) {
            return null;
        }
    }
}
