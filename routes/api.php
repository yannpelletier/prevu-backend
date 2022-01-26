<?php

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

Route::post('auth/register', 'AuthController@register');
Route::post('auth/login', 'AuthController@login');
Route::post('auth/reset-password', 'AuthController@resetPassword');
Route::post('auth/send-password-reset', 'AuthController@sendPasswordResetLink');

Route::get('auth/google/link', 'AuthController@createGoogleSignInLink');
Route::post('auth/google/sign-in', 'AuthController@completeGoogleSignIn');

Route::resource('assets', 'AssetController')->only(['index', 'store', 'destroy']);
Route::resource('watermarks', 'WatermarkController')->only(['index', 'store', 'show', 'update', 'destroy']);

Route::get('notifications', 'NotificationController@index');
Route::delete('notifications', 'NotificationController@deleteAll');

Route::get('products/{product}/preview', 'ProductController@showPreview');
Route::get('products/{product}/thumbnail', 'ProductController@showThumbnail');
Route::get('products/{product}/thumbnail/original', 'ProductController@showOriginalThumbnail');
Route::resource('products', 'ProductController')->only(['index', 'store', 'show', 'update', 'destroy']);

Route::get('stores/{id}', 'StoreController@show');
Route::get('stores/{id}/products', 'StoreController@getProducts');
Route::get('stores/{id}/products/{slug}', 'StoreController@getProduct');

Route::resource('contact', 'ContactController')->only(['store']);

// Routes only allowed to logged in users.
Route::group(['middleware' => 'auth:api'], function () {
    Route::post('auth/logout', 'AuthController@logout');

    Route::get('users/me', 'UserController@show');
    Route::get('users/me/seller-progression', 'UserController@getSellerProgression');
    Route::patch('users/me', 'UserController@update');

    Route::resource('stores', 'StoreController')->only(['update', 'store']);
    Route::get('users/me/store', 'UserController@getStore');
    Route::get('users/me/sales', 'UserController@getSales');
    Route::get('users/me/analytics', 'UserController@getAnalytics');

    Route::get('users/me/stripe-link', 'UserController@getStripeLink');
    Route::post('users/me/stripe-account', 'UserController@addStripeAccount');

    Route::resource('purchases', 'PurchaseController')->only(['index', 'store']);
    Route::get('purchases/zip', 'PurchaseController@downloadZip');
    Route::get('purchases/{purchase}/download', 'PurchaseController@downloadOriginal');
    Route::get('purchases/{purchase}/original', 'PurchaseController@showOriginal');
    Route::get('purchases/{purchase}/thumbnail', 'PurchaseController@showThumbnail');

    Route::resource('feedback', 'FeedbackController')->only(['store']);
});
