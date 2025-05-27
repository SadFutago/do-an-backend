<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    private const SEARCH_FIELDS = ['name'];

    public function index(Request $request) {
        $query = Permission::query();
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Permission::onlyTrashed();
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function show(string $id) {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }
    public function store(Request $request) {
        Permission::create($request->all());
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $permission = Permission::findOrFail($id);
        $permission->update($request->all());
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $permission = Permission::findOrFail($id);
        $permission->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $permission = Permission::onlyTrashed()->findOrFail($id);
        $permission->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $permission = Permission::onlyTrashed()->findOrFail($id);
        $permission->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Permission::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Permission::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Permission::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ.'], 400);
        }
    }
}
