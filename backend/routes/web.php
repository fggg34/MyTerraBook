<?php

use Illuminate\Support\Facades\Route;

// Filament admin lives at /admin (login: /admin/login). Root redirects there for convenience.
Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});
