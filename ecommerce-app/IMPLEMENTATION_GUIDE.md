# E-Commerce Full Stack Implementation Guide

This guide provides step-by-step instructions to complete your React + Node.js e-commerce application.

## Current Status

### ✅ Completed
1. Project structure created
2. Backend package.json with all dependencies
3. Express server setup with middleware
4. Database models created:
   - User (with authentication methods)
   - Product
   - Category
   - Cart
   - Order
   - Contact
5. Authentication middleware
6. Error handling middleware

### 🔄 To Be Completed

## Step 1: Install Node.js (REQUIRED)

Before proceeding, you MUST install Node.js:

1. **Download Node.js:**
   - Go to https://nodejs.org/
   - Download the LTS version (recommended for most users)
   - Run the installer

2. **Verify Installation:**
   ```bash
   node --version
   npm --version
   ```

3. **Restart your terminal/IDE** after installation

## Step 2: Install Backend Dependencies

```bash
cd "c:\Users\User\OneDrive - Fakulteti i Teknologjise se Informacionit\Documents\Web-Development-\ecommerce-app\backend"
npm install
```

## Step 3: Set Up Environment Variables

Create a `.env` file in the backend folder:

```bash
cp .env.example .env
```

Edit the `.env` file with your actual values:
- Set a strong JWT_SECRET
- Configure MongoDB URI (local or MongoDB Atlas)
- Add email credentials (Gmail with app password recommended)

## Step 4: Install MongoDB

### Option A: Local MongoDB
- Download from https://www.mongodb.com/try/download/community
- Install and start the service
- Use `mongodb://localhost:27017/ecommerce` as your connection string

### Option B: MongoDB Atlas (Cloud - Recommended)
1. Create account at https://www.mongodb.com/cloud/atlas
2. Create a free cluster
3. Get connection string
4. Update MONGODB_URI in .env

## Step 5: Complete Backend Routes

Create the following route files in `backend/src/routes/`:

### 5.1 Authentication Routes (auth.routes.js)

```javascript
import express from 'express';
import {
  register,
  login,
  logout,
  getMe,
  verifyEmail,
  forgotPassword,
  resetPassword,
  refreshToken
} from '../controllers/auth.controller.js';
import { protect } from '../middleware/auth.middleware.js';
import { registerLimiter, loginLimiter } from '../middleware/rateLimiter.js';

const router = express.Router();

router.post('/register', registerLimiter, register);
router.post('/login', loginLimiter, login);
router.post('/logout', logout);
router.get('/me', protect, getMe);
router.post('/verify-email', verifyEmail);
router.post('/forgot-password', forgotPassword);
router.post('/reset-password', resetPassword);
router.post('/refresh-token', refreshToken);

export default router;
```

### 5.2 User Routes (user.routes.js)

```javascript
import express from 'express';
import {
  getProfile,
  updateProfile,
  updatePassword,
  uploadAvatar,
  getAllUsers,
  getUserById,
  updateUserRole,
  deleteUser
} from '../controllers/user.controller.js';
import { protect, authorize } from '../middleware/auth.middleware.js';
import { upload } from '../middleware/upload.js';

const router = express.Router();

// User routes
router.get('/profile', protect, getProfile);
router.put('/profile', protect, updateProfile);
router.put('/password', protect, updatePassword);
router.post('/avatar', protect, upload.single('avatar'), uploadAvatar);

// Admin routes
router.get('/', protect, authorize('admin'), getAllUsers);
router.get('/:id', protect, authorize('admin'), getUserById);
router.put('/:id/role', protect, authorize('admin'), updateUserRole);
router.delete('/:id', protect, authorize('admin'), deleteUser);

export default router;
```

### 5.3 Product Routes (product.routes.js)

```javascript
import express from 'express';
import {
  getProducts,
  getProductById,
  createProduct,
  updateProduct,
  deleteProduct,
  uploadProductImages
} from '../controllers/product.controller.js';
import { protect, authorize } from '../middleware/auth.middleware.js';
import { upload } from '../middleware/upload.js';

const router = express.Router();

router.get('/', getProducts);
router.get('/:id', getProductById);
router.post('/', protect, authorize('admin'), createProduct);
router.put('/:id', protect, authorize('admin'), updateProduct);
router.delete('/:id', protect, authorize('admin'), deleteProduct);
router.post('/:id/images', protect, authorize('admin'), upload.array('images', 5), uploadProductImages);

export default router;
```

### 5.4 Category Routes (category.routes.js)

