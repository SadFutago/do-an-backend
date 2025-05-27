<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    private const SEARCH_FIELDS = ['phone', 'name', 'username'];
    private const FILTER_FIELDS = [
        'filterByRole' => ['column' => 'role_id'],
        'filterByStatus' => ['column' => 'status'],
    ];

    public function updateProfile(Request $request) {
        $userId = $request->user_id;
        $user = User::findOrFail($userId);
        $user->update($request->only([
            'name', 'email', 'phone', 'date_of_birth', 'city_id', 'district_id', 'ward_id',  'address', 'avatar_id'
        ]));
        if ($request->hasFile('avatar')) {
            if ($user->avatar_id) {
                $oldImage = $user->avatar;
                if ($oldImage) {
                    $path = str_replace('/storage', '', $oldImage->path);
                    Storage::disk('public')->delete($path);
                    $oldImage->delete();
                }
            }
            $storagePath = "User/{$user->id}";
            $file = $request->file('avatar');
            $path = $file->store($storagePath, 'public');
            $image = $user->avatar()->create([
                'path' => Storage::url($path),
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
            $user->avatar_id = $image->id;
            $user->save();
        }
        return response()->json($user->load(['avatar:id,path']));
    }

    public function resetPassword(Request $request) {
        $userId = $request->user_id;
        $user = User::findOrFail($userId);
        if (!Hash::check($request->oldPassword, $user->password)) {
            return response()->json(['message' => 'Mật khẩu cũ không chính xác'], 422);
        }
        $user->update([
            'password' => $request->newPassword
        ]);
        return response()->json(['message' => 'success']);
    }

    public function index(Request $request) {
        $query = User::query()->with('role:id,name');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function trashed(Request $request) {
        $query = User::onlyTrashed()->with('role:id,name');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }
    public function show(string $id) {
        $user = User::findOrFail($id);
        return response()->json($user);
    }
    public function store(Request $request) {
        User::create($request->all());
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        $targetId = $request->input('targetId');

        switch ($action) {
            case 'delete':
                User::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                User::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                User::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            case 'setRole':
                User::whereIn('id', $ids)->update(['role_id' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            case 'removeRole':
                User::whereIn('id', $ids)->update(['role_id' => null]);
                return response()->json(['message' => 'success'], 200);
            case 'setStatus':
                User::whereIn('id', $ids)->update(['status' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ'], 400);
        }
    }
}
