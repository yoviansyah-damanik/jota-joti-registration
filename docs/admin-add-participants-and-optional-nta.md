# Admin Add Participants & Optional NTA

- **Optional NTA**:
  - `participants.kta_number`: `nullable()->index()`
  - Excel parser (`ParticipantSheetParser`) and manual inputs accept empty/null NTA/KTA without validation errors.
- **Admin Participant Management**:
  - `ParticipantTable`: added `openAddModal()`, `addParticipant()`, and `deleteParticipant()`. Automatically generates `attendance_code` if troop is verified.
  - `Step2ParticipantUpload`: added manual participant entry modal (`openManualModal()`, `addManualParticipant()`, `removeRow()`) during registration wizard.
