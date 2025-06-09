<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminProfileController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('admin/profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('admin.profile.show');
        Route::put('/', [ProfileController::class, 'update'])->name('admin.profile.update');
        Route::get('/skills', [ProfileController::class, 'getSkills'])->name('admin.profile.skills');
        Route::post('/skills', [ProfileController::class, 'addSkill'])->name('admin.profile.skills.add');
        Route::delete('/skills/{skill}', [ProfileController::class, 'removeSkill'])->name('admin.profile.skills.remove');
    });
});

