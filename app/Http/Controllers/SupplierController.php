<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    private const SEARCH_FIELDS = ['name'];

    public function index(Request $request) {
        $query = Supplier::query();
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function trashed(Request $request) {
        $query = Supplier::onlyTrashed();
        return $this->getListResponse($query, $request, self::SEARCH_FIELDS);
    }
    public function show(string $id) {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }
    public function store(Request $request) {
        Supplier::create($request->all());
        return response()->json(['message' => 'success'], 201);
    }
    public function update(Request $request, string $id) {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        return response()->json(['message' => 'success'], 200);
    }
    public function destroy(string $id) {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();
        return response()->json(['message' => 'success'], 200);
    }
    public function restore(string $id) {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->restore();
        return response()->json(['message' => 'success'], 200);
    }
    public function forceDelete(string $id) {
        $supplier = Supplier::onlyTrashed()->findOrFail($id);
        $supplier->forceDelete();
        return response()->json(['message' => 'success'], 204);
    }
    public function handleFormActions(Request $request) {
        $action = $request->input('action');
        $ids = $request->input('selectedIds', []);
        switch ($action) {
            case 'delete':
                Supplier::destroy($ids);
                return response()->json(['message' => 'success'], 200);
            case 'restore':
                Supplier::onlyTrashed()->whereIn('id', $ids)->restore();
                return response()->json(['message' => 'success'], 200);
            case 'forceDelete':
                Supplier::onlyTrashed()->whereIn('id', $ids)->forceDelete();
                return response()->json(['message' => 'success'], 204);
            default:
                return response()->json(['message' => 'Action is invalid'], 400);
        }
    }
}
