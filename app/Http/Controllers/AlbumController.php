<?php

namespace App\Http\Controllers;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\MP3;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    //         $ffmpeg = FFMpeg::create();
    // $audio = $ffmpeg->open(storage_path("app/public/{$songPath}"));
    // $duration = $audio->getFormat()->get('duration'); 

        $song = Song::create([
            'artist_id' => $artist->id,
            'album_id' => $album->id, 
            'artist_name' => $artist->name,
            'cover_path' => $coverPath ?? null, 
            // 'cover_path' => $coverPath ? Storage::url($coverPath) : null,
            'title' => $validated['title'],
            'song_path' => $songPath,
            // 'song_path' => $songPath ? Storage::url($songPath) : null,
        ]);

        $album->songs()->attach($song->id);

        return response()->json(['message' => 'Song added to album successfully', 'song' => $song], 201);
    }



    public function index()
{
    $albums = Album::with(['songs','artist'])->get();
    return response()->json($albums);
}

public function show($id){

    $album=Album::with('songs')->find($id);
    if(!$album){
        return response()->json(['message'=>'album not found']);
    }

    return response()->json([
        'album'=>[
            'id'=>$album->id,
            'title'=>$album->title,
            'artist'=>$album->artist->name,
            'artist_id'=>$album->artist_id,
            'album_cover'=>$album->album_cover,
        ],
        'songs' => $album->songs->map(function ($song) {
            return [
                'id' => $song->id,
                'artist_name' => $song->artist_name,
                'artist' => $song->artist->name,
                // 'song_path' => $song->song_path, 
                'song_path' => asset("storage/{$song->song_path}"),
               
            ];
        }),
    ], 200);

}




public function trendingAlbums()
{
    $albums = Album::with('songs')
        ->withCount(['songs as total_plays' => function ($query) {
            $query->select(DB::raw('COALESCE(SUM(plays), 0)'));
        }])
        ->orderByDesc('total_plays')
        ->limit(10)
        ->get();

    return response()->json($albums);
}



}

