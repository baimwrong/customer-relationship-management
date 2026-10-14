<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\BulkImportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected CRM Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Companies / Accounts
    Route::get('/companies/export', [CompanyController::class, 'export'])->name('companies.export');
    Route::resource('companies', CompanyController::class);

    // Contacts
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
    Route::resource('contacts', ContactController::class);

    // Leads & Conversion
    Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::resource('leads', LeadController::class);

    // Deals & Pipeline Kanban
    Route::get('/deals/export', [DealController::class, 'export'])->name('deals.export');
    Route::get('/deals/kanban', [DealController::class, 'kanban'])->name('deals.kanban');
    Route::patch('/deals/{deal}/stage', [DealController::class, 'updateStage'])->name('deals.update-stage');
    Route::resource('deals', DealController::class);

    // Activities & Interactions
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    // Tasks & Action Items
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Admin & Sales Manager Bulk Import System
    Route::middleware('role:admin,sales_manager')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/imports', [BulkImportController::class, 'index'])->name('imports.index');
        Route::post('/imports', [BulkImportController::class, 'import'])->name('imports.process');
        Route::get('/imports/template/{type}', [BulkImportController::class, 'downloadTemplate'])->name('imports.template');
        Route::get('/imports/{bulkImport}', [BulkImportController::class, 'show'])->name('imports.show');
    });

    // Admin System Management
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::resource('users', UserController::class);
    });
});
