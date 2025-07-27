<?php
require_once '../config.php';
$pageTitle = 'Checkout';

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="cart.php">Cart</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <h2 class="mb-4">Checkout</h2>
                
                <form id="checkoutForm">
                    <!-- Customer Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Billing Address -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Billing Address</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="address" class="form-label">Street Address *</label>
                                    <input type="text" class="form-control" id="address" required>
                                </div>
                                <div class="col-12">
                                    <label for="address2" class="form-label">Apartment, suite, etc. (optional)</label>
                                    <input type="text" class="form-control" id="address2">
                                </div>
                                <div class="col-md-6">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="city" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="state" class="form-label">State/Province *</label>
                                    <input type="text" class="form-control" id="state" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="zip" class="form-label">ZIP/Postal Code *</label>
                                    <input type="text" class="form-control" id="zip" required>
                                </div>
                                <div class="col-12">
                                    <label for="country" class="form-label">Country *</label>
                                    <select class="form-select" id="country" required>
                                        <option value="">Choose...</option>
                                        <option value="IN">India</option>
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="AE">UAE</option>
                                        <option value="SG">Singapore</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="sameAsShipping" checked>
                                <label class="form-check-label" for="sameAsShipping">
                                    <h5 class="mb-0">Shipping address same as billing</h5>
                                </label>
                            </div>
                        </div>
                        <div class="card-body" id="shippingAddressForm" style="display: none;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="shippingAddress" class="form-label">Street Address *</label>
                                    <input type="text" class="form-control" id="shippingAddress">
                                </div>
                                <div class="col-12">
                                    <label for="shippingAddress2" class="form-label">Apartment, suite, etc. (optional)</label>
                                    <input type="text" class="form-control" id="shippingAddress2">
                                </div>
                                <div class="col-md-6">
                                    <label for="shippingCity" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="shippingCity">
                                </div>
                                <div class="col-md-3">
                                    <label for="shippingState" class="form-label">State/Province *</label>
                                    <input type="text" class="form-control" id="shippingState">
                                </div>
                                <div class="col-md-3">
                                    <label for="shippingZip" class="form-label">ZIP/Postal Code *</label>
                                    <input type="text" class="form-control" id="shippingZip">
                                </div>
                                <div class="col-12">
                                    <label for="shippingCountry" class="form-label">Country *</label>
                                    <select class="form-select" id="shippingCountry">
                                        <option value="">Choose...</option>
                                        <option value="IN">India</option>
                                        <option value="US">United States</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="CA">Canada</option>
                                        <option value="AU">Australia</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                        <option value="AE">UAE</option>
                                        <option value="SG">Singapore</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="payment-methods">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="razorpay" value="razorpay" checked>
                                    <label class="form-check-label" for="razorpay">
                                        <img src="https://cdn.jsdelivr.net/npm/payment-icons@1.2.5/min/flat/razorpay.svg" alt="Razorpay" height="24" class="me-2">
                                        Razorpay (Credit/Debit Card, UPI, Net Banking)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="payment-security mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt text-success me-1"></i>
                                    Your payment information is encrypted and secure
                                </small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="order-summary">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Order items will be loaded here -->
                        <div id="checkoutItemsContainer">
                            <div class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="order-totals">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="checkoutSubtotal">€0</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Shipping:</span>
                                <span class="text-success">Free</span>
                            </div>
                            <div class="d-flex justify-content-between" id="checkoutDiscountRow" style="display: none;">
                                <span>Discount:</span>
                                <span class="text-success" id="checkoutDiscount">-€0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between h5">
                                <strong>Total:</strong>
                                <strong id="checkoutTotal">€0</strong>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-primary w-100 mt-3" id="placeOrderBtn">
                            <i class="fas fa-lock me-2"></i>Place Order
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                By placing your order, you agree to our Terms of Service and Privacy Policy
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Trust badges -->
                <div class="trust-badges mt-4">
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <i class="fas fa-shield-alt text-success fa-2x"></i>
                            <small class="d-block">SSL Secure</small>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-shipping-fast text-success fa-2x"></i>
                            <small class="d-block">Free Shipping</small>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-undo text-success fa-2x"></i>
                            <small class="d-block">30-Day Returns</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Confirmation Modal -->
<div class="modal fade" id="orderConfirmationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>Order Confirmed!
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="py-4">
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                    <h3>Thank you for your order!</h3>
                    <p class="lead">Your order has been successfully placed and will be processed soon.</p>
                    
                    <div class="order-details mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Order Number:</strong>
                                <p id="orderNumber"></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Amount:</strong>
                                <p id="orderAmount"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <p>A confirmation email has been sent to your email address.</p>
                        <p>You can track your order status in your account dashboard.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
                <a href="products.php" class="btn btn-outline-primary">View Products</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCheckoutItems();
    initializeCheckout();
});

function initializeCheckout() {
    // Toggle shipping address form
    document.getElementById('sameAsShipping').addEventListener('change', function() {
        const shippingForm = document.getElementById('shippingAddressForm');
        shippingForm.style.display = this.checked ? 'none' : 'block';
    });
    
    // Place order button
    document.getElementById('placeOrderBtn').addEventListener('click', function() {
        if (validateCheckoutForm()) {
            processPayment();
        }
    });
}

function loadCheckoutItems() {
    const cart = getCart();
    
    if (cart.length === 0) {
        window.location.href = 'cart.php';
        return;
    }
    
    fetch('../api/products.php')
        .then(response => response.json())
        .then(products => {
            renderCheckoutItems(cart, products);
            updateCheckoutSummary(cart, products);
        })
        .catch(error => {
            console.error('Error loading products:', error);
        });
}

