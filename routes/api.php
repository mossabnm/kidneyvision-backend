<?php

use App\Http\Controllers\Api\V1\AnalysisController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PDFReportController;
use App\Http\Controllers\Api\V1\StatisticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — KidneyVision AI
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically.
|
*/

// ──────────────────────────────────────────────
// Public Routes (no authentication required)
// ──────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->name('auth.register');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::post('/password/email', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'sendResetLinkEmail'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::post('/password/reset', [\App\Http\Controllers\Api\V1\PasswordResetController::class, 'reset'])
        ->name('password.reset');
});

Route::post('/guest-predict', [\App\Http\Controllers\Api\V1\GuestAnalysisController::class, 'predict'])
    ->name('guest.predict');

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
})->name('health');

Route::get('/cache-test', function () {
    \Illuminate\Support\Facades\Cache::put('test_key', 'working', 60);
    return response()->json(['cache_status' => \Illuminate\Support\Facades\Cache::get('test_key')]);
});

Route::get('/test-email', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email', 'test@example.com');
    \Illuminate\Support\Facades\Mail::raw('This is a test email from KidneyVision AI. Your SMTP configuration is working perfectly!', function ($message) use ($email) {
        $message->to($email)->subject('KidneyVision SMTP Test');
    });
    return response()->json(['status' => 'Email sent successfully to ' . $email]);
});

// ──────────────────────────────────────────────
// Protected Routes (Sanctum authentication)
// ──────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');

    Route::get('/auth/me', [AuthController::class, 'me'])
        ->name('auth.me');

    // Prediction
    Route::post('/predict', [AnalysisController::class, 'predict'])
        ->name('analysis.predict');

    // Analyses CRUD
    Route::get('/analyses', [AnalysisController::class, 'index'])
        ->name('analyses.index');

    Route::get('/analyses/{id}', [AnalysisController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('analyses.show');

    Route::delete('/analyses/{id}', [AnalysisController::class, 'destroy'])
        ->where('id', '[0-9]+')
        ->name('analyses.destroy');

    // Statistics
    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->name('statistics.index');

    // PDF Report
    Route::get('/analyses/{id}/report/pdf', [PDFReportController::class, 'download'])
        ->where('id', '[0-9]+')
        ->name('analyses.report.pdf');
});

// ──────────────────────────────────────────────
// Database Connection Test Route
// ──────────────────────────────────────────────
Route::get('/db-test', function () {
    try {
        $count = \Illuminate\Support\Facades\DB::table('users')->count();
        return response()->json([
            'status' => 'OK',
            'users_count' => $count
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'message' => $e->getMessage()
        ], 500);
    }
});

// ──────────────────────────────────────────────
// Environment Debug Route
// ──────────────────────────────────────────────
Route::get('/env-test', function () {
    return response()->json([
        'os_env' => explode("\n", shell_exec('env')),
        'directory_files' => explode("\n", shell_exec('ls -la')),
        'config_cache_exists' => file_exists(base_path('bootstrap/cache/config.php')),
        'env_file_exists' => file_exists(base_path('.env'))
    ]);
});

// ──────────────────────────────────────────────
// Mail Debug Route
// ──────────────────────────────────────────────
Route::get('/mail-test', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('This is a test email from KidneyVision API directly via Resend.', function ($message) {
            $message->to('mossab.nm.yt@gmail.com')
                    ->subject('Test Email Resend');
        });
        return response()->json([
            'status' => 'OK',
            'message' => 'Email sent successfully via Resend API!',
            'from_configured' => env('MAIL_FROM_ADDRESS')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'message' => $e->getMessage(),
            'from_configured' => env('MAIL_FROM_ADDRESS')
        ]);
    }
});
