# Official Logo (Upload & Homepage Section)

- **Admin Upload & Management**:
  - `Settings` Livewire component ([app/Livewire/Admin/Settings.php](file:///d:/WebApps/jota-joti-registration/app/Livewire/Admin/Settings.php)):
    - Upload official logo (`logo`: PNG, JPG, WebP, SVG, max 2MB).
    - Stored in `storage/app/public/logos` and tracked in `SettingService` under `official_logo_path`.
    - Instant live preview and reset to default logo button.
- **Dynamic Application Logo**:
  - `SettingService` ([app/Services/SettingService.php](file:///d:/WebApps/jota-joti-registration/app/Services/SettingService.php)):
    - `logoUrl()`, `logoPath()`, and `hasCustomLogo()` helpers.
  - Component `<x-ui.logo />` ([resources/views/components/ui/logo.blade.php](file:///d:/WebApps/jota-joti-registration/resources/views/components/ui/logo.blade.php)):
    - Automatically displays the active official logo throughout all views (navbar, sidebar, login, cards, prints).
- **Public Homepage Section**:
  - Dedicated "Official Logo" section on [resources/views/livewire/public/terms-page.blade.php](file:///d:/WebApps/jota-joti-registration/resources/views/livewire/public/terms-page.blade.php):
    - High-resolution showcase card with drop shadow.
    - Official branding guidelines.
    - Direct download button via route `/logo/download` ([DownloadController::officialLogo()](file:///d:/WebApps/jota-joti-registration/app/Http/Controllers/DownloadController.php)).
