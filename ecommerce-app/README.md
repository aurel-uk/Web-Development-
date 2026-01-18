# E-Commerce Platform - React + Node.js

Modern full-stack e-commerce application built with React (Vite), Node.js, Express, and MongoDB.

## 🚀 Quick Start Options

Choose your preferred setup method:

### Option 1: Docker (Recommended - Easiest)
**No Node.js or MongoDB installation needed!**

```bash
# 1. Install Docker Desktop from https://www.docker.com/products/docker-desktop/
# 2. Run one command:
docker-compose up -d

# 3. Access: http://localhost:5173
```

📖 **See [DOCKER_QUICK_START.md](DOCKER_QUICK_START.md)** for detailed Docker setup (3 minutes)

### Option 2: Manual Setup
**Requires Node.js and MongoDB installation**

📖 **See [QUICK_START.md](QUICK_START.md)** for manual installation guide (10 minutes)

## Features

### User Features
- User registration with email verification
- Login/Logout with JWT authentication
- Password reset functionality
- Profile management with image upload
- Shopping cart management
- Order placement and tracking
- Product browsing with filters and search
- Responsive design

### Admin Features
- Admin dashboard with statistics
- User management
- Product management (CRUD)
- Category management
- Order management
- Payment tracking

### Security Features
- JWT-based authentication
- Password hashing with bcrypt
- CSRF protection
- Rate limiting
- Input validation and sanitization
- Secure session management

## Tech Stack

### Frontend
- **React 18** - UI library
- **Vite** - Build tool
- **React Router v6** - Routing
- **Axios** - HTTP client
- **Zustand** - State management
- **React Hook Form** - Form handling
- **Tailwind CSS** - Styling

### Backend
- **Node.js** - Runtime
- **Express** - Web framework
- **MongoDB** - Database
- **Mongoose** - ODM
- **JWT** - Authentication
- **bcrypt** - Password hashing
- **Multer** - File uploads
- **Stripe & PayPal** - Payment processing

## Project Structure

```
ecommerce-app/
├── frontend/                 # React frontend
│   ├── src/
│   │   ├── components/      # Reusable components
│   │   ├── pages/          # Page components
│   │   ├── hooks/          # Custom hooks
│   │   ├── store/          # State management
│   │   ├── services/       # API services
│   │   ├── utils/          # Utility functions
│   │   └── App.jsx         # Main app component
│   ├── public/             # Static assets
│   └── package.json
│
├── backend/                 # Node.js backend
│   ├── src/
│   │   ├── models/         # Database models
│   │   ├── routes/         # API routes
│   │   ├── controllers/    # Route controllers
│   │   ├── middleware/     # Custom middleware
│   │   ├── utils/          # Utility functions
│   │   ├── config/         # Configuration
│   │   └── server.js       # Express server
│   └── package.json
│
└── README.md               # This file
```

## Prerequisites

