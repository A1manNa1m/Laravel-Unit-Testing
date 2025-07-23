<?php

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/setup', function () {
    $credentials = [
        'email' => 'admin@admin.com',
        'password' => 'password'
    ];

    // Check if user exists
    $user = User::firstOrCreate(
        ['email' => $credentials['email']],
        [
            'name' => 'Admin',
            'password' => Hash::make($credentials['password'])
        ]
    );

    // Log in the user (optional — not needed to generate token)
    Auth::login($user);

    // Now you can safely create tokens directly
    return [
        'admin' => $user->createToken('admin-token')->plainTextToken,
        'update' => $user->createToken('update-token', ['read','create','update'])->plainTextToken,
        'read' => $user->createToken('read-token',['read'])->plainTextToken,
        'basic' => $user->createToken('basic-token',['none'])->plainTextToken,
    ];
});