# Admin Username Login

- **Database**:
  - `users.username`: `string()->unique()`
  - `users.email`: `string()->unique()->nullable()`
- **Model & Factory**:
  - `User`: Added `username` to fillable attributes.
  - `UserFactory`: Added `fake()->unique()->userName()`.
- **Seeder & Environment**:
  - Added `ADMIN_USERNAME=admin` to `.env` and `.env.example`.
  - `DatabaseSeeder`: Seeds administrator with username `admin`.
- **Authentication**:
  - `Login` component: Authenticates via `username` (with automatic fallback to `email` if input format is an email address).
  - `login.blade.php`: Input field changed from Email to Username.
  - `admin-sidebar.blade.php`: Displays `@username` in user profile badge.
