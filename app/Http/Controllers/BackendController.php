<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackendController extends Controller
{
	//
	public function handle(Request $request)
    {
        $action = $request->input('action');

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->uploadImage($request->file('image'));
        }

        if ($action === "Insert") {

            DB::table('product_items')->insert([
                'productname' => $request->productname,
                'price'       => $request->price,
                'quantity'    => $request->quantity,
                'image'       => $imagePath,
                'created_at'  => now(),
                'updated_at'  => now()
            ]);

        } elseif ($action === "Update") {

            $data = [
                'productname' => $request->productname,
                'price'       => $request->price,
                'quantity'    => $request->quantity,
                'updated_at'  => now()
            ];

            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            DB::table('product_items')
                ->where('id', $request->id)
                ->update($data);

        } elseif ($action === "Delete") {

            DB::table('product_items')
                ->where('id', $request->id)
                ->delete();
        }

        return redirect('/'); // same as header("Location: index.php")
    }


    private function uploadImage($file)
    {
        $filename = 'img_' . date('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // store in storage/app/public/uploads
        $path = $file->storeAs('uploads', $filename, 'public');

        return 'storage/' . $path; // public path
    }
    public function list()
{
    $products = DB::table('product_items')->get();
    return view('products_table', compact('products'));
}
}
