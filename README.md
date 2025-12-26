# LeadGen CMS - Lead Generation Platform

A modern, responsive PHP-based Lead Generation CMS built with a sleek dark theme inspired by the Amoor design language. This platform enables you to capture, manage, and convert leads from multiple sources with a powerful PHP backend and MySQL database.

## 🚀 Version 2.0 - PHP Backend Update

This version has been completely upgraded with:
- **Full PHP Backend** with MySQL database support
- **RESTful API** for all lead operations
- **Web Scraper Integration** (Apify, Hunter.io)
- **Region Data APIs** for location-based features
- **Enhanced Responsive Design** for all devices

## 🎯 Features

### Lead Management
- **Full CRUD Operations**: Create, Read, Update, Delete leads
- **Status Tracking**: New, Contacted, Qualified, Converted, Lost
- **Source Tracking**: Contact Form, HubSpot, Google Maps, Hunter.io, Apify
- **Lead Scoring**: Visual score indicators
- **Bulk Operations**: Select and delete multiple leads
- **Export to CSV**: Download all leads as CSV file
- **Search & Filtering**: Filter by status, source, and search terms

### Lead Capture
- **Embed Code**: Copy-paste HTML form code for your website
- **Laravel Integration**: Backend controller code included
- **Database Migration**: Ready-to-use migration schema

### API Integrations
Configure API keys for:
- **HubSpot** - CRM & Lead Management
- **Apify** - Web scraping platform
- **Hunter.io** - Email finding & verification
- **Apollo.io** - B2B lead database

### Web Scraper
- **Google Maps Scraper** - Extract business data (via Apify or demo mode)
- **Hunter.io Integration** - Find professional emails
- **Configurable**: Search query, location, result limits
- **Import to Leads** - One-click import scraped data

### Region Data
- **Countries API** - Fetch list of all countries
- **IP Geolocation** - Detect user location
- **Currency & Timezone Data**

## 📁 Project Structure

```
lead_generate/
├── index.php              # Main CMS Dashboard
├── landing.php            # Public-facing landing page
├── setup.php              # Database installation script
├── README.md              # Documentation
│
├── config/
│   ├── config.php         # Main configuration
│   └── database.php       # Database connection
│
├── models/
│   ├── Lead.php           # Lead model (CRUD operations)
│   ├── LeadSource.php     # Lead sources model
│   └── ApiKey.php         # API key management
│
├── api/
│   ├── leads.php          # Leads REST API
│   ├── scraper.php        # Web scraper endpoints
│   ├── api-keys.php       # API keys management
│   └── regions.php        # Region data API
│
├── database/
│   └── schema.sql         # Database schema
│
├── css/
│   ├── styles.css         # Core styles & design tokens
│   ├── dashboard.css      # Dashboard & layout styles
│   ├── components.css     # UI component styles
│   ├── responsive.css     # Responsive breakpoints
│   └── mobile.css         # Enhanced mobile styles
│
├── js/
│   └── leadgen.js         # Frontend application
│
├── cache/                  # API response cache
└── uploads/               # File uploads
```

## 🎨 Design Features

| Feature | Description |
|---------|-------------|
| **Dark Theme** | Elegant navy backgrounds (`#0f0f1a`) |
| **Light Theme** | Clean white theme option |
| **Primary Color** | Indigo gradient (`#6366f1` → `#8b5cf6`) |
| **Typography** | Inter (body), Outfit (headings) |
| **Glassmorphism** | Backdrop blur with transparency |
| **Animations** | Smooth transitions & hover effects |
| **Responsive** | Mobile, tablet, desktop optimized |
| **Touch Optimized** | 44px+ touch targets |

## 🚀 Getting Started

### Prerequisites
- **XAMPP/WAMP/LAMP** or any PHP server
- **PHP 7.4+** with PDO MySQL extension
- **MySQL 5.7+** or MariaDB

### Installation

1. **Clone/Copy to your web server directory:**
   ```
   c:\xampp\htdocs\lead_generate\
   ```

2. **Run the database setup:**
   - Open browser and navigate to: `http://localhost/lead_generate/setup.php`
   - Click "Install Database" to create tables

