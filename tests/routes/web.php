<?php

use Illuminate\Support\Facades\Route;

Route::any('/sns', 'Rennokki\LaravelSnsEvents\Tests\Controllers\SnsController@handle')
    ->name('sns');

Route::any('/sns-custom', 'Rennokki\LaravelSnsEvents\Tests\Controllers\CustomSnsController@handle')
    ->name('custom-sns');
