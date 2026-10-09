<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TestimonialRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/experience', ExperienceController::class)->name('experience');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');

Route::get('/resume', [ResumeController::class, 'show'])->name('resume');
Route::get('/resume/download', [ResumeController::class, 'download'])->name('resume.download');

// Private, single-use testimonial links (created in the admin). Never listed or indexed.
Route::middleware('noindex')->group(function () {
    Route::get('/feedback/thanks', [TestimonialRequestController::class, 'thanks'])->name('testimonial.thanks');
    Route::get('/feedback/{token}', [TestimonialRequestController::class, 'show'])
        ->where('token', '[A-Za-z0-9]+')
        ->middleware('throttle:30,1')
        ->name('testimonial.request');
    Route::post('/feedback/{token}', [TestimonialRequestController::class, 'store'])
        ->where('token', '[A-Za-z0-9]+')
        ->middleware('throttle:6,1')
        ->name('testimonial.submit');
});

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
|
| Content management behind authentication + the `access-admin` gate.
| There is no registration route; create administrators with
| `php artisan portfolio:admin`.
|
*/

if (config('portfolio.admin.enabled')) {
    Route::prefix(config('portfolio.admin.path'))
        ->name('admin.')
        ->middleware('noindex')
        ->group(function () {
            Route::middleware('guest')->group(function () {
                Route::get('login', [Admin\AuthController::class, 'create'])->name('login');
                Route::post('login', [Admin\AuthController::class, 'store'])
                    ->middleware('throttle:admin-login')
                    ->name('login.store');
            });

            Route::middleware(['auth', 'can:access-admin'])->group(function () {
                Route::post('logout', [Admin\AuthController::class, 'destroy'])->name('logout');

                Route::get('/', Admin\DashboardController::class)->name('dashboard');
                Route::post('maintenance/{action}', Admin\MaintenanceController::class)
                    ->whereIn('action', ['clear-cache', 'optimize', 'clear-optimization'])
                    ->name('maintenance');

                Route::resource('projects', Admin\ProjectController::class)->except('show');
                Route::post('projects/{project}/images', [Admin\ProjectImageController::class, 'store'])
                    ->name('projects.images.store');
                Route::delete('projects/{project}/images/{image}', [Admin\ProjectImageController::class, 'destroy'])
                    ->scopeBindings()
                    ->name('projects.images.destroy');

                Route::post('testimonials/request', [Admin\TestimonialController::class, 'requestLink'])
                    ->name('testimonials.request');
                Route::post('testimonials/{testimonial}/regenerate-link', [Admin\TestimonialController::class, 'regenerateLink'])
                    ->name('testimonials.regenerate');
                Route::resource('testimonials', Admin\TestimonialController::class)->except('show');

                Route::resource('skills', Admin\SkillController::class)->except('show');
                Route::resource('experiences', Admin\ExperienceController::class)->except('show');
                Route::resource('educations', Admin\EducationController::class)->except('show');

                Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
                Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
                Route::patch('messages/{message}', [Admin\MessageController::class, 'update'])->name('messages.update');
                Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

                Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
                Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
            });
        });
}
