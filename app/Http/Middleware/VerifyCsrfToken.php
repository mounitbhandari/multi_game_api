<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'http://all.kfatafat.com/multi_game_api/public/api/*',
        'https://all.kfatafat.com/multi_game_api/public/api/*'
    ];
}
