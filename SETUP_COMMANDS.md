# Setup Commands for Microsoft Login

## Prerequisites

Make sure you have:
- PHP 8.2 or higher
- Composer installed
- Node.js and npm (for frontend assets)
- Database configured (SQLite, MySQL, PostgreSQL, etc.)

---

## Step-by-Step Setup Commands

### 1. **Install Dependencies** (if not already done)

```bash
composer install
```

This installs Laravel and required packages including:
- `laravel/socialite` - OAuth integration
- `socialiteproviders/microsoft` - Microsoft provider

---

### 2. **Configure Environment Variables**

Create or edit your `.env` file and add:

```env
MICROSOFT_CLIENT_ID=your_client_id_here
MICROSOFT_CLIENT_SECRET=your_client_secret_here
MICROSOFT_REDIRECT_URI=http://localhost:8000/auth/microsoft/callback
MICROSOFT_TENANT_ID=common
```

**Note:** Replace `your_client_id_here` and `your_client_secret_here` with your actual Azure AD credentials.

---

### 3. **Run Database Migrations**

This creates the database tables and adds the `microsoft_id` column:

```bash
php artisan migrate
```

**What this does:**
- Creates `users` table
- Creates `sessions` table
- Creates `password_reset_tokens` table
- **Adds `microsoft_id` column** to users table (for Microsoft login)

---

### 4. **Clear Configuration Cache** (if needed)

If you've updated configuration files:

```bash
php artisan config:clear
php artisan cache:clear
```

---

### 5. **Start the Development Server**

```bash
php artisan serve
```

This will start the server at `http://localhost:8000`

**Alternative:** You can specify a custom port:
```bash
php artisan serve --port=8080
```

---

### 6. **Build Frontend Assets** (if using Vite)

If you have frontend assets that need compiling:

```bash
npm install
npm run dev
```

Or for production:
```bash
npm run build
```

---

## Quick Start (All Commands Together)

If you're setting up from scratch, run these in order:

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file (if .env doesn't exist)
copy .env.example .env

# 3. Generate application key (if needed)
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Clear caches
php artisan config:clear
php artisan cache:clear

# 6. Start server
php artisan serve
```

---

## Testing the Microsoft Login

1. **Open your browser** and go to: `http://localhost:8000`
2. **Click "Sign in with Microsoft"** button
3. **You should be redirected** to Microsoft's login page
4. **After login**, you'll be redirected back to your dashboard

---

## Common Commands Reference

### Database Commands

```bash
# Run all pending migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations (WARNING: deletes all data)
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

### Cache Commands

```bash
# Clear all caches
php artisan optimize:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

### Server Commands

```bash
# Start development server
php artisan serve

# Start on specific host and port
php artisan serve --host=0.0.0.0 --port=8000
```

### Debug Commands

```bash
# Check configuration (if debug route exists)
# Visit: http://localhost:8000/debug-config

# Check authentication status
# Visit: http://localhost:8000/debug-auth
```

---

## Troubleshooting

### If migrations fail:

```bash
# Check if database exists
# For SQLite: database/database.sqlite should exist
# For MySQL/PostgreSQL: Check .env database settings

# Try fresh migration (WARNING: deletes data)
php artisan migrate:fresh
```

### If Microsoft login doesn't work:

1. **Check environment variables:**
   ```bash
   php artisan tinker
   >>> config('services.microsoft.client_id')
   >>> config('services.microsoft.client_secret')
   >>> config('services.microsoft.redirect')
   ```

2. **Check logs:**
   ```bash
   # View Laravel logs
   tail -f storage/logs/laravel.log
   ```

3. **Verify Azure AD configuration:**
   - Client ID is correct
   - Client Secret is correct
   - Redirect URI matches exactly (including http/https and port)

### If you get "Class not found" errors:

```bash
# Regenerate autoload files
composer dump-autoload
```

---

## Production Deployment Commands

For production, run these additional commands:

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Make sure APP_ENV=production in .env
# Make sure APP_DEBUG=false in .env
```

---

## Windows PowerShell Specific

If you're using PowerShell on Windows:

```powershell
# Use backticks for line continuation
php artisan serve `--port=8000

# Or use semicolons
php artisan config:clear; php artisan cache:clear
```

---

## Need Help?

If you encounter any issues:
1. Check `storage/logs/laravel.log` for error messages
2. Verify all environment variables are set correctly
3. Ensure database is properly configured
4. Make sure Azure AD app registration is complete

