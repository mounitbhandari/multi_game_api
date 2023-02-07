<?php

namespace App\Console\Commands;

use App\Http\Controllers\CentralController;
use Illuminate\Console\Command;

class generateRolletResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateRollet:result';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        sleep(29);
        $centralControllerObj = new CentralController();
        $ret = $centralControllerObj->createResult(6,1);
    }
}
