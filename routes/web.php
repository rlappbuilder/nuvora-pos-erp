<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MasterData\CategoryController;
use App\Http\Controllers\MasterData\BrandController;
use App\Http\Controllers\MasterData\UnitController;
use App\Http\Controllers\MasterData\ColorController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
/**  oute master data */
Route::prefix('master-data')
    ->group(function () {

        Route::get(
            '/categories',
            [CategoryController::class, 'index']
        )->name('categories.index');

        Route::post(
            '/categories',
            [CategoryController::class, 'store']
        )->name('categories.store');

        Route::put(
            '/categories/{category}',
            [CategoryController::class, 'update']
        )->name('categories.update');

        Route::delete(
            '/categories/{category}',
            [CategoryController::class, 'destroy']
        )->name('categories.destroy');
         });
/** end route master data  categories*/
       
   
    /** route Master data brand */
    Route::prefix('master-data')->group(function () {
        Route::resource(
    'brands',
    BrandController::class
        );
  });

     /** route Master data unit */
    Route::prefix('master-data')->group(function () {
        Route::resource(
    'units',
    UnitController::class
);
  });

  /** route Master data Color */
Route::prefix('master-data')->group(function () {

    Route::resource(

        'colors',

        \App\Http\Controllers\MasterData\ColorController::class

    );

});
require __DIR__.'/auth.php';
