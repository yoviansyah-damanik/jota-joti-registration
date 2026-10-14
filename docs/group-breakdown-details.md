# Group Breakdown Details (Rincian Pembagian Grup)

- **Route & Component**:
  - Route: `/admin/groups/details` (`admin.groups.details`)
  - Livewire Component: `App\Livewire\Admin\Groups\Details`
  - View: `resources/views/livewire/admin/groups/details.blade.php`
- **Features**:
  - **Summary Stats**: Total verified, assigned, unassigned, and gender composition (male/female).
  - **View Modes**:
    - `list` (Daftar Peserta): Detailed table with search, quick group filter pills (All, Group 1..N, Unassigned), troop filter, gender filter, and pagination.
    - `matrix` (Matriks Persebaran Gudep): Troop-by-group distribution table with row and column totals and minimum-per-number warnings.
  - **Navigation Integration**:
    - Direct access button from page header on `/admin/groups`.
    - Clickable group badges and 'View details' button inside `BalanceReport`.
    - Sidebar active state maintained with `admin.groups*`.
