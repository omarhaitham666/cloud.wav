<?php

namespace App\Http\Controllers;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
public function store(Request $request)
    {
        dd($request->all());
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

public function deleteAlbum($id)
{
    try {
        if (!Auth::check()) {
            return response()->json(['error' => 'يجب عليك تسجيل الدخول كفنان'], 403);
        }

        $album = Album::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $album->artist_id && !in_array(trim($user->role), ['admin', 'artist'])) {
            return response()->json(['message' => 'ليس لديك الصلاحية لمسح الألبومات'], 403);
        }

        
        foreach ($album->songs as $song) {
            if (Storage::disk('public')->exists($song->song_path)) {
                Storage::disk('public')->delete($song->song_path);
            }
            if (Storage::disk('public')->exists($song->cover_path)) {
                Storage::disk('public')->delete($song->cover_path);
            }

            $song->delete();
        }

        
        if (Storage::disk('public')->exists($album->album_cover)) {
            Storage::disk('public')->delete($album->album_cover);
        }

        
        $album->delete();

        return response()->json(['message' => 'تم حذف الألبوم والأغاني المرتبطة به بنجاح']);

    } catch (\Exception $e) {
        return response()->json(['message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()], 500);
    }
}

public function update(Request $request, $id) {
    $album = Album::findOrFail($id);
    $user = Auth::user();

    
    if ($user->id !== $album->artist_id && !in_array(trim($user->role), ['admin', 'artist'])) {
        return response()->json(['message' => 'ليس لديك صلاحية تحديث البيانات '], 403);
    }

    
    $request->validate([
        'title' => 'required_without:album_cover|string|max:255',
        'album_cover' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // التحقق من الصورة
    ]);

    
    $album->title = $request->title;

    
    if ($request->hasFile('album_cover')) {
        
        if ($album->album_cover && Storage::disk('public')->exists($album->album_cover)) {
            Storage::disk('public')->delete($album->album_cover);
        }

        
        $coverPath = $request->file('album_cover')->store('album_covers', 'public');
        $album->album_cover = $coverPath;
    }

    $album->save();

    return response()->json(['message' => 'تم تحديث بيانات الألبوم بنجاح', 'album' => $album]);
}

}

