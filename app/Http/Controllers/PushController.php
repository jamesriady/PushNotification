<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Notifications\PushDemo;
use Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;

class PushController extends Controller
{

	use ValidatesRequests;

    public function __construct(){
      $this->middleware('auth');
    }

    /**
     * Store the PushSubscription.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request,[ 'endpoint' => 'required' ]);
        $request->user()->updatePushSubscription(
        	$request->endpoint, 
        	$request->publicKey, 
        	$request->authToken, 
        	$request->contentEncoding
        );
        
        return response()->json(['success' => true],200);
    }

    public function destroy(Request $request)
    {
        $this->validate($request,[ 'endpoint' => 'required' ]);
      	$request->user()->deletePushSubscription($request->endpoint);

      	return response()->json(null, 204);
    }
    
}