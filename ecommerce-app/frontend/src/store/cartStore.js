import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export const useCartStore = create(
  persist(
    (set, get) => ({
      items: [],
      total: 0,

      // Add item to cart
      addItem: (product, quantity = 1) => {
        const { items } = get();
        const existingItem = items.find(item => item.product._id === product._id);

        if (existingItem) {
          set({
            items: items.map(item =>
              item.product._id === product._id
                ? { ...item, quantity: item.quantity + quantity }
                : item
            )
          });
        } else {
          set({
            items: [...items, { product, quantity, price: product.currentPrice || product.price }]
          });
        }

        get().calculateTotal();
      },

      // Update item quantity
      updateQuantity: (productId, quantity) => {
        const { items } = get();
        if (quantity <= 0) {
          get().removeItem(productId);
          return;
        }

        set({
          items: items.map(item =>
            item.product._id === productId
              ? { ...item, quantity }
              : item
          )
        });

        get().calculateTotal();
      },

      // Remove item from cart
      removeItem: (productId) => {
        const { items } = get();
        set({
          items: items.filter(item => item.product._id !== productId)
        });

        get().calculateTotal();
      },

      // Clear cart
      clearCart: () => {
        set({ items: [], total: 0 });
      },

      // Calculate total
      calculateTotal: () => {
        const { items } = get();
        const total = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        set({ total });
      },

      // Get cart count
      getCartCount: () => {
        const { items } = get();
        return items.reduce((count, item) => count + item.quantity, 0);
      },

      // Get item quantity
      getItemQuantity: (productId) => {
        const { items } = get();
        const item = items.find(item => item.product._id === productId);
        return item ? item.quantity : 0;
      }
    }),
    {
      name: 'cart-storage'
    }
  )
);
