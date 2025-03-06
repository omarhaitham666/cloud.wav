<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\ArtistRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ArtistRequestController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        if (Artist::where('user_id', $userId)->exists()) {
            return response()->json(['error' => 'لقد تمت الموافقة على طلبك بالفعل ولا يمكنك إرسال طلب آخر'], 403);
        }
    

    if (ArtistRequest::where('user_id', $userId)->exists()) {
        return response()->json(['error' => 'لقد قمت بإرسال طلب مسبقًا ولا يمكنك إرسال طلب آخر'], 403);
    }
    

        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
            'name' => 'required|string',
            'email' => 'required|email',
            'number' => 'required|string|digits:11',
            'whatsapp_number' => 'required|string|regex:/^\d{11}$/',
            'details' => 'required|string',
            'division' => 'required|in:rap,pop,jazz,rock,Mahraganat',
            'social_links' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $imageName = time().'_'.$request->file('profile_image')->getClientOriginalName();
        $imagePath = $request->file('profile_image')->storeAs('uploads/artist', $imageName, 'public');

        $ArtistRequest = ArtistRequest::create([
            'user_id' => $userId,
            'profile_image' => $imagePath,
            'name' => $request->name,
            'email' => $request->email,
            'number' => $request->number,
            'whatsapp_number' => $request->whatsapp_number,
            'details' => $request->details,
            'division' => $request->division,
            'social_links' => $request->social_links,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم إرسال الطلب بنجاح',
            'request' => $ArtistRequest
        ], 201);
    }

    public function index()
    {
        $requests = ArtistRequest::with('user')->get();
        return response()->json($requests);
    }

public function updateStatus(Request $request, $id)
{
    $ArtistRequest = ArtistRequest::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'status' => 'required|in:approved,rejected',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $user = User::findOrFail($ArtistRequest->user_id);

    if ($request->status === 'approved') {
        if ($user->role !== 'artist') {
            $user->role = 'artist';
            $user->save();
        }

        
        Artist::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $ArtistRequest->name,
                'email' => $ArtistRequest->email,
                'number' => $ArtistRequest->number,
                'whatsapp_number' => $ArtistRequest->whatsapp_number,
                'details' => $ArtistRequest->details,
                'profile_image' => $ArtistRequest->profile_image,
                'division' => $ArtistRequest->division,
                'social_links' => $ArtistRequest->social_links,
            ]
        );

        
        $ArtistRequest->delete();
    } else {
        if ($user->role === 'artist') {
            $user->role = 'user';
            $user->save();
        }
        $ArtistRequest->delete();
    }

    return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح']);
}


}
