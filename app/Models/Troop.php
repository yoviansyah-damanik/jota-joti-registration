<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\ScoutUnit;
use App\Enums\TroopStatus;
use App\Support\TroopNumber;
use Database\Factories\TroopFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'troop_number', 'troop_number_pair', 'troop_name', 'fee_per_participant', 'unit', 'pic_name', 'pic_phone', 'pic_email',
    'registration_code', 'status', 'admin_note', 'verified_by', 'verified_at', 'submitted_at',
])]
class Troop extends Model
{
    /** @use HasFactory<TroopFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'status' => TroopStatus::class,
            'fee_per_participant' => 'integer',
            'unit' => ScoutUnit::class,
            'verified_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class)->orderBy('full_name');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TroopDocument::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function document(DocumentType $type): ?TroopDocument
    {
        return $this->documents->firstWhere('type', $type);
    }

    /**
     * Nomor gudep kedua selalu diturunkan dari nomor pertama (+1).
     */
    protected static function booted(): void
    {
        static::saving(function (Troop $troop) {
            $troop->troop_number = TroopNumber::normalize($troop->troop_number);
            $troop->troop_number_pair = TroopNumber::pair($troop->troop_number) ?? $troop->troop_number;
        });
    }

    /**
     * Label lengkap, mis. "01-083 & 01-084".
     */
    protected function numberLabel(): Attribute
    {
        return Attribute::get(fn () => TroopNumber::label($this->troop_number, $this->troop_number_pair));
    }

    /**
     * Total biaya pendaftaran = jumlah peserta × biaya per peserta (snapshot saat mendaftar).
     */
    protected function feeTotal(): Attribute
    {
        return Attribute::get(fn () => (int) ($this->participants_count ?? $this->participants()->count()) * (int) $this->fee_per_participant);
    }

    #[Scope]
    protected function verified(Builder $query): void
    {
        $query->where('status', TroopStatus::Verified);
    }

    public function isVerified(): bool
    {
        return $this->status === TroopStatus::Verified;
    }

    /**
     * Gudep yang memiliki salah satu nomor yang diberikan (nomor pertama atau pasangannya).
     */
    #[Scope]
    protected function withAnyNumber(Builder $query, array $numbers): void
    {
        $query->where(fn (Builder $q) => $q->whereIn('troop_number', $numbers)->orWhereIn('troop_number_pair', $numbers));
    }

    /**
     * Gudep aktif (belum ditolak) yang pasangan nomornya bersinggungan dengan nomor pertama ini.
     */
    #[Scope]
    protected function activeNumber(Builder $query, string $troopNumber): void
    {
        $query->withAnyNumber(array_filter([$troopNumber, TroopNumber::pair($troopNumber)]))
            ->whereIn('status', TroopStatus::activeValues());
    }

    public function isEditableByRegistrant(): bool
    {
        return $this->status === TroopStatus::Revision;
    }
}
