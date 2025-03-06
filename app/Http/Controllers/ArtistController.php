<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    //
    public function index(){
        $artists=Artist::all();
        return response()->json($artists);
    }

    public function show($id)
{
    $artist = Artist::with('songs')->findOrFail($id);

    return response()->json([
        'id' => $artist->id,
        'user_id' => $artist->user_id,
        'profile_image' => asset("storage/{$artist->profile_image}"), 
        'name' => $artist->name,
        'email' => $artist->email,
        'number' => $artist->number,
        'whatsapp_number' => $artist->whatsapp_number,
        'social_links' => $artist->social_links,
        'details' => $artist->details,
        'division' => $artist->division,
        'songs' => $artist->songs->map(function ($song) {
            return [
                'id' => $song->id,
                'title' => $song->title,
                'cover_path' => asset("storage/{$song->cover_path}"),
                'song_path' => asset("storage/{$song->song_path}"),
            ];
        }),
    ]);
}

    public function update(Request $request, $id)
{
    
    $artist = Artist::findOrFail($id);

    
    if (Auth::id() !== $artist->user_id && Auth::user()->role !== 'admin') {
        return response()->json(['error' => 'غير مسموح لك بتعديل هذا الفنان'], 403);
    }

    
    $request->validate([
        'name' => 'required|string|max:255',
        'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
    ]);

    
    $artist->name = $request->name;

    
    if ($request->hasFile('profile_image')) {
        
        if ($artist->profile_image) {
            Storage::disk('public')->delete($artist->profile_image);
        }

        
        $imagePath = $request->file('profile_image')->store('artists', 'public');
        $artist->profile_image = $imagePath;
    }

    $artist->save();

    return response()->json([
        'message' => 'تم تحديث بيانات الفنان بنجاح',
        'artist' => [
            'id' => $artist->id,
            'name' => $artist->name,
            'profile_image' => asset("storage/{$artist->profile_image}"), // إرجاع رابط الصورة الجديدة
        ]
    ]);
}

    public function destroy($id){
        $artist=Artist::findOrFail($id);
        if(Auth::user()->role!=='admin'){
            return response()->json(['error'=>'غير مسموح لك بحذف الفنان '],403);
        }
        $artist->delete();
        return response()->json(['message'=>'تم حذف الفنان بنجاح']);
    }

    public function getArtist($id){
        $artist=Artist::findOrFail($id);
        

        return response()->json([
            'id'=>$artist->id,
            'user_id'=>$artist->user_id,
            'profile_image'=>$artist->profile_image,
            'name'=>$artist->name,
            'email'=>$artist->email,
            'number'=>$artist->number,
            'whatsapp_number'=>$artist->whatsapp_number,
            'social_links'=>$artist->social_links,
            'details'=>$artist->details,
            'division'=>$artist->division,
            'songs' => $artist->songs->map(function ($song) {
                return [
                    'id' => $song->id,
                    'title' => $song->title,
                    'cover_path' => asset("storage/{$song->cover_path}"),
                    'song_path' => asset("storage/{$song->song_path}"),
                ];
            }),
        ]);
    }

    public function getArtists()
    {
        $artists = Artist::with('songs')->get()->map(function ($artist) {
            return [
                'id' => $artist->id,
                'user_id' => $artist->user_id,
                'profile_image' => asset("storage/{$artist->profile_image}"),
                'name' => $artist->name,
                'email' => $artist->email,
                'number' => $artist->number,
                'whatsapp_number' => $artist->whatsapp_number,
                'social_links' => $artist->social_links,
                'details' => $artist->details,
                'division' => $artist->division,
                'songs' => $artist->songs->map(function ($song) {
                    return [
                        'id' => $song->id,
                        'title' => $song->title,
                        'cover_path' => asset("storage/{$song->cover_path}"),
                        'song_path' => asset("storage/{$song->song_path}"),
                    ];
                }),
            ];
        });

        return response()->json($artists);
    }
    
}



