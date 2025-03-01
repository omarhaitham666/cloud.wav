<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
{
    

    // public function uploadSong(Request $request)
    // {
    

    //     try {
           
    //         $artist = Artist::where('user_id', Auth::id())->firstOrFail();
            

           
    //         $request->validate([
    //             'title' => 'required|string|max:255',
    //             'cover_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    //             'song_path' => 'required|mimes:mp3,wav,ogg|max:10240',
    //         ]);

            
    //         $coverPath = $request->file('cover_path')->store('songs/covers', 'public');
    //         $songPath = $request->file('song_path')->store('songs/files', 'public');


    //         $song = Song::create([
    //             'title' => $request->title,
    //             'artist_id' => $artist->id,
    //             'artist_name' => $artist->name,
    //             'cover_path' => $coverPath,
    //             'song_path' => $songPath,
    //         ]);
            

    //         return response()->json([
    //             'message' => 'تم رفع الأغنية بنجاح',
    //             'song' => [
    //                 'id' => $song->id,
    //                 'title' => $song->title,
    //                 'artist_name' => $song->artist_name,
    //                 'cover_url' => asset("storage/{$coverPath}"),
    //                 'song_url' => asset("storage/{$songPath}"),
    //             ]
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'حدث خطأ أثناء رفع الأغنية', 'details' => $e->getMessage()], 500);
    //     }
    // }

//     public function uploadSong(Request $request)
// {
//     try {
//         $artist = Artist::where('user_id', Auth::id())->firstOrFail();

//         $request->validate([
//             'title' => 'required|string|max:255',
//             'cover_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
//             'song_path' => 'required|mimes:mp3,wav,ogg|max:10240',
//             'album_id' => 'nullable|exists:albums,id', // التحقق من صحة album_id إذا تم إرساله
//         ]);

//         $coverPath = $request->file('cover_path')->store('songs/covers', 'public');
//         $songPath = $request->file('song_path')->store('songs/files', 'public');

//         $song = Song::create([
//             'title' => $request->title,
//             'artist_id' => $artist->id,
//             'artist_name' => $artist->name,
//             'cover_path' => $coverPath,
//             'song_path' => $songPath,
//         ]);

//         // إضافة الأغنية إلى الألبوم إذا تم تحديد album_id
//         if ($request->has('album_id')) {
//             $album = Album::find($request->album_id);

//             if ($album) {
//                 $album->songs()->attach($song->id);
//             }
//         }

//         return response()->json([
//             'message' => 'تم رفع الأغنية بنجاح',
//             'song' => [
//                 'id' => $song->id,
//                 'title' => $song->title,
//                 'artist_name' => $song->artist_name,
//                 'cover_url' => asset("storage/{$coverPath}"),
//                 'song_url' => asset("storage/{$songPath}"),
//             ]
//         ], 201);
//     } catch (\Exception $e) {
//         return response()->json(['error' => 'حدث خطأ أثناء رفع الأغنية', 'details' => $e->getMessage()], 500);
//     }
// }


public function uploadSong(Request $request)
{
    try {
        $artist = Artist::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'cover_path' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'song_path' => 'required|mimes:mp3,wav,ogg|max:10240',
            'album_ids' => 'nullable|array', // دعم عدة ألبومات
            'album_ids.*' => 'exists:albums,id',
        ]);

        $coverPath = $request->file('cover_path')->store('songs/covers', 'public');
        $songPath = $request->file('song_path')->store('songs/files', 'public');

        $song = Song::create([
            'title' => $request->title,
            'artist_id' => $artist->id,
            'artist_name' => $artist->name,
            'cover_path' => $coverPath,
            'song_path' => $songPath,
        ]);

        // ربط الأغنية بأكثر من ألبوم
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

        
        if (Auth::id() !== $song->artist_id && Auth::user()->role !== 'admin') {
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

    public function getSongs()
    {
        $songs = Song::all()->map(function ($song) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'artist_name' => $song->artist_name,
                'cover_url' => asset("storage/{$song->cover_path}"),
                'song_url' => asset("storage/{$song->song_path}"),
            ];
        });
    
        return response()->json($songs);
    }




    
    public function getSong($id)
{
    $song = Song::findOrFail($id);

    return response()->json([
        'id' => $song->id,
        'title' => $song->title,
        'artist_name' => $song->artist_name,
        'cover_url' => asset("storage/{$song->cover_path}"),
        'song_url' => asset("storage/{$song->song_path}"),
    ]);
}






}
