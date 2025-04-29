Inventory System
The Inventory System is a web-based application designed to streamline inventory management, sales, and user operations for businesses. Built with PHP and MySQL, it offers secure authentication, role-based access control (RBAC), a responsive UI, and robust APIs. This README provides an overview, setup instructions, and key features.
Table of Contents

Features
Technologies
Installation
Usage
Modules
Security
Contributing
License

Features

Secure Authentication: Session-based login with RBAC (Admin/Staff roles).
Product Management: CRUD operations for products, categories, and stock with barcode generation.
Sales & Order Management: Order processing, customer management, and PDF invoicing.
Batch & Stock Management: Batch tracking, stock adjustments, and low-stock alerts.
User Management: User creation, role assignment, and email notifications.
Dashboard & Reports: Real-time analytics and exports (PDF/Excel/CSV).
Notifications: In-app and email alerts for stock, sales, and system events.
Catalog: Searchable product listings with filters and detailed views.

Technologies

Frontend: HTML, CSS, JavaScript (AJAX for real-time updates)
Backend: PHP 7.4+
Database: MySQL 5.7+
Libraries: Dompdf for PDF generation
APIs: RESTful endpoints for all modules
Environment: Apache/Nginx, PHP-enabled server

Installation
Prerequisites

PHP 7.4 or higher
MySQL 5.7 or higher
Apache/Nginx web server
Composer for dependency management

Steps

Clone the Repository:
git clone https://github.com/your-repo/inventory-system.git
cd inventory-system


Install Dependencies:
composer install


Configure Environment:

Copy .env.example to .env:cp .env.example .env


Update .env with database credentials and email settings:DB_HOST=localhost
DB_DATABASE=inventory
DB_USERNAME=your_username
DB_PASSWORD=your_password
MAIL_HOST=smtp.example.com
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password




Set Up Database:

Create a MySQL database (e.g., inventory).
Import the schema from database/schema.sql:mysql -u your_username -p inventory < database/schema.sql




Configure Web Server:

Point the web server to the public/ directory.
Ensure .htaccess is enabled for Apache or configure Nginx accordingly.


Start the Application:

Access the app via your browser (e.g., http://localhost/inventory-system).
Default admin credentials: admin@example.com / password (change immediately).



Usage

Login: Access the system at /login.php with your credentials.
Dashboard: View real-time metrics and navigate to modules.
Manage Products: Add/edit products, categories, and stock at /products.php.
Process Sales: Create orders and generate invoices at /sales.php.
Admin Tasks: Manage users and settings at /users.php and /settings.php.
Reports: Generate and export reports at /reports.php.

Modules
Authentication

Session-based login with password recovery and RBAC.
API: /api/auth.php (login, forgot, reset).

Product Management

CRUD for products, categories, and sizes with barcode support.
API: /api/products.php (list, create, update, delete).

Sales & Order Management

Order creation, status tracking, and PDF invoicing.
API: /api/sales.php (list, create, update, analytics).

Batch & Stock Management

Batch tracking, stock adjustments, and low-stock alerts.
API: /api/stock.php(adjust, logs).

User Management

User creation, role assignment, and email notifications.
API: /api/users.php (list, create, update, delete).

Dashboard & Reports

Real-time KPIs, charts, and exportable reports.
API: /api/dashboard.php (metrics), /api/reports.php (generate).

Notifications

In-app and email alerts for stock, sales, and system events.
API: /api/notifications.php (send, list).

Catalog

Searchable product listings with filters.
API: /api/catalog.php (list, details).

Security

Password hashing with bcrypt.
Protection against SQL injection, XSS, and CSRF.
Secure session management with timeouts.
Role-based access (Admin: full control; Staff: limited).
HTTPS and secure cookies recommended.

Contributing

Fork the repository.
Create a feature branch (git checkout -b feature/your-feature).
Commit changes (git commit -m "Add your feature").
Push to the branch (git push origin feature/your-feature).
Open a pull request.

Please follow the code of conduct and ensure tests pass.
License
This project is licensed under the MIT License. See LICENSE for details.