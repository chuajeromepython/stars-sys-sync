<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::get('/authenticate/{username}/{password}/', [AppApiController::class, 'authenticate']);
Route::get('/ecdc/domains', [AppApiController::class, 'getEcdcDomains']);
Route::post('/ecdc/domains/sync', [AppApiController::class, 'syncEcdcDomains']);
Route::post('/classrooms/sync', [AppApiController::class, 'syncClassroomsByTeacherUserId']);
Route::post('/students/sync', [AppApiController::class, 'studentsPerClassroom']);
Route::post('/assessment/sync', [AppApiController::class, 'syncAssessment']); // user_id
Route::post('/upload/assessment', [AppApiController::class, 'uploadAssessment']);
