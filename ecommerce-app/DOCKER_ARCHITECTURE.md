# Docker Architecture - E-Commerce Application

Visual guide to understanding how the Docker containers work together.

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         Your Computer                            │
│                                                                  │
│  ┌────────────────────────────────────────────────────────┐   │
│  │              Docker Desktop                             │   │
│  │                                                          │   │
│  │  ┌──────────────────────────────────────────────────┐  │   │
│  │  │         ecommerce-network (Bridge)               │  │   │
│  │  │                                                    │  │   │
│  │  │   ┌─────────────┐        ┌─────────────┐        │  │   │
│  │  │   │  Frontend   │───────→│  Backend    │        │  │   │
│  │  │   │  (React)    │        │  (Node.js)  │        │  │   │
│  │  │   │  Port: 5173 │        │  Port: 5000 │        │  │   │
│  │  │   └─────────────┘        └──────┬──────┘        │  │   │
│  │  │                                  │                 │  │   │
│  │  │                                  ↓                 │  │   │
│  │  │                          ┌─────────────┐          │  │   │
│  │  │                          │  MongoDB    │          │  │   │
│  │  │                          │  (Database) │          │  │   │
│  │  │                          │  Port: 27017│          │  │   │
│  │  │                          └──────┬──────┘          │  │   │
│  │  │                                  ↑                 │  │   │
│  │  │                          ┌───────┴──────┐         │  │   │
│  │  │                          │ Mongo Express│         │  │   │
│  │  │                          │  (Admin UI)  │         │  │   │
│  │  │                          │  Port: 8081  │         │  │   │
│  │  │                          └──────────────┘         │  │   │
│  │  │                                                    │  │   │
│  │  └────────────────────────────────────────────────────┘  │   │
│  │                                                          │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Browser: http://localhost:5173 ─────────────┐                 │
│  Browser: http://localhost:5000 ─────────┐   │                 │
│  Browser: http://localhost:8081 ─────┐   │   │                 │
│                                       │   │   │                 │
└───────────────────────────────────────┼───┼───┼─────────────────┘
                                        │   │   │
                                        ↓   ↓   ↓
                                    Accessed via Browser
```

## Container Details

### 1. Frontend Container (ecommerce-frontend)

```
┌─────────────────────────────────────┐
│   Frontend Container                │
├─────────────────────────────────────┤
│ Image: node:18-alpine               │
│ Port: 5173 → 5173                   │
├─────────────────────────────────────┤
│ Contains:                            │
│ ✓ React 18                          │
│ ✓ Vite (build tool)                 │
│ ✓ Tailwind CSS                      │
│ ✓ React Router                      │
│ ✓ Zustand (state)                   │
│ ✓ Axios (HTTP)                      │
├─────────────────────────────────────┤
│ Volumes:                             │
│ • ./frontend/src → /app/src         │
│ • node_modules (Docker volume)      │
├─────────────────────────────────────┤
│ Command: npm run dev -- --host      │
│ Hot Reload: ✓ Yes                   │
└─────────────────────────────────────┘
```

**What it does:**
- Serves the React application
- Watches for file changes
- Hot reloads when you edit code
- Makes API calls to backend

**Access:** http://localhost:5173

---

### 2. Backend Container (ecommerce-backend)

```
┌─────────────────────────────────────┐
│   Backend Container                 │
├─────────────────────────────────────┤
│ Image: node:18-alpine               │
│ Port: 5000 → 5000                   │
├─────────────────────────────────────┤
│ Contains:                            │
│ ✓ Node.js 18                        │
│ ✓ Express.js                        │
│ ✓ Mongoose (MongoDB ODM)            │
│ ✓ JWT authentication                │
│ ✓ bcrypt (passwords)                │
│ ✓ Multer (file uploads)             │
├─────────────────────────────────────┤
│ Volumes:                             │
│ • ./backend/src → /app/src          │
│ • ./backend/public → /app/public    │
│ • node_modules (Docker volume)      │
├─────────────────────────────────────┤
│ Environment:                         │
│ • MONGODB_URI=mongodb://...          │
│ • JWT_SECRET=...                     │
│ • CLIENT_URL=http://localhost:5173  │
├─────────────────────────────────────┤
│ Command: npm run dev                │
│ Auto-restart: ✓ Yes (nodemon)       │
└─────────────────────────────────────┘
```

**What it does:**
- Provides REST API endpoints
- Handles authentication
- Manages business logic
- Connects to MongoDB
- Serves uploaded files

**Access:** http://localhost:5000/api

---

### 3. MongoDB Container (ecommerce-mongodb)

```
┌─────────────────────────────────────┐
│   MongoDB Container                 │
├─────────────────────────────────────┤
│ Image: mongo:7.0                    │
│ Port: 27017 → 27017                 │
├─────────────────────────────────────┤
│ Credentials:                         │
│ • Username: admin                   │
│ • Password: admin123                │
│ • Database: ecommerce               │
├─────────────────────────────────────┤
│ Collections:                         │
│ • users                             │
│ • products                          │
│ • categories                        │
│ • carts                             │
│ • orders                            │
│ • contacts                          │
├─────────────────────────────────────┤
│ Volumes:                             │
│ • mongodb_data → /data/db           │
│ • mongodb_config → /data/configdb   │
├─────────────────────────────────────┤
│ Data Persistence: ✓ Yes             │
│ Survives restarts: ✓ Yes            │
└─────────────────────────────────────┘
```

**What it does:**
- Stores all application data
- Persists data in Docker volumes
- Data survives container restarts
- Accessed by backend and Mongo Express

**Access:** Internal (mongodb:27017)

---

### 4. Mongo Express Container (ecommerce-mongo-express)

```
┌─────────────────────────────────────┐
│   Mongo Express Container           │
├─────────────────────────────────────┤
│ Image: mongo-express:latest         │
│ Port: 8081 → 8081                   │
├─────────────────────────────────────┤
│ Purpose:                             │
│ • Visual database management        │
│ • Browse collections                │
│ • Edit documents                    │
│ • Create/delete data                │
├─────────────────────────────────────┤
│ Login:                               │
│ • Username: admin                   │
│ • Password: admin123                │
├─────────────────────────────────────┤
│ Connected to:                        │
│ • MongoDB container                 │
│ • URI: mongodb://admin:...@mongodb  │
└─────────────────────────────────────┘
```

**What it does:**
- Provides web UI for MongoDB
- Lets you view/edit database
- Useful for debugging
- Create admin users manually

**Access:** http://localhost:8081

---

## Network Architecture

```
┌─────────────────────────────────────────────────────────┐
│              ecommerce-network (Bridge Network)         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Container Name          Internal Hostname              │
│  ──────────────         ──────────────────              │
│  ecommerce-frontend  →  frontend                        │
│  ecommerce-backend   →  backend                         │
│  ecommerce-mongodb   →  mongodb                         │
│  ecommerce-mongo-express → mongo-express                │
│                                                          │
│  Communication:                                          │
│  • Frontend calls: http://backend:5000                  │
│  • Backend calls: mongodb://mongodb:27017               │
│  • Mongo Express calls: mongodb://mongodb:27017         │
│                                                          │
│  External Access:                                        │
│  • localhost:5173 → frontend:5173                       │
│  • localhost:5000 → backend:5000                        │
│  • localhost:8081 → mongo-express:8081                  │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Data Flow

