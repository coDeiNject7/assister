<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;

// Home route
Route::get('/', function () {
    return view('welcome');
});

// Test MongoDB connection route
Route::get('/test-mongo', function () {
    return User::all(); // Returns all users from MongoDB
});

use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Command;

Route::get('/test-mongo', function () {
    $client = DB::connection('mongodb')->getMongoClient();
    $database = config('database.connections.mongodb.database'); // ✅ correct way
    $command = new MongoDB\Driver\Command(['ping' => 1]);
    $result = $client->selectDatabase($database)->command($command);
    return response()->json($result->toArray());
});