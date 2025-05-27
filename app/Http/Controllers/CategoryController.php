<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    private const SEARCH_FIELDS = ['name', 'type'];

    public function getBySlug(string $slug) {
        $category = Category::where('slug', $slug)->first();
        if (!$category) {
            return response()->json(['message' => 'Không tìm thấy danh mục'], 404);
        }
        $related = Category::where('type', $category->type)->where('id', '!=', $category->id) ->get();
        
        return response()->json([
            'data' => $category,
            'related' => $related
        ]);
    }

    public function index(Request $request) {
        $query = Category::with('images');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Category::onlyTrashed()->with('images');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function show(string $id) {
        $category = Category::with('images')->findOrFail($id);
        return response()->json($category);
    }
    public function store(Request $request) {
        $category = Category::create($request->all());
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $category);
        }
        if ($request->has('selectedThumbnail')) {
            $this->setThumbnail($category, $request->input('selectedThumbnail'));
        }
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        if ($request->has('deletedImageIds')) {
            $this->deleteImages(json_decode($request->input('deletedImageIds'), true), $category);
        }
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $category);
        }
        if ($request->has('selectedThumbnail')) {
            $this->setThumbnail($category, $request->input('selectedThumbnail'));
        }
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $category = Category::onlyTrashed()->findOrFail($id);
        $this->deleteFolder($category);
        $category->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Category::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Category::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Category::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ'], 400);
        }
    }
}
