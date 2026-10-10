<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PullRequest extends Model
{
    protected $fillable = ['repository_id', 'author_id', 'number', 'title', 'description', 'base_branch', 'head_branch', 'status', 'merge_commit', 'merge_base', 'merge_head', 'merged_at'];

    protected function casts(): array
    {
        return ['merged_at' => 'datetime'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
