<?php

namespace App\Console\Commands;

use App\Http\Controllers\LeadsController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class dump_leads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:dump_leads
                            {group_id : Group ID}
                            {tz : Timezone}
                            {db : Reporting database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull and dump leads to an ftp server';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $request = new Request([
            'group_id' => $this->argument('group_id'),
            'tz' => $this->argument('tz'),
            'db' => $this->argument('db'),
        ]);

        $lc = new LeadsController();
        $lc->leadDump($request);
    }
}
