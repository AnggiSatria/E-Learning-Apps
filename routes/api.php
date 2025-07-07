<?php

use Illuminate\Http\Request;
use App\Http\Controllers\{
    UserController,
    StudentController,
    TeacherController,
    ClassController,
    ClassMemberController,
    TaskController,
    AssignmentController
};

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return response()->json($request->user());
});
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
Route::apiResource('users', UserController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('classes', ClassController::class);
Route::apiResource('class-members', ClassMemberController::class);
Route::get('/class-members/user/{userId}', [ClassMemberController::class, 'getByUserId']);
Route::apiResource('tasks', TaskController::class);
Route::get('/tasks/user/{userId}', [TaskController::class, 'getTasksByUserId']);
Route::get('/tasks/class/{classId}', [TaskController::class, 'getByClassId']);
Route::get('/tasks/class/{classId}/user/{userId}', [TaskController::class, 'getByClassAndUser']);
Route::apiResource('assignments', AssignmentController::class);
