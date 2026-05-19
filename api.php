<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Patient\ProfileController;
use App\Http\Controllers\Patient\MedicineSearchController;
use App\Http\Controllers\Patient\AuthController;
use App\Http\Controllers\Patient\GoogleAuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MedicineController;
use App\Http\Controllers\Admin\UserController;

// ══ عام — بدون توكن ═══════════════════════════════════════════════════
Route::post('register',              [AuthController::class,       'register']);
Route::post('login',       [AuthController::class,      'login']);

// ✅ هذه الثلاثة خارج أي middleware — المريض يحتاجها قبل تسجيل الدخول
Route::post('auth/complete-profile', [AuthController::class,       'completeProfile']);
Route::get('auth/google',            [GoogleAuthController::class, 'redirect']);
Route::get('auth/google/callback',   [GoogleAuthController::class, 'callback']);

Route::post('admin/login', [AdminAuthController::class, 'login']);

// ══ الأدمن فقط ════════════════════════════════════════════════════════

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {

    Route::post('logout', [AdminAuthController::class, 'logout']);

    Route::get('dashboard', [DashboardController::class, 'index']);

    // ── الأدوية
    Route::prefix('medicines')->group(function () {
        Route::get('index',        [MedicineController::class, 'index']);
        Route::post('store',       [MedicineController::class, 'store']);
        Route::get('{id}',    [MedicineController::class, 'show']);
        Route::put('{id}',    [MedicineController::class, 'update']);
        Route::delete('{id}', [MedicineController::class, 'destroy']);
    });

    // ── المستخدمين
      Route::prefix('users')->group(function () {

        Route::get('/',                   [UserController::class, 'index']);
        Route::post('store',                  [UserController::class, 'store']);
        Route::get('{id}',               [UserController::class, 'show']);
        Route::put('{id}',               [UserController::class, 'update']);
        Route::patch('{id}/activate',    [UserController::class, 'activate']);
        Route::patch('{id}/deactivate',  [UserController::class, 'deactivate']);
  });
});

// ══ المريض فقط ════════════════════════════════════════════════════════

Route::middleware(['auth:sanctum'])->prefix('patient')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);

    // ── الملف الشخصي
    Route::get('profile',          [ProfileController::class, 'show']);
    Route::put('profile',          [ProfileController::class, 'update']);
    Route::post('change-password', [ProfileController::class, 'changePassword']);
Route::prefix('medicines')->group(function () {
    // ── البحث عن الأدوية
    Route::get('search',            [MedicineSearchController::class, 'search']);
    Route::get('search/ingredient', [MedicineSearchController::class, 'searchByIngredient']);
    Route::get('search/category',   [MedicineSearchController::class, 'searchByCategory']);
    Route::get('{id}',              [MedicineSearchController::class, 'show']);
});
});// ══ معلومات المستخدم الحالي ═══════════════════════════════════════════
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');











///////////////////////////////////////////////////////////////////////////////////////////////////////////////
