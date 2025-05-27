<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class Controller
{
    // chuẩn hóa chuỗi tiếng việt (loại dấu & chuyển sang chữ thường)
    private function normalizeVietnameseString($str) {
        $str = mb_strtolower($str, 'UTF-8');
        $patterns = [
            '/[áàảãạăắằẳẵặâấầẩẫậ]/u' => 'a',
            '/[éèẻẽẹêếềểễệ]/u' => 'e',
            '/[íìỉĩị]/u' => 'i',
            '/[óòỏõọôốồổỗộơớờởỡợ]/u' => 'o',
            '/[úùủũụưứừửữự]/u' => 'u',
            '/[ýỳỷỹỵ]/u' => 'y',
            '/đ/u' => 'd',
        ];
        return preg_replace(array_keys($patterns), array_values($patterns), $str);
    }
    protected function search($query, $key, array $columns = []) {
        if (empty($key)) {
            return $query;
        }
        
        if (is_numeric($key)) {
            $primaryKey = $query->getModel()->getKeyName();
            return $query->where($primaryKey, $key);
        }

        $normalizedKey = $this->normalizeVietnameseString($key);
        $query->where(function ($q) use ($normalizedKey, $columns) {
            foreach ($columns as $column) {
                if (strpos($column, '.') !== false) {
                    $parts = explode('.', $column);
                    $relatedColumn = array_pop($parts);
                    $relationPath = implode('.', $parts);
                    $q->orWhereHas($relationPath, function($subQuery) use ($normalizedKey, $relatedColumn) {
                        $subQuery->whereRaw("LOWER(UNACCENT({$relatedColumn})) LIKE ?", ["%{$normalizedKey}%"]);
                    });
                } else {
                    $q->orWhereRaw("LOWER(UNACCENT({$column})) LIKE ?", ["%{$normalizedKey}%"]);
                }
            }
        });
        return $query;
    }
    protected function sort($query, $sort) {
        if ($sort && isset($sort['enabled']) && $sort['enabled']) {
            $query->orderBy($sort['column'], $sort['type']);
        }
        return $query;
    }
    protected function filter($query, $action, $targetId, array $filters = []) {
        if (!$action || !isset($filters[$action])) {
            return $query;
        }
        $filter = $filters[$action];
        if (isset($filter['relation'])) {
            $query->whereHas($filter['relation'], function ($q) use ($filter, $targetId) {
                $q->where($filter['column'], $targetId);
            });
        } else {
            if ($targetId === null) {
                $query->whereNull($filter['column']);
            } else {
                $query->where($filter['column'], $targetId);
            }
        }
        return $query;
    }
    protected function getListResponse($query, Request $request, array $searchableColumns = [], array $filters = [], int $perPage = null) {
        $perPage = $perPage ?? config('app.per_page');
        $query = $this->search($query, $request->query('key'), $searchableColumns);
        $query = $this->filter($query, $request->query('action'), $request->query('targetId'), $filters);
        $query = $this->sort($query, $request->_sort);
        $isPaginated = !$request->boolean('all');
        $items = $isPaginated ? $query->paginate($perPage) : $query->get();
        $data = $isPaginated ? $items->items() : $items->toArray();
        $response = [
            'data' => $data,
            '_sort' => $request->_sort,
        ];
        if ($isPaginated) {
            $response['pagination'] = [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ];
        }
        return response()->json($response);
    }
    protected function storeImages($files, Model $model) {
        if (empty($files)) return;
        if (!is_array($files)) {
            $files = [$files];
        }

        $storagePath = class_basename($model) . "/{$model->id}";
        
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            
            $path = $file->store($storagePath, 'public');
            $model->images()->create([
                'path' => Storage::url($path),
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
    protected function deleteImages(array $imageIds, Model $model) {
        if (empty($imageIds)) return;

        $images = $model->images()->whereIn('id', $imageIds)->get();
        foreach ($images as $image) {
            $path = str_replace('/storage', '', $image->path);
            Storage::disk('public')->delete($path);
        }
        $model->images()->whereIn('id', $imageIds)->delete();
    }
    protected function deleteFolder(Model $model) {
        $storagePath = class_basename($model) . "/{$model->id}";
        Storage::disk('public')->deleteDirectory($storagePath);
    }
    protected function setThumbnail(Model $model, $imageName) {
        $model->images()->update(['is_thumbnail' => false]);
        $model->images()->where('filename', $imageName)->update(['is_thumbnail' => true]);
        $model->refresh();
    }
    protected function addIds(Model $model, array $ids = [], String $relation, array $idsWithAttributes = []) {   // cho các bảng có quan hệ n-n
        if (!empty($ids)) {
            $model->$relation()->syncWithoutDetaching($ids);
        } elseif (!empty($idsWithAttributes)) {
            $data = [];
            foreach ($idsWithAttributes as $i) {
                if (!isset($i['id'])) continue;

                $data[$i['id']] = array_filter($i, function ($key) {
                    return $key !== 'id';
                }, ARRAY_FILTER_USE_KEY);
            }
            $model->$relation()->syncWithoutDetaching($data);
        }
    }
    protected function removeIds(Model $model, array $ids, String $relation) {
        $model->$relation()->detach($ids);
    }
    protected function updateIds(Model $model, array $idsWithAttributes, string $relation) {
        $data = [];
        foreach ($idsWithAttributes as $i) {
            if (!isset($i['id'])) continue;
            
            $data[$i['id']] = Arr::except($i, 'id');
        }
        $model->$relation()->syncWithoutDetaching($data);
    }
    protected function calculateTotalAmount(array $details, String $quantity = 'quantity', String $price = 'price'): int {
        $total = 0;
        foreach ($details as $detail) {
            if (isset($detail[$quantity]) && isset($detail[$price])) {
                $total += (int)$detail[$quantity] * (int)$detail[$price];
            }
        }
        return $total;
    }
    protected function saveDetails(Model $model, array $details, String $relation) {
        $relationInstance = $model->$relation();
        $relatedModel = $relationInstance->getRelated();
        $foreignKey = $relationInstance->getForeignKeyName();

        $existingIds = $model->$relation->pluck('id')->toArray();
        $updatedIds = [];
        foreach ($details as $detail) {
            if (isset($detail['id'])) {
                $detailModel = $relatedModel::findOrFail($detail['id']);
                $detailModel->update($detail);
                $updatedIds[] = $detailModel->id;
            } else {
                $detail[$foreignKey] = $model->id;
                $detailModel = $relatedModel::create($detail);
                $updatedIds[] = $detailModel->id;
            }
        }

        $idsToDelete = array_diff($existingIds, $updatedIds);
        if (!empty($idsToDelete)) {
            $relatedModel::destroy($idsToDelete);
        }
    }
}
