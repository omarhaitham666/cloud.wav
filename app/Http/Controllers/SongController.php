<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Like;
use App\Models\Song;
use App\Models\User;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
    {

public function uploadSong(Request $request)
{
    try {
        $artist = Artist::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'cover_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'song_path' => 'required|mimes:mp3,wav,ogg|max:10240',
            'album_ids' => 'nullable|array', 
            'album_ids.*' => 'exists:albums,id',
        ]);

        $coverPath = $request->file('cover_path')->store('songs/covers', 'public');
        $songPath = $request->file('song_path')->store('songs/files', 'public');

        // $ffmpeg = FFMpeg::create();
        // $audio = $ffmpeg->open(storage_path("app/public/{$songPath}"));
        // $duration = $audio->getFormat()->get('duration'); 
    
        $song = Song::create([
            'title' => $request->title,
            'artist_id' => $artist->id,
            'artist_name' => $artist->name,
            'cover_path' => $coverPath,
            'song_path' => $songPath,
            // 'duration'=>$duration,
        ]);

        
        if ($request->has('album_ids')) {
            $song->albums()->attach($request->album_ids);
        }

        return response()->json([
            'message' => 'تم رفع الأغنية بنجاح',
            'song' => [
                'id' => $song->id,
                'title' => $song->title,
                'artist_name' => $song->artist_name,
                'cover_url' => asset("storage/{$coverPath}"),
                'song_url' => asset("storage/{$songPath}"),
            ]
        ], 201);
    } catch (\Exception $e) {
        return response()->json(['error' => 'حدث خطأ أثناء رفع الأغنية', 'details' => $e->getMessage()], 500);
    }
}


    public function deleteSong($id)
    {
        $song = Song::findOrFail($id);

        
        // if (Auth::id() !== $song->artist_id && Auth::user()->role !== 'admin,artist') {
        //     return response()->json(['error' => 'غير مسموح لك بحذف هذه الأغنية'], 403);
        // }
        if (Auth::id() !== $song->artist_id && !in_array(Auth::user()->role, ['admin', 'artist'])) {
            return response()->json(['error' => 'غير مسموح لك بحذف هذه الأغنية'], 403);
        }
        
       
        try {
            
            Storage::disk('public')->delete([$song->song_path, $song->cover_path]);

            
            $song->delete();

            return response()->json(['message' => 'تم حذف الأغنية بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ أثناء الحذف'], 500);
        }
        
    }



    public function likeSong($id)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'يجب تسجيل الدخول'], 401);
    }

    $song = Song::findOrFail($id);
    $user = Auth::user();

    $like = Like::where('user_id', $user->id)->where('song_id', $song->id)->first();

    if ($like) {
        $like->delete();
        $isLiked = false;
    } else {
        Like::create([
            'user_id' => $user->id,
            'song_id' => $song->id,
        ]);
        $isLiked = true;
    }

    $likesCount = Like::where('song_id', $song->id)->count();

    return response()->json([
        'isLiked' => $isLiked,
        'likesCount' => $likesCount
    ]);
}



public function getSongs()
{
    $userId = Auth::id(); 
    $songs = Song::with('likes')->get()->map(function ($song) {
        return [
            'id' => $song->id,
            'title' => $song->title,
            'artist_name' => $song->artist_name,
            'cover_url' => asset("storage/{$song->cover_path}"),
            'song_url' => asset("storage/{$song->song_path}"),
            'likes_count' => $song->likes->count(),
            // 'isLiked' => Auth::check() ? $song->isLikedByUser() : false,
            'isLiked' => Auth::id() ? $song->isLikedByUser() : false,
        ];
    });

    return response()->json($songs);
}



public function getSong($id)
{
    $userId = Auth::id();
    $song = Song::withCount('likes')->findOrFail($id);

    $isLiked = false;
    if ($userId) {
        $isLiked = Like::where('user_id', $userId)
                       ->where('song_id', $song->id)
                       ->exists();
    }

    $isLiked = $userId ? Like::where('user_id', $userId)->where('song_id', $song->id)->exists() : false;


    return response()->json([
        'id' => $song->id,
        'title' => $song->title,
        'artist_name' => $song->artist_name,
        'cover_url' => asset("storage/{$song->cover_path}"),
        'song_url' => asset("storage/{$song->song_path}"),
        'likes_count' => $song->likes_count,
        // 'isLiked' => $userId ? Like::where('user_id', $userId)->where('song_id', $song->id)->exists() : false,
        'isLiked' => $isLiked,
        'plays' => $song->plays,
    ]);
}




public function trendingSongs(){
    $songs=Song::withCount('likes')
               ->orderByDesc('likes_count')
               ->take(10)
               ->get()
               ->map(function($song){
                return[
                    'id'=>$song->id,
                    'title'=>$song->title,
                    'artist'=>$song->artist,
                    'likes_count'=>$song->likes_count,
                    'audio_url'=>asset("storage/{$song->song_path}"),
                    ];
               });

               return response()->json($songs);

    
}


public function downloadSong($id)
{

    $song = Song::findOrFail($id);

    // التأكد من أن الملف موجود
    $filePath = storage_path("app/public/{$song->song_path}");
    

    if (!file_exists($filePath)) {
        return response()->json(['error' => 'الملف غير موجود'], 404);
    }

    // return response()->download($filePath, $song->title . '.mp3');
    return Response::download($filePath, $song->title . '.mp3', [
        'Content-Type' => 'audio/mpeg',
        'Content-Disposition' => 'attachment; filename="' . $song->title . '.mp3"',
        ]);
    }



    // public function playSong($id){
    //     $song=Song::findOrFail($id);
    //     $song->increment('plays');

    //     return response()->json([
    //         'message'=>'song played',
    //         'song'=>[
    //             'id'=>$song->id,
    //             'title'=>$song->title,
    //             'plays'=>$song->plays,
    //             'song_path'=>asset("storage/{$song->song_path}")
    //         ]

    //     ]);
    // }

//     public function playSong($id)
// {
//     $song = Song::findOrFail($id);
//     $song->increment('plays'); // زيادة عدد مرات التشغيل بمقدار 1
//     return response()->json(['message' => 'تم تشغيل الأغنية', 'plays' => $song->plays]);
// }

public function playSong($id)
{
    $song = Song::findOrFail($id);
    $song->increment('plays'); // زيادة عدد مرات التشغيل بمقدار 1

    return response()->json([
        'message' => 'تم تشغيل الأغنية',
        'song' => [
            'id' => $song->id,
            'title' => $song->title,
            'plays' => $song->plays,
            'song_url' => asset("storage/{$song->song_path}")
        ]
    ]);
}




    }
