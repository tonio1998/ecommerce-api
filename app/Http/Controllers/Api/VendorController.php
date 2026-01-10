<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $vendor = Vendor::where('user_id', auth()->id())->get();
        return json_encode($vendor);
    }

    public function store(Request $request)
    {
        return Vendor::create([
            'user_id' => auth()->id(),
            'shop_name' => $request->shop_name,
            'address' => $request->address,
            'is_active' => 1,
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::where('user_id', auth()->id())->findOrFail($id);
        $vendor->update($request->only(['name', 'address']));
        return $vendor;
    }

    public function toggle($id)
    {
        $vendor = Vendor::where('user_id', auth()->id())->findOrFail($id);
        $vendor->is_active = !$vendor->is_active;
        $vendor->save();
        return $vendor;
    }
}
