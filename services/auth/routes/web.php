<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version(), 'name'=> 'sudesh'];
});

require __DIR__.'/auth.php';
