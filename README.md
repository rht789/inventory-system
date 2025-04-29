Inventory System Documentation
Introduction
The Inventory System is a comprehensive web-based application designed to streamline inventory management, sales, and user operations for businesses. Built using PHP and MySQL, it features secure authentication, role-based access control (RBAC), a responsive user interface, and robust APIs. This document provides a functional overview of the system’s modules, workflows, UI components, and API endpoints, serving as a guide for developers, administrators, and stakeholders.
System Overview
The Inventory System offers a modular platform to manage inventory operations efficiently. Key features include:

Secure Authentication: Session-based login with RBAC.
Product Management: CRUD operations for products, categories, and stock tracking.
Sales and Order Management: Order processing, customer management, and invoicing.
Batch and Stock Management: Batch tracking, stock adjustments, and logging.
User Management: User creation, role assignment, and profile management.
Dashboard and Reporting: Real-time analytics and exportable reports.
Notifications and Catalog: Alerts and product listings for enhanced usability.

The system follows a modular architecture with a responsive HTML/CSS/JavaScript frontend, PHP-based backend, and RESTful APIs interacting with a MySQL database.
Table of Contents

Authentication System
Product Management
Sales & Order Management
Batch & Stock Management
User Management
Dashboard & Reports
Notifications
Catalog
Role Management
Settings
Navigation
Miscellaneous Features
Best Practices
Error Handling
Future Improvements


Authentication System
Purpose
Ensures secure access through session-based authentication and RBAC, with password recovery and role-aware navigation.
Key Workflows

Login: Validates credentials, creates a session, and redirects based on role.
Logout: Destroys session and clears cookies.
Password Recovery: Sends a reset token via email for password changes.
Access Enforcement: Middleware checks login status and role permissions.

UI Components

Responsive login form with client-side validation (login.php).
“Forgot Password” modal for recovery requests.
Role-aware navigation menu reflecting user permissions.

API Endpoints (api/auth.php)



Action
Method
Parameters
Response



login
POST
email, password
{ success: bool, user: { id, name, role } }


forgot
POST
email
{ success: bool, message }


reset
POST
token, new_password
{ success: bool, message }


Security Features

Password hashing with bcrypt (password_hash()).
Secure session management with timeouts and regeneration.
Protection against SQL injection, XSS, and CSRF.
Rate limiting on login attempts.


Product Management
Purpose
Manages products, categories, and inventory with CRUD operations, barcode generation, and size-specific stock tracking.
Key Workflows

Add/Edit Product: Enter details (name, category, prices, images), assign sizes, and set stock.
Delete Product: Soft-delete to preserve historical data.
Category Management: Create, edit, or remove categories.
Barcode Generation: Generate and print barcodes for inventory labeling.

UI Components (products.php)

Header & Controls: Buttons for adding products or managing categories.
Filters & Search: Live filtering by name, category, or stock status.
Product Grid/Table: Displays thumbnails, pricing, stock badges, and action buttons.
Modals: Forms for adding/editing products and categories.

API Endpoints (api/products.php)



Endpoint
Method
Parameters
Response



GET /products
GET
search, category_id, stock_filter
List of products


POST /products
POST
name, category, price, images, sizes
Created product object


PUT /products
PUT
id, updated fields
Updated product object


DELETE /products
DELETE
id
{ success: bool }


Security

Role-based access: Admins have full control; Staff have limited permissions.
Secure file uploads with validation for image formats and sizes.
Input sanitization to prevent injection attacks.


Sales & Order Management
Purpose
Handles order creation, status tracking, customer management, and invoicing with real-time stock updates.
Key Workflows

Create Order: Select customer, add items, apply discounts, and save.
Update Status: Transition orders through Pending → Confirmed → Delivered → Canceled.
Invoice Generation: Auto-generate and email PDF invoices.
Refund/Cancellation: Restock items and log transactions.

UI Components (sales.php)

Stats Cards: Display total sales, pending orders, and key metrics.
Order Table: Supports bulk actions, status updates, and date filters.
Order Detail Modal: Shows line items, pricing, and notes.

API Endpoints (api/sales.php)



Endpoint
Method
Parameters
Response



GET /sales
GET
recent, limit, status
List of sales


POST /sales
POST
customer_id, items[{ product_id, qty }], note
Created order object


PUT /sales
PUT
id, status
Updated order status


GET /sales/analytics
GET
timeRange, period, product, category
Aggregated analytics data


Security

Role-based access to ensure transaction integrity.
Audit logging for all sales activities.
Transaction validation to prevent stock inconsistencies.


Batch & Stock Management
Purpose
Tracks product batches and stock levels with adjustment logging and low-stock alerts.
Key Workflows

Add Batch: Link to product/size, record manufacturing date and quantity.
Adjust Stock: Increase/decrease stock with reasons (e.g., in, out, transfer).
Low-Stock Alerts: Notify when stock falls below threshold.
Audit Trail: Log all stock changes with user and timestamp.

UI Components (batches.php, stock.php)

Batch Table: Displays batch number, product, date, and stock.
Stock Adjustment Modal: Fields for product, size, quantity, reason, and location.
Stock Logs View: Filterable log of adjustments by type, date, or product.

API Endpoints (api/stock.php)



Endpoint
Method
Parameters
Response



POST /stock
POST
product_id, size_id, quantity, type (in/out), reason
{ success: bool }


GET /stock/logs
GET
search, type, date_from, date_to, page, per_page
Paginated logs


Security

Role-based access to prevent unauthorized changes.
Transaction validation for data integrity.
Detailed audit trails for stock adjustments.


User Management
Purpose
Manages system users, roles, and profiles with secure onboarding and notifications.
Key Workflows

