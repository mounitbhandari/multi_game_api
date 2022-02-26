<?php

namespace Database\Seeders;

use App\Models\CardCombination;
use Illuminate\Database\Seeder;

class CardCombinationSeeder extends Seeder
{
    public function run()
    {
        CardCombination::insert([
//            12-Card
            ['rank_name'=>'Jack','suit_name'=>'Club','rank_initial'=>'J', 'card_combination_type_id' => 1],
            ['rank_name'=>'Jack','suit_name'=>'Diamon','rank_initial'=>'J', 'card_combination_type_id' => 1],
            ['rank_name'=>'Jack','suit_name'=>'Heart','rank_initial'=>'J', 'card_combination_type_id' => 1],
            ['rank_name'=>'Jack','suit_name'=>'Spade','rank_initial'=>'J', 'card_combination_type_id' => 1],

            ['rank_name'=>'Queen','suit_name'=>'Club','rank_initial'=>'Q', 'card_combination_type_id' => 1],
            ['rank_name'=>'Queen','suit_name'=>'Diamon','rank_initial'=>'Q', 'card_combination_type_id' => 1],
            ['rank_name'=>'Queen','suit_name'=>'Heart','rank_initial'=>'Q', 'card_combination_type_id' => 1],
            ['rank_name'=>'Queen','suit_name'=>'Spade','rank_initial'=>'Q', 'card_combination_type_id' => 1],

            ['rank_name'=>'King','suit_name'=>'Club','rank_initial'=>'K', 'card_combination_type_id' => 1],
            ['rank_name'=>'King','suit_name'=>'Diamon','rank_initial'=>'K', 'card_combination_type_id' => 1],
            ['rank_name'=>'King','suit_name'=>'Heart','rank_initial'=>'K', 'card_combination_type_id' => 1],
            ['rank_name'=>'King','suit_name'=>'Spade','rank_initial'=>'K', 'card_combination_type_id' => 1],




//            16-Card
            ['rank_name'=>'Jack','suit_name'=>'Club','rank_initial'=>'J', 'card_combination_type_id' => 2],
            ['rank_name'=>'Jack','suit_name'=>'Diamond','rank_initial'=>'J', 'card_combination_type_id' => 2],
            ['rank_name'=>'Jack','suit_name'=>'Heart','rank_initial'=>'J', 'card_combination_type_id' => 2],
            ['rank_name'=>'Jack','suit_name'=>'Spade','rank_initial'=>'J', 'card_combination_type_id' => 2],

            ['rank_name'=>'Queen','suit_name'=>'Club','rank_initial'=>'Q', 'card_combination_type_id' => 2],
            ['rank_name'=>'Queen','suit_name'=>'Diamond','rank_initial'=>'Q', 'card_combination_type_id' => 2],
            ['rank_name'=>'Queen','suit_name'=>'Heart','rank_initial'=>'Q', 'card_combination_type_id' => 2],
            ['rank_name'=>'Queen','suit_name'=>'Spade','rank_initial'=>'Q', 'card_combination_type_id' => 2],

            ['rank_name'=>'King','suit_name'=>'Club','rank_initial'=>'K', 'card_combination_type_id' => 2],
            ['rank_name'=>'King','suit_name'=>'Diamond','rank_initial'=>'K', 'card_combination_type_id' => 2],
            ['rank_name'=>'King','suit_name'=>'Heart','rank_initial'=>'K', 'card_combination_type_id' => 2],
            ['rank_name'=>'King','suit_name'=>'Spade','rank_initial'=>'K', 'card_combination_type_id' => 2],

            ['rank_name'=>'King','suit_name'=>'Club','rank_initial'=>'A', 'card_combination_type_id' => 2],
            ['rank_name'=>'King','suit_name'=>'Diamond','rank_initial'=>'A', 'card_combination_type_id' => 2],
            ['rank_name'=>'King','suit_name'=>'Heart','rank_initial'=>'A', 'card_combination_type_id' => 2],
            ['rank_name'=>'King','suit_name'=>'Spade','rank_initial'=>'A', 'card_combination_type_id' => 2],

        ]);
    }
}
