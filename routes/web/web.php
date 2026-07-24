<?php

use App\Http\Controllers\Frontend\ContactUs\ContactUsController;
use App\Http\Controllers\Frontend\Home\HomeController;
use App\Http\Controllers\Frontend\Offers\OffersController;
use App\Http\Controllers\Frontend\Pages\PagesController;
use App\Http\Controllers\Frontend\Posts\PostsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home
Route::get('/', [HomeController::class, 'index'])->name('home.index');

//get post by id
Route::get('/posts/{id}', [PostsController::class, 'index'])->name('post.index');

//get offer by id
Route::get('/offers/{id}', [OffersController::class, 'index'])->name('offer.index');

//get pages by id
Route::get('/pages/{id}/{slug?}', [PagesController::class, 'index'])->name('pages.index');

//change language
Route::get('languages/{code}', function ($code) {
    Session::put('userLocale', $code);
    if (Auth::guard('admin')->check()) {
        Auth::guard('admin')->user()->update([
            'language_code' => $code,
        ]);
    }
    return redirect()->back();
})->name('language.change');

//contact us
Route::get('contact-us/{type?}', [ContactUsController::class, 'index'])->name('contact-us.index');

// Auth
Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => true, // Password Reset Routes...
    'verify' => true, // Email Verification Routes...
]);
