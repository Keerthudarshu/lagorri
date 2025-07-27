// Shopping Cart functionality for Lagorii Kids

class ShoppingCart {
    constructor() {
        this.cart = this.loadCart();
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateDisplay();
    }
    
    loadCart() {
        try {
            const cartData = localStorage.getItem('lagorii_cart');
            return cartData ? JSON.parse(cartData) : [];
        } catch (error) {
            console.error('Error loading cart:', error);
            return [];
        }
    }
    
    saveCart() {
        try {
            localStorage.setItem('lagorii_cart', JSON.stringify(this.cart));
            this.updateCartCount();
            this.updateDisplay();
        } catch (error) {
            console.error('Error saving cart:', error);
        }
    }
    
    addItem(productId, quantity = 1, options = {}) {
        // Validate inputs
        if (!productId || quantity < 1) {
            throw new Error('Invalid product ID or quantity');
        }
        
        console.log('Cart: Adding item', productId, 'quantity:', quantity, 'options:', options);
        
        // Check if item already exists with same options
        const existingItemIndex = this.cart.findIndex(item => 
            item.productId == productId && 
            this.optionsMatch(item.options, options)
        );
        
        if (existingItemIndex >= 0) {
            // Update existing item quantity
            const oldQuantity = this.cart[existingItemIndex].quantity;
            this.cart[existingItemIndex].quantity += quantity;
            
            // Ensure quantity doesn't exceed maximum
            if (this.cart[existingItemIndex].quantity > 10) {
                this.cart[existingItemIndex].quantity = 10;
            }
            
            console.log('Cart: Updated existing item from', oldQuantity, 'to', this.cart[existingItemIndex].quantity);
        } else {
            // Add new item
            const newItem = {
                productId: productId,
                quantity: Math.min(quantity, 10),
                options: options,
                addedAt: new Date().toISOString()
            };
            this.cart.push(newItem);
            console.log('Cart: Added new item:', newItem);
        }
        
        this.saveCart();
        this.showAddedToCartMessage();
        
        console.log('Cart: Current cart state:', this.cart);
        return true;
    }
    
    removeItem(productId, options = {}) {
        this.cart = this.cart.filter(item => 
            !(item.productId === productId && this.optionsMatch(item.options, options))
        );
        this.saveCart();
    }
    
    updateItemQuantity(productId, quantity, options = {}) {
        const itemIndex = this.cart.findIndex(item => 
            item.productId === productId && 
            this.optionsMatch(item.options, options)
        );
        
        if (itemIndex >= 0) {
            if (quantity <= 0) {
                this.removeItem(productId, options);
            } else {
                this.cart[itemIndex].quantity = Math.min(quantity, 10);
                this.saveCart();
            }
        }
    }
    
    clearCart() {
        this.cart = [];
        this.saveCart();
    }
    
    getCart() {
        return [...this.cart]; // Return copy to prevent external modification
    }
    
