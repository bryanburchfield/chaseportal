<?php

namespace App\Http\Controllers;

use App\Imports\DncImportHeader;
use App\Imports\DncImportNoHeader;
use App\Models\DncFile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class DncController extends Controller
{
    public function index()
    {
        $page['menuitem'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'files' => $this->getFiles(),
        ];

        return view('tools.dnc_importer')->with($data);
    }

    private function getFiles()
    {
        $tz = Auth::user()->ianaTz;

        $files = DncFile::select('id', 'description', 'uploaded_at', 'processed_at')
            ->orderBy('uploaded_at', 'desc')
            ->get();

        foreach ($files as $file) {
            // get details
            $file->recs = $file->dncFileDetails->count();
            $file->errors = $file->dncFileDetails->where('succeeded', false)->count();

            // format dates
            $file->uploaded_at = Carbon::parse($file->uploaded_at)
                ->tz($tz)
                ->toDateTimeString();
            if (!empty($file->processed_at)) {
                $file->processed_at = Carbon::parse($file->processed_at)
                    ->tz($tz)
                    ->toDateTimeString();
            } else {
                $file->processed_at = '';
            }
        }

        return $files->toArray();
    }

    public function uploadIndex()
    {
        $page['menuitem'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
        ];

        return view('tools.dnc_upload')->with($data);
    }

    public function uploadFile(Request $request)
    {
        try {
            $headings = (new HeadingRowImport())->toArray($request->file('myfile'));
        } catch (\Exception $e) {
            $headings = null;
        }

        if (isset($headings[0][0][1])) {
            $headings = $headings[0][0];
            // need to figure out column
            $column = '?';
        } elseif (isset($headings[0][0][0])) {
            if ($request->has_headers) {
                $column = $headings[0][0][0];
            } else {
                $column = 0;
            }
        } else {
            return back()->withErrors(['file' => ['Error in file.']]);
        }

        dd($column);

        // insert dnc_file record
        $dnc_file_id = 99;

        // load file
        if ($request->has_headers) {
            Excel::import(new DncImportHeader($dnc_file_id, $column), $request->file('myfile'));
        } else {
            Excel::import(new DncImportNoHeader($dnc_file_id, $column), $request->file('myfile'));
        }

        // roll it all back if errors

        return $request->file('myfile');
    }

    public function deleteFile(Request $request)
    {
        return $request->all();
    }

    public function processFile(Request $request)
    {
        return $request->all();
    }
}
