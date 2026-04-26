# Navigation System (Menus & MenuItems)

## Overview

The navigation system enables creation and management of multiple menus.

## Key Features

- **N-level nesting** via nested set pattern
- **Multi-language** support for menu item titles
- **Flexible linking** (internal pages or external URLs)
- **Performance** through aggressive caching
- **Reorderable** via drag-and-drop in admin
- **Scoped management** by menu ID in admin interface


## Database Structure

### Menus Table
**Migration**: [`database/migrations/2026_02_17_113244_create_menus_tables.php`](database/migrations/2026_02_17_113244_create_menus_tables.php:10)

- `menus` - Main menu container table with Twill default fields and `title`
- `menu_revisions` - Revision history for menus

### MenuItems Table
**Migrations**: 
- [`database/migrations/2026_02_17_113245_create_menuitems_tables.php`](database/migrations/2026_02_17_113245_create_menuitems_tables.php:10)
- [`database/migrations/2026_02_17_170804_add_extra_fields_to_menuitems_table.php`](database/migrations/2026_02_17_170804_add_extra_fields_to_menuitems_table.php:13)

**Structure**:
- `menuitems` - Menu items with:
  - `menu_id` - Foreign key to parent menu (cascade delete)
  - `position` - Sort order within parent
  - Nested set columns (`_lft`, `_rgt`, `parent_id`) for hierarchical structure
  - `type` - Link type: `internal` or `external`
  - `external_url` - URL for external links
  - `target` - Link target (`_self` or `_blank`)
- `menuitem_translations` - Translatable fields (`title`, `description`)

## Models

### Menu Model
**File**: [`app/Models/Menu.php`](app/Models/Menu.php:8)

Simple model with:
- `HasRevisions` behavior for version control
- Fillable fields: `published`, `title`, `description`

### Menuitem Model
**File**: [`app/Models/Menuitem.php`](app/Models/Menuitem.php:16)

**Behaviors**:
- `HasTranslation` - Multi-language support
- `HasFiles` - File attachments capability
- `HasPosition` - Manual ordering
- `HasNesting` - Hierarchical structure (nested set pattern)
- `HasRelated` - Relations to other Twill modules
- `Sortable` - Drag-and-drop reordering

**Key Relations**:
- [`menu()`](app/Models/Menuitem.php:36) - BelongsTo relationship to parent Menu
- [`linkedPage()`](app/Models/Menuitem.php:44) - HasOneThrough to Page model via `twill_related` table for internal links

## Repository

**File**: [`app/Repositories/MenuitemRepository.php`](app/Repositories/MenuitemRepository.php:13)

### Core Responsibilities

**Form Handling** ([`getFormFields()`](app/Repositories/MenuitemRepository.php:32)):
- Fixes nested module data for Twill admin Vue components
- Manually retrieves and formats related page data for browser field

**Menu Tree Building** ([`getMenuTree()`](app/Repositories/MenuitemRepository.php:55)):
- Retrieves published menu items with translations and linked pages
- Builds hierarchical tree structure grouped by `parent_id`
- Resolves URLs based on item type (internal/external)
- Filters out items without title or URL
- **Caching**: Results cached forever per menu ID and locale

**Cache Management**:
- [`afterSave()`](app/Repositories/MenuitemRepository.php:72) - Invalidates cache on item save
- [`afterDelete()`](app/Repositories/MenuitemRepository.php:78) - Invalidates cache on item delete
- [`forgetMenuCache()`](app/Repositories/MenuitemRepository.php:84) - Clears all locale variants

**URL Resolution** ([`getItemUrl()`](app/Repositories/MenuitemRepository.php:118)):
- `external` type → returns `external_url` field
- `internal` type → constructs localized URL from linked page slug

## Controllers

### MenuController
**File**: [`app/Http/Controllers/Twill/MenuController.php`](app/Http/Controllers/Twill/MenuController.php:9)

- Standard Twill module controller
- Disables permalink functionality
- Adds custom column ([`additionalIndexTableColumns()`](app/Http/Controllers/Twill/MenuController.php:17)) with link to manage menu items filtered by menu ID

### MenuitemController
**File**: [`app/Http/Controllers/Twill/MenuitemController.php`](app/Http/Controllers/Twill/MenuitemController.php:18)

**Configuration**:
- Extends `NestedModuleController` for hierarchical support
- `$nestedItemsDepth = 1` - Limits nesting depth display
- `$showOnlyParentItemsInBrowsers = true` - Simplifies item selection
- Enables reordering, disables permalinks

**Form Building**:
- [`getCreateForm()`](app/Http/Controllers/Twill/MenuitemController.php:38) / [`getForm()`](app/Http/Controllers/Twill/MenuitemController.php:44) - Builds admin forms with:
  - Translatable title input
  - Menu selector (pre-filled from filter context)
  - Type selector (internal/external)
  - Conditional fields based on type:
    - Internal: Browser field for page selection
    - External: URL input field
  - Target selector (_self/_blank)

**Filtering** ([`getIndexItems()`](app/Http/Controllers/Twill/MenuitemController.php:120)):
- Extracts `menu_id` from request filter
- Scopes listing to specific menu

**Navigation** ([`getBackLink()`](app/Http/Controllers/Twill/MenuitemController.php:148)):
- Preserves menu filter when returning from edit view

## Configuration

**File**: [`config/menu.php`](config/menu.php:1)

Defines menu IDs for global usage:
- `primary_id` - Main navigation menu (ID: 1)
- `footer_id` - Footer navigation menu (ID: 2)

These IDs are used to expose specific menus via Inertia props globally.

## Data Flow

1. **Admin creates Menu** → [`Menu`](app/Models/Menu.php:8) record created
2. **Admin adds MenuItems** → [`Menuitem`](app/Models/Menuitem.php:16) records with `menu_id` foreign key
3. **Item type selection**:
   - **Internal**: Links to Page via `twill_related` table (browser field)
   - **External**: Stores URL in `external_url` field
4. **Nesting**: Drag-and-drop in admin updates nested set columns
5. **Frontend request** → [`MenuitemRepository::getMenuTree()`](app/Repositories/MenuitemRepository.php:55) retrieves cached tree
6. **Cache invalidation** → Automatic on save/delete via repository hooks
