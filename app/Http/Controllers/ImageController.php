<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
class ImageController extends Controller
{
    // upload image to the backend folder
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif',
        ]);

        $image = $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->store('images');
    }
    //  up the image to data base
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalName();
        $image->move(public_path('images'), $imageName);
        return response()->json([
            'message' => 'Image uploaded successfully',
            'image' => $imageName
        ]);
    }

    //  update profile image for user
    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        if ($user->image) {
            $user->image->delete();
        }
        $uploadedImage = $request->file('image');
        $imageName = time() . '.' . $uploadedImage->getClientOriginalExtension();
        $uploadedImage->move(public_path('images'), $imageName);

        $image = new Image();
        $image->path = $imageName;
        $image->imageable_id = $user->id;
        $image->imageable_type = get_class($user);
        $image->save();

        return response()->json([
            'status' => 200,
            'message' => 'Profile image updated successfully',
            'image' => [
                'id' => $image->id,
                'path' => 'images/' . $image->path,
            ],
        ]);
    }
}
