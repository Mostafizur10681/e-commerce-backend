<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    /**
     * Public: Return the footer settings.
     */
    public function index(): JsonResponse
    {
        $settings = FooterSetting::getOrCreate();

        return response()->json([
            'success' => true,
            'message' => 'Footer settings retrieved successfully',
            'data'    => $settings,
        ]);
    }

    /**
     * Admin: Update footer settings.
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'store_name'        => 'nullable|string|max:100',
            'store_icon'        => 'nullable|string|max:20',
            'store_description' => 'nullable|string|max:500',
            'copyright_text'    => 'nullable|string|max:255',
            'social_links'      => 'nullable|array',
            'social_links.*.name' => 'required_with:social_links|string|max:50',
            'social_links.*.icon' => 'required_with:social_links|string|max:50',
            'social_links.*.url'  => 'required_with:social_links|string|max:255',
            'quick_links'       => 'nullable|array',
            'quick_links.*.label' => 'required_with:quick_links|string|max:100',
            'quick_links.*.path'  => 'required_with:quick_links|string|max:255',
            'service_links'     => 'nullable|array',
            'service_links.*.label' => 'required_with:service_links|string|max:100',
            'service_links.*.path'  => 'required_with:service_links|string|max:255',
            'contact_address'   => 'nullable|string|max:255',
            'contact_phone'     => 'nullable|string|max:50',
            'contact_email'     => 'nullable|email|max:100',
            'contact_hours'     => 'nullable|string|max:100',
        ]);

        $settings = FooterSetting::getOrCreate();
        $settings->fill($data);
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Footer settings updated successfully',
            'data'    => $settings,
        ]);
    }
}
