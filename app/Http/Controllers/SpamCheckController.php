<?php

namespace App\Http\Controllers;

use App\Models\SpamCheckBatch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SpamCheckController extends Controller
{
    public function index()
    {
        $jsfile[] = '';
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
}
