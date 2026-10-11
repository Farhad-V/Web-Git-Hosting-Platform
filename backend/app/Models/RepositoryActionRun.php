<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RepositoryActionRun extends Model
{
    protected $fillable = ['repository_id', 'actor_id', 'branch', 'commit', 'status', 'steps', 'log', 'started_at', 'finished_at'];

    protected function casts(): array
    {
        return ['steps' => 'array', 'started_at' => 'datetime', 'finished_at' => 'datetime'];
    }
}
