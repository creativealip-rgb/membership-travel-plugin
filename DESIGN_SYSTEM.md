# Travel Membership Pro - Design System

## 🎨 Color Palette

### Primary Colors
- **Primary Blue**: `#0066cc` - Main brand color, trust, professionalism
- **Primary Dark**: `#004c99` - Hover states, emphasis
- **Primary Light**: `#e6f0ff` - Backgrounds, highlights

### Secondary Colors
- **Accent Teal**: `#00b8a8` - Success, confirmation, positive actions
- **Accent Coral**: `#ff6b6b` - Urgency, important notices
- **Accent Amber**: `#f59e0b` - Warnings, pending states

### Neutral Colors
- **Text Dark**: `#1a202c` - Primary text
- **Text Medium**: `#4a5568` - Secondary text
- **Text Light**: `#718096` - Tertiary text, placeholders
- **Border**: `#e2e8f0` - Dividers, borders
- **Background**: `#f7fafc` - Page backgrounds
- **Surface**: `#ffffff` - Cards, surfaces

### Status Colors
- **Success**: `#48bb78` / `#f0fff4`
- **Warning**: `#ed8936` / `#fffaf0`
- **Error**: `#f56565` / `#fff5f5`
- **Info**: `#4299e1` / `ebf8ff`

## 📐 Typography

### Font Stack
```css
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
```

### Scale
- **Display**: 2.5rem (40px) - Page titles
- **H1**: 2rem (32px) - Section headers
- **H2**: 1.5rem (24px) - Subsections
- **H3**: 1.25rem (20px) - Card titles
- **H4**: 1.125rem (18px) - Small headers
- **Body**: 1rem (16px) - Default text
- **Small**: 0.875rem (14px) - Secondary info
- **XS**: 0.75rem (12px) - Captions, labels

### Weights
- **Regular**: 400
- **Medium**: 500
- **Semibold**: 600
- **Bold**: 700

### Line Heights
- **Tight**: 1.25 - Headings
- **Normal**: 1.5 - Body text
- **Relaxed**: 1.75 - Large text blocks

## 📏 Spacing System

### Base Unit: 4px
- **xs**: 0.25rem (4px)
- **sm**: 0.5rem (8px)
- **md**: 1rem (16px)
- **lg**: 1.5rem (24px)
- **xl**: 2rem (32px)
- **2xl**: 3rem (48px)
- **3xl**: 4rem (64px)

## 🔲 Border Radius
- **None**: 0
- **SM**: 0.25rem (4px) - Small elements
- **MD**: 0.5rem (8px) - Inputs, buttons
- **LG**: 0.75rem (12px) - Cards
- **XL**: 1rem (16px) - Modals, large cards
- **Full**: 9999px - Pills, badges

## 🌑 Shadows

### Elevation Levels
- **None**: `none`
- **SM**: `0 1px 2px 0 rgba(0, 0, 0, 0.05)`
- **MD**: `0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)`
- **LG**: `0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)`
- **XL**: `0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)`

## 🎭 Component Styles

### Buttons
- **Primary**: Gradient blue to purple, white text
- **Secondary**: Gray background, dark text
- **Success**: Green background
- **Danger**: Red background
- **Outline**: Transparent with border

### Cards
- White background
- Rounded corners (LG)
- Subtle shadow (MD)
- Hover lift effect

### Forms
- Clean inputs with subtle borders
- Focus states with primary color ring
- Clear labels with proper spacing
- Validation states (success/error)

### Badges
- Pill-shaped (Full radius)
- Color-coded by status
- Subtle backgrounds with darker text

## 📱 Responsive Breakpoints
- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

## ✨ Animations & Transitions

### Timing
- **Fast**: 150ms - Micro-interactions
- **Normal**: 300ms - Standard transitions
- **Slow**: 500ms - Large movements

### Easing
- **Default**: `ease-in-out`
- **Bounce**: `cubic-bezier(0.68, -0.55, 0.265, 1.55)`

### Common Animations
- **Fade In**: opacity 0 → 1
- **Slide Up**: translateY(10px) → 0
- **Scale In**: scale(0.95) → 1
- **Hover Lift**: translateY(0) → -4px

## 🎯 Accessibility
- Minimum contrast ratio: 4.5:1 for text
- Focus indicators on all interactive elements
- Semantic HTML structure
- ARIA labels where needed
- Keyboard navigation support
