<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LabController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\SubAdminController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\SuperAdminController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Public (rate-limited) ────────────────────────────────────────────────
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login',    [AuthController::class, 'login']);
    });

    // ── Plans (public read — for pricing page) ───────────────────────────────
    Route::get('plans', [PlanController::class, 'index']);

    // ── Protected ────────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout',   [AuthController::class, 'logout']);
        Route::get('auth/me',        [AuthController::class, 'me']);
        Route::patch('auth/profile', [AuthController::class, 'updateProfile']);

        // Subscription status (for lab-facing app)
        Route::get('subscription/status', [SubscriptionController::class, 'myStatus']);

        // ── Super Admin routes ───────────────────────────────────────────────
        Route::middleware('role:superadmin')->prefix('super')->group(function () {
            Route::get('stats',                    [SuperAdminController::class, 'stats']);
            Route::get('labs',                     [SuperAdminController::class, 'labs']);
            Route::post('labs',                    [SuperAdminController::class, 'createLab']);
            Route::get('labs/{lab}',               [SuperAdminController::class, 'showLab']);
            Route::patch('labs/{lab}',                             [SuperAdminController::class, 'updateLab']);
            Route::patch('labs/{lab}/toggle',                      [SuperAdminController::class, 'toggleLab']);
            Route::patch('labs/{lab}/users/{user}/password',       [SuperAdminController::class, 'setUserPassword']);
            Route::post('labs/{lab}/users',                        [SuperAdminController::class, 'addLabUser']);
            Route::delete('labs/{lab}/users/{user}',               [SuperAdminController::class, 'removeLabUser']);
            Route::post('labs/{lab}/subscription',                 [SubscriptionController::class, 'assign']);

            Route::get('plans',            [PlanController::class, 'index']);
            Route::post('plans',           [PlanController::class, 'store']);
            Route::patch('plans/{plan}',   [PlanController::class, 'update']);
            Route::delete('plans/{plan}',  [PlanController::class, 'destroy']);
        });

        // ── Lab-facing routes (subscription gated) ──────────────────────────
        Route::middleware('subscription')->group(function () {

            // Lab
            Route::get('lab',    [LabController::class, 'show']);
            Route::patch('lab',  [LabController::class, 'update']);

            // Users (admin manages team members)
            Route::get('users',           [UserController::class, 'index']);
            Route::post('users',          [UserController::class, 'store']);
            Route::delete('users/{user}', [UserController::class, 'destroy']);

            // Dashboard
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

            // Patients
            Route::apiResource('patients', PatientController::class);

            // Tests + Reference Ranges
            Route::apiResource('tests', TestController::class);
            Route::post('tests/{test}/ranges',           [TestController::class, 'storeRange']);
            Route::put('tests/{test}/ranges/{range}',    [TestController::class, 'updateRange']);
            Route::delete('tests/{test}/ranges/{range}', [TestController::class, 'destroyRange']);

            // Orders
            Route::get('orders',                  [OrderController::class, 'index']);
            Route::post('orders',                 [OrderController::class, 'store']);
            Route::get('orders/{order}',          [OrderController::class, 'show']);
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
            Route::delete('orders/{order}',       [OrderController::class, 'destroy']);

            // Results & Reports
            Route::post('orders/{order}/results', [ResultController::class, 'bulkStore']);
            Route::put('results/{result}',        [ResultController::class, 'update']);
            Route::get('orders/{order}/report',   [ReportController::class, 'downloadReport']);

            // Billing
            Route::get('bills',                  [BillController::class, 'index']);
            Route::post('bills',                 [BillController::class, 'store']);
            Route::get('bills/{bill}',           [BillController::class, 'show']);
            Route::patch('bills/{bill}',         [BillController::class, 'update']);
            Route::patch('bills/{bill}/payment', [BillController::class, 'recordPayment']);
            Route::get('orders/{order}/invoice', [ReportController::class, 'downloadInvoice']);
        });
    });
});
