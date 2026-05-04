# 🍦 Cremoso - Sales & Transaction Management System

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-Educational-green)

A comprehensive **PHP-based web application** for managing soft-serve ice cream sales and transactions across multiple branch locations. Built for Cremoso, featuring **Google OAuth authentication**, role-based access control, real-time analytics, multi-channel ordering, and advanced reporting capabilities.

Perfect for small to medium-sized food service businesses looking for a complete point-of-sale and management solution with modern authentication and detailed operational insights.

---

## 📋 Table of Contents

- [✨ Features](#-features)
- [🚀 Quick Start](#-quick-start)
- [📦 Prerequisites](#-prerequisites)
- [⚙️ Installation](#️-installation)
  - [Environment Configuration](#environment-configuration)
  - [Database Setup](#database-setup)
  - [Google OAuth Setup](#google-oauth-setup)
- [🗄️ Database Architecture](#️-database-architecture)
- [👥 Features by Role](#-features-by-role)
  - [Admin Capabilities](#admin-capabilities)
  - [Staff Capabilities](#staff-capabilities)
- [📁 Project Structure](#-project-structure)
- [🛠️ Tech Stack & Architecture](#️-tech-stack--architecture)
- [🎨 UI/UX & Design System](#-uiux--design-system)
- [🔒 Security & Production Deployment](#-security--production-deployment)
- [💻 Development & Contribution](#-development--contribution)
- [📄 License](#-license)
- [🤝 Support](#-support)

---

## ✨ Features

- **🔐 Dual Authentication System** - Traditional username/password + Google OAuth 2.0 integration
- **👥 Role-Based Access Control** - Separate dashboards and permissions for Admin and Staff roles
- **🏪 Multi-Branch Management** - Manage multiple store locations with branch-specific operations
- **📦 Advanced Order Management** - Create orders with items, add-ons (toppings, sauces, fruits), and quantities
- **🌐 Multi-Channel Ordering** - Support for Walk-in, Facebook Messenger, and Foodpanda orders
- **💳 Multiple Payment Methods** - Cash, GCash, Credit/Debit Card with account name tracking
- **📊 Real-Time Analytics Dashboard** - Interactive sales charts and KPIs powered by Chart.js
- **📈 Comprehensive Reporting** - Generate sales reports, transaction history, and daily summaries
- **👨‍💼 Staff Management** - Admin approval workflow for new staff registrations
- **🍨 Menu Management** - Full CRUD operations for items, categories, sizes, and add-ons
- **⏳ Pending Orders System** - Track and manage orders from creation to completion
- **📝 Daily Activity Logs** - Staff can log daily operations and activities
- **📱 Responsive Design** - Mobile-friendly interface with collapsible navigation
- **🕐 Timezone Support** - Configured for Asia/Manila (GMT+8) timezone

---


## 🚀 Quick Start

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

> 💡 **Note**: For detailed setup including Google OAuth, see [Installation](#️-installation) section.

---


## 📦 Prerequisites

Before installing Cremoso, ensure your system meets these requirements:

- **PHP 7.4 or higher** with the following extensions:
  - `pdo_mysql` - Database connectivity
  - `mbstring` - String handling
  - `json` - JSON processing
- **MySQL 5.7+ or MariaDB 10.3+**
- **Web Server**: Apache, Nginx, or PHP built-in development server
- **Composer** (optional, for dependency management)
- **Git** (for cloning the repository)

---

## ⚙️ Installation

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

> ⚠️ **Important**: 
> - For local development, use `http://localhost:8000/google_callback.php`
> - For production, update to your actual domain (e.g., `https://yourdomain.com/google_callback.php`)
> - See [Google OAuth Setup](#google-oauth-setup) for obtaining credentials

### Step 3: Database Setup

#### Create the Database

```bash
mysql -u root -p -e "CREATE DATABASE cremoso_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### Import Schema

```bash
mysql -u root -p cremoso_db < database/migrations/schema.sql
```

#### Choose a Seed Option (Optional)

Cremoso provides three seed files for different scenarios:

**Option A: Basic Demo Data** (Recommended for first-time setup)
```bash
# Already included in schema.sql - no additional import needed
```

**Option B: Realistic Sample Data** (30+ transactions with varied patterns)
```bash
mysql -u root -p cremoso_db < database/seeds/seed_realistic_data.sql
```

**Option C: 30-Day Historical Data** (For testing analytics and reports)
```bash
mysql -u root -p cremoso_db < database/seeds/seed_30_days.sql
```

#### Configure Database Connection

If using non-default credentials, edit `config/database.php`:

```php
$host = 'localhost';
$dbname = 'cremoso_db';
$username = 'root';      // Change if needed
$password = '';          // Add your MySQL password
```

### Step 4: Start the Application

**Using PHP Built-in Server** (Development):
```bash
php -S localhost:8000
```

**Using XAMPP/WAMP**:
- Place the project in `htdocs/` or `www/` directory
- Access via `http://localhost/Cremoso/login.php`

**Using Apache/Nginx**:
- Configure virtual host to point to the project directory
- Ensure `.htaccess` is enabled (Apache) or configure rewrite rules (Nginx)

### Step 5: Access the Application

Open your browser and navigate to:
```
http://localhost:8000/login.php
```

Login with the demo credentials from the [Quick Start](#-quick-start) section.

### Troubleshooting

**Database Connection Failed**
- Verify MySQL is running: `mysql -u root -p`
- Check credentials in `config/database.php`
- Ensure database `cremoso_db` exists

**Google OAuth Not Working**
- Verify `.env` file exists and contains valid credentials
- Check redirect URI matches Google Cloud Console configuration
- See [Google OAuth Setup](#google-oauth-setup) for detailed instructions

**Permission Errors**
- Ensure web server has read access to all project files
- On Linux/Mac: `chmod -R 755 /path/to/cremoso`

**Blank Page or PHP Errors**
- Enable error reporting in `php.ini`: `display_errors = On`
- Check PHP version: `php -v` (must be 7.4+)
- Verify required extensions: `php -m | grep -E 'pdo_mysql|mbstring|json'`

---


### Google OAuth Setup

Cremoso supports Google Sign-In for both login and staff registration. Follow these steps to configure OAuth:

#### Step 1: Create a Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Select a project"** → **"New Project"**
3. Enter project name (e.g., "Cremoso POS") and click **"Create"**
4. Wait for project creation, then select your new project

#### Step 2: Enable Google+ API

1. In the left sidebar, navigate to **"APIs & Services"** → **"Library"**
2. Search for **"Google+ API"** or **"Google Identity"**
3. Click on it and press **"Enable"**

#### Step 3: Configure OAuth Consent Screen

1. Go to **"APIs & Services"** → **"OAuth consent screen"**
2. Select **"External"** user type (or Internal if using Google Workspace)
3. Fill in required fields:
   - **App name**: Cremoso Sales System
   - **User support email**: Your email
   - **Developer contact**: Your email
4. Click **"Save and Continue"**
5. Skip "Scopes" section (click **"Save and Continue"**)
6. Add test users if needed, then click **"Save and Continue"**

#### Step 4: Create OAuth Credentials

1. Go to **"APIs & Services"** → **"Credentials"**
2. Click **"+ Create Credentials"** → **"OAuth client ID"**
3. Select **"Web application"**
4. Configure:
   - **Name**: Cremoso Web Client
   - **Authorized JavaScript origins**: 
     - `http://localhost:8000` (development)
     - `https://yourdomain.com` (production)
   - **Authorized redirect URIs**:
     - `http://localhost:8000/google_callback.php` (development)
     - `https://yourdomain.com/google_callback.php` (production)
5. Click **"Create"**

#### Step 5: Copy Credentials to .env

1. Copy the **Client ID** and **Client Secret** from the popup
2. Open your `.env` file and update:

```env
GOOGLE_CLIENT_ID=123456789-abcdefghijklmnop.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-your_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/google_callback.php
```

3. Save the file

#### Step 6: Test OAuth Integration

1. Navigate to `http://localhost:8000/login.php`
2. Click **"Sign in with Google"** button
3. Select your Google account
4. Grant permissions
5. You should be redirected back and logged in

#### OAuth Flow Modes

Cremoso supports two OAuth modes:

- **Login Mode** (`state=login`): Authenticates existing users via Google account
- **Signup Mode** (`state=signup`): Registers new staff accounts (requires admin approval)

#### Production Deployment Notes

When deploying to production:

1. Update `.env` with production domain:
   ```env
   GOOGLE_REDIRECT_URI=https://yourdomain.com/google_callback.php
   ```

2. Add production redirect URI to Google Cloud Console:
   - Go to **Credentials** → Edit your OAuth client
   - Add `https://yourdomain.com/google_callback.php` to Authorized redirect URIs

3. Publish OAuth consent screen (if using External user type):
   - Go to **OAuth consent screen** → Click **"Publish App"**

#### Troubleshooting OAuth

**Error: redirect_uri_mismatch**
- Ensure redirect URI in `.env` exactly matches Google Cloud Console configuration
- Check for trailing slashes, http vs https, port numbers

**Error: invalid_client**
- Verify Client ID and Client Secret are correct in `.env`
- Ensure no extra spaces or quotes in `.env` values

**User not found after OAuth**
- For login: User must exist in database with matching email
- For signup: New staff accounts require admin approval before login

---


## 🗄️ Database Architecture

### Schema Overview

Cremoso uses a simplified, normalized database structure optimized for transaction processing and reporting.

#### Core System Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| **`branches`** | Store locations | `branch_id`, `branch_name`, `location`, `is_active` |
| **`users`** | Admin & staff accounts | `user_id`, `username`, `fullname`, `role`, `google_id`, `email`, `is_confirmed` |
| **`customers`** | Customer records | `customer_id`, `customer_name`, `phone`, `email` |
| **`order_channels`** | Sales channels | `channel_id`, `channel_name` (Walk-in, Facebook, Foodpanda) |
| **`payment_methods`** | Payment types | `payment_method_id`, `method_name` (Cash, GCash, Card) |

#### Menu & Inventory Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| **`items`** | Menu items with variations | `item_id`, `item_name`, `category`, `base_item`, `variant`, `size`, `price` |
| **`addons`** | Toppings, sauces, fruits | `addon_id`, `addon_name`, `addon_type`, `price` |

**Menu Categories**: Soft-serve, Cremdae, Parfait, Frozen Yogurt, Float, Yogurt  
**Add-on Types**: Topping (16 items), Sauce (9 items), Fruit (4 items)

#### Transaction Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| **`transactions`** | Order headers | `transaction_id`, `order_number`, `customer_id`, `user_id`, `branch_id`, `channel_id`, `payment_method_id`, `total_amount`, `status` |
| **`transaction_items`** | Line items per order | `transaction_item_id`, `transaction_id`, `item_id`, `quantity`, `addons_detail` (JSON), `subtotal` |

### Database Migrations

Located in `database/migrations/`:

- **`schema.sql`** - Complete database schema with basic seed data (2 branches, 3 users, 22 menu items, 29 add-ons)
- **`fix_status.sql`** - Migration to add/update transaction status field

### Seed Data Options

Located in `database/seeds/`:

| Seed File | Purpose | Contents |
|-----------|---------|----------|
| **`seed_data_fixed.sql`** | Basic demo data | Minimal transactions for testing |
| **`seed_realistic_data.sql`** | Realistic sample data | 30+ transactions with varied patterns, multiple channels, payment methods |
| **`seed_30_days.sql`** | Historical data | 30 days of transaction history for analytics and reporting testing |

**Usage**:
```bash
# Import schema first (required)
mysql -u root -p cremoso_db < database/migrations/schema.sql

# Then optionally import additional seed data
mysql -u root -p cremoso_db < database/seeds/seed_realistic_data.sql
```

### Key Design Decisions

1. **Simplified Menu Structure**: Items table stores complete variations (flavor + size) instead of complex joins
2. **Add-ons as JSON**: `transaction_items.addons_detail` stores selected add-ons as JSON for flexibility
3. **Snapshot Pattern**: Transaction items store item names/prices at time of order (prevents historical data corruption)
4. **Google OAuth Integration**: `users.google_id` and `users.email` support OAuth authentication
5. **Staff Approval Workflow**: `users.is_confirmed` requires admin approval for new staff accounts
6. **Timezone Awareness**: All timestamps use Asia/Manila (GMT+8) timezone

### Entity Relationships

```
branches (1) ──< (N) users
branches (1) ──< (N) transactions

users (1) ──< (N) transactions [staff who processed]
customers (1) ──< (N) transactions

transactions (1) ──< (N) transaction_items
items (1) ──< (N) transaction_items

order_channels (1) ──< (N) transactions
payment_methods (1) ──< (N) transactions
```

---


## 👥 Features by Role

### Admin Capabilities

Administrators have full system access with the following features:

#### 📊 Dashboard & Analytics
- **Real-time KPIs**: Total sales, orders, revenue trends
- **Interactive Charts**: Daily/weekly/monthly sales visualization with Chart.js
- **Branch Performance**: Compare sales across multiple locations
- **Top Products**: Identify best-selling items and categories

#### 🏪 Branch Management
- Create, edit, and deactivate branch locations
- Assign staff to specific branches
- View branch-specific sales and operations
- Manage branch contact information and status

#### 👨‍💼 Staff Management
- **Approval Workflow**: Review and approve new staff registrations
- **Account Management**: Create, edit, deactivate staff accounts
- **Role Assignment**: Assign staff to branches
- **Activity Monitoring**: View staff transaction history
- **Google OAuth Integration**: Staff can sign up with Google accounts (pending admin approval)

#### 🍨 Menu Management
- **Item Management**: Full CRUD operations for menu items
  - Add new items with category, variant, size, and pricing
  - Edit existing items and update prices
  - Deactivate items without deleting historical data
- **Add-ons Management**: Manage toppings, sauces, and fruits
  - Configure add-on types and pricing
  - Control availability and display order
- **Category Organization**: Organize items by category (Soft-serve, Parfait, Float, etc.)

#### 📈 Reports & Transaction History
- **Sales Reports**: Generate reports by date range, branch, or channel
- **Transaction History**: View all orders with detailed line items
- **Daily Summaries**: Review daily sales performance
- **Export Capabilities**: Download reports for external analysis
- **Payment Method Breakdown**: Analyze payment preferences (Cash, GCash, Card)

#### 👤 Profile Management
- Update personal information
- Change password
- View account details

---

### Staff Capabilities

Staff members have branch-specific access with operational features:

#### 📦 Order Management
- **Create New Orders**: 
  - Select items from menu with variants and sizes
  - Add multiple add-ons (toppings, sauces, fruits)
  - Set quantities and calculate totals automatically
  - Choose order channel (Walk-in, Facebook, Foodpanda)
  - Select payment method and enter account name if needed
- **Pending Orders**: 
  - View orders awaiting completion
  - Mark orders as completed
  - Track order status and details

#### 📊 Branch Dashboard
- View branch-specific sales statistics
- Monitor daily performance metrics
- Track order counts and revenue
- View recent transactions

#### 📝 Daily Activity Log
- Log daily operations and activities
- Record notes and observations
- Track operational issues or highlights
- Maintain activity history

#### 📈 Reports
- Generate branch-specific sales reports
- View transaction history for assigned branch
- Access daily summaries

#### 👤 Profile & Account
- **Profile Management**: Update personal information
- **Staff Signup**: Register using Google OAuth (requires admin approval)
- **Password Management**: Change account password

---


## 📁 Project Structure

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
│   ├── get_item_sizes.php       # AJAX endpoint for item sizes
│   └── get_item_flavors.php     # AJAX endpoint for item flavors
│
├── database/
│   ├── migrations/
│   │   ├── schema.sql           # Complete database schema with basic seed data
│   │   └── fix_status.sql       # Status field migration
│   └── seeds/
│       ├── seed_data_fixed.sql  # Basic demo data
│       ├── seed_realistic_data.sql  # 30+ realistic transactions
│       └── seed_30_days.sql     # 30-day historical data
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

### Key Files Explained

**Entry Points:**
- `index.php` - Redirects authenticated users to appropriate dashboard based on role
- `login.php` - Handles traditional and Google OAuth authentication
- `google_callback.php` - Processes OAuth responses and creates/authenticates users

**Configuration:**
- `config/database.php` - Establishes PDO connection, sets timezone to Asia/Manila
- `config/google.php` - Loads `.env` file and defines OAuth constants

**Shared Components:**
- `includes/auth.php` - Functions: `isLoggedIn()`, `isAdmin()`, `isStaff()`, `requireAuth()`
- `includes/menu_helpers.php` - Database queries for menu items and add-ons

**AJAX Endpoints:**
- `admin/get_item_sizes.php` - Returns available sizes for selected item
- `staff/get_item_sizes.php` - Returns sizes for order creation
- `staff/get_item_flavors.php` - Returns flavors/variants for selected item

---


## 🛠️ Tech Stack & Architecture

### Technology Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **Backend** | PHP 7.4+ | Server-side logic and rendering |
| **Database** | MySQL 5.7+ / MariaDB | Relational data storage |
| **Authentication** | Google OAuth 2.0 | Third-party authentication |
| **Session Management** | PHP Sessions | User state management |
| **Frontend Framework** | Bootstrap 5.3 | Responsive UI components |
| **Icons** | Font Awesome 6 | Icon library |
| **Charts** | Chart.js | Data visualization |
| **Typography** | Google Fonts (Inter) | Custom web fonts |
| **Database Access** | PDO (PHP Data Objects) | Secure database queries |

### Architecture Patterns

#### Authentication Flow

1. **Traditional Login**: Username/password → Session creation → Role-based redirect
2. **Google OAuth Login**: 
   - User clicks "Sign in with Google" → Google authorization → Callback with code
   - Exchange code for tokens → Fetch user info → Match email in database
   - Create session → Redirect to dashboard
3. **Google OAuth Signup**:
   - Staff clicks "Sign up with Google" → Google authorization → Callback with code
   - Create pending user account (`is_confirmed = FALSE`)
   - Admin reviews and approves → Staff can login

#### Database Access Pattern

- **PDO with Prepared Statements**: All queries use parameterized statements to prevent SQL injection
- **Global Connection**: `$pdo` variable available via `global $pdo` in all scripts
- **Timezone Handling**: Database and PHP both set to `Asia/Manila` (GMT+8)
- **Transaction Safety**: Uses `BEGIN`, `COMMIT`, `ROLLBACK` for multi-step operations

#### Template Pattern

All pages follow this structure:

```php
<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// 1. Authentication check
if (!isLoggedIn()) { header('Location: ../login.php'); exit(); }
if (!isAdmin()) { header('Location: ../index.php'); exit(); }

// 2. Business logic & database queries
$stmt = $pdo->prepare("SELECT * FROM table WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetchAll();

// 3. Include header & sidebar
include '../includes/header.php';
include '../includes/sidebar_admin.php';
?>

<!-- 4. Main content -->
<div class="main-content">
    <!-- Page content here -->
</div>

<?php
// 5. Include footer
include '../includes/footer.php';
?>
```

### Development Conventions

#### Code Style
- **Indentation**: 4 spaces (no tabs)
- **Naming**: `snake_case` for variables/functions, `PascalCase` for classes
- **SQL**: Uppercase keywords (`SELECT`, `FROM`, `WHERE`)
- **Comments**: Use `//` for single-line, `/* */` for multi-line

#### Security Practices
- **Prepared Statements**: Always use `?` placeholders with `execute([$params])`
- **Session Validation**: Check `isLoggedIn()` on every protected page
- **Role Verification**: Use `isAdmin()` or `isStaff()` for role-specific pages
- **Output Escaping**: Use `htmlspecialchars()` for user-generated content (currently limited)

#### File Organization
- **Entry Points**: Root directory (`index.php`, `login.php`, `logout.php`)
- **Role Modules**: Separate directories (`admin/`, `staff/`)
- **Shared Code**: `includes/` for reusable components
- **Configuration**: `config/` for database and OAuth settings
- **Assets**: `assets/css/`, `assets/js/`, `assets/images/`

### Currency & Localization

- **Currency**: Philippine Peso (₱)
- **Timezone**: Asia/Manila (GMT+8)
- **Date Format**: `Y-m-d H:i:s` (MySQL DATETIME)
- **Number Format**: `number_format($amount, 2)` for currency display

---


## 🎨 UI/UX & Design System

### Color Palette

**Cremoso Teal Theme** - Primary brand color with complementary shades

```css
--primary: #2DA89B;           /* Cremoso Teal */
--primary-dark: #258a7f;      /* Hover states */
--primary-light: #e8f6f5;     /* Backgrounds */
--secondary: #6c757d;         /* Gray accents */
--success: #28a745;           /* Success states */
--danger: #dc3545;            /* Error states */
--warning: #ffc107;           /* Warning states */
```

### Typography

- **Primary Font**: Inter (Google Fonts)
- **Fallback Stack**: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif
- **Headings**: Bold weight (700)
- **Body Text**: Regular weight (400)
- **Base Size**: 16px

### Layout Structure

#### Admin & Staff Dashboards

```
┌─────────────────────────────────────────┐
│  Top Navigation Bar (Logo, User Menu)  │
├──────┬──────────────────────────────────┤
│      │                                  │
│ Side │  Main Content Area               │
│ bar  │  (Dashboard, Forms, Tables)      │
│      │                                  │
│ Nav  │                                  │
│      │                                  │
└──────┴──────────────────────────────────┘
```

**Responsive Behavior**:
- **Desktop (≥992px)**: Sidebar always visible, full width
- **Tablet (768px-991px)**: Collapsible sidebar with toggle button
- **Mobile (<768px)**: Hamburger menu, overlay sidebar

### Component Patterns

#### Cards
- **Shadow**: `box-shadow: 0 2px 4px rgba(0,0,0,0.1)`
- **Border Radius**: `8px`
- **Padding**: `1.5rem`
- **Background**: White with subtle hover effects

#### Buttons
- **Primary**: Cremoso Teal background, white text
- **Secondary**: Gray outline, transparent background
- **Sizes**: Small (`.btn-sm`), Default, Large (`.btn-lg`)
- **States**: Hover (darker), Active (pressed), Disabled (opacity 0.6)

#### Forms
- **Input Height**: `38px` (default)
- **Border**: `1px solid #ced4da`
- **Focus State**: Cremoso Teal border with subtle shadow
- **Labels**: Bold, positioned above inputs
- **Validation**: Red border for errors, green for success

#### Tables
- **Striped Rows**: Alternating light gray background
- **Hover Effect**: Light teal highlight on row hover
- **Responsive**: Horizontal scroll on mobile devices
- **Actions Column**: Right-aligned with icon buttons

### Navigation

#### Sidebar Navigation
- **Width**: 250px (desktop), 100% (mobile overlay)
- **Background**: Dark gradient (`#2c3e50` to `#34495e`)
- **Active State**: Cremoso Teal left border + lighter background
- **Icons**: Font Awesome 6 icons with text labels
- **Collapsible**: Smooth slide animation (300ms)

#### Top Navigation
- **Height**: 60px
- **Background**: White with bottom shadow
- **Logo**: Left-aligned, 40px height
- **User Menu**: Right-aligned dropdown
- **Mobile**: Hamburger menu button (left), logo (center), user menu (right)

### Iconography

**Font Awesome 6** icon usage:
- **Dashboard**: `fa-chart-line`
- **Orders**: `fa-shopping-cart`
- **Reports**: `fa-file-alt`
- **Settings**: `fa-cog`
- **Users**: `fa-users`
- **Logout**: `fa-sign-out-alt`

### Responsive Breakpoints

```css
/* Mobile First Approach */
@media (min-width: 576px)  { /* Small devices */ }
@media (min-width: 768px)  { /* Tablets */ }
@media (min-width: 992px)  { /* Desktops */ }
@media (min-width: 1200px) { /* Large desktops */ }
```

### CSS Organization

- **`style.css`** (50KB) - Main stylesheet with global styles, utilities, and shared components
- **`admin.css`** (6KB) - Admin-specific overrides and dashboard styles
- **`staff.css`** (7KB) - Staff-specific overrides and order management styles
- **`auth.css`** (3KB) - Login/signup page styles with centered card layout

### Accessibility

- **Color Contrast**: WCAG AA compliant (4.5:1 minimum)
- **Focus Indicators**: Visible outline on keyboard navigation
- **Alt Text**: Images include descriptive alt attributes
- **Semantic HTML**: Proper heading hierarchy (h1-h6)
- **ARIA Labels**: Used for icon-only buttons

---


## 🔒 Security & Production Deployment

### Current Security Status

⚠️ **Important**: This application is designed for **educational and demonstration purposes**. Several security features require implementation before production deployment.

### Known Limitations

- **Plain-text Passwords**: User passwords are stored without hashing (demo only)
- **No CSRF Protection**: Forms lack CSRF token validation
- **Limited Input Sanitization**: Relies primarily on prepared statements
- **No Rate Limiting**: Login attempts are not throttled
- **Session Security**: Basic session management without advanced protections
- **No Pagination**: Large datasets may cause performance issues
- **CDN Dependencies**: Chart.js and other libraries loaded from CDN (requires internet)

### Security Recommendations for Production

#### 1. Password Security

**Current (Demo)**:
```php
$password = $_POST['password'];  // Plain text
```

**Production**:
```php
// Registration
$hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Login verification
if (password_verify($_POST['password'], $user['password'])) {
    // Login successful
}
```

#### 2. CSRF Protection

Add CSRF tokens to all forms:

```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// In form
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Validate
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF token validation failed');
}
```

#### 3. Input Sanitization & Output Escaping

```php
// Sanitize input
$input = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING);

// Escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

#### 4. Session Security

Add to `config/database.php`:

```php
// Regenerate session ID on login
session_regenerate_id(true);

// Set secure session parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);      // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
```

#### 5. HTTPS Configuration

**Production Requirements**:
- Obtain SSL/TLS certificate (Let's Encrypt recommended)
- Configure web server to redirect HTTP → HTTPS
- Update `.env` with HTTPS URLs
- Enable `Strict-Transport-Security` header

#### 6. Environment Variables

**Never commit `.env` to version control**:

```bash
# .gitignore
.env
config/database.php  # If it contains credentials
```

**Production `.env`**:
```env
GOOGLE_CLIENT_ID=production_client_id
GOOGLE_CLIENT_SECRET=production_secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/google_callback.php
```

#### 7. Database Security

- Use separate database user with minimal privileges
- Enable MySQL SSL connections
- Regular backups with encryption
- Implement query logging for auditing

```sql
-- Create limited user
CREATE USER 'cremoso_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE ON cremoso_db.* TO 'cremoso_app'@'localhost';
FLUSH PRIVILEGES;
```

#### 8. Rate Limiting

Implement login attempt throttling:

```php
// Track failed attempts
$_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
$_SESSION['last_attempt'] = time();

// Block after 5 attempts
if ($_SESSION['login_attempts'] >= 5) {
    $lockout_time = 900; // 15 minutes
    if (time() - $_SESSION['last_attempt'] < $lockout_time) {
        die('Too many login attempts. Try again in 15 minutes.');
    }
}
```

#### 9. Error Handling

**Development**:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Production**:
```php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

#### 10. File Permissions

```bash
# Linux/Mac production server
chmod 755 /path/to/cremoso
chmod 644 /path/to/cremoso/*.php
chmod 600 /path/to/cremoso/.env
chown -R www-data:www-data /path/to/cremoso
```

### Production Deployment Checklist

- [ ] Implement password hashing with `password_hash()`
- [ ] Add CSRF protection to all forms
- [ ] Enable HTTPS and obtain SSL certificate
- [ ] Update `.env` with production credentials
- [ ] Configure session security settings
- [ ] Implement rate limiting on login
- [ ] Add input sanitization and output escaping
- [ ] Set up error logging (disable display_errors)
- [ ] Configure database user with minimal privileges
- [ ] Set proper file permissions
- [ ] Enable MySQL SSL connections
- [ ] Implement automated database backups
- [ ] Add pagination to large data tables
- [ ] Host static assets locally (remove CDN dependencies)
- [ ] Configure web server security headers
- [ ] Set up monitoring and alerting
- [ ] Perform security audit and penetration testing

### Recommended Security Headers

Add to Apache `.htaccess` or Nginx config:

```apache
# Apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

```nginx
# Nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

---


## 💻 Development & Contribution

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

### Coding Standards

#### PHP Conventions

- **PSR-12 Compliant**: Follow PHP-FIG coding standards where applicable
- **Indentation**: 4 spaces (no tabs)
- **Naming Conventions**:
  - Variables/Functions: `snake_case` (e.g., `$user_id`, `get_user_data()`)
  - Classes: `PascalCase` (e.g., `UserManager`)
  - Constants: `UPPER_SNAKE_CASE` (e.g., `MAX_LOGIN_ATTEMPTS`)
- **SQL Keywords**: Always uppercase (`SELECT`, `FROM`, `WHERE`, `JOIN`)
- **Prepared Statements**: Always use parameterized queries with `?` placeholders

#### File Structure

- **One responsibility per file**: Each PHP file should have a single, clear purpose
- **Consistent includes**: Always include `database.php` and `auth.php` at the top
- **Template pattern**: Follow the established pattern (auth → logic → header → content → footer)

#### Database Changes

**Creating Migrations**:

1. Create a new SQL file in `database/migrations/` with descriptive name:
   ```
   database/migrations/add_user_preferences.sql
   ```

2. Include both `UP` and `DOWN` migrations:
   ```sql
   -- UP: Add user preferences table
   CREATE TABLE user_preferences (
       preference_id INT PRIMARY KEY AUTO_INCREMENT,
       user_id INT NOT NULL,
       theme VARCHAR(20) DEFAULT 'light',
       FOREIGN KEY (user_id) REFERENCES users(user_id)
   );
   
   -- DOWN: Remove user preferences table
   -- DROP TABLE user_preferences;
   ```

3. Document the migration in this README

**Updating Seed Data**:

- Modify existing seed files or create new ones for specific scenarios
- Ensure seed data is realistic and useful for testing
- Document any new seed files in the Database Architecture section

### Adding New Features

#### Admin Features

1. Create new PHP file in `admin/` directory
2. Follow template pattern with authentication checks
3. Add navigation link to `includes/sidebar_admin.php`
4. Update this README's "Admin Capabilities" section

#### Staff Features

1. Create new PHP file in `staff/` directory
2. Ensure branch-specific data filtering
3. Add navigation link to `includes/sidebar_staff.php`
4. Update this README's "Staff Capabilities" section

#### AJAX Endpoints

1. Create endpoint file (e.g., `get_data.php`)
2. Return JSON responses:
   ```php
   header('Content-Type: application/json');
   echo json_encode(['success' => true, 'data' => $data]);
   ```
3. Include proper error handling
4. Document endpoint in Project Structure section

### Testing Guidelines

#### Manual Testing Checklist

- [ ] Test with both Admin and Staff roles
- [ ] Verify branch-specific data isolation (Staff)
- [ ] Test all CRUD operations (Create, Read, Update, Delete)
- [ ] Check responsive design on mobile/tablet/desktop
- [ ] Verify form validation (client-side and server-side)
- [ ] Test error handling (invalid inputs, missing data)
- [ ] Check authentication and authorization
- [ ] Test with different browsers (Chrome, Firefox, Safari, Edge)

#### Database Testing

- [ ] Test with empty database (fresh install)
- [ ] Test with all three seed data options
- [ ] Verify foreign key constraints
- [ ] Check data integrity after operations

### Code Review Guidelines

When reviewing pull requests, check for:

- **Security**: Prepared statements, input validation, output escaping
- **Authentication**: Proper role checks on protected pages
- **Code Quality**: Follows conventions, readable, well-commented
- **Database**: Proper use of transactions, efficient queries
- **UI/UX**: Consistent with design system, responsive
- **Documentation**: README updated if needed

### Common Pitfalls to Avoid

1. **SQL Injection**: Never concatenate user input into SQL queries
   ```php
   // ❌ BAD
   $sql = "SELECT * FROM users WHERE id = " . $_GET['id'];
   
   // ✅ GOOD
   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
   $stmt->execute([$_GET['id']]);
   ```

2. **Missing Authentication**: Always check login status on protected pages
   ```php
   // ✅ Required at top of every protected page
   if (!isLoggedIn()) { header('Location: ../login.php'); exit(); }
   ```

3. **Hardcoded Values**: Use configuration files or database for settings
   ```php
   // ❌ BAD
   $branch_id = 1;
   
   // ✅ GOOD
   $branch_id = $_SESSION['branch_id'];
   ```

4. **Ignoring Timezone**: Always use configured timezone
   ```php
   // ✅ Already set in config/database.php
   date_default_timezone_set('Asia/Manila');
   ```

### Getting Help

- **Code Questions**: Review existing similar files for patterns
- **Database Issues**: Check `database/migrations/schema.sql` for structure
- **Authentication**: See `includes/auth.php` for helper functions
- **UI Components**: Reference Bootstrap 5.3 documentation

---


## 📄 License

This project is provided as-is for **educational and demonstration purposes**. 

---

## 🤝 Support

For questions, issues, or contributions:

- **Documentation**: Review this README and inline code comments
- **Database Schema**: Check `database/migrations/schema.sql` for structure details
- **Code Patterns**: Examine existing files for implementation examples
- **Issues**: Report bugs or request features via GitHub Issues

---

**Built with ❤️ for Cremoso Soft-Serve Ice Cream**