```javascript
import express from 'express';
import {
  getCategories,
  getCategoryById,
  createCategory,
  updateCategory,
  deleteCategory
} from '../controllers/category.controller.js';
import { protect, authorize } from '../middleware/auth.middleware.js';

const router = express.Router();

router.get('/', getCategories);
router.get('/:id', getCategoryById);
router.post('/', protect, authorize('admin'), createCategory);
router.put('/:id', protect, authorize('admin'), updateCategory);
router.delete('/:id', protect, authorize('admin'), deleteCategory);

export default router;
```

### 5.5 Cart Routes (cart.routes.js)

```javascript
import express from 'express';
import {
  getCart,
  addToCart,
  updateCartItem,
  removeFromCart,
  clearCart
} from '../controllers/cart.controller.js';
import { protect } from '../middleware/auth.middleware.js';

const router = express.Router();

router.get('/', protect, getCart);
router.post('/', protect, addToCart);
router.put('/:itemId', protect, updateCartItem);
router.delete('/:itemId', protect, removeFromCart);
router.delete('/', protect, clearCart);

export default router;
```

### 5.6 Order Routes (order.routes.js)

```javascript
import express from 'express';
import {
  createOrder,
  getMyOrders,
  getOrderById,
  updateOrderStatus,
  getAllOrders
} from '../controllers/order.controller.js';
import { protect, authorize } from '../middleware/auth.middleware.js';

const router = express.Router();

router.post('/', protect, createOrder);
router.get('/my-orders', protect, getMyOrders);
router.get('/:id', protect, getOrderById);
router.put('/:id/status', protect, authorize('admin'), updateOrderStatus);
router.get('/', protect, authorize('admin'), getAllOrders);

export default router;
```

### 5.7 Payment Routes (payment.routes.js)

```javascript
import express from 'express';
import {
  createStripePaymentIntent,
  stripeWebhook,
  createPayPalOrder,
  capturePayPalPayment
} from '../controllers/payment.controller.js';
import { protect } from '../middleware/auth.middleware.js';

const router = express.Router();

router.post('/stripe/create-intent', protect, createStripePaymentIntent);
router.post('/stripe/webhook', express.raw({type: 'application/json'}), stripeWebhook);
router.post('/paypal/create-order', protect, createPayPalOrder);
router.post('/paypal/capture', protect, capturePayPalPayment);

export default router;
```

### 5.8 Contact Routes (contact.routes.js)

```javascript
import express from 'express';
import {
  submitContactForm,
  getAllMessages,
  getMessageById,
  markAsRead,
  replyToMessage,
  deleteMessage
} from '../controllers/contact.controller.js';
import { protect, authorize } from '../middleware/auth.middleware.js';

const router = express.Router();

router.post('/', submitContactForm);
router.get('/', protect, authorize('admin'), getAllMessages);
router.get('/:id', protect, authorize('admin'), getMessageById);
router.put('/:id/read', protect, authorize('admin'), markAsRead);
router.post('/:id/reply', protect, authorize('admin'), replyToMessage);
router.delete('/:id', protect, authorize('admin'), deleteMessage);

export default router;
```

## Step 6: Create Controllers

You need to create controller files in `backend/src/controllers/` for each route.

### Example: Auth Controller (auth.controller.js)

