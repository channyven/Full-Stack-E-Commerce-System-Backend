<?php

namespace App\Traits;

trait ScopedQuery
{
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published')->where('is_active', true);
    }
}
