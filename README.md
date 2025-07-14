# YogoFura - Smart Ordering and Delivery System

## Project Description
YogoFura is a web-based platform designed for local Yoghurt-Fura vendors and customers in Nigeria. It enables customers to browse menus, place orders, and track deliveries, vendors to manage products and orders, and admins to oversee platform activities.

## Setup Instructions
1. **Prerequisites**:
   - XAMPP/WAMP or any PHP server with MySQL
   - PHP 7.4 or higher
   - MySQL with phpMyAdmin
2. **Installation**:
   - Clone or download the project to your server's root directory (e.g., `htdocs/yogofura`).
   - Create a MySQL database named `yogofura`.
   - Import `database.sql` into phpMyAdmin to set up tables and seed data.
   - Ensure the `uploads/` folder is writable for image uploads.
   - Update `includes/config.php` with your base URL if not using `http://localhost/yogofura/`.
3. **Run the Application**:
   - Start your server (Apache/MySQL).
   - Access the platform at `http://localhost/yogofura/`.
4. **Sample Credentials**:
   - Admin: `admin@yogofura.com` / `password`
   - Vendor: `vendor1@yogofura.com` / `password`
   - Customer: `customer1@yogofura.com` / `password`

## Features
- **Customer**: Browse vendors, order products, simulate payments, view order history.
- **Vendor**: Manage products, view and update orders, track earnings.
- **Admin**: Approve/reject vendors, monitor orders, view platform stats.
- Simulated Paystack payment and static Google Maps for delivery visualization.

## Notes
- Ensure the `uploads/` folder exists and is writable.
- Passwords in `database.sql` are hashed; use `password` for all seed accounts.
- Static Google Maps API is used for simplicity; replace with a valid API key for production.