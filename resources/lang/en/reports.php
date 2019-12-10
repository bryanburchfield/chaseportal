<?php

return [
	// Email text
	'report'      => 'Report',
	'settings'    => 'Settings',
	'unsubscribe' => 'If you do not wish to receive e-mail messages from Chase Data Corp, please visit the Automated Reports page to change your settings.',

	// Report names
	'agent_activity'                => 'Agent Activity',
	'agent_analysis'                => 'Agent Analysis',
	'agent_pause_time'              => 'Agent Pause Time',
	'agent_summary'                 => 'Agent Summary',
	'agent_summary_campaign'        => 'Agent Summary By Camp',
	'agent_summary_subcampaign'     => 'Agent Summary By Sub',
	'agent_timesheet'               => 'Agent Time Sheet',
	'call_details'                  => 'Call Details',
	'caller_id'                     => 'Caller ID Tracking',
	'campaign_call_log'             => 'Campaign Call Log',
	'campaign_summary'              => 'Campaign Summary',
	'campaign_usage'                => 'Campaign Usage',
	'inbound_summary'               => 'Inbound Summary',
	'lead_inventory'                => 'Lead Inventory',
	'lead_inventory_sub'            => 'Lead Inventory By Sub',
	'missed_calls'                  => 'Missed Calls',
	'production_report'             => 'Production Report',
	'production_report_subcampaign' => 'Production By Sub',
	'shift_report'                  => 'Shift Report',
	'subcampaign_summary'           => 'Subcampaign Summary',

	// Filter fields
	'as_of'              => 'As of',
	'call_statuses'      => 'Call Statuses',
	'call_type'          => 'Call Type',
	'callerid'           => 'Caller ID',
	'campaign'           => 'Campaign',
	'duration_secs'      => 'Duration ( seconds )',
	'end'                => 'End',
	'from'               => 'From',
	'inbound_sources'    => 'Inbound Sources',
	'rep'                => 'Rep',
	'reps'               => 'Reps',
	'run_report'         => 'Run Report',
	'skill'              => 'Skill',
	'start'              => 'Start',
	'subcampaign'        => 'Subcampaign',
	'termination_status' => 'Show only termination status',
	'to'                 => 'To',

	// error messages
	'errcampaignrequired'  => 'Campaign required',
	'errcampaignsrequired' => 'At least 1 Campaign required',
	'errdatabases'         => 'Must select at least 1 Database',
	'errdaterange'         => 'To date must be after From date',
	'errduration'          => 'Invalid Duration values',
	'errfromdateinvalid'   => 'From date not a valid date/time',
	'errfromdaterequired'  => 'From date required',
	'errpagenumb'          => 'Invalid page number',
	'errpagesize'          => 'Invalid page size',
	'errrepsrequired'      => 'At least 1 Rep required',
	'errresults'           => 'No results found',
	'errtodateinvalid'     => 'To date not a valid date/time',
	'errtodaterequired'    => 'To date required',

	// Column headings
	'abandoned'           => 'Abandoned Calls',
	'agent'               => 'Agent Calls',
	'aph'                 => 'S-L-A/HR',
	'attempt'             => 'Attempt',
	'available'           => 'Available',
	'availtimesec'        => 'Time Avail',
	'avattempt'           => 'Avg Attempts',
	'avdispotime'         => 'Avg Wrap Up Time',
	'avholdtime'          => 'Avg Hold Time',
	'avtalktime'          => 'Avg Talk Time',
	'avwaittime'          => 'Avg Wait Time',
	'breakcode'           => 'Break Code',
	'callbacks'           => 'Call Backs',
	'calls'               => 'Calls',
	'calls'               => 'Total Dials',
	'callstatus'          => 'Call Status',
	'calltype'            => 'Call Type',
	'cepts'               => 'Operator Disconnects',
	'cnt'                 => 'Calls',
	'connectedtimesec'    => 'Talk Time',
	'connectpct'          => 'Connect %',
	'connectrate'         => 'Connect Rate',
	'connects'            => 'Connects',
	'contacts'            => 'Contacts',
	'conversionfactor'    => 'Conversion Factor',
	'conversionrate'      => 'Conversion Rate',
	'count'               => 'Count',
	'cph'                 => 'CPH',
	'date'                => 'Date',
	'description'         => 'Description',
	'details'             => 'Details',
	'dialed'              => 'Dialed',
	'dispositiontimesec'  => 'Wrap Up Time',
	'dph'                 => 'Dials per Hr',
	'dropcallspercentage' => 'Drop Rate (Connected Calls)',
	'duration'            => 'Duration',
	'event'               => 'Event',
	'firstname'           => 'First',
	'handledbyivr'        => 'Handled By IVR',
	'handledbyrep'        => 'Handled By Rep',
	'hours'               => 'Hours Worked',
	'lastname'            => 'Last',
	'leads'               => 'Sale/Lead/App',
	'loggedintime'        => 'Logged In Time',
	'loggedintimesec'     => 'Logged In Time',
	'logintime'           => 'LogIn Time',
	'logouttime'          => 'LogOut Time',
	'manhours'            => 'Man Hours',
	'manhoursec'          => 'Man Hours',
	'missedcalls'         => 'Missed Calls',
	'mostrecent'          => 'Most Recent',
	'pausedtime'          => 'Pause Time',
	'pausedtimesec'       => 'Time Paused',
	'pct'                 => 'Percent',
	'phone'               => 'Phone',
	'resultcodes'         => 'Result Codes',
	'saleratevalue'       => 'S-L-A Rate Value',
	'sales'               => 'Sale/Lead/App',
	'source'              => 'Inbound Source',
	'stat'                => 'Status',
	'subcampaign'         => 'Subcampaign',
	'talktimesec'         => 'Talk Time',
	'total'               => 'Total',
	'totalcalls'          => 'Total Calls',
	'totmanhours'         => 'Total Man Hours',
	'totpausedsec'        => 'Total Paused',
	'tries'               => 'Count',
	'type'                => 'Type',
	'unpausedtime'        => 'UnPause Time',
	'voicemail'           => 'Voice Mail',
	'waittimesec'         => 'Wait Time',

	'auto_report_text' => 'Here you can toggle on and off automated reports. Reports are emailed to the address you registered with and will be sent daily at 6:00am EST.',
	'call_vol_per_int'		=> 'Call Volume Per 15 Min Interval',
	'calls_by_caller_ID'	=> 'Calls by Caller ID',
	'count_of_leads_by_attempt' => 'Count of Leads by Attempt',
	'download'               => 'Download'
];