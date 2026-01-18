import mongoose from 'mongoose';

const productSchema = new mongoose.Schema({
  name: {
    type: String,
    required: [true, 'Ju lutem vendosni emrin e produktit'],
    trim: true,
    maxlength: [200, 'Emri i produktit nuk mund të jetë më i gjatë se 200 karaktere']
  },
  slug: {
    type: String,
    unique: true,
    lowercase: true
  },
  description: {
    type: String,
    required: [true, 'Ju lutem vendosni përshkrimin e produktit'],
    maxlength: [2000, 'Përshkrimi nuk mund të jetë më i gjatë se 2000 karaktere']
  },
  price: {
    type: Number,
    required: [true, 'Ju lutem vendosni çmimin e produktit'],
    min: [0, 'Çmimi nuk mund të jetë negativ']
  },
  salePrice: {
    type: Number,
    min: [0, 'Çmimi i zbritur nuk mund të jetë negativ'],
    default: null
  },
  category: {
    type: mongoose.Schema.Types.ObjectId,
    ref: 'Category',
    required: [true, 'Ju lutem zgjidhni kategorinë']
  },
  images: [{
    type: String
  }],
  stock: {
    type: Number,
    required: [true, 'Ju lutem vendosni sasinë në stok'],
    min: [0, 'Stoku nuk mund të jetë negativ'],
    default: 0
  },
  sku: {
    type: String,
    unique: true,
    sparse: true
  },
  isActive: {
    type: Boolean,
    default: true
  },
  isFeatured: {
    type: Boolean,
    default: false
  },
  specifications: {
    type: Map,
    of: String
  },
  rating: {
    type: Number,
    default: 0,
    min: [0, 'Rating nuk mund të jetë negativ'],
    max: [5, 'Rating nuk mund të jetë më i madh se 5']
  },
  numReviews: {
    type: Number,
    default: 0
  },
  views: {
    type: Number,
    default: 0
  },
  salesCount: {
    type: Number,
    default: 0
  }
}, {
  timestamps: true,
  toJSON: { virtuals: true },
  toObject: { virtuals: true }
});

// Virtual for discount percentage
productSchema.virtual('discountPercentage').get(function() {
  if (this.salePrice && this.salePrice < this.price) {
    return Math.round(((this.price - this.salePrice) / this.price) * 100);
  }
  return 0;
});

// Virtual for current price
productSchema.virtual('currentPrice').get(function() {
  return this.salePrice && this.salePrice < this.price ? this.salePrice : this.price;
});

// Virtual for in stock status
productSchema.virtual('inStock').get(function() {
  return this.stock > 0;
});

// Indexes
productSchema.index({ slug: 1 });
productSchema.index({ category: 1 });
productSchema.index({ price: 1 });
productSchema.index({ isActive: 1 });
productSchema.index({ isFeatured: 1 });
productSchema.index({ createdAt: -1 });
productSchema.index({ salesCount: -1 });
productSchema.index({ rating: -1 });
productSchema.index({ name: 'text', description: 'text' });

// Generate slug from name
productSchema.pre('save', function(next) {
  if (this.isModified('name')) {
    this.slug = this.name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '');
  }
  next();
});

// Generate SKU if not provided
productSchema.pre('save', function(next) {
  if (!this.sku) {
    this.sku = `SKU-${Date.now()}-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;
  }
  next();
});

const Product = mongoose.model('Product', productSchema);

export default Product;
