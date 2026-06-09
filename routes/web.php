<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Deskly's pages are file-based via Laravel Folio (resources/views/pages).
| Package routes (auth, billing, changelog, notifications, foundation) are
| registered by their own service providers. Add only bespoke routes here.
|
*/

if (app()->environment('local')) {
    Route::get('/_demo-login', function () {
        auth()->login(User::where('email', 'demo@devdojo.test')->firstOrFail());

        return redirect('/dashboard');
    });
}
