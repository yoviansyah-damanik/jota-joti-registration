<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['executed_by', 'max_group_number', 'is_reset', 'total_troops', 'total_participants', 'summary'])]
class GroupAssignmentBatch extends Model
{
    protected function casts(): array
    {
        return [
            'is_reset' => 'boolean',
            'summary' => 'array',
        ];
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
