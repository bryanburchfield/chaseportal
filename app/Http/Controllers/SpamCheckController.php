<?php

namespace App\Http\Controllers;

use App\Imports\SpamImportNoHeaders;
use App\Imports\SpamImportWithHeaders;
use App\Models\SpamCheckBatch;
use App\Models\SpamCheckBatchDetail;
use App\Services\SpamCheckService;
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
            $file->recs = $file->spamCheckBatchDetails->count();
            $file->errors = $file->errorRecs()->count();
            $file->flags = $file->flaggedRecs()->count();

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

    public function showRecords(Request $request)
    {
        $file = SpamCheckBatch::findOrFail($request->id);

        $page['sidenav'] = 'admin';
        $page['menuitem'] = 'spam_check';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $file,
            'records' => $file->spamCheckBatchDetails()->paginate(50),
        ];

        return view('admin.spam_check_records')->with($data);
    }

    public function showErrors(Request $request)
    {
        $file = SpamCheckBatch::findOrFail($request->id);

        $page['sidenav'] = 'admin';
        $page['menuitem'] = 'spam_check';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $file,
            'records' => $file->errorRecs()->paginate(50),
        ];

        return view('admin.spam_check_records')->with($data);
    }

    public function showFlags(Request $request)
    {
        $file = SpamCheckBatch::findOrFail($request->id);

        $page['sidenav'] = 'admin';
        $page['menuitem'] = 'spam_check';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $file,
            'records' => $file->flaggedRecs()->paginate(50),
        ];

        return view('admin.spam_check_records')->with($data);
    }

    public function submitNumber(Request $request)
    {
        $validatedData = $request->validate([
            'phone' => 'required',
        ]);

        // create batch of 1 record
        $spam_check_batch = SpamCheckBatch::create([
            'user_id' => Auth::user()->id,
            'description' => 'Check ' . $validatedData['phone'],
            'uploaded_at' => now(),
        ]);

        $spam_check_batch_detail = SpamCheckBatchDetail::create([
            'spam_check_batch_id' => $spam_check_batch->id,
            'line' => 1,
            'phone' => $validatedData['phone'],
        ]);

        // error check batch
        $this->errorCheckBatch($spam_check_batch);

        // process batch
        $this->processBatch($spam_check_batch);

        $spam_check_batch->refresh();

        return redirect()->action("SpamCheckController@showRecords", ["id" => $spam_check_batch->id]);
    }

    public function errorCheckBatch(SpamCheckBatch $spamCheckBatch)
    {
        foreach ($spamCheckBatch->spamCheckBatchDetails->all() as $detail) {

            if ($detail->succeeded === null) {
                if (!$this->validPhone($detail->phone)) {
                    $detail->error = 'Invalid phone number';
                }
                $detail->succeeded = empty($detail->error);

                $detail->save();
            }
        }
    }

    public function validPhone($phone)
    {
        // Strip non-digits
        $phone = preg_replace("/[^0-9]/", '', $phone);

        // should now be either 10 digits without a leading '1', or 11 digits with
        if (strlen($phone) == 10 && substr($phone, 0, 1) != '1') {
            return true;
        }

        if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
            return true;
        }

        return false;
    }

    public function processBatch(SpamCheckBatch $spamCheckBatch)
    {
        $spamCheckBatch->process_started_at = now();
        $spamCheckBatch->save();

        $spamCheckService = new SpamCheckService();

        foreach ($spamCheckBatch->spamCheckBatchDetails->all() as $detail) {
            if ($detail->succeeded && !$detail->checked) {
                $detail->flags = $spamCheckService->checkNumber($detail->phone);
                $detail->flagged = !empty($detail->flags);
                $detail->checked = 1;

                $detail->save();
            }
        }

        $spamCheckBatch->processed_at = now();
        $spamCheckBatch->save();
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
            // 'filename' => $request->file('spamcheckfile')->getClientOriginalName(),
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

        Excel::import($importer, $request->file('spamcheckfile'));

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
