**Inventory System Documentation**

**Introduction**
This document provides functional, module‑level documentation for the Inventory System. It describes each subsystem’s purpose, key workflows, UI components, and API endpoints—without low‑level database schemas—to give a clear operational overview.

---

## Table of Contents
1. [Authentication System](#authentication-system)
2. [Product Management](#product-management)
3. [Sales & Order Management](#sales--order-management)
4. [Batch & Stock Management](#batch--stock-management)
5. [User Management](#user-management)
6. [Dashboard & Reports](#dashboard--reports)
7. [Notifications](#notifications)
8. [Catalog](#catalog)
9. [Role Management](#role-management)
10. [Settings](#settings)
11. [Navigation](#navigation)
12. [Miscellaneous Features](#miscellaneous-features)

---

## Authentication System

**Purpose:** Secure access to the application via login, session handling, and role‑based access control.

**Key Workflows:**
- **Login**: Validate credentials, establish session, redirect based on role.
- **Logout**: Destroy session, clear cookies.
- **Password Recovery**: Request reset, email token, set new password.
- **Access Enforcement**: Middleware functions to guard pages by login status and role.

**UI Components:**
- Responsive login form with client‑side validation.
- “Forgot Password” modal.
- Role‑aware navigation menu.

**API Endpoints (`api/auth.php`):**
| Action    | Method | Parameters            | Response                                   |
|-----------|--------|-----------------------|--------------------------------------------|
| login     | POST   | email, password       | { success: bool, user: { id, name, role } } |
| forgot    | POST   | email                 | { success: bool, message }                 |
| reset     | POST   | token, new_password   | { success: bool, message }                 |

---

## Product Management

**Purpose:** Full CRUD for products and categories, image handling, barcode generation, and stock overview.

**Key Workflows:**
- **Add/Edit Product:** Enter details (name, category, price, images), assign sizes, set initial stock.
- **Delete Product:** Soft‑delete to preserve history.
- **Category Maintenance:** Create, edit, or remove product categories.
- **Barcode/Label Printing:** Generate and batch‑print barcodes for inventory labeling.

**UI Components (`products.php`):**
- **Header & Controls:** Add Product, Manage Categories.
- **Filters & Search:** Live filtering by name, category, stock status.
- **Product Grid/Table:** Image thumbnail, pricing, stock badge, action buttons.
- **Modals**: Add/Edit Product, Category Editor.

**API Endpoints (`api/products.php`):**
| Endpoint         | Method | Parameters                               | Response                          |
|------------------|--------|------------------------------------------|-----------------------------------|
| GET /products    | GET    | search, category_id, stock_filter        | List of products                  |
| POST /products   | POST   | name, category, price, images, sizes     | Created product object            |
| PUT /products    | PUT    | id, updated fields                       | Updated product object            |
| DELETE /products | DELETE | id                                       | { success: bool }                 |

---

## Sales & Order Management

**Purpose:** Manage order placement, status tracking, invoicing, and customer history.

**Key Workflows:**
- **Create Order:** Select customer, add items, apply discounts.
- **Update Status:** Pending → Confirmed → Delivered → Canceled.
- **Invoice Generation:** Auto‑generate PDF invoice and email to customer.
- **Refund/Cancellation:** Restock items, record transaction.

**UI Components (`sales.php`):**
- **Stats Cards:** Total sales, pending orders, etc.
- **Order Table:** Bulk actions, status dropdowns, date filters.
- **Order Detail Modal:** Line items, pricing breakdown, notes.

**API Endpoints (`api/sales.php`):**
| Endpoint                   | Method | Parameters                                     | Response                      |
|----------------------------|--------|------------------------------------------------|-------------------------------|
| GET /sales                 | GET    | recent, limit, status                          | List of sales                 |
| POST /sales                | POST   | customer_id, items[{ product_id, qty }], note  | Created order object          |
| PUT /sales                 | PUT    | id, status                                     | Updated order status          |
| GET /sales/analytics       | GET    | timeRange, period, product, category           | Aggregated analytics data     |

---

## Batch & Stock Management

**Purpose:** Track manufacturing batches, adjust stock levels, and maintain audit logs.

**Key Workflows:**
- **Add Batch:** Link batch to product, record manufacturing date, initial quantity.
- **Adjust Stock:** Increase or decrease stock with reason (in, out, transfer).
- **Low‑Stock Alerts:** Trigger notifications when levels fall below threshold.
- **Audit Trail:** View history of all adjustments with user and timestamp.

**UI Components (`batches.php` & `stock.php`):**
- **Batch Table:** Batch number, product, date, current stock.
- **Stock Adjustment Modal:** Fields: product, size, quantity change, reason, location.
- **Stock Logs View:** Filter by type, date range, product.

**API Endpoints (`api/stock.php`):**
| Endpoint                   | Method | Parameters                                           | Response             |
|----------------------------|--------|------------------------------------------------------|----------------------|
| POST /stock                | POST   | product_id, size_id, quantity, type (in/out), reason | { success: bool }    |
| GET /stock/logs            | GET    | search, type, date_from, date_to, page, per_page     | Paginated logs       |

---

## User Management

**Purpose:** Administer system users, roles, and profiles with secure onboarding and offboarding.

**Key Workflows:**
- **Invite User:** Collect email, assign role, send welcome link.
- **Edit Profile:** Update name, email, avatar.
- **Change Status:** Activate, suspend, or deactivate accounts.
- **Password Reset:** Admin‑triggered or self‑service.

**UI Components (`users.php`):**
- **User List:** Search, filter by role/status, bulk actions.
- **Add/Edit Modal:** Fields: name, email, role, status.
- **Profile View:** Personal details, last login, activity log.

**API Endpoints (`api/users.php`):**
| Endpoint           | Method | Parameters                        | Response              |
|--------------------|--------|-----------------------------------|-----------------------|
| GET /users         | GET    | search, role, status              | List of users         |
| POST /users        | POST   | name, email, role                 | Created user object   |
| PUT /users         | PUT    | id, updated fields                | Updated user object   |
| DELETE /users      | DELETE | id                                | { success: bool }      |

---

## Dashboard & Reports

**Purpose:** Real‑time monitoring and custom report generation for actionable insights.

**Key Features:**
- **Dashboard:** Live KPIs (sales, inventory levels, low‑stock items).
- **Charts:** Time series, bar, and pie charts for trends and comparisons.
- **Reports:** Ad‑hoc exports (PDF, Excel, CSV) on any data dimension.
- **Scheduled Reports:** Email reports automatically at set intervals.

**UI Components:**
- **dashboard.php:** Stats cards, chart panels, recent activity feed.
- **reports.php:** Filterable report builder with export options.

**API Endpoints:**
| Endpoint                | Method | Parameters                          | Response                  |
|-------------------------|--------|-------------------------------------|---------------------------|
| GET /dashboard/metrics  | GET    | metrics[], date_range               | KPI values                |
| POST /reports/generate  | POST   | type, filters[], format             | { fileUrl }               |

---

## Notifications

**Purpose:** Deliver real‑time alerts and summaries via in‑app banners, emails, or push.

**Types & Triggers:**
- **System Alerts:** Errors, maintenance notices.
- **Stock Alerts:** Low‑stock, batch expirations.
- **Sales Alerts:** Large orders, payment failures.
- **User Alerts:** Password changes, role updates.

**API Endpoints:**
| Endpoint               | Method | Parameters                     | Response             |
|------------------------|--------|--------------------------------|----------------------|
| POST /notifications    | POST   | type, message, user_id         | { success: bool }    |
| GET /notifications     | GET    | user_id, status (read/unread)  | List of notifications|

---

## Catalog

**Purpose:** Present product offerings with search, filter, and detail views.

**Key Workflows:**
- **Product Listing:** Paginated grid with filters by category, price range, availability.
- **Detail View:** Full product information, images, stock status.
- **Search:** Keyword search with relevance ranking.

**UI Components:** Front‑end listing pages and product detail pages.

**API Endpoints:**
| Endpoint             | Method | Parameters                     | Response             |
|----------------------|--------|--------------------------------|----------------------|
| GET /catalog         | GET    | page, per_page, filters[]      | List of products     |
| GET /catalog/:id     | GET    | id                             | Product details      |

---

## Role Management

**Purpose:** Define and enforce permissions tied to `admin` and `staff` roles.

**Key Concepts:**
- **Role Definitions:** Which modules and actions each role can access.
- **Permission Checks:** Middleware functions to verify rights before executing operations.

**Implementation Snippet:**
```php
function requireRole(array $allowedRoles) {
  if (!in_array(getUserRole(), $allowedRoles)) {
    redirect('access_denied.php');
  }
}
```

---

## Settings

**Purpose:** Centralize application configuration and user preferences.

**Key Workflows:**
- **General Settings:** Email server, notification preferences, UI themes.
- **User Preferences:** Language, time zone, dashboard layout.
- **Audit Settings:** Log verbosity, retention policies.

**UI Components:** Settings panels under Admin and in user profile.

**API Endpoints:**
| Endpoint               | Method | Parameters               | Response           |
|------------------------|--------|--------------------------|--------------------|
| GET /settings          | GET    | context (system/user)    | Key/value pairs    |
| PUT /settings          | PUT    | key, value               | { success: bool }  |

---

## Navigation

**Purpose:** Dynamically build menu structure based on user’s role and permissions.

**Key Workflow:**
- **Menu Generation:** On login, retrieve role, assemble menu items from configuration.

**Implementation Snippet:**
```php
$menu = buildNavigation(getUserRole());
foreach ($menu as $item) {
  echo "<li><a href='{$item['url']}'>$item[name]</a></li>";
}
```

---

## Miscellaneous Features

- **Search System:** Global search widget covering all entities.
- **Help & Documentation:** Embedded help panels, FAQ, video tutorials.
- **Backup & Restore:** Manual and scheduled backups with restore interface.
- **Logging & Audit:** Central log viewer for errors, user actions, performance metrics.

---

**End of Documentation**

