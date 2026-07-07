<?php

use App\Http\Controllers\Web\BookingController;
use App\Http\Controllers\Web\CourseController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LeadController;
use App\Http\Controllers\Web\ListingController;
use App\Http\Controllers\Web\LocaleController;
use App\Http\Controllers\Web\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// بوابة الدخول (معلم / مشرف / أدمن)
Route::view('/portal', 'portal')->name('portal');

// "View all" listing pages
Route::get('/teachers', [ListingController::class, 'teachers'])->name('teachers.index');
Route::get('/programs', [ListingController::class, 'programs'])->name('programs.index');
Route::get('/courses', [ListingController::class, 'courses'])->name('courses.index');
Route::get('/testimonials', [ListingController::class, 'testimonials'])->name('testimonials.index');
Route::get('/faqs', [ListingController::class, 'faqs'])->name('faqs.index');

// Detail pages
Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('bookings.store');

Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/admin-locale/{locale}', [LocaleController::class, 'switchAdmin'])
    ->middleware('auth')
    ->name('admin.locale.switch');
