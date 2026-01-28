# Bootstrap 5 Migration Guide

## Overview

YiiBooster has been upgraded from Bootstrap 3.3.2 to Bootstrap 5.3.3. This document outlines the changes made and what users need to know when upgrading.

## Major Changes

### 1. Bootstrap Version
- **Before**: Bootstrap 3.3.2
- **After**: Bootstrap 5.3.3

### 2. jQuery Dependency Removed
Bootstrap 5 no longer depends on jQuery. All JavaScript components now use vanilla JavaScript.

**Impact**: 
- Bootstrap components work without jQuery being loaded
- Custom JavaScript that uses jQuery to manipulate Bootstrap components may need updating
- The package configuration has been updated to remove the jQuery dependency

### 3. Data Attributes Updated
All Bootstrap data attributes now use the `data-bs-*` namespace.

**Changes**:
| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `data-toggle` | `data-bs-toggle` |
| `data-target` | `data-bs-target` |
| `data-dismiss` | `data-bs-dismiss` |
| `data-parent` | `data-bs-parent` |
| `data-slide` | `data-bs-slide` |
| `data-slide-to` | `data-bs-slide-to` |

**Impact**:
- All YiiBooster widgets and helpers have been updated
- Custom code that uses these attributes must be updated

### 4. CSS Class Changes
Several utility classes have been renamed or changed.

**Changes**:
| Bootstrap 3 | Bootstrap 5 |
|------------|-------------|
| `pull-left` | `float-start` |
| `pull-right` | `float-end` |
| `help-block` | `form-text` |

**Impact**:
- All YiiBooster widgets and helpers have been updated
- Custom templates or views using these classes must be updated

### 5. JavaScript Initialization
Bootstrap 5 components are initialized using vanilla JavaScript instead of jQuery.

**Before (Bootstrap 3)**:
```javascript
$('[data-toggle="tooltip"]').tooltip();
$('[data-toggle="popover"]').popover();
```

**After (Bootstrap 5)**:
```javascript
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    new bootstrap.Tooltip(el);
});
document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
    new bootstrap.Popover(el);
});
```

**Impact**:
- YiiBooster core initialization has been updated
- Custom initialization code must be updated

### 6. CDN URL Updated
- **Before**: `https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/`
- **After**: `https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/`

## Files Modified

### Core Components
- `src/components/Booster.php` - Updated initialization and selectors
- `src/components/packages.php` - Updated CDN URLs and package configuration

### Helper Classes
- `src/helpers/TbHtml.php` - Updated all data attributes and CSS classes (12 locations)

### Widgets (18 files)
- `src/widgets/TbActiveForm.php`
- `src/widgets/TbAlert.php`
- `src/widgets/TbBaseMenu.php`
- `src/widgets/TbBulkActions.php`
- `src/widgets/TbButton.php`
- `src/widgets/TbButtonColumn.php`
- `src/widgets/TbButtonGroup.php`
- `src/widgets/TbButtonGroupColumn.php`
- `src/widgets/TbCarousel.php`
- `src/widgets/TbImageGallery.php`
- `src/widgets/TbNavbar.php`
- `src/widgets/TbPager.php`
- `src/widgets/TbPanel.php`
- `src/widgets/TbScrollSpy.php`
- `src/widgets/TbTabView.php`
- `src/widgets/TbTabs.php`
- `src/widgets/TbToggleColumn.php`
- `src/widgets/TbWizard.php`
- `src/widgets/input/TbInput.php`
- `src/widgets/input/TbInputHorizontal.php`

### Views
- `src/views/fileupload/form.php`
- `src/views/gallery/preview.php`

### Templates
- `src/gii/bootstrap/templates/default/_form.php`

### Bootstrap Assets
All Bootstrap CSS, JavaScript, and source map files have been replaced with Bootstrap 5.3.3 versions.

## Upgrade Guide for Users

### 1. Update Your Custom Code
If you have custom views or JavaScript that uses Bootstrap:

#### Data Attributes
Replace any `data-toggle`, `data-target`, etc. with their `data-bs-*` equivalents:
```php
// Old
echo '<button data-toggle="modal" data-target="#myModal">Open</button>';

// New
echo '<button data-bs-toggle="modal" data-bs-target="#myModal">Open</button>';
```

#### CSS Classes
Replace deprecated utility classes:
```php
// Old
echo '<div class="pull-right">Content</div>';
echo '<p class="help-block">Help text</p>';

// New
echo '<div class="float-end">Content</div>';
echo '<p class="form-text">Help text</p>';
```

#### JavaScript
Update any custom JavaScript that initializes Bootstrap components:
```javascript
// Old (jQuery)
$('.tooltip').tooltip();

// New (Vanilla JS)
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    new bootstrap.Tooltip(el);
});
```

### 2. Test Your Application
After upgrading, thoroughly test:
- All forms and form validation
- Modals, tooltips, and popovers
- Navigation components (navbars, tabs, accordions)
- Data tables and grids
- Any custom Bootstrap integrations

### 3. Browser Compatibility
Bootstrap 5 drops support for Internet Explorer. Ensure your target browsers are supported:
- Chrome >= 60
- Firefox >= 60
- Safari >= 12
- Edge >= 79

## New Features Available

Bootstrap 5 brings new features that you can now use:
- Enhanced grid system
- Improved form controls
- New utility classes
- Better RTL support
- Improved accessibility
- Smaller file sizes

## Breaking Changes Summary

1. **No IE support** - Bootstrap 5 does not support Internet Explorer
2. **jQuery removed** - jQuery is no longer a dependency
3. **Data attributes namespaced** - All data attributes use `data-bs-*`
4. **Some classes renamed** - Utility classes have been updated
5. **JavaScript API changes** - Component initialization uses vanilla JS

## Resources

- [Bootstrap 5 Official Documentation](https://getbootstrap.com/docs/5.3/)
- [Bootstrap 5 Migration Guide](https://getbootstrap.com/docs/5.3/migration/)
- [Bootstrap 5 GitHub Repository](https://github.com/twbs/bootstrap)

## Support

If you encounter issues after upgrading:
1. Check this migration guide
2. Review the Bootstrap 5 official migration documentation
3. Open an issue on the YiiBooster GitHub repository

## Summary

The upgrade to Bootstrap 5.3.3 is a major improvement that brings:
- ✅ Modern, lightweight framework without jQuery dependency
- ✅ Better performance and smaller file sizes
- ✅ Improved accessibility and RTL support
- ✅ Enhanced components and utilities
- ✅ Active development and long-term support

All YiiBooster core components have been updated to work seamlessly with Bootstrap 5. Users only need to update their custom code that directly uses Bootstrap features.
