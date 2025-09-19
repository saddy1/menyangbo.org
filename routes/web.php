<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PersonController as AdminPersonController;
use App\Http\Controllers\Admin\RelationshipController;
use App\Http\Controllers\Admin\UnionController;


Route::get('/', [TreeController::class, 'index'])->name('tree.index');
Route::get('/graph', [TreeController::class, 'graph'])->name('tree.graph');

Route::get('/tree-json', [TreeController::class, 'treeJson'])->name('tree.json');

Route::get('/person/{person}', [PersonController::class, 'show'])->name('person.show'); // JSON
Route::post('/person', [PersonController::class, 'store'])->name('person.store');
Route::put('/person/{person}', [PersonController::class, 'update'])->name('person.update');


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Persons CRUD
    Route::get('/persons', [AdminPersonController::class, 'index'])->name('persons.index');
    Route::get('/persons/create', [AdminPersonController::class, 'create'])->name('persons.create');
    Route::post('/persons', [AdminPersonController::class, 'store'])->name('persons.store');
    Route::get('/persons/{person}/edit', [AdminPersonController::class, 'edit'])->name('persons.edit');
    Route::put('/persons/{person}', [AdminPersonController::class, 'update'])->name('persons.update');
    Route::delete('/persons/{person}', [AdminPersonController::class, 'destroy'])->name('persons.destroy');

    // Relationships (parent-child)
    Route::get('/relationships', [RelationshipController::class, 'index'])->name('relationships.index');
    Route::post('/relationships', [RelationshipController::class, 'store'])->name('relationships.store');
    Route::delete('/relationships/{edge}', [RelationshipController::class, 'destroy'])->name('relationships.destroy');

    // Unions (marriages/partnerships)
    Route::get('/unions', [UnionController::class, 'index'])->name('unions.index');
    Route::post('/unions', [UnionController::class, 'store'])->name('unions.store');
    Route::delete('/unions/{union}', [UnionController::class, 'destroy'])->name('unions.destroy');
});