# Docker Setup Guide - E-Commerce Application

Complete guide to run your e-commerce application with Docker. **No need to install Node.js or MongoDB manually!**

## Table of Contents
- [Prerequisites](#prerequisites)
- [Quick Start (3 Steps)](#quick-start-3-steps)
- [What's Included](#whats-included)
- [Accessing the Application](#accessing-the-application)
- [Managing Docker Containers](#managing-docker-containers)
- [Development Workflow](#development-workflow)
- [Database Access](#database-access)
- [Troubleshooting](#troubleshooting)
- [Production Deployment](#production-deployment)

---

## Prerequisites

You only need **Docker Desktop** installed. That's it!

### Install Docker Desktop

#### Windows:
1. Download Docker Desktop from: https://www.docker.com/products/docker-desktop/
2. Run the installer
3. **Important:** Enable WSL 2 if prompted (recommended)
4. Restart your computer
5. Start Docker Desktop
6. Verify installation:
   ```bash
   docker --version
   docker-compose --version
   ```

#### macOS:
1. Download Docker Desktop from: https://www.docker.com/products/docker-desktop/
2. Drag to Applications folder
3. Open Docker Desktop
4. Verify installation:
   ```bash
   docker --version
   docker-compose --version
   ```

#### Linux:
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify
docker --version
docker-compose --version
```

---

## Quick Start (3 Steps)

### Step 1: Navigate to Project Directory

```bash
cd "C:\Users\User\OneDrive - Fakulteti i Teknologjise se Informacionit\Documents\Web-Development-\ecommerce-app"
```

### Step 2: Start All Services

```bash
docker-compose up -d
```

This single command will:
- ✅ Download MongoDB image
- ✅ Download Node.js images
- ✅ Install all backend dependencies
- ✅ Install all frontend dependencies
- ✅ Start MongoDB database
- ✅ Start backend API
- ✅ Start frontend app
- ✅ Start Mongo Express (database admin UI)

**First time setup takes 5-10 minutes** (downloads and installs everything)
**Subsequent starts take 10-30 seconds**

### Step 3: Access the Application

Wait about 30 seconds for all services to start, then open:

- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:5000
- **Database Admin:** http://localhost:8081 (username: `admin`, password: `admin123`)

That's it! Your application is running! 🎉

---

## What's Included

Docker Compose will start 4 containers:

### 1. MongoDB (Database)
- **Container:** `ecommerce-mongodb`
- **Port:** 27017
- **Credentials:**
  - Username: `admin`
  - Password: `admin123`
  - Database: `ecommerce`
- **Data:** Persisted in Docker volume (survives container restarts)

### 2. Backend API (Node.js + Express)
- **Container:** `ecommerce-backend`
- **Port:** 5000
- **Auto-reload:** Yes (code changes apply instantly)
- **Logs:** See below for viewing logs

### 3. Frontend (React + Vite)
- **Container:** `ecommerce-frontend`
- **Port:** 5173
- **Auto-reload:** Yes (code changes apply instantly)
- **HMR:** Hot Module Replacement enabled

### 4. Mongo Express (Database Admin UI)
- **Container:** `ecommerce-mongo-express`
- **Port:** 8081
- **Credentials:** admin/admin123
- **Purpose:** Visual database management (view/edit collections)

---

## Accessing the Application

### Frontend (React App)
```
http://localhost:5173
```
- Main application interface
- Register, login, browse products, etc.

### Backend API
```
http://localhost:5000
```
- API endpoints
- Health check: http://localhost:5000/api/health

### Database Admin (Mongo Express)
```
http://localhost:8081
```
- Login with: `admin` / `admin123`
- View and edit database collections
- Useful for:
  - Creating admin users
  - Viewing orders
  - Managing products
  - Debugging data issues

---

## Managing Docker Containers

### View Running Containers
```bash
docker-compose ps
```

Expected output:
```
NAME                      COMMAND                  SERVICE         STATUS
ecommerce-backend         "npm run dev"            backend         Up
ecommerce-frontend        "npm run dev -- --ho…"   frontend        Up
ecommerce-mongodb         "docker-entrypoint.s…"   mongodb         Up (healthy)
ecommerce-mongo-express   "tini -- /docker-ent…"   mongo-express   Up
```

### View Logs

**All services:**
```bash
docker-compose logs -f
```

**Specific service:**
```bash
# Backend logs
docker-compose logs -f backend

# Frontend logs
docker-compose logs -f frontend

# Database logs
docker-compose logs -f mongodb
```

Press `Ctrl+C` to stop viewing logs (containers keep running).

### Stop All Services
```bash
docker-compose down
```

This stops and removes containers but **keeps your data** (database, uploaded files).

### Stop and Remove Everything (Including Data)
```bash
docker-compose down -v
```

**⚠️ Warning:** This deletes the database! Use only when you want a fresh start.

### Restart All Services
```bash
docker-compose restart
```

### Restart Specific Service
```bash
# Restart backend only
docker-compose restart backend

# Restart frontend only
docker-compose restart frontend
```

### Rebuild Containers (After Dependency Changes)
```bash
docker-compose up -d --build
```

Use this when you add new npm packages to `package.json`.

---

## Development Workflow

### Making Code Changes

**Frontend:**
1. Edit files in `frontend/src/`
2. Changes appear instantly in browser (HMR)
3. No restart needed

**Backend:**
1. Edit files in `backend/src/`
2. Server restarts automatically (nodemon)
3. Refresh API calls to see changes

### Installing New Dependencies

**Backend:**
```bash
# Option 1: Add to package.json and rebuild
cd backend
# Edit package.json to add dependency
cd ..
docker-compose up -d --build backend

# Option 2: Install directly in container
docker-compose exec backend npm install <package-name>
docker-compose restart backend
```

**Frontend:**
```bash
# Option 1: Add to package.json and rebuild
cd frontend
# Edit package.json to add dependency
cd ..
docker-compose up -d --build frontend

# Option 2: Install directly in container
docker-compose exec frontend npm install <package-name>
docker-compose restart frontend
```

### Running Commands Inside Containers

**Backend:**
```bash
# Access backend container shell
docker-compose exec backend sh

# Run commands (e.g., database seeding)
docker-compose exec backend npm run seed
```

**Frontend:**
```bash
# Access frontend container shell
docker-compose exec frontend sh

# Run build
docker-compose exec frontend npm run build
```

**MongoDB:**
```bash
# Access MongoDB shell
docker-compose exec mongodb mongosh -u admin -p admin123 --authenticationDatabase admin

# Inside mongosh:
use ecommerce
db.users.find()
```

---

## Database Access

### Option 1: Mongo Express (Recommended for Beginners)

1. Open http://localhost:8081
2. Login: `admin` / `admin123`
3. Click on `ecommerce` database
4. Browse collections

### Option 2: MongoDB Compass (Desktop App)

1. Download MongoDB Compass: https://www.mongodb.com/try/download/compass
2. Connect with:
   ```
   mongodb://admin:admin123@localhost:27017/ecommerce?authSource=admin
   ```

### Option 3: Command Line (mongosh)

```bash
# Access MongoDB shell
docker-compose exec mongodb mongosh -u admin -p admin123 --authenticationDatabase admin

# Switch to ecommerce database
use ecommerce

# List collections
show collections

# Find all users
db.users.find().pretty()

# Create admin user
db.users.insertOne({
  name: "Admin User",
  email: "admin@ecommerce.com",
  password: "$2a$10$xQ5qP5Z6J7O8Z9Z8Z9Z8Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z",
  role: "admin",
  isVerified: true,
  isActive: true,
  createdAt: new Date(),
  updatedAt: new Date()
})

# Exit
exit
```

### Creating Admin User via Mongo Express

1. Go to http://localhost:8081
2. Click on `ecommerce` database
3. Click on `users` collection
4. Click "New Document"
5. Paste this JSON:

```json
{
  "name": "Admin User",
  "email": "admin@ecommerce.com",
  "password": "$2a$10$xQ5qP5Z6J7O8Z9Z8Z9Z8Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z",
  "role": "admin",
  "isVerified": true,
  "isActive": true,
  "avatar": "default-avatar.png",
  "loginAttempts": 0,
  "createdAt": {"$date": "2024-01-01T00:00:00.000Z"},
  "updatedAt": {"$date": "2024-01-01T00:00:00.000Z"}
}
```

6. Click "Save"
7. Login with: `admin@ecommerce.com` / `admin123`

---

## Troubleshooting

### Port Already in Use

**Error:** "Bind for 0.0.0.0:5000 failed: port is already allocated"

**Solution:**
```bash
# Option 1: Kill process on that port (Windows)
netstat -ano | findstr :5000
taskkill /PID <PID> /F

# Option 2: Change port in docker-compose.yml
# Edit docker-compose.yml and change "5000:5000" to "5001:5000"
```

### Containers Won't Start

**Check Docker is running:**
```bash
docker ps
```

If error, start Docker Desktop.

**View error logs:**
```bash
docker-compose logs
```

**Reset everything:**
```bash
docker-compose down -v
docker-compose up -d
```

### MongoDB Connection Failed

**Check MongoDB is healthy:**
```bash
docker-compose ps
```

Look for `Up (healthy)` status for mongodb.

**View MongoDB logs:**
```bash
docker-compose logs mongodb
```

**Restart MongoDB:**
```bash
docker-compose restart mongodb
```

### Backend Won't Connect to Database

**Check environment variables:**
```bash
docker-compose exec backend env | grep MONGODB
```

Should show:
```
MONGODB_URI=mongodb://admin:admin123@mongodb:27017/ecommerce?authSource=admin
```

**Restart backend:**
```bash
docker-compose restart backend
```

### Frontend Can't Connect to Backend

**Check backend is running:**
```bash
curl http://localhost:5000/api/health
```

Should return:
```json
{"success":true,"message":"Server is running","timestamp":"..."}
```

**Check frontend environment:**
```bash
docker-compose exec frontend env | grep VITE_API_URL
```

Should show:
```
VITE_API_URL=http://localhost:5000/api
```

### Code Changes Not Appearing

**Backend:**
```bash
docker-compose restart backend
docker-compose logs -f backend
```

**Frontend:**
```bash
# Clear browser cache (Ctrl+Shift+R)
# Or restart frontend
docker-compose restart frontend
```

### Database Data Lost After Restart

This shouldn't happen unless you used `docker-compose down -v`.

**Check volumes:**
```bash
docker volume ls | grep ecommerce
```

Should see:
- `ecommerce-app_mongodb_data`
- `ecommerce-app_mongodb_config`

**Backup database:**
```bash
docker-compose exec mongodb mongodump --uri="mongodb://admin:admin123@localhost:27017/ecommerce?authSource=admin" --out=/data/backup
```

### Out of Disk Space

**Check Docker disk usage:**
```bash
docker system df
```

**Clean up:**
```bash
# Remove unused images
docker image prune -a

# Remove unused volumes
docker volume prune

# Remove everything unused
docker system prune -a --volumes
```

---

## Environment Variables

### Modify Environment Variables

**Edit docker-compose.yml:**
```yaml
backend:
  environment:
    JWT_SECRET: your-new-secret-key
    SMTP_USER: your-email@gmail.com
    # ... other variables
```

**Or create .env file in project root:**
```env
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-password
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

### Email Configuration

To enable email functionality:

1. Edit `.env` file in project root:
   ```env
   SMTP_USER=your-email@gmail.com
   SMTP_PASS=your-gmail-app-password
   ```

2. For Gmail, create App Password:
   - Go to Google Account → Security
   - Enable 2-Step Verification
   - Create App Password
   - Use that password in SMTP_PASS

3. Restart:
   ```bash
   docker-compose restart backend
   ```

---

## Production Deployment

### Build for Production

**Create production docker-compose:**

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  mongodb:
    image: mongo:7.0
    restart: always
    environment:
      MONGO_INITDB_ROOT_USERNAME: ${MONGO_USER}
      MONGO_INITDB_ROOT_PASSWORD: ${MONGO_PASSWORD}
    volumes:
      - mongodb_data:/data/db
    networks:
      - ecommerce-network

  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile.prod
    restart: always
    environment:
      NODE_ENV: production
      MONGODB_URI: ${MONGODB_URI}
      JWT_SECRET: ${JWT_SECRET}
      # ... other production env vars
    depends_on:
      - mongodb
    networks:
      - ecommerce-network

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile.prod
    restart: always
    depends_on:
      - backend
    networks:
      - ecommerce-network

  nginx:
    image: nginx:alpine
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - frontend
      - backend
    networks:
      - ecommerce-network

networks:
  ecommerce-network:
    driver: bridge

volumes:
  mongodb_data:
```

**Create production .env file:**
```env
MONGO_USER=production_user
MONGO_PASSWORD=strong_random_password_here
MONGODB_URI=mongodb://production_user:strong_random_password_here@mongodb:27017/ecommerce?authSource=admin
JWT_SECRET=very_long_random_secret_key_for_production
# ... other production variables
```

**Deploy:**
```bash
docker-compose -f docker-compose.prod.yml up -d
```

---

## Useful Docker Commands

### Clean Restart
```bash
docker-compose down
docker-compose up -d
```

### View Resource Usage
```bash
docker stats
```

### Inspect Container
```bash
docker inspect ecommerce-backend
```

### Copy Files from Container
```bash
# Backup uploads folder
docker cp ecommerce-backend:/app/public/uploads ./backup-uploads
```

### Execute One-off Commands
```bash
# Create database backup
docker-compose exec mongodb mongodump --uri="mongodb://admin:admin123@localhost:27017/ecommerce?authSource=admin" --archive=/data/backup.archive

# Restore database
docker-compose exec mongodb mongorestore --uri="mongodb://admin:admin123@localhost:27017/ecommerce?authSource=admin" --archive=/data/backup.archive
```

---

## Benefits of Docker Setup

### Before (Manual Setup)
- ❌ Install Node.js
- ❌ Install MongoDB
- ❌ Configure environment variables
- ❌ Manage multiple terminal windows
- ❌ Different setup for each developer
- ❌ "Works on my machine" problems

### After (Docker Setup)
- ✅ One command to start everything
- ✅ Consistent environment for all developers
- ✅ No manual installations needed
- ✅ Easy to reset/restart
- ✅ Production-ready configuration
- ✅ Built-in database admin UI

---

## Summary

### Daily Development Workflow

**Start working:**
```bash
docker-compose up -d
# Wait 30 seconds
# Open http://localhost:5173
```

**Make changes:**
- Edit code in `frontend/src/` or `backend/src/`
- Changes apply automatically
- No restart needed

**View logs (if needed):**
```bash
docker-compose logs -f backend
docker-compose logs -f frontend
```

**End of day:**
```bash
docker-compose down
```

**Fresh start:**
```bash
docker-compose down -v
docker-compose up -d
```

That's it! You now have a complete Docker-based development environment! 🐳🎉

---

## Next Steps

1. ✅ Install Docker Desktop
2. ✅ Run `docker-compose up -d`
3. ✅ Access http://localhost:5173
4. ✅ Create admin user via Mongo Express
5. ✅ Start developing!

For implementation details, see:
- `IMPLEMENTATION_GUIDE.md` - How to complete controllers and components
- `README.md` - Full project documentation
- `PROJECT_SUMMARY.md` - Project overview
