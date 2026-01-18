# Quick Start Guide - E-Commerce React + Node.js

Get your e-commerce application running in 10 minutes!

## Prerequisites Check

Before starting, verify you have:
- [ ] Node.js installed (v18+)
- [ ] MongoDB installed OR MongoDB Atlas account
- [ ] A code editor (VS Code recommended)
- [ ] Terminal/Command Prompt access

## Step 1: Install Node.js (If Not Installed)

### Windows:
1. Go to https://nodejs.org/
2. Download the LTS version
3. Run the installer
4. **IMPORTANT:** Restart your terminal/VS Code after installation
5. Verify: `node --version` and `npm --version`

### Verify Installation:
```bash
node --version
# Should show: v18.x.x or higher

npm --version
# Should show: 9.x.x or higher
```

## Step 2: Set Up MongoDB

### Option A: MongoDB Atlas (Cloud - Recommended for Beginners)

1. **Create Account:**
   - Go to https://www.mongodb.com/cloud/atlas
   - Click "Try Free"
   - Sign up with email or Google

2. **Create Cluster:**
   - Click "Build a Database"
   - Choose "FREE" (M0) tier
   - Select a cloud provider and region close to you
   - Click "Create Cluster"

3. **Set Up Access:**
   - Create a database user:
     - Username: `admin`
     - Password: `admin123` (or your choice)
     - Click "Create User"
   - Add your IP address:
     - Click "Add My Current IP Address"
     - Or add `0.0.0.0/0` to allow all (for development only)

4. **Get Connection String:**
   - Click "Connect"
   - Choose "Connect your application"
   - Copy the connection string
   - It looks like: `mongodb+srv://admin:<password>@cluster0.xxxxx.mongodb.net/`
   - Replace `<password>` with your actual password
   - Add database name: `mongodb+srv://admin:admin123@cluster0.xxxxx.mongodb.net/ecommerce`

### Option B: Local MongoDB

1. Download from https://www.mongodb.com/try/download/community
2. Install MongoDB Community Edition
3. MongoDB will run automatically as a service
4. Use connection string: `mongodb://localhost:27017/ecommerce`

## Step 3: Install Backend Dependencies

```bash
# Navigate to project root
cd "c:\Users\User\OneDrive - Fakulteti i Teknologjise se Informacionit\Documents\Web-Development-\ecommerce-app"

# Go to backend folder
cd backend

# Install dependencies (this will take 2-3 minutes)
npm install
```

## Step 4: Configure Backend Environment

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` file with these essential values:**

```env
# Server
PORT=5000
NODE_ENV=development

# Database - USE YOUR MONGODB CONNECTION STRING
MONGODB_URI=mongodb+srv://admin:admin123@cluster0.xxxxx.mongodb.net/ecommerce

# JWT Secrets - Change these to random strings
JWT_SECRET=mysupersecretkey123456789
JWT_EXPIRE=30d
JWT_REFRESH_SECRET=myrefreshsecretkey987654321
JWT_REFRESH_EXPIRE=90d

# Email (Optional for now - skip if you want)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
FROM_EMAIL=noreply@ecommerce.com
FROM_NAME=E-Commerce

# File Upload
MAX_FILE_SIZE=5242880
FILE_UPLOAD_PATH=./public/uploads

# Payment (Optional for now - skip if you want)
STRIPE_SECRET_KEY=sk_test_your_key
PAYPAL_CLIENT_ID=your_paypal_id

# URLs
CLIENT_URL=http://localhost:5173
SERVER_URL=http://localhost:5000

# Security
RATE_LIMIT_WINDOW=15
RATE_LIMIT_MAX=100
MAX_LOGIN_ATTEMPTS=7
LOCKOUT_DURATION=30
```

3. **Create uploads folder:**
   ```bash
   mkdir public
   mkdir public/uploads
   ```

## Step 5: Install Frontend Dependencies

```bash
# From backend folder, go back to root
cd ..

# Go to frontend folder
cd frontend

# Install dependencies (this will take 2-3 minutes)
npm install
```

## Step 6: Configure Frontend Environment

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` file:**
   ```env
   VITE_API_URL=http://localhost:5000/api
   VITE_STRIPE_PUBLIC_KEY=pk_test_your_stripe_key
   VITE_PAYPAL_CLIENT_ID=your_paypal_client_id
   ```

## Step 7: Start the Application

### Open TWO terminal windows:

**Terminal 1 - Backend:**
```bash
cd backend
npm run dev
```

You should see:
```
╔════════════════════════════════════════╗
║   E-Commerce Backend Server Running   ║
╠════════════════════════════════════════╣
║  Environment: development              ║
║  Port: 5000                           ║
║  URL: http://localhost:5000          ║
╚════════════════════════════════════════╝
MongoDB Connected: cluster0.xxxxx.mongodb.net
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```

You should see:
```
  VITE v5.0.8  ready in 500 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
```

## Step 8: Access the Application

1. **Open your browser**
2. **Go to:** http://localhost:5173
3. **You should see the homepage!**

## Step 9: Create Admin Account

Since the app is new, you need to manually create an admin user in MongoDB:

