<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\GalleryController as PublicGalleryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserPersonChangeRequestController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PersonController as AdminPersonController;
use App\Http\Controllers\Admin\RelationshipController;
use App\Http\Controllers\Admin\UnionController;
use App\Http\Controllers\Admin\PeopleTableController;
use App\Http\Controllers\Admin\AdminPersonChangeRequestController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\PostEventController;
use App\Http\Controllers\Admin\PrintTreeController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\HomeSectionController;
use App\Http\Controllers\Admin\NepaliCalendarYearController;
use App\Http\Controllers\Admin\PopupNoticeController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\PublicNoticeController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\Admin\CommitteeController as AdminCommitteeController;

/* ─────────────────────────────────────
   Public pages
───────────────────────────────────── */
Route::get('/', [DashboardController::class, 'home'])->name('home');
Route::get('/sitemap.xml', \App\Http\Controllers\SitemapController::class)->name('sitemap');

Route::get('/सुझाव', [FeedbackController::class, 'create'])->name('feedback.create');
Route::post('/सुझाव', [FeedbackController::class, 'store'])->name('feedback.store')->middleware('throttle:10,1');
Route::get('/सुझाव/धन्यवाद', [FeedbackController::class, 'thanks'])->name('feedback.thanks');

Route::get('/वशावलि', [TreeController::class, 'index'])->name('tree.index');
Route::get('/tree-json', [TreeController::class, 'treeJson'])->name('tree.json');
Route::get('/tree-unconnected', [TreeController::class, 'unconnected'])->name('tree.unconnected');
Route::get('/people/search', [TreeController::class, 'searchPeople'])->name('people.search');
Route::get('/people/first-by-pusta', [TreeController::class, 'firstPersonByPusta'])->name('people.firstByPusta');
Route::get('/person/{person}', [TreeController::class, 'personShow'])->name('person.show');
Route::get('/कार्यसमिति', [CommitteeController::class, 'index'])->name('committee.index');
Route::get('/member/{person}', [TreeController::class, 'memberPage'])->name('member.page');

Route::get('/members', [PeopleTableController::class, 'index'])->name('admin.people.directory');
Route::get('/members/json', [PeopleTableController::class, 'all'])->name('admin.people.directory.all');

Route::get('/सूचनाहरू', [PublicNoticeController::class, 'index'])->name('notices.public');
Route::get('/सूचनाहरू/{notice}', [PublicNoticeController::class, 'show'])->name('notices.show');
Route::get('/storage/notices/{file}', function (string $file) {
    abort_unless(file_exists(public_path('notices/' . $file)), 404);
    return redirect(asset('notices/' . $file));
})->where('file', '.*');
Route::get('/gallery', [PublicGalleryController::class, 'index'])->name('gallery.index');
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

// CMS pages (must be last to avoid conflicts)
Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');

/* ─────────────────────────────────────
   User Authentication
───────────────────────────────────── */
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

/* ─────────────────────────────────────
   User Change Requests (auth + verified required)
───────────────────────────────────── */
Route::middleware('auth')->group(function () {
    Route::post('/person/{person}/request-child', [UserPersonChangeRequestController::class, 'addChild'])->name('request.child');
    Route::post('/person/{person}/request-death', [UserPersonChangeRequestController::class, 'markDeceased'])->name('request.death');
    Route::post('/person/{person}/request-edit', [UserPersonChangeRequestController::class, 'requestProfileUpdate'])->name('request.edit');
    Route::post('/person/{person}/request-marriage', [UserPersonChangeRequestController::class, 'requestMarriage'])->name('request.marriage');
    Route::post('/person/{person}/request-parent', [UserPersonChangeRequestController::class, 'linkParent'])->name('request.parent');
    Route::post('/person/{person}/upload-photo', [UserPersonChangeRequestController::class, 'uploadPhoto'])->name('person.upload-photo');
    Route::post('/request-not-listed', [UserPersonChangeRequestController::class, 'notListed'])->name('request.notlisted');
    Route::get('/my-requests', [UserPersonChangeRequestController::class, 'myRequests'])->name('my.requests');
});

