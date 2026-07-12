<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use App\Http\Resources\API\V1\AboutPageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AboutPageController extends Controller
{
    public function index(): JsonResponse
    {
        $about = AboutPage::first();
        
        if (!$about) {
            $about = new AboutPage();
        }

        return response()->json([
            'success' => true,
            'message' => 'About page content retrieved successfully',
            'data' => new AboutPageResource($about)
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $about = AboutPage::first() ?: new AboutPage();

        $data = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string',
            'hero_badge' => 'nullable|string|max:255',
            'story_title' => 'nullable|string|max:255',
            'story_badge' => 'nullable|string|max:255',
            'story_description_1' => 'nullable|string',
            'story_description_2' => 'nullable|string',
            'story_since' => 'nullable|string|max:255',
            'story_points' => 'nullable|array',
            'story_image' => 'nullable|string',
            'mission_title' => 'nullable|string|max:255',
            'mission_description' => 'nullable|string',
            'vision_title' => 'nullable|string|max:255',
            'vision_description' => 'nullable|string',
            'why_choose_badge' => 'nullable|string|max:255',
            'why_choose_title' => 'nullable|string|max:255',
            'why_choose_subtitle' => 'nullable|string',
            'features' => 'nullable|array',
            'stats' => 'nullable|array',
            'team_badge' => 'nullable|string|max:255',
            'team_title' => 'nullable|string|max:255',
            'team_subtitle' => 'nullable|string',
            'team' => 'nullable|array',
        ]);

        // Process story image if it is a base64 string
        if (isset($data['story_image'])) {
            $storyImage = $this->handleBase64Image($data['story_image'], 'about');
            if ($storyImage) {
                // Delete old file if it was a stored upload
                if ($about->story_image && str_contains($about->story_image, 'storage/about/')) {
                    $oldPath = str_replace(url('storage') . '/', '', $about->story_image);
                    Storage::disk('public')->delete($oldPath);
                }
                $data['story_image'] = $storyImage;
            }
        }

        // Process team member images if they are base64 strings
        if (isset($data['team']) && is_array($data['team'])) {
            $teamMembers = $data['team'];
            $oldTeamMembers = $about->team ?? [];

            foreach ($teamMembers as $index => &$member) {
                if (isset($member['image'])) {
                    $memberImage = $this->handleBase64Image($member['image'], 'about');
                    if ($memberImage) {
                        // Find matching old member and delete old image if it was a stored upload
                        $oldMember = $oldTeamMembers[$index] ?? null;
                        if ($oldMember && isset($oldMember['image']) && str_contains($oldMember['image'], 'storage/about/')) {
                            $oldPath = str_replace(url('storage') . '/', '', $oldMember['image']);
                            Storage::disk('public')->delete($oldPath);
                        }
                        $member['image'] = $memberImage;
                    }
                }
            }
            $data['team'] = $teamMembers;
        }

        $about->fill($data);
        $about->save();

        return response()->json([
            'success' => true,
            'message' => 'About page content updated successfully',
            'data' => new AboutPageResource($about)
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
