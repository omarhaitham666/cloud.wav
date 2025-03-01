<?php

// namespace App\Http\Controllers;

// use App\Models\Album;
// use App\Models\Artist;
// use App\Models\Song;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;

// class AlbumController extends Controller
// {
//     //
//     public function store(Request $request){
//         $artist = Artist::where('user_id', Auth::id())->firstOrFail();
//         if(!$artist){
//             return response()->json(['error'=>'غير مصرح لك بالقيام بذلك']);
//         }

//         $validated=$request->validate([
//             'title' => 'required|string|max:255',
//             'album_cover' => 'required|image|mimes:jpeg,png,jpg|max:2048',
//         ]);

//         if ($request->hasFile('album_cover')) {
//             $coverPath = $request->file('album_cover')->store('album_covers', 'public');
//         }
    
//         $album = Album::create([
//             'artist_id' => $artist->id,
//             'title' => $validated['title'],
//             'album_cover' => $coverPath ?? null,
//         ]);

//         return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
//     }

//     public function addSongToAlbum(Request $request,$albumId){

//         $artist = Artist::where('user_id', Auth::id())->firstOrFail();
//         if(!$artist){
//             return response()->json(['error'=>'غير مصرح لك بالقيام بذلك']);
//         }

//         $album = Album::where('id', $albumId)->where('artist_id', $artist->id)->firstOrFail();

//         $validated = $request->validate([
//             'title' => 'required|string|max:255',

//             'file' => 'required|mimes:mp3,wav,ogg|max:10000',
//         ]);
    
//         if ($request->hasFile('file')) {
//             $song_Path = $request->file('file')->store('songs', 'public');
//         }
    
//         $song = Song::create([
//             'artist_id' => $artist->id,
//             'cover_path'=>$artist->cover_path,
//             'title' => $validated['title'],
//             'song_path' => $song_Path ?? null,
//         ]);
    
        
//         $album->songs()->attach($song->id);
    
//         return response()->json(['message' => 'Song added to album successfully', 'song' => $song], 201);
//     }
// }



namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlbumController extends Controller
{
    public function store(Request $request)
    {
        $artist = Artist::where('user_id', Auth::id())->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'album_cover' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $coverPath = $request->hasFile('album_cover') ? 
            $request->file('album_cover')->store('album_covers', 'public') 
            : null;

           
        $album = Album::create([
            'artist_id' => $artist->id,
            'title' => $validated['title'],
            'album_cover' => $coverPath,
        ]);

        return response()->json(['message' => 'Album created successfully', 'album' => $album], 201);
    }

    public function addSongToAlbum(Request $request, $albumId)
    {
        $artist = Artist::where('user_id', Auth::id())->firstOrFail();

        $album = Album::where('id', $albumId)->where('artist_id', $artist->id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|mimes:mp3,wav,ogg|max:10000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('song_covers', 'public');
        }

        $songPath = $request->hasFile('file') ? 
            $request->file('file')->store('songs', 'public') 
            : null;

        $song = Song::create([
            'artist_id' => $artist->id,
            'album_id' => $album->id, 
            'artist_name' => $artist->name,
            'cover_path' => $coverPath ?? null, 
            'title' => $validated['title'],
            'song_path' => $songPath,
        ]);

        $album->songs()->attach($song->id);

        return response()->json(['message' => 'Song added to album successfully', 'song' => $song], 201);
    }



    public function index()
{
    $albums = Album::with(['songs','artist'])->get();
    return response()->json($albums);
}

}

