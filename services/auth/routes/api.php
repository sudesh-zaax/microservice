<?php

use App\Services\AuthServiceClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/ggg123', function () {
    $authClient = new AuthServiceClient();
    // GET request example
    $response = $authClient->login('/oauth/token',[
        'grant_type' => 'password',
        'client_id' => config('passport.password_access_client.id'),
        'client_secret' => config('passport.password_access_client.secret'),
        'username' => 'test@example.com',
        'password' => 'password',
        'scope' => '',
    ]);
    
    if ($response['success']) {
        return response()->json($response['data']);
    }
    
    return response()->json($response['error'], $response['status']);
});
