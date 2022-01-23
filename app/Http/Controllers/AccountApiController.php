<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


class AccountApiController extends Controller
{
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {

            $data['status'] = "error";
            $data['msg'] = "params missing";
            $data['data'] = $validator->errors();
            return response()->json($data, 200);

        }

        $user = User::where('email', $request->email)->first();
        if(!empty($user) && Hash::check($request->password, $user->password)){

            $token = Str::random(60);
            User::where('id', $user->id)->update([
                'remember_token' => $token
            ]);


            $data['status'] = "success";
            $data['msg'] = "login successfully";
            $data['token'] = $token;
            return response()->json($data, 200);
        

        }else{

            $data['status'] = "error";
            $data['msg'] = "email or password wrong";
            return response()->json($data, 401);

        }

    }


    public function signin(Request $request)
    {

        
    }
}
