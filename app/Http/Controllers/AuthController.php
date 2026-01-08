<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'nullable',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('profiles', 'public');
        }

        $user->profile()->create([
            'phone' => $request->phone,
            'address' => $request->address,
            'image' => $imagePath,
            'type' => $request->type,
        ]);

        return response()->json([
            "user" => $user->load('profile'),
            "message" => "User Registered Successfully",
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid email or password'
            ], 401);
        }
        $sqlPermission = " SELECT " .
            "       p.*" .
            " FROM permissions p" .
            " INNER JOIN permission_roles pr ON p.id = pr.permission_id" .
            "  INNER JOIN roles r ON pr.role_id = r.id" .
            "  INNER JOIN user_roles ur ON r.id  = ur.role_id" .
            "  WHERE ur.user_id = ?;";
        $permission = DB::select($sqlPermission, [JWTAuth::user()->id]);

        // protect route api
        $payload = [
            'user' => JWTAuth::user()->load('profile'),
            "permission" => $permission,
        ];
        $token = JWTAuth::claims($payload)->fromUser(JWTAuth::user());
        return response()->json([
            'access_token' => $token,
            'message' => 'Login Successfully',
            'user' => JWTAuth::user()->load('profile'),
            'permission' => $permission,
        ]);
    }
}
