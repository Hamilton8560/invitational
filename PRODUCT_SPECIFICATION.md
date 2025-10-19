# Bryant Invitational - Complete Product Specification

**Version:** 1.0
**Last Updated:** October 16, 2025
**Event Launch Date:** January 17, 2026
**Location:** Bryant Sports Complex, Santa Tecla, El Salvador

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [System Architecture](#system-architecture)
3. [Core Features & User Flows](#core-features--user-flows)
4. [User Roles & Permissions](#user-roles--permissions)
5. [Business Logic & Rules](#business-logic--rules)
6. [Admin Panel Features](#admin-panel-features)
7. [API & Integration Points](#api--integration-points)
8. [Phase 1 MVP Scope](#phase-1-mvp-scope)
9. [Future Enhancements](#future-enhancements)
10. [Technical Implementation Notes](#technical-implementation-notes)

---

## Executive Summary

### Product Overview

The Bryant Invitational is a **multi-sport tournament management platform** designed to host simultaneous sports tournaments while maximizing space utilization and revenue through dynamic registration, advertising sales, and flexible scheduling.

### Business Model

**Revenue Streams:**

1. **Entry Fees**
   - Individual sports (Pickleball Singles): $125/person
   - Team sports (Futsal, Volleyball, Cricket, etc.): $500/team
   - Spectator tickets: $29/person (primarily for youth sports parents)

2. **Advertising Sales**
   - Court partition branding + booth packages: $5,000 each
   - Banner locations: Variable pricing
   - Website advertisements: Variable pricing based on placement

3. **Charitable Component**
   - Partnership with domestic violence charity
   - Enhances sponsor appeal and goodwill

### Target Market

- **Primary:** Adult recreational athletes (ages 18-55)
- **Secondary:** Youth sports teams (U10, U12, U14, U16)
- **Tertiary:** Corporate sponsors and local businesses
- **Scale:** 5,000+ spectators, 130+ courts, 100+ teams across multiple sports

### Key Success Metrics

- **Registration Goal:** Fill 60-100 pickleball courts + all team sports
- **Advertising Revenue:** 15 booths × $5,000 = $75,000
- **Time to Launch:** ~2 months (December 16 registration opens)
- **Geographic Expansion:** Replicate in NYC by April 2026

---

## System Architecture

### Technology Stack

- **Backend Framework:** Laravel 12
- **Admin Panel:** Filament 3.3
- **Permissions:** Spatie Laravel Permission
- **Payment Processing:** Paddle (subscription billing platform)
- **Database:** MySQL/SQLite
- **Authentication:** Laravel Fortify (2FA enabled)
- **Streaming:** YouTube API integration ($50/month bandwidth)
- **QR Codes:** Native PHP QR code generation
- **Email/SMS:** Laravel Notifications (provider TBD)

### Database Schema Overview

**Core Entity Relationships:**

```
Venue
  ├── Events
      ├── EventTimeSlots
      ├── EventSports
      │   ├── AgeGroups
      │   ├── SkillLevels
      │   └── Divisions
      │       ├── Products (team/player entries)
      │       ├── Teams
      │       │   └── TeamPlayers
      │       └── IndividualPlayers
      ├── Products (spectator, booth, banner, website ads)
      ├── Sales
      │   └── Refunds
      ├── Booths
      ├── Banners
      └── WebsiteAds
```

**Key Tables (19 total):**

1. **Venues** - Physical locations
2. **Events** - Tournament instances
3. **Sports** - Sport types (Pickleball, Futsal, etc.)
4. **EventTimeSlots** - Time blocks for space reuse
5. **EventSports** - Sports assigned to specific time slots
6. **AgeGroups** - Age brackets per sport
7. **SkillLevels** - Skill ratings (e.g., Pickleball 3.0-3.5)
8. **Divisions** - Combinations of age + skill
9. **Products** - Everything for sale (unified inventory)
10. **Users** - All system users (multi-role)
11. **Teams** - Team registrations
12. **TeamPlayers** - Roster members
13. **IndividualPlayers** - Singles registrations
14. **Booths** - Vendor booth purchasesvery
15. **Banners** - Banner advertising purchases
16. **WebsiteAds** - Digital ad placements
17. **Sales** - All transactions (Paddle integration)
18. **Refunds** - Refund requests and processing
19. **EventTemplates** - Cloning successful events

**Permission Tables (from Spatie):**

- `roles` - Role definitions
- `permissions` - Permission definitions
- `role_has_permissions` - Role → Permission mapping
- `model_has_roles` - User → Role assignments
- `model_has_permissions` - Direct user permissions

---

## Core Features & User Flows

### 1. Event Creation & Management

**Admin Workflow:**

1. **Create Venue** (one-time setup)
   - Define total sports space (100,000 sq ft)
   - Define spectator space (20,000 sq ft)
   - Set banner spots (20)
   - Set booth spots (15)

2. **Create Event**
   - Name: "The Bryant Invitational"
   - Dates: January 17-19, 2026
   - Venue: Bryant Sports Complex
   - Status: Draft → Open → Closed → Completed
   - Refund cutoff: 2 weeks before start (January 3, 2026)

3. **Define Time Slots**
   - Morning: 7am-12pm (100,000 sq ft available)
   - Afternoon: 12pm-4pm (100,000 sq ft available)
   - Evening: 5pm-10pm (100,000 sq ft available)

4. **Assign Sports to Time Slots**
   - Pickleball Singles: 7am-12pm (60,000 sq ft, 100 courts)
   - Futsal + Pickleball Doubles: 12pm-4pm (50,000 sq ft)
   - Cricket + Lacrosse: 5pm-10pm (80,000 sq ft)

5. **Create Divisions per Sport**
   - Pickleball Singles: "3.0-3.5", "4.0-4.5", "Open"
   - Futsal: "U10 Boys", "U12 Boys", "Adult Open"
   - Cricket: "Adult Open"

6. **Generate Products**
   - Team Entry: "Futsal U12 Team" - $500
   - Player Entry: "Pickleball Singles 3.0-3.5" - $125
   - Spectator: "Youth Parent Pass" - $29
   - Booth: "Court Partition + Booth Package" - $5,000
   - Banner: "Entrance Banner" - $2,000
   - Website Ad: "Header Banner" - $1,000

### 2. Space Allocation Algorithm

**Calculation Logic:**

```php
// For each time slot:
$availableSpace = $timeSlot->available_space_sqft;

// Subtract allocated sports:
$usedSpace = EventSport::where('time_slot_id', $timeSlot->id)
    ->sum('space_required_sqft');

$remainingSpace = $availableSpace - $usedSpace;

// Check if new sport fits:
if ($newSport->space_required_sqft <= $remainingSpace) {
    // Allow addition
} else {
    // Reject or suggest different time slot
}
```

**Overbooking Strategy:**
- Advertise 100 pickleball courts
- Expect ~60 to fill
- System allows flexible allocation based on actual registrations
- Admin can adjust divisions/groupings until December 16 finalization

### 3. Registration System

#### Team Registration Flow

**User Journey:**

1. **Browse Events** → See "The Bryant Invitational"
2. **Select Sport** → "Futsal U12 Boys"
3. **View Product** → "$500 per team, max 5 players"
4. **Create Account / Login**
5. **Register Team**
   - Enter team name
   - Become "team owner"
6. **Add Players** (can be done immediately or later)
   - Invite by email
   - Player creates account
   - Player fills emergency contact info
   - Player signs digital waiver
7. **Checkout via Paddle**
   - Card, PayPal, or other Paddle methods
8. **Receive Confirmation Email + QR Code**

**Database Flow:**

```
Sale Created (status: pending)
  → Paddle webhook confirms payment
    → Sale Updated (status: completed)
      → Team Created (linked to sale)
        → Product.current_quantity incremented
          → TeamPlayers Added
            → Team.current_players incremented
```

#### Individual Player Registration Flow

**User Journey:**

1. **Browse Events** → Pickleball Singles
2. **Select Division** → "3.0-3.5 Skill Level"
3. **View Product** → "$125 per person"
4. **Create Account / Login**
5. **Register as Individual**
   - Enter skill rating (3.2)
   - Emergency contact info
   - Sign waiver
6. **Checkout via Paddle**
7. **Receive Confirmation Email + QR Code**

**Database Flow:**

```
Sale Created (status: pending)
  → Paddle webhook confirms payment
    → Sale Updated (status: completed)
      → IndividualPlayer Created
        → Product.current_quantity incremented
```

#### Spectator Registration Flow

**User Journey:**

1. **Select "Spectator Tickets"**
2. **Choose Quantity** (e.g., 2 parents for wiffle ball)
3. **Enter Names** (if required)
4. **Checkout**
5. **Receive QR Codes for Entry**

### 4. Advertising Sales

#### Booth Package Flow

**Vendor Journey:**

1. **Browse "Advertising Opportunities"**
2. **Select Court Partition + Booth Package**
   - See available locations (e.g., "Pickleball Court 5")
   - View specs: 10x10 tent space, partition branding
3. **Enter Company Info**
   - Company name
   - Contact details
4. **Upload Branding Assets** (can be done later)
5. **Checkout via Paddle ($5,000)**
6. **Receive Confirmation + Setup Instructions**

**Admin Responsibilities:**
- Assign booth number after purchase
- Coordinate setup/breakdown with staff
- Ensure partition graphics are printed and installed

#### Banner & Website Ad Flow

**Similar to booths:**
- Fixed inventory (20 banner spots)
- Itemized locations (Entrance, Court Side A, etc.)
- Admin assigns location post-purchase
- Vendors upload creative assets via admin panel

### 5. Payment Processing & Refunds

#### Paddle Integration

**Webhook Events:**

- `transaction.completed` → Update sale status to "completed"
- `transaction.failed` → Update sale status to "failed", notify user
- `subscription.created` → Handle recurring ads (if applicable)
- `refund.created` → Update refund status to "completed"

**Sale Record:**

```php
Sale {
  event_id: 1,
  user_id: 42,
  product_id: 15,
  quantity: 1,
  unit_price: 125.00,
  total_amount: 125.00,
  status: 'completed',
  paddle_transaction_id: 'txn_abc123',
  payment_method: 'card',
  purchased_at: '2025-12-20 14:30:00',
  team_id: null,
  individual_player_id: 89,
}
```

#### Refund Policy & Workflow

**Business Rule:**
- No refunds within 2 weeks of event start
- Enforced by `events.refund_cutoff_date` (January 3, 2026)

**User Refund Request:**

1. User logs in → "My Registrations"
2. Click "Request Refund" on eligible purchase
3. Fill out reason (optional)
4. Submit request → Creates `Refund` record (status: pending)

**Admin Refund Processing:**

1. Admin reviews refund in Filament panel
2. Checks eligibility (before cutoff date)
3. Approves or rejects
4. If approved:
   - Paddle API processes refund
   - Sale status → "refunded"
   - Product.current_quantity decremented
   - Team/player record soft-deleted or marked inactive
   - User notified via email

**Database Flow:**

```
Refund Created (status: pending, requested_by: user_id)
  → Admin Reviews
    → If Approved:
        → Paddle Refund API called
          → Refund Updated (status: completed, processed_by: admin_id)
            → Sale Updated (status: refunded)
              → Product.current_quantity -= 1
                → Team/IndividualPlayer soft-deleted
```

### 6. Check-In System

**QR Code Generation:**

- Upon sale completion, generate unique QR code
- QR payload: `{"sale_id": 123, "user_id": 42, "product_type": "team", "team_id": 15}`
- Encoded as URL: `https://bryantinvitational.com/checkin/{encrypted_data}`

**On-Site Check-In:**

1. **Staff iPad Setup**
   - Admin provides iPads with scanning app
   - App loaded with event check-in interface

2. **Player Arrives**
   - Shows QR code (email or phone)
   - Staff scans code

3. **Validation**
   - System decrypts payload
   - Verifies sale is "completed"
   - Confirms team/player is active (not refunded)
   - Marks check-in timestamp

4. **Team Area Assignment**
   - System assigns bench/area number
   - Prints wristbands or provides digital badge

**Fallback:**
- Manual lookup by name/email if QR fails
- Staff can check in via search interface

### 7. Streaming Integration

**Setup:**

- YouTube Live API integration
- $50/month bandwidth (unlimited streaming)
- Admin creates YouTube events per court/field

**Privacy Controls:**

- **Adult Tournaments:** Streamed worldwide (public)
- **Youth Tournaments:** Streaming disabled (protect minors)

**Implementation:**

- Each court has assigned camera/streaming device
- Streams embed on event website + YouTube channel
- Real-time scoreboard overlays (future enhancement)

---

## User Roles & Permissions

### Role Definitions

| Role | Description | Count (Est.) |
|------|-------------|--------------|
| **Admin** | Event organizers (Brynley, David) | 2-5 |
| **Team Owner** | Purchased team entry, manages roster | 100+ |
| **Player** | Individual or team member | 1,000+ |
| **Vendor** | Purchased advertising (booth/banner) | 15-30 |
| **Spectator** | Purchased spectator ticket | 5,000+ |

### Permission Matrix

| Action | Admin | Team Owner | Player | Vendor | Spectator |
|--------|-------|------------|--------|--------|-----------|
| **Events** |
| View events | ✓ | ✓ | ✓ | ✓ | ✓ |
| Create event | ✓ | ✗ | ✗ | ✗ | ✗ |
| Edit event | ✓ | ✗ | ✗ | ✗ | ✗ |
| Delete event | ✓ | ✗ | ✗ | ✗ | ✗ |
| **Products** |
| View products | ✓ | ✓ | ✓ | ✓ | ✓ |
| Create product | ✓ | ✗ | ✗ | ✗ | ✗ |
| Edit product | ✓ | ✗ | ✗ | ✗ | ✗ |
| Delete product | ✓ | ✗ | ✗ | ✗ | ✗ |
| Purchase product | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Teams** |
| View own teams | ✓ | ✓ | ✓ | ✗ | ✗ |
| View all teams | ✓ | ✗ | ✗ | ✗ | ✗ |
| Create team | ✓ | ✓* | ✗ | ✗ | ✗ |
| Edit own team | ✓ | ✓ | ✗ | ✗ | ✗ |
| Edit any team | ✓ | ✗ | ✗ | ✗ | ✗ |
| Add players to own team | ✓ | ✓ | ✗ | ✗ | ✗ |
| Remove players from own team | ✓ | ✓ | ✗ | ✗ | ✗ |
| **Sales** |
| View own sales | ✓ | ✓ | ✓ | ✓ | ✓ |
| View all sales | ✓ | ✗ | ✗ | ✗ | ✗ |
| Create sale | ✓ | ✓ | ✓ | ✓ | ✓ |
| Process refund | ✓ | ✗ | ✗ | ✗ | ✗ |
| Request refund | ✓ | ✓ | ✓ | ✓ | ✓ |
| **Advertising** |
| View booths | ✓ | ✓ | ✓ | ✓ | ✓ |
| Purchase booth | ✓ | ✗ | ✗ | ✓ | ✗ |
| Edit own booth | ✓ | ✗ | ✗ | ✓ | ✗ |
| Edit any booth | ✓ | ✗ | ✗ | ✗ | ✗ |
| Upload booth assets | ✓ | ✗ | ✗ | ✓ | ✗ |
| **Users** |
| View all users | ✓ | ✗ | ✗ | ✗ | ✗ |
| Edit any user | ✓ | ✗ | ✗ | ✗ | ✗ |
| Delete user | ✓ | ✗ | ✗ | ✗ | ✗ |
| Edit own profile | ✓ | ✓ | ✓ | ✓ | ✓ |

*\*Team Owner can only create teams after purchasing team product*

### Spatie Permission Implementation

**Permission Naming Convention:**

```
{action}_{resource}
{action}_any_{resource}
{action}_own_{resource}
```

**Examples:**

- `view_event`
- `view_any_team`
- `create_product`
- `update_own_team`
- `delete_any_user`
- `process_refund`

**Role Assignments:**

```php
// Admin gets all permissions
$admin = Role::create(['name' => 'admin']);
$admin->givePermissionTo(Permission::all());

// Team Owner gets limited permissions
$teamOwner = Role::create(['name' => 'team_owner']);
$teamOwner->givePermissionTo([
    'view_event',
    'view_any_product',
    'purchase_product',
    'view_own_team',
    'update_own_team',
    'add_player_to_own_team',
    'view_own_sale',
    'request_refund',
]);

// Player gets minimal permissions
$player = Role::create(['name' => 'player']);
$player->givePermissionTo([
    'view_event',
    'view_any_product',
    'purchase_product',
    'view_own_sale',
    'request_refund',
    'update_own_profile',
]);

// Vendor gets advertising permissions
$vendor = Role::create(['name' => 'vendor']);
$vendor->givePermissionTo([
    'view_event',
    'view_any_product',
    'purchase_product',
    'view_own_booth',
    'update_own_booth',
    'upload_booth_assets',
    'view_own_sale',
]);
```

---

## Business Logic & Rules

### 1. Space Management

**Constraint:** Total sports space per time slot cannot exceed venue capacity

**Validation Rule:**

```php
// SpaceAvailabilityRule.php
public function passes($attribute, $value)
{
    $timeSlot = EventTimeSlot::find(request('time_slot_id'));
    $existingSpace = EventSport::where('time_slot_id', $timeSlot->id)
        ->sum('space_required_sqft');

    $newSpace = request('space_required_sqft');

    return ($existingSpace + $newSpace) <= $timeSlot->available_space_sqft;
}
```

**Admin Interface:**

- Show real-time space usage per time slot
- Visual indicator: Green (plenty of space) → Yellow (70% full) → Red (at capacity)
- Suggest alternative time slots if current is full

### 2. Dynamic Pricing & Inventory

**Product Quantity Tracking:**

```php
// On sale creation:
Product::where('id', $productId)->increment('current_quantity');

// On refund approval:
Product::where('id', $productId)->decrement('current_quantity');

// Check availability before purchase:
if ($product->current_quantity >= $product->max_quantity) {
    abort(422, 'Product sold out');
}
```

**Inventory Display:**

- Show "X spots remaining" on product listing
- Hide product when sold out (or show "Sold Out" badge)
- Admin can override max_quantity if needed

### 3. Refund Cutoff Enforcement

**Validation:**

```php
// RefundCutoffRule.php
public function passes($attribute, $value)
{
    $sale = Sale::find($value);
    $event = $sale->event;

    return now()->lte($event->refund_cutoff_date);
}

public function message()
{
    return 'Refunds are no longer available within 2 weeks of the event.';
}
```

**User Interface:**

- Show "Refundable until January 3, 2026" on registration confirmation
- Disable "Request Refund" button after cutoff
- Display clear messaging about policy during checkout

### 4. Tournament Scheduling Flexibility

**Challenge:** Exact team counts unknown until registration closes

**Solution:**

1. **Initial Setup (November - December 16)**
   - Create divisions with estimated capacities
   - Allow open registration

2. **Finalization Period (December 16 - January 16)**
   - Lock registrations
   - Admin reviews actual counts
   - Adjust divisions/groupings:
     - Merge under-enrolled divisions
     - Split over-enrolled divisions
     - Reassign court allocations

3. **Final Scheduling (January 16)**
   - Generate brackets/round-robin schedules
   - Assign specific court numbers and times
   - Notify participants of schedule

**Admin Tools Needed:**

- Division merging interface
- Court reassignment tool
- Schedule generator (possibly external tool integration)

### 5. Overbooking Strategy

**Pickleball Example:**

- **Advertised:** 100 courts available
- **Physical Capacity:** 100 courts (60,000 sq ft ÷ 600 sq ft per court)
- **Expected Fill Rate:** 60-70%
- **Strategy:** Accept registrations for all 100, knowing some won't fill

**Safeguards:**

- Monitor registration rates weekly
- Close divisions early if approaching true capacity
- Offer waitlist for popular divisions
- Admin can manually cap divisions before max is reached

### 6. Team Capacity Management

**Constraint:** Teams have max players (e.g., 5 for futsal)

**Validation:**

```php
// TeamCapacityRule.php
public function passes($attribute, $value)
{
    $team = Team::find(request('team_id'));

    return $team->current_players < $team->max_players;
}
```

**Auto-increment:**

```php
// TeamPlayerObserver.php
public function created(TeamPlayer $teamPlayer)
{
    $teamPlayer->team->increment('current_players');
}

public function deleted(TeamPlayer $teamPlayer)
{
    $teamPlayer->team->decrement('current_players');
}
```

---

## Admin Panel Features

### Filament Resources Needed

| Resource | Purpose | Key Actions |
|----------|---------|-------------|
| **EventResource** | Manage events | Create, edit, view sales, clone to template |
| **VenueResource** | Manage venues | CRUD operations |
| **SportResource** | Manage sports | CRUD operations |
| **EventTimeSlotResource** | Time block management | CRUD, view space usage |
| **EventSportResource** | Assign sports to slots | CRUD, validate space availability |
| **DivisionResource** | Manage divisions | CRUD, merge divisions |
| **ProductResource** | Manage products | CRUD, track inventory, adjust pricing |
| **TeamResource** | View/edit teams | View rosters, check-in status, reassign divisions |
| **IndividualPlayerResource** | View/edit players | View details, check-in status |
| **SaleResource** | View sales | Filter by status, export reports, process refunds |
| **RefundResource** | Process refunds | Approve/reject, trigger Paddle refunds |
| **BoothResource** | Manage booths | Assign numbers, view assets, contact vendors |
| **BannerResource** | Manage banners | Assign locations, view assets |
| **WebsiteAdResource** | Manage ads | Assign placements, view assets |
| **UserResource** | Manage users | CRUD, assign roles, view purchase history |
| **RoleResource** | Manage roles (Shield) | Assign permissions |

### Key Filament Customizations

#### 1. EventResource - Dashboard Widget

**Real-time Event Stats:**

```php
// EventStatsWidget.php
protected function getStats(): array
{
    $event = $this->getRecord();

    return [
        Stat::make('Total Sales', $event->sales()->sum('total_amount'))
            ->description('Revenue to date')
            ->color('success'),

        Stat::make('Registrations', $event->sales()->completed()->count())
            ->description('Teams + Individuals')
            ->color('primary'),

        Stat::make('Available Space', $this->getAvailableSpace($event))
            ->description('Square feet remaining')
            ->color($this->getSpaceColor($event)),

        Stat::make('Days Until Event', now()->diffInDays($event->start_date))
            ->description($event->start_date->format('M j, Y'))
            ->color('warning'),
    ];
}
```

#### 2. ProductResource - Inventory Tracking

**Custom Table Columns:**

```php
Tables\Columns\ProgressColumn::make('inventory')
    ->label('Inventory')
    ->getStateUsing(fn ($record) => [
        'current' => $record->current_quantity,
        'max' => $record->max_quantity ?? 999,
    ])
    ->formatStateUsing(fn ($state) =>
        "{$state['current']} / " . ($state['max'] == 999 ? '∞' : $state['max'])
    ),

Tables\Columns\BadgeColumn::make('status')
    ->getStateUsing(function ($record) {
        if ($record->max_quantity === null) return 'unlimited';
        $percent = ($record->current_quantity / $record->max_quantity) * 100;

        if ($percent >= 100) return 'sold_out';
        if ($percent >= 80) return 'low_stock';
        return 'available';
    })
    ->colors([
        'success' => 'available',
        'warning' => 'low_stock',
        'danger' => 'sold_out',
        'secondary' => 'unlimited',
    ]),
```

#### 3. SaleResource - Refund Processing

**Custom Action:**

```php
Tables\Actions\Action::make('process_refund')
    ->label('Process Refund')
    ->icon('heroicon-o-currency-dollar')
    ->requiresConfirmation()
    ->visible(fn ($record) => $record->status === 'completed')
    ->action(function ($record) {
        // Create refund request
        $refund = Refund::create([
            'sale_id' => $record->id,
            'amount' => $record->total_amount,
            'reason' => 'Admin-initiated refund',
            'status' => 'approved',
            'requested_by' => auth()->id(),
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Call Paddle API
        ProcessPaddleRefund::dispatch($refund);

        Notification::make()
            ->success()
            ->title('Refund initiated')
            ->send();
    }),
```

#### 4. EventSportResource - Space Validation

**Custom Form Validation:**

```php
Forms\Components\TextInput::make('space_required_sqft')
    ->numeric()
    ->required()
    ->reactive()
    ->afterStateUpdated(function ($state, $get, $set) {
        $timeSlotId = $get('time_slot_id');
        if (!$timeSlotId) return;

        $timeSlot = EventTimeSlot::find($timeSlotId);
        $usedSpace = EventSport::where('time_slot_id', $timeSlotId)
            ->sum('space_required_sqft');

        $available = $timeSlot->available_space_sqft - $usedSpace;

        if ($state > $available) {
            $set('space_required_sqft', $available);
            Notification::make()
                ->warning()
                ->title('Space adjusted to maximum available')
                ->body("Only {$available} sq ft remaining in this time slot")
                ->send();
        }
    }),

Forms\Components\Placeholder::make('space_remaining')
    ->label('Space Remaining in Time Slot')
    ->content(function ($get) {
        $timeSlotId = $get('time_slot_id');
        if (!$timeSlotId) return 'Select a time slot first';

        $timeSlot = EventTimeSlot::find($timeSlotId);
        $usedSpace = EventSport::where('time_slot_id', $timeSlotId)
            ->sum('space_required_sqft');

        $remaining = $timeSlot->available_space_sqft - $usedSpace;
        return number_format($remaining) . ' sq ft';
    }),
```

#### 5. TeamResource - Roster Management

**Relation Manager:**

```php
// TeamPlayersRelationManager.php
public function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('user.full_name'),
            Tables\Columns\TextColumn::make('jersey_number'),
            Tables\Columns\IconColumn::make('waiver_signed')
                ->boolean(),
            Tables\Columns\TextColumn::make('emergency_contact_name'),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make()
                ->disabled(fn ($livewire) =>
                    $livewire->ownerRecord->current_players >= $livewire->ownerRecord->max_players
                )
                ->disabledTooltip('Team is at full capacity'),
        ]);
}
```

### Permission Integration

**Filament Policy Registration:**

```php
// AuthServiceProvider.php
protected $policies = [
    Event::class => EventPolicy::class,
    Product::class => ProductPolicy::class,
    Team::class => TeamPolicy::class,
    Sale::class => SalePolicy::class,
    Refund::class => RefundPolicy::class,
    // ... etc
];

// EventPolicy.php
public function viewAny(User $user): bool
{
    return $user->can('view_any_event');
}

public function create(User $user): bool
{
    return $user->can('create_event');
}

public function update(User $user, Event $event): bool
{
    return $user->can('update_event');
}
```

**Shield Auto-generation:**

```bash
php artisan shield:generate --all
```

This creates permissions for all Filament resources automatically.

---

## API & Integration Points

### 1. Paddle Payment Integration

**Webhook Endpoints:**

```php
// routes/api.php
Route::post('/paddle/webhook', PaddleWebhookController::class)
    ->middleware('verify-paddle-signature');
```

**Webhook Handler:**

```php
// PaddleWebhookController.php
public function __invoke(Request $request)
{
    $eventType = $request->input('alert_name');

    match($eventType) {
        'transaction_completed' => $this->handleTransactionCompleted($request),
        'transaction_failed' => $this->handleTransactionFailed($request),
        'refund_completed' => $this->handleRefundCompleted($request),
        'subscription_created' => $this->handleSubscriptionCreated($request),
        default => Log::info("Unhandled Paddle event: {$eventType}"),
    };

    return response()->json(['status' => 'ok']);
}

protected function handleTransactionCompleted(Request $request)
{
    $transactionId = $request->input('transaction_id');

    $sale = Sale::where('paddle_transaction_id', $transactionId)->first();

    if (!$sale) {
        Log::error("Sale not found for transaction: {$transactionId}");
        return;
    }

    $sale->update([
        'status' => 'completed',
        'payment_method' => $request->input('payment_method'),
    ]);

    // Increment product quantity
    $sale->product->increment('current_quantity');

    // Create team/player/booth record based on product type
    $this->createPurchaseRecord($sale);

    // Send confirmation email
    SendPurchaseConfirmation::dispatch($sale);

    // Generate QR code
    GenerateQRCode::dispatch($sale);
}
```

**Paddle SDK Integration:**

```php
// config/services.php
'paddle' => [
    'vendor_id' => env('PADDLE_VENDOR_ID'),
    'api_key' => env('PADDLE_API_KEY'),
    'public_key' => env('PADDLE_PUBLIC_KEY'),
    'sandbox' => env('PADDLE_SANDBOX', true),
],

// Initiate Paddle checkout
$paddle = new \Paddle\PaddleClient(config('services.paddle.api_key'));

$checkout = $paddle->createCheckout([
    'product_id' => $product->paddle_product_id,
    'prices' => [$product->price],
    'customer_email' => $user->email,
    'passthrough' => json_encode([
        'sale_id' => $sale->id,
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]),
    'success_url' => route('checkout.success', $sale),
    'cancel_url' => route('checkout.cancel', $sale),
]);
```

### 2. QR Code Generation

**Library:** `simplesoftwareio/simple-qrcode`

```bash
composer require simplesoftwareio/simple-qrcode
```

**Implementation:**

```php
// GenerateQRCode.php (Job)
public function handle()
{
    $sale = $this->sale;

    $payload = encrypt([
        'sale_id' => $sale->id,
        'user_id' => $sale->user_id,
        'event_id' => $sale->event_id,
        'product_type' => $sale->product->type,
        'team_id' => $sale->team_id,
        'individual_player_id' => $sale->individual_player_id,
    ]);

    $url = route('checkin.scan', ['token' => $payload]);

    $qrCode = QrCode::format('png')
        ->size(300)
        ->generate($url);

    // Save to storage
    Storage::put("qrcodes/sale_{$sale->id}.png", $qrCode);

    // Attach to confirmation email
    $sale->user->notify(new PurchaseConfirmed($sale));
}
```

**Check-in Endpoint:**

```php
// routes/web.php
Route::get('/checkin/{token}', CheckinController::class)
    ->name('checkin.scan');

// CheckinController.php
public function __invoke($token)
{
    try {
        $payload = decrypt($token);
    } catch (DecryptException $e) {
        abort(403, 'Invalid QR code');
    }

    $sale = Sale::find($payload['sale_id']);

    if ($sale->status !== 'completed') {
        abort(422, 'Registration not completed or has been refunded');
    }

    // Record check-in
    Checkin::create([
        'sale_id' => $sale->id,
        'user_id' => $payload['user_id'],
        'checked_in_at' => now(),
        'checked_in_by' => auth()->id(), // Staff member
    ]);

    return view('checkin.success', compact('sale'));
}
```

### 3. YouTube Streaming Integration

**YouTube Data API Setup:**

```php
// config/services.php
'youtube' => [
    'api_key' => env('YOUTUBE_API_KEY'),
    'client_id' => env('YOUTUBE_CLIENT_ID'),
    'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
],
```

**Create Live Stream:**

```php
// YouTube API call (simplified)
$youtube = new \Google_Service_YouTube($client);

$broadcast = new \Google_Service_YouTube_LiveBroadcast();
$broadcast->setSnippet(new \Google_Service_YouTube_LiveBroadcastSnippet([
    'title' => 'Pickleball Court 5 - Bryant Invitational',
    'scheduledStartTime' => $event->start_date->toIso8601String(),
]));

$broadcast->setStatus(new \Google_Service_YouTube_LiveBroadcastStatus([
    'privacyStatus' => 'public', // or 'unlisted' for youth events
]));

$createdBroadcast = $youtube->liveBroadcasts->insert('snippet,status', $broadcast);

// Save stream URL to database
$eventSport->update([
    'stream_url' => "https://youtube.com/watch?v={$createdBroadcast->getId()}",
]);
```

**Embed on Website:**

```html
<iframe
    src="https://www.youtube.com/embed/{{ $eventSport->stream_id }}"
    width="100%"
    height="500"
    frameborder="0"
    allowfullscreen>
</iframe>
```

### 4. Email & SMS Notifications

**Laravel Notifications:**

```php
// SendPurchaseConfirmation.php
public function via($notifiable)
{
    return ['mail', 'database'];
}

public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Registration Confirmed - Bryant Invitational')
        ->greeting("Hi {$notifiable->first_name}!")
        ->line("Your registration for {$this->sale->product->name} has been confirmed.")
        ->line("Event: {$this->sale->event->name}")
        ->line("Date: {$this->sale->event->start_date->format('F j, Y')}")
        ->action('View Details', route('registrations.show', $this->sale))
        ->attach(Storage::path("qrcodes/sale_{$this->sale->id}.png"), [
            'as' => 'checkin-qrcode.png',
            'mime' => 'image/png',
        ]);
}

public function toDatabase($notifiable)
{
    return [
        'sale_id' => $this->sale->id,
        'event_name' => $this->sale->event->name,
        'product_name' => $this->sale->product->name,
        'amount_paid' => $this->sale->total_amount,
    ];
}
```

**SMS via Twilio (optional):**

```php
// config/services.php
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'token' => env('TWILIO_TOKEN'),
    'from' => env('TWILIO_FROM'),
],

// Notification channel
public function via($notifiable)
{
    return ['mail', 'database', TwilioChannel::class];
}

public function toTwilio($notifiable)
{
    return (new TwilioMessage)
        ->content("Your registration for Bryant Invitational is confirmed! Check your email for QR code.");
}
```

### 5. External Integrations (Future)

**Potential Integrations:**

- **Stripe** (alternative to Paddle)
- **Eventbrite** (event discovery)
- **Mailchimp** (email marketing)
- **Google Analytics** (website tracking)
- **Facebook Pixel** (ad tracking)
- **Zapier** (workflow automation)

---

## Phase 1 MVP Scope

### Must-Have Features (Launch by January 17, 2026)

**Timeline:** 2 months (October 16 - December 16)

#### Week 1-2 (October 16 - October 30): Foundation

- [x] ✅ Install Spatie Permissions + Filament + Shield
- [ ] Generate all models, migrations, relationships from Blueprint
- [ ] Run migrations
- [ ] Create Filament resources for core models:
  - [ ] Event
  - [ ] Venue
  - [ ] Sport
  - [ ] Product
  - [ ] Sale
  - [ ] Team
  - [ ] User
- [ ] Set up basic role permissions (admin, team_owner, player, vendor)
- [ ] Deploy to staging environment

#### Week 3-4 (November 1 - November 15): Event Setup

- [ ] Admin creates "Bryant Invitational" event
- [ ] Define time slots (Morning/Afternoon/Evening)
- [ ] Add sports to time slots:
  - [ ] Pickleball (singles, doubles)
  - [ ] Futsal
  - [ ] Volleyball
  - [ ] Cricket
  - [ ] Lacrosse
  - [ ] Wiffle ball
- [ ] Create divisions for each sport
- [ ] Generate products for:
  - [ ] Team entries ($500 each)
  - [ ] Individual entries ($125 each)
  - [ ] Spectator tickets ($29 each)
  - [ ] Booth packages ($5,000 each)
- [ ] Test product inventory tracking

#### Week 5-6 (November 16 - November 30): Payment Integration

- [ ] Set up Paddle account
- [ ] Create products in Paddle
- [ ] Implement Paddle checkout flow
- [ ] Build webhook handler
- [ ] Test complete purchase flow:
  - [ ] Team registration
  - [ ] Individual registration
  - [ ] Booth purchase
- [ ] Generate QR codes on purchase
- [ ] Send confirmation emails with QR codes

#### Week 7 (December 1 - December 7): Public Website

- [ ] Build landing page with:
  - [ ] Event details
  - [ ] Schedule overview
  - [ ] Pricing
  - [ ] Registration links
  - [ ] Sponsor/advertising info
- [ ] Product listing pages (browse by sport)
- [ ] User registration/login
- [ ] Checkout pages
- [ ] "My Registrations" dashboard
- [ ] Refund request form

#### Week 8 (December 8 - December 15): Testing & Launch

- [ ] End-to-end testing:
  - [ ] Register team
  - [ ] Add players to team
  - [ ] Sign waivers
  - [ ] Purchase booth
  - [ ] Request refund (before cutoff)
- [ ] Load testing (simulate 1,000 purchases)
- [ ] Security audit
- [ ] Set up monitoring/logging
- [ ] **Launch registration (December 16)**

#### Week 9-13 (December 16 - January 16): Operations

- [ ] Monitor registrations daily
- [ ] Answer support emails
- [ ] Process refunds as needed
- [ ] Adjust divisions based on actual sign-ups
- [ ] Finalize tournament brackets
- [ ] Assign booth numbers to vendors
- [ ] Set up YouTube streams
- [ ] Prepare check-in iPads
- [ ] **Finalize schedule (January 16)**

#### Week 14 (January 17-19): Event Execution

- [ ] On-site check-in with QR codes
- [ ] Live streaming
- [ ] Real-time support
- [ ] Post-event survey

### Nice-to-Have Features (Post-MVP)

**Phase 2 (February - March 2026):**

- [ ] Event cloning/templates
- [ ] Advanced scheduling/bracket generation
- [ ] Live scoring integration
- [ ] Mobile app (React Native)
- [ ] Vendor asset upload portal
- [ ] Email marketing automation
- [ ] Discount codes/promotions
- [ ] Affiliate program for teams

**Phase 3 (April 2026+):**

- [ ] Multi-city expansion (NYC event)
- [ ] Recurring events/subscriptions
- [ ] Player profiles and stats
- [ ] Team messaging system
- [ ] Sponsor ROI dashboard
- [ ] API for third-party integrations

---

## Future Enhancements

### 1. Multi-City Expansion

**Goal:** Launch NYC Bryant Invitational in April 2026

**Implementation:**

- Clone El Salvador event as template
- Create new venue (NYC Sports Complex)
- Adjust pricing for NYC market
- Localize marketing materials
- Use same codebase, different event instance

**Database Support:**
- Multi-tenancy already supported (events tied to venues)
- No schema changes needed

### 2. Event Templates & Cloning

**Feature:** Save successful event as template

**Workflow:**

1. After event completes, admin clicks "Save as Template"
2. System copies:
   - Event sports configuration
   - Divisions
   - Products (with pricing)
   - Time slots
3. Next year, admin clicks "Create Event from Template"
4. System pre-fills all settings
5. Admin adjusts dates and inventory
6. Launch registration

**Implementation:**

```php
// EventTemplate model
public function clone(): Event
{
    $template = $this->sourceEvent;

    $newEvent = Event::create([
        'venue_id' => $template->venue_id,
        'name' => $template->name . ' 2027',
        'start_date' => $template->start_date->addYear(),
        'end_date' => $template->end_date->addYear(),
        'status' => 'draft',
        'refund_cutoff_date' => $template->start_date->addYear()->subWeeks(2),
    ]);

    // Clone time slots
    foreach ($template->timeSlots as $slot) {
        $newSlot = $newEvent->timeSlots()->create($slot->only([
            'start_time', 'end_time', 'available_space_sqft'
        ]));

        // Clone event sports
        foreach ($slot->eventSports as $eventSport) {
            $newEventSport = $newSlot->eventSports()->create([
                'sport_id' => $eventSport->sport_id,
                'space_required_sqft' => $eventSport->space_required_sqft,
                'max_teams' => $eventSport->max_teams,
                'max_players' => $eventSport->max_players,
            ]);

            // Clone divisions and products
            // ...
        }
    }

    return $newEvent;
}
```

### 3. Analytics & Reporting

**Admin Dashboard:**

- Total revenue by event
- Revenue by product type (teams vs advertising)
- Registration trends over time
- Popular sports/divisions
- Refund rate analysis
- Geographic distribution of participants
- Conversion funnel (visits → registrations)

**Sponsor Dashboard:**

- Booth/banner impressions (estimated)
- Stream viewer counts
- Click-through rates on website ads
- ROI calculator

**Implementation:**

```php
// EventAnalyticsWidget.php
protected function getStats(): array
{
    $event = $this->getRecord();

    return [
        'total_revenue' => $event->sales()->completed()->sum('total_amount'),
        'team_revenue' => $event->teamSales()->sum('total_amount'),
        'advertising_revenue' => $event->advertisingSales()->sum('total_amount'),
        'registrations' => $event->sales()->completed()->count(),
        'refund_rate' => $this->calculateRefundRate($event),
        'conversion_rate' => $this->calculateConversionRate($event),
    ];
}
```

### 4. Mobile App

**Purpose:**

- Better user experience for registration
- Push notifications for schedule updates
- Live scoring and brackets
- Venue maps and navigation
- Team messaging

**Tech Stack:**

- **Framework:** React Native or Flutter
- **API:** Laravel API (already built)
- **Authentication:** Sanctum tokens
- **Push Notifications:** Firebase Cloud Messaging

**Features:**

- Event browsing and registration
- QR code wallet integration
- Real-time schedule updates
- Live stream viewing
- Team roster management
- In-app messaging

### 5. Advanced Scheduling

**Current Limitation:** Manual bracket/schedule creation

**Future Enhancement:**

- Automatic bracket generation
- Round-robin scheduling
- Court/time optimization algorithm
- Conflict detection (same player in multiple divisions)
- Referee assignment

**Potential Integration:**

- **Tournkey** (tournament management software)
- **LeagueApps** (sports league management)
- Custom algorithm using constraint satisfaction solver

---

## Technical Implementation Notes

### Database Indexes for Performance

**Critical Indexes (already defined in schema):**

```sql
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_events_dates ON events(start_date, end_date);
CREATE INDEX idx_sales_event ON sales(event_id);
CREATE INDEX idx_sales_user ON sales(user_id);
CREATE INDEX idx_sales_status ON sales(status);
CREATE INDEX idx_products_event ON products(event_id);
CREATE INDEX idx_products_type ON products(type);
CREATE INDEX idx_teams_event ON teams(event_id);
CREATE INDEX idx_teams_owner ON teams(owner_id);
CREATE INDEX idx_individual_players_event ON individual_players(event_id);
CREATE INDEX idx_event_sports_timeslot ON event_sports(time_slot_id);
CREATE INDEX idx_paddle_transaction ON sales(paddle_transaction_id);
```

**Additional Recommended Indexes:**

```sql
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_team_players_team ON team_players(team_id);
CREATE INDEX idx_team_players_user ON team_players(user_id);
CREATE INDEX idx_refunds_sale ON refunds(sale_id);
CREATE INDEX idx_refunds_status ON refunds(status);
```

### Model Observers

**ProductObserver:**

```php
// On sale creation, increment product quantity
// On refund approval, decrement product quantity
```

**TeamPlayerObserver:**

```php
// On player added, increment team.current_players
// On player removed, decrement team.current_players
```

**SaleObserver:**

```php
// On status change to 'completed', create team/player/booth record
// On status change to 'refunded', soft-delete related records
```

### Custom Validation Rules

**SpaceAvailabilityRule:**

```php
// Ensure event_sport fits in time_slot without exceeding capacity
```

**ProductQuantityRule:**

```php
// Ensure product is not sold out before purchase
```

**RefundCutoffRule:**

```php
// Ensure refund request is before event.refund_cutoff_date
```

**TeamCapacityRule:**

```php
// Ensure team has space for new player
```

### Scopes

**Event Scopes:**

```php
public function scopeUpcoming($query)
{
    return $query->where('start_date', '>=', now())
        ->where('status', '!=', 'cancelled');
}

public function scopeOpen($query)
{
    return $query->where('status', 'open');
}

public function scopeClosed($query)
{
    return $query->where('status', 'closed');
}
```

**Product Scopes:**

```php
public function scopeAvailable($query)
{
    return $query->whereRaw('current_quantity < max_quantity')
        ->orWhereNull('max_quantity');
}
```

**Sale Scopes:**

```php
public function scopeCompleted($query)
{
    return $query->where('status', 'completed');
}

public function scopeRefunded($query)
{
    return $query->where('status', 'refunded');
}
```

### Accessors & Mutators

**Product:**

```php
public function getIsAvailableAttribute(): bool
{
    if ($this->max_quantity === null) return true;
    return $this->current_quantity < $this->max_quantity;
}

public function getRemainingQuantityAttribute(): int
{
    return $this->max_quantity - $this->current_quantity;
}
```

**Event:**

```php
public function getIsRefundableAttribute(): bool
{
    return now()->lte($this->refund_cutoff_date);
}

public function getDaysUntilEventAttribute(): int
{
    return now()->diffInDays($this->start_date);
}
```

**EventTimeSlot:**

```php
public function getAvailableSpaceAttribute(): int
{
    $usedSpace = $this->eventSports()->sum('space_required_sqft');
    return $this->available_space_sqft - $usedSpace;
}

public function getSpaceUsagePercentAttribute(): float
{
    $usedSpace = $this->eventSports()->sum('space_required_sqft');
    return ($usedSpace / $this->available_space_sqft) * 100;
}
```

### Jobs

**ProcessPaddleWebhook:**

```php
// Handle Paddle payment notifications in background
```

**SendPurchaseConfirmation:**

```php
// Send email + SMS after successful purchase
```

**SendRefundNotification:**

```php
// Notify user when refund is processed
```

**GenerateQRCode:**

```php
// Create QR code image for check-in
```

**ProcessPaddleRefund:**

```php
// Call Paddle API to refund transaction
```

**SendEventReminder:**

```php
// Send email 1 week before event starts
```

### Notifications

**PurchaseConfirmed:**

```php
// Email + SMS + Database notification
// Attach QR code
```

**RefundApproved:**

```php
// Email notification with refund details
```

**EventReminder:**

```php
// Email reminder 1 week before
```

**TeamPlayerAdded:**

```php
// Notify team owner when player joins
```

**WaiverReminder:**

```php
// Email players who haven't signed waiver
```

### Middleware

**CheckEventStatus:**

```php
// Prevent actions on closed/cancelled events
```

**CheckProductAvailability:**

```php
// Prevent purchase if product is sold out
```

**CheckRefundEligibility:**

```php
// Enforce 2-week refund cutoff
```

**VerifyPaddleSignature:**

```php
// Validate Paddle webhook signatures for security
```

---

## Appendix: Blueprint YAML Reference

See `draft.yaml` for complete Laravel Blueprint definition.

**Key Models:**

- Venue
- Event
- Sport
- EventTimeSlot
- EventSport
- AgeGroup
- SkillLevel
- Division
- Product
- User
- Team
- TeamPlayer
- IndividualPlayer
- Booth
- Banner
- WebsiteAd
- Sale
- Refund
- EventTemplate

**Relationships:**

- Venue → Events (1:many)
- Event → Products, Teams, Sales, etc. (1:many)
- EventSport → Divisions (1:many)
- Division → Products (1:many)
- Product → Sales (1:many)
- Sale → Refunds (1:many)
- Team → TeamPlayers (1:many)
- User → Teams (owner), Sales, Refunds (1:many)

---

## Conclusion

The Bryant Invitational platform is a **comprehensive multi-sport tournament management system** designed for scalability, flexibility, and revenue optimization. By combining dynamic space allocation, flexible scheduling, unified product inventory, and robust payment processing, the system enables efficient management of complex events while maximizing participant satisfaction and profitability.

**Next Steps:**

1. Review and approve this specification
2. Begin Phase 1 development (Week 1-2: Foundation)
3. Set up development environment and staging server
4. Generate models and migrations from Blueprint
5. Build Filament admin panel resources
6. Integrate Paddle payments
7. Launch registration by December 16, 2025

**Questions or Changes?**

Contact David Hamilton (david@devvista.com) or Brynley Bryant for revisions.

---

**Document Version:** 1.0
**Date:** October 16, 2025
**Status:** Draft - Pending Approval
