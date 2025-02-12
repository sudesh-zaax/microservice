<?php

use Illuminate\Support\Facades\Route;
use App\Services\PolicyServiceClient;

Route::get('/', function () {
    return ['Laravel' => app()->version(), 'name'=> 'auth service sudesh'];
});

require __DIR__.'/auth.php';
// Basic usage example



Route::get('/ggg', function () {
    $policyClient = new PolicyServiceClient();
    
    // GET request example
    $response = $policyClient->getPolicies('');
    
    if ($response['success']) {
        return response()->json($response['data']);
    }
    
    return response()->json($response['error'], $response['status']);
});
