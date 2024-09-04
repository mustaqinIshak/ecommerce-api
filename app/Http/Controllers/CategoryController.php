<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //

    public function all(Request $request) {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_products = $request->input('show_products');

        if($show_products == 1) {
            if($id) {
                $category = ProductCategory::with('products')->find($id);
                
                if($category) {
                    return ResponseFormatter::success(
                        $category, 
                        'Data Kategori Berhasil Diambil'
                    );
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data Kategori Tidak Ditemukan',
                        404
                    );
                }
            }
            $category = ProductCategory::with('products');

            if($name) {
                $category->where('name', 'like', '%'. $name .'%');
            }

            return ResponseFormatter::success(
                $category->paginate($limit),
                'Data Kategori Berhasil Diambil'
            );
        } else {
            if($id) {
                $category = ProductCategory::find($id);
                
                if($category) {
                    return ResponseFormatter::success(
                        $category, 
                        'Data Kategori Berhasil Diambil'
                    );
                } else {
                    return ResponseFormatter::error(
                        null,
                        'Data Kategori Tidak Ditemukan',
                        404
                    );
                }
            }
            $category = ProductCategory::query();

            if($name) {
                $category->where('name', 'like', '%'. $name .'%');
            }

            if($category) {
                return ResponseFormatter::success(
                    $category->paginate($limit),
                    'Data Kategori Berhasil Diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data Kategori Tidak Ditemukan',
                    404
                );
            }
        }
    }
}