```javascript
import User from '../models/User.model.js';
import crypto from 'crypto';
import { sendEmail } from '../utils/sendEmail.js';

// @desc    Register user
// @route   POST /api/auth/register
// @access  Public
export const register = async (req, res, next) => {
  try {
    const { name, email, password } = req.body;

    // Check if user exists
    const userExists = await User.findOne({ email });
    if (userExists) {
      return res.status(400).json({
        success: false,
        message: 'Përdoruesi me këtë email ekziston tashmë'
      });
    }

    // Create user
    const user = await User.create({
      name,
      email,
      password
    });

    // Generate verification token
    const verificationToken = user.getVerificationToken();
    await user.save();

    // Create verification URL
    const verificationUrl = `${process.env.CLIENT_URL}/verify-email?token=${verificationToken}`;

    // Send verification email
    try {
      await sendEmail({
        email: user.email,
        subject: 'Verifikoni Email-in tuaj',
        html: `
          <h1>Mirë se vini!</h1>
          <p>Ju lutem klikoni linkun më poshtë për të verifikuar email-in tuaj:</p>
          <a href="${verificationUrl}" target="_blank">Verifiko Email-in</a>
          <p>Linku skadon pas 24 orëve.</p>
        `
      });

      res.status(201).json({
        success: true,
        message: 'Regjistrimi u krye me sukses. Ju lutem verifikoni email-in tuaj.',
        user: {
          id: user._id,
          name: user.name,
          email: user.email
        }
      });
    } catch (error) {
      user.verificationToken = undefined;
      user.verificationTokenExpire = undefined;
      await user.save();

      return res.status(500).json({
        success: false,
        message: 'Email-i nuk mund të dërgohet'
      });
    }
  } catch (error) {
    next(error);
  }
};

// @desc    Login user
// @route   POST /api/auth/login
// @access  Public
export const login = async (req, res, next) => {
  try {
    const { email, password } = req.body;

    // Validate email & password
    if (!email || !password) {
      return res.status(400).json({
        success: false,
        message: 'Ju lutem vendosni email dhe fjalëkalimin'
      });
    }

    // Check for user
    const user = await User.findOne({ email }).select('+password');

    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'Kredencialet janë të pasakta'
      });
    }

    // Check if account is locked
    if (user.isLocked) {
      return res.status(423).json({
        success: false,
        message: 'Llogaria është e bllokuar. Ju lutem provoni më vonë.'
      });
    }

    // Check if password matches
    const isMatch = await user.matchPassword(password);

    if (!isMatch) {
      await user.incLoginAttempts();
      return res.status(401).json({
        success: false,
        message: 'Kredencialet janë të pasakta'
      });
    }

    // Reset login attempts
    await user.resetLoginAttempts();

    // Create tokens
    const token = user.getSignedJwtToken();
    const refreshToken = user.getRefreshToken();
    await user.save();

    // Set cookie
    const cookieOptions = {
      expires: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000),
      httpOnly: true,
      secure: process.env.NODE_ENV === 'production',
      sameSite: 'strict'
    };

    res
      .status(200)
      .cookie('token', token, cookieOptions)
      .json({
        success: true,
        token,
        refreshToken,
        user: {
          id: user._id,
          name: user.name,
          email: user.email,
          role: user.role,
          avatar: user.avatar,
          isVerified: user.isVerified
        }
      });
  } catch (error) {
    next(error);
  }
};

// @desc    Logout user
// @route   POST /api/auth/logout
// @access  Private
export const logout = async (req, res) => {
  res.cookie('token', 'none', {
    expires: new Date(Date.now() + 10 * 1000),
    httpOnly: true
  });

  res.status(200).json({
    success: true,
    message: 'Jeni çkyçur me sukses'
  });
};

// @desc    Get current logged in user
// @route   GET /api/auth/me
// @access  Private
export const getMe = async (req, res) => {
  res.status(200).json({
    success: true,
    user: req.user
  });
};

// ... Add more auth controller functions
```

## Step 7: Create Utility Functions

Create utility files in `backend/src/utils/`:

### 7.1 Email Service (sendEmail.js)

```javascript
import nodemailer from 'nodemailer';

export const sendEmail = async (options) => {
  const transporter = nodemailer.createTransporter({
    host: process.env.SMTP_HOST,
    port: process.env.SMTP_PORT,
    auth: {
      user: process.env.SMTP_USER,
      pass: process.env.SMTP_PASS
    }
  });

  const message = {
    from: `${process.env.FROM_NAME} <${process.env.FROM_EMAIL}>`,
    to: options.email,
    subject: options.subject,
    html: options.html
  };

  await transporter.sendMail(message);
};
```

### 7.2 Rate Limiter (rateLimiter.js in middleware)

```javascript
import rateLimit from 'express-rate-limit';

export const registerLimiter = rateLimit({
  windowMs: 60 * 60 * 1000, // 1 hour
  max: 3,
  message: 'Shumë kërkesa nga kjo IP, ju lutem provoni më vonë'
});

export const loginLimiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 7,
  message: 'Shumë kërkesa hyrjeje, ju lutem provoni më vonë'
});
```

### 7.3 File Upload (upload.js in middleware)

```javascript
import multer from 'multer';
import path from 'path';

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'public/uploads/');
  },
  filename: (req, file, cb) => {
    cb(null, `${Date.now()}-${file.originalname}`);
  }
});

const fileFilter = (req, file, cb) => {
  const allowedTypes = /jpeg|jpg|png|gif/;
  const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
  const mimetype = allowedTypes.test(file.mimetype);

  if (extname && mimetype) {
    return cb(null, true);
  } else {
    cb('Vetëm imazhe janë të lejuara!');
  }
};

export const upload = multer({
  storage,
  limits: { fileSize: 5 * 1024 * 1024 }, // 5MB
  fileFilter
});
```

