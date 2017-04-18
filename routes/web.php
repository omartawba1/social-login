<?php

Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::get('users/login', 'UsersController@login');
Route::get('users/logout', 'UsersController@logout');
Route::get('users/callback', 'UsersController@callback');
Route::get('users/dashboard', 'UsersController@dashboard')->middleware('auth');
