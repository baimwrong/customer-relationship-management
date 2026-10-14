# Enterprise CRM

> **Production-Ready Customer Relationship Management Web Application**  
> Architected with **Laravel 13**, **PostgreSQL 18** readiness, modern **Bootstrap 5**, **FontAwesome / Bootstrap Icons**, and **Chart.js**.  
> **100% Offline & Internal Network (Air-Gapped / Intranet) Ready** — All CSS, JavaScript, and WebFont libraries are self-contained locally in `public/vendor/`. No internet access or external CDN connections required.

---

## 🌟 Executive Overview & Key Features

Enterprise CRM is designed for high-performing sales organizations, revenue operations, and executive leadership. It provides end-to-end sales lifecycle visibility, automated qualification scoring, transactional lead conversion, and bulk data ingestion capabilities.

### 1. Executive Sales Analytics Dashboard
- **Financial KPIs**: Real-time computation of Total Pipeline Value, Weighted Forecast (probability-adjusted), Closed Won Revenue, and Win Rate percentage.
- **Velocity Metrics**: Total active leads, conversion velocity percentage, and total account counts.
- **Local Interactive Charts (Chart.js)**:
  - **Pipeline Funnel Distribution**: Bar chart categorizing deal values across all sales stages.
  - **Revenue Trends**: 6-month historical won revenue trajectory.
  - **Leads by Acquisition Source**: Doughnut chart illustrating lead generation channels.
- **Sales Rep Leaderboard**: Dynamic ranking of top sales representatives by won deal revenue.
- **Actionable Task Feeds & Real-time Interaction Timeline**: Direct tracking of overdue and upcoming items.

### 2. Opportunity Pipeline & Visual Kanban Board
- **List & Visual Kanban Views**: Toggle seamlessly between tabular data and an agile drag/move Kanban pipeline board.
- **Configurable Sales Stages**:
  - `Qualification` (20% probability)
  - `Needs Analysis` (40% probability)
  - `Proposal / Quote` (60% probability)
  - `Negotiation` (80% probability)
  - `Closed Won` (100% probability, auto-timestamps `closed_at`)
  - `Closed Lost` (0% probability, structured loss reasoning capture)
- **Weighted Value Forecasting**: Real-time calculation based on stage probabilities.
- **Audit Logging**: Automatic activity note creation whenever an opportunity changes stage.

### 3. Sales Leads & Transactional Lead Conversion
- **Prospect Lifecycle**: Capture contact details, company association, job title, and estimated deal potential.
- **Qualification Scoring**: 0–100 lead score progression for pipeline qualification.
- **Atomic One-Click Lead Conversion Workflow**:
  - Automatically transitions lead to `Converted` status with timestamps.
  - Optionally creates a new **Company Account** or associates with an existing one.
  - Automatically generates the verified **Contact Person**.
  - Optionally spawns an active **Pipeline Opportunity / Deal** with user-selected initial stage and target close date.
  - Transfers all previous lead activities and follow-up tasks to the new entities inside a **Database Transaction**.

### 4. 360-Degree Company & Account Management
- Comprehensive corporate records: annual revenue, employee count, industry sector, corporate website, address, and account status (`customer`, `prospect`, `partner`, `active`, `inactive`).
- Complete unified account view showcasing all linked contacts, all associated pipeline deals, interaction timeline, and pending tasks.

### 5. Stakeholder Contacts Directory
- Track key decision-makers, procurement leads, and corporate champions.
- Direct communication actions: mailto links, phone links, and contact status tracking (`vip`, `active`, `lead`, `inactive`).

### 6. Engagement & Customer Interaction Log
- Log customer touchpoints across multiple channels: **Phone Calls**, **Meetings**, **Emails**, **Internal Memos / Notes**, and **Site Visits**.
- Priority markers (`low`, `normal`, `high`, `urgent`), duration tracking, and polymorphic entity association.

### 7. Action Tasks & Follow-up Scheduler
- Task delegation to sales representatives with due dates and priority tags.
- Visual overdue highlighting for missed follow-up deadlines.
- One-click task completion toggle.

### 8. Enterprise Bulk Data Ingestion Center (Admin Feature)
- **Supported Entity Types**: Bulk import **Leads**, **Contacts**, **Companies**, and **Deals**.
- **Resilient CSV Parser**:
  - Automatically strips UTF-8 BOM characters.
  - Auto-detects comma (`,`) and semicolon (`;`) delimiters.
  - Case-insensitive, snake_case header normalization (e.g. `First Name` -> `first_name`).
  - Row-level validation: processes all valid rows while isolating invalid rows.
  - Comprehensive diagnostic error logging with exact row numbers and cause of rejection.
