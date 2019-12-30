<?php

namespace App\Http\Controllers;

use App\Imports\DncImportWithHeaders;
use App\Imports\DncImportNoHeaders;
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

        $files = DncFile::select(
            'id',
            'description',
            'uploaded_at',
            'process_started_at',
            'processed_at',
            'reverse_started_at',
            'reversed_at'
        )
            ->orderBy('id', 'desc')
            ->get();

        foreach ($files as $file) {
            // get details
            $file->recs = $file->dncFileDetails->count();
            $file->errors = $file->dncFileDetails->where('succeeded', "!=", null)->where('succeeded', false)->count();

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

            if (!empty($file->reversed_at)) {
                $file->reversed_at = Carbon::parse($file->reversed_at)
                    ->tz($tz)
                    ->toDateTimeString();
            } else {
                $file->reversed_at = '';
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
        if ($request->has_headers) {
            $column = 'phone';
        } else {
            $column = 0;
        }

        // insert dnc_file record
        $dnc_file = DncFile::create([
            'group_id' => Auth::user()->group_id,
            'user_id' => Auth::user()->id,
            'description' => $request->description,
            'uploaded_at' => now(),
            'processed_at' => null,
        ]);

        // load file
        if ($request->has_headers) {
            $importer = new DncImportWithHeaders($dnc_file->id, $column);
        } else {
            $importer = new DncImportNoHeaders($dnc_file->id, $column);
        }

        Excel::import($importer, $request->file('myfile'));

        $request->session()->flash('flash', 'Uploaded ' . $importer->getCount() . ' records.');

        return redirect()->action('DncController@index');
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
