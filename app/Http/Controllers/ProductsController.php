<?php

namespace App\Http\Controllers;

use App\Models\CategoriesModel;
use App\Models\ProductsModel;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function create(Request $request)
{
    $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
    $user = User::find($data->id);

    // Pengguna diautentikasi, dilanjutkan dengan validasi dan pembuatan data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|integer',
        'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Memvalidasi jenis dan ukuran file
        'categories_id' => 'required|string|max:255', // Terima kategori sebagai nama atau id
        'expired_at' => 'required|date',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->messages())->setStatusCode(422);
    }

    $validated = $validator->validated();

    // Menangani input kategori (nama atau ID)
    $categoryInput = $validated['categories_id'];
    if (is_numeric($categoryInput)) {
        // Jika kategori adalah numerik, perlakukan sebagai ID
        $category = CategoriesModel::find($categoryInput);
    } else {
        // Jika tidak, perlakukan itu sebagai nama
        $category = CategoriesModel::firstOrCreate(['name' => $categoryInput]);
    }

    if (!$category) {
        return response()->json(['message' => 'Invalid category'], 422);
    }

    $validated['categories_id'] = $category->id;

    // Mengambil ID pengguna dan email
    $userId = $user->id;
    $userEmail = $user->email;

    // Pastikan ID pengguna tidak null sebelum melanjutkan
    if (!$userId) {
        return response()->json(['message' => 'User ID not found'], 401);
    }

    // Menangani upload gambar
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imagePath = $image->store('images', 'public'); // Simpan gambar di direktori 'public/images'
        $validated['image'] = $imagePath; // Simpan path ke database
    }

    // Menambahkan email pengguna sebagai modified_by
    $validated['modified_by'] = $userEmail;

    // Buat produk
    $product = ProductsModel::create($validated);

    return response()->json([
        'message' => "Data Berhasil Disimpan",
        'data' => $product
    ], 200);
}


    public function read()
    {
        $products = ProductsModel::all();
        return response()->json([
            'msg' => 'Data Produk Keseluruhan',
            'data' => $products
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $data = JWT::decode($request->bearerToken(), new Key(env('JWT_SECRET_KEY'), 'HS256'));
        $user = User::find($data->id);
    
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|integer',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', // Memvalidasi jenis dan ukuran file
            'categories_id' => 'sometimes|string|max:255', // Terima kategori sebagai nama atau id
            'expired_at' => 'sometimes|date',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->messages())->setStatusCode(422);
        }
    
        $validated = $validator->validated();
    
        // Menangani input kategori (nama atau ID)
        if (isset($validated['categories_id'])) {
            $categoryInput = $validated['categories_id'];
            if (is_numeric($categoryInput)) {
                // Jika kategori adalah numerik, perlakukan sebagai ID
                $category = CategoriesModel::find($categoryInput);
            } else {
                // Jika tidak, perlakukan itu sebagai name
                $category = CategoriesModel::firstOrCreate(['name' => $categoryInput]);
            }
    
            if (!$category) {
                return response()->json(['message' => 'Invalid category'], 422);
            }
    
            $validated['categories_id'] = $category->id;
        }
    
        // Mengambil ID pengguna dan email
        $userId = $user->id;
        $userEmail = $user->email;
    
        // Pastikan ID pengguna tidak null sebelum melanjutkan
        if (!$userId) {
            return response()->json(['message' => 'User ID not found'], 404);
        }
    
        // Menambahkan email pengguna sebagai modified_by
        $validated['modified_by'] = $userEmail;
    
        $product = ProductsModel::find($id);
    
        if ($product) {
            // Menangani upload gambar
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
    
                $image = $request->file('image');
                $imagePath = $image->store('images', 'public'); // Simpan gambar baru
                $validated['image'] = $imagePath; // Memperbarui path gambar dalam data yang divalidasi
            }
    
            $product->update($validated);
    
            return response()->json([
                'msg' => 'Data dengan id: ' . $id . ' berhasil diupdate',
                'data' => $product
            ], 200);
        }
    
        return response()->json([
            'msg' => 'Data dengan id: ' . $id . ' tidak ditemukan'
        ], 404);
    }    

    public function delete($id)
    {
        $product = ProductsModel::find($id);

        if ($product) {
            // Hapus gambar dari penyimpanan jika ada
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            return response()->json([
                'msg' => 'Data produk dengan ID: ' . $id . ' berhasil dihapus'
            ], 200);
        }

        return response()->json([
            'msg' => 'Data produk dengan ID: ' . $id . ' tidak ditemukan',
        ], 404);
    }
}