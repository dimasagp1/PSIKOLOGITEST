<?php

use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root Route - Redirect berdasarkan status auth
Route::get('/', function () {
    return Auth::check() ? redirect('/home') : view('welcome');
});

// ==================== AUTH ROUTES ====================
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Password Reset Routes
Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// ==================== PUBLIC ROUTES ====================
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// ==================== USER ROUTES (Require Auth) ====================
Route::middleware(['auth'])->group(function () {

        // Route baru untuk complete test manual
    Route::post('/test/complete', [TestController::class, 'completeTestManual'])->name('test.complete');
    
    // Dashboard User
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    

    // Test Routes untuk User Biasa
    Route::prefix('test')->group(function () {
        // Halaman utama tes
        Route::get('/', [TestController::class, 'index'])->name('test.index');
        
        // Memulai tes baru
        Route::post('/start', [TestController::class, 'startTest'])->name('test.start');
        
        // Halaman pengerjaan tes
        Route::get('/take', [TestController::class, 'takeTest'])->name('test.take');
        
        // Submit jawaban
        Route::post('/submit-answer', [TestController::class, 'submitAnswer'])->name('test.submit');
        
        // Skip soal
        Route::post('/skip-question', [TestController::class, 'skipQuestion'])->name('test.skip');
        
        // Check auto skip
        Route::get('/check-auto-skip', [TestController::class, 'checkAutoSkip'])->name('test.check-auto-skip');
        
        // Update waktu
        Route::post('/update-time', [TestController::class, 'updateTime'])->name('test.update-time');
        
        // Status tes (untuk user biasa)
        Route::get('/status', [TestController::class, 'status'])->name('test.status');
    });
});

// ==================== ADMIN ROUTES (Require Auth & Admin Role) ====================
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    // Dashboard Admin
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Manajemen User
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{user}/notes', [AdminController::class, 'updateUserNotes'])->name('admin.update-notes');
    
    // Hasil Tes
    Route::get('/test-results', [AdminController::class, 'testResults'])->name('admin.test-results');
    
    // Detail hasil tes per user
    Route::get('/results/{test}', [AdminController::class, 'showUserResult'])->name('admin.results.show');
    
    // Analitik
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
    
    // Export data
    Route::get('/export-results', [AdminController::class, 'exportResults'])->name('admin.export-results');

    Route::get('/results/{test}/download', [AdminController::class, 'downloadPDF'])->name('admin.results.download');

    Route::get('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    
    Route::post('/users/{user}/change-password', [AdminController::class, 'changePassword'])->name('admin.users.change-password');

    // Tambahkan routes baru untuk edit dan hapus user
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
    Route::post('/users/{user}/reset-test', [AdminController::class, 'resetUserTest'])->name('admin.users.reset-test');

});

// ==================== FALLBACK ROUTE ====================
Route::fallback(function () {
    return Auth::check() ? redirect('/home') : redirect('/welcome');
});

// Route debug sementara
Route::get('/debug/test/{id}', function($id) {
    if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isHR())) {
        abort(403);
    }
    
    $test = \App\Models\KraeplinTest::with('user')->find($id);
    
    if (!$test) {
        return "Test tidak ditemukan";
    }
    
    echo "<h1>Data Test - {$test->user->name}</h1>";
    echo "<p>Created: {$test->created_at}</p>";
    echo "<hr>";
    
    echo "<h2>Summary</h2>";
    echo "Correct: {$test->correct_answers}<br>";
    echo "Wrong: {$test->wrong_answers}<br>";
    echo "Skipped: {$test->skipped_answers}<br>";
    echo "Total Score: {$test->total_score}<br>";
    echo "<hr>";
    
    echo "<h2>Detail Soal</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>No</th><th>Soal</th><th>Correct</th><th>User Answer</th><th>Is Correct</th><th>Skipped</th></tr>";
    
    foreach ($test->test_data as $index => $question) {
        $soal = "{$question['number1']} + {$question['number2']}";
        $correct = $question['correct_answer'];
        $userAnswer = $question['user_answer'] ?? 'NULL';
        $isCorrect = $question['is_correct'] ? '✅' : '❌';
        $skipped = $question['skipped'] ? '✅' : '❌';
        
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>{$soal} = {$correct}</td>";
        echo "<td>{$correct}</td>";
        echo "<td>{$userAnswer}</td>";
        echo "<td>{$isCorrect}</td>";
        echo "<td>{$skipped}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
})->middleware('auth');

// Route untuk testing reset data
Route::get('/test/reset-data', function() {
    if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isHR())) {
        abort(403);
    }
    
    // Reset user test status
    \App\Models\User::where('role', 'user')->update(['has_taken_test' => false]);
    
    // Hapus semua data tes
    \App\Models\KraeplinTest::truncate();
    \App\Models\TestSession::truncate();
    
    return redirect('/admin/dashboard')->with('success', 'Data test berhasil direset');
})->middleware('auth');

// Route untuk memperbaiki data tes yang inconsistent (admin only)
Route::post('/admin/test/{test}/fix-data', function($test) {
    if (!Auth::check() || (!Auth::user()->isAdmin() && !Auth::user()->isHR())) {
        abort(403);
    }
    
    $test = \App\Models\KraeplinTest::find($test);
    
    if (!$test) {
        return redirect()->back()->with('error', 'Test tidak ditemukan');
    }
    
    $test->fixInconsistentData();
    
    return redirect()->back()->with('success', 'Data test berhasil diperbaiki');
})->name('admin.test.fix-data')->middleware('auth');