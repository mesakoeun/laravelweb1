<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function list()
    {
        $products = DB::table('product_items')->get();
        return view('table', compact('products'));
    }

    public function save(Request $request)
    {
        $imagePath = null;

        if ($request->hasFile('image')) {
            $filename = time().'_'.$request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs('uploads', $filename, 'public');
            $imagePath = 'storage/'.$path;
        }

        if ($request->action == "Insert") {

            DB::table('product_items')->insert([
                'productname' => $request->productname,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'image' => $imagePath
            ]);

        } elseif ($request->action == "Update") {

            $data = [
                'productname' => $request->productname,
                'price' => $request->price,
                'quantity' => $request->quantity
            ];

            if ($imagePath) {
                $data['image'] = $imagePath;
            }

            DB::table('product_items')
                ->where('id', $request->id)
                ->update($data);
        }

        return response()->json(['status' => 'success']);
    }

    public function delete(Request $request)
    {
        DB::table('product_items')->where('id', $request->id)->delete();
        return response()->json(['status' => 'deleted']);
    }
}
