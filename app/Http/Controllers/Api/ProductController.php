<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = $request->vendor_id;
        $perPage = $request->get('per_page', 50);

        return Product::where('vendor_id', $vendorId)
            ->where('archived', 0)
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function sync(Request $request)
    {
        $vendorId = $request->vendor_id;
        $afterId = $request->get('after_id', 0);

        return Product::where('vendor_id', $vendorId)
            ->where('archived', 0)
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get();
    }



}
