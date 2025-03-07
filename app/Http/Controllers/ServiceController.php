<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    //
    public function store(Request $request)
        {
            if (!Auth::check()) {
                return response()->json(['error' => 'يجب عليك تسجيل الدخول لاستخدام الخدمة'], 401);
            }

    
            $validatedType = $request->validate([
                'type' => 'required|string',
            ]);
            
            $existingService = Service::where('user_id', Auth::id())
        ->where('type', $validatedType['type'])
        ->first();

        if ($existingService) {
            return response()->json([
            'error' => 'لقد قمت بالفعل بطلب هذه الخدمة مسبقًا',
            ], 409);
        }

            $rules = ['data' => 'required|array'];
    
            $commonRules = [
                'data.name' => 'required|string|min:3|max:100',
                'data.email' => 'required|email|string|min:15',
                'data.phone' => 'required|string|regex:/^[0-9]{11}$/',
                'data.whatsapp_number' => 'required|string|regex:/^[0-9]{11}$/',
                'data.platform' => 'required|string',
                'data.details' => 'required|string',
            ];
    
            $artistServiceOptions = [
                'distribution',
                'music video',
                'promotion',
                'Writing a song',
                'Voice recording',
                'mix & master'
            ];
    
            switch ($validatedType['type']) {
                case 'account_creation':
                    $rules = array_merge($rules, $commonRules);
                    break;
    
                case 'verify social media accounts':
                case 'recover social media account':
                case 'Sponsored ads':
                    $rules = array_merge($rules, $commonRules, [
                        'data.social_media_account' => 'required|url',
                    ]);
                    break;
    
                case 'artist_service':
                    $rules = array_merge($rules, $commonRules, [
                        'data.social_media_account' => 'required|url',
                        'data.options' => 'required|array|min:1',
                        'data.options.*' => 'string|in:' . implode(',', $artistServiceOptions),
                    ]);
                    break;
    
                default:
                    return response()->json(['error' => 'نوع الخدمة غير مدعوم'], 400);
            }
    
            $validatedData = $request->validate($rules);
    
            $service = Service::create([
                'user_id' => Auth::id(),
                'type' => $validatedType['type'],
                'data' => json_encode($validatedData['data']),
            ]);
    
            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح',
                'service' => $service
            ], 201);
        }

        public function updateStatus(Request $request,$id){

             if(Auth::user()->role !== 'admin'){
                return response()->json(['message'=>'غير مصرح لك بالقيام بتحديث الحاله']);
            }

            $service=Service::findOrFail($id);

            if(!$service){
                return response()->json(['error'=>'الطلب غير موجود']);
            }

            $request->validate([
                'status'=>'required|string|in:pending,in progress,completed'
            ]);

            $service->update([
                'status'=>$request->status
            ]);

            return response()->json([
                'message'=>'تم تحديث الطلب بنجاح',
                'service'=>$service
            ],200);
        }


        public function getByType($type){

            if(Auth::user()->role !== 'admin'){
                return response()->json(['message'=>'غير مصرح لك بالقيام بتحديث الحاله']);
            }

            $services=Service::where('type',$type)->get();
            if($services->isEmpty()){
                return response()->json(['message'=>'لا يوجد طلبات لهذا النوع']);
            }
            return response()->json($services,200);
        }


        public function delete($id){

            $order=Service::findOrFail($id);

            if(!$order){
                return response()->json(['error'=>'الطلب غير موجود'],404);
            }

            if(Auth::user()->role !=='admin'){
                return response()->json(['error'=>'غير مصرح لك بحذف هذا الطلب'],404);
            }

            $order->delete();

            return response()->json(['message'=>'تم حذف هذا الطلب بنجاح'],200);
        }
        
    }