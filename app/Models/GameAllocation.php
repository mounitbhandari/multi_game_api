<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameAllocation extends Model
{
    use HasFactory;

    protected $hidden = [
        "inforce","created_at","updated_at",
    ];
}