- **Downloadable Sample CSV Templates**: Built-in template generator for all entity types.
- **Full CSV Data Export**: One-click data streaming export for Leads, Contacts, Companies, and Deals.

### 9. Role-Based Access Control (RBAC)
- **`admin`**: Full system administration, sales team user management, bulk data import & export, system configuration.
- **`sales_manager`**: Full sales pipeline visibility, team oversight, bulk data imports.
- **`sales_rep`**: Access to assigned opportunities, accounts, contacts, lead qualification, and personal activity logs.

---

## 🛠️ Technology Stack

| Layer | Technology | Version |
|---|---|---|
| **Backend Framework** | Laravel | 13.x |
| **Language Runtime** | PHP | 8.3 / 8.4 |
| **Production Database** | PostgreSQL | 18 (Enterprise Ready) |
| **Testing / Fallback DB** | SQLite / MySQL | Compatible |
| **UI Framework** | Bootstrap | 5.3+ (Bundled locally) |
| **Icon Libraries** | FontAwesome 6 Free & Bootstrap Icons | Bundled locally |
| **Data Visualizations** | Chart.js (UMD) | Bundled locally |
| **Environment Tool** | Laragon / Windows Server / Linux | Fully compatible |

---

## 🔒 100% Offline / Intranet Network Architecture

To guarantee operation inside air-gapped corporate intranets without public internet access:
- **No external CDNs**: All stylesheets, JavaScript files, and web fonts are hosted directly in `public/vendor/`:
  - `public/vendor/bootstrap/` — Bootstrap CSS & JavaScript bundles
  - `public/vendor/bootstrap-icons/` — Bootstrap Icons CSS and font glyphs
  - `public/vendor/fontawesome/` — FontAwesome CSS and webfonts (`.woff2`, `.ttf`)
  - `public/vendor/chartjs/` — Chart.js UMD bundle
  - `public/css/crm-custom.css` — Enterprise CRM styles
- All Blade views reference local assets using the `asset()` helper.

---

## 🚀 Installation & Setup Guide

