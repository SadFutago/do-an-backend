<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
{
    private const SEARCH_FIELDS = ['batch_number', 'product.name'];
    private const FILTER_FIELDS = [
        'filterByProduct' => ['column' => 'product_id'],
        'filterByImport' => ['column' => 'import_id'],
    ];

    public function index(Request $request) {
        $query = Inventory::with([
            'locations:id,zone,aisle,rack,shelf,bin,category_id',
            'product:id,name,unit_id',
            'product.unit:id,name'
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function trashed(Request $request) {
        $query = Inventory::onlyTrashed()
        ->with([
            'locations:id,zone,aisle,rack,shelf,bin,category_id',
            'product:id,name,unit_id',
            'product.unit:id,name'
        ]);
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS, self::FILTER_FIELDS);
    }

    public function show(string $id) {
        $inventory = Inventory::with([
            'locations:id,zone,aisle,rack,shelf,bin,category_id'
        ])->findOrFail($id);
        return response()->json($inventory);
    }

    public function store(Request $request) {
        $inventory = Inventory::create($request->all());
        if ($request->has('addedIdsWithAttributes')) {
            $this->addIds($inventory, [], 'locations', $request->addedIdsWithAttributes);
        }
        return response()->json(['message' => 'success'], 201);
    }

    public function update(Request $request, string $id) {
        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->all());
        if ($request->has('addedIdsWithAttributes')) {
            $this->addIds($inventory, [], 'locations', $request->addedIdsWithAttributes);
        }
        if ($request->has('deletedIds')) {
            $this->removeIds($inventory, $request->deletedIds, 'locations');
        }
        if ($request->has('updatedIdsWithAttributes')) {
            $this->updateIds($inventory, $request->updatedIdsWithAttributes, 'locations');
        }
        return response()->json(['message' => 'success'], 200);
    }

    public function destroy(string $id) {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return response()->json(['message' => 'success'], 200);
    }

    public function restore(string $id) {
        $inventory = Inventory::onlyTrashed()->findOrFail($id);
        $inventory->restore();
        return response()->json(['message' => 'success'], 200);
    }

    public function forceDelete(string $id) {
        $inventory = Inventory::onlyTrashed()->findOrFail($id);
        $inventory->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }

    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        $targetId = $request->input('targetId');
        switch ($action) {
            case 'delete':
                Inventory::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Inventory::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Inventory::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            case 'setProduct':
                Inventory::whereIn('id', $ids)->update(['product_id' => $targetId]);
                return response()->json(['message' => 'success'], 200);
            default:
                return response()->json(['message' => 'Action is invalid'], 400);
        }
    }
}
