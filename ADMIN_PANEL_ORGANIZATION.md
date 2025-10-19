# Filament Admin Panel - Navigation Organization

**Updated:** October 16, 2025

---

## Navigation Structure

The admin panel is now organized into 5 logical groups with appropriate icons and sort orders:

### 1. 📅 Event Management (Always Expanded)

**Purpose:** Core event setup and configuration

| Resource | Icon | Sort | Description |
|----------|------|------|-------------|
| Events | 📅 calendar-days | 1 | Tournament instances |
| Venues | 🏢 building-office-2 | 2 | Physical locations |
| Sports | 🏆 trophy | 3 | Sport types (Pickleball, Futsal, etc.) |
| Divisions | ⊞ squares-2x2 | 4 | Age groups + skill levels |

---

### 2. 🛒 Sales & Products (Always Expanded)

**Purpose:** Inventory, sales tracking, and refunds

| Resource | Icon | Sort | Description |
|----------|------|------|-------------|
| Products | 🛒 shopping-cart | 1 | Team entries, tickets, ads for sale |
| Sales | 💵 currency-dollar | 2 | Transaction records |
| Refunds | ↩️ arrow-uturn-left | 3 | Refund requests and processing |

---

### 3. 👥 Participants (Always Expanded)

**Purpose:** Team and player management

| Resource | Icon | Sort | Description |
|----------|------|------|-------------|
| Teams | 👥 user-group | 1 | Team registrations and rosters |
| Individual Players | 👤 user | 2 | Singles registrations (Pickleball, etc.) |

---

### 4. 📢 Marketing & Advertising (Collapsed by Default)

**Purpose:** Vendor advertising products

| Resource | Icon | Sort | Description |
|----------|------|------|-------------|
| Booths | 🏪 building-storefront | 1 | Vendor booth packages |
| Banners | 🖼️ photo | 2 | Banner advertising |
| Website Ads | 🌐 globe-alt | 3 | Digital ad placements |

---

### 5. ⚙️ Settings (Collapsed by Default)

**Purpose:** System configuration and user management

| Resource | Icon | Sort | Description |
|----------|------|------|-------------|
| Roles | 🛡️ shield-check | 1 | Permission management (Shield) |
| Users | 👥 users | 2 | User accounts and roles |

---

## Navigation Group Behavior

**Always Expanded:**
- Event Management - Primary workflow
- Sales & Products - Revenue tracking
- Participants - Daily operations

**Collapsed by Default:**
- Marketing & Advertising - Less frequent access
- Settings - Administrative tasks only

---

## Icon Reference

All icons use Heroicons Outline style (`heroicon-o-*`):

### Group Icons
- 📅 Event Management: `heroicon-o-calendar`
- 🛍️ Sales & Products: `heroicon-o-shopping-bag`
- 👥 Participants: `heroicon-o-users`
- 📢 Marketing: `heroicon-o-megaphone`
- ⚙️ Settings: `heroicon-o-cog-6-tooth`

### Resource Icons
- Events: `heroicon-o-calendar-days`
- Venues: `heroicon-o-building-office-2`
- Sports: `heroicon-o-trophy`
- Divisions: `heroicon-o-squares-2x2`
- Products: `heroicon-o-shopping-cart`
- Sales: `heroicon-o-currency-dollar`
- Refunds: `heroicon-o-arrow-uturn-left`
- Teams: `heroicon-o-user-group`
- Individual Players: `heroicon-o-user`
- Booths: `heroicon-o-building-storefront`
- Banners: `heroicon-o-photo`
- Website Ads: `heroicon-o-globe-alt`
- Users: `heroicon-o-users`

---

## User Workflow

### Typical Admin Flow:

1. **Event Setup** (Event Management group)
   - Create Venue
   - Create Event
   - Add Sports
   - Create Divisions

2. **Product Creation** (Sales & Products group)
   - Create Products for each division
   - Set pricing and inventory limits

3. **Monitor Registrations** (Participants group)
   - View Teams
   - View Individual Players

4. **Track Sales** (Sales & Products group)
   - View Sales records
   - Process Refunds (if needed)

5. **Manage Advertising** (Marketing group)
   - Assign Booth numbers
   - Track Banner locations
   - Manage Website Ads

6. **User Management** (Settings group)
   - Create admin accounts
   - Assign roles
   - Manage permissions

---

## Color Scheme

**Primary Color:** Amber
- Bright, energetic
- Associated with sports and competition
- Good contrast and visibility

---

## Next Steps

### Phase 2 Enhancements:

1. **Add Navigation Badges**
   - Show count on "Teams" (e.g., "Teams (45)")
   - Show count on "Sales" (e.g., "Sales (127)")
   - Show pending count on "Refunds" (e.g., "Refunds (3)")

2. **Create Custom Dashboard Widgets**
   - Total Revenue card
   - Registrations by Sport chart
   - Upcoming Events calendar
   - Recent Sales table

3. **Add Relation Managers**
   - Team → Team Players
   - Event → Products
   - Event → Sales

4. **Improve Forms**
   - Add field hints and help text
   - Add reactive fields (e.g., calculate total on quantity change)
   - Add validation messages

5. **Add Filters**
   - Sales by status (pending, completed, refunded)
   - Events by status (draft, open, closed)
   - Products by type (team, player, booth, etc.)

---

## Configuration Files Modified

1. **AdminPanelProvider.php**
   - Added `navigationGroups()` with icons and collapse behavior
   - Set group order: Event Management → Sales → Participants → Marketing → Settings

2. **filament-shield.php**
   - Changed Role resource navigation group to "Settings"
   - Set navigation sort to 1

3. **All Resource Files**
   - Added `$navigationIcon` (unique for each)
   - Added `$navigationGroup` (logical grouping)
   - Added `$navigationSort` (order within group)

---

## Refresh Your Browser!

After these changes, when you visit `/admin`, you'll see:

```
Dashboard

📅 Event Management
  📅 Events
  🏢 Venues
  🏆 Sports
  ⊞ Divisions

🛍️ Sales & Products
  🛒 Products
  💵 Sales
  ↩️ Refunds

👥 Participants
  👥 Teams
  👤 Individual Players

📢 Marketing & Advertising [Collapsed]
  🏪 Booths
  🖼️ Banners
  🌐 Website Ads

⚙️ Settings [Collapsed]
  🛡️ Roles
  👥 Users
```

---

**Clean, organized, and ready for production use!** 🎉
