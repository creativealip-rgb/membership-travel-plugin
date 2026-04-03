# Travel Membership Pro - All-in-One

**Version:** 2.0.0  
**Author:** Kowhi 🦭  
**License:** GPL v2 or later

Plugin WordPress all-in-one untuk website travel membership dengan tour booking system. Track perjalanan member, manage membership, dan jual paket tour!

---

## 🚀 **Fitur Lengkap**

### **🗺️ Travel Tracking**
- ✅ Custom Post Type: Destinations
- ✅ Taxonomies: Countries & Categories
- ✅ User Travel History
- ✅ Interactive Travel Map (Leaflet.js)
- ✅ Photo Upload
- ✅ Travel Statistics

### **💎 Membership System**
- ✅ Free & Paid Tiers
- ✅ Destination Limits
- ✅ Content Restriction
- ✅ Integration dengan membership plugin lain

### **🎫 Tour Booking**
- ✅ Create Tour Packages
- ✅ Tour Itinerary Builder
- ✅ Tour Gallery
- ✅ Online Booking Form
- ✅ Payment Proof Upload
- ✅ Booking Status Tracking
- ✅ Admin Booking Management

### **📊 Dashboard**
- ✅ User Travel Dashboard
- ✅ User Booking Dashboard
- ✅ Admin Reports & Analytics
- ✅ Export Data

---

## 📦 **Installation**

### **1. Upload Plugin**

**Via WordPress Admin:**
1. Zip folder `travel-membership-plugin` jadi `travel-membership-pro.zip`
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Pilih file ZIP
4. **Install Now → Activate**

**Via FTP:**
1. Upload folder ke `/wp-content/plugins/travel-membership-plugin/`
2. WordPress Admin → Plugins → Activate

### **2. Setup Awal**

#### **A. Configure Settings**
- **Settings → Travel Membership**
- Set:
  - Free Tier Limit: `5` destinations
  - Enable Membership: ✓
  - Map Provider: `Leaflet (Free)`
  - Currency: `IDR`

#### **B. Add Countries & Categories**
- **Destinations → Countries** → Add negara
- **Destinations → Categories** → Add kategori (Adventure, Beach, Culture, dll)

#### **C. Create Pages**

| Page | Shortcode | URL |
|------|-----------|-----|
| Travel Dashboard | `[travel_dashboard]` | `/my-travel-dashboard/` |
| Tour List | `[tour_list]` | `/tours/` |
| My Bookings | `[my_bookings]` | `/my-bookings/` |

---

## 📄 **Shortcodes**

### **Travel Features:**
| Shortcode | Deskripsi |
|-----------|-----------|
| `[travel_dashboard]` | Full user dashboard dengan form & history |
| `[travel_map]` | Interactive travel map |
| `[travel_stats]` | User travel statistics |
| `[user_travel_history]` | Travel history list |

### **Tour Booking Features:**
| Shortcode | Deskripsi |
|-----------|-----------|
| `[tour_list]` | List semua tours |
| `[tour_single id="123"]` | Single tour detail page |
| `[booking_form tour_id="123"]` | Booking form |
| `[my_bookings]` | User booking history |

---

## 🎯 **Cara Pakai**

### **Untuk Admin:**

#### **1. Add Destinations**
- **Destinations → Add New**
- Fill: Name, Country, Category, Photos
- **Publish**

#### **2. Create Tour Packages**
- **Destinations → Tours → Add New Tour**
- Fill:
  - Title: "Bali Adventure 5 Days"
  - Price: Rp 5.000.000
  - Duration: 5 days
  - Quota: 20 persons
  - Itinerary: Day-by-day
  - Includes/Excludes
  - Gallery Photos
- **Publish**

#### **3. Manage Bookings**
- **Destinations → Bookings**
- View all bookings
- Update status: Pending → Paid → Confirmed → Completed
- View payment proof

#### **4. View Reports**
- **Destinations → Reports**
- Total users, travelers, destinations
- Top countries
- Export data

### **Untuk User/Member:**

#### **1. Track Travels**
- Login → My Travel Dashboard
- Click "+ Add New Travel"
- Fill: Destination, Date, Photos, Notes
- **Add Travel**

#### **2. Book Tours**
- Visit `/tours/`
- View tour details
- Fill booking form
- Upload payment proof
- Get booking code

#### **3. View Bookings**
- Visit `/my-bookings/`
- See all bookings with status
- Cancel if needed

