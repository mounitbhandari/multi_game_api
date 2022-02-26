<?php

namespace Database\Seeders;

use App\Models\NextGameDraw;
use Illuminate\Database\Seeder;

class NextGameDrawSeeder extends Seeder
{
    public function run()
    {
        NextGameDraw::create(['next_draw_id' => 2, 'last_draw_id' => 1, 'game_id'=>1]);
        NextGameDraw::create(['next_draw_id' => 10, 'last_draw_id' => 9, 'game_id'=>2]);
        NextGameDraw::create(['next_draw_id' => 18, 'last_draw_id' => 17, 'game_id'=>3]);
        NextGameDraw::create(['next_draw_id' => 28, 'last_draw_id' => 27, 'game_id'=>4]);
    }
}
