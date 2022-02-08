<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\PushNotificationTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class HandshakeController extends Controller
{
    public function handshake(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'push_notification_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(array(
                'status' => array(
                    'statusCode' => 400,
                    'message' => 'The given data was invalid.'
                ),
                'result' => array('errors' => $validator->errors())
            ), 400);
        }

        $push_tokens = PushNotificationTokens::where('user_id', Auth::id())->where('token', $request->push_notification_token)->first();
        if(!empty($push_tokens)){
            PushNotificationTokens::where('id', $push_tokens->id)->update([
                'user_id' => Auth::id(),
                'token' => $request->push_notification_token
            ]);
        }else{
            PushNotificationTokens::create([
                'user_id' => Auth::id(),
                'token' => $request->push_notification_token
            ]);
        }
        
        User::where('id', Auth::id())->update([
            'status' => 'active'
        ]);

        return response()->json([
            'status' => array(
                'statusCode' => 200,
                'message' => 'Successfully saved push notification token'
            ),
            'result' => array('token_info' => Auth::user()->token())
        ], 200);
    }
}
