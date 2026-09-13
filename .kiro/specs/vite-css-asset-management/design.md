# Design Document

## Overview

The current issue stems from a mismatch between the CSS files referenced in the Blade templates and those configured in Vite's build system. The `show-dark-mode.css` file exists but is not included in the Vite configuration, causing manifest errors when the application tries to load it.

This design addresses the asset management problem by:
1. Updating Vite configuration to include all required CSS files
2. Implementing a systematic approach to CSS asset management
3. Ensuring proper build pipeline integration for all CSS dependencies
4. Optimizing CSS loading performance

## Architecture

### Current State Analysis

**Problem:** The `main.blade.php` layout references `show-dark-mode.css` in the `@vite` directive, but `vite.config.js` only includes:
- `resources/css/app.css`
- `resources/css/browser-compatibility.css` 
- `resources/css/dark-mode.css`

**Missing:** `resources/css/show-dark-mode.css`

### Proposed Solution Architecture

```
Vite Build System
├── Input Configuration
│   ├── resources/css/app.css (base styles)
│   ├── resources/css/browser-compatibility.css (compatibility)
│   ├── resources/css/dark-mode.css (general dark mode)
│   ├── resources/css/show-dark-mode.css (page-specific dark mode)
│   └── resources/js/app.jsx (JavaScript entry)
├── Build Pipeline
│   ├── CSS Processing (PostCSS, Tailwind)
│   ├── Asset Optimization
│   └── Manifest Generation
└── Output
    ├── Compiled CSS bundles
    ├── Asset manifest
    └── Optimized JavaScript
```

## Components and Interfaces

### 1. Vite Configuration Component

**Purpose:** Central configuration for all build assets

**Interface:**
```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS Files
                'resources/css/app.css',
                'resources/css/browser-compatibility.css', 
                'resources/css/dark-mode.css',
                'resources/css/show-dark-mode.css', // ADD THIS
                // JavaScript Files
                'resources/js/app.jsx'
            ],
            refresh: true,
        }),
        react(),
    ],
    // ... rest of config
});
```

### 2. CSS Loading Strategy Component

**Purpose:** Optimize CSS loading performance and organization

**Current Strategy Issues:**
- All CSS files loaded on every page
- No conditional loading based on page type
- Potential for unused CSS on certain pages

**Proposed Strategy:**
- Keep critical CSS (app.css, browser-compatibility.css) global
- Implement conditional loading for page-specific CSS
- Maintain dark-mode.css as global for theme consistency

### 3. Asset Manifest Integration

**Purpose:** Ensure proper asset resolution in production

**Components:**
- Vite manifest generation
- Laravel asset helper integration
- Cache busting through hashed filenames

## Data Models

### CSS Asset Configuration Model

```javascript
const cssAssets = {
    global: [
        'resources/css/app.css',
        'resources/css/browser-compatibility.css',
        'resources/css/dark-mode.css'
    ],
    pageSpecific: {
        'show': ['resources/css/show-dark-mode.css'],
        // Future page-specific CSS can be added here
    },
    javascript: [
        'resources/js/app.jsx'
    ]
};
```

### Build Configuration Model

```javascript
const buildConfig = {
    cssCodeSplit: true,
    rollupOptions: {
        output: {
            assetFileNames: (assetInfo) => {
                // Organized asset naming strategy
                if (/css/i.test(assetInfo.name)) {
                    return `css/[name]-[hash][extname]`;
                }
                return `assets/[name]-[hash][extname]`;
            }
        }
    }
};
```

## Error Handling

### 1. Missing Asset Detection

**Strategy:** Implement build-time validation to catch missing assets

**Implementation:**
- Vite plugin to validate all referenced assets exist
- Clear error messages during development
- Fail-fast approach during build process

### 2. Manifest Resolution Errors

**Strategy:** Graceful fallback for missing manifest entries

**Implementation:**
- Development vs production asset loading strategies
- Fallback CSS loading for critical styles
- Error logging for debugging

### 3. CSS Loading Failures

**Strategy:** Ensure core functionality remains intact

**Implementation:**
- Critical CSS inlining for essential styles
- Progressive enhancement for advanced styling
- Fallback themes for dark mode functionality

## Testing Strategy

### 1. Build Process Testing

**Objectives:**
- Verify all CSS files are included in build output
- Validate manifest generation includes all assets
- Confirm asset paths resolve correctly

**Tests:**
- Build process integration tests
- Manifest content validation
- Asset resolution verification

### 2. Page Loading Testing

**Objectives:**
- Ensure show.blade.php loads without errors
- Verify dark mode functionality works correctly
- Confirm CSS styles apply properly

**Tests:**
- Page load error detection
- CSS application verification
- Dark mode toggle functionality

### 3. Performance Testing

**Objectives:**
- Measure CSS loading performance impact
- Validate code splitting effectiveness
- Ensure optimal asset delivery

**Tests:**
- Bundle size analysis
- Loading time measurements
- Network request optimization verification

## Implementation Approach

### Phase 1: Immediate Fix
1. Update `vite.config.js` to include `show-dark-mode.css`
2. Rebuild assets to generate new manifest
3. Test show.blade.php page functionality

### Phase 2: Optimization
1. Implement conditional CSS loading strategy
2. Optimize asset bundling configuration
3. Add build-time asset validation

### Phase 3: Maintenance
1. Document CSS asset management guidelines
2. Create development workflow for new CSS files
3. Implement automated testing for asset integrity

## Performance Considerations

### CSS Bundle Optimization
- **Code Splitting:** Enable CSS code splitting to reduce initial bundle size
- **Critical CSS:** Identify and inline critical CSS for faster rendering
- **Lazy Loading:** Implement lazy loading for page-specific CSS

### Caching Strategy
- **Asset Hashing:** Use content-based hashing for optimal caching
- **Cache Headers:** Configure appropriate cache headers for CSS assets
- **CDN Integration:** Prepare for potential CDN deployment

### Build Performance
- **Incremental Builds:** Optimize for faster development builds
- **Parallel Processing:** Leverage Vite's parallel processing capabilities
- **Asset Optimization:** Balance between build time and output optimization