### User Registration Flow

```
1. User fills form in Browser
   ↓
2. Frontend (React) → POST http://localhost:5000/api/auth/register
   ↓
3. Backend (Express) receives request
   ↓
4. Backend validates data
   ↓
5. Backend hashes password (bcrypt)
   ↓
6. Backend → MongoDB saves user
   ↓
7. MongoDB stores in 'users' collection
   ↓
8. MongoDB → Backend (success)
   ↓
9. Backend → Frontend (JWT token)
   ↓
10. Frontend stores token in localStorage
   ↓
11. Frontend redirects to dashboard
```

### Product Browsing Flow

```
1. User opens products page
   ↓
2. Frontend → GET http://localhost:5000/api/products
   ↓
3. Backend → MongoDB finds products
   ↓
4. MongoDB → Backend (product list)
   ↓
5. Backend → Frontend (JSON data)
   ↓
6. Frontend renders product cards
   ↓
7. User sees products in browser
```

### Shopping Cart Flow

```
1. User clicks "Add to Cart"
   ↓
2. Frontend updates Zustand state (local)
   ↓
3. Frontend → POST http://localhost:5000/api/cart
   ↓
4. Backend → MongoDB updates/creates cart
   ↓
5. MongoDB persists cart data
   ↓
6. Backend → Frontend (success)
   ↓
7. Frontend shows cart count badge
```

---

## Volume Management

### What are Volumes?

Docker volumes are persistent storage that survives container restarts.

```
┌─────────────────────────────────────────┐
│         Docker Volumes                  │
├─────────────────────────────────────────┤
│                                          │
│  mongodb_data                            │
│  ├── Database files                     │
│  ├── Collections data                   │
│  └── Indexes                             │
│  Size: Grows with data                  │
│  Persists: ✓ Yes                        │
│                                          │
│  mongodb_config                          │
│  ├── MongoDB configuration              │
│  └── System files                       │
│  Size: Small (~10MB)                    │
│  Persists: ✓ Yes                        │
│                                          │
│  backend_node_modules                   │
│  ├── Backend dependencies               │
│  └── npm packages                       │
│  Size: ~200MB                           │
│  Rebuilt: On package.json changes       │
│                                          │
│  frontend_node_modules                  │
│  ├── Frontend dependencies              │
│  └── npm packages                       │
│  Size: ~300MB                           │
│  Rebuilt: On package.json changes       │
│                                          │
└─────────────────────────────────────────┘
```