Before you begin, ensure you have the following installed:
- **Node.js** (v18 or higher) - [Download here](https://nodejs.org/)
- **MongoDB** - [Download here](https://www.mongodb.com/try/download/community) or use [MongoDB Atlas](https://www.mongodb.com/cloud/atlas)
- **Git** (optional but recommended)

## Installation

### 1. Install Node.js

If you don't have Node.js installed:

**Windows:**
1. Download the installer from https://nodejs.org/
2. Run the installer and follow the prompts
3. Restart your terminal/command prompt
4. Verify installation: `node --version` and `npm --version`

**macOS:**
```bash
brew install node
```

**Linux:**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Clone or Navigate to Project

```bash
cd "C:\Users\User\OneDrive - Fakulteti i Teknologjise se Informacionit\Documents\Web-Development-\ecommerce-app"
```

### 3. Install Backend Dependencies

```bash
cd backend
npm install
```

### 4. Install Frontend Dependencies

```bash
cd ../frontend
npm install
```

### 5. Configure Environment Variables

Create `.env` files in both frontend and backend directories:

**backend/.env**
```env
PORT=5000
NODE_ENV=development
MONGODB_URI=mongodb://localhost:27017/ecommerce
JWT_SECRET=your-super-secret-jwt-key-change-this
JWT_EXPIRE=30d
JWT_REFRESH_SECRET=your-refresh-token-secret
JWT_REFRESH_EXPIRE=90d

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
FROM_EMAIL=noreply@ecommerce.com
FROM_NAME=E-Commerce Platform

# File Upload
MAX_FILE_SIZE=5242880
FILE_UPLOAD_PATH=./public/uploads

# Payment
STRIPE_SECRET_KEY=your-stripe-secret-key
PAYPAL_CLIENT_ID=your-paypal-client-id
PAYPAL_CLIENT_SECRET=your-paypal-client-secret

# URLs
CLIENT_URL=http://localhost:5173
SERVER_URL=http://localhost:5000
```

**frontend/.env**
```env
VITE_API_URL=http://localhost:5000/api
VITE_STRIPE_PUBLIC_KEY=your-stripe-public-key
VITE_PAYPAL_CLIENT_ID=your-paypal-client-id
```

### 6. Set Up MongoDB

**Option A: Local MongoDB**
1. Install MongoDB Community Edition
2. Start MongoDB service:
   - Windows: MongoDB runs as a service automatically
   - macOS: `brew services start mongodb-community`
   - Linux: `sudo systemctl start mongod`

**Option B: MongoDB Atlas (Cloud)**
1. Create a free account at https://www.mongodb.com/cloud/atlas
2. Create a cluster
3. Get your connection string
4. Update `MONGODB_URI` in backend/.env

### 7. Seed Database (Optional)

```bash
cd backend
npm run seed
```

## Running the Application

### Development Mode

**Terminal 1 - Backend:**
```bash
cd backend
npm run dev
```
Backend will run on http://localhost:5000

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```
Frontend will run on http://localhost:5173

### Production Mode

**Build Frontend:**
```bash
cd frontend
npm run build
```

**Run Backend:**
```bash
cd backend
npm start
```

## API Documentation

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/auth/register` | Register new user | No |
| POST | `/api/auth/login` | Login user | No |
| POST | `/api/auth/logout` | Logout user | Yes |
| POST | `/api/auth/verify-email` | Verify email | No |
| POST | `/api/auth/forgot-password` | Request password reset | No |
| POST | `/api/auth/reset-password` | Reset password | No |
| GET | `/api/auth/me` | Get current user | Yes |

### User Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/users/profile` | Get user profile | Yes |
| PUT | `/api/users/profile` | Update profile | Yes |
| PUT | `/api/users/password` | Change password | Yes |
| POST | `/api/users/avatar` | Upload avatar | Yes |
| GET | `/api/users` | Get all users (admin) | Admin |
| PUT | `/api/users/:id/role` | Update user role (admin) | Admin |
| DELETE | `/api/users/:id` | Delete user (admin) | Admin |

### Product Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/products` | Get all products | No |
| GET | `/api/products/:id` | Get product by ID | No |
| POST | `/api/products` | Create product (admin) | Admin |
| PUT | `/api/products/:id` | Update product (admin) | Admin |
| DELETE | `/api/products/:id` | Delete product (admin) | Admin |

### Cart Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/cart` | Get user cart | Yes |
| POST | `/api/cart` | Add item to cart | Yes |
| PUT | `/api/cart/:id` | Update cart item | Yes |
| DELETE | `/api/cart/:id` | Remove from cart | Yes |
| DELETE | `/api/cart` | Clear cart | Yes |

### Order Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/orders` | Get user orders | Yes |
| GET | `/api/orders/:id` | Get order by ID | Yes |
| POST | `/api/orders` | Create order | Yes |
| PUT | `/api/orders/:id/status` | Update order status (admin) | Admin |
| GET | `/api/orders/admin/all` | Get all orders (admin) | Admin |

## Testing

### Backend Tests
```bash
cd backend
npm test
```

### Frontend Tests
```bash
cd frontend
npm test
```

## Deployment

### Backend Deployment (e.g., Heroku)

1. Create a Heroku app
2. Add MongoDB Atlas connection string
3. Set environment variables
4. Deploy:
```bash
git push heroku main
```

### Frontend Deployment (e.g., Vercel/Netlify)

1. Build the frontend: `npm run build`
2. Deploy the `dist` folder
3. Set environment variables in hosting platform

## Default Admin Account

After seeding the database:
- Email: admin@ecommerce.com
- Password: admin123

**Important:** Change these credentials immediately in production!

## Common Issues & Solutions

### Port Already in Use
```bash
# Find and kill process on port 5000 (backend)
npx kill-port 5000

# Find and kill process on port 5173 (frontend)
npx kill-port 5173
```

### MongoDB Connection Error
- Ensure MongoDB is running
- Check MONGODB_URI in .env
- Check firewall settings
- For MongoDB Atlas, whitelist your IP address

### CORS Issues
- Ensure CLIENT_URL in backend/.env matches frontend URL
- Check CORS configuration in backend/src/server.js

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## License

MIT License - feel free to use this project for learning or commercial purposes.

## Support

For issues or questions, please open an issue on GitHub or contact the development team.

## Acknowledgments

- React and Vite teams for excellent documentation
- Express.js community
- MongoDB team
- All open-source contributors

---

**Note:** This application is a migration from the original PHP-based e-commerce platform. All features from the original application have been preserved and modernized with React and Node.js.
