<?php

namespace App\Http\Controllers;

use App\Imports\DncImport;
use App\Models\DncFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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
        Excel::import(new DncImport(99), $request->file('myfile'));

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
