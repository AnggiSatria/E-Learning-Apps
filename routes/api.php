<?php

use App\Http\Controllers\{
    UserController,
    StudentController,
    TeacherController,
    ClassController,
    ClassMemberController,
    TaskController,
    AssignmentController
};

Route::apiResource('users', UserController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('teachers', TeacherController::class);
Route::apiResource('classes', ClassController::class);
Route::apiResource('class-members', ClassMemberController::class);
Route::apiResource('tasks', TaskController::class);
Route::apiResource('assignments', AssignmentController::class);
