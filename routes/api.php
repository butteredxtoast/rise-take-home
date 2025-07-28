<?php

use App\Http\Controllers\PresidentsController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/bigquery-health', [PresidentsController::class, 'bigqueryHealth']);

Route::get('/presidents/{date}', [PresidentsController::class, 'getPresidentByDate']);
Route::get('/random', [PresidentsController::class, 'random']);
