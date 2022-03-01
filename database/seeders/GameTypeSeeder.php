<?php

namespace Database\Seeders;

use App\Models\GameType;
use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    public function run()
    {
        GameType::insert([
            ['game_type_name'=>'single','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>9, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>100,'default_payout'=>100],
            ['game_type_name'=>'triple','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>900, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>100,'default_payout'=>100],
            ['game_type_name'=>'12-Card','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>100, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>100,'default_payout'=>100],
            ['game_type_name'=>'16-Card','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>100, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>100,'default_payout'=>100],
            ['game_type_name'=>'double','game_type_initial' => '' ,'mrp'=> 1.00, 'winning_price'=>90, 'winning_bonus_percent'=>0.2, 'commission'=>0.00, 'payout'=>100,'default_payout'=>100]
        ]);
    }
}
