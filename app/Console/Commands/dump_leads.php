<?php

namespace App\Console\Commands;

use App\Services\LeadsService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class dump_leads extends Command
{
     /**
      * The name and signature of the console command.
      *
      * Run from command line (ideally cron)
      * php artisan command:dump_leads {group_id} {timezone} {database}
      * eg: php artisan command:dump_leads 224347 America/New_York PowerV2_Reporting_Dialer-17
      *
      * NOTE: .env must contain the following keys for each client:
      *   FTP_HOST_{group_id}
      *   FTP_USERNAME_{group_id}
      *   FTP_PASSWORD_{group_id}
      *   FTP_EMAIL_{group_id}
      *
      * and config/filesystems.php must contain for each client:
      * 'disks' => [
      *       'ftp_{group_id}' => [
      *           'driver' => 'ftp',
      *           'host' => env('FTP_HOST_{group_id}'),
      *           'username' => env('FTP_USERNAME_{group_id}'),
      *           'password' => env('FTP_PASSWORD_{group_id}'),
      *           'email' => env('FTP_EMAIL_{group_id}'),
      *           'root' => '/',
      *        ],
      *   ]
      *
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

          $lc = new LeadsService();
          $lc->leadDump($request);
     }
}
