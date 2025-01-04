<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required|unique:users,phone_number',
            'password' => 'required|min:8|confirmed'
        ], [
            'phone_number.unique' => 'phone_number is not unique'
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        $defaultImagePath = '1732817786.Default-Profile-Picture.png';
        $user->image()->create([
            'path' => $defaultImagePath,
        ]);

        $token = $user->createToken('API TOKEN')->plainTextToken;


        $user->load('image');
        $profileImagePath = $user->image ? 'images/' . $user->image->path : null;

        return response()->json([
            'status' => 200,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone_number' => $user->phone_number,
                    'image' => [
                        'id' => $user->image->id,

                        'path' => $profileImagePath,
                    ],
                ],
                'token' => $token,
            ],
            'message' => 'User Registered Successfully',
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'exists:users,phone_number',
            'password' => 'required',
        ]);
        if (!Auth::attempt($request->only(['phone_number', 'password']))) {
            $message = 'phone_number & Password do not match with our records.';
            return response()->json([
                'data' => [],
                'status' => 0,
                'message' => $message,
            ], 500);
        }

        $user = User::where('phone_number', '=', $request['phone_number'])->first();
        $token = $user->createToken('API TOKEN')->plainTextToken;

        $data = [];
        $data['user'] = $user;
        $data['token'] = $token;
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => 'User Logged in successfuly'
        ]);

    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User Logged out successfully'
        ]);

    }
    public function ShowUserProfile()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'User not Found',
            ], 404);
        }
        $profileImage = $user->image ? 'images/' . $user->image->path : null;
        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone_number' => $user->phone_number,
            'address' => $user->address ? $user->address->location : null,
            'image' => $profileImage,
        ]);
    }



    public function UpdateProfile(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $updateData = [
            'first_name' => $request->filled('first_name') ? $request->first_name : $user->first_name,
            'last_name' => $request->filled('last_name') ? $request->last_name : $user->last_name,
            'phone_number' => $request->filled('phone_number') ? $request->phone_number : $user->phone_number,
        ];

        try {
            $user = User::find($user->id);
            $user->update($updateData);

            $profileImage = $user->image ? 'images/' . $user->image->path : null;

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone_number' => $user->phone_number,
                    'profile_image' => $profileImage,
                ],
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'This phone number is already in use. Please use a different number.',
                ], 400);
            }
            return response()->json([
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }



}
