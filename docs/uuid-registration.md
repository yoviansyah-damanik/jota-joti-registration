# UUID for Registration (Troop)

- **Model**: `Troop` uses `Illuminate\Database\Eloquent\Concerns\HasUuids`.
- **Database**:
  - `troops.id`: `uuid()->primary()`
  - `participants.troop_id`: `foreignUuid('troop_id')->constrained('troops')->cascadeOnDelete()`
  - `troop_documents.troop_id`: `foreignUuid('troop_id')->constrained('troops')->cascadeOnDelete()`
- **Components & Services**: Updated property and parameter type-hints from `int` to `string` for `troopId`.
