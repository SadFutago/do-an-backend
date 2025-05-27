<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    private const SEARCH_FIELDS = ['name'];
    private const FILTER_FIELDS = [
        'filterByPermission' => [
            'relation' => 'permissions',
            'column' => 'permissions.id'
        ],
    ];

    public function index(Request $request) {
        $query = Role::query()->with('permissions');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Role::onlyTrashed()->with('permissions');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function show(string $id) {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }
    public function store(Request $request) {
        $role = Role::create($request->all());
        if ($request->has('addedIds')) {
            $this->addIds($role, $request->input('addedIds'), 'permissions');
        }
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $role = Role::findOrFail($id);
        $role->update($request->all());
        if ($request->has('addedIds')) {
            $this->addIds($role, $request->input('addedIds'), 'permissions');
        }
        if ($request->has('deletedIds')) {
            $this->removeIds($role, $request->input('deletedIds'), 'permissions');
        }
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $role = Role::onlyTrashed()->findOrFail($id);
        $role->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $role = Role::onlyTrashed()->findOrFail($id);
        $role->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        $targetId = $request->input('targetId');

        switch ($action) {
            case 'delete':
                Role::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Role::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Role::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            case 'addPermission':
                if (!$targetId) {
                    return response()->json(['message' => 'Vui lòng chọn phân quyền để thực hiện hành động này.'], 400);
                }
                $roles = Role::whereIn('id', $ids)->get();
                foreach ($roles as $role) {
                    $role->permissions()->syncWithoutDetaching([$targetId]);
                }
                return response()->json(['message' => 'success'], 200);
            case 'removePermission':
                if (!$targetId) {
                    return response()->json(['message' => 'Vui lòng chọn phân quyền để thực hiện hành động này.'], 400);
                }
                $roles = Role::whereIn('id', $ids)->get();
                foreach ($roles as $role) {
                    $role->permissions()->detach($targetId);
                }
                return response()->json(['message' => 'success'], 200);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ.'], 400);
        }
    }
}
