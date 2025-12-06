<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PersonController as AdminPersonController;
use App\Http\Controllers\Admin\RelationshipController;
use App\Http\Controllers\Admin\UnionController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Http\Controllers\FeedbackController;


Route::get('/',[DashboardController::class, 'home'])->name('home');
Route::get('/admin', [DashboardController::class, 'showadminform'])->name('admin.login.form');
Route::post('/login/admin', [DashboardController::class, 'adminLogin'])->name('admin.login');

Route::get('/sujhav', [FeedbackController::class,'create'])->name('feedback.create');
Route::post('/sujhav', [FeedbackController::class,'store'])->name('feedback.store')->middleware('throttle:10,1');
Route::get('/sujhav/thanks', [FeedbackController::class,'thanks'])->name('feedback.thanks');





Route::get('/वशावलि', [TreeController::class, 'index'])->name('tree.index');
Route::get('/people/search', [TreeController::class, 'searchPeople'])->name('people.search');     // name+pusta filter
Route::get('/people/by-pusta', [TreeController::class, 'peopleByPusta'])->name('people.byPusta'); // list people in a pusta


Route::get('/graph', [TreeController::class, 'graph'])->name('tree.graph');
Route::get('/कार्यसमिति', [TreeController::class, 'committee'])->name('committee.index');

Route::get('/tree-json', [TreeController::class, 'treeJson'])->name('tree.json');

Route::get('/person/{person}', [PersonController::class, 'show'])->name('person.show'); // JSON
Route::post('/person', [PersonController::class, 'store'])->name('person.store');
Route::put('/person/{person}', [PersonController::class, 'update'])->name('person.update');


Route::group(['middleware' => 'admin.auth'], routes: function () {

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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




        Route::get('/admin/feedback', [FeedbackController::class,'adminIndex'])->name('feedback.index');
    Route::get('/admin/feedback/{feedback}', [FeedbackController::class,'adminShow'])->name('feedback.show');
    Route::delete('/admin/feedback/{feedback}', [FeedbackController::class,'destroy'])->name('feedback.destroy');

        Route::post('/logout', action: [DashboardController::class, 'logout'])->name('logout');
    });
});
