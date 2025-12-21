# Supply Chain Management System (GIS)

A Laravel-based Supply Chain Management System with Geographic Information System (GIS) features for tracking suppliers, factories, distributors, and couriers.

## Features

- 🗺️ **Interactive Map Dashboard** - Real-time location tracking on Leaflet maps
- 👥 **Role-Based Access** - Superadmin, Supplier, Factory, Distributor, Courier
- 📦 **Order Management** - Full order lifecycle from creation to delivery
- 🚚 **Courier GPS Tracking** - Real-time courier location updates
- 💬 **Chat System** - Direct messaging and broadcast messages
- 🔐 **Google OAuth** - Social login with Google

## Tech Stack

- **Backend**: Laravel 11
- **Database**: MySQL
- **Frontend**: Blade Templates, Vanilla CSS
- **Maps**: Leaflet.js + OpenStreetMap
- **Authentication**: Laravel Auth + Google OAuth (Socialite)

## Installation

### Local Development

```bash
# Clone repository
git clone https://github.com/nicojoee/SupplyChainTBD.git
cd SupplyChainTBD

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=supply_chain_gis
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

## Deploy to Vercel

### Prerequisites
- Vercel account
- External MySQL database (PlanetScale, Railway, Aiven, etc.)

### Steps

1. **Push to GitHub** (already done)

2. **Import to Vercel**
   - Go to [vercel.com](https://vercel.com)
   - Click "Import Project"
   - Select your GitHub repository

3. **Configure Environment Variables** in Vercel Dashboard:
   ```
   APP_NAME=Supply Chain GIS
   APP_ENV=production
   APP_KEY=base64:YOUR_APP_KEY_HERE
   APP_DEBUG=false
   APP_URL=https://your-domain.vercel.app
   
   DB_CONNECTION=mysql
   DB_HOST=your-db-host.com
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   
   SESSION_DRIVER=cookie
   CACHE_STORE=array
   LOG_CHANNEL=stderr
   
   GOOGLE_CLIENT_ID=your-google-client-id
   GOOGLE_CLIENT_SECRET=your-google-client-secret
   GOOGLE_REDIRECT_URI=https://your-domain.vercel.app/auth/google/callback
   
   SUPERADMIN_EMAIL=your-email@example.com
   ```

4. **Deploy** - Vercel will automatically build and deploy

### Generate APP_KEY

Run locally and copy the key:
```bash
php artisan key:generate --show
```

## Database Schema

See [database/schema/supply_chain_database.sql](database/schema/supply_chain_database.sql) for complete MySQL schema.

### Main Tables
- `users` - User accounts with roles
- `suppliers`, `factories`, `distributors`, `couriers` - Entity profiles
- `products` - Master product catalog
- `supplier_products`, `factory_products`, `distributor_stocks` - Inventory
- `orders`, `order_items` - Order management
- `conversations`, `messages` - Chat system

## User Roles

| Role | Description |
|------|-------------|
| `superadmin` | Full system access, manage all entities |
| `supplier` | Manage raw materials, fulfill orders to factories |
| `factory` | Buy from suppliers, produce & sell to distributors |
| `distributor` | Buy from factories, manage warehouse stock |
| `courier` | GPS tracking, deliver orders |

## API Endpoints

| Endpoint | Description |
|----------|-------------|
| `GET /api/map-data` | Get all entities for map display |
| `POST /courier/location` | Update courier GPS position |
| `GET /api/suppliers` | Paginated suppliers list |
| `GET /api/factories` | Paginated factories list |
| `GET /api/distributors` | Paginated distributors list |
| `GET /api/couriers` | Paginated couriers list |

## License

MIT License
