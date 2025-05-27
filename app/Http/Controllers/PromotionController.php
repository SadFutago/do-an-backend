<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotion;

class PromotionController extends Controller
{
    private const SEARCH_FIELDS = ['name'];
    private const FILTER_FIELDS = [
        'filterByStatus' => ['column' => 'status'],
        'filterByUnapproved' => ['column' => 'approved_by'],
    ];

    public function approve(Request $request, string $id) {
        $approverId = $request->user_id;
        $promotion = Promotion::findOrFail($id);
        $promotion->approved_by = $approverId;
        $promotion->save();
        return response()->json(['message' => 'success'], 200);
    }

    public function index(Request $request) {
        $query = Promotion::with([
            'creator:id,name',
            'approver:id,name',
        ])->orderBy('updated_at', 'desc');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Promotion::onlyTrashed()->with([
            'creator:id,name',
            'approver:id,name',
        ])->orderBy('updated_at', 'desc');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function show(string $id) {
        $promotion = Promotion::findOrFail($id);
        return response()->json($promotion);
    }
    public function store(Request $request) {
        $creatorId = $request->user_id;
        Promotion::create($request->all() + ['created_by' => $creatorId]);
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $promotion = Promotion::findOrFail($id);
        $promotion->update($request->all());
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $promotion = Promotion::onlyTrashed()->findOrFail($id);
        $promotion->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $promotion = Promotion::onlyTrashed()->findOrFail($id);
        $promotion->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        $targetId = $request->input('targetId');
        switch ($action) {
            case 'delete':
                Promotion::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Promotion::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Promotion::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            case 'setStatus':
                Promotion::whereIn('id', $ids)->update(['status' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ.'], 400);
        }
    }
}
