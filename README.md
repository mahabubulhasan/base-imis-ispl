# BASE IMIS

This repository hosts the source code for the IMIS web application, along with essential resources such as technical documentation, ERD, data dictionary, GeoServer guides, and more.

Please refer to the deployment documentation maintained in the GitHub organization under the repository deployment_documentation for detailed instructions on the process.

[Follow the link to View Deployment Documentation.](https://github.com/base-imis/deployment_documentation)


### Codebase Analysis: Mermaid Diagram of Code Flow

Based on the Laravel-based IMIS (Integrated Municipal Information System) codebase, I've analyzed the structure, focusing on entry points (routes), dependencies (framework/packages), and critical paths (authentication, role-based access, data flow). The app is modular, with core modules like FSM (Fecal Sludge Management), CWIS (City-Wide Inclusive Sanitation), Building Info, Utility Info, and Public Health. It uses PostgreSQL with PostGIS for spatial data, Spatie Laravel Permission for RBAC, and Laravel Sanctum for API auth.

Key Insights from Analysis:
- **Language/Framework**: PHP/Laravel 8, with JavaScript (jQuery/Bootstrap/Chart.js) for frontend.
- **Architecture**: MVC with service layer for business logic. Modules are organized in subdirectories (e.g., Fsm, Fsm).
- **Entry Points**: Web routes (e.g., landing page, auth-protected dashboards) and API routes (e.g., login, data retrieval for mobile/web clients).
- **Dependencies**: Laravel core, Spatie Permission (roles/permissions), DomPDF/Snappy (PDF generation), Maatwebsite Excel (exports), Yajra DataTables (grids), and spatial libs (PostGIS).
- **Critical Paths**: User authentication → Role/permission checks → Module access (e.g., FSM applications flow through controllers → services → models → DB). Public forms/API endpoints bypass auth for submissions. Data exports and maps rely on spatial queries.
- **Modules Overview**: ~10 modules (FSM, CWIS, etc.), each with controllers, models, and views. Shared components like Auth, Helpers, and Enums.

#### Mermaid Diagram
The diagram below visualizes the high-level flow. It starts from entry points, passes through middleware/guards, processes via controllers/services, interacts with models/DB, and renders outputs. Arrows indicate dependencies and data flow; critical paths are highlighted in bold.

```mermaid
flowchart TD
    %% Entry Points
    A1["Web Routes (routes/web.php)"] --> B["Middleware: Auth, Permissions (Spatie)"]
    A2["API Routes (routes/api.php)"] --> C["Middleware: Auth:Sanctum (Token-based)"]

    %% Authentication & Guards
    B --> D{"User Authenticated?"}
    C --> D
    D -->|"No"| E["Redirect to Login / API Error"]
    D -->|"Yes"| F["Role/Permission Check (User::hasRole/hasPermission)"]

    %% Core Processing
    F -->|"Authorized"| G["Controllers (Grouped by Modules)"]
    G --> H["Services (Business Logic, e.g., UserService, Fsm Services)"]
    H --> I["Models (Eloquent, e.g., User, Fsm\\Application)"]
    I --> J["Database (PostgreSQL + PostGIS, Schemas: auth, fsm, cwis, etc.)"]

    %% Outputs & Dependencies
    J --> K["Views (Blade Templates, e.g., dashboards, forms)"]
    K --> L["Assets (JS/CSS via Laravel Mix: Bootstrap, Chart.js, DataTables)"]
    L --> M["Rendered UI / API Responses"]

    %% Module Breakdown (Critical Paths)
    G --> G1["Auth Controllers (Login, User/Role Mgmt)"]
    G --> G2["FSM Controllers (Applications, Emptyings, Treatment Plants)"]
    G --> G3["CWIS Controllers (Dashboards, KPIs)"]
    G --> G4["BuildingInfo Controllers (Surveys, Structures)"]
    G --> G5["UtilityInfo Controllers (Roads, Sewers, Water Supply)"]
    G --> G6["PublicHealth Controllers (Water Samples, Hotspots)"]
    G --> G7["Other (Maps, Exports, Language)"]

    H --> H1["UserService (User CRUD with Roles)"]
    H --> H2["Module Services (e.g., Application Processing)"]

    I --> I1["User Model (HasRoles, Relationships to Modules)"]
    I --> I2["Module Models (e.g., Containment, ServiceProvider)"]

    %% External Dependencies
    J -.-> N["Dependencies: Spatie Permission, PostGIS, DomPDF, Excel"]
    L -.-> O["JS Libs: jQuery, Bootstrap, Chart.js"]

    %% Critical Paths
    A1 -.-> P["Public Forms (e.g., FSM Application Submission)"]
    P --> Q["API Endpoints (e.g., /save-emptying)"]
    Q --> R["Data Validation & Storage"]
    R --> S["Spatial Queries (Maps, Reports)"]
    S --> T["Exports (PDF/CSV via Snappy/Excel)"]

    %% Loops/Feedback
    M --> U["User Interactions (Forms, AJAX)"] --> G
```

#### Explanation of Diagram Elements
- **Entry Points**: Routes define access. Web routes handle browser requests (e.g., dashboards); API routes handle programmatic access (e.g., mobile apps submitting emptying data).
- **Dependencies**: Laravel provides MVC; Spatie handles RBAC; PostGIS enables spatial features (e.g., map queries); JS libs power interactive UI (e.g., DataTables for grids).
- **Critical Paths**:
  - **Authentication Flow**: Login → Middleware checks → Role validation → Access modules. Failure redirects/errors.
  - **Data CRUD**: Controllers receive requests → Services apply logic (e.g., validate FSM applications) → Models interact with DB (e.g., save containments) → Views render or APIs respond.
  - **Public/Public API Paths**: Unauthenticated routes (e.g., public FSM forms) → Direct to controllers → DB storage → Responses. Bypasses role checks.
  - **Spatial/Data Export Paths**: Modules like Maps/Controllers query PostGIS → Generate reports/PDFs via DomPDF/Snappy → Export CSVs via Maatwebsite Excel.
  - **Module-Specific**: FSM is central (applications → emptyings → treatment); others (e.g., CWIS KPIs) depend on shared models.
- **Modules & Flow**: Controllers are namespaced (e.g., `App\Http\Controllers\Fsm\ApplicationController`). Services encapsulate logic; Models use Eloquent with relationships (e.g., User belongsTo ServiceProvider).
- **Potential Bottlenecks**: DB queries (spatial joins), permission checks (cached but role-heavy), asset compilation (Mix).

This diagram is adaptable; if you need a deeper dive into a specific module (e.g., FSM flow), provide more details! For build/run, see previous response.