Invite User: Send welcome email with temporary password.
Edit Profile: Update name, email, avatar, or role.
Change Status: Activate, suspend, or deactivate accounts.
Password Reset: Admin-triggered or self-service via email token.

UI Components (users.php)

User List: Searchable, filterable by role/status, with bulk actions.
Add/Edit Modal: Fields for name, email, role, and status.
Profile View: Shows details, last login, and activity log.

API Endpoints (api/users.php)



Endpoint
Method
Parameters
Response



GET /users
GET
search, role, status
List of users


POST /users
POST
name, email, role
Created user object


PUT /users
PUT
id, updated fields
Updated user object


DELETE /users
DELETE
id
{ success: bool }


Security

Password hashing with bcrypt and strength requirements.
Token-based password resets with time-limited tokens.
Email notifications for account changes.


Dashboard & Reports
Purpose
Provides real-time monitoring and customizable reporting for business insights.
Key Features

Dashboard: Live KPIs (sales, inventory, low-stock items) and charts (trends, comparisons).
Reports: Ad-hoc exports in PDF, Excel, or CSV for sales, inventory, or customers.
Scheduled Reports: Automated email delivery of reports.

UI Components

dashboard.php: Stats cards, chart panels, and recent activity feed.
reports.php: Filterable report builder with export options.

API Endpoints



Endpoint
Method
Parameters
Response



GET /dashboard/metrics
GET
metrics[], date_range
KPI values


POST /reports/generate
POST
type, filters[], format
{ fileUrl }


PDF Generation
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

function generatePDF($data, $template) {
    $dompdf = new Dompdf();
    $html = renderTemplate($template, $data);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    return $dompdf->output();
}

Security

Admin-only access to dashboard and reports.
Password-protected PDF exports.


Notifications
Purpose
Delivers real-time alerts via in-app banners, email, or push notifications.
Types & Triggers

System Alerts: Errors or maintenance notices.
Stock Alerts: Low stock or batch expirations.
Sales Alerts: Large orders or payment issues.
User Alerts: Password or role changes.

API Endpoints



Endpoint
Method
Parameters
Response



POST /notifications
POST
type, message, user_id
{ success: bool }


GET /notifications
GET
user_id, status (read/unread)
List of notifications



Catalog
Purpose
Presents product offerings with search, filtering, and detailed views.
Key Workflows

Product Listing: Paginated grid with filters by category, price, or availability.
Detail View: Full product details, images, and stock status.
Search: Keyword-based with relevance ranking.

UI Components

Front-end listing pages and product detail views.

API Endpoints



Endpoint
Method
Parameters
Response



GET /catalog
GET
page, per_page, filters[]
List of products


GET /catalog/:id
GET
id
Product details



Role Management
Purpose
Defines and enforces permissions for Admin and Staff roles.
Key Concepts

Role Definitions: Specify accessible modules and actions per role.
Permission Checks: Middleware ensures role-based access.

Implementation Snippet
function requireRole(array $allowedRoles) {
    if (!in_array(getUserRole(), $allowedRoles)) {
        redirect('access_denied.php');
    }
}


Settings
Purpose
Centralizes application and user-specific configurations.
Key Workflows

General Settings: Configure email, notifications, or UI themes.
User Preferences: Set language, time zone, or dashboard layout.
Audit Settings: Define log verbosity and retention.

UI Components

Settings panels in Admin and user profile sections.

API Endpoints



Endpoint
Method
Parameters
Response



GET /settings
GET
context (system/user)
Key/value pairs


PUT /settings
PUT
key, value
{ success: bool }



Navigation
Purpose
Dynamically generates a role-based menu for quick access to modules.
Key Workflow

Menu Generation: Builds menu items based on user role upon login.

Implementation Snippet
$menu = buildNavigation(getUserRole());
foreach ($menu as $item) {
    echo "<li><a href='{$item['url']}'>$item[name]</a></li>";
}


Miscellaneous Features

Search System: Global search across products, users, and orders.
Help & Documentation: Embedded help panels, FAQs, and tutorials.
Backup & Restore: Manual and scheduled backups with restore interface.
Logging & Audit: Central log viewer for errors, actions, and metrics.


Best Practices
Security

Use HTTPS and secure cookies.
Implement rate limiting and monitor failed logins.
Regular security audits and dependency updates.
Sanitize inputs to prevent SQL injection, XSS, and CSRF.

Performance

Optimize database queries with indexing.
Cache frequently accessed data (e.g., product listings).
Minimize session data and API requests.

User Experience

Ensure responsive design for mobile and desktop.
Provide clear feedback and error messages.
Use AJAX for real-time updates.


Error Handling
Common Errors

Authentication: Invalid credentials, session timeouts, permission issues.
Product/Sales: Duplicate entries, insufficient stock, invalid data.
Stock/Batch: Invalid quantities, duplicate batch numbers.
Reports: Data collection or formatting issues.

Handling Strategy

Display user-friendly error messages.
Log detailed errors for administrators.
Use transactions to maintain data integrity.


Future Improvements
Security Enhancements

Implement two-factor authentication (2FA) and OAuth.
Adopt JSON Web Tokens (JWT) for APIs.
Enhance encryption for sensitive data.

Feature Additions

Support bulk operations for products and users.
Introduce predictive stock forecasting.
Develop a mobile app with online payment integration.

Performance Optimization

Use caching (e.g., Redis) for API responses.
Implement load balancing for high traffic.
Optimize database queries and API latency.

User Experience

Add interactive tutorials and tooltips.
Introduce customizable dashboards with widgets.
Enhance filtering and search capabilities.


Conclusion
The Inventory System is a robust, secure, and scalable solution for managing inventory, sales, and user operations. Its modular design and adherence to best practices make it suitable for businesses of varying sizes. Ongoing maintenance and planned improvements will ensure it meets evolving needs.