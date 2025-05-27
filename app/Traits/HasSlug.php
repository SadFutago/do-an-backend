<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    abstract protected function slugSourceField(): string;
    
    protected function slugField(): string
    {
        return 'slug';
    }
    
    public static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->generateSlugOnCreate();
        });

        static::updating(function ($model) {
            $model->generateSlugOnUpdate();
        });
    }

    protected function generateSlugOnCreate()
    {
        $slugField = $this->slugField();
        $sourceValue = $this->{$this->slugSourceField()};

        if (empty($this->{$slugField}) && !empty($sourceValue)) {
            $this->{$slugField} = $this->generateUniqueSlug($sourceValue);
        }
    }

    protected function generateSlugOnUpdate()
    {
        $slugField = $this->slugField();
        $sourceField = $this->slugSourceField();

        if ($this->isDirty($sourceField)) {
            $this->{$slugField} = $this->generateUniqueSlug($this->{$sourceField});
        }
    }

    public function generateUniqueSlug(string $value): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug)) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        return static::where($this->slugField(), $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists();
    }
}