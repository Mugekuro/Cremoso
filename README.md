# 🍦 Cremoso - Sales & Transaction Management System

A **PHP-based web application** for managing soft-serve ice cream sales and transactions across multiple branch locations. Built for Cremoso, featuring role-based access for admins and staff with real-time analytics, order management, and comprehensive reporting.

## ✨ Features

- **🔐 Role-Based Authentication** - Separate dashboards for Admin and Staff roles
- **🏪 Multi-Branch Support** - Manage multiple store locations from one system
- **📦 Order Management** - Create orders with flavors, sizes, and quantities
- **🌐 Multi-Channel Ordering** - Walk-in, Facebook Messenger, Foodpanda
- **💳 Multiple Payment Methods** - Cash, GCash, Credit/Debit Card
- **📊 Analytics Dashboard** - Interactive sales charts powered by Chart.js
- **📈 Reporting** - Generate sales reports and transaction history
- **📝 Staff Daily Log** - Track daily activities and operations
- **📱 Responsive Design** - Mobile-friendly with collapsible sidebar navigation

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 7.4+ (PDO) |
| **Database** | MySQL 5.7+ / MariaDB |
| **Frontend** | HTML5, CSS3, Bootstrap 5, Font Awesome 6 |
| **Charts** | Chart.js |
| **Server** | Apache/Nginx or PHP built-in server |

## 📁 Project Structure

```
Cremoso/
├── index.php                 # Entry point - role-based redirect
├── login.php                 # Login page with demo credentials
├── logout.php                # Session destruction & logout
├── schema.sql                # Database schema & seed data
├── README.md                 # This file
│
├── config/
│   └── database.php          # Database connection configuration
│
├── includes/
│   ├── auth.php              # Authentication helper functions
│   ├── header.php            # Shared HTML head section
│   ├── footer.php            # Shared footer & sidebar JavaScript
│   ├── sidebar_admin.php     # Admin navigation sidebar
│   └── sidebar_staff.php     # Staff navigation sidebar
│
├── admin/
│   ├── dashboard.php         # Admin overview with stats & charts
│   ├── analytics.php         # Detailed sales analytics
│   ├── items.php             # Item management (CRUD operations)
│   ├── transactions.php      # Transaction history viewer
│   ├── reports.php           # Report generation
│   └── profile.php           # Admin profile management
│
├── staff/
│   ├── dashboard.php         # Staff branch overview
│   ├── new_order.php         # Order creation interface
│   ├── save_order.php        # Order processing handler
│   ├── daily_log.php         # Daily activity log
│   └── profile.php           # Staff profile management
│
└── assets/
    ├── css/
    │   └── style.css         # Custom stylesheet (Cremoso Teal theme)
    └── images/
        └── logo.jpg          # Brand logo
```

## 🗄️ Database Schema

### Core Tables

- **`branches`** - Store locations (HQ Main, Downtown, etc.)
- **`users`** - Admin & staff accounts with role assignment
- **`customers`** - Customer records
- **`order_channels`** - Sales channels (Walk-in, Facebook, Foodpanda)
- **`payment_methods`** - Payment types (Cash, GCash, Card)
- **`flavors`** - Soft-serve flavors (Vanilla, Chocolate, Strawberry, Ube, Cheese)
- **`item_sizes`** - Size options with price multipliers (Small, Medium, Large)
- **`items`** - Menu items (flavor + size + price combinations)
- **`transactions`** - Order headers with customer, user, branch, channel, payment
- **`transaction_items`** - Line items per transaction

### Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin` |
| Staff 1 (HQ) | `staff1` | `staff1` |
| Staff 2 (Downtown) | `staff2` | `staff2` |

> ⚠️ **Security Note**: Passwords are stored in plain text for demo purposes. Use `password_hash()` for production.

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher (with PDO MySQL extension enabled)
- MySQL 5.7+ or MariaDB
- Web server (Apache, Nginx, or PHP built-in server)

### Installation

1. **Clone or download the repository**
   ```bash
   cd Cremoso
   ```

2. **Create the database and import schema**
   ```bash
   mysql -u root -p < schema.sql
   ```

3. **Configure database connection**

   Edit `config/database.php` with your MySQL credentials:
   ```php
   $host = 'localhost';
   $dbname = 'cremoso_db';
   $username = 'root';
   $password = '';
   ```

4. **Start the web server**

   Using PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```

   Or configure with Apache/Nginx to serve the project directory.

5. **Access the application**

   Open your browser and navigate to:
   ```
   http://localhost:8000/login.php
   ```

   Use the demo credentials above to log in.

## 🎨 UI/UX Design

- **Color Theme**: Cremoso Teal palette (`--primary: #2DA89B`)
- **Typography**: Inter (Google Fonts)
- **Layout**: Responsive design with mobile-first breakpoints
- **Navigation**: Collapsible sidebar with hamburger menu on mobile

## 💰 Currency

All prices are displayed in **Philippine Peso (₱)**.

## 📝 Development Conventions

### Authentication
- Session-based authentication using `$_SESSION`
- Helper functions in `includes/auth.php`: `isLoggedIn()`, `isAdmin()`, `isStaff()`, etc.
- All protected pages verify authentication before rendering

### Database Access
- PDO with prepared statements for all queries (SQL injection prevention)
- Positional placeholders (`?`) with parameter binding
- Global `$pdo` variable via `global $pdo`

### Template Pattern
Pages follow this structure:
1. Authentication check & redirect
2. Database queries
3. Include header + appropriate sidebar
4. Render main content
5. Include footer

## ⚠️ Known Limitations

- [ ] **Plain-text passwords** - Not hashed (demo only)
- [ ] **No CSRF protection** on forms
- [ ] **No input sanitization** beyond prepared statements (potential XSS risk)
- [ ] **No pagination** on transaction/item lists
- [ ] **Chart.js via CDN** - Requires internet connection
- [ ] **No REST API** - All logic is server-rendered PHP

## 🔒 Security Recommendations for Production

1. Implement `password_hash()` and `password_verify()` for user passwords
2. Add CSRF tokens to all forms
3. Implement input sanitization and output escaping
4. Add session fixation prevention
5. Implement rate limiting on login attempts
6. Use HTTPS in production
7. Add pagination to large data lists

## 📄 License

This project is provided as-is for educational and demonstration purposes.

## 🤝 Support

For questions or issues, please review the project structure and database schema. Most functionality is self-contained within the PHP files.

---

**Built with ❤️ for Cremoso Soft-Serve Ice Cream**