function renderCheckoutItems(cart, products) {
    const container = document.getElementById('checkoutItemsContainer');
    container.innerHTML = '';
    
    cart.forEach(cartItem => {
        const product = products.find(p => p.id === cartItem.productId);
        if (!product) return;
        
        const itemElement = document.createElement('div');
        itemElement.className = 'checkout-item d-flex align-items-center mb-3';
        itemElement.innerHTML = `
            <div class="item-image me-3">
                <img src="${product.images[0]}" alt="${product.name}" class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
            </div>
            <div class="item-details flex-grow-1">
                <h6 class="mb-1">${product.name}</h6>
                <small class="text-muted">
                    ${cartItem.options.size ? `Size: ${cartItem.options.size}` : ''}
                    ${cartItem.options.color ? ` | Color: ${cartItem.options.color}` : ''}
                    | Qty: ${cartItem.quantity}
                </small>
            </div>
            <div class="item-price">
                <strong>${formatPrice(product.price * cartItem.quantity)}</strong>
            </div>
        `;
        
        container.appendChild(itemElement);
    });
}

function updateCheckoutSummary(cart, products) {
    let subtotal = 0;
    
    cart.forEach(cartItem => {
        const product = products.find(p => p.id === cartItem.productId);
        if (product) {
            subtotal += product.price * cartItem.quantity;
        }
    });
    
    // Calculate discount
    let discount = 0;
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    if (totalItems >= 3) {
        discount = subtotal * 0.10;
    } else if (totalItems >= 2) {
        discount = subtotal * 0.05;
    }
    
    const total = subtotal - discount;
    
    document.getElementById('checkoutSubtotal').textContent = formatPrice(subtotal);
    document.getElementById('checkoutTotal').textContent = formatPrice(total);
    
    if (discount > 0) {
        document.getElementById('checkoutDiscount').textContent = '-' + formatPrice(discount);
        document.getElementById('checkoutDiscountRow').style.display = 'flex';
    }
}

function validateCheckoutForm() {
    const form = document.getElementById('checkoutForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    // Email validation
    const email = document.getElementById('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
        email.classList.add('is-invalid');
        isValid = false;
    }
    
    if (!isValid) {
        alert('Please fill in all required fields correctly.');
    }
    
    return isValid;
}

function processPayment() {
    const cart = getCart();
    const total = calculateTotal(cart);
    
    // Prepare order data
    const orderData = {
        customer: {
            firstName: document.getElementById('firstName').value,
            lastName: document.getElementById('lastName').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value
        },
        billing: {
            address: document.getElementById('address').value,
            address2: document.getElementById('address2').value,
            city: document.getElementById('city').value,
            state: document.getElementById('state').value,
            zip: document.getElementById('zip').value,
            country: document.getElementById('country').value
        },
        shipping: document.getElementById('sameAsShipping').checked ? null : {
            address: document.getElementById('shippingAddress').value,
            address2: document.getElementById('shippingAddress2').value,
            city: document.getElementById('shippingCity').value,
            state: document.getElementById('shippingState').value,
            zip: document.getElementById('shippingZip').value,
            country: document.getElementById('shippingCountry').value
        },
        items: cart,
        total: total
    };
    
    // Initialize Razorpay
    const options = {
        key: '<?= RAZORPAY_KEY_ID ?>',
        amount: Math.round(total * 100), // Amount in paise
        currency: 'EUR',
        name: '<?= SITE_NAME ?>',
        description: 'Children\'s Clothing Purchase',
        image: 'https://lagorii.com/cdn/shop/files/lagoriilogo_180x.svg?v=1681645627',
        handler: function(response) {
            // Payment successful
            handlePaymentSuccess(response, orderData);
        },
        prefill: {
            name: orderData.customer.firstName + ' ' + orderData.customer.lastName,
            email: orderData.customer.email,
            contact: orderData.customer.phone
        },
        notes: {
            address: orderData.billing.address
        },
        theme: {
            color: '#007bff'
        }
    };
    
    const rzp = new Razorpay(options);
    
    rzp.on('payment.failed', function(response) {
        alert('Payment failed. Please try again.');
        console.error('Payment failed:', response.error);
    });
    
    rzp.open();
}

function handlePaymentSuccess(paymentResponse, orderData) {
    // Create order number
    const orderNumber = 'LK' + Date.now();
    
    // Save order data
    const order = {
        orderNumber: orderNumber,
        paymentId: paymentResponse.razorpay_payment_id,
        orderData: orderData,
        status: 'confirmed',
        date: new Date().toISOString()
    };
    
    // Save to localStorage (in a real app, this would be saved to a database)
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    orders.push(order);
    localStorage.setItem('orders', JSON.stringify(orders));
    
    // Clear cart
    localStorage.removeItem('cart');
    updateCartCount();
    
    // Show confirmation modal
    showOrderConfirmation(orderNumber, orderData.total);
}

function showOrderConfirmation(orderNumber, total) {
    document.getElementById('orderNumber').textContent = orderNumber;
    document.getElementById('orderAmount').textContent = formatPrice(total);
    
    const modal = new bootstrap.Modal(document.getElementById('orderConfirmationModal'));
    modal.show();
}

function calculateTotal(cart) {
    // This should match the calculation in updateCheckoutSummary
    let subtotal = 0;
    
    // For demo purposes, using fixed product data
    cart.forEach(cartItem => {
        // In a real app, you'd fetch the actual product price
        subtotal += 25 * cartItem.quantity; // Using average price
    });
    
    // Calculate discount
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    let discount = 0;
    
    if (totalItems >= 3) {
        discount = subtotal * 0.10;
    } else if (totalItems >= 2) {
        discount = subtotal * 0.05;
    }
    
    return subtotal - discount;
}

function formatPrice(price) {
    return '€' + Math.round(price);
}
</script>

<?php include '../includes/footer.php'; ?>
