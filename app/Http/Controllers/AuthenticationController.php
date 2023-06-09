<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken('user login')->plainTextToken;
    }

    public function logout(Request $request)
    {
        //$user->tokens()->where('id', $tokenId)->delete();
        
        $user = Auth::user();

        $user->currentAccessToken()->delete();

        return Response(['data => User Logout Successfully'],200);
        
    }

    public function me(Request $request)
    {

        return response()->json(Auth::user());
    }

    public function register(Request $request)
    {
        /*$validated = $request->validate([
            'username' => 'required|unique:posts|max:255',
            'password' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
        ]);*/

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|min:2|max:100',
            'password' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
        ]);

        if ($validator->fails()) {
            /*return redirect('post/create')
                        ->withErrors($validator)
                        ->withInput();*/
            return response()->json([
               'message'=>'Validation Error',
               'errors' => $validator->errors()
                ],422);

        }

        $user = User::create([
            'username'=>$request->username,
            'password'=>Hash::make($request->password),
            'firstname'=>$request->firstname,
            'lastname'=>$request->lastname
        ]);

        return response()->json([
            'message'=>'Registration Successfully',
            'data' => $user
             ],200);

        
    }
}
