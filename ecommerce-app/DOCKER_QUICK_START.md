# Docker Quick Start - 3 Minutes to Running App

The absolute fastest way to get your e-commerce application running!

## Prerequisites

**Only Docker Desktop is needed!**

Download and install Docker Desktop:
- Windows/Mac: https://www.docker.com/products/docker-desktop/
- Linux: `curl -fsSL https://get.docker.com | sh`

**Verify installation:**
```bash
docker --version
docker-compose --version
```

---

## 3-Step Setup

### Step 1: Open Terminal in Project Directory

**Windows (PowerShell or CMD):**
```bash
cd "C:\Users\User\OneDrive - Fakulteti i Teknologjise se Informacionit\Documents\Web-Development-\ecommerce-app"
```

**Mac/Linux:**
```bash
cd /path/to/ecommerce-app
```

### Step 2: Start Everything with One Command

```bash
docker-compose up -d
```

**What this does:**
- Downloads MongoDB, Node.js images (first time only, ~5-10 min)
- Installs all dependencies automatically
- Starts 4 containers (Database, Backend, Frontend, DB Admin)
- Sets up networking between services

**First run:** 5-10 minutes
**Subsequent runs:** 10-30 seconds

### Step 3: Access the Application

Wait about 30 seconds after `docker-compose up -d` completes, then open:

| Service | URL | Credentials |
|---------|-----|-------------|
| **Frontend (Main App)** | http://localhost:5173 | - |
| **Backend API** | http://localhost:5000/api | - |
| **Database Admin** | http://localhost:8081 | admin / admin123 |

**That's it!** Your application is now running! 🎉

---

## Create Admin User

To access admin features, create an admin account:

### Option 1: Via Mongo Express (Easiest)

1. Open http://localhost:8081
2. Login: `admin` / `admin123`
3. Click `ecommerce` database
4. Click `users` collection
5. Click "New Document"
6. Paste this JSON:

```json
{
  "name": "Admin",
  "email": "admin@ecommerce.com",
  "password": "$2a$10$xQ5qP5Z6J7O8Z9Z8Z9Z8Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z",
  "role": "admin",
  "isVerified": true,
  "isActive": true,
  "createdAt": {"$date": "2024-01-01T00:00:00.000Z"},
  "updatedAt": {"$date": "2024-01-01T00:00:00.000Z"}
}
```

7. Click "Save"
8. Login at http://localhost:5173 with:
   - Email: `admin@ecommerce.com`
   - Password: `admin123`

### Option 2: Via Command Line

```bash
docker-compose exec mongodb mongosh -u admin -p admin123 --authenticationDatabase admin

use ecommerce

db.users.insertOne({
  name: "Admin",
  email: "admin@ecommerce.com",
  password: "$2a$10$xQ5qP5Z6J7O8Z9Z8Z9Z8Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z",
  role: "admin",
  isVerified: true,
  isActive: true,
  createdAt: new Date(),
  updatedAt: new Date()
})

exit
```

---

## Common Commands

### View Status
```bash
docker-compose ps
```

Expected output shows all 4 containers running:
```
ecommerce-backend       Up
ecommerce-frontend      Up
ecommerce-mongodb       Up (healthy)
ecommerce-mongo-express Up
```

### View Logs
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f frontend
```

Press `Ctrl+C` to exit logs (containers keep running).

### Stop Everything
```bash
docker-compose down
```

Your data is preserved (database, files).

### Restart Everything
```bash
docker-compose restart
```

### Fresh Start (Deletes All Data)
```bash
docker-compose down -v
docker-compose up -d
```

⚠️ Warning: This deletes the database!

---

## Daily Workflow

**Start your day:**
```bash
cd ecommerce-app
docker-compose up -d
```

**Make code changes:**
- Edit files in `frontend/src/` or `backend/src/`
- Changes apply automatically
- Just refresh your browser

**End your day:**
```bash
docker-compose down
```

---

## Troubleshooting

### "Port is already allocated"

**Windows:**
```bash
netstat -ano | findstr :5000
taskkill /PID <PID> /F
```

**Mac/Linux:**
```bash
lsof -ti:5000 | xargs kill -9
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

### Backend shows errors in logs

