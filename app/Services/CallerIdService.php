<?php

namespace App\Services;

use App\Mail\CallerIdMail;
use App\Models\DailyPhoneFlag;
use App\Models\Dialer;
use App\Models\OwnedDid;
use App\Models\PhoneFlag;
use App\Models\PhoneReswap;
use App\Traits\PhoneTraits;
use App\Traits\SqlServerTraits;
use App\Traits\TimeTraits;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CallerIdService
{
    use SqlServerTraits;
    use TimeTraits;
    use PhoneTraits;

    private $spamCheckService;
    private $didSwapService;

    private $run_date;

    private $startdate;
    private $enddate;
    private $yesterday;
    private $maxcount;

    private $supported_dialers = [7, 9, 24, 26];

    // Groups to exclude
    private $ignoreGroups =
    [
        224195,
        236163,
        2256969,
    ];

    // Non-flagged swap params
    // private $swap_dials = 1000;
    // private $swap_connectpct = 10.8;

    // Don't swap if flagged and connect pct at least
    private $dont_swap_connectpct = 15.0;

    public static function execute()
    {
        $caller_id_service = new CallerIdService();
        $caller_id_service->runReport();
    }

    private function initialize()
    {
        $this->run_date = now();

        $this->enddate = Carbon::parse('midnight');
        $this->yesterday = $this->enddate->copy()->subDay(1);
        $this->maxcount = 0;

        $this->spamCheckService = new SpamCheckService();
        $this->didSwapService = new DidSwapService();
    }

    public function runReport()
    {
        Log::info('Starting CallerID report');

        $this->initialize();

        echo "Saving owned counts\n";
        Log::info('Saving owned counts');
        $this->saveOwnedCounts();

        echo "Pulling 1 day report\n";
        Log::info('Pulling 1 day report');
        $this->startdate = $this->yesterday;
        $this->save1DayReport();

        echo "Pulling 30 day report\n";
        Log::info('Pulling 30 day report');
        $this->startdate = $this->enddate->copy()->subDay(30);
        $this->save30DayReport();

        echo "Checking flags\n";
        Log::info('Checking flags');
        $this->checkFlags();

        echo "Swap Numbers\n";
        Log::info('Swapping numbers');
        $this->swapNumbers();

        echo "Check and re-swap numbers\n";
        Log::info('Check and Re-swap Numbers');
        $this->checkSwapped();

        echo "Creating report\n";
        Log::info('Creating report');
        $this->createReport();

        echo "Finished\n";
        Log::info('Finished');
    }

    private function saveOwnedCounts()
    {
        // Make list of groups to ignore
        $ignoreGroups = implode(',', $this->ignoreGroups);

        $sql = "SET NOCOUNT ON;
        SELECT GroupId, GroupName, SUM(OwnedDidCount) AS OwnedDidCount FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {

            $sql .= " $union SELECT I.GroupId, G.GroupName, COUNT (DISTINCT I.InboundSource) AS OwnedDidCount 
                FROM [" . $dialer->reporting_db . "].[dbo].[InboundSources] I
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[Groups] G ON G.GroupId = I.GroupId AND G.IsActive = 1
                WHERE I.GroupId NOT IN ($ignoreGroups)
                GROUP BY I.GroupId, G.GroupName";

            $union = 'UNION';
        }

        $sql .= ") tmp
        GROUP BY GroupId, GroupName";

        $results = $this->runSql($sql);

        if (count($results)) {
            foreach ($results as $rec) {
                try {
                    OwnedDid::create([
                        'run_date' => $this->run_date,
                        'group_id' => $rec['GroupId'],
                        'group_name' => $rec['GroupName'],
                        'owned_did_count' => $rec['OwnedDidCount'],
                    ]);
                } catch (Exception $e) {
                    Log::error('Could not create OwnedDid: ' . $e->getMessage());
                }
            }
        }
    }

    private function createReport()
    {
        // big report
        $all_results = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->orderBy('phone')
            ->get() as $rec) {
            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        $mainCsv = $this->makeCsv($all_results);

        // autoswap report
        $all_results = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('owned', 1)
            ->whereIn('dialer_numb', $this->supported_dialers)
            ->where(function ($query) {
                $query->where('ring_group', 'ilike', '%caller%id%call%back%')
                    ->orWhere('ring_group', 'ilike', '%nationwide%');
            })
            ->where(function ($query) {
                $query
                    ->where(function ($query2) {
                        $query2
                            ->where('flagged', 1)
                            ->where('connect_ratio', '<', $this->dont_swap_connectpct);
                    });
                // ->orWhere(function ($query2) {
                //     $query2
                //         ->where('calls', '>=', $this->swap_dials)
                //         ->where('connect_ratio', '<', $this->swap_connectpct);
                // });
            })
            ->orderBy('dialer_numb')
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        $autoswapCsv = $this->makeCsv($all_results);

        // manual swap report
        $all_results = [];

        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('owned', 1)
            ->whereNotIn('dialer_numb', $this->supported_dialers)
            ->where(function ($query) {
                $query->where('ring_group', 'ilike', '%caller%id%call%back%')
                    ->orWhere('ring_group', 'ilike', '%nationwide%');
            })
            ->where(function ($query) {
                $query
                    ->where(function ($query2) {
                        $query2
                            ->where('flagged', 1)
                            ->where('connect_ratio', '<', $this->dont_swap_connectpct);
                    });
                // ->orWhere(function ($query2) {
                //     $query2
                //         ->where('calls', '>=', $this->swap_dials)
                //         ->where('connect_ratio', '<', $this->swap_connectpct);
                // });
            })
            ->orderBy('dialer_numb')
            ->orderBy('group_name')
            ->orderBy('calls', 'desc')
            ->orderBy('phone')
            ->get() as $rec) {

            $rec['connect_ratio'] = round($rec['connect_ratio'], 2) . '%';

            $all_results[] = $rec;
        }

        $manualswapCsv = $this->makeCsv($all_results);

        $this->emailReport($mainCsv, $autoswapCsv, $manualswapCsv);
    }

    private function save1DayReport()
    {
        // Save results to database
        foreach ($this->runQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }

            $phone = $this->formatPhoneElevenDigits($rec['CallerId']);

            try {
                DailyPhoneFlag::create([
                    'call_date' => $this->yesterday,
                    'group_id' => $rec['GroupId'],
                    'group_name' => $rec['GroupName'],
                    'dialer_numb' => $rec['DialerNumb'],
                    'phone' => $phone,
                    'ring_group' => $rec['RingGroup'],
                    'owned' => $rec['Owned'],
                    'callerid_check' => $rec['CallerIdCheck'],
                    'calls' => $rec['Dials'],
                    'connects' => $rec['Connects'],
                    'connect_ratio' => $rec['Connects'] / $rec['Dials'] * 100,
                ]);
            } catch (Exception $e) {
                Log::error('Error creating PhoneFlag: ' . $phone);
                Log::critical($e->getMessage());
            }
        }
    }

    private function save30DayReport()
    {
        // Save results to database
        foreach ($this->runQuery() as $rec) {
            if (count($rec) == 0) {
                continue;
            }

            $phone = $this->formatPhoneElevenDigits($rec['CallerId']);

            try {
                PhoneFlag::create([
                    'run_date' => $this->run_date,
                    'group_id' => $rec['GroupId'],
                    'group_name' => $rec['GroupName'],
                    'dialer_numb' => $rec['DialerNumb'],
                    'phone' => $phone,
                    'ring_group' => $rec['RingGroup'],
                    'owned' => $rec['Owned'],
                    'callerid_check' => $rec['CallerIdCheck'],
                    'calls' => $rec['Dials'],
                    'connects' => $rec['Connects'],
                    'connect_ratio' => $rec['Connects'] / $rec['Dials'] * 100,
                ]);
            } catch (Exception $e) {
                Log::error('Error creating PhoneFlag: ' . $phone);
                Log::critical($e->getMessage());
            }
        }
    }

    private function runQuery()
    {
        // Have to hard-code what's considered 'system' for connect calculations
        $system_codes = "
'CR_BAD_NUMBER',
'CR_BUSY',
'CR_CEPT',
'CR_CNCT/CON_CAD',
'CR_CNCT/CON_PAMD',
'CR_CNCT/CON_PVD',
'CR_DISCONNECTED',
'CR_DROPPED',
'CR_ERROR',
'CR_FAILED',
'CR_FAXTONE',
'CR_HANGUP',
'CR_NOANS',
'CR_NORB',
'CR_UNFINISHED',
'Inbound Transfer',
'Inbound Voicemail',
'PARKED',
'SYS_CALLBACK',
'Skip',
'TRANSFERRED',
'UNFINISHED',
'UNKNOWN'
        ";

        // Make list of groups to ignore
        $ignoreGroups = implode(',', $this->ignoreGroups);

        $bind = [];
        $bind['maxcount'] = $this->maxcount;

        $sql = "SET NOCOUNT ON;
        SELECT DISTINCT Phone, CallerIdCheck INTO #phones FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {

            $sql .= " $union SELECT O.phone, MAX(CAST(CallerIdCheck as INT)) as CallerIdCheck
                FROM [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O
                INNER JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] S on S.InboundSource = O.phone
                WHERE O.Active = 1
                AND O.GroupId NOT IN ($ignoreGroups)
                GROUP BY Phone";

            $union = 'UNION';
        }

        $sql .= ") tmp
        
        SELECT GroupId, GroupName, DialerNumb, CallerId, RingGroup, CallerIdCheck, Owned, SUM(cnt) as Dials, SUM(Connects) as Connects FROM (";

        $union = '';
        foreach (Dialer::all() as $i => $dialer) {
            // foreach (Dialer::where('dialer_numb', 7)->get() as $i => $dialer) {

            $bind['startdate' . $i] = $this->startdate->toDateTimeString();
            $bind['enddate' . $i] = $this->enddate->toDateTimeString();
            $bind['inner_maxcount' . $i] = $this->maxcount;

            $sql .= " $union SELECT
                DR.GroupId,
                G.GroupName, " .
                $dialer->dialer_numb . " as DialerNumb,
                DR.CallerId,
                I.Description as RingGroup,
                TP.CallerIdCheck,
                'Owned' = CASE WHEN TP.Phone IS NOT NULL THEN 1 ELSE 0 END,
                'cnt' = COUNT(*),
                SUM(CASE WHEN DR.CallStatus NOT IN ($system_codes) THEN 1 ELSE 0 END) as Connects
            FROM [" . $dialer->reporting_db . "].[dbo].[DialingResults] DR
            INNER JOIN [" . $dialer->reporting_db . "].[dbo].[Groups] G on G.GroupId = DR.GroupId AND G.IsActive = 1
            LEFT JOIN #phones TP ON TP.Phone = DR.CallerId
            LEFT JOIN [" . $dialer->reporting_db . "].[dbo].[InboundSources] I ON I.GroupId = DR.GroupId AND I.InboundSource = DR.CallerId
            CROSS APPLY (
                SELECT TOP 1 O.Active
                FROM [" . $dialer->reporting_db . "].[dbo].[OwnedNumbers] O
                WHERE O.GroupId = DR.GroupId AND O.Phone = DR.CallerId
                ORDER BY O.Active DESC) as O
            WHERE DR.CallDate >= :startdate$i AND DR.CallDate < :enddate$i
            AND DR.CallerId != ''
            AND DR.CallType IN (0,2)
            AND DR.CallStatus NOT IN ('CR_CNCT/CON_CAD','CR_CNCT/CON_PVD')
            AND DR.GroupId NOT IN ($ignoreGroups)
            AND O.Active = 1
            GROUP BY DR.GroupId, GroupName, CallerId, I.Description, TP.CallerIdCheck, I.InboundSource, O.Active, TP.Phone
            HAVING COUNT(*) >= :inner_maxcount$i
                ";

            $union = 'UNION';
        }

        $sql .= ") tmp
            GROUP BY GroupId, GroupName, DialerNumb, CallerId, RingGroup, CallerIdCheck, Owned
            HAVING SUM(cnt) >= :maxcount";

        return $this->runSql($sql, $bind);
    }

    private function makeCsv($results)
    {
        $headers = [
            'Dialer',
            'GroupName',
            'GroupID',
            'CallerID',
            // 'CallerIdCheck',
            'RingGroup',
            'Dials in Last 30 Days',
            'Connect Ratio',
            'Owned',
            'Flagged',
            // 'Flags',
            'Replaced By',
            'Error',
        ];

        // write to file
        $tempfile = tempnam("/tmp", "CID");
        $handle = fopen($tempfile, "w");

        fputcsv($handle, $headers);

        foreach ($results as $rec) {
            $row = [
                $rec->dialer_numb,
                $rec->group_name,
                $rec->group_id,
                $rec->phone,
                // $rec->callerid_check,
                $rec->ring_group,
                $rec->calls,
                $rec->connect_ratio,
                $rec->owned,
                $rec->flagged,
                // $rec->flags,
                $rec->replaced_by,
                $rec->swap_error,
            ];

            fputcsv($handle, $row);
        }

        fclose($handle);

        return $tempfile;
    }

    private function emailReport($mainCsv, $autoswapCsv, $manualswapCsv)
    {
        // read files into variables, then delete files
        $mainData = file_get_contents($mainCsv);
        $autoswapData = file_get_contents($autoswapCsv);
        $manualswapData = file_get_contents($manualswapCsv);

        unlink($mainCsv);
        unlink($autoswapCsv);
        unlink($manualswapCsv);

        $to = 'jonathan.gryczka@chasedatacorp.com';
        $cc = [
            'g.sandoval@chasedatacorp.com',
            'brandon.b@chasedatacorp.com',
            'ahmed@chasedatacorp.com',
            'dylan.farley@chasedatacorp.com'
        ];

        // email report
        $message = [
            'subject' => 'Caller ID Report',
            'mainCsv' => base64_encode($mainData),
            'autoswapCsv' => base64_encode($autoswapData),
            'manualswapCsv' => base64_encode($manualswapData),
            'url' => url('/') . '/',
            'startdate' => $this->startdate->toFormattedDateString(),
            'enddate' => $this->enddate->toFormattedDateString(),
            'maxcount' => $this->maxcount,
        ];

        Mail::to($to)
            ->cc($cc)
            ->bcc('bryan.burchfield@chasedatacorp.com')
            ->send(new CallerIdMail($message));
    }

    private function checkFlags()
    {
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('checked', 0)
            // ->where('callerid_check', 1)     uncomment when this is working
            ->select('phone')->distinct()
            ->orderBy('phone')
            ->get() as $rec) {

            $phone = $this->formatPhoneElevenDigits($rec['phone']);

            echo "check $phone\n";

            $flags = $this->spamCheckService->checkNumber($phone);
            $flagged = !empty($flags);

            // update all matching numbers
            PhoneFlag::where('run_date', $this->run_date)
                ->where('phone', $phone)
                ->update([
                    'checked' => 1,
                    'flags' => $flags,
                    'flagged' => $flagged
                ]);

            DailyPhoneFlag::where('call_date', $this->yesterday)
                ->where('phone', $phone)
                ->update([
                    'checked' => 1,
                    'flags' => $flags,
                    'flagged' => $flagged
                ]);
        }
    }

    private function swapNumbers()
    {
        // read results from db
        foreach (PhoneFlag::where('run_date', $this->run_date)
            ->where('owned', 1)
            ->whereIn('dialer_numb', $this->supported_dialers)
            ->whereNull('replaced_by')
            ->where(function ($query) {
                $query->where('ring_group', 'ilike', '%caller%id%call%back%')
                    ->orWhere('ring_group', 'ilike', '%nationwide%');
            })
            ->where(function ($query) {
                $query
                    ->where(function ($query2) {
                        $query2
                            ->where('flagged', 1)
                            ->where('connect_ratio', '<', $this->dont_swap_connectpct);
                    });
                // ->orWhere(function ($query2) {
                //     $query2
                //         ->where('calls', '>=', $this->swap_dials)
                //         ->where('connect_ratio', '<', $this->swap_connectpct);
                // });
            })
            ->orderBy('dialer_numb')
            ->orderBy('phone')
            ->get() as $rec) {

            list($rec->replaced_by, $rec->swap_error) = $this->didSwapService->swapNumber($rec->phone, $rec->dialer_numb, $rec->group_id);
            $rec->save();

            try {
                DailyPhoneFlag::where('call_date', $this->yesterday)
                    ->where('phone', $rec->phone)
                    ->where('dialer_numb', $rec->dialer_numb)
                    ->where('group_id', $rec->group_id)
                    ->where('ring_group', $rec->ring_group)
                    ->update([
                        'replaced_by' => $rec->replaced_by,
                        'swap_error' => $rec->swap_error,
                    ]);
            } catch (Exception $e) {
                Log::error('Update DailyPhoneFlag Failed: ' . $e->getMessage());
            }
        }
    }

    private function checkSwapped()
    {
        $client = new Client();

        // make a list of new numbers
        $replacements = PhoneFlag::where('run_date', $this->run_date)
            ->whereNotNull('replaced_by')
            ->orderBy('replaced_by')
            ->get();

        Log::info('Swapped: ' . $replacements->count());

        $attempt = 0;

        while ($attempt < 3) {
            $attempt++;

            // Spam check them
            $replacements->transform(function ($item, $key) {
                $item->new_flags = $this->spamCheckService->checkNumber($item->replaced_by);
                return $item;
            });

            // Filter spammy numbers
            $replacements = $replacements->filter(function ($item, $key) {
                return !empty($item->new_flags);
            });

            Log::info('Swapped spammy: ' . $replacements->count());

            // Replace them
            $replacements->transform(function ($item, $key) use ($attempt) {

                list($replaced_by, $swap_error) = $this->didSwapService->swapNumber($item->replaced_by, $item->dialer_numb, $item->group_id);

                // save to db
                try {
                    PhoneReswap::create([
                        'run_date' => $this->run_date,
                        'group_id' => $item->group_id,
                        'dialer_numb' => $item->dialer_numb,
                        'phone' => $item->phone,
                        'replaced_by' => $item->replaced_by,
                        'replaced_again' => $replaced_by,
                        'attempt' => $attempt,
                        'flags' => $item->new_flags,
                    ]);
                } catch (Exception $e) {
                    Log::error('Could not crate phone_reswap: ' . $item->id . ' ' . $e->getMessage());
                }

                $item->new_flags = null;
                $item->replaced_by = $replaced_by;
                $item->swap_error = $swap_error;

                // update phone_flags
                try {
                    PhoneFlag::where('id', $item->id)
                        ->update([
                            'replaced_by' => $item->replaced_by,
                            'swap_error' => $item->swap_error,
                        ]);
                } catch (Exception $e) {
                    Log::error('Could not update phone_flags: ' . $item->id . ' ' . $e->getMessage());
                }

                // update daily_phone_flags
                try {
                    DailyPhoneFlag::where('call_date', $this->yesterday)
                        ->where('phone', $item->phone)
                        ->where('dialer_numb', $item->dialer_numb)
                        ->where('group_id', $item->group_id)
                        ->where('ring_group', $item->ring_group)
                        ->update([
                            'replaced_by' => $item->replaced_by,
                            'swap_error' => $item->swap_error,
                        ]);
                } catch (Exception $e) {
                    Log::error('Could not update daily_phone_flags: ' . $item->id . ' ' . $e->getMessage());
                }

                return $item;
            });
        }
    }
}
