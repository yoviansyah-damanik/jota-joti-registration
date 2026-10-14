<?php

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\ParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['troop_id', 'kta_number', 'attendance_code', 'full_name', 'birth_date', 'gender', 'group_number', 'assigned_at'])]
class Participant extends Model
{
    /** @use HasFactory<ParticipantFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'gender' => Gender::class,
            'group_number' => 'integer',
            'assigned_at' => 'datetime',
        ];
    }

    public function troop(): BelongsTo
    {
        return $this->belongsTo(Troop::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Hanya peserta dari gudep yang sudah diverifikasi.
     */
    #[Scope]
    protected function fromVerifiedTroops(Builder $query): void
    {
        $query->whereHas('troop', fn (Builder $q) => $q->verified());
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $query->when($term, fn (Builder $q) => $q->where(fn (Builder $q) => $q
            ->where('kta_number', 'like', "%{$term}%")
            ->orWhere('full_name', 'like', "%{$term}%")));
    }
}
