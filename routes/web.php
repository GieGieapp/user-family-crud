<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserUiController;


Route::resource('users', UserUiController::class)
    ->only(['create','store','show','update','destroy']);
