<?php

use Illuminate\Http\Request;
use App\Models\Test;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/getOnePageHouse','Test@getOnePageHouse');
Route::get('/getOnePageHouseMultiThread','Test@getOnePageHouseMultiThread');
Route::get('/getOnePageHouseTest','Test@getOnePageHouseTest');

Route::get('/setShHouse','Test@setShHouse');
Route::get('/getShList','Test@getShList');
Route::get('/test','Test@test');