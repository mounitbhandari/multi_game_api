<?php

namespace Database\Seeders;

use App\Models\GameType;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GameType::insert([
            ['game_type_name'=>'single','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>9, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>150,'default_payout'=>150],
            ['game_type_name'=>'triple','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>100, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>150,'default_payout'=>150],
            ['game_type_name'=>'12-Card','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>100, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>150,'default_payout'=>150],
            ['game_type_name'=>'16-Card','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>100, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>150,'default_payout'=>150]
        ]);
    }
}
