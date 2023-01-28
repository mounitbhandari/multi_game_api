<?php

namespace App\Console\Commands;

use App\Http\Controllers\CentralController;
use App\Models\DrawMaster;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMissingResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateMissing:result';

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
        //triple chance
        $draw_master = DrawMaster::whereActive(1)->whereGameId(1)->first();
        $min_draw = Carbon::parse($draw_master->end_time)->minute;
        $day_draw = Carbon::parse($draw_master->end_time)->day;
        $hour_draw = Carbon::parse($draw_master->end_time)->hour;
        $min_now = Carbon::now()->minute ;
        $day_now = Carbon::now()->day ;
        $hour_now = Carbon::now()->hour ;
        if(($day_draw === $day_now) && (($min_draw<=$min_now) || ($hour_draw<$hour_now)) &&  (($min_now % $draw_master->time_diff) != 0)){
            $centralControllerObj = new CentralController();
            $ret = $centralControllerObj->createResult(1,1);
        }
    }
}
