<?php
//Profile
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::get('dark-mode-change', [ProfileController::class, 'darkModeChange'])->name('dark_mode_change');
Route::post('profile-edit', [ProfileController::class, 'editPost'])->name('profile.edit_post');
Route::get('password-change', [ProfileController::class, 'passwordEdit'])->name('profile.password_change');
Route::post('password-change', [ProfileController::class, 'passwordUpdate']);
