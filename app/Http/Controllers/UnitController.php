<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    private const SEARCH_FIELDS = ['name'];

    public function index(Request $request) {
        $query = Unit::query();
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Unit::onlyTrashed();
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function show(string $id) {
        $unit = Unit::findOrFail($id);
        return response()->json($unit);
    }
    public function store(Request $request) {
        Unit::create($request->all());
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $unit = Unit::findOrFail($id);
        $unit->update($request->all());
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $unit = Unit::findOrFail($id);
        $unit->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $unit = Unit::onlyTrashed()->findOrFail($id);
        $unit->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $unit = Unit::onlyTrashed()->findOrFail($id);
        $unit->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Unit::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Unit::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Unit::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ.'], 400);
        }
    }
}
