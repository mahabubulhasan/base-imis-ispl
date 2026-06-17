# Building Form Metadata API — Mobile Integration Contract

Verified against the backend implementation and `tests/Feature/Api/BuildingFormMetadataTest.php` (run via Docker: `docker compose exec app php artisan test --filter=BuildingFormMetadata`).

**Base URL:** `http://localhost:7010` (Docker web) or your deployment host.

**Auth:** `Authorization: Bearer {token}` (Sanctum — same as existing building APIs).

**Headers:** `Accept: application/json`

---

## Endpoints

| Method | URL | When to call |
|--------|-----|--------------|
| `GET` | `/api/building-info/buildings/form-metadata` | Once on map screen mount (prefetch 8 static maps) |
| `GET` | `/api/building-info/buildings/{bin}/edit-data` | Edit screen open (WMS path) |
| `POST` | `/api/building-info/buildings` | Create save (unchanged) |
| `POST` | `/api/building-info/update/{bin}` | Edit save (unchanged) |
| ~~`GET`~~ | ~~`/api/building-info/buildings/create-data`~~ | **Deprecated** — use `form-metadata` |

**Search endpoints** (on-demand; see [building-form-search-apis-migration.md](./building-form-search-apis-migration.md)):

| Method | URL |
|--------|-----|
| `GET` | `/api/building-info/roads/search` |
| `GET` | `/api/building-info/sewers/search` |
| `GET` | `/api/building-info/drains/search` |
| `GET` | `/api/building-info/water-supplies/search` |
| `GET` | `/api/building-info/lics/search` |
| `GET` | `/api/building-info/buildings/bins/search` |

---

## Response envelope

```json
{
  "status": 200,
  "message": "Human-readable message",
  "data": { }
}
```

| HTTP | Body |
|------|------|
| 401 | `{ "message": "Unauthenticated." }` |
| 404 | `{ "status": 404, "message": "Building not found.", "data": null }` |
| 422 | `{ "status": 422, "message": "...", "errors": { }, "data": null }` |
| 500 | `{ "status": 500, "message": "...", "data": null }` |

`create-data` still returns 200 but includes headers:

- `Deprecation: true`
- `Link: </api/building-info/buildings/form-metadata>; rel="successor-version"`

---

## GET `/api/building-info/buildings/form-metadata`

Returns **8 global dropdown maps**. Cache in Redux on map load. Large/contextual lookups (roads, sewers, drains, water supply, LICs, BINs) use [search APIs](./building-form-search-apis-migration.md) instead.

### TypeScript

```typescript
type OptionMap = Record<string, string>;

type FormMetadata = {
  ward: OptionMap;
  structure_type: OptionMap;
  functional_use: OptionMap;
  usecatgs_json: Record<string, OptionMap>;
  water_source: OptionMap;
  toilet_connection: OptionMap;
  defecation_place: OptionMap;
  ctpt: OptionMap;
};
```

### Verified example (trimmed — seeded DB)

```json
{
  "status": 200,
  "message": "Building form metadata fetched successfully.",
  "data": {
    "ward": { "1": "1", "2": "2" },
    "structure_type": { "1": "Katcha", "3": "Pucca" },
    "functional_use": { "8": "Assembly", "9": "Business (Offices)" },
    "usecatgs_json": {
      "7": { "26": "Agriculture Farm", "27": "LiveStocks" }
    },
    "water_source": { "6": "Deep Boring", "2": "Jar Water" },
    "toilet_connection": { "2": "Drain Network", "3": "Septic Tank" },
    "defecation_place": { "9": "Community Toilet", "10": "Open Defecation" },
    "ctpt": { "108": "108 - CT004", "109": "109 - CT005" }
  }
}
```

**Notes:**

- All map keys are stringified IDs; values are display labels.
- `usecatgs_json` is a nested object (not a JSON string).
- `ctpt` is a single key (no separate `capitalizedctpt`).
- Removed keys (`road_code`, `sewer_code`, `drain_code`, `water_supply`, `lic_names`, `building_bin`, `preconnected_bin`) — use search endpoints documented in [building-form-search-apis-migration.md](./building-form-search-apis-migration.md).

### Legacy key mapping

| Old key (`create-data` / old `edit-data`) | New key (`form-metadata`) |
|-------------------------------------------|---------------------------|
| `buildingBin` | `building_bin` |
| `bin` | `preconnected_bin` |
| `usecatgsJson` | `usecatgs_json` |
| `toiletConnection` | `toilet_connection` |
| `defecationPlace` | `defecation_place` |
| `licNames` | `lic_names` |
| `waterSupply` | `water_supply` |
| `capitalizedctpt` / `ctpt` | `ctpt` |

### Mobile normalization

```typescript
const toOptions = (map: Record<string, string>) =>
  Object.entries(map ?? {}).map(([value, label]) => ({ value, label }));

const useCategories = metadata.usecatgs_json[functionalUseId] ?? {};
```

---

## GET `/api/building-info/buildings/{bin}/edit-data`

Returns **building field values + containment cards only**. No global dropdown maps.

### TypeScript

