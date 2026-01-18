import jwt from 'jsonwebtoken';
import User from '../models/User.model.js';

// Protect routes - verify JWT token
export const protect = async (req, res, next) => {
  let token;

  // Check for token in headers
  if (req.headers.authorization && req.headers.authorization.startsWith('Bearer')) {
    token = req.headers.authorization.split(' ')[1];
  }
  // Check for token in cookies
  else if (req.cookies.token) {
    token = req.cookies.token;
  }

  // Make sure token exists
  if (!token) {
    return res.status(401).json({
      success: false,
      message: 'Nuk jeni i autorizuar për të hyrë në këtë rrugë'
    });
  }

  try {
    // Verify token
    const decoded = jwt.verify(token, process.env.JWT_SECRET);

    // Get user from token
    req.user = await User.findById(decoded.id).select('-password');

    if (!req.user) {
      return res.status(401).json({
        success: false,
        message: 'Përdoruesi nuk u gjet'
      });
    }

    // Check if user is active
    if (!req.user.isActive) {
      return res.status(401).json({
        success: false,
        message: 'Llogaria juaj është çaktivizuar'
      });
    }

    next();
  } catch (error) {
    return res.status(401).json({
      success: false,
      message: 'Tokeni është i pavlefshëm ose ka skaduar'
    });
  }
};

// Grant access to specific roles
export const authorize = (...roles) => {
  return (req, res, next) => {
    if (!roles.includes(req.user.role)) {
      return res.status(403).json({
        success: false,
        message: `Roli ${req.user.role} nuk është i autorizuar për të hyrë në këtë rrugë`
      });
    }
    next();
  };
};

// Check if user is verified
export const checkVerified = (req, res, next) => {
  if (!req.user.isVerified) {
    return res.status(403).json({
      success: false,
      message: 'Ju lutem verifikoni email-in tuaj për të vazhduar'
    });
  }
  next();
};
