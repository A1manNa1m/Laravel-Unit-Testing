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



// Route::get('/token-test', function () {
//     $user = User::first();

//     if (!$user) {
//         return 'No user found.';
//     }

//     dd([
//         'class' => get_class($user),
//         'traits' => class_uses($user),
//         'methods' => get_class_methods($user),
//         'has_createToken' => method_exists($user, 'createToken'),
//     ]);
// });


// Route::get('/setup', function () {
//     $credentials = [
//         'email' => 'admin@admin.com',
//         'password' => 'password'
//     ];

//     // Ensure user exists
//     $user = User::firstOrCreate(
//         ['email' => $credentials['email']],
//         [
//             'name' => 'Admin',
//             'password' => Hash::make($credentials['password'])
//         ]
//     );

//     // Attempt login
//     if (!Auth::attempt($credentials)) {
//         return response()->json(['error' => 'Login failed'], 401);
//     }

//     $user = Auth::user();

//     return [
//         'admin' => $user->createToken('admin-token', ['create', 'update', 'delete'])->plainTextToken,
//         'update' => $user->createToken('update-token', ['create', 'update'])->plainTextToken,
//         'basic' => $user->createToken('basic-token')->plainTextToken,
//     ];
// });