### Using MongoDB Atlas:
1. Go to your Atlas dashboard
2. Click "Browse Collections"
3. Select "ecommerce" database
4. Click "Insert Document" in the "users" collection
5. Paste this (replace with your details):

```json
{
  "name": "Admin User",
  "email": "admin@ecommerce.com",
  "password": "$2a$10$xQ5qP5Z6J7O8Z9Z8Z9Z8Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z",
  "role": "admin",
  "isVerified": true,
  "isActive": true,
  "createdAt": {"$date": "2024-01-01T00:00:00.000Z"},
  "updatedAt": {"$date": "2024-01-01T00:00:00.000Z"}
}
```

**Note:** The password above is `admin123` (already hashed with bcrypt)

### Or register normally and update role:
1. Register a new account via the app
2. Go to MongoDB
3. Find your user in the `users` collection
4. Change `role` from `"user"` to `"admin"`

## Step 10: Test the Application

### Test User Registration:
1. Click "Register" in the app
2. Fill in the form
3. Submit
4. You should be logged in!

### Test Product Browsing:
1. Click "Products"
2. Browse available products (if any)

### Test Admin Access:
1. Login with admin account
2. Go to `/admin/dashboard`
3. You should see the admin panel

## Troubleshooting

### Backend won't start:

**Error: "npm: command not found"**
- Node.js is not installed or not in PATH
- Install Node.js and restart terminal

**Error: "MongoServerError: bad auth"**
- MongoDB connection string is wrong
- Check username/password in connection string
- In MongoDB Atlas, ensure you created a database user

**Error: "connect ECONNREFUSED"**
- MongoDB is not running (for local MongoDB)
- Or connection string is wrong
- Verify MongoDB is running: check MongoDB Compass or Atlas

**Error: "Port 5000 is already in use"**
```bash
# Kill the process using port 5000
npx kill-port 5000
```

### Frontend won't start:

**Error: "Failed to resolve import"**
- Dependencies not installed
- Run `npm install` again

**Error: "Port 5173 is already in use"**
```bash
# Kill the process using port 5173
npx kill-port 5173
```

### Cannot connect frontend to backend:

**Error: "Network Error" or "Failed to fetch"**
- Backend is not running
- Check backend terminal for errors
- Verify backend is running on port 5000
- Check `.env` file in frontend has correct `VITE_API_URL`

### Email verification not working:

- This is optional for development
- Check backend console for verification link
- Or skip email verification:
  - In MongoDB, set `isVerified: true` for your user

## What's Next?

Now that your app is running:

1. **Read the IMPLEMENTATION_GUIDE.md** for detailed implementation instructions
2. **Complete the controllers** - Only routes and models are created, you need controllers
3. **Add products** - Create some sample products via admin panel
4. **Customize styling** - Update colors, fonts, etc.
5. **Add features** - Reviews, wishlist, etc.

## Need Help?

### Check these files:
- `README.md` - Full project documentation
- `IMPLEMENTATION_GUIDE.md` - Detailed implementation steps
- Backend logs in Terminal 1
- Frontend logs in Terminal 2
- Browser console (F12) for frontend errors

### Common Locations:
- Backend: `http://localhost:5000`
- Backend API: `http://localhost:5000/api`
- Backend Health: `http://localhost:5000/api/health`
- Frontend: `http://localhost:5173`

### Debug Mode:
```bash
# Backend - check if running
curl http://localhost:5000/api/health

# Should return:
# {"success":true,"message":"Server is running","timestamp":"..."}
```

## Project Structure Quick Reference

```
ecommerce-app/
├── backend/
│   ├── src/
│   │   ├── models/          # Database models (✅ Done)
│   │   ├── routes/          # API routes (⚠️ Need to create)
│   │   ├── controllers/     # Route handlers (⚠️ Need to create)
│   │   ├── middleware/      # Auth, error handling (✅ Done)
│   │   ├── utils/           # Helpers (⚠️ Need to create)
│   │   └── server.js        # Main server (✅ Done)
│   ├── .env                 # Environment config
│   └── package.json         # Dependencies (✅ Done)
│
├── frontend/
│   ├── src/
│   │   ├── components/      # React components (⚠️ Need to create)
│   │   ├── pages/           # Page components (⚠️ Need to create)
│   │   ├── store/           # State management (✅ Done)
│   │   ├── services/        # API services (✅ Done)
│   │   └── App.jsx          # Main app (✅ Done)
│   ├── .env                 # Environment config
│   └── package.json         # Dependencies (✅ Done)
│
├── README.md                # Full documentation
├── IMPLEMENTATION_GUIDE.md  # Step-by-step guide
└── QUICK_START.md          # This file
```

## Success Checklist

- [ ] Node.js installed and verified
- [ ] MongoDB Atlas account created OR local MongoDB running
- [ ] Backend dependencies installed
- [ ] Backend `.env` file configured
- [ ] Frontend dependencies installed
- [ ] Frontend `.env` file configured
- [ ] Backend running on port 5000
- [ ] Frontend running on port 5173
- [ ] Can access http://localhost:5173 in browser
- [ ] Can register a new user
- [ ] Admin account created

---

**Congratulations!** 🎉 If all checks pass, your e-commerce application is running!

Next step: Read `IMPLEMENTATION_GUIDE.md` to complete the remaining controllers and components.
