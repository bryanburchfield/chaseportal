<?php

namespace App\Http\Controllers;

use App\Models\PlaybookRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlaybookHistoryController extends Controller
{
    public function index()
    {
        $page = [
            'menuitem' => 'playbook',
            'menu' => 'tools',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_history.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'history' => $this->getHistory(),
        ];

        return view('tools.playbook.history.index')->with($data);
    }

    private function getHistory()
    {
        return DB::table('playbook_runs')
            ->join('contacts_playbooks', 'contacts_playbooks.id', '=', 'playbook_runs.contacts_playbook_id')
            ->where('contacts_playbooks.group_id', Auth::user()->group_id)
            ->orderBy('playbook_runs.created_at', 'desc')
            ->get();
    }
}
