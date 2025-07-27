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

<!-- Cart Item Template -->
<template id="cartItemTemplate">
    <div class="cart-item">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="" alt="" class="img-fluid rounded cart-item-image">
                    </div>
                    <div class="col-md-4">
                        <h6 class="cart-item-name mb-1"></h6>
                        <div class="cart-item-options">
                            <small class="text-muted cart-item-size"></small>
                            <small class="text-muted cart-item-color"></small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary btn-sm quantity-decrease" type="button">-</button>
                            <input type="number" class="form-control form-control-sm text-center cart-item-quantity" min="1" max="10">
                            <button class="btn btn-outline-secondary btn-sm quantity-increase" type="button">+</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <span class="cart-item-price h6"></span>
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-outline-danger btn-sm remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

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
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h4>Your cart is empty</h4>
                <p class="text-muted">Add some products to get started!</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        `;
        document.getElementById('proceedToCheckout').disabled = true;
        return;
    }
    
    // Load product data for cart items
    fetch('../api/products.php')
        .then(response => response.json())
        .then(products => {
            renderCartItems(cart, products);
            updateCartSummary(cart, products);
            document.getElementById('proceedToCheckout').disabled = false;
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
}

function renderCartItems(cart, products) {
    const container = document.getElementById('cartItemsContainer');
    const template = document.getElementById('cartItemTemplate');
    
    container.innerHTML = '';
    
    cart.forEach(cartItem => {
        const product = products.find(p => p.id === cartItem.productId);
        if (!product) return;
        
        const itemElement = template.content.cloneNode(true);
        
        // Populate item data
        itemElement.querySelector('.cart-item-image').src = product.images[0];
        itemElement.querySelector('.cart-item-image').alt = product.name;
        itemElement.querySelector('.cart-item-name').textContent = product.name;
        itemElement.querySelector('.cart-item-quantity').value = cartItem.quantity;
        itemElement.querySelector('.cart-item-price').textContent = formatPrice(product.price * cartItem.quantity);
        
        // Set options
        if (cartItem.options.size) {
            itemElement.querySelector('.cart-item-size').textContent = `Size: ${cartItem.options.size}`;
        }
        if (cartItem.options.color) {
            itemElement.querySelector('.cart-item-color').textContent = ` | Color: ${cartItem.options.color}`;
        }
        
        // Add event listeners
        const decreaseBtn = itemElement.querySelector('.quantity-decrease');
        const increaseBtn = itemElement.querySelector('.quantity-increase');
        const quantityInput = itemElement.querySelector('.cart-item-quantity');
        const removeBtn = itemElement.querySelector('.remove-item');
        
        decreaseBtn.addEventListener('click', () => {
            updateCartItemQuantity(cartItem.productId, Math.max(1, cartItem.quantity - 1));
        });
        
        increaseBtn.addEventListener('click', () => {
            updateCartItemQuantity(cartItem.productId, Math.min(10, cartItem.quantity + 1));
        });
        
        quantityInput.addEventListener('change', (e) => {
            const newQuantity = parseInt(e.target.value);
            if (newQuantity >= 1 && newQuantity <= 10) {
                updateCartItemQuantity(cartItem.productId, newQuantity);
            }
        });
        
        removeBtn.addEventListener('click', () => {
            removeFromCart(cartItem.productId);
        });
        
        container.appendChild(itemElement);
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

function updateCartSummary(cart, products) {
    let subtotal = 0;
    
    cart.forEach(cartItem => {
        const product = products.find(p => p.id === cartItem.productId);
        if (product) {
            subtotal += product.price * cartItem.quantity;
        }
    });
    
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
            addToCart(this.dataset.productId, 1);
        });
    });
}

function formatPrice(price) {
    return '€' + Math.round(price);
}
</script>

<?php include '../includes/footer.php'; ?>