/* ─────────────────────────────────────
   Admin Login
───────────────────────────────────── */
Route::get('/admin', [DashboardController::class, 'showadminform'])->name('admin.login.form');
Route::post('/login/admin', [DashboardController::class, 'adminLogin'])->name('admin.login');

/* ─────────────────────────────────────
   Admin Panel (protected)
───────────────────────────────────── */
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/renewal', [DashboardController::class, 'updateRenewal'])->name('renewal.update');
    Route::post('/logout', [DashboardController::class, 'logout'])->name('logout');

    // Persons CRUD
    Route::get('/persons/search', [AdminPersonController::class, 'searchJson'])->name('persons.search');
    Route::post('/persons/generate-member-numbers', [AdminPersonController::class, 'generateMemberNumbers'])->name('persons.generate-member-numbers');
    Route::get('/persons', [AdminPersonController::class, 'index'])->name('persons.index');
    Route::get('/persons/create', [AdminPersonController::class, 'create'])->name('persons.create');
    Route::post('/persons', [AdminPersonController::class, 'store'])->name('persons.store');
    Route::get('/persons/{person}/edit', [AdminPersonController::class, 'edit'])->name('persons.edit');
    Route::patch('/persons/{person}/inline', [AdminPersonController::class, 'inlineUpdate'])->name('persons.inline');
    Route::put('/persons/{person}', [AdminPersonController::class, 'update'])->name('persons.update');
    Route::delete('/persons/{person}', [AdminPersonController::class, 'destroy'])->name('persons.destroy');

    // Relationships
    Route::get('/relationships/search', [RelationshipController::class, 'searchJson'])->name('relationships.search');
    Route::get('/relationships/preview', [RelationshipController::class, 'preview'])->name('relationships.preview');
    Route::get('/relationships/blocked', [RelationshipController::class, 'blocked'])->name('relationships.blocked');
    Route::get('/relationships/parent/{person}/children', [RelationshipController::class, 'children'])->name('relationships.children');
    Route::put('/relationships/parent/{person}/children', [RelationshipController::class, 'updateChildren'])->name('relationships.children.update');
    Route::get('/relationships', [RelationshipController::class, 'index'])->name('relationships.index');
    Route::post('/relationships', [RelationshipController::class, 'store'])->name('relationships.store');
    Route::delete('/relationships/{edge}', [RelationshipController::class, 'destroy'])->name('relationships.destroy');

    // Unions
    Route::get('/unions/search', [UnionController::class, 'searchJson'])->name('unions.search');
    Route::get('/unions', [UnionController::class, 'index'])->name('unions.index');
    Route::post('/unions', [UnionController::class, 'store'])->name('unions.store');
    Route::delete('/unions/{union}', [UnionController::class, 'destroy'])->name('unions.destroy');

    // Change Requests
    Route::get('/change-requests', [AdminPersonChangeRequestController::class, 'index'])->name('requests.index');
    Route::get('/change-requests/{r}', [AdminPersonChangeRequestController::class, 'show'])->name('requests.show');
    Route::post('/change-requests/{r}/approve', [AdminPersonChangeRequestController::class, 'approve'])->name('requests.approve');
    Route::post('/change-requests/{r}/reject', [AdminPersonChangeRequestController::class, 'reject'])->name('requests.reject');
    Route::delete('/change-requests/{r}', [AdminPersonChangeRequestController::class, 'destroy'])->name('requests.destroy');

    // Feedback
    Route::get('/feedback', [FeedbackController::class, 'adminIndex'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'adminShow'])->name('feedback.show');
    Route::delete('/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

    // Menus
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');
    Route::post('/menus/reorder', [MenuController::class, 'reorder'])->name('menus.reorder');

    // Pages
    Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [AdminPageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');

    // Notices
    Route::get('/notices', [NoticeController::class, 'index'])->name('notices.index');
    Route::post('/notices', [NoticeController::class, 'store'])->name('notices.store');
    Route::put('/notices/{notice}', [NoticeController::class, 'update'])->name('notices.update');
    Route::delete('/notices/{notice}', [NoticeController::class, 'destroy'])->name('notices.destroy');

    // Gallery (media library)
    Route::get('/gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
    Route::post('/gallery/upload', [MediaController::class, 'uploadMultiple'])->name('gallery.upload');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    // Popup Notices
    Route::get('/popups', [PopupNoticeController::class, 'index'])->name('popups.index');
    Route::post('/popups', [PopupNoticeController::class, 'store'])->name('popups.store');
    Route::get('/popups/{popup}/edit', [PopupNoticeController::class, 'edit'])->name('popups.edit');
    Route::put('/popups/{popup}', [PopupNoticeController::class, 'update'])->name('popups.update');
    Route::delete('/popups/{popup}', [PopupNoticeController::class, 'destroy'])->name('popups.destroy');
    Route::patch('/popups/{popup}/toggle', [PopupNoticeController::class, 'toggle'])->name('popups.toggle');

    // Print Tree
    Route::get('/print-tree', [PrintTreeController::class, 'form'])->name('print-tree.form');
    Route::post('/print-tree', [PrintTreeController::class, 'generate'])->name('print-tree.generate');

    // User Management (visible to all admins, write actions restricted to super_admin in controller)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    // Events
    Route::get('/events', [PostEventController::class, 'index'])->name('events.index');
    Route::post('/events', [PostEventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [PostEventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [PostEventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [PostEventController::class, 'destroy'])->name('events.destroy');

    // Home Page Sections
    Route::get('/home-sections', [HomeSectionController::class, 'index'])->name('home-sections.index');
    Route::get('/home-sections/create', [HomeSectionController::class, 'create'])->name('home-sections.create');
    Route::post('/home-sections', [HomeSectionController::class, 'store'])->name('home-sections.store');
    Route::get('/home-sections/{homeSection}/edit', [HomeSectionController::class, 'edit'])->name('home-sections.edit');
    Route::put('/home-sections/{homeSection}', [HomeSectionController::class, 'update'])->name('home-sections.update');
    Route::delete('/home-sections/{homeSection}', [HomeSectionController::class, 'destroy'])->name('home-sections.destroy');

    // Nepali Calendar Years
    Route::get('/calendar-years', [NepaliCalendarYearController::class, 'index'])->name('calendar-years.index');
    Route::post('/calendar-years', [NepaliCalendarYearController::class, 'store'])->name('calendar-years.store');
    Route::put('/calendar-years/{calendarYear}', [NepaliCalendarYearController::class, 'update'])->name('calendar-years.update');
    Route::delete('/calendar-years/{calendarYear}', [NepaliCalendarYearController::class, 'destroy'])->name('calendar-years.destroy');

    // Committees
    Route::get('/committees', [AdminCommitteeController::class, 'index'])->name('committees.index');
    Route::post('/committees', [AdminCommitteeController::class, 'store'])->name('committees.store');
    Route::put('/committees/{committee}', [AdminCommitteeController::class, 'update'])->name('committees.update');
    Route::delete('/committees/{committee}', [AdminCommitteeController::class, 'destroy'])->name('committees.destroy');
    Route::post('/committees/{committee}/members', [AdminCommitteeController::class, 'storeMember'])->name('committees.members.store');
    Route::put('/committees/{committee}/members/{member}', [AdminCommitteeController::class, 'updateMember'])->name('committees.members.update');
    Route::delete('/committees/{committee}/members/{member}', [AdminCommitteeController::class, 'destroyMember'])->name('committees.members.destroy');
});
