<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Http\Resources\API\V1\ContactMessageResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::query();

        // 1. Search Query
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%");
            });
        }

        // 2. Status Filter
        if ($request->filled('status') && $request->input('status') !== 'All') {
            $query->where('status', $request->input('status'));
        }

        // 3. Date Filter
        if ($request->filled('date') && $request->input('date') !== 'All Time') {
            $dateFilter = $request->input('date');
            switch ($dateFilter) {
                case 'Today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'This Week':
                    $query->where('created_at', '>=', Carbon::now()->subWeek());
                    break;
                case 'This Month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'This Year':
                    $query->whereYear('created_at', Carbon::now()->year);
                    break;
            }
        }

        // 4. Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 5. Statistics
        $today = Carbon::today();
        $stats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('status', 'Unread')->count(),
            'read' => ContactMessage::whereIn('status', ['Read', 'Replied'])->count(),
            'today' => ContactMessage::whereDate('created_at', $today)->count(),
        ];

        // 6. Pagination
        $limit = (int) $request->input('limit', 10);
        $paginator = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => ContactMessageResource::collection($paginator->items()),
            'total' => $paginator->total(),
            'totalPages' => $paginator->lastPage(),
            'currentPage' => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        $message = ContactMessage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Message submitted successfully',
            'data' => new ContactMessageResource($message)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Message retrieved successfully',
            'data' => new ContactMessageResource($message)
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|in:Unread,Read,Replied',
            'adminNote' => 'nullable|string',
        ]);

        if (isset($validated['adminNote'])) {
            $validated['admin_note'] = $validated['adminNote'];
            unset($validated['adminNote']);
        }

        $message->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Message updated successfully',
            'data' => new ContactMessageResource($message)
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully'
        ]);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:markAllAsRead',
        ]);

        if ($validated['action'] === 'markAllAsRead') {
            $updatedCount = ContactMessage::where('status', 'Unread')->update(['status' => 'Read']);
            return response()->json([
                'success' => true,
                'message' => 'All unread messages marked as read',
                'updatedCount' => $updatedCount
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid action'
        ], 400);
    }
}
