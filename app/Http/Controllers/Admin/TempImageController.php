<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TempImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImageController extends Controller
{
    public function store(Request $request)
    {
        // Validation for the image
        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpeg,jpg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors('image')
            ]);
        }

        $image = $request->image;

        $ext = $image->getClientOriginalExtension();
        $imageName = strtotime('now') . '.' . $ext; // ✅ Fixed $text to $ext

        // Save image to temp_images table
        $model = new TempImage();
        $model->name = $imageName;
        $model->save();

        // save image in uploads/temp directory
        $image->move(public_path('uploads/temp'), $imageName);

        // create small thumbnail here
        $sourcePath = public_path('uploads/temp/' . $imageName);
        $destPath = public_path('uploads/temp/thumb/' . $imageName);

        $manager = new ImageManager(Driver::class);
        $image = $manager->read($sourcePath);
        $image->coverDown(300, 300);
        $image->save($destPath); //

        return response()->json([
            'status' => true,
            'data' => $model,
            'message' => 'Image uploaded successfully'
        ]);
    }
}
