<?php

namespace App\Http\Controllers;

use App\Models\PlaybookRun;
use App\Models\PlaybookRunTouch;
use App\Models\PlaybookRunTouchAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'history' => $this->getHistory(),
        ];

        return view('tools.playbook.history.index')->with($data);
    }

    public function runIndex(Request $request)
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
            'playbook_run' => $this->findPlaybookRun($request->id),
            'history' => $this->getRunHistory($request->id),
        ];

        return view('tools.playbook.history.run_index')->with($data);
    }

    public function runActionIndex(Request $request)
    {
        $playbook_run_touch_action = $this->findPlaybookRunTouchAction($request->id);

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
            'playbook_run' => $playbook_run_touch_action->playbook_run_touch->playbook_run,
            'playbook_run_touch_action' => $playbook_run_touch_action,
            'details' => $this->getActionDetails($playbook_run_touch_action),
        ];

        return view('tools.playbook.history.run_action_index')->with($data);
    }

    private function findPlaybookRun($id)
    {
        $playbook_run = PlaybookRun::with('contacts_playbook')
            ->findOrFail($id);

        if ($playbook_run->contacts_playbook->group_id != Auth::user()->group_id) {
            abort(404);
        }

        return $playbook_run;
    }

    private function findPlaybookRunTouchAction($id)
    {
        $playbook_run_touch_action = PlaybookRunTouchAction::with('playbook_action', 'playbook_run_touch.playbook_run')
            ->findOrFail($id);

        if ($playbook_run_touch_action->playbook_action->group_id != Auth::user()->group_id) {
            abort(404);
        }

        return $playbook_run_touch_action;
    }

    private function getHistory()
    {
        return DB::table('playbook_runs')
            ->join('contacts_playbooks', 'contacts_playbooks.id', '=', 'playbook_runs.contacts_playbook_id')
            ->where('contacts_playbooks.group_id', Auth::user()->group_id)
            ->select(['playbook_runs.*', 'contacts_playbooks.name'])
            ->orderBy('playbook_runs.created_at', 'desc')
            ->get();
    }

    private function getRunHistory($playbook_run_id)
    {
        $touches = [];
        $i = 0;

        $playbook_run_touches = PlaybookRunTouch::where('playbook_run_id', $playbook_run_id)
            ->with(['playbook_run_touch_actions.playbook_action', 'playbook_touch'])
            ->get();

        foreach ($playbook_run_touches as $playbook_run_touch) {
            foreach ($playbook_run_touch->playbook_run_touch_actions as $playbook_run_touch_action) {
                $i++;
                $touches[$i] = [
                    'id' => $playbook_run_touch_action->id,
                    'touch_name' => $playbook_run_touch->playbook_touch->name,
                    'action_name' => $playbook_run_touch_action->playbook_action->name,
                    'processed_at' => $playbook_run_touch_action->processed_at,
                    'reversed_at' => $playbook_run_touch_action->reversed_at,
                ];
            }
        }

        return $touches;
    }

    private function getActionDetails(PlaybookRunTouchAction $playbook_run_touch_action)
    {
        $details = [];
        foreach ($playbook_run_touch_action->playbook_run_touch_action_details as $playbook_run_touch_action_detail) {
            //
        }

        return $details;
    }
}
