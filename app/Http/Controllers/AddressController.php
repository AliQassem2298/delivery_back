<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{

    public function updateAddress(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized user',
            ]);
        }
        $validator = Validator::make($request->all(), [
            'location' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'data' => $validator->errors(),
                'message' => 'Incorrect or missing information',
                'status' => 400
            ]);
        }
        $address = $user->address;
        if ($address) {
            $address->update([
                'location' => $request->location,
            ]);
            $message = 'Address updated successfully';
        } else {
            $address = Address::create([
                'location' => $request->location,
                'user_id' => $user->id,
            ]);
            $message = 'Address created successfully';
        }
        return response()->json([
            'status' => 200,
            'message' => $message,
            'address' => $address,
        ]);
    }
    }

