<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Add this for Validator
use Illuminate\Support\Facades\Auth; // Needed for Auth
use App\Models\User; // Required to use the User model

class AuthenticationController extends Controller
{
    public function authenticate(Request $request) {
        // Apply Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // You can return a success response or token here
            $user = User::find(Auth::user()->id);
            $token = $user->createToken('token')->plainTextToken;
            return response()->json([
                'status' => true,
                'token' => $token,
                'id' => Auth::user()->id
            ]);

        } else {
            return response()->json([
                'status' => false,
                'message' => 'either email/password is incorrect'
            ]);
        }
    }

    public function logout(){
        $user = User :: find(Auth::user()->id);
        $user->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logout successfully.'
        ]);
    }
}