3. **Access the application:**
   - Dashboard: `http://localhost/lead_generate/index.php`
   - Landing Page: `http://localhost/lead_generate/landing.php`

4. **Default Admin Credentials:**
   - Email: `admin@leadgen.com`
   - Password: `admin123`

### Manual Database Setup
If you prefer manual setup:
```sql
mysql -u root -p < database/schema.sql
```

## 📊 Database Schema

### Tables
- **users** - Admin users
- **leads** - Lead records with full details
- **lead_sources** - Inbound/outbound sources
- **api_keys** - Encrypted API key storage
- **scraped_data** - Web scraper results
- **activity_logs** - Audit trail
- **settings** - Application settings

### Quick Reference
```php
// Lead status values
'new', 'contacted', 'qualified', 'converted', 'lost'

// Lead sources
'Contact Form', 'HubSpot', 'Google Maps', 'Hunter.io', 
'Apify', 'Apollo.io', 'Manual', 'Website', 'Referral', 'Social Media'
```

## 🔌 API Endpoints

### Leads API (`/api/leads.php`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?action=list` | Get all leads (paginated) |
| GET | `?action=get&id=1` | Get single lead |
| GET | `?action=statistics` | Get dashboard stats |
| GET | `?action=recent` | Get recent leads |
| GET | `?action=export` | Export to CSV |
| POST | `?action=create` | Create new lead |
| PUT | `?id=1` | Update lead |
| DELETE | `?id=1` | Delete lead |
| DELETE | `?action=bulk` | Bulk delete |

### Scraper API (`/api/scraper.php`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `?action=google-maps` | Scrape Google Maps |
| POST | `?action=hunter` | Search Hunter.io |
| POST | `?action=import` | Import scraped leads |

### Regions API (`/api/regions.php`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?action=countries` | List all countries |
| GET | `?action=country&code=US` | Get country details |
| GET | `?action=ip-location` | Get location from IP |

## 🔐 Security Features

- **Prepared Statements** - SQL injection prevention
- **CSRF Protection** - Token-based form validation
- **Input Sanitization** - XSS prevention
- **API Key Encryption** - AES-256 encrypted storage
- **Security Headers** - XSS, clickjacking protection
- **Activity Logging** - Full audit trail

## 📱 Responsive Breakpoints

- **Desktop**: Full sidebar (280px)
- **Tablet** (< 992px): Overlay sidebar, 2-column layouts
- **Mobile** (< 768px): Stacked layouts, touch-optimized
- **Small** (< 480px): Compact UI, hidden search
- **Safe Areas**: iPhone X+ notch support

## 🛠️ External APIs Used

### Free Public APIs
- **REST Countries** - Country data (restcountries.com)
- **IP-API** - IP geolocation (ip-api.com)

### Paid APIs (Optional)
- **Apify** - Web scraping (apify.com)
- **Hunter.io** - Email finder (hunter.io)
- **Apollo.io** - B2B data (apollo.io)
- **HubSpot** - CRM sync (hubspot.com)

## 📋 Pages

1. **Dashboard** - Overview with stats, recent leads, source breakdown
2. **All Leads** - Full lead management with filters & search
3. **Lead Capture** - Embed codes & Laravel integration
4. **Lead Sources** - Configure inbound/outbound sources
5. **API Settings** - Manage API keys
6. **Web Scraper** - Google Maps scraper tool

## 🛠️ Technologies

### Backend
- PHP 7.4+
- MySQL/MariaDB
- PDO Database Layer
- RESTful API Architecture

### Frontend
- HTML5 & CSS3 (Custom Properties, Flexbox, Grid)
- Vanilla JavaScript (ES6+)
- Async/Await API calls
- Font Awesome 6 (Icons)
- Google Fonts (Inter, Outfit)

## 📝 Configuration

Edit `config/database.php` to update database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'leadgen_cms');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Edit `config/config.php` for application settings:
```php
define('APP_DEBUG', true);  // Set to false in production
define('ITEMS_PER_PAGE', 20);
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📄 License

This project is open-source and available under the MIT License.

---

Built with ❤️ for Lead Generation | Version 2.0.0
