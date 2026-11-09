# Module Refactoring & Standardization Guide

This document outlines the standard workflow and step-by-step instructions for modularizing any feature/module within the `tori-international` ERP system. 

When giving instructions to the AI assistant, you can simply refer to this file or say **"Follow the Module Refactoring Guide for the [X] Module"**, and the assistant will automatically know the exact steps to execute.

## Standard Refactoring Workflow

### 1. File Relocation (Controllers, Models, Views)
- **Controllers:** Move relevant controllers to `app/Http/Controllers/[Module_Name]/`.
- **Models:** Move domain-specific models to `app/Models/[Module_Name]/` (Exclude shared models like `Supplier` if they are used across multiple modules).
- **Views:** Move blade files to `resources/views/[module_name]/`.

### 2. Namespace & Dependency Updates
- Search and replace the old namespaces with the new ones.
  - Example: `App\Models\OldModel` -> `App\Models\[Module_Name]\OldModel`.
- Update the `view('...')` paths in the controllers to reflect the new `resources/views/[module_name]/` directory.

### 3. Route Modularization
- Move all module-related routes (including reports) to `routes/[module_name].php`.
- Remove redundant/duplicate routes from `routes/reports.php` or `routes/web.php`.
- Ensure proper middleware is assigned (e.g., `middleware('permission:[specific_permission]')`).

### 4. Roles & Permissions Standardization
- Navigate to `database/seeders/PermissionSeeder.php`.
- Create or update the module's main permission group (e.g., `'name' => 'module_name', 'sort' => X`).
- Add specific permissions as children (e.g., `module_reports`, `module_payment`, etc.).
- Run `php artisan db:seed --class=PermissionSeeder` to sync the database.

### 5. Sidebar Navigation & Active States
- Update the module's menu file in `resources/views/layouts/menu/modular/__[module_name].blade.php`.
- Replace old route names with the newly defined ones.
- Update `@if(auth()->user()->can('...'))` blocks with the exact new permissions.
- **CRITICAL:** Update `resources/views/layouts/partial/__sidebar_modular.blade.php` by adding the route prefixes to the `$moduleMap` array. This ensures the top-level module remains "Active" when navigating through the deep nested links.

### 6. Dashboard Integration
- **Rule:** Every module must have a dedicated premium dashboard to serve as its entry point.
- **Controller:** Create `DashboardController.php` within `app/Http/Controllers/[Module_Name]/` with an `index()` method that aggregates key metrics.
- **View:** Create `resources/views/[module_name]/dashboard.blade.php` following the premium dashboard UI pattern (using `@include('layouts.partial.__premium_dashboard_css')`, welcome banners, and metric cards).
- **Routing:** Add the `dashboard` route at the top of `routes/[module_name].php`.
- **Navigation:**
  - Add the `Dashboard` menu item at the absolute top of `resources/views/layouts/menu/modular/__[module_name].blade.php`.
  - Update `resources/views/layouts/partial/__quick_apps.blade.php` so the module's quick app icon redirects to `[module_name].dashboard` instead of the index page, and ensure it is wrapped with appropriate `@can` or `@if(auth()->user()->can(...))` checks.

---

## Critical Security & Routing Standards (Learned from Trainee Management)

### 1. Route Precedence (Fixing 404/ModelNotFound Errors)
- **Rule:** Always place generic resource routes (`Route::resource(...)`) at the absolute **BOTTOM** of the route file or routing group. 
- **Reason:** If a resource route like `Route::resource('trainee', ...)` is placed before specific static routes (like `trainee/tasks` or `trainee/datatable`), Laravel's router will mistake the static segment (`tasks`) for the dynamic parameter (`{trainee}`) and try to fetch it from the database, resulting in a `No query results for model` (404) exception.

### 2. Controller-Level Permission Checks (`abort_if`)
- **Rule:** Do NOT use `$this->middleware('permission:...')` inside controller constructors (`__construct()`).
- **Standard:** Use inline `abort_if` checks at the very beginning of **each** controller method. 
- **Example:**
  ```php
  public function create()
  {
      abort_if(!auth()->user()->hasPermissionTo('module_create'), 403, 'Unauthorized');
      // Logic...
  }
  ```

### 3. Route-Level Permission Middleware
- **Rule:** Attach specific permission middlewares directly onto the route definitions or route groups in the module's route file (`routes/module_name.php`).
- **Example:** `Route::prefix('visits')->middleware('permission:module_visits')->group(...)` or `Route::resource('module', Controller::class)->middleware('permission:module_read')`.

### 4. Modular Menu Permissions
- **Rule:** Do not rely solely on the top-level parent `@if(auth()->user()->can('module_management'))`.
- **Standard:** Every specific sidebar link (`<li>`) within `resources/views/layouts/menu/modular/__[module_name].blade.php` must be explicitly wrapped with its corresponding specific permission check (e.g., `@if(auth()->user()->can('module_tasks'))`).

---

**AI Instruction Prompt Example:**
> *"AI, please refactor the `Sales` module following the standard `MODULE_REFACTORING_GUIDE.md`. The models `SaleOrder` and `SalePayment` should go to the `Sales` folder, and the Sales Report should be moved to its own module."*
