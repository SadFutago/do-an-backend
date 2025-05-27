<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    private const SEARCH_FIELDS = ['zone', 'aisle', 'rack', 'shelf', 'bin'];
    private const FILTER_FIELDS = [
        'filterByCategory' => ['column' => 'category_id'],
        'filterByStatus' => ['column' => 'status'],
    ];

    public function index(Request $request) {
        $query = Location::with([
            'category:id,name', 
            'inventories:id'
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function trashed(Request $request) {
        $query = Location::onlyTrashed()->with([
            'category:id,name', 
            'inventories:id'
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function show(string $id) {
        $location = Location::with([
            'category:id,name',
            'inventories:id,batch_number,product_id',
            'inventories.product:id,name,unit_id',
            'inventories.product.unit:id,name'
        ])->findOrFail($id);
        return response()->json($location);
    }

    public function store(Request $request) {
        $location = Location::create($request->all());
        if ($request->has('addedIdsWithAttributes')) {
            $this->addIds($location, [], 'inventories', $request->addedIdsWithAttributes);
        }
        return response()->json(['message' => 'success'], 201);
    }

    public function update(Request $request, string $id) {
        $location = Location::findOrFail($id);
        $location->update($request->all());
        if ($request->has('addedIdsWithAttributes')) {
            $this->addIds($location, [], 'inventories', $request->addedIdsWithAttributes);
        }
        if ($request->has('deletedIds')) {
            $this->removeIds($location, $request->deletedIds, 'inventories');
        }
        if ($request->has('updatedIdsWithAttributes')) {
            $this->updateIds($location, $request->updatedIdsWithAttributes, 'inventories');
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id) {
        $location = Location::findOrFail($id);
        $location->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function restore(string $id) {
        $location = Location::onlyTrashed()->findOrFail($id);
        $location->restore();
        return response()->json(['message' => 'success'], 200);
    }

    public function forceDelete(string $id) {
        $location = Location::onlyTrashed()->findOrFail($id);
        $location->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }

    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        $targetId = $request->input('targetId');
        switch ($action) {
            case 'delete':
                Location::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Location::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Location::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            case 'setCategory':
                Location::whereIn('id', $ids)->update(['category_id' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            case 'setStatus':
                Location::whereIn('id', $ids)->update(['status' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            default:
                return response()->json(['message' => 'Action is invalid'], 400);
        }
    }
}