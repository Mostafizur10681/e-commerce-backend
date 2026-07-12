<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Http\Resources\API\V1\ContactSettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContactSettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = ContactSetting::first();

        if (!$settings) {
            $settings = new ContactSetting();
        }

        return response()->json([
            'success' => true,
            'message' => 'Contact settings retrieved successfully',
            'data' => new ContactSettingResource($settings)
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $settings = ContactSetting::first() ?: new ContactSetting();

        $data = $request->validate([
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'business_hours_weekday' => 'nullable|string|max:255',
            'business_hours_weekend' => 'nullable|string|max:255',
            'support_title' => 'nullable|string|max:255',
            'support_desc' => 'nullable|string|max:255',
            'support_phone' => 'nullable|string|max:255',
            'support_image' => 'nullable|string',
        ]);

        // Process support image if it is a base64 string
        if (isset($data['support_image'])) {
            $supportImage = $this->handleBase64Image($data['support_image'], 'contact');
            if ($supportImage) {
                // Delete old file if it was a stored upload
                if ($settings->support_image && str_contains($settings->support_image, 'storage/contact/')) {
                    $oldPath = str_replace(url('storage') . '/', '', $settings->support_image);
                    Storage::disk('public')->delete($oldPath);
                }
                $data['support_image'] = $supportImage;
            }
        }

        $settings->fill($data);
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact settings updated successfully',
            'data' => new ContactSettingResource($settings)
        ]);
    }

    private function handleBase64Image(?string $base64String, string $folder): ?string
    {
        if (!$base64String) {
            return null;
        }

        // Check if it is a valid base64 image data URI
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $data = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
                return null;
            }

            $data = base64_decode($data);
            if ($data === false) {
                return null;
            }

            $fileName = Str::random(20) . '.' . $type;

            Storage::disk('public')->put($folder . '/' . $fileName, $data);

            return url('storage/' . $folder . '/' . $fileName);
        }

        return null;
    }
}
