<?php

namespace App\Support;

use App\Enums\DocumentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Menyimpan data wizard pendaftaran sementara di session (tanpa login),
 * sedangkan file disimpan di disk privat folder drafts/{id}.
 */
class RegistrationDraft
{
    private const KEY = 'registration.draft';

    public const DISK = 'local';

    public function id(): string
    {
        if (! session()->has(self::KEY.'.id')) {
            session()->put(self::KEY.'.id', (string) Str::uuid());
        }

        return session(self::KEY.'.id');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return session(self::KEY.'.'.$key, $default);
    }

    public function put(string $key, mixed $value): void
    {
        $this->id();
        session()->put(self::KEY.'.'.$key, $value);
    }

    public function troop(): array
    {
        return $this->get('troop', []);
    }

    public function participants(): array
    {
        return $this->get('participants', []);
    }

    /**
     * @return array<string, array{path: string, original_name: string, mime: ?string, size: int}>
     */
    public function files(): array
    {
        return $this->get('files', []);
    }

    public function file(DocumentType $type): ?array
    {
        return $this->files()[$type->value] ?? null;
    }

    /**
     * Menyimpan file unggahan ke folder draft dan mengganti file lama dengan tipe yang sama.
     */
    public function storeFile(DocumentType $type, UploadedFile $file): array
    {
        if ($old = $this->file($type)) {
            Storage::disk(self::DISK)->delete($old['path']);
        }

        $meta = [
            'path' => $file->store('drafts/'.$this->id(), self::DISK),
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];

        $this->put('files.'.$type->value, $meta);

        return $meta;
    }

    public function isStepComplete(int $step): bool
    {
        return match ($step) {
            1 => $this->troop() !== [],
            2 => $this->participants() !== [],
            3 => $this->file(DocumentType::AssignmentLetter) !== null && $this->file(DocumentType::PaymentProof) !== null,
            default => false,
        };
    }

    /**
     * Langkah pertama yang belum lengkap (4 = siap direview).
     */
    public function currentStep(): int
    {
        foreach ([1, 2, 3] as $step) {
            if (! $this->isStepComplete($step)) {
                return $step;
            }
        }

        return 4;
    }

    public function clear(bool $deleteFiles = true): void
    {
        if ($deleteFiles && session()->has(self::KEY.'.id')) {
            Storage::disk(self::DISK)->deleteDirectory('drafts/'.$this->id());
        }

        session()->forget(self::KEY);
    }
}
