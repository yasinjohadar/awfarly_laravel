<?php

//*********************************************************************************
//Users profile image

//Get current user profile image
use App\Helpers\Files;
use App\Helpers\Images;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

Route::get('user/profile/image', function () {
    return Images::GetCurrentUserProfileImage();
})->name('user.profile.image');

//Get user profile image
Route::get('users/profile/image/{image?}', function ($image = null) {
    return Images::GetUserProfileImage($image);
})->name('users.profile.image')->where(['image' => '(.*)']);

//*********************************************************************************
//Get user Chat image
Route::get('admin/chats/{image?}', function ($image = null) {
    return Images::getChatImage($image);
})->name('chat.image.get')->where(['image' => '(.*)']);

//*********************************************************************************
//Get category image
Route::get('categories/{image?}', function ($image = null) {
    return Images::getCategoryImage($image);
})->name('category.image.get')->where(['image' => '(.*)']);

//*********************************************************************************
//Get category image
Route::get('image/{image?}', function ($image = null) {
    return Images::getImage($image);
})->name('files.image.get')->where(['image' => '(.*)']);

//*********************************************************************************
//view Media inline (images + videos) — used by the app/frontend to display media
Route::get('media/view/{uuid}/{conversion?}', function ($uuid, $conversion = null) {
    $media = Media::findByUuid($uuid);
    return Files::streamMedia($media, $conversion);
})->name('media.view')->where(['conversion' => '[A-Za-z0-9_-]+']);

//*********************************************************************************
//download Media
Route::get('media/{uuid}', function ($uuid) {
    $media = Media::findByUuid($uuid);
    return Files::downloadMedia($media);
})->name('media.download');
