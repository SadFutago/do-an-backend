<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    private const SEARCH_FIELDS = ['title', 'creator.name'];
    private const FILTER_FIELDS = [
        'filterByUnapproved' => ['column' => 'approved_by'],
    ];

    public function approve(Request $request, string $id) {
        $approverId = $request->user_id;
        $article = Article::findOrFail($id);
        $article->approved_by = $approverId;
        if (is_null($article->publish_date)) {
            $article->publish_date = now();
        }
        $article->save();
        return response()->json(['message' => 'success'], 200);
    }

    public function getByCategory(string $categorySlug) {
        $limit = $categorySlug === 'tin-noi-bat' ? 5 : 15;
        $query = Article::whereHas('category', function($query) use ($categorySlug) {
            $query->where('slug', $categorySlug);
        })->whereNotNull('approved_by')
        ->orderBy('publish_date', 'desc')
        ->limit($limit)
        ->with([
            'creator:id,name',
            'images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'article_id')->limit(1);
            },
        ])
        ->select('id', 'title', 'slug', 'publish_date', 'highlight', 'created_at', 'author');
        return response()->json($query->get());
    }

    public function getBySlug(string $slug) {
        $article = Article::where('slug', $slug)->whereNotNull('approved_by')
        ->with([
            'creator:id,name,avatar_id',
            'creator.avatar:id,path',
            'images:id,article_id,path,is_thumbnail,filename',
        ])->firstOrFail();
        return response()->json($article);
    }

    public function getPublished(Request $request) {
        $query = Article::whereNotNull('approved_by')
        ->orderBy('publish_date', 'desc')
        ->with([
            'creator:id,name',
            'images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'article_id')->limit(1);
            },
        ])
        ->select('id', 'title', 'slug', 'author', 'publish_date', 'highlight');

        return $this->getListResponse($query, $request, [], [], 9);
    }

    public function index(Request $request) {
        $query = Article::with([
            'creator:id,name',
            'approver:id,name',
        ])->orderBy('updated_at', 'desc');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function trashed(Request $request) {
        $query = Article::onlyTrashed()->with([
            'creator:id,name',
            'approver:id,name',
        ])->orderBy('updated_at', 'desc');
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function show(string $id) {
        $article = Article::with('images')->findOrFail($id);
        return response()->json($article);
    }

    public function store(Request $request) {
        $creatorId = $request->user_id;
        $article = Article::create($request->all() + ['author' => $creatorId]);
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $article);
        }
        if ($request->has('selectedThumbnail')) {
            $this->setThumbnail($article, $request->input('selectedThumbnail'));
        }
        return response()->json(['message' => 'success'], 201);
    }

    public function update(Request $request, string $id) {
        $article = Article::findOrFail($id);
        $article->update($request->all());
        if ($request->has('deletedImageIds')) {
            $this->deleteImages(json_decode($request->input('deletedImageIds'), true), $article);
        }
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $article);
        }
        if ($request->has('selectedThumbnail')) {
            $this->setThumbnail($article, $request->input('selectedThumbnail'));
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id) {
        $article = Article::findOrFail($id);
        $article->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function restore(string $id) {
        $article = Article::onlyTrashed()->findOrFail($id);
        $article->restore();
        return response()->json(['message' => 'success'], 200);
    }

    public function forceDelete(string $id) {
        $article = Article::onlyTrashed()->findOrFail($id);
        $this->deleteFolder($article);
        $article->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }

    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Article::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Article::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Article::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ'], 400);
        }
    }
}
