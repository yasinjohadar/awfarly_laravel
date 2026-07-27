# Design: Governorates + Cities (3-level geography)

**Date:** 2026-07-27  
**Status:** Approved for planning  
**Stack:** Laravel 8 + Livewire 2 + Passport API

## Goal

Add a third geographic level under Syria’s current “cities” (which are actually governorates):

```
Country (دولة)
  └── Governorate (محافظة)   ← current `cities` table, renamed
        └── City (مدينة)     ← new table (e.g. الميدان، باب سريجة، حرستا)
```

Users (advertisers + customers) select **both** governorate and city. City is **required for everyone**. Filtering follows the same patterns as today’s city filters, extended with governorate + city.

## Decisions (locked)

| Topic | Choice |
|-------|--------|
| User location | Governorate **and** city (city implies governorate) |
| Naming (UI/AR) | Current level → **المحافظات**; new level → **المدن** |
| Scope | Full app: admin + API + register/profile + filters |
| Existing data | City required for all; seed cities then assign users/ads |
| Schema approach | Proper rename (`cities` → `governorates`, new `cities`) — breaking API change |

## Data model

### Tables

**`governorates`** (renamed from `cities`)

| Column | Notes |
|--------|--------|
| `id` | PK |
| `order` | sort |
| `country_code` | FK → `countries.code` |
| `name_ar`, `name_en` | unique names |
| timestamps | |

**`cities`** (new)

| Column | Notes |
|--------|--------|
| `id` | PK |
| `order` | sort |
| `governorate_id` | FK → `governorates.id` (cascade) |
| `name_ar`, `name_en` | unique per governorate (composite unique preferred) |
| timestamps | |

### User tables

`advertisers_users` and `customers_users`:

- Rename `city_id` → `governorate_id` (FK → `governorates.id`)
- Add `city_id` (FK → `cities.id`, **NOT NULL** after backfill)
- Validation: selected city must belong to selected governorate

### Advertisements

Today: JSON `countries`, JSON `cities` (arrays of IDs).

After:

- Keep / rename targeting JSON to `governorates` (array of governorate IDs) — migrate existing JSON city IDs (old meaning) into governorate IDs
- Add JSON `cities` (array of new city IDs) for finer targeting
- Filter APIs: `whereJsonContains` on both, same spirit as today

### Models / relationships

- `Country` → `hasMany` Governorate
- `Governorate` → `belongsTo` Country; `hasMany` City
- `City` → `belongsTo` Governorate
- Advertiser/Customer → `belongsTo` Governorate + City

## Migration plan

1. Rename table `cities` → `governorates` (preserve data / FKs carefully).
2. On `advertisers_users` / `customers_users`: rename `city_id` → `governorate_id`.
3. Create new `cities` table with `governorate_id`.
4. Seed cities for each Syrian governorate (multiple districts/neighborhoods).
5. Backfill: for every user (and ad targeting), set `city_id` to a random city **inside** their `governorate_id`.
6. Enforce NOT NULL on `city_id` after backfill.
7. Update advertisements JSON keys/values as above.
8. Rename permissions: `cities.*` → `governorates.*`; add new `cities.*` for the new level. Update roles/seeders.

Prefer one (or few) dedicated migration(s) rather than editing old 2020 migrations in place, so production can migrate forward.

## Admin panel

### Sidebar (under Settings)

- **المحافظات** → former cities admin (routes/names become `admin.governorates.*`)
- **المدن** → new CRUD (`admin.cities.*`)
- Direct links (no submenu for inquiry/create); add buttons on index pages (existing UX pattern)

### Features (mirror current cities UX)

Governorates & cities each support:

- Inquiry (datatable), create, edit modal, delete, sort
- Cities filtered / nested by governorate (like cities nested under country today)
- Select2 / dependent dropdowns: country → governorates; governorate → cities

### Permissions

| Old | New |
|-----|-----|
| `cities.inquiry/add/edit/delete` | `governorates.inquiry/add/edit/delete` |
| — | `cities.inquiry/add/edit/delete` (new meaning) |

## API (breaking)

### System geography

| Before | After |
|--------|--------|
| `GET /system/countries` (+ nested cities) | Nested **governorates** (optionally with cities) |
| `GET /system/cities` | `GET /system/governorates` |
| `GET /system/cities/{id}` | `GET /system/governorates/{id}` |
| — | `GET /system/cities?governorateId=` |
| — | `GET /system/cities/{id}` |

Resources:

- `GovernoratesResource`: `id`, `name`, `countryCode`
- `CitiesResource`: `id`, `name`, `governorateId` (and optionally governorate name / countryCode)

### Account / users / filters

| Before | After |
|--------|--------|
| `cityId` (= governorate) | `governorateId` |
| — | `cityId` (= city) |

Update register, profile update, list filters (offers/posts/ads/notifications) to accept:

- `governorateId` and/or `cityId`
- Filtering by city implies that city; filtering by governorate includes all its cities / users in that governorate (same principle as current city filter)

Mobile apps **must** ship with this rename in the same release.

## Forms (admin + API + frontend if any)

Dependent selects:

1. Country (if multi-country) → Governorates  
2. Governorate → Cities  

Both governorate and city required on create/update for advertisers and customers.

## Seed data

Extend `SyriaGeoSeeder` (or split):

1. Country SY + 14 governorates (current data, table renamed)
2. For each governorate, seed a realistic set of cities/districts (Arabic + English names)
3. `DemoUsersSeeder` / ads: assign `governorate_id` + `city_id` consistently
4. Permission seeder: governorates + cities permissions

## Out of scope

- Renaming historical log payloads beyond what is needed for permissions display
- Soft-delete / archive for geo entities
- Map coordinates / GeoJSON
- Non-Syria detailed city lists (structure supports them; seed focuses on SY)

## Success criteria

- [ ] Admin can CRUD/sort governorates and cities
- [ ] Register/profile require governorate + city with dependency validation
- [ ] API exposes governorates/cities; account resources return both IDs
- [ ] Offers/posts/ads filters work with governorateId and cityId
- [ ] Fresh `migrate:fresh --seed` yields SY + governorates + cities + users with both FKs
- [ ] Production migrate path renames without data loss and backfills city_id

## Risks

- **Breaking mobile API** — coordinate app release
- Unique name constraints on cities: prefer unique `(governorate_id, name_ar)` / `(governorate_id, name_en)` so same district name can exist in different governorates
- Advertisement JSON migration must map old city IDs → governorate IDs correctly before introducing new city IDs
