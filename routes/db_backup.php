<?php

use App\Http\Controllers\DatabaseBackupController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;


Route::resource('database-backups',DatabaseBackupController::class);
Route::get('database-backups-datatable', [DatabaseBackupController::class, 'dataTable'])->name('database-backups.datatable');