---

## 📊 **Booking Status Flow**

```
⏳ Pending Payment
   ↓ (user upload payment)
📤 Payment Uploaded
   ↓ (admin verify)
✅ Paid
   ↓ (admin confirm)
✓ Confirmed
   ↓ (after tour)
✔ Completed

OR cancel → ❌ Cancelled
```

---

## 💰 **Payment Methods**

**Default:**
- Bank Transfer (BCA, Mandiri, BNI, BRI)
- E-Wallet (GoPay, OVO, DANA, ShopeePay)

**Configure:**
- **Destinations → Tours → Settings**
- Edit bank accounts di `includes/class-payment-handler.php`

---

## 🎨 **Customization**

### **CSS Styling:**

Add to **Appearance → Customize → Additional CSS**:

```css
/* Change primary color */
.tmp-btn-primary {
    background: #28a745;
}

/* Change stat card style */
.tmp-stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.tmp-stat-number {
    color: white;
}
```

### **Email Notifications:**

Edit `includes/class-booking-manager.php`:

```php
private function send_booking_notification($booking_id, $type, $extra = null) {
    // Customize email subject & message
}
```

---

## 📁 **File Structure**

```
travel-membership-plugin/
├── travel-membership-pro.php      ✅ Main file (v2.0.0)
├── uninstall.php                   ✅ Cleanup
│
├── includes/                       ✅ Core Classes
│   ├── class-travel-post-type.php
│   ├── class-travel-taxonomy.php
│   ├── class-user-travel-tracker.php
│   ├── class-membership-checker.php
│   ├── class-ajax-handlers.php
│   ├── class-tour-post-type.php    🆕
│   ├── class-booking-manager.php   🆕
│   └── class-payment-handler.php   🆕
│
├── admin/                          ✅ Admin Panel
│   ├── class-admin-menu.php
│   ├── class-settings-page.php
│   ├── class-tour-admin.php        🆕
│   └── views/
│       ├── all-travels.php
│       └── reports.php
│
├── public/                         ✅ Frontend
│   ├── class-shortcodes.php
│   ├── class-asset-loader.php
│   ├── class-tour-public.php       🆕
│   ├── css/
│   │   ├── travel-membership.css
│   │   └── tour-booking.css        🆕
│   └── js/
│       ├── travel-membership.js
│       └── tour-booking.js         🆕
│
├── templates/                      ✅ Templates
│   ├── travel-dashboard.php
│   ├── travel-map.php
│   ├── travel-stats.php
│   ├── travel-history.php
│   ├── tour-list.php               🆕
│   ├── tour-single.php             🆕
│   ├── booking-form.php            🆕
│   └── my-bookings.php             🆕
│
└── README.md                       ✅ Documentation
```

---

## 🔐 **Security**

- ✅ Nonce verification
- ✅ Capability checks
- ✅ Data sanitization
- ✅ File upload validation (type & size)
- ✅ AJAX security

---

## 🐛 **Troubleshooting**

### **Plugin gak muncul?**
- Clear cache browser
- Logout & login lagi
- Check Plugins page

### **Map gak muncul?**
- Check browser console (F12) untuk errors
- Pastikan tema gak conflict dengan Leaflet.js

### **Booking form gak jalan?**
- Check user sudah login
- Check browser console untuk JavaScript errors

### **Payment upload gagal?**
- Check file size (max 5MB)
- Check file type (JPG, PNG, GIF, WebP)

---

## 📞 **Support**

**Email:** support@nggawe.web.id  
**Docs:** https://nggawe.web.id/docs/travel-membership-pro

---

## 📝 **Changelog**

### **Version 2.0.0** (March 2026) 🆕
- ✅ **NEW:** Tour Booking System
- ✅ **NEW:** Online booking form
- ✅ **NEW:** Payment proof upload
- ✅ **NEW:** Booking management
- ✅ **NEW:** Tour itinerary builder
- ✅ Merge addon ke main plugin

### **Version 1.0.0** (March 2026)
- ✅ Initial release
- ✅ Travel tracking
- ✅ Membership system
- ✅ Interactive map
- ✅ User dashboard

---

## 👨‍💻 **Credits**

**Developed by:** Kowhi 🦭  
**For:** Nggawe Web (https://nggawe.web.id)  
**Date:** March 2026

---

**Enjoy your travel membership website! 🗺️✈️🎫**
