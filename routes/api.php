<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\ArtistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArtistRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FamousArtistRequestsController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SongController;
use Illuminate\Http\Request;

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendVerificationCode']);
Route::post('/reset-password',[ForgotPasswordController::class,'resetPassword']);

// ...........................songs..........................................
Route::get('/Songs', [SongController::class, 'getSongs']);
Route::get('/songs/{id}', [SongController::class, 'getSong']);
Route::get('/songs/download/{id}', [SongController::class, 'downloadSong']);
Route::get('/trendingSongs',[SongController::class,'trendingSongs']);
Route::post('/songs/{id}/play', [SongController::class, 'playSong']);
// ............................songs........................................

// .....................artists.........................................
Route::get('/artist/{id}',[ArtistController::class,'getArtist']);
Route::get('/artists',[ArtistController::class,'getArtists']);
Route::get('/Artists', [ArtistController::class, 'index']);
Route::get('/Artists/{id}', [ArtistController::class, 'show']);
// .................artists...........................................


// ..............albums.......................................
Route::get('/albums', [AlbumController::class, 'index']);
Route::get('/albums/{id}', [AlbumController::class, 'show']);
Route::get('/trending-albums', [AlbumController::class, 'trendingAlbums']);

// ..............albums.......................................




Route::middleware('auth:sanctum')->group(function(){
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/artist-requests', [ArtistRequestController::class, 'store']);
    Route::post('/famous-artist-requests', [FamousArtistRequestsController::class, 'store']);
    Route::post('/songs/{id}/like', [SongController::class, 'likeSong']);
    Route::post('/songs/{id}/play', [SongController::class, 'playSong']);
    Route::post('/services',[ServiceController::class,'store']);
}); 

Route::middleware(['auth:sanctum', 'isArtist'])->group(function () {
    Route::post('/songs/upload', [SongController::class, 'uploadSong']);
    Route::delete('/songs-delete/{id}', [SongController::class, 'deleteSong']);
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::post('/albums/{albumId}/songs', [AlbumController::class, 'addSongToAlbum']);
    Route::delete('albums-delete/{id}',[AlbumController::class,'deleteAlbum']);
    Route::put('album-update/{id}',[AlbumController::class,'update']);
});


Route::middleware(['auth:sanctum','admin'])->group(function(){

    Route::get('/artist-requests', [ArtistRequestController::class, 'index']);
    Route::post('/artist-requests/{id}/status', [ArtistRequestController::class, 'updateStatus']);
    Route::delete('/songs/{id}', [SongController::class, 'deleteSong']);
// .....................................end of artist request...........................................
    Route::post('/famous_artist-requests/{id}/status', [FamousArtistRequestsController::class, 'updateStatus']);
    Route::get('/famous_artist-requests', [FamousArtistRequestsController::class, 'index']);
    Route::put('/artists/{id}', [ArtistController::class, 'update']);
    Route::delete('/artists/{id}', [ArtistController::class, 'destroy']);
    Route::put('/service-update-status/{id}',[ServiceController::class,'updateStatus']);
    Route::get('/services/type/{type}', [ServiceController::class, 'getByType']);
    Route::delete('/service-delete/{id}', [ServiceController::class, 'delete']);
});



