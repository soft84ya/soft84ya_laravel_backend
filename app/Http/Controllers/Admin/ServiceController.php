<?php


namespace App\Http\Controllers\Admin;

use App\Models\Service;
use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use League\Uri\UriTemplate\Template;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => true,
            'data' => $services
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:services,slug',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $model = new Service();
        $model->title = $request->title;
        $model->slug = Str::slug($request->slug);
        $model->short_desc = $request->short_desc ?? null;
        $model->content = $request->content ?? null;
        $model->image = $request->image ?? null;
        $model->status = $request->status ?? 1;
        $model->save();


        if ($request->imageId > 0) {

            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {

                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') .  $model->id . '.' . $ext;

                // ✅ First thumbnail (small)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/services/small/' . $fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath);
                $image->coverDown(500, 600);
                $image->save($destPath);


                // ✅ Second thumbnail (large)
                $destPathLarge = public_path('uploads/services/large/' . $fileName); // ✅ Corrected path
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200);
                $image->save($destPathLarge);
                $model->image =  $fileName;
                $model->save();


            }
        }


        return response()->json([
            'status' => true,
            'message' => 'Service added successfully'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Check if service exists
        $service = Service::find($id);

        // If no service is found, return a response with status false and message
        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found',
            ], 404); // Returning 404 status code for not found
        }

        // If service found, return the service data
        return response()->json([
            'status' => true,
            'data' => $service,
        ], 200); // Returning 200 status code for success
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if ($service == null) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ]);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required|unique:services,slug,' . $id

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }


        $service->title = $request->title;
        $service->slug = Str::slug($request->slug);
        $service->short_desc = $request->short_desc ?? null;
        $service->content = $request->content ?? null;
        $service->status = $request->status ?? 1;
        $service->image = $request->image ?? null;
        $service->save();

        //save temp image here

        if ($request->imageId > 0) {
            $oldImage = $service->image;
            $tempImage = TempImage::find($request->imageId);
            if ($tempImage != null) {

                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now') . $service->id . '.' . $ext;

                // ✅ First thumbnail (small)
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);
                $destPath = public_path('uploads/services/small/' . $fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath);
                $image->coverDown(500, 600);
                $image->save($destPath);


                // ✅ Second thumbnail (large)
                $destPathLarge = public_path('uploads/services/large/' . $fileName); // ✅ Corrected path
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200);
                $image->save($destPathLarge);
                $service->image =  $fileName;
                $service->save();

                if ($oldImage != '') {
                    File::delete(public_path('uploads/services/large/' . $oldImage));
                    File::delete(public_path('uploads/services/small/' . $oldImage));
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Service update successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json([
                'status' => false,
                'message' => 'Service not found'
            ]);
        }

        $service->delete();

        return response()->json([
            'status' => true,
            'message' => 'Service deleted successfully'
        ]);
    }
}
