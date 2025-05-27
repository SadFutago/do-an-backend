<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'type',
    ];

    protected function slugSourceField(): string {
        return 'name';
    }

    public function images() {
        return $this->hasMany(Image::class);
    }
}
