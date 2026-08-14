<?php

namespace App\Http\Resources;

use App\Enums\RepositoryStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RepositoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'full_name' => "{$this->owner->username}/{$this->slug}",
            'description' => $this->description,
            'visibility' => $this->visibility->value,
            'status' => $this->status->value,
            'default_branch' => $this->default_branch,
            'is_empty' => $this->default_branch === null,
            'is_writable' => $this->isWritable(),
            'is_archived' => (
                $this->status === RepositoryStatus::Archived
            ),
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'username' => $this->owner->username,
            ],
            'archived_at' => $this->archived_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'links' => [
                'api' => route(
                    'api.v1.repositories.show',
                    $this->resource,
                ),
            ],
        ];
    }
}
