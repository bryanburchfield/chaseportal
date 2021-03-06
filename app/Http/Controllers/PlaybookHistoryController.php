<?php

namespace App\Http\Controllers;

use App\Jobs\ReversePlaybookAction;
use App\Models\PlaybookRun;
use App\Models\PlaybookRunTouch;
use App\Models\PlaybookRunTouchAction;
use App\Traits\SqlServerTraits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaybookHistoryController extends Controller
{
    use SqlServerTraits;

    public function index()
    {
        $page = [
            'menuitem' => 'playbook',
            'sidenav' => 'main',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_history.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', 'https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css'],
            'history' => $this->getHistory(),
        ];

        return view('playbook.history.index')->with($data);
    }

    public function runIndex(PlaybookRun $playbook_run)
    {
        $this->checkPlaybookGroup($playbook_run);

        $page = [
            'menuitem' => 'playbook',
            'sidenav' => 'main',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_history.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', 'https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'playbook_run' => $playbook_run,
            'history' => $this->getRunHistory($playbook_run->id),
        ];

        return view('playbook.history.run_index')->with($data);
    }

    public function runActionIndex(PlaybookRunTouchAction $playbook_run_touch_action)
    {
        $this->checkPlaybookGroup($playbook_run_touch_action->playbook_run_touch->playbook_run);

        $page = [
            'menuitem' => 'playbook',
            'sidenav' => 'main',
            'type' => 'other',
        ];

        $data = [
            'page' => $page,
            'jsfile' => ['playbook_history.js'],
            'cssfile' => ['https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css', 'https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css'],
            'group_id' => Auth::user()->group_id,
            'playbook_run' => $playbook_run_touch_action->playbook_run_touch->playbook_run,
            'playbook_run_touch_action' => $playbook_run_touch_action,
            'details' => $this->getActionDetails($playbook_run_touch_action),
        ];

        return view('playbook.history.run_action_index')->with($data);
    }

    private function checkPlaybookGroup(PlaybookRun $playbook_run)
    {
        if ($playbook_run->contacts_playbook->group_id !== Auth::user()->group_id) {
            abort(403, 'Unauthorized');
        }
    }

    private function getHistory()
    {
        $playbook_runs = PlaybookRun::whereHas('contacts_playbook', function (Builder $query) {
            $query->where('group_id', Auth::user()->group_id);
        })
            ->orderBy('created_at', 'desc')
            ->get();

        return $playbook_runs;
    }

    private function getRunHistory($playbook_run_id)
    {
        $touches = [];

        $playbook_run_touches = PlaybookRunTouch::where('playbook_run_id', $playbook_run_id)
            ->with(['playbook_run_touch_actions.playbook_action', 'playbook_touch'])
            ->get();

        foreach ($playbook_run_touches as $playbook_run_touch) {
            foreach ($playbook_run_touch->playbook_run_touch_actions as $playbook_run_touch_action) {
                $touches[] = [
                    'id' => $playbook_run_touch_action->id,
                    'touch_name' => $playbook_run_touch->playbook_touch->name,
                    'action_name' => $playbook_run_touch_action->playbook_action->name,
                    'action_type' => $playbook_run_touch_action->playbook_action->action_type,
                    'record_count' => $playbook_run_touch_action->playbook_run_touch_action_details->count(),
                    'process_started_at' => $playbook_run_touch_action->process_started_at,
                    'processed_at' => $playbook_run_touch_action->processed_at,
                    'reverse_started_at' => $playbook_run_touch_action->reverse_started_at,
                    'reversed_at' => $playbook_run_touch_action->reversed_at,
                ];
            }
        }

        return $touches;
    }

    private function getActionDetails(PlaybookRunTouchAction $playbook_run_touch_action)
    {
        $leads = [];
        $lead_list = [];

        foreach ($playbook_run_touch_action->playbook_run_touch_action_details as $playbook_run_touch_action_detail) {
            $leads[$playbook_run_touch_action_detail->lead_id] = $playbook_run_touch_action_detail->toArray();
            if (!isset($lead_list[$playbook_run_touch_action_detail->reporting_db])) {
                $lead_list[$playbook_run_touch_action_detail->reporting_db] = '';
            }
            $lead_list[$playbook_run_touch_action_detail->reporting_db] .= ',' . $playbook_run_touch_action_detail->lead_id;
        }

        $sql = '';
        $union = '';
        foreach ($lead_list as $db => $list) {
            $sql .= "$union SELECT * FROM [$db].[dbo].[Leads] L
            WHERE L.id IN (" . substr($list, 1) . ')';

            $union = 'UNION ALL ';
        }

        $results = $this->runSql($sql);

        foreach ($results as $rec) {
            $leads[$rec['id']] += $rec;
        }

        return $leads;
    }

    public function reverseAction(PlaybookRunTouchAction $playbook_run_touch_action)
    {
        $this->checkPlaybookGroup($playbook_run_touch_action->playbook_run_touch->playbook_run);

        if (!empty($playbook_run_touch_action->reverse_started_at)) {
            return ['status' => 'error'];
        }

        $playbook_run_touch_action->reverse_started_at = now();
        $playbook_run_touch_action->save();

        // Dispatch job to run in the background
        ReversePlaybookAction::dispatch($playbook_run_touch_action);

        return ['status' => 'success'];
    }
}
