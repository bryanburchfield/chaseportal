<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class convert_users extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:convert_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert users from old app';

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
        include(app_path() . '/scripts/ConvertUsers.php');
    }
}
