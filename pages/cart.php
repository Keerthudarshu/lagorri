<?php
require_once '../config.php';
$pageTitle = 'Shopping Cart';

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item active">Shopping Cart</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="cart-items">
                <h2 class="mb-4">Shopping Cart</h2>
                
                <!-- Cart items will be loaded here by JavaScript -->
                <div id="cartItemsContainer">
                    <div class="empty-cart text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4>Your cart is empty</h4>
                        <p class="text-muted">Add some products to get started!</p>
                        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="cart-summary">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span id="cartSubtotal">€0</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Shipping:</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between" id="discountRow" style="display: none;">
                            <span>Discount:</span>
                            <span class="text-success" id="cartDiscount">-€0</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <strong>Total:</strong>
                            <strong id="cartTotal">€0</strong>
                        </div>
                        
                        <!-- Discount info -->
                        <div class="discount-info mt-3 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-tag me-1"></i>
                                <strong>Special Offers:</strong><br>
                                Buy 2 items: Save 5%<br>
                                Buy 3+ items: Save 10%
                            </small>
                        </div>
                        
                        <button class="btn btn-primary w-100 mt-3" id="proceedToCheckout" disabled>
                            Proceed to Checkout
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="products.php" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Trust badges -->
                <div class="trust-badges mt-4">
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <i class="fas fa-shield-alt text-success fa-2x"></i>
                            <small class="d-block">Secure Payment</small>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-shipping-fast text-success fa-2x"></i>
                            <small class="d-block">Free Shipping</small>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-undo text-success fa-2x"></i>
                            <small class="d-block">Easy Returns</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recently Viewed Products -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">You Might Also Like</h3>
            <div class="row g-4" id="recommendedProducts">
                <!-- Recommended products will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
    loadRecommendedProducts();
    
    // Proceed to checkout
    document.getElementById('proceedToCheckout').addEventListener('click', function() {
        if (getCartItemCount() > 0) {
            window.location.href = 'checkout.php';
        }
    });
});

function loadCartItems() {
    const cart = getCart();
    const container = document.getElementById('cartItemsContainer');
    
    console.log('Cart: Loading cart items...', cart);
    
    if (cart.length === 0) {
        console.log('Cart: Cart is empty');
        container.innerHTML = `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Add some products to get started!</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        `;
        updateCartSummary([], []);
        document.getElementById('proceedToCheckout').disabled = true;
        return;
    }
    
    console.log('Cart: Found', cart.length, 'items in cart, fetching product data...');
    
    // Load product data for cart items
    fetch('../api/products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(products => {
            console.log('Cart: Loaded products from API:', products.length);
            renderCartItems(cart, products);
            updateCartSummary(cart, products);
            document.getElementById('proceedToCheckout').disabled = false;
        })
        .catch(error => {
            console.error('Error loading products:', error);
            // Show error message to user
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5>Error Loading Cart Items</h5>
                    <p>There was a problem loading your cart items. Please refresh the page.</p>
                    <small>Error: ${error.message}</small>
                </div>
            `;
        });
}

function renderCartItems(cart, products) {
    const container = document.getElementById('cartItemsContainer');
    
    console.log('Cart: Rendering cart items...', { cartItems: cart.length, products: products.length });
    
    container.innerHTML = '';
    
    cart.forEach((cartItem, index) => {
        console.log(`Cart: Processing cart item ${index + 1}:`, cartItem);
        
        const product = products.find(p => p.id == cartItem.productId);
        if (!product) {
            console.warn(`Cart: Product not found for ID: ${cartItem.productId}`);
            // Show a placeholder for missing product
            const missingElement = document.createElement('div');
            missingElement.className = 'cart-item';
            missingElement.innerHTML = `
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <strong>Product not found</strong><br>
                            Product ID: ${cartItem.productId}<br>
                            <button class="btn btn-sm btn-danger mt-2" onclick="removeCartItem('${cartItem.productId}')">
                                Remove from cart
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(missingElement);
            return;
        }
        
        const totalPrice = product.price * cartItem.quantity;
        
        console.log(`Cart: Rendering product: ${product.name}, Price: ${product.price}, Quantity: ${cartItem.quantity}`);
        
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        itemElement.dataset.productId = cartItem.productId;
        itemElement.innerHTML = `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="${product.images && product.images[0] ? product.images[0] : 'https://via.placeholder.com/80x80?text=No+Image'}" 
                                 alt="${product.name}" 
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
        `;
        
        container.appendChild(itemElement);
    });
    
    console.log('Cart: Finished rendering cart items, binding events...');
    // Add event listeners for cart controls
    bindCartEvents();
}

