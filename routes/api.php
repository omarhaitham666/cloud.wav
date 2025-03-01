<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtistRequestController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FamousArtistRequestsController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SongController;
use Illuminate\Http\Request;

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendVerificationCode']);
Route::post('/reset-password',[ForgotPasswordController::class,'resetPassword']);
// Route::get('/artists/{artistId}/songs', [SongController::class, 'getArtistSongs']);
Route::get('/Artists', [ArtistController::class, 'index']);
Route::get('/Artists/{id}', [ArtistController::class, 'show']);
Route::get('/Songs', [SongController::class, 'getSongs']);
Route::get('/songs/{id}', [SongController::class, 'getSong']);
Route::get('/artist/{id}',[ArtistController::class,'getArtist']);
Route::get('/artists',[ArtistController::class,'getArtists']);
Route::get('/albums', [AlbumController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    // Route::post('/upload_song',[SongController::class,'uploadSong']);
    Route::post('/artist-requests', [ArtistRequestController::class, 'store']);
    Route::post('/famous-artist-requests', [FamousArtistRequestsController::class, 'store']);
  

}); 

Route::middleware(['auth:sanctum', 'isArtist'])->group(function () {
    Route::post('/songs/upload', [SongController::class, 'uploadSong']);
    Route::delete('/songs/{id}', [SongController::class, 'deleteSong']);
     
     Route::post('/albums', [AlbumController::class, 'store']);

     
     Route::post('/albums/{albumId}/songs', [AlbumController::class, 'addSongToAlbum']);
});


Route::middleware(['auth:sanctum','admin'])->group(function(){

    Route::get('/artist-requests', [ArtistRequestController::class, 'index']);
    Route::post('/artist-requests/{id}/status', [ArtistRequestController::class, 'updateStatus']);
// .....................................end of artist request...........................................
    Route::post('/famous_artist-requests/{id}/status', [FamousArtistRequestsController::class, 'updateStatus']);
    Route::get('/famous_artist-requests', [FamousArtistRequestsController::class, 'index']);
    Route::patch('/artists/{id}', [ArtistController::class, 'update']);
    Route::delete('/artists/{id}', [ArtistController::class, 'destroy']);

});