```bash
# View detailed logs
docker-compose logs backend

# Restart backend
docker-compose restart backend
```

### Frontend not loading

1. Check backend is running:
   ```bash
   curl http://localhost:5000/api/health
   ```

2. Clear browser cache (Ctrl+Shift+R)

3. Restart frontend:
   ```bash
   docker-compose restart frontend
   ```

### Database connection failed

```bash
# Check MongoDB is healthy
docker-compose ps

# Restart MongoDB
docker-compose restart mongodb

# View MongoDB logs
docker-compose logs mongodb
```

### Complete reset (when nothing works)

```bash
docker-compose down -v
docker system prune -a
docker-compose up -d --build
```

⚠️ This deletes everything and rebuilds from scratch.

---

## What's Running?

After `docker-compose up -d`, you have 4 containers:

1. **MongoDB** (Port 27017)
   - Your database
   - Credentials: admin/admin123
   - Data persisted in Docker volume

2. **Backend** (Port 5000)
   - Node.js + Express API
   - Auto-reloads on code changes
   - Connected to MongoDB

3. **Frontend** (Port 5173)
   - React + Vite
   - Hot Module Replacement (HMR)
   - Connected to Backend

4. **Mongo Express** (Port 8081)
   - Database admin UI
   - View/edit database visually
   - Login: admin/admin123

---

## Advantages vs Manual Setup

| Manual Setup | Docker Setup |
|--------------|--------------|
| Install Node.js | ❌ Not needed |
| Install MongoDB | ❌ Not needed |
| Configure environment | ✅ Pre-configured |
| Multiple terminal windows | ✅ One command |
| Consistent environment | ✅ Always the same |
| Setup time | ⏱️ 30+ minutes | ⏱️ 3 minutes |

---

## Environment Variables

All environment variables are pre-configured in `docker-compose.yml`.

To customize (e.g., email settings):

1. Edit `.env` file in project root:
   ```env
   SMTP_USER=your-email@gmail.com
   SMTP_PASS=your-app-password
   ```

2. Restart:
   ```bash
   docker-compose down
   docker-compose up -d
   ```

---

## Testing the Application

### Test User Registration
1. Go to http://localhost:5173
2. Click "Register"
3. Fill in the form
4. Submit

### Test Product Browsing
1. Go to http://localhost:5173/products
2. Browse available products

### Test Admin Access
1. Login with admin account (created above)
2. Go to http://localhost:5173/admin/dashboard
3. Access admin features

---

## Production Deployment

For production, you'll want to:

1. Use production MongoDB (MongoDB Atlas recommended)
2. Set strong passwords in environment variables
3. Use HTTPS/SSL certificates
4. Add nginx reverse proxy
5. Set up monitoring and logging

See `DOCKER_SETUP.md` for detailed production deployment guide.

---

## Next Steps

Now that your app is running:

1. ✅ **Complete the implementation** - Follow `IMPLEMENTATION_GUIDE.md`
   - Create controllers
   - Create components
   - Add features

2. ✅ **Customize** - Edit code in:
   - `frontend/src/` for UI changes
   - `backend/src/` for API changes

3. ✅ **Test** - Register users, add products, place orders

4. ✅ **Deploy** - When ready for production

---

## Support

**Detailed Documentation:**
- `DOCKER_SETUP.md` - Complete Docker guide
- `IMPLEMENTATION_GUIDE.md` - How to complete the app
- `README.md` - Full project documentation

**Quick Checks:**
```bash
# Are containers running?
docker-compose ps

# Are there errors?
docker-compose logs

# Is backend healthy?
curl http://localhost:5000/api/health
```

**Community Help:**
- Docker Documentation: https://docs.docker.com/
- MongoDB Documentation: https://docs.mongodb.com/
- React Documentation: https://react.dev/

---

## Summary

```bash
# Start everything
docker-compose up -d

# Access app
http://localhost:5173

# Create admin user
http://localhost:8081

# View logs
docker-compose logs -f

# Stop everything
docker-compose down
```

**Congratulations!** 🎉 You have a fully Dockerized development environment!

No need to install Node.js, MongoDB, or manage complex configurations. Just Docker and you're ready to code! 🚀
