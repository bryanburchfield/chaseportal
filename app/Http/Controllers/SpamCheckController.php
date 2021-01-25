<?php

namespace App\Http\Controllers;

use App\Models\SpamCheckBatch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SpamCheckController extends Controller
{
    public function index()
    {
        $jsfile[] = '';
        $page['sidenav'] = 'admin';
        $page['menuitem'] = 'spam_check';
        $page['type'] = 'page';
        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
            'files' => $this->paginateCollection($this->getFiles()),
        ];

        return view('admin.spam_check')->with($data);
    }

    private function getFiles()
    {
        $tz = Auth::user()->ianaTz;

        $files = SpamCheckBatch::select(
            'id',
            'description',
            'uploaded_at',
            'process_started_at',
            'processed_at',
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
        }

        return $files;
    }

    private function paginateCollection($collection, $perPage = 50)
    {
        // Get current page from url e.x. &page=1
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        // Slice the collection to get the items to display in current page
        $currentPageItems = $collection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();

        // Create the paginator
        $paginatedItems = new LengthAwarePaginator($currentPageItems, count($collection), $perPage);

        // set url path for generted links
        $paginatedItems->setPath(request()->url());

        return $paginatedItems;
    }

    public function uploadIndex()
    {

        $jsfile[] = '';
        $page['sidenav'] = 'admin';
        $page['menuitem'] = 'spam_check';
        $page['type'] = 'page';
        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
        ];

        return view('admin.spam_check_upload')->with($data);
    }

    public function uploadFile(Request $request)
    {
        if ($request->has('cancel')) {
            return redirect()->action('SpamCheckController@index');
        }

        if ($request->has_headers) {
            $column = 'phone';
        } else {
            $column = 0;
        }

        // We have no control over what files the user throws at us
        // so wrap this in a transaction in case it craps out
        DB::beginTransaction();

        // insert spam_check_batch record
        $spam_check_batch = SpamCheckBatch::create([
            'user_id' => Auth::user()->id,
            'filename' => $request->file('dncfile')->getClientOriginalName(),
            'description' => $request->description,
            'uploaded_at' => now(),
            'processed_at' => null,
        ]);

        // load file
        if ($request->has_headers) {
            $importer = new SpamImportWithHeaders($spam_check_batch->id, $column);
        } else {
            $importer = new SpamImportNoHeaders($spam_check_batch->id, $column);
        }

        Excel::import($importer, $request->file('spamfile'));

        // Commit all the inserts
        DB::commit();

        $spam_check_batch->refresh();

        $tot_recs = $spam_check_batch->spamFileDetails->count();

        if (
            $tot_recs == 0 ||
            $tot_recs == $spam_check_batch->errorRecs()->count()
        ) {
            $spam_check_batch->delete();
            session()->flash('flash', trans('tools.no_valid_phones'));
        } else {
            session()->flash('flash', trans_choice('tools.uploaded_records', $tot_recs));
        }

        return redirect()->action('SpamCheckController@index');
    }
}