## Step 8: Create Frontend with Vite

```bash
cd "c:\Users\User\OneDrive - Fakulteti i Teknologjise se Informacionit\Documents\Web-Development-\ecommerce-app"
npm create vite@latest frontend -- --template react
cd frontend
npm install
```

### Install Frontend Dependencies

```bash
npm install axios react-router-dom zustand react-hook-form react-icons
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

## Step 9: Frontend Structure

Create the following structure in `frontend/src/`:

```
src/
├── components/
│   ├── layout/
│   │   ├── Navbar.jsx
│   │   ├── Footer.jsx
│   │   └── Layout.jsx
│   ├── auth/
│   │   ├── LoginForm.jsx
│   │   ├── RegisterForm.jsx
│   │   └── PrivateRoute.jsx
│   ├── product/
│   │   ├── ProductCard.jsx
│   │   ├── ProductList.jsx
│   │   └── ProductDetail.jsx
│   ├── cart/
│   │   ├── CartItem.jsx
│   │   └── CartSummary.jsx
│   └── common/
│       ├── Button.jsx
│       ├── Input.jsx
│       ├── Alert.jsx
│       └── Loader.jsx
├── pages/
│   ├── Home.jsx
│   ├── Products.jsx
│   ├── ProductDetail.jsx
│   ├── Cart.jsx
│   ├── Checkout.jsx
│   ├── Login.jsx
│   ├── Register.jsx
│   ├── Profile.jsx
│   ├── Orders.jsx
│   └── admin/
│       ├── Dashboard.jsx
│       ├── ProductManagement.jsx
│       └── UserManagement.jsx
├── store/
│   ├── authStore.js
│   ├── cartStore.js
│   └── productStore.js
├── services/
│   ├── api.js
│   ├── authService.js
│   ├── productService.js
│   └── cartService.js
├── hooks/
│   ├── useAuth.js
│   └── useCart.js
├── utils/
│   ├── helpers.js
│   └── constants.js
├── App.jsx
└── main.jsx
```

## Step 10: Frontend Example Files

### API Service (services/api.js)

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:5000/api',
  headers: {
    'Content-Type': 'application/json'
  }
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    const message = error.response?.data?.message || 'Ndodhi një gabim';
    return Promise.reject(message);
  }
);

export default api;
```

### Auth Store (store/authStore.js)

```javascript
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useAuthStore = create(
  persist(
    (set) => ({
      user: null,
      token: null,
      isAuthenticated: false,

      login: (user, token) => set({ user, token, isAuthenticated: true }),

      logout: () => set({ user: null, token: null, isAuthenticated: false }),

      updateUser: (user) => set({ user })
    }),
    {
      name: 'auth-storage'
    }
  )
);
```

## Step 11: Run the Application

### Terminal 1 - Backend
```bash
cd backend
npm run dev
```

### Terminal 2 - Frontend
```bash
cd frontend
npm run dev
```

## Step 12: Test the Application

1. Open http://localhost:5173 in your browser
2. Register a new account
3. Verify email (check console logs for verification link if email not configured)
4. Login
5. Browse products
6. Add to cart
7. Checkout

## Common Issues & Solutions

### MongoDB Connection Failed
- Ensure MongoDB is running
- Check connection string in .env
- For MongoDB Atlas, whitelist your IP

### Port Already in Use
```bash
npx kill-port 5000
npx kill-port 5173
```

### CORS Errors
- Verify CLIENT_URL in backend .env
- Check CORS configuration in server.js

### Email Not Sending
- Use Gmail with App Password
- Enable "Less secure app access" or use App Passwords
- Check SMTP credentials in .env

## Next Steps

1. Complete all controller functions
2. Add input validation
3. Implement pagination
4. Add search functionality
5. Implement product reviews
6. Add admin analytics dashboard
7. Set up Stripe/PayPal integration
8. Add unit tests
9. Deploy to production

## Resources

- [Express.js Documentation](https://expressjs.com/)
- [React Documentation](https://react.dev/)
- [MongoDB Documentation](https://docs.mongodb.com/)
- [Mongoose Documentation](https://mongoosejs.com/)
- [JWT Documentation](https://jwt.io/)
- [Stripe Documentation](https://stripe.com/docs)

## Support

If you encounter any issues, check the error logs and refer to the documentation above. Common errors are usually related to:
- Missing environment variables
- Database connection issues
- Port conflicts
- Missing dependencies

---

**Note:** This is a comprehensive guide. Take it step by step and test each feature as you implement it.
