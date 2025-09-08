<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Curator\LandmarkController;
use App\Http\Controllers\Curator\TriviaController;
use App\Http\Controllers\Curator\DashboardController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\QrController;
use App\Http\Controllers\Admin\ReportController;

Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');


// routes/web.php (top-level, outside /curators group)
Route::get('/s/{id}', function ($id) {
    return redirect()->route('curators.map', ['id' => $id]);
})->name('qr.resolve');


Route::prefix('curators')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('curators.dashboard');

    Route::get('/map', [LandmarkController::class, 'map'])->name('curators.map');

    Route::resource('landmarks', LandmarkController::class);

    Route::get('/qr',                 [QrController::class, 'index'])->name('curators.qr');
    Route::post('/qr',                [QrController::class, 'store'])->name('curators.qr.store');
    Route::delete('/qr/{id}',         [QrController::class, 'destroy'])->name('curators.qr.destroy');
    Route::get('/qr/{id}/download',   [QrController::class, 'download'])->name('curators.qr.download');


    Route::prefix('landmarks/{landmark}')->group(function () {
        Route::get('/trivia', [TriviaController::class, 'index'])->name('landmarks.trivia.index');
        Route::get('/trivia/create', [TriviaController::class, 'create'])->name('landmarks.trivia.create');
        Route::post('/trivia', [TriviaController::class, 'store'])->name('landmarks.trivia.store');
        Route::get('/trivia/{trivia}/edit', [TriviaController::class, 'edit'])->name('landmarks.trivia.edit');
        Route::put('/trivia/{trivia}', [TriviaController::class, 'update'])->name('landmarks.trivia.update');
        Route::delete('/trivia/{trivia}', [TriviaController::class, 'destroy'])->name('landmarks.trivia.destroy');
    });

    Route::get('/trivia', [TriviaController::class, 'all'])->name('curators.trivia.all');
});

Route::prefix('admin')->middleware(['web'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/curators', [AdminController::class, 'curators'])->name('admin.curators');
    Route::get('/landmarks', [AdminController::class, 'landmarks'])->name('admin.landmarks');
    Route::get('/logs', [AdminController::class, 'logs'])->name('admin.logs');
    Route::delete('/logs/clear', [AdminController::class, 'clearLogs'])->name('admin.logs.clear');

     // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::get('/reports/export/{any?}', [ReportController::class, 'export'])->name('admin.reports.export');
});
