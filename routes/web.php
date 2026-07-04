<?php

use App\Http\Controllers\DoctorController;
use Illuminate\Support\Facades\Route;

Route::get('/doctor/network-aggregates/{id}', [DoctorController::class, 'networkAggregates']);
