<?php

namespace App\Http\Controllers;

use App\Imports\DncImportWithHeaders;
use App\Imports\DncImportNoHeaders;
use App\Jobs\ProcessDncFile;
use App\Jobs\ReverseDncFile;
use App\Models\DncFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'filename',
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
            $file->errors = $file->errorRecs()->count();

            // format dates
            $file->uploaded_at = Carbon::parse($file->uploaded_at)
                ->tz($tz)
                ->isoFormat('L LT');

            if (!empty($file->processed_at)) {
                $file->processed_at = Carbon::parse($file->processed_at)
                    ->tz($tz)
                    ->isoFormat('L LT');
            } else {
                $file->processed_at = '';
            }

            if (!empty($file->reversed_at)) {
                $file->reversed_at = Carbon::parse($file->reversed_at)
                    ->tz($tz)
                    ->isoFormat('L LT');
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
        if ($request->has('cancel')) {
            return redirect()->action('DncController@index');
        }

        if ($request->has_headers) {
            $column = 'phone';
        } else {
            $column = 0;
        }

        // We have no control over what files the user throws at us
        // so wrap this in a transaction in case it craps out
        DB::beginTransaction();

        // insert dnc_file record
        $dnc_file = DncFile::create([
            'group_id' => Auth::user()->group_id,
            'user_id' => Auth::user()->id,
            'filename' => $request->file('myfile')->getClientOriginalName(),
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

        // Commit all the inserts
        DB::commit();

        $dnc_file->refresh();

        $tot_recs = $dnc_file->dncFileDetails->count();

        if (
            $tot_recs == 0 ||
            $tot_recs == $dnc_file->errorRecs()->count()
        ) {
            $dnc_file->delete();
            session()->flash('flash', 'ERROR: No valid phone numbers could be found in that file');
        } else {
            session()->flash('flash', 'Uploaded ' . $tot_recs . ' records.');
        }

        return redirect()->action('DncController@index');
    }

    public function handleAction(Request $request)
    {
        list($action, $id) = explode(':', $request->action);

        switch ($action) {
            case 'delete':
                $this->deleteFile($id);
                break;
            case 'process':
                $this->processFile($id);
                break;
            case 'reverse':
                $this->reverseFile($id);
                break;
            default:
                abort(404);
        }

        return redirect()->action('DncController@index');
    }

    public function showErrors(Request $request)
    {
        $dnc_file = DncFile::where('id', $request->id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();

        $page['menuitem'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $dnc_file,
            'records' => $dnc_file->errorRecs()->paginate(50),
        ];

        return view('tools.dnc_records')->with($data);
    }

    public function showRecords(Request $request)
    {
        $dnc_file = DncFile::where('id', $request->id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();

        $page['menuitem'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $dnc_file,
            'records' => $dnc_file->dncFileDetails()->paginate(50),
        ];

        return view('tools.dnc_records')->with($data);
    }

    private function deleteFile($id)
    {
        $dnc_file = DncFile::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->whereNull('process_started_at')
            ->firstOrFail();

        $dnc_file->delete();

        session()->flash('flash', 'Deleted file #' . $id);
    }

    private function processFile($id)
    {
        $dnc_file = DncFile::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->whereNull('process_started_at')
            ->firstOrFail();

        $dnc_file->process_started_at = now();
        $dnc_file->save();

        // Dispatch job to run in the background
        ProcessDncFile::dispatch($dnc_file, Auth::user()->id);

        session()->flash('flash', 'Processing file #' . $id);
    }
    private function reverseFile($id)
    {
        $dnc_file = DncFile::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->whereNotNull('processed_at')
            ->whereNull('reverse_started_at')
            ->firstOrFail();

        $dnc_file->reverse_started_at = now();
        $dnc_file->save();

        // Dispatch job to run in the background
        ReverseDncFile::dispatch($dnc_file, Auth::user()->id);

        session()->flash('flash', 'Reversing file #' . $id);
    }
}