### Prerequisites
- PHP 8.3 or PHP 8.4 installed (Laragon includes PHP 8.3/8.4 in `C:\laragon\bin\php\`).
- Composer 2.x installed.
- PostgreSQL 18 installed (or Laragon PostgreSQL / MySQL).

---

### Step 1: Database Setup (PostgreSQL 18)

1. Open PostgreSQL 18 command prompt (or pgAdmin / psql) and create the database:
   ```sql
   CREATE DATABASE crm_database;
   ```

2. Verify or create a dedicated PostgreSQL user:
   ```sql
   CREATE USER postgres WITH ENCRYPTED PASSWORD 'postgres';
   GRANT ALL PRIVILEGES ON DATABASE crm_database TO postgres;
   ```

---

### Step 2: Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

Open `.env` and verify the PostgreSQL 18 connection credentials:
```env
APP_NAME="Enterprise CRM"
APP_ENV=local
APP_KEY=base64:jgnP2uQO0FSGbNdxmvGWPdpoCADeKV0zZjEN8DhBtaY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# PostgreSQL 18 Configuration
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=crm_database
DB_USERNAME=postgres
DB_PASSWORD=postgres
DB_SCHEMA=public
DB_SSLMODE=prefer
```

*(Note: If testing temporarily without PostgreSQL 18 installed, you can set `DB_CONNECTION=sqlite` and create `database/database.sqlite`)*

---

### Step 3: Run Database Migrations and Seed Enterprise Data

Execute migrations and load the rich enterprise demonstration data:

```bash
php artisan migrate --seed
```

This populates the database with:
- 4 Role-based user accounts.
- Multi-industry enterprise companies.
- Key corporate contacts.
- Inbound qualified and unqualified sales leads.
- Active pipeline deals spanning all stages.
- Historical activity logs and scheduled follow-up tasks.

---

### Step 4: Launch Application Server

You can run the application with Laragon (virtual host) or using the built-in development server:

```bash
php artisan serve
```

Access the application in your browser at:
👉 **`http://localhost:8000`** (or your Laragon local host URL `http://customer-relationship-management.test`)

---

## 🔑 Default Demonstration Credentials

The application includes pre-configured accounts across all CRM roles. The login screen also features **One-Click Quick Login** buttons for instant testing.

| Role | Email Address | Password | Permissions |
|---|---|---|---|
| **System Administrator** | `admin@crm.test` | `password` | Full system access, Bulk Import Center, Team Management |
| **Sales Vice President** | `manager@crm.test` | `password` | Executive Pipeline, Bulk Import, Team Analytics |
| **Strategic Account Rep 1** | `rep1@crm.test` | `password` | Deals Pipeline, Leads Conversion, Contact Management |
| **Commercial Account Rep 2** | `rep2@crm.test` | `password` | Deals Pipeline, Leads Conversion, Contact Management |

---

## 📁 Bulk Data Import & Sample Files

The Admin Bulk Data Import Hub allows importing datasets in CSV format. Sample data files are included directly in `database/data/` for immediate testing:

1. **Leads Dataset**: `database/data/sample_leads.csv`
   - Fields: `first_name`, `last_name`, `company_name`, `email`, `phone`, `job_title`, `source`, `status`, `estimated_value`, `score`, `notes`
2. **Contacts Dataset**: `database/data/sample_contacts.csv`
   - Fields: `first_name`, `last_name`, `company_name`, `email`, `phone`, `mobile`, `job_title`, `department`, `status`, `notes`
3. **Companies Dataset**: `database/data/sample_companies.csv`
   - Fields: `name`, `industry`, `website`, `email`, `phone`, `address`, `city`, `state`, `country`, `postal_code`, `annual_revenue`, `employees_count`, `status`, `notes`
4. **Deals Dataset**: `database/data/sample_deals.csv`
   - Fields: `title`, `company_name`, `stage`, `amount`, `probability`, `expected_close_date`, `deal_type`, `notes`

### How to Import Data:
1. Log in as an Administrator (`admin@crm.test`) or Sales Manager (`manager@crm.test`).
2. Navigate to **Administration & Data** &rarr; **Bulk Data Import** in the sidebar.
3. Select your target entity type (e.g. `Sales Leads`).
4. Select one of the sample CSV files (e.g. `database/data/sample_leads.csv`).
5. Click **Start Bulk Ingestion**.
6. The system executes the import, presents a summary of imported records, and logs any row errors in the audit trail.

---

## 🧪 Automated Testing Suite

The application includes an automated PHPUnit Feature test suite covering authentication, dashboards, deals, leads, companies, contacts, activities, tasks, and bulk imports.

To run the full test suite:
```bash
vendor/bin/phpunit
```
Or with artisan:
```bash
php artisan test --compact
```

All 45 tests run in an isolated in-memory database and pass with 100% success rate:
```text
Pass: 45, Assertions: 142, Duration: 1.5s
```

---

## 📂 Project Directory Structure

```text
customer-relationship-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── BulkImportController.php    # Admin bulk data ingestion
│   │   │   │   └── UserController.php          # Team member administration
│   │   │   ├── ActivityController.php          # Activity logging
│   │   │   ├── AuthController.php              # Authentication & login
│   │   │   ├── CompanyController.php           # Account management & 360-view
│   │   │   ├── ContactController.php           # Contacts directory
│   │   │   ├── DashboardController.php         # Analytics & KPI dashboard
│   │   │   ├── DealController.php              # Pipeline & visual Kanban
│   │   │   ├── LeadController.php              # Leads & conversion workflow
│   │   │   ├── ProfileController.php           # User profile & security
│   │   │   └── TaskController.php              # Follow-up scheduler
│   │   ├── Middleware/
│   │   │   └── EnsureUserRole.php              # RBAC role protection
│   │   └── Requests/                           # Validated Form Requests
│   ├── Models/                                 # Eloquent Domain Models
│   │   ├── Activity.php
│   │   ├── BulkImport.php
│   │   ├── Company.php
│   │   ├── Contact.php
│   │   ├── Deal.php
│   │   ├── Lead.php
│   │   ├── Task.php
│   │   └── User.php
│   └── Services/                               # Business Logic Services
│       ├── BulkImportService.php               # CSV parsing, validation & templates
│       ├── CrmAnalyticsService.php             # KPIs & Chart.js aggregation
│       └── LeadConversionService.php           # Atomic lead conversion
├── database/
│   ├── data/                                   # Sample CSV datasets
│   ├── factories/                              # Model testing factories
│   ├── migrations/                             # PostgreSQL schema migrations
│   └── seeders/                                # Enterprise demo seeders
├── public/
│   ├── css/crm-custom.css                      # Enterprise CRM custom theme
│   └── vendor/                                 # 100% Offline Asset Bundles
│       ├── bootstrap/                          # Bootstrap 5 CSS & JS
│       ├── bootstrap-icons/                    # Bootstrap Icons & fonts
│       ├── fontawesome/                        # FontAwesome Free & webfonts
│       └── chartjs/                            # Chart.js UMD bundle
├── resources/views/                            # Blade templates & layouts
├── routes/
│   └── web.php                                 # 62 CRM web endpoints
└── tests/Feature/                              # PHPUnit Feature test suite
```
