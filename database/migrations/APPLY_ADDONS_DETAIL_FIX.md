# Add-ons Detail Column Fix

## What This Fix Does

This fix ensures that the `addons_detail` column in the `transaction_items` table:
1. Stores "NONE" instead of NULL when an order has no add-ons
2. Stores JSON data when add-ons are present
3. Properly handles both cases in all related code

## Steps to Apply

### 1. Run the Migration SQL

Execute the migration file to add/update the column:

```bash
# Using MySQL command line
mysql -u root -p cremoso_db < database/migrations/add_addons_detail.sql

# OR using phpMyAdmin
# - Open phpMyAdmin
# - Select cremoso_db database
# - Go to SQL tab
# - Copy and paste the contents of add_addons_detail.sql
# - Click "Go"
```

### 2. Verify the Changes

The following files have been updated:

- ✅ `database/migrations/add_addons_detail.sql` - Migration to add column
- ✅ `database/migrations/schema.sql` - Updated schema definition
- ✅ `staff/save_order.php` - Stores "NONE" or JSON correctly
- ✅ `includes/menu_helpers.php` - Handles "NONE" when reading data

### 3. Test the Fix

1. **Test Order WITHOUT Add-ons:**
   - Go to Staff > New Order
   - Add an item without selecting any toppings/sauces/fruits
   - Submit the order
   - Check database: `addons_detail` should be "NONE"

2. **Test Order WITH Add-ons:**
   - Go to Staff > New Order
   - Add an item and select some toppings/sauces/fruits
   - Submit the order
   - Check database: `addons_detail` should contain JSON like:
     ```json
     [{"type":"topping","name":"Crushed Oreos","price":15},{"type":"sauce","name":"Chocolate","price":20}]
     ```

### 4. Verify Database Query

Run this query to check existing data:

```sql
SELECT 
    transaction_item_id,
    item_name,
    addons_detail,
    CASE 
        WHEN addons_detail = 'NONE' THEN 'No Add-ons'
        WHEN addons_detail IS NULL THEN 'NULL (needs update)'
        ELSE 'Has Add-ons'
    END as status
FROM transaction_items
ORDER BY transaction_item_id DESC
LIMIT 10;
```

## How It Works

### When Creating Orders (save_order.php)

```php
// If no add-ons selected
$addonsDetail = 'NONE';

// If add-ons selected
$addonsDetail = json_encode([
    ['type' => 'topping', 'name' => 'Crushed Oreos', 'price' => 15],
    ['type' => 'sauce', 'name' => 'Chocolate', 'price' => 20]
]);
```

### When Reading Orders (menu_helpers.php)

```php
// Check if addons_detail is not "NONE" before parsing JSON
if ($result['addons_detail'] && $result['addons_detail'] !== 'NONE') {
    $addons = json_decode($result['addons_detail'], true);
    // Process add-ons...
}
```

## Benefits

1. ✅ No more NULL values cluttering the database
2. ✅ Easy to identify orders without add-ons (just check for "NONE")
3. ✅ Proper JSON structure for orders with add-ons
4. ✅ Better data consistency and reporting
5. ✅ Prevents JSON parsing errors on NULL values

## Troubleshooting

**If you still see NULL values after migration:**
```sql
UPDATE transaction_items 
SET addons_detail = 'NONE' 
WHERE addons_detail IS NULL OR addons_detail = '';
```

**If orders fail to save:**
- Check that the column exists: `DESCRIBE transaction_items;`
- Check for any database errors in PHP error logs
- Verify the migration ran successfully

**If add-ons don't display correctly:**
- Check that `getTransactionItemCustomizations()` is being used
- Verify the JSON structure in the database
- Check browser console for JavaScript errors
