<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run()
    {
        Game::insert([
            ['game_name'=> 'SINGLE DOUBLE TRIPLE'],
            ['game_name'=> '12 CARD'],
            ['game_name'=> '16 CARD',],
            ['game_name'=> 'KALYAN MATKA'],
        ]);
    }
}
