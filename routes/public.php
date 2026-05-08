<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobVacancyController;

// Public Interview Application Routes
Route::get('/interview/apply/{uuid}', [InterviewController::class, 'publicForm'])->name('interviews.public.form');
Route::post('/interview/apply/{uuid}', [InterviewController::class, 'publicUpdate'])->name('interviews.public.update');

// Public Job Vacancy Routes
Route::get('/career/{slug}', [JobVacancyController::class, 'publicView'])->name('job-vacancies.public');
Route::get('/career/{slug}/apply', [JobVacancyController::class, 'showApplyForm'])->name('job-vacancies.apply.form');
Route::post('/career/{slug}/apply', [JobVacancyController::class, 'submitApplication'])->name('job-vacancies.apply.submit');
