<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (if needed)
// Route::post('/login', [AuthController::class, 'login']);

// Protected routes - require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    
    // ============================================
    // USER MANAGEMENT ROUTES
    // ============================================
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/trashed', [UserController::class, 'trashed'])->name('trashed');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [UserController::class, 'forceDestroy'])->name('force-destroy');
    });

    // ============================================
    // ROLE MANAGEMENT ROUTES
    // ============================================
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}', [RoleController::class, 'show'])->name('show');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        
        // Role assignment operations
        Route::post('/{role}/assign-user', [RoleController::class, 'assignToUser'])->name('assign-user');
        Route::post('/{role}/revoke-user', [RoleController::class, 'revokeFromUser'])->name('revoke-user');
        
        // Role permission operations
        Route::post('/{role}/assign-permission', [RoleController::class, 'assignPermission'])->name('assign-permission');
        Route::post('/{role}/revoke-permission', [RoleController::class, 'revokePermission'])->name('revoke-permission');
    });

    // ============================================
    // PERMISSION MANAGEMENT ROUTES
    // ============================================
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/categories', [PermissionController::class, 'categories'])->name('categories');
        Route::get('/by-category', [PermissionController::class, 'byCategory'])->name('by-category');
        Route::get('/{permission}', [PermissionController::class, 'show'])->name('show');
        
        // Permission assignment operations
        Route::post('/{permission}/assign-user', [PermissionController::class, 'assignToUser'])->name('assign-user');
        Route::post('/{permission}/revoke-user', [PermissionController::class, 'revokeFromUser'])->name('revoke-user');
        Route::post('/bulk-assign-user', [PermissionController::class, 'bulkAssignToUser'])->name('bulk-assign-user');
        
        // Get user permissions
        Route::get('/user/{user}', [PermissionController::class, 'userPermissions'])->name('user-permissions');
    });

});
