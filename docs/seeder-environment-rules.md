# Seeder Environment Restrictions

- **Production Environment (`production`, `prod`)**:
  - `DatabaseSeeder`: Only seeds the default administrator account (`User::updateOrCreate`).
  - `DemoSeeder`: Prevented from running; skips execution with a warning if triggered in production.
- **Local/Development Environment (`local`, `dev`, `development`)**:
  - `DatabaseSeeder`: Seeds the administrator account and automatically seeds all sample data by invoking `DemoSeeder` and demo settings.
