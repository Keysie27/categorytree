<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

// Web Routes for Views
Route::view('/', 'welcome');
Route::view('/categories/tree', 'categories.tree');
Route::view('/categories/search', 'categories.search');

// Category Controller Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/create', [CategoryController::class, 'create']);
    Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/{id}', [CategoryController::class, 'deleteCategory']);
    Route::get('/{id}', [CategoryController::class, 'getCategory']);
    Route::get('/tree', [CategoryController::class, 'printTree']);
    Route::get('/search', [CategoryController::class, 'searchView']);
});

// API Controller Routes
Route::prefix('/api/categories')->group(function () {
    Route::post('/', [CategoryController::class, 'addCategory'])->name('categories.add');
    Route::put('/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/{id}', [CategoryController::class, 'deleteCategory']);
    Route::get('/', [CategoryController::class, 'getAllCategories']);
    Route::get('/{id}', [CategoryController::class, 'getCategory']);
    Route::get('/tree', [CategoryController::class, 'printTree']);
    Route::get('/search', [CategoryController::class, 'search'])->name('categories.search');
});

