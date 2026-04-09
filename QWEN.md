# Cremoso - Sales & Transaction Management System

## Project Overview

Cremoso is a **PHP-based web application** for managing soft-serve ice cream sales and transactions across multiple branch locations. It provides role-based access for **admins** and **staff**, with features including order management, transaction tracking, analytics, and reporting.

### Key Features
- **Role-based authentication** (Admin / Staff)
- **Multi-branch support** (HQ Main, Downtown, etc.)
- **Order creation** with item selection (flavors, sizes, quantities)
- **Multi-channel ordering** (Walk-in, Facebook Messenger, Foodpanda)
- **Multiple payment methods** (Cash, GCash, Credit/Debit Card)
- **Analytics dashboard** with sales charts (Chart.js)
- **Reporting** and transaction history
- **Staff daily log** and profile management
- **Responsive UI** with mobile sidebar toggle

### Tech Stack
| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ (PDO) |
| Database | MySQL |
| Frontend | HTML5, CSS3 (Custom), Bootstrap 5, Font Awesome 6 |
| Charts | Chart.js |
| Server | Apache/Nginx (traditional PHP hosting) |

## Project Structure

```
Cremoso/
├── index.php                 # Entry point - redirects by role
├── login.php                 # Login page with demo credentials
├── logout.php                # Session destroy & redirect
├── schema.sql                # Database schema & seed data
│
├── config/
│   └── database.php          # PDO connection setup
│
├── includes/
│   ├── auth.php              # Authentication helper functions
│   ├── header.php            # Shared HTML head + mobile toggle
│   ├── footer.php            # Shared footer + sidebar JS
│   ├── sidebar.php           # Base sidebar (unused?)
│   ├── sidebar_admin.php     # Admin navigation
│   └── sidebar_staff.php     # Staff navigation
│
├── admin/
│   ├── dashboard.php         # Admin overview (stats, charts, activity)
│   ├── analytics.php         # Sales analytics
│   ├── items.php             # Item management (CRUD)
│   ├── transactions.php      # Transaction history
│   ├── reports.php           # Reports generation
│   └── profile.php           # Admin profile
│
├── staff/
│   ├── dashboard.php         # Staff branch overview
│   ├── new_order.php         # Order creation interface
│   ├── save_order.php        # Order processing handler
│   ├── daily_log.php         # Daily log entries
│   └── profile.php           # Staff profile
│
├── assets/
│   ├── css/
│   │   └── style.css         # Full stylesheet (Cremoso teal theme)
│   └── images/
│       └── logo.jpg          # Brand logo
│
└── .playwright-mcp/          # Playwright MCP session logs
```

## Database Setup

The database is defined in `schema.sql`. It creates:

### Tables
- `branches` - Store locations
- `users` - Admin & staff accounts (plain-text passwords for demo)
- `customers` - Customer records
- `order_channels` - Sales channels (Walk-in, Facebook, Foodpanda)
- `payment_methods` - Payment types (Cash, GCash, Card)
- `flavors` - Soft-serve flavors (Vanilla, Chocolate, Strawberry, Ube, Cheese)
- `item_sizes` - Sizes with price multipliers (Small, Medium, Large)
- `items` - Menu items (flavor + size + price)
- `transactions` - Order headers
- `transaction_items` - Line items per order

### Demo Credentials
| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin` |
| Staff 1 (HQ) | `staff1` | `staff1` |
| Staff 2 (Downtown) | `staff2` | `staff2` |

## Running the Application

### Prerequisites
- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB
- Web server (Apache, Nginx, or PHP built-in server)

### Setup Steps

1. **Create the database:**
   ```bash
   mysql -u root -p < schema.sql
   ```

2. **Configure database connection:**
   Edit `config/database.php` if your MySQL credentials differ from defaults:
   ```php
   $host = 'localhost';
   $dbname = 'cremoso_db';
   $username = 'root';
   $password = '';
   ```

3. **Start the PHP server:**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   ```

4. **Access the application:**
   Open `http://localhost:8000/login.php` in your browser.

## Development Conventions

### Authentication
- Sessions are used for user state (`$_SESSION`)
- Helper functions in `includes/auth.php`: `isLoggedIn()`, `isAdmin()`, `isStaff()`, `redirectIfNotLoggedIn()`, etc.
- All protected pages check authentication at the top

### Database Access
- PDO with prepared statements for all queries
- `$pdo` variable is made available globally via `global $pdo`
- Queries use positional placeholders (`?`) with bound parameters

### Security Notes
- **Passwords are stored in plain text** — this is a demo/prototype system. For production, implement `password_hash()` / `password_verify()`.
- No CSRF protection on forms
- Session fixation prevention is not implemented

### UI/Styling
- Custom CSS using CSS custom properties (design tokens)
- Color theme: **Cremoso Teal** palette (`--primary: #2DA89B`)
- Font: Inter (Google Fonts)
- Responsive design with mobile-first breakpoints at 768px and 640px
- Sidebar navigation with mobile toggle (hamburger menu)

### Template Pattern
- Pages follow a consistent structure:
  1. Auth check & redirect
  2. Database queries
  3. Include header + sidebar
  4. Render main content
  5. Include footer

## Currency

The system uses **Philippine Peso (₱)** as its currency.

## Known Limitations

1. **Plain-text passwords** in database (not hashed)
2. **No input sanitization** beyond prepared statements (XSS risk on `htmlspecialchars()` output only)
3. **No pagination** on transaction/item lists
4. **Chart.js** loaded via CDN (requires internet)
5. **No API layer** — all logic is server-rendered PHP
