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

//Android App Links verification — lets the OS confirm this domain belongs to
//the app, so a shared post/offer link opens the app directly instead of the
//browser when it's installed. Package/SHA256 are the real values for
//com.price.crush.app's release keystore (android/app/cert/key.jks) — if the
//app is later published via Play App Signing, Google may re-sign it with a
//different certificate, in which case this fingerprint must be updated from
//Play Console after publishing.
Route::get('/.well-known/assetlinks.json', function () {
    return response()->json([
        [
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => 'com.price.crush.app',
                'sha256_cert_fingerprints' => [
                    '4C:A6:FB:CE:16:BB:22:3D:F1:F5:A6:D7:E5:04:DC:C4:E1:2C:F9:50:22:BD:52:D2:AF:79:76:62:39:13:C1:04',
                ],
            ],
        ],
    ]);
})->name('android.assetlinks');

//iOS Universal Links verification — same purpose as assetlinks.json above,
//for Apple. Team ID + bundle ID are the real values from
//ios/Runner.xcodeproj/project.pbxproj. Must be served with no file extension
//and a JSON content-type (enforced by response()->json() below).
Route::get('/.well-known/apple-app-site-association', function () {
    return response()->json([
        'applinks' => [
            'apps' => [],
            'details' => [
                [
                    'appID' => 'QZ8RQMKNTB.com.masar.price.crush',
                    'paths' => ['/posts/*', '/offers/*'],
                ],
            ],
        ],
    ]);
})->name('ios.apple-app-site-association');

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
