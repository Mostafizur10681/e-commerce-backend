<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Http\Resources\API\V1\BannerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Banner::query();

        if ($request->boolean('public')) {
            $query->where('is_active', true);
        }

        if ($request->filled('menu')) {
            $query->where('menu_location', $request->input('menu'));
        }

        $banners = $query->orderBy('order', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Banners retrieved successfully',
            'data' => BannerResource::collection($banners)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required',
            'badge' => 'nullable|string|max:255',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'menu_location' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $validated['image'] = url('storage/' . $path);
        } else if (is_string($request->input('image'))) {
            $validated['image'] = $request->input('image');
        }

        $banner = Banner::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully',
            'data' => new BannerResource($banner)
        ], 201);
    }

    public function show(Banner $banner): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Banner retrieved successfully',
            'data' => new BannerResource($banner)
        ]);
    }

    public function update(Request $request, Banner $banner): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable',
            'badge' => 'nullable|string|max:255',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'menu_location' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('banners', 'public');
            $validated['image'] = url('storage/' . $path);
            
            // Delete old image if it's local
            if ($banner->image && str_contains($banner->image, 'storage/banners/')) {
                $oldPath = str_replace(url('storage') . '/', '', $banner->image);
                Storage::disk('public')->delete($oldPath);
            }
        } else if (is_string($request->input('image'))) {
            $validated['image'] = $request->input('image');
        } else {
            // keep old image if no new file/string is uploaded
            unset($validated['image']);
        }

        $banner->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => new BannerResource($banner)
        ]);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        if ($banner->image && str_contains($banner->image, 'storage/banners/')) {
            $oldPath = str_replace(url('storage') . '/', '', $banner->image);
            Storage::disk('public')->delete($oldPath);
        }

        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully'
        ]);
    }
}
