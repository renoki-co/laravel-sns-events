<?php

use Illuminate\Support\Facades\Route;

Route::any('/sns', 'Rennokki\LaravelSnsEvents\Http\Controllers\SnsController@handle')
    ->name('sns');
