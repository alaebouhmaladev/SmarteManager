# 📘 SmarteManager — HR, Attendance, Inventory & Expense Management System

SmarteManager is a modern, modular management system built for restaurants, cafés, retail stores, and small businesses.
It centralizes:

- Employee management
- Attendance & time tracking
- Inventory & stock control
- Supplier management
- Expense tracking
- Dashboard analytics
- Role-based access (Admin / Manager / Staff)
- Secure REST API using Laravel Sanctum

## 🚀 Key Features

### 🧑‍💼 HR & Time Tracking Module
* **Employee CRUD:** Full management of employee records (Create, Read, Update, Delete).
* **Check-In / Check-Out:** Integrated time clock functionality for attendance tracking.
* **Automated Calculation:** Automatic computation of total hours worked.
* **Attendance History:** Detailed daily and monthly attendance logs.
* **Payroll Management:** Automated monthly salary calculation based on hours and rates.
* **Data Export:** Export functionality for reports (Coming Soon).

### 📦 Inventory Module
* **Product Management:** easy management of products and raw ingredients.
* **Real-Time Stock:** Live view of current inventory levels.
* **Stock Movement:** Track all stock entries (purchases) and exits (sales/usage).
* **Cost Analysis:** Automatic calculation of the average cost of goods.
* **Smart Alerts:** Notifications for minimum stock levels to prevent outages.
* **Valuation:** Instant view of the total monetary value of the stock.

### 💰 Expense Module
* **Categorized Expenses:** Organize all business expenses by custom categories.
* **Detailed Tracking:** Record amount, date, and supplier for every transaction.
* **Monthly Reports:** Generate summaries of expenses per month.
* **Category Management:** Create and modify expense categories (Optional).

### 🏪 Supplier Module
* **Vendor Management:** Centralized database for all supplier profiles.
* **Transaction History:** Complete history of purchases and expenses linked to specific suppliers.
* **Contact Details:** Store full information including name, contact person, phone numbers, etc.

---

## 🚀 Tech Stack

### Backend
- Laravel 11
- PHP 8.2+
- Laravel Sanctum
- MySQL
- Eloquent ORM
- REST API

### Frontend (optional)
- Vue.js 3
- Vite
- Axios
- Pinia

## 📂 Backend Project Structure

```
app/
 ├── Http/
 │    ├── Controllers/
 │    ├── Middleware/
 ├── Models/
 └── Providers/

routes/
 ├── api.php
 └── web.php

database/
 ├── migrations/
 ├── seeders/
 └── factories/
```

## 🔐 Authentication (Laravel Sanctum)

### Login
```
POST /api/auth/login
```

Body:
```json
{
  "email": "admin@example.com",
  "password": "password"
}
```

### Logout
```
POST /api/auth/logout
```

### Get Current User
```
GET /api/auth/me
```

Add token:
```
Authorization: Bearer YOUR_TOKEN
```

## 👥 User Roles

| Role    | Permissions                 |
|---------|------------------------------|
| Admin   | Full control                |
| Manager | Manage major modules        |
| Staff   | Attendance + dashboard only |

## 📦 Modules Overview

### Employees
CRUD operations.

### Attendance
Check-in/check-out.

### Products
Inventory products.

### Suppliers
Manage suppliers.

### Stock Movements
Track stock in/out.

### Expenses
Track business expenses.

### Dashboard
Global analytics.

## 🛠 Installation Guide

```
git clone https://github.com/yourusername/smarte-manager-api.git
cd smarte-manager-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### Create Admin User
```
php artisan tinker
User::create([...])
```

### Start Server
```
php artisan serve
```

## 📡 API Usage Example

Use Bearer Token in Postman for all protected routes.

## 🏁 Conclusion

A complete backend for HR, attendance, inventory, expenses, suppliers, dashboard, and admin roles.


## 📌 Future Improvements
- PDF export  
- CRM module  
- Customer order management system  
- Multi-store support  
- Mobile application  

---

## 👤 Created By
**ALAE BOUHMALA**  
Project – SmarteManager   
---

