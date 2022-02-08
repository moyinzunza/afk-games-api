<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use DateTime;

class AuthController extends Controller
{
    /**
     * Registro de usuario
     */
    public function signUp(Request $request)
    {

        //reward for newuser
        $reward_new_user = 10000;

        //plus rewards if referred
        $referred_reward = 5000;
        $referred_reward_new_user = 5000;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string',
            'username' => 'required|string|unique:users',
            'referred_by_username' => 'string'
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

        $uname = preg_replace('/[^A-Za-z0-9\-]/', '', $request->username);;
        $uname = str_replace(' ', '', $uname);
        $uname = str_replace('-', '', $uname);

        $check_uname = User::where('username', $uname)->first();
        if(!empty($check_uname)){
            return response()->json(array(
                'status' => array(
                    'statusCode' => 400,
                    'message' => 'The given data was invalid.'
                ),
                'result' => array('errors' => array('username' => array('The username '.$uname.' has already been taken.')))
            ), 400);
        }

        $referred_by_userid = null;

        if(!empty($request->referred_by_username)){
            $chek_referred = User::where('username', $request->referred_by_username)->first();
            if(!empty($chek_referred)){
                $reward_new_user += $referred_reward_new_user;
                $referred_by_userid = $chek_referred->id;
                $chek_referred->paid_resource += $referred_reward;
                $chek_referred->save();
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'username' => $uname,
            'referred_by_userid' => $referred_by_userid,
            'paid_resource' => $reward_new_user
        ]);

        return response()->json([
            'status' => array(
                'statusCode' => 201,
                'message' => 'Successfully created user!'
            )
        ], 201);
    }

    /**
     * Inicio de sesión y creación de token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'status' => array(
                    'statusCode' => 401,
                    'message' => 'Unauthorized'
                )
            ], 401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'status' => array(
                'statusCode' => 200,
                'message' => 'Login Successfully'
            ),
            'result' => array(
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
            )
        ]);
    }

    /**
     * Cierre de sesión (anular el token)
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => array(
                'statusCode' => 200,
                'message' => 'Successfully logged out'
            )
        ]);
    }

    /**
     * Obtener el objeto User como json
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => array(
                'statusCode' => 200,
                'message' => 'Successfully logged out'
            ),
            'result' => $request->user()
        ]);
    }

    public function inactivate_accounts(){

        $date = new DateTime();
        $date->modify("-7 day");
        User::where('updated_at', '<', $date)->update([
            'status' => 'inactive'
        ]);

    }
}
