<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version(), 'name'=> 'my name is policy_service'];
});

require __DIR__.'/auth.php';
