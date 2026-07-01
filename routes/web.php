<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DailyPlannerController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Public landing page - redirect to login or dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// All authenticated routes
Route::middleware(['auth'])->group(function () {

    // ──────────────── Dashboard ────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // ──────────────── Profile Management ────────────────
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ──────────────── Task Management ────────────────
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggleStatus'])->name('tasks.toggle');
    Route::get('/tasks/export/pdf', [TaskController::class, 'exportPdf'])->name('tasks.export.pdf');
    Route::get('/tasks/export/excel', [TaskController::class, 'exportExcel'])->name('tasks.export.excel');


    // ──────────────── Daily Planner ────────────────
    Route::get('/planner', [DailyPlannerController::class, 'index'])->name('planner');
    Route::post('/planner/block', [DailyPlannerController::class, 'blockTime'])->name('planner.block');

    // ──────────────── Calendar ────────────────
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/calendar/feed', [CalendarController::class, 'feed'])->name('calendar.feed');
    Route::post('/calendar/reschedule', [CalendarController::class, 'reschedule'])->name('calendar.reschedule');

    // ──────────────── Goals ────────────────
    Route::resource('goals', GoalController::class)->except(['show', 'create', 'edit']);

    // ──────────────── Notes ────────────────
    Route::resource('notes', NoteController::class)->except(['show', 'create', 'edit']);

    // ──────────────── Analytics ────────────────
    Route::get('/analytics', [ProductivityController::class, 'index'])->name('analytics');

    // ──────────────── Settings ────────────────
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings/theme', [SettingController::class, 'updateTheme'])->name('settings.theme');
    Route::post('/settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile');
    Route::get('/settings/backup', [SettingController::class, 'exportBackup'])->name('settings.backup');
    Route::post('/settings/restore', [SettingController::class, 'importRestore'])->name('settings.restore');

    // ──────────────── Notifications ────────────────
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');

    // ──────────────── Admin Panel ────────────────
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminController::class, 'updateRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });

});

require __DIR__.'/auth.php';
