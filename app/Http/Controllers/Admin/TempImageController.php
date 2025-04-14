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
        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpeg,jpg,gif'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->first('image')
            ]);
        }

        $image = $request->file('image');

        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;

            // Save to temp_images table
            $model = new TempImage();
            $model->name = $imageName;
            $model->save();

            // Move image to public/uploads/temp
            $image->move(public_path('uploads/temp'), $imageName);

            //create small thumb nail here

            $sourcePath = public_path('uploads/temp/'.$imageName);
            $destinationPath = public_path('uploads/temp/thumb/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);
            $image->coverDown(300,300);
            $image->save($destinationPath);

            return response()->json([
                'status' => true,
                'data' => $model,
                'message' => 'Image uploaded successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'No image uploaded.'
        ]);
    }
}
