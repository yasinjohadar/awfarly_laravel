# Governorates + Cities Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rename current cities to governorates and add a real cities level (Country → Governorate → City) across admin, API, forms, filters, and seeders.

**Architecture:** Forward migration renames `cities` → `governorates` and user `city_id` → `governorate_id`, creates new `cities` with `governorate_id`, backfills required `city_id`, updates permissions/routes/Livewire/API resources and filters. Breaking API rename: old `cityId` becomes `governorateId`; new `cityId` is the district/city.

**Tech Stack:** Laravel 8, Livewire 2, Spatie permissions, Passport API, MySQL

**Spec:** `docs/superpowers/specs/2026-07-27-governorates-cities-design.md`

---

## File map

| Area | Create | Modify / rename |
|------|--------|-----------------|
| Migration | `database/migrations/2026_07_27_140000_rename_cities_to_governorates_and_add_cities.php` | — |
| Models | `app/Models/Countries/Governorates/Governorate.php` | Move/replace `City` to `app/Models/Countries/Cities/City.php` (new schema); `Country.php` relations |
| Users | — | `AdvertiserUser.php`, `CustomerUser.php` |
| Seeders | — | `SyriaGeoSeeder.php`, `DemoUsersSeeder.php`, `DemoContentSeeder.php`, `RolesAndPermissionsTableSeeder.php`, `PermissionsGroupsAndDataTableSeeder.php` |
| Admin routes | — | `routes/web/admin.php` |
| Admin controllers | `CountriesGovernoratesController`, keep/adapt cities controller for new cities | rename/adapt Livewire under `Governorates/` + new `Cities/` |
| Admin views/langs | governorates views + cities views | sidebar, langs AR/EN |
| API | `GovernoratesController`, resources | `routes/api/api.php`, `CountriesResource`, account/user resources, register, filters |
| Components | `governorates-select2`, `cities-select2` (by governorate) | advertisers/customers create/edit |

---

### Task 1: Schema migration

**Files:**
- Create: `database/migrations/2026_07_27_140000_rename_cities_to_governorates_and_add_cities.php`

- [ ] **Step 1: Write migration** that:
  1. Drops FKs from `advertisers_users.city_id` and `customers_users.city_id` pointing at `cities`
  2. Renames table `cities` → `governorates`
  3. Renames columns `advertisers_users.city_id` → `governorate_id`, `customers_users.city_id` → `governorate_id`
  4. Re-adds FKs `governorate_id` → `governorates.id`
  5. Creates new `cities` (`id`, `order`, `governorate_id` FK cascade, `name_ar`, `name_en`, unique `(governorate_id, name_ar)`, unique `(governorate_id, name_en)`, timestamps)
  6. Adds nullable `city_id` on advertisers_users and customers_users (FK to new cities) — NOT NULL applied in a later step after seed/backfill in same migration only if cities already exist; for fresh installs seed runs after migrations, so leave nullable in migration and enforce in app validation + a second migration `2026_07_27_140001_make_city_id_required.php` that only runs when no nulls remain, OR document that `migrate:fresh --seed` then optional tighten. **Decision for this plan:** keep `city_id` nullable at DB level for migration safety; application validation requires it; seeder always fills it. (Avoids chicken-egg on fresh migrate before seed.)
  7. On `advertisements`: rename JSON column `cities` → `governorates` (data already holds old city/governorate IDs); add new JSON `cities` nullable default `[]`

```php
// Core of up():
Schema::table('advertisers_users', function (Blueprint $table) {
    $table->dropForeign(['city_id']);
});
Schema::table('customers_users', function (Blueprint $table) {
    $table->dropForeign(['city_id']);
});
Schema::rename('cities', 'governorates');
Schema::table('advertisers_users', function (Blueprint $table) {
    $table->renameColumn('city_id', 'governorate_id');
});
Schema::table('customers_users', function (Blueprint $table) {
    $table->renameColumn('city_id', 'governorate_id');
});
// re-add FKs to governorates, create cities, add city_id nullable, rename ads cities→governorates, add ads cities
```

Note: `doctrine/dbal` may be required for `renameColumn` on Laravel 8.

- [ ] **Step 2: Run migration on local**

