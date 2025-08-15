<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BulkRequestController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/bulk-requests', [BulkRequestController::class, 'store']);
    Route::get('/bulk-requests', [BulkRequestController::class, 'index']);
    Route::get('/notifications', [BulkRequestController::class, 'notifications']); // For notifications
    Route::post('/notifications/{id}/mark-as-read', [BulkRequestController::class, 'markNotificationAsRead']);
});
Broadcast::routes(['middleware' => ['auth:sanctum']]);
