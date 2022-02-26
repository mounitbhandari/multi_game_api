<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run()
    {
        Game::insert([
            ['game_name'=> 'FATAFAT'],
            ['game_name'=> 'SHIRDI'],
            ['game_name'=> 'MUMBAI MAIN BAZAR',],
            ['game_name'=> 'KALYAN MATKA'],
        ]);
    }
}
