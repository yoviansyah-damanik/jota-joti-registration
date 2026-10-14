# UUID for Sensitive Data & Documents

- **Models**:
  - `TroopDocument`: Uses `HasUuids`. Primary key `id` is UUID.
  - `Participant`: Uses `HasUuids`. Primary key `id` is UUID.
- **Database Migrations**:
  - `troop_documents.id`: `uuid()->primary()`
  - `participants.id`: `uuid()->primary()`
  - `attendances.participant_id`: `foreignUuid('participant_id')->constrained('participants')->cascadeOnDelete()`
- **Storage & File Security**:
  - `RegistrationService::attachDocument()`: Generates file names using `Str::uuid()` (`troops/{troop_id}/{type}-{uuid}.{ext}`).
- **Routes & Controllers**:
  - `admin.documents.show`: Route model binding resolves `TroopDocument` by UUID.
  - `daftar.bukti`: Resolves registration receipt by `registration_code` or `troop_id` (UUID).
- **Livewire Components**:
  - `ParticipantTable`: `deleteParticipant(string $participantId)`
  - `ParticipantList`: `setStatus(string $participantId, ...)`
