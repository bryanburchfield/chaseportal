<?php

namespace App\Http\Controllers;

use App\Http\Requests\DncFile as RequestsDncFile;
use App\Imports\DncImportWithHeaders;
use App\Imports\DncImportNoHeaders;
use App\Jobs\ProcessDncFile;
use App\Jobs\ReverseDncFile;
use App\Models\DncFile;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class DncController extends Controller
{
    /**
     * Index
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     * @throws Exception 
     */
    public function index()
    {
        $jsfile[] = 'dncuploader.js';
        $page['menuitem'] = 'dnc_importer';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
            'files' => $this->paginateCollection($this->getFiles()),
        ];

        return view('tools.dnc_importer')->with($data);
    }

    /**
     * Get Files
     * 
     * @return mixed 
     * @throws Exception 
     */
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
            ->where('group_id', Auth::User()->group_id)
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

        return $files;
    }

    /**
     * Paginate Collection
     * 
     * @param mixed $collection 
     * @param int $perPage 
     * @return Illuminate\Pagination\LengthAwarePaginator 
     */
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

    /**
     * Display form to upload a file
     * 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function uploadIndex()
    {
        $jsfile[] = 'dncuploader.js';
        $page['menuitem'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'jsfile' => $jsfile,
            'page' => $page,
        ];

        return view('tools.dnc_upload')->with($data);
    }

    /**
     * Handle upload file form submission
     * 
     * @param Request $request 
     * @return Illuminate\Http\RedirectResponse 
     * @throws InvalidArgumentException 
     * @throws UrlGenerationException 
     */
    public function uploadFile(RequestsDncFile $request)
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
            'filename' => $request->file('dncfile')->getClientOriginalName(),
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

        Excel::import($importer, $request->file('dncfile'));

        // Commit all the inserts
        DB::commit();

        $dnc_file->refresh();

        $tot_recs = $dnc_file->dncFileDetails->count();

        if (
            $tot_recs == 0 ||
            $tot_recs == $dnc_file->errorRecs()->count()
        ) {
            $dnc_file->delete();
            session()->flash('flash', trans('tools.no_valid_phones'));
        } else {
            session()->flash('flash', trans_choice('tools.uploaded_records', $tot_recs));
        }

        return redirect()->action('DncController@index');
    }

    /**
     * Handle actions from DNC files listing
     * 
     * @param Request $request 
     * @return Illuminate\Http\RedirectResponse 
     * @throws HttpResponseException 
     * @throws InvalidArgumentException 
     * @throws UrlGenerationException 
     */
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

    /**
     * Show Errors in DNC file
     * 
     * @param Request $request 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function showErrors(Request $request)
    {
        $dnc_file = DncFile::where('id', $request->id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();

        $page['menuitem'] = 'dnc_importer';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $dnc_file,
            'records' => $dnc_file->errorRecs()->paginate(50),
        ];

        return view('tools.dnc_records')->with($data);
    }

    /**
     * Show all records in DNC file
     * 
     * @param Request $request 
     * @return Illuminate\View\View|Illuminate\Contracts\View\Factory 
     */
    public function showRecords(Request $request)
    {
        $dnc_file = DncFile::where('id', $request->id)
            ->where('group_id', Auth::user()->group_id)
            ->firstOrFail();

        $page['menuitem'] = 'dnc_importer';
        $page['sidenav'] = 'tools';
        $page['type'] = 'page';
        $data = [
            'page' => $page,
            'file' => $dnc_file,
            'records' => $dnc_file->dncFileDetails()->paginate(50),
        ];

        return view('tools.dnc_records')->with($data);
    }

    /**
     * Delete a file if not processed
     * 
     * @param mixed $id 
     * @return void 
     */
    private function deleteFile($id)
    {
        $dnc_file = DncFile::where('id', $id)
            ->where('group_id', Auth::user()->group_id)
            ->whereNull('process_started_at')
            ->firstOrFail();

        $dnc_file->delete();

        session()->flash('flash', trans('tools.delete_file_numb') . $id);
    }

    /**
     * Process DNC File
     * 
     * @param mixed $id 
     * @return void 
     */
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

        session()->flash('flash', trans('tools.processing_file_numb') . $id);
    }

    /**
     * Reverse a processed DNC file
     * 
     * @param mixed $id 
     * @return void 
     */
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

        session()->flash('flash', trans('tools.reversing_file_numb') . $id);
    }
}
