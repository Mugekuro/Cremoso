# Cremoso - Sales & Transaction Management System

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-Educational-green)

A web application that manages soft-serve ice cream sales and transactions across multiple branch locations.


---

## Key Features

- **Role-Based Access**: Separate dashboards and permissions for Admin and Staff roles
- **Multi-Branch Management**: Manage multiple store locations with branch-specific operations
- **Order Management**: Create orders with items, add-ons, and multiple payment methods
- **Staff Management**: Admin approval workflow for new staff registrations

---


## Quick Start

Get Cremoso running in 5 minutes:

1. **Clone and navigate to the project**
   ```bash
   cd Cremoso
   ```

2. **Configure environment variables**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

3. **Create database and import schema**
   ```bash
   mysql -u root -p -e "CREATE DATABASE cremoso_db;"
   mysql -u root -p cremoso_db < database/migrations/schema.sql
   ```

4. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

5. **Access the application**
   - Open: `http://localhost:8000/login.php`
   - Login with demo credentials below

### Demo Credentials

| Role | Username | Password | Branch |
|------|----------|----------|--------|
| **Admin** | `admin` | `admin` | All branches |
| **Staff** | `staff1` | `staff1` | HQ Main |
| **Staff** | `staff2` | `staff2` | Downtown Branch |

> **Note**: For detailed setup including Google OAuth, see [Installation](#️-installation) section.

---


## Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/cremoso.git
cd cremoso
```

### Step 2: Environment Configuration

Create your environment configuration file:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/google_callback.php
```

> **Important**: 
> - For local development, use `http://localhost:8000/google_callback.php`
> - For production, update to your actual domain (e.g., `https://yourdomain.com/google_callback.php`)
> - See [Google OAuth Setup](#google-oauth-setup) for obtaining credentials

### Step 3: Database Setup

#### Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE cremoso_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```



---


## Project Structure

```
Cremoso/
├── index.php                    # Entry point - role-based redirect to dashboards
├── login.php                    # Authentication page with Google OAuth integration
├── logout.php                   # Session destruction and logout handler
├── google_callback.php          # OAuth callback handler for Google Sign-In
├── .env                         # Environment configuration (not in repo)
├── .env.example                 # Environment template
├── .gitignore                   # Git ignore rules
│
├── config/
│   ├── database.php             # PDO database connection & timezone config
│   └── google.php               # Google OAuth configuration loader
│
├── includes/
│   ├── auth.php                 # Authentication helper functions
│   ├── header.php               # Shared HTML head section
│   ├── footer.php               # Shared footer with scripts
│   ├── sidebar_admin.php        # Admin navigation sidebar
│   ├── sidebar_staff.php        # Staff navigation sidebar
│   ├── topnav_admin.php         # Admin top navigation bar
│   ├── topnav_staff.php         # Staff top navigation bar
│   ├── menu_helpers.php         # Menu data retrieval functions
│   └── revoke_modal.php         # Confirmation modal component
│
├── admin/                       # Admin modules (13 files)
│   ├── dashboard.php            # Analytics dashboard with charts
│   ├── analytics.php            # Detailed sales analytics
│   ├── branch_management.php   # Branch CRUD operations
│   ├── staff_management.php    # Staff approval & management
│   ├── menu_management.php     # Menu & add-ons management
│   ├── items.php                # Item listing & management
│   ├── add_item.php             # Create new menu items
│   ├── edit_item.php            # Edit existing items
│   ├── transactions.php         # Transaction history viewer
│   ├── reports.php              # Report generation
│   ├── daily_detail.php         # Daily transaction details
│   ├── profile.php              # Admin profile management
│   └── get_item_sizes.php       # AJAX endpoint for item sizes
│
├── staff/                       # Staff modules (10 files)
│   ├── dashboard.php            # Branch-specific dashboard
│   ├── new_order.php            # Order creation interface
│   ├── save_order.php           # Order processing handler
│   ├── pending_orders.php       # Pending orders management
│   ├── daily_log.php            # Daily activity logging
│   ├── reports.php              # Branch reports
│   ├── profile.php              # Staff profile management
│   ├── signup.php               # Google OAuth staff registration
│   ├── get_item_sizes.php       # endpoint for item sizes
│   └── get_item_flavors.php     # endpoint for item flavors
│
├── database/
│   ├── migrations/
│   │   ├── schema.sql           # Complete database schema with basic seed data
│   │   └── fix_status.sql       # Status field migration
│   └── seeds/
│       └── seed_data_fixed.sql  # Basic demo data
│       
│       
│
└── assets/
    ├── css/
    │   ├── style.css            # Main stylesheet (Cremoso Teal theme)
    │   ├── admin.css            # Admin-specific styles
    │   ├── staff.css            # Staff-specific styles
    │   └── auth.css             # Authentication page styles
    ├── js/
    │   └── staff_modal.js       # Staff management modal interactions
    └── images/
        └── logo.jpg             # Cremoso brand logo
```


---


## Development & Contribution

### Development Workflow

#### Setting Up Development Environment

1. **Fork and clone the repository**
   ```bash
   git clone https://github.com/yourusername/cremoso.git
   cd cremoso
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes** following the coding conventions below

4. **Test thoroughly** in your local environment

5. **Commit with descriptive messages**
   ```bash
   git add .
   git commit -m "Add: Brief description of changes"
   ```

6. **Push and create a pull request**
   ```bash
   git push origin feature/your-feature-name
   ```


## About

This project is provided as-is for **educational and demonstration purposes**. 



