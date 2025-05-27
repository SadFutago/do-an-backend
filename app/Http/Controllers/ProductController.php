<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;

class ProductController extends Controller
{
    private const SEARCH_FIELDS = ['name'];
    private const FILTER_FIELDS = [
        'filterByCategory' => [
            'relation' => 'categories',
            'column' => 'categories.id'
        ],
        'filterBySupplier' => ['column' => 'supplier_id'],
        'filterByPromotion' => ['column' => 'promotion_id'],
        'filterByUnit' => ['column' => 'unit_id'],
        'filterByStatus' => ['column' => 'status'],
    ];

    public function getBySlug(string $slug) {
        $product = Product::with([
            'images:id,path,product_id',
            'unit:id,name',
            'promotion:id,discount_percent',
            'categories:id,name,slug',
            'categories.images' => function($query) {
                $query->select('id', 'path', 'category_id')
                ->where('is_thumbnail', true)->limit(1);
            },
        ])->select('id', 'name', 'slug', 'description', 'price', 'original_price',
            'quantity', 'dimensions', 'weight', 'color', 'material', 'compatibility', 'unit_id', 'supplier_id', 'promotion_id')
        ->where('slug', $slug)->firstOrFail();

        $related = Product::with([
            'images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id');
            },
            'unit:id,name',
            'promotion:id,discount_percent'
        ])->select('id', 'name', 'slug', 'price', 'original_price', 'unit_id', 'supplier_id', 'promotion_id')
        ->whereHas('categories', function($query) use ($product) {
            $query->whereIn('categories.id', $product->categories->pluck('id'));
        })
        ->where('id', '!=', $product->id)
        ->take(8)
        ->get();

        $ratingCounts = Review::where('product_id', $product->id)
        ->selectRaw('rating, COUNT(*) as count')
        ->groupBy('rating')
        ->pluck('count', 'rating')
        ->toArray();

        return response()->json([
            'data' => $product,
            'related' => $related,
            'ratingCounts' => $ratingCounts
        ]);
    }

    public function getByCategorySlug(Request $request, ?string $slug = null) {
        $query = Product::with([
            'images' => function($query) {
                $query->where('is_thumbnail', true)->select('id', 'path', 'product_id');
            }, 
            'unit:id,name',
            'promotion:id,discount_percent'
        ])->where('status', Product::STATUS_ACTIVE);

        if ($slug !== null) {
            $query->whereHas('categories', function($q) use ($slug) {
                $q->where('slug', $slug);
            });
        } else if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by');
            switch ($sortBy) {
                case 'on_sale':
                    $query->whereColumn('price', '!=', 'original_price');
                    break;
            }
        }

        if ($request->has('price_range')) {
            $priceRange = $request->input('price_range');
            switch ($priceRange) {
                case 'under_500k':
                    $query->where('price', '<', 500000);
                    break;
                case '500k_2m':
                    $query->whereBetween('price', [500000, 2000000]);
                    break;
                case '2m_5m':
                    $query->whereBetween('price', [2000000, 5000000]);
                    break;
                case '5m_10m':
                    $query->whereBetween('price', [5000000, 10000000]);
                    break;
                case 'above_10m':
                    $query->where('price', '>', 10000000);
                    break;
            }
        }
        
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS, 20);
    }

    public function index(Request $request) {
        $query = Product::with([
            'categories:id,name',
            'unit:id,name',
            'supplier:id,name',
            'promotion:id,discount_percent,status,name'
        ]);
        $action = $request->query('action');
        switch ($action) {
            case 'filterByOutOfStock':
                $query->where('quantity', 0)->where('status', Product::STATUS_ACTIVE);
                break;
            case 'filterByLowStock':
                $query->where('quantity', '<', 10)->where('quantity', '>', 0)->where('status', Product::STATUS_ACTIVE);
                break;
            default:
                break;
        }
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function trashed(Request $request) {
        $query = Product::onlyTrashed()->with([
            'images',
            'categories:id,name',
            'unit:id,name',
            'supplier:id,name',
            'promotion:id,discount_percent,status,name'
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function show(string $id) {
        $product = Product::with([
            'images',
            'categories:id,name',
            'unit:id,name',
            'supplier:id,name',
            'promotion:id,discount_percent'
        ])->findOrFail($id);
        return response()->json($product);
    }

    public function showBySlug(string $slug) {
        $product = Product::with('images')->where('slug', $slug)->firstOrFail();
        return response()->json($product);
    }

    public function store(Request $request) {
        $product = Product::create($request->all());
        if ($request->has('addedIds')) {
            $this->addIds($product, json_decode($request->input('addedIds'), true), 'categories');
        }
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $product);
        }
        if ($request->has('selectedThumbnail')) {
            $this->setThumbnail($product, $request->input('selectedThumbnail'));
        }
        return response()->json(['message' => 'success'], 201);
    }

    public function update(Request $request, string $id) {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        if ($request->has('addedIds')) {
            $this->addIds($product, json_decode($request->input('addedIds'), true), 'categories');
        }
        if ($request->has('deletedIds')) {
            $this->removeIds($product, json_decode($request->input('deletedIds'), true), 'categories');
        }
        if ($request->has('deletedImageIds')) {
            $this->deleteImages(json_decode($request->input('deletedImageIds'), true), $product);
        }
        if ($request->hasFile('images')) {
            $this->storeImages($request->file('images'), $product);
        }
        if ($request->has('selectedThumbnail')) {
            $this->setThumbnail($product, $request->input('selectedThumbnail'));
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id) {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function restore(string $id) {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();
        return response()->json(['message' => 'success'], 200);
    }

    public function forceDelete(string $id) {
        $product = Product::onlyTrashed()->findOrFail($id);
        $this->deleteFolder($product);
        $product->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }

    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        $targetId = $request->input('targetId');
        switch ($action) {
            case 'delete':
                Product::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Product::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Product::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            case 'setStatus':
                Product::whereIn('id', $ids)->update(['status' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            case 'addCategory':
                $products = Product::whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    $product->categories()->syncWithoutDetaching([$targetId]);
                }
                return response()->json(['message' => 'success'], 200);
            case 'removeCategory':
                $products = Product::whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    $product->categories()->detach($targetId);
                }
                return response()->json(['message' => 'success'], 200);
            case 'setSupplier':
                Product::whereIn('id', $ids)->update(['supplier_id' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            case 'setPromotion':
                Product::whereIn('id', $ids)->update(['promotion_id' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            case 'removePromotion':
                Product::whereIn('id', $ids)->update(['promotion_id' => null]);
                return response()->json(['message' => 'success'], 200);
            case 'setUnit':
                Product::whereIn('id', $ids)->update(['unit_id' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            default:
                return response()->json(['message' => 'Hành động không hợp lệ'], 400);
        }
    }
}
