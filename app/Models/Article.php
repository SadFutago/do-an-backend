<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'highlight',
        'publish_date',
        'content',
        'author',
        'approved_by',
        'category_id'
    ];

    protected $casts = [
        'publish_date' => 'date:Y-m-d',
    ];
    
    protected function slugSourceField(): string 
    {
        return 'title';
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'author');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