function bindCartEvents() {
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
                updateCartItemQuantity(productId, newQuantity);
            }
        });
    });
    
    // Quantity input changes
    document.querySelectorAll('.cart-item-quantity').forEach(input => {
        input.addEventListener('change', (e) => {
            const productId = e.target.dataset.productId;
            const quantity = parseInt(e.target.value);
            
            if (quantity >= 1 && quantity <= 10) {
                updateCartItemQuantity(productId, quantity);
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
                removeCartItem(productId);
            }
        });
    });
}

function updateCartItemQuantity(productId, quantity) {
    const cart = getCart();
    const itemIndex = cart.findIndex(item => item.productId === productId);
    
    if (itemIndex !== -1) {
        cart[itemIndex].quantity = quantity;
        saveCart(cart);
        loadCartItems(); // Reload cart display
        updateCartCount();
    }
}

function removeCartItem(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.productId !== productId);
    saveCart(cart);
    loadCartItems(); // Reload cart display
    updateCartCount();
    
    // Show toast notification
    if (window.LagoriiApp && window.LagoriiApp.showToast) {
        window.LagoriiApp.showToast('Item removed from cart');
    }
}

function getCart() {
    try {
        const cartStr = localStorage.getItem('lagorii_cart');
        return cartStr ? JSON.parse(cartStr) : [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem('lagorii_cart', JSON.stringify(cart));
    updateCartCount();
}

function getCartItemCount() {
    const cart = getCart();
    return cart.reduce((sum, item) => sum + item.quantity, 0);
}

function updateCartCount() {
    const count = getCartItemCount();
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        cartCountElement.style.display = count > 0 ? 'inline' : 'none';
    }
}

function updateCartSummary(cart, products) {
    let subtotal = 0;
    
    if (products && products.length > 0) {
        cart.forEach(cartItem => {
            const product = products.find(p => p.id === cartItem.productId);
            if (product) {
                subtotal += product.price * cartItem.quantity;
            }
        });
    }
    
    // Calculate discount based on quantity
    let discount = 0;
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalItems >= 3) {
        discount = subtotal * 0.10; // 10% discount
    } else if (totalItems >= 2) {
        discount = subtotal * 0.05; // 5% discount
    }
    
    const total = subtotal - discount;
    
    document.getElementById('cartSubtotal').textContent = formatPrice(subtotal);
    document.getElementById('cartTotal').textContent = formatPrice(total);
    
    if (discount > 0) {
        document.getElementById('cartDiscount').textContent = '-' + formatPrice(discount);
        document.getElementById('discountRow').style.display = 'flex';
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }
    
    // Enable/disable checkout button
    const checkoutBtn = document.getElementById('proceedToCheckout');
    if (checkoutBtn) {
        checkoutBtn.disabled = cart.length === 0;
    }
}

function loadRecommendedProducts() {
    fetch('../api/products.php?featured=true&limit=4')
        .then(response => response.json())
        .then(products => {
            renderRecommendedProducts(products);
        })
        .catch(error => {
            console.error('Error loading recommended products:', error);
        });
}

function renderRecommendedProducts(products) {
    const container = document.getElementById('recommendedProducts');
    
    products.forEach(product => {
        const productElement = document.createElement('div');
        productElement.className = 'col-lg-3 col-md-6';
        productElement.innerHTML = `
            <div class="product-card">
                <div class="product-image">
                    <a href="product-detail.php?id=${product.id}">
                        <img src="${product.images[0]}" alt="${product.name}" class="img-fluid">
                    </a>
                    <div class="product-actions">
                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="${product.id}">
                            <i class="fas fa-cart-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info p-3">
                    <h6 class="product-title">
                        <a href="product-detail.php?id=${product.id}" class="text-decoration-none">
                            ${product.name}
                        </a>
                    </h6>
                    <div class="product-price">
                        <span class="current-price">${formatPrice(product.price)}</span>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(productElement);
    });
    
    // Add event listeners for add to cart buttons
    container.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const cart = getCart();
            
            // Check if item already exists
            const existingItemIndex = cart.findIndex(item => item.productId === productId);
            
            if (existingItemIndex >= 0) {
                cart[existingItemIndex].quantity += 1;
            } else {
                cart.push({
                    productId: productId,
                    quantity: 1,
                    options: {},
                    addedAt: new Date().toISOString()
                });
            }
            
            saveCart(cart);
            
            // Show success message
            if (window.LagoriiApp && window.LagoriiApp.showToast) {
                window.LagoriiApp.showToast('Item added to cart!');
            }
        });
    });
}

function formatPrice(price) {
    return '€' + Math.round(price);
}
</script>

<?php include '../includes/footer.php'; ?>