    getItemCount() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }
    
    optionsMatch(options1, options2) {
        return JSON.stringify(options1) === JSON.stringify(options2);
    }
    
    updateCartCount() {
        const cartCountElements = document.querySelectorAll('[data-cart-count]');
        const count = this.getItemCount();
        
        console.log('Cart: Updating cart count to:', count);
        console.log('Cart: Items in cart:', this.cart);
        
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'inline' : 'none';
        });
        
        // Update navbar cart count
        const navCartCount = document.getElementById('cartCount');
        if (navCartCount) {
            navCartCount.textContent = count;
            navCartCount.style.display = count > 0 ? 'inline' : 'none';
            console.log('Cart: Updated navbar cart count element');
        } else {
            console.log('Cart: Could not find navbar cart count element');
        }
    }
    
    updateDisplay() {
        this.updateCartCount();
        
        // Update cart page if we're on it
        if (window.location.pathname.includes('cart.php')) {
            this.renderCartPage();
        }
    }
    
    async renderCartPage() {
        const cartContainer = document.getElementById('cartItemsContainer');
        if (!cartContainer) return;
        
        if (this.cart.length === 0) {
            cartContainer.innerHTML = this.getEmptyCartHTML();
            this.updateCartSummary(0, 0, 0);
            return;
        }
        
        try {
            // Fetch product data for cart items
            const productData = await this.fetchProductData();
            cartContainer.innerHTML = this.renderCartItems(productData);
            this.bindCartEvents();
            this.updateCartSummary(productData);
        } catch (error) {
            console.error('Error rendering cart:', error);
            cartContainer.innerHTML = '<div class="alert alert-danger">Error loading cart items</div>';
        }
    }
    
    async fetchProductData() {
        try {
            const response = await fetch('../api/products.php');
            if (!response.ok) {
                throw new Error('Failed to fetch products');
            }
            const products = await response.json();
            
            // Filter products that are in the cart and ensure we have the right structure
            return products.filter(product => 
                this.cart.some(cartItem => cartItem.productId == product.id)
            ).map(product => ({
                ...product,
                // Ensure we have the right image field
                images: product.images || [product.primary_image || 'https://via.placeholder.com/300x300?text=No+Image']
            }));
        } catch (error) {
            console.error('Error fetching product data:', error);
            throw error;
        }
    }
    
    renderCartItems(products) {
        return this.cart.map(cartItem => {
            const product = products.find(p => p.id == cartItem.productId);
            if (!product) return '';
            
            const totalPrice = product.price * cartItem.quantity;
            const productImage = product.primary_image || (product.images && product.images[0]) || 'https://via.placeholder.com/80x80?text=No+Image';
            
            return `
                <div class="cart-item" data-product-id="${cartItem.productId}">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="${productImage}" alt="${product.name}" 
                                         class="img-fluid rounded cart-item-image"
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-4">
                                    <h6 class="mb-1">${product.name}</h6>
                                    <div class="cart-item-options">
                                        ${cartItem.options && cartItem.options.size ? `<small class="text-muted">Size: ${cartItem.options.size}</small>` : ''}
                                        ${cartItem.options && cartItem.options.color ? `<small class="text-muted"> | Color: ${cartItem.options.color}</small>` : ''}
                                    </div>
                                    <small class="text-muted">€${product.price} each</small>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary btn-sm quantity-decrease" 
                                                type="button" data-action="decrease">-</button>
                                        <input type="number" class="form-control form-control-sm text-center cart-item-quantity" 
                                               value="${cartItem.quantity}" min="1" max="10" 
                                               data-product-id="${cartItem.productId}">
                                        <button class="btn btn-outline-secondary btn-sm quantity-increase" 
                                                type="button" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <span class="cart-item-price h6">€${Math.round(totalPrice)}</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn btn-outline-danger btn-sm remove-item" 
                                            data-product-id="${cartItem.productId}"
                                            title="Remove item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    bindCartEvents() {
        // Quantity controls
        document.querySelectorAll('.quantity-decrease, .quantity-increase').forEach(button => {
            button.addEventListener('click', (e) => {
                const cartItem = e.target.closest('.cart-item');
                const productId = cartItem.dataset.productId;
                const quantityInput = cartItem.querySelector('.cart-item-quantity');
                const currentQuantity = parseInt(quantityInput.value);
                const action = e.target.dataset.action;
                
                let newQuantity = currentQuantity;
                if (action === 'decrease' && currentQuantity > 1) {
                    newQuantity = currentQuantity - 1;
                } else if (action === 'increase' && currentQuantity < 10) {
                    newQuantity = currentQuantity + 1;
                }
                
                if (newQuantity !== currentQuantity) {
                    this.updateItemQuantity(productId, newQuantity);
                    quantityInput.value = newQuantity;
                    this.renderCartPage(); // Re-render to update totals
                }
            });
        });
        
        // Quantity input changes
        document.querySelectorAll('.cart-item-quantity').forEach(input => {
            input.addEventListener('change', (e) => {
                const productId = e.target.dataset.productId;
                const quantity = parseInt(e.target.value);
                
                if (quantity >= 1 && quantity <= 10) {
                    this.updateItemQuantity(productId, quantity);
                    this.renderCartPage(); // Re-render to update totals
                } else {
                    e.target.value = Math.min(Math.max(quantity, 1), 10);
                }
            });
        });
        
        // Remove item buttons
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', (e) => {
                const productId = e.target.closest('[data-product-id]').dataset.productId;
                
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    this.removeItem(productId);
                    this.renderCartPage();
                }
            });
        });
    }
    
    updateCartSummary(products) {
        let subtotal = 0;
        
        if (products && products.length > 0) {
            this.cart.forEach(cartItem => {
                const product = products.find(p => p.id === cartItem.productId);
                if (product) {
                    subtotal += product.price * cartItem.quantity;
                }
            });
        }
        
        // Calculate discount
        const totalItems = this.getItemCount();
        let discountPercent = 0;
        let discountAmount = 0;
        
        if (totalItems >= 3) {
            discountPercent = 10;
            discountAmount = subtotal * 0.10;
        } else if (totalItems >= 2) {
            discountPercent = 5;
            discountAmount = subtotal * 0.05;
        }
        
        const total = subtotal - discountAmount;
        
        // Update summary elements
        const subtotalElement = document.getElementById('cartSubtotal');
        const discountElement = document.getElementById('cartDiscount');
        const discountRow = document.getElementById('discountRow');
        const totalElement = document.getElementById('cartTotal');
        const checkoutBtn = document.getElementById('proceedToCheckout');
        
        if (subtotalElement) subtotalElement.textContent = `€${Math.round(subtotal)}`;
        if (totalElement) totalElement.textContent = `€${Math.round(total)}`;
        
        if (discountElement && discountRow) {
            if (discountAmount > 0) {
                discountElement.textContent = `-€${Math.round(discountAmount)}`;
                discountRow.style.display = 'flex';
            } else {
                discountRow.style.display = 'none';
            }
        }
        
        if (checkoutBtn) {
            checkoutBtn.disabled = this.cart.length === 0;
        }
    }
    
    getEmptyCartHTML() {
        return `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Add some products to get started!</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        `;
    }
    
    showAddedToCartMessage() {
        // Show toast notification
        if (window.LagoriiApp && window.LagoriiApp.showToast) {
            window.LagoriiApp.showToast('Item added to cart!', 'success');
        }
        
        // Animate cart icon
        const cartIcon = document.querySelector('a[href*="cart"] i');
        if (cartIcon) {
            cartIcon.style.transform = 'scale(1.2)';
            cartIcon.style.transition = 'transform 0.2s ease';
            setTimeout(() => {
                cartIcon.style.transform = 'scale(1)';
            }, 200);
        }
    }
    
    bindEvents() {
        // Remove any existing event listeners to prevent duplicates
        if (this.handleAddToCart) {
            document.removeEventListener('click', this.handleAddToCart);
        }
        
        // Create a bound event handler
        this.handleAddToCart = (e) => {
            // Handle regular add to cart buttons
            const addToCartButton = e.target.closest('.add-to-cart');
            if (addToCartButton) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = addToCartButton.dataset.productId;
                console.log('Cart: Add to cart clicked for product:', productId);
                
                if (productId) {
                    this.addItem(productId, 1);
                }
                return;
            }
            
            // Handle product detail page add to cart with options
            const addToCartDetailButton = e.target.closest('.add-to-cart-detail');
            if (addToCartDetailButton) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = addToCartDetailButton.dataset.productId;
                console.log('Cart: Add to cart detail clicked for product:', productId);
                
                // Get selected options
                const sizeElement = document.querySelector('input[name="size"]:checked');
                const colorElement = document.querySelector('input[name="color"]:checked');
                const quantityElement = document.getElementById('quantity');
                
                const options = {};
                if (sizeElement) options.size = sizeElement.value;
                if (colorElement) options.color = colorElement.value;
                
                const quantity = quantityElement ? parseInt(quantityElement.value) : 1;
                
                // Validate required selections
                const sizeRequired = document.querySelectorAll('input[name="size"]').length > 0;
                const colorRequired = document.querySelectorAll('input[name="color"]').length > 0;
                
                if (sizeRequired && !options.size) {
                    alert('Please select a size');
                    return;
                }
                
                if (colorRequired && !options.color) {
                    alert('Please select a color');
                    return;
                }
                
                if (productId) {
                    this.addItem(productId, quantity, options);
                }
                return;
            }
        };
        
        // Bind the event listener with delegation
        document.addEventListener('click', this.handleAddToCart);
        console.log('Cart: Event listeners bound');
    }
    
    // Utility method to get cart data for checkout
    getCheckoutData() {
        return {
            items: this.cart,
            itemCount: this.getItemCount(),
            timestamp: new Date().toISOString()
        };
    }
}

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize cart if it hasn't been initialized yet
    if (!window.cart) {
        window.cart = new ShoppingCart();
        console.log('Cart system initialized');
    }
    
    // Expose cart methods globally for backwards compatibility
    window.addToCart = (productId, quantity, options) => {
        console.log('Global addToCart called with:', productId, quantity, options);
        return window.cart.addItem(productId, quantity, options);
    };
    window.removeFromCart = (productId, options) => window.cart.removeItem(productId, options);
    window.getCart = () => window.cart.getCart();
    window.saveCart = (cartData) => {
        window.cart.cart = cartData;
        window.cart.saveCart();
    };
    window.updateCartCount = () => window.cart.updateCartCount();
    window.getCartItemCount = () => window.cart.getItemCount();
    
    // Override main.js functions to ensure consistency
    if (window.LagoriiApp) {
        window.LagoriiApp.addToCart = window.addToCart;
        window.LagoriiApp.removeFromCart = window.removeFromCart;
        window.LagoriiApp.getCart = window.getCart;
        window.LagoriiApp.saveCart = window.saveCart;
        window.LagoriiApp.updateCartCount = window.updateCartCount;
    }
    
    // Ensure cart count is updated on page load
    setTimeout(() => {
        window.cart.updateCartCount();
    }, 100);
});