### View Volumes

```bash
docker volume ls
```

### Inspect Volume

```bash
docker volume inspect ecommerce-app_mongodb_data
```

### Remove Volumes (⚠️ Deletes data!)

```bash
docker-compose down -v
```

---

## Port Mappings

```
Host Port → Container Port   Service
────────────────────────────────────────
5173      → 5173             Frontend (React)
5000      → 5000             Backend (API)
27017     → 27017            MongoDB (Database)
8081      → 8081             Mongo Express (UI)
```

**What this means:**
- Accessing `localhost:5173` on your computer reaches port 5173 in the frontend container
- Containers can talk to each other using internal hostnames (e.g., `mongodb:27017`)
- You can only access services through the exposed ports

---

## Resource Usage

Typical resource consumption:

```
Container          CPU    Memory   Disk
──────────────────────────────────────────
Frontend           5-10%  200MB    300MB (node_modules)
Backend            5-10%  150MB    200MB (node_modules)
MongoDB            2-5%   100MB    Grows with data
Mongo Express      1-2%   50MB     50MB
──────────────────────────────────────────
Total (idle)       ~15%   500MB    ~1GB
Total (active)     ~30%   800MB    ~1GB + data
```

---

## Development Workflow

### File Changes

```
1. Edit file in ./frontend/src/App.jsx
   ↓
2. Docker detects change (volume mount)
   ↓
3. Vite hot reloads
   ↓
4. Browser updates instantly
   ✓ No container restart needed
```

### Package Installation

```
1. Edit ./frontend/package.json
   ↓
2. Run: docker-compose up -d --build frontend
   ↓
3. Docker rebuilds container
   ↓
4. npm install runs
   ↓
5. Container restarts with new packages
```

---

## Lifecycle Commands

### Start

```bash
docker-compose up -d

# What happens:
1. Starts MongoDB (waits for healthy)
2. Starts Backend (connects to MongoDB)
3. Starts Frontend (connects to Backend)
4. Starts Mongo Express (connects to MongoDB)
```

### Stop

```bash
docker-compose down

# What happens:
1. Stops all containers
2. Removes containers
3. Keeps volumes (data persists)
4. Keeps network
```

### Restart

```bash
docker-compose restart

# What happens:
1. Stops containers (in reverse order)
2. Starts containers (in dependency order)
3. Keeps volumes and data
```

### Full Reset

```bash
docker-compose down -v

# What happens:
1. Stops containers
2. Removes containers
3. Deletes volumes (⚠️ DATA LOSS)
4. Fresh start next time
```

---

## Health Checks

### MongoDB Health Check

```yaml
healthcheck:
  test: echo 'db.runCommand("ping").ok' | mongosh localhost:27017/ecommerce --quiet
  interval: 10s
  timeout: 5s
  retries: 5
```

**What it does:**
- Checks MongoDB every 10 seconds
- Backend waits for "healthy" status
- Prevents connection errors on startup

### Check Health Status

```bash
docker-compose ps
```

Look for "(healthy)" next to mongodb.

---

## Security Considerations

### Development (Current Setup)

```
✓ Isolated network (containers only)
✓ Environment variables in docker-compose
⚠️ Default passwords (change for production)
⚠️ Ports exposed to localhost only
```

### Production Recommendations

```
✓ Strong passwords (use secrets)
✓ SSL/TLS for MongoDB
✓ Nginx reverse proxy
✓ Rate limiting
✓ Firewall rules
✓ Container security scanning
✓ No exposed MongoDB port
✓ Environment files in secrets manager
```

---

## Troubleshooting

### View All Container Logs

```bash
docker-compose logs -f
```

### Check Container Status

```bash
docker-compose ps
```

### Access Container Shell

```bash
# Backend
docker-compose exec backend sh

# Frontend
docker-compose exec frontend sh

# MongoDB
docker-compose exec mongodb mongosh -u admin -p admin123 --authenticationDatabase admin
```

### Restart Single Service

```bash
docker-compose restart backend
```

### View Resource Usage

```bash
docker stats
```

---

## Summary

**4 Containers working together:**
1. **Frontend** - React UI (Port 5173)
2. **Backend** - Node.js API (Port 5000)
3. **MongoDB** - Database (Port 27017)
4. **Mongo Express** - DB Admin (Port 8081)

**Connected via:**
- Docker bridge network (ecommerce-network)
- Volume mounts for data persistence
- Environment variables for configuration

**One command to rule them all:**
```bash
docker-compose up -d
```

**That's the magic of Docker!** 🐳✨