```typescript
type EditData = {
  building: Record<string, string | number | null>;
  buildingSurvey: null;
  containment: ContainmentCard[];
};

type ContainmentCard = {
  containment_id: string;
  toilet_name: string | null;
  sanitation_system: string | number | null;
  containment_volume: string | number | null;
  containment_location: string | null;
};
```

### Verified example (BIN `B49598`, seeded DB)

```json
{
  "status": 200,
  "message": "Building edit form data fetched successfully.",
  "data": {
    "building": {
      "bin": "B49598",
      "building_associated_to": null,
      "ward": 5,
      "road_code": "20512510050719-00",
      "house_number": "B49598-0809-00",
      "house_locality": "Samsur Huda sumon, Dokhla Bari Road, Ward-5.0, Lashmipur Purashava, Lakshmipur.",
      "tax_code": "05-035-0809-00",
      "structure_type_id": 2,
      "functional_use_id": 1,
      "use_category_id": 68,
      "owner_name": "Samsur Huda sumon",
      "owner_gender": "Male",
      "owner_contact": 1799784497,
      "main_building": "1",
      "lic_status": "0",
      "water_source_id": 4,
      "toilet_status": "1",
      "sanitation_system_id": 3,
      "sewer_code": null,
      "drain_code": "D-R140-00",
      "ctpt_name": null
    },
    "buildingSurvey": null,
    "containment": [
      {
        "containment_id": "C006983",
        "toilet_name": "Septic Tank connected to Drain Network",
        "sanitation_system": 3,
        "containment_volume": "7.40",
        "containment_location": "Inside the house"
      }
    ]
  }
}
```

**Notes:**

- `building` is a flat object (no nested `StructureType`, `Owners`, etc.).
- `geom` is excluded from the API payload.
- `main_building` is `"1"` / `"0"` string.
- `ctpt_name` is a comma-separated string of CTPT IDs when shared toilets exist, else `null`.
- Numeric DB columns may be returned as numbers; mobile `normalizeBuildingValues` should coerce as today.
- Extra audit columns (`created_at`, `user_id`, etc.) may appear; mobile can ignore unknown keys.

### Keys removed from edit-data (use form-metadata cache)

`ward`, `road_code`, `structure_type`, `functional_use`, `usecatgsJson`, `water_source`, `toiletConnection`, `defecationPlace`, `licNames`, `ctpt`, `capitalizedctpt`, `sewer_code`, `drain_code`, `buildingBin`, `bin`, `waterSupply`, `use_category_id`, `containment_id`, `containment_type`, `drain_status`, `sewer_status`

---

## Mobile prefetch flow

```
BuildingMapScreen mount
  └─ GET /api/building-info/buildings/form-metadata
       └─ Redux: map.buildingFormMetadata
            ├─ CreateBuildingAfterDrawScreen → read cache (no metadata API)
            └─ BuildingEditScreen → dropdowns from cache
                 └─ GET /{bin}/edit-data → values + containment only
```

**Error handling:**

- Metadata prefetch fails → show retry; do **not** fall back to `create-data`.
- Form opens with empty cache → block form, retry prefetch.
- `edit-data` 404 → show not found, navigate back.

---

## Metadata → form field map

| Form field | Source | Visible when |
|------------|--------|--------------|
| `ward` | form-metadata `ward` | Always |
| `road_code` | [search: `/roads/search`](./building-form-search-apis-migration.md) | Always (create) |
| `structure_type_id` | form-metadata `structure_type` | Always |
| `functional_use_id` | form-metadata `functional_use` | Always |
| `use_category_id` | form-metadata `usecatgs_json[functional_use_id]` | When functional use requires category |
| `water_source_id` | form-metadata `water_source` | Always |
| `building_associated_to` | [search: `/buildings/bins/search`](./building-form-search-apis-migration.md) | Create: `main_building === "0"` |
| `lic_id` | [search: `/lics/search`](./building-form-search-apis-migration.md) | `lic_status === "1"` |
| `sanitation_system_id` | form-metadata `toilet_connection` | `toilet_status === "1"` |
| `defecation_place` | form-metadata `defecation_place` | `toilet_status === "0"` |
| `ctpt_name` | form-metadata `ctpt` | `defecation_place === "9"` |
| `build_contain` | [search: `/buildings/bins/search`](./building-form-search-apis-migration.md) | Create: `sanitation_system_id === "11"` |
| `sewer_code` | [search: `/sewers/search`](./building-form-search-apis-migration.md) | `sanitation_system_id === "1"` |
| `drain_code` | [search: `/drains/search`](./building-form-search-apis-migration.md) | `sanitation_system_id === "2"` |
| `watersupply_pipe_code` | [search: `/water-supplies/search`](./building-form-search-apis-migration.md) | Create: `water_source_id === "1"` |

**Edit-only (no searchable dropdown):** `building_associated_to` and `watersupply_pipe_code` are plain text inputs.

---

## curl examples (Docker)

```bash
cd /home/stl/projects/base-imis-docker

# Obtain token via login, then:
curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  http://localhost:7010/api/building-info/buildings/form-metadata | jq .

curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" \
  http://localhost:7010/api/building-info/buildings/B49598/edit-data | jq .
```
