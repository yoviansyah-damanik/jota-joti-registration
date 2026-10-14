<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'participant_id',
    'jid',
    'jjid',
    'jota_file_path',
    'joti_file_path',
    'notes',
    'jota_status',
    'jota_admin_note',
    'joti_status',
    'joti_admin_note',
    'final_status',
    'admin_notes',
    'assessed_by',
    'assessed_at',
])]
class TaskSubmission extends Model
{
    /** @use HasFactory<\Database\Factories\TaskSubmissionFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'jota_status' => TaskStatus::class,
            'joti_status' => TaskStatus::class,
            'final_status' => TaskStatus::class,
            'assessed_at' => 'datetime',
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $query->when($term, function (Builder $q) use ($term) {
            $q->where('jid', 'like', "%{$term}%")
                ->orWhere('jjid', 'like', "%{$term}%")
                ->orWhereHas('participant', function (Builder $pq) use ($term) {
                    $pq->where('full_name', 'like', "%{$term}%")
                        ->orWhere('attendance_code', 'like', "%{$term}%")
                        ->orWhere('kta_number', 'like', "%{$term}%")
                        ->orWhereHas('troop', fn (Builder $tq) => $tq->where('troop_name', 'like', "%{$term}%"));
                });
        });
    }
}
