# Sorakaro Developer Documentation

Welcome to the Developer Documentation for **Sorakaro**, an interactive learning platform for the Karo language. This guide provides an in-depth look at the architecture, directory structure, coding standards, and common development workflows to help developers onboard quickly and improve the app effectively.

---

## 1. System Architecture overview

Sorakaro is built on the robust **Laravel 12** PHP framework, leveraging a modern monolithic approach with the following key components:

- **Routing & Controllers**: Handled by Laravel's standard routing system (`routes/web.php`) mapped to dedicated Controllers (`app/Http/Controllers/`).
- **Data Access Layer**: Managed via Laravel Eloquent ORM. Models sit in `app/Models/` and schemas are defined through Migrations.
- **Frontend Layer**: 
  - Server-side rendering using **Blade Templates** (`resources/views/`).
  - Styling is built using **Tailwind CSS v3/v4** and **Flowbite** components.
  - Interactive frontend behaviors use **Alpine.js**.
  - Asset bundling is performed by **Vite** (`vite.config.js`).
- **Administration Panel**: Handled by **FilamentPHP v3**, generating powerful CRUD interfaces and dashboards located in `app/Filament/`.
- **Authentication & Security**: User authentication is structured by **Laravel Breeze**. Role management and permissions are administered via `spatie/laravel-permission`.

---

## 2. Directory Structure Guide

Understanding where to place your code is crucial for a clean and maintainable project.

```text
sorakaro/
├── app/
│   ├── Http/
│   │   ├── Controllers/   # 💡 Action logic for standard users (e.g. Quizzes, Dashboard)
│   │   └── Middleware/    # HTTP route guards and interceptors
│   ├── Models/            # 💡 Eloquent database models
│   └── Filament/          # 💡 Admin panel elements (Resources, Pages, Widgets)
├── database/
│   ├── migrations/        # Database structural changes
│   ├── seeders/           # 💡 Seed dummy or required data here (e.g., RoleSeeder)
│   └── factories/         # Used together with Seeders for populating testing data
├── public/                # Web-accessible root. Use this for general images/logos.
├── resources/
│   ├── css/               # Add custom frontend CSS variables here (app.css)
│   ├── js/                # Add custom JS and Alpine extensions here (app.js)
│   └── views/             # 💡 Blade templates (layouts, dashboards, lessons, quizzes)
├── routes/
│   ├── web.php            # 💡 Map your GET/POST URLs to controllers here
│   └── console.php        # Artisan console commands/schedules
├── tailwind.config.js     # Add custom design tokens, colors, or Flowbite options
└── package.json           # Frontend dependencies mapped for Vite
```

---

## 3. Database & Data Flow

The platform centers around several core domains. Make sure you understand the relationships when modifying logic:

### Users & Access
- `User`: Standard accounts. Connected to `roles`/`permissions` via Spatie tables.
- **Tracking**: Users track their learning progress through a `current_level_id`.

### Learning Pipeline
The curriculum flows hierarchically:
`Level` > `Lesson` 
- Pre-quiz studying uses the `GuidebookItems` mapped to `GuidebookSections`.
- Quizzes are evaluated using the `Questions` and `Choices` tables.
- **Attempts**: A user's try on a quiz is saved as an `Attempt` along with line-items for each answer in `AttemptAnswers`. The system scores the result and identifies if the user `passed`.

---

## 4. Development Workflows

### 4.1 Running the App Locally

Start the local server logic:
```bash
php artisan serve
```

Run Vite to compile frontend assets on-the-fly:
```bash
npm run dev
```

### 4.2 Adding a New Frontend Page

1. **Define the Route** in `routes/web.php`:
   ```php
   Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
   ```
2. **Create the Controller**:
   ```bash
   php artisan make:controller LeaderboardController
   ```
3. **Build the View**: Create `resources/views/leaderboard.blade.php`. Use the master application layout.
   ```blade
   <x-app-layout>
       <div class="max-w-7xl mx-auto px-4 ...">
           <!-- Content -->
       </div>
   </x-app-layout>
   ```

### 4.3 Modifying the Admin Panel (Filament)

To add manageable CRUD for a new table (e.g., `Certificates`):
1. **Create Model & Migration**: 
   ```bash
   php artisan make:model Certificate -m
   ```
2. **Generate Filament Resource**: 
   ```bash
   php artisan make:filament-resource Certificate
   ```
3. **Customize Forms & Tables** in `app/Filament/Resources/CertificateResource.php`. Define what inputs are needed and what columns should display in the data table.

### 4.4 Managing the Database

- If you change a migration file **that has already run**, you must refresh the database. *Warning: In development only!* 
  ```bash
  php artisan migrate:fresh --seed
  ```
- Make sure that when you run `--seed`, all necessary seeders are called inside `DatabaseSeeder.php` so the database remains in a usable state.

---

## 5. Coding Standards & Best Practices

1. **Fat Models, Skinny Controllers**: Keep complex logic inside Models or dedicated Service classes. Try to keep Controller methods focused on returning Views or standardizing API responses.
2. **Use Eloquent Relationships**: Do not execute raw JOINs unless absolutely necessary computationally. Let Eloquent relationships handle mapping (e.g., `$user->attempts()`).
3. **N+1 Problem Prevention**: When loading lists of data, use `with()` to eager load relations.
   ```php
   // Bad
   $lessons = Lesson::all(); // Triggers a query for every lesson when looping to find its questions
   
   // Good
   $lessons = Lesson::with('questions')->get();
   ```
4. **Tailwind Styling Utility**: Prefer generic Tailwind classes over writing manual CSS. Avoid creating custom CSS unless it's an incredibly unique micro-interaction. Use Flowbite UI components for consistency where applicable.

---

## 6. Useful Troubleshooting Commands

- **Clear all cached application data** (Useful when routes/configs aren't displaying updates):
  ```bash
  php artisan optimize:clear
  ```
- **Symlink visual assets** (Make `storage/app/public` accessible to the web):
  ```bash
  php artisan storage:link
  ```
- **Update Admin Assets** (If Filament styles become scrambled):
  ```bash
  php artisan filament:assets
  ```
- **Reset Filament User Password**:
  ```bash
  php artisan make:filament-user
  ```
  *(Entering an existing email will reset their password)*
