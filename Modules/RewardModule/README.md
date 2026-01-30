# Reward Management Module - Documentation

## Overview

The Reward Management Module allows administrators to configure reward points for service variants. When customers place bookings with specific service variants that have reward configurations, they automatically earn reward points that are added to their `loyalty_point` balance. The system includes a minimum order amount threshold to ensure rewards are only given for qualifying orders.

---

## Table of Contents

1. [Features](#features)
2. [Database Structure](#database-structure)
3. [Module Structure](#module-structure)
4. [Admin Interface](#admin-interface)
5. [Configuration](#configuration)
6. [Usage Guide](#usage-guide)
7. [Integration Points](#integration-points)
8. [API Reference](#api-reference)

---

## Features

✅ **Service Variant Based Rewards**
- Configure reward points for specific service variants (not sub categories)
- Each variant shows provider name for easy identification
- Support for multiple variants in bulk configuration

✅ **Minimum Order Amount**
- Set a minimum order amount threshold
- Rewards are only added if the order amount exceeds this threshold
- Prevents abuse and ensures meaningful rewards

✅ **Usage Limits**
- Set maximum uses per configuration (0 = unlimited)
- Automatic tracking of current uses
- Reset usage counter option

✅ **Active/Inactive Status**
- Enable or disable reward configurations
- Filter by active status in admin panel

✅ **Usage History**
- Track all reward point awards
- Filter by user or service variant
- View booking associations

---

## Database Structure

### Table: `reward_point_configs`

Stores reward point configurations for service variants.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | Primary key |
| `sub_category_id` | UUID (FK, nullable) | Legacy field for backward compatibility |
| `service_variant_id` | UUID (FK, nullable) | Reference to `variations` table |
| `reward_points` | DECIMAL(24,3) | Points to award (default: 0.000) |
| `minimum_order_amount` | DECIMAL(24,3) | Minimum order amount required (default: 0.000) |
| `max_uses` | INTEGER | Maximum times this can be used (0 = unlimited, default: 0) |
| `current_uses` | INTEGER | Current usage count (default: 0) |
| `is_active` | BOOLEAN | Active status (default: true) |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

**Indexes:**
- `is_active`
- `service_variant_id`
- `[service_variant_id, is_active]` (composite)

**Foreign Keys:**
- `service_variant_id` → `variations.id` (CASCADE DELETE)
- `sub_category_id` → `categories.id` (CASCADE DELETE, nullable)

---

### Table: `reward_point_usages`

Tracks all reward point awards to users.

| Column | Type | Description |
|--------|------|-------------|
| `id` | UUID (PK) | Primary key |
| `user_id` | UUID (FK) | Reference to `users` table |
| `booking_id` | UUID (FK, nullable) | Reference to `bookings` table |
| `sub_category_id` | UUID (FK, nullable) | Legacy field |
| `service_variant_id` | UUID (FK, nullable) | Reference to `variations` table |
| `reward_points` | DECIMAL(24,3) | Points awarded (default: 0.000) |
| `reward_config_id` | UUID (FK) | Reference to `reward_point_configs` table |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Update timestamp |

**Indexes:**
- `user_id`
- `booking_id`
- `sub_category_id`
- `service_variant_id`
- `reward_config_id`
- `[user_id, booking_id]` (composite)

**Foreign Keys:**
- `user_id` → `users.id` (CASCADE DELETE)
- `booking_id` → `bookings.id` (NULL ON DELETE)
- `service_variant_id` → `variations.id` (CASCADE DELETE)
- `sub_category_id` → `categories.id` (CASCADE DELETE, nullable)
- `reward_config_id` → `reward_point_configs.id` (CASCADE DELETE)

---

## Module Structure

```
Modules/RewardModule/
├── Database/
│   ├── Migrations/
│   │   ├── 2026_01_28_000001_create_reward_point_configs_table.php
│   │   ├── 2026_01_28_000002_create_reward_point_usages_table.php
│   │   ├── 2026_01_28_000003_update_reward_point_configs_for_variants.php
│   │   └── 2026_01_28_000004_update_reward_point_usages_for_variants.php
│   └── Seeders/
│       └── RewardModuleDatabaseSeeder.php
├── Entities/
│   ├── RewardPointConfig.php
│   └── RewardPointUsage.php
├── Http/
│   └── Controllers/
│       └── Web/
│           └── Admin/
│               └── RewardPointConfigController.php
├── Resources/
│   └── views/
│       └── admin/
│           ├── config/
│           │   ├── list.blade.php
│           │   ├── create.blade.php
│           │   └── edit.blade.php
│           └── usage/
│               └── list.blade.php
├── Routes/
│   └── web.php
├── Providers/
│   ├── RewardModuleServiceProvider.php
│   └── RouteServiceProvider.php
├── Config/
│   └── config.php
├── composer.json
├── module.json
└── README.md
```

---

## Admin Interface

### Menu Location

The Reward Management section is located in the admin sidebar under a dedicated category:

**Navigation Path:** `Admin Panel → Reward Management`

**Menu Items:**
1. **Reward Point Configurations** - View all configurations
2. **Configure Reward Points** - Create/update configurations
3. **Usage History** - View reward point awards history

---

### Pages

#### 1. Reward Point Configurations List

**Route:** `GET /admin/reward-point/config/list`

**Features:**
- Paginated list of all reward configurations
- Filter by active/inactive status
- Search by variant name or provider name
- Display columns:
  - Service Variant (with service name)
  - Provider Name
  - Reward Points
  - Minimum Order Amount
  - Max Uses / Current Uses / Remaining
  - Status (Active/Inactive)
  - Actions (Edit/Delete)

**Query Parameters:**
- `search` - Search term (variant or provider name)
- `is_active` - Filter: `all`, `active`, `inactive`

---

#### 2. Configure Reward Points (Create/Bulk Update)

**Route:** `GET /admin/reward-point/config/create`  
**Submit Route:** `POST /admin/reward-point/config/store`

**Features:**
- Multi-select dropdown for service variants
- Each option displays:
  - Variant name
  - Service name
  - Provider name (company name)
- Form fields:
  - **Service Variants** (required, multi-select)
  - **Reward Points** (required, decimal, min: 0)
  - **Minimum Order Amount** (required, decimal, min: 0)
  - **Max Uses** (required, integer, min: 0, 0 = unlimited)
  - **Active** (checkbox, default: checked)

**Behavior:**
- Can select multiple variants at once
- Creates new configs or updates existing ones
- Bulk operation in a single transaction

**Validation:**
- At least one service variant must be selected
- All service variant IDs must exist in `variations` table
- Reward points and minimum order amount must be >= 0
- Max uses must be >= 0

---

#### 3. Edit Reward Point Configuration

**Route:** `GET /admin/reward-point/config/edit/{id}`  
**Update Route:** `PUT /admin/reward-point/config/update/{id}`

**Features:**
- Displays service variant, service, and provider information (read-only)
- Editable fields:
  - **Reward Points**
  - **Minimum Order Amount**
  - **Max Uses**
  - **Active** status
  - **Reset Current Uses** (checkbox to reset counter to 0)

**Validation:**
- Same as create form
- Config must exist

---

#### 4. Delete Configuration

**Route:** `DELETE /admin/reward-point/config/delete/{id}`

**Features:**
- Soft delete (if model uses SoftDeletes)
- Confirmation dialog before deletion
- All related usage records remain (for audit trail)

---

#### 5. Usage History

**Route:** `GET /admin/reward-point/usage`

**Features:**
- Paginated list of all reward point awards
- Filter by:
  - User ID
  - Sub Category ID (legacy)
- Display columns:
  - User
  - Service Variant (with service name)
  - Provider Name
  - Reward Points
  - Booking ID
  - Date

**Query Parameters:**
- `user_id` - Filter by user UUID
- `sub_category_id` - Filter by sub category UUID (legacy)

---

## Configuration

### Model Relationships

#### RewardPointConfig

```php
// Relationships
serviceVariant()  // BelongsTo Variation
subCategory()     // BelongsTo Category (legacy)
usages()          // HasMany RewardPointUsage

// Scopes
active()          // Where is_active = true

// Accessors
remaining_uses    // Calculated: max_uses - current_uses (null if unlimited)
```

#### RewardPointUsage

```php
// Relationships
user()            // BelongsTo User
booking()         // BelongsTo Booking
serviceVariant()  // BelongsTo Variation
subCategory()     // BelongsTo Category (legacy)
rewardConfig()    // BelongsTo RewardPointConfig
```

---

## Usage Guide

### For Administrators

#### Creating Reward Configurations

1. Navigate to **Reward Management → Configure Reward Points**
2. Select one or more service variants from the dropdown
   - Each option shows: `Variant Name - Service Name (Provider: Company Name)`
3. Enter:
   - **Reward Points**: Points to award (e.g., 50.000)
   - **Minimum Order Amount**: Minimum order value required (e.g., 100.00)
   - **Max Uses**: Maximum times this can be used (0 = unlimited)
   - **Active**: Check to enable immediately
4. Click **Save**

#### Editing a Configuration

1. Go to **Reward Management → Reward Point Configurations**
2. Click the **Edit** icon for the desired configuration
3. Modify the fields as needed
4. Check **Reset Current Uses** if you want to reset the usage counter
5. Click **Update**

#### Viewing Usage History

1. Navigate to **Reward Management → Usage History**
2. Optionally filter by:
   - User ID (paste UUID)
   - Sub Category ID (legacy)
3. View all reward point awards with details

---

### For Developers

#### Adding Reward Points on Booking Completion

**Location:** `Modules/BookingModule/Http/Controllers/.../BookingController.php`

**Example Implementation:**

```php
use Modules\RewardModule\Entities\RewardPointConfig;
use Modules\RewardModule\Entities\RewardPointUsage;
use Illuminate\Support\Facades\DB;

// After booking is created successfully
DB::beginTransaction();
try {
    foreach ($booking->details as $detail) {
        // Get the service variant ID from booking detail
        $serviceVariantId = $detail->service_variant_id ?? null;
        
        if ($serviceVariantId) {
            // Find active reward config for this variant
            $rewardConfig = RewardPointConfig::where('service_variant_id', $serviceVariantId)
                ->where('is_active', true)
                ->first();
            
            if ($rewardConfig) {
                // Check minimum order amount
                if ($booking->total_booking_amount >= $rewardConfig->minimum_order_amount) {
                    // Check max uses (if not unlimited)
                    if ($rewardConfig->max_uses == 0 || 
                        $rewardConfig->current_uses < $rewardConfig->max_uses) {
                        
                        // Add points to user
                        $user = $booking->customer;
                        $user->loyalty_point += $rewardConfig->reward_points;
                        $user->save();
                        
                        // Create usage record
                        RewardPointUsage::create([
                            'user_id' => $user->id,
                            'booking_id' => $booking->id,
                            'service_variant_id' => $serviceVariantId,
                            'reward_points' => $rewardConfig->reward_points,
                            'reward_config_id' => $rewardConfig->id,
                        ]);
                        
                        // Increment usage counter
                        $rewardConfig->current_uses++;
                        $rewardConfig->save();
                    }
                }
            }
        }
    }
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Log error but don't fail the booking
    \Log::error('Reward point processing failed: ' . $e->getMessage());
}
```

#### Displaying Potential Rewards in Cart API

**Location:** `Modules/CartModule/Http/Controllers/.../CartController.php`

**Example Implementation:**

```php
use Modules\RewardModule\Entities\RewardPointConfig;

// In getCartItems() method
$rewardDetails = [];
$totalPotentialRewards = 0;

foreach ($cartItems as $item) {
    if ($item->service_variant_id) {
        $rewardConfig = RewardPointConfig::where('service_variant_id', $item->service_variant_id)
            ->where('is_active', true)
            ->first();
        
        if ($rewardConfig) {
            // Check if order amount meets minimum
            if ($cartTotal >= $rewardConfig->minimum_order_amount) {
                $rewardDetails[] = [
                    'service_variant_id' => $item->service_variant_id,
                    'service_variant_name' => $item->variant->variant ?? 'N/A',
                    'reward_points' => $rewardConfig->reward_points,
                    'minimum_order_amount' => $rewardConfig->minimum_order_amount,
                    'max_uses' => $rewardConfig->max_uses,
                    'current_uses' => $rewardConfig->current_uses,
                    'remaining_uses' => $rewardConfig->max_uses == 0 
                        ? null 
                        : max(0, $rewardConfig->max_uses - $rewardConfig->current_uses),
                ];
                $totalPotentialRewards += $rewardConfig->reward_points;
            }
        }
    }
}

// Add to response
$response['data']['reward_points'] = [
    'total_potential_reward_points' => $totalPotentialRewards,
    'reward_details' => $rewardDetails,
    'message' => "You will earn {$totalPotentialRewards} reward points on booking completion",
];
```

---

## Integration Points

### 1. Booking Module

**When:** After booking is successfully created and paid

**Action:** 
- Check for active reward configs for service variants in booking
- Validate minimum order amount
- Check max uses limit
- Add reward points to user's `loyalty_point`
- Create `RewardPointUsage` record
- Increment `current_uses` counter

**Files to Modify:**
- `Modules/BookingModule/Http/Controllers/.../BookingController.php`
- `Modules/BookingModule/Http/Traits/BookingTrait.php` (if reward logic is in trait)

---

### 2. Cart Module

**When:** When fetching cart items (API)

**Action:**
- Fetch active reward configs for service variants in cart
- Calculate total potential reward points
- Include reward information in cart response

**Files to Modify:**
- `Modules/CartModule/Http/Controllers/Api/V1/Customer/CartController.php`

---

## API Reference

### Admin Routes

All routes require `admin` middleware.

| Method | Route | Controller Method | Description |
|--------|-------|-------------------|-------------|
| GET | `/admin/reward-point/config/list` | `index()` | List all configurations |
| GET | `/admin/reward-point/config/create` | `create()` | Show create form |
| POST | `/admin/reward-point/config/store` | `store()` | Create/update configs |
| GET | `/admin/reward-point/config/edit/{id}` | `edit()` | Show edit form |
| PUT | `/admin/reward-point/config/update/{id}` | `update()` | Update config |
| DELETE | `/admin/reward-point/config/delete/{id}` | `destroy()` | Delete config |
| GET | `/admin/reward-point/usage` | `usage()` | View usage history |

---

## Database Migrations

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Run only RewardModule migrations
php artisan migrate --path=Modules/RewardModule/Database/Migrations
```

### Migration Files

1. **2026_01_28_000001_create_reward_point_configs_table.php**
   - Creates `reward_point_configs` table
   - Initial structure with `sub_category_id`

2. **2026_01_28_000002_create_reward_point_usages_table.php**
   - Creates `reward_point_usages` table
   - Tracks all reward point awards

3. **2026_01_28_000003_update_reward_point_configs_for_variants.php**
   - Adds `service_variant_id` column
   - Adds `minimum_order_amount` column
   - Makes `sub_category_id` nullable

4. **2026_01_28_000004_update_reward_point_usages_for_variants.php**
   - Adds `service_variant_id` column to usages table
   - Makes `sub_category_id` nullable

---

## Important Notes

### Backward Compatibility

- The `sub_category_id` field is kept as nullable for backward compatibility
- Existing code using sub categories will continue to work
- New configurations should use `service_variant_id`

### Minimum Order Amount

- Reward points are **only** added if the order amount is **greater than or equal to** the minimum order amount
- This prevents abuse and ensures meaningful rewards
- Set to `0.000` to disable this check

### Max Uses

- `max_uses = 0` means **unlimited**
- When `max_uses > 0`, the system checks `current_uses < max_uses` before awarding points
- Use the "Reset Current Uses" option in edit form to reset the counter

### Transaction Safety

- All reward point operations should be wrapped in database transactions
- If reward processing fails, the booking should still succeed (log the error)
- This ensures users aren't blocked from completing bookings

---

## Troubleshooting

### Issue: "Column not found: name in providers"

**Solution:** The Provider model uses `company_name`, not `name`. All references have been updated to use `company_name`.

### Issue: "Call to undefined method Variation::service()"

**Solution:** The `service()` relationship has been added to the Variation model. Clear cache if needed:
```bash
php artisan config:clear
php artisan route:clear
```

### Issue: No service variants showing in dropdown

**Check:**
- Variations must have both `provider_id` and `service_id` set
- Both provider and service must exist
- Use `whereHas('provider')->whereHas('service')` in query

---

## Future Enhancements

Potential improvements for future versions:

1. **Time-based Rewards**
   - Start/end dates for reward configurations
   - Seasonal or promotional reward campaigns

2. **Tiered Rewards**
   - Different reward amounts based on order value tiers
   - Percentage-based rewards

3. **Provider-specific Settings**
   - Allow providers to configure their own reward points
   - Provider dashboard integration

4. **Analytics Dashboard**
   - Reward point distribution charts
   - Most rewarded variants
   - User engagement metrics

5. **Notification System**
   - Notify users when they earn reward points
   - Email/SMS notifications

---

## Support

For issues or questions:
1. Check this documentation first
2. Review the code comments in controller and models
3. Check Laravel logs: `storage/logs/laravel.log`

---

**Last Updated:** January 28, 2026  
**Module Version:** 1.0.0
