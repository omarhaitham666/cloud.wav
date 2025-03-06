<?php

namespace App\Http\Controllers;

use App\Models\FamousArtistRequest;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FamousArtistRequestsController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        $existingRequest = FamousArtistRequest::where('user_id', $userId)->first();
        if ($existingRequest) {
            return response()->json(['error' => 'لقد قمت بإرسال طلب مسبقًا ولا يمكنك إرسال طلب آخر'], 403);
        }

        if (Artist::where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'لقد تمت الموافقة على طلبك بالفعل ولا يمكنك إرسال طلب آخر'], 403);
        }

        $validator = Validator::make($request->all(), [
            'famous_profile_image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'famous_name' => 'required|string',
            'famous_email' => 'required|email',
            'famous_number' => 'required|string|digits:11',
            'famous_whatsapp_number' => 'required|string|regex:/^\d{11}$/',
            'famous_details' => 'required|string',
            'famous_id_card_image'=>'required|image|mimes:jpeg,png,jpg|max:2048',
            'famous_division' => 'required|in:rap,pop,jazz,rock,Mahraganat',
            'famous_social_links' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $imageName = time().'_'.$request->file('famous_profile_image')->getClientOriginalName();
        $imagePath = $request->file('famous_profile_image')->storeAs('uploads/famous_artist', $imageName, 'public');


        $idCardName = time().'_'.$request->file('famous_id_card_image')->getClientOriginalName();
$idCardPath = $request->file('famous_id_card_image')->storeAs('uploads/id_cards', $idCardName, 'public');


        $famousArtistRequest = FamousArtistRequest::create([
            'user_id' => $userId,
            'famous_profile_image' => $imagePath,
            'famous_name' => $request->famous_name,
            'famous_email' => $request->famous_email,
            'famous_number' => $request->famous_number,
            'famous_whatsapp_number' => $request->famous_whatsapp_number,
            'famous_details' => $request->famous_details,
            'famous_id_card_image'=>$idCardPath,
            'famous_division' => $request->famous_division,
            'famous_social_links' => $request->famous_social_links,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم إرسال الطلب بنجاح',
            'request' => $famousArtistRequest
        ], 201);
    }

public function index()
    {
        $requests = FamousArtistRequest::with('user')->get();
        return response()->json($requests);
    }

public function updateStatus(Request $request, $id)
{
    $famousArtistRequest = FamousArtistRequest::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'status' => 'required|in:approved,rejected',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $user = User::findOrFail($famousArtistRequest->user_id);

    if ($request->status === 'approved') {
        if ($user->role !== 'artist') {
            $user->role = 'artist';
            $user->save();
        }

        
        $artist = Artist::where('user_id', $user->id)->first();

        if ($artist) {
            
            $artist->update(['type' => 'famous']);
        } else {
            
            Artist::create([
                'user_id' => $user->id,
                'name' => $famousArtistRequest->famous_name,
                'email' => $famousArtistRequest->famous_email,
                'number' => $famousArtistRequest->famous_number,
                'whatsapp_number' => $famousArtistRequest->famous_whatsapp_number,
                'details' => $famousArtistRequest->famous_details,
                'division' => $famousArtistRequest->famous_division,
                'social_links' => $famousArtistRequest->famous_social_links,
                'profile_image' => $famousArtistRequest->famous_profile_image,
                'famous_id_card_image' => $famousArtistRequest->famous_id_card_image,
                'type' => 'famous',
            ]);
        }

        
        $famousArtistRequest->delete();
    } else {
        if ($user->role === 'artist') {
            $user->role = 'user';
            $user->save();
        }

        
        Storage::delete(['public/' . $famousArtistRequest->famous_profile_image, 'public/' . $famousArtistRequest->famous_id_card_image]);


        $famousArtistRequest->delete();
    }

    return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح']);
}

}
