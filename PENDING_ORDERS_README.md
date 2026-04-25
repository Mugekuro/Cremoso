# Pending Orders System

## Overview
The Cremoso ice cream shop management system now includes a pending orders workflow to better manage customer transactions and payment confirmations.

## How It Works

### 1. Order Creation
- When staff creates a new order through `staff/new_order.php`, the order is saved with status `'pending'`
- The order is immediately placed in the pending queue instead of being marked as completed
- Staff is redirected to the Pending Orders page to see the new order

### 2. Pending Orders Management
- Access pending orders via `staff/pending_orders.php` or the "Pending Orders" link in the sidebar
- View all orders awaiting payment confirmation
- See detailed order information including items, customer, payment method, and staff member

### 3. Order Confirmation/Cancellation
- **Confirm Order**: Click "Confirm" when customer completes payment - moves order to `'confirmed'` status
- **Cancel Order**: Click "Cancel" if payment is not received - moves order to `'cancelled'` status
- Only confirmed orders appear in dashboard statistics and daily logs

### 4. Dashboard Integration
- Dashboard now shows a "Pending Orders" count in the stats grid
- Count turns orange/warning color when there are pending orders
- Only confirmed orders are included in revenue and order statistics
- Recent Orders section only shows confirmed transactions

## Database Changes

### Migration Required
Run the SQL migration in `pending_orders_migration.sql`:

```sql
-- Add status column to transactions table
ALTER TABLE transactions 
ADD COLUMN status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending' AFTER total_amount;

-- Update existing transactions to be confirmed (backward compatibility)
UPDATE transactions SET status = 'confirmed' WHERE status = 'pending';
```

### Status Values
- `'pending'`: Order created, awaiting payment confirmation
- `'confirmed'`: Payment received and confirmed by staff
- `'cancelled'`: Order cancelled due to non-payment or other issues

## Files Modified/Created

### New Files
- `staff/pending_orders.php` - Main pending orders management page
- `pending_orders_migration.sql` - Database migration script

### Modified Files
- `staff/save_order.php` - Now saves orders as 'pending' status
- `staff/dashboard.php` - Updated to show only confirmed orders and pending count
- `staff/daily_log.php` - Updated to show only confirmed transactions
- `includes/sidebar_staff.php` - Added pending orders navigation link
- `assets/css/style.css` - Added success/error button styles and status colors

## Benefits

1. **Better Payment Tracking**: Orders don't appear as completed until payment is confirmed
2. **Reduced Confusion**: Clear separation between pending and completed orders
3. **Improved Cash Flow**: Staff can easily see which orders need payment follow-up
4. **System Availability**: Cancelled orders free up the system for new customers
5. **Accurate Reporting**: Only confirmed orders are included in sales reports and statistics

## Usage Workflow

1. Staff creates order → Order goes to pending
2. Customer pays → Staff confirms order → Order becomes confirmed
3. Customer doesn't pay → Staff cancels order → Order becomes cancelled
4. Only confirmed orders appear in dashboard and reports