Run: `php artisan migrate --force`  
Expected: success (or `migrate:fresh` later with seed)

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_07_27_140000_*.php
git commit -m "migrate: rename cities to governorates and add cities table"
```

---

### Task 2: Models

**Files:**
- Create: `app/Models/Countries/Governorates/Governorate.php`
- Modify: `app/Models/Countries/Cities/City.php` (new fillable/relations)
- Modify: `app/Models/Countries/Country.php`
- Modify: `app/Models/Users/Advertisers/AdvertiserUser.php`
- Modify: `app/Models/Users/Customers/CustomerUser.php`

- [ ] **Step 1: Governorate model** — table `governorates`, fillable `order,country_code,name_ar,name_en`, `country()`, `cities()`
- [ ] **Step 2: Rewrite City model** — fillable `order,governorate_id,name_ar,name_en`, `governorate()`, remove `country_code`
- [ ] **Step 3: Country** — replace `cities()` with `governorates()` → Governorate
- [ ] **Step 4: Users** — fillable `governorate_id` + `city_id`; relations `governorate()` and `city()`; remove old `city()` that pointed at governorate-as-city
- [ ] **Step 5: Commit** `feat: add Governorate model and rewire City/Country/User relations`

---

### Task 3: Seeders

**Files:**
- Modify: `database/seeders/SyriaGeoSeeder.php`
- Modify: `database/seeders/DemoUsersSeeder.php`
- Modify: `database/seeders/DemoContentSeeder.php`
- Modify: `database/seeders/RolesAndPermissionsTableSeeder.php`
- Modify: `database/seeders/PermissionsGroupsAndDataTableSeeder.php` (if groups reference cities)

- [ ] **Step 1: SyriaGeoSeeder** — truncate cities then governorates then countries; seed SY + 14 governorates into `governorates`; for each governorate insert 3–8 cities (Damascus: الميدان، باب سريجة، المزة، كفرسوسة، جوبر؛ etc.)
- [ ] **Step 2: DemoUsersSeeder** — set both `governorate_id` and `city_id` (city belonging to that governorate)
- [ ] **Step 3: DemoContentSeeder** — ads use `governorates` + `cities` JSON
- [ ] **Step 4: Permissions** — `governorates.inquiry/add/edit/delete` + `cities.inquiry/add/edit/delete`; assign both to super admin; update permission groups labels
- [ ] **Step 5: Run** `php artisan migrate:fresh --seed --force`  
  Expected: no errors; SY has 14 governorates and many cities; users have both FKs
- [ ] **Step 6: Commit** `seed: syria governorates and cities with permissions`

---

### Task 4: Admin routes + controllers + Livewire rename (governorates)

**Files:**
- Modify: `routes/web/admin.php`
- Rename/adapt: `CountriesCitiesController` → governorates controller; Livewire `Countries/Cities/*` → `Countries/Governorates/*` (or keep path but change model)
- Views under `resources/views/admin/pages/countries/governorates/` (move from cities)
- Langs: rename keys cities→governorates in AR/EN for this level
- Sidebar: label المحافظات, route `admin.governorates.*`, permission `governorates.*`

Practical approach matching codebase:
1. Rename route resource `cities` → `governorates`
2. Point Livewire components at `Governorate` model
3. Update all `__('...cities...')` admin strings for this level to governorates
4. Keep nested drill-down from country → governorates (existing CountriesComponent)

- [ ] **Step 1: Routes** `admin.governorates.index/create`, `admin.country.governorates` (Select2 by country_code)
- [ ] **Step 2: Update Livewire inquiry/create/sort to use Governorate**
- [ ] **Step 3: Sidebar + langs**
- [ ] **Step 4: Smoke** open `/admin/governorates` after login
- [ ] **Step 5: Commit** `admin: rename cities management to governorates`

---

### Task 5: Admin cities (new level) CRUD

**Files:**
- Create Livewire: `CitiesInquiryComponent`, `CitiesCreateComponent`, `CitiesSortComponent` under `app/Http/Livewire/Countries/Cities/` (new meaning)
- Create views: `admin/pages/countries/cities/*`, modals, langs
- Route: `admin.cities.*`, Select2 `admin.governorate.cities` by `governorate_id`
- Sidebar under settings: المدن

Mirror governorates UX: index with add button, create page, edit/delete modals, sort, filter by governorate, nest under governorate from inquiry.

- [ ] **Step 1: Routes + controller stubs**
- [ ] **Step 2: Livewire inquiry/create/sort + views + langs**
- [ ] **Step 3: Sidebar entry**
- [ ] **Step 4: Smoke CRUD one city under Damascus**
- [ ] **Step 5: Commit** `admin: add cities CRUD under governorates`

---

### Task 6: Admin user forms (advertisers + customers)

**Files:**
- Modify create/edit Livewire + blades + modals for advertisers and customers
- Components: country → governorates select2 → cities select2
- Validation: both required; city belongs to governorate

- [ ] **Step 1: Update AdvertisersCreate/Inquiry edit**
- [ ] **Step 2: Update CustomersCreate/Inquiry edit**
- [ ] **Step 3: Smoke create advertiser with دمشق + الميدان**
- [ ] **Step 4: Commit** `admin: require governorate and city on users`

---

### Task 7: System API geography

**Files:**
- Create: `app/Http/Controllers/API/System/Countries/Governorates/GovernoratesController.php`
- Create: `app/Http/Resources/System/Countries/Governorates/GovernoratesResource.php`
- Modify: `CitiesController` + `CitiesResource` for new cities
- Modify: `CountriesResource` nest governorates
- Modify: `routes/api/api.php`

Endpoints:
- `GET /system/governorates`, `GET /system/governorates/{id}`
- `GET /system/cities?governorateId=`, `GET /system/cities/{id}`
- Remove old cities-as-governorates behavior

- [ ] **Step 1: Implement controllers/resources/routes**
- [ ] **Step 2: Manual curl/Postman check JSON shape**
- [ ] **Step 3: Commit** `api: expose governorates and cities endpoints`

---

### Task 8: API register, account, filters

**Files (representative — update all that reference cityId as governorate):**
- `RegisterController.php`
- `Advertisers/Account/AccountController.php`, `Customers/Account/AccountController.php`
- Account + user resources
- Community offers/posts controllers (guest/advertiser/customer)
- Advertisements controllers
- NotificationsController
- Lang validation messages AR/EN

Rules:
- Request fields: `governorateId` + `cityId` (both required on register/update)
- Filters: `governorateId` and/or `cityId`
- Resources return both IDs and names

- [ ] **Step 1: Register + account update**
- [ ] **Step 2: Resources**
- [ ] **Step 3: List filters** (offers, posts, ads, advertisers listing, notifications)
- [ ] **Step 4: Commit** `api: use governorateId and cityId across auth and filters`

---

### Task 9: Advertisements targeting

**Files:**
- Admin advertisement Livewire create/edit/show
- API advertisement store/filter
- Models/casts if any

- [ ] **Step 1: Persist JSON `governorates` + `cities`**
- [ ] **Step 2: Filters use both**
- [ ] **Step 3: Commit** `ads: target by governorates and cities`

---

### Task 10: Final verification

- [ ] **Step 1:** `php artisan migrate:fresh --seed --force`
- [ ] **Step 2:** Confirm counts: 1 country, 14 governorates, cities > 14, users have both FKs
- [ ] **Step 3:** Admin smoke: governorates, cities, create advertiser
- [ ] **Step 4:** API smoke: `/system/governorates`, `/system/cities?governorateId=1`
- [ ] **Step 5: Commit** any leftover fixes `fix: geography rename leftovers`

---

## Spec coverage checklist

| Spec item | Task |
|-----------|------|
| Rename cities→governorates | 1–2 |
| New cities table + FK | 1–2 |
| User governorate_id + city_id | 1–2, 6, 8 |
| Ads JSON | 1, 9 |
| Admin CRUD both levels | 4–5 |
| Permissions | 3 |
| API system | 7 |
| Register/profile/filters | 8 |
| Syria seed cities | 3 |
| Full migrate:fresh | 10 |

## Notes for implementers

- Do **not** edit the 2020 create_cities migration in place; use 2026 forward migration so production can upgrade.
- Laravel 8 `renameColumn` needs `doctrine/dbal`.
- After Task 3, prefer `migrate:fresh --seed` locally; production uses migrate + one-time backfill SQL inside migration for existing rows (add backfill block in Task 1 migration: after creating cities, if users exist without city_id, assign random city in their governorate — seed data may not exist on production yet; production deploy order: migrate schema → deploy code that can seed cities via artisan → backfill. For this project, include city inserts for SY inside migration **or** document run seeder then backfill command. **Preferred:** SyriaGeoSeeder handles fresh; migration `up()` includes backfill only when `governorates` already have rows and new `cities` get seeded inline for SY in the migration after create. Keep seeder as source of truth for fresh installs; migration backfill: if cities empty skip; if cities present assign null city_ids.)
