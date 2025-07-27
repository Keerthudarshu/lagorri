// Main JavaScript functionality for Lagorii Kids eCommerce site

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize all components
    initializeNavigation();
    initializeSearch();
    initializeProductCards();
    initializeAuth();
    updateCartCount();
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function initializeNavigation() {
    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 100) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });
    
    // Mobile menu toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.toggle('show');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navbarToggler.contains(e.target) && !navbarCollapse.contains(e.target)) {
            navbarCollapse.classList.remove('show');
        }
    });
}

function initializeSearch() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                // Redirect to products page with search parameter
                window.location.href = `pages/products.php?search=${encodeURIComponent(searchTerm)}`;
            }
        });
    }
    
    // Search suggestions (basic implementation)
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = this.value.trim();
                if (query.length > 2) {
                    fetchSearchSuggestions(query);
                } else {
                    hideSearchSuggestions();
                }
            }, 300);
        });
    }
}

function fetchSearchSuggestions(query) {
    // In a real implementation, this would fetch from an API
    const suggestions = [
        'Girls Party Wear',
        'Boys Casual',
        'Infant Rompers',
        'Ethnic Wear',
        'Wedding Collection'
    ].filter(item => item.toLowerCase().includes(query.toLowerCase()));
    
    showSearchSuggestions(suggestions);
}

function showSearchSuggestions(suggestions) {
    let suggestionsList = document.getElementById('searchSuggestions');
    
    if (!suggestionsList) {
        suggestionsList = document.createElement('div');
        suggestionsList.id = 'searchSuggestions';
        suggestionsList.className = 'search-suggestions position-absolute bg-white border rounded shadow-sm w-100';
        suggestionsList.style.zIndex = '1000';
        suggestionsList.style.top = '100%';
        suggestionsList.style.left = '0';
        
        const searchForm = document.getElementById('searchForm');
        searchForm.style.position = 'relative';
        searchForm.appendChild(suggestionsList);
    }
    
    suggestionsList.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'suggestion-item p-2 border-bottom cursor-pointer';
        item.textContent = suggestion;
        item.addEventListener('click', function() {
            document.getElementById('searchInput').value = suggestion;
            hideSearchSuggestions();
            document.getElementById('searchForm').dispatchEvent(new Event('submit'));
        });
        
        item.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.backgroundColor = 'white';
        });
        
        suggestionsList.appendChild(item);
    });
    
    suggestionsList.style.display = 'block';
}

function hideSearchSuggestions() {
    const suggestionsList = document.getElementById('searchSuggestions');
    if (suggestionsList) {
        suggestionsList.style.display = 'none';
    }
}

function initializeProductCards() {
    // Add hover effects and interactions to product cards
    document.querySelectorAll('.product-card').forEach(card => {
        // Add to cart functionality
        const addToCartBtn = card.querySelector('.add-to-cart');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const productId = this.dataset.productId;
                if (productId) {
                    addToCart(productId, 1);
                }
            });
        }
        
        // Wishlist functionality
        const wishlistBtn = card.querySelector('.btn-outline-primary:not(.add-to-cart)');
        if (wishlistBtn && wishlistBtn.innerHTML.includes('heart')) {
            wishlistBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle wishlist state
                const icon = this.querySelector('i');
                if (icon.classList.contains('far')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-primary');
                    showToast('Added to wishlist');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-outline-primary');
                    showToast('Removed from wishlist');
                }
            });
        }
    });
}

function initializeAuth() {
    // Check if user is logged in
    const user = getCurrentUser();
    const authLink = document.getElementById('authLink');
    
    if (user && authLink) {
        authLink.innerHTML = `<i class="fas fa-user"></i> ${user.name}`;
        authLink.href = '#';
        
        // Add dropdown for user menu
        authLink.addEventListener('click', function(e) {
            e.preventDefault();
            showUserMenu();
        });
    }
}

function showUserMenu() {
    // Create user dropdown menu
    let userMenu = document.getElementById('userMenu');
    
    if (!userMenu) {
        userMenu = document.createElement('div');
        userMenu.id = 'userMenu';
        userMenu.className = 'user-menu position-absolute bg-white border rounded shadow-sm';
        userMenu.style.cssText = `
            top: 100%;
            right: 0;
            min-width: 200px;
            z-index: 1000;
            display: none;
        `;
        
        userMenu.innerHTML = `
            <div class="p-2">
                <a href="#" class="dropdown-item">My Account</a>
                <a href="#" class="dropdown-item">Order History</a>
                <a href="#" class="dropdown-item">Wishlist</a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item" onclick="logout()">Sign Out</a>
            </div>
        `;
        
        document.getElementById('authLink').parentNode.style.position = 'relative';
        document.getElementById('authLink').parentNode.appendChild(userMenu);
    }
    
    userMenu.style.display = userMenu.style.display === 'block' ? 'none' : 'block';
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!userMenu.contains(e.target) && !document.getElementById('authLink').contains(e.target)) {
            userMenu.style.display = 'none';
        }
    });
}

function getCurrentUser() {
    try {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    } catch (e) {
        return null;
    }
}

function logout() {
    fetch('api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'logout'
        })
    })
    .then(response => response.json())
    .then(data => {
        localStorage.removeItem('user');
        window.location.reload();
    })
    .catch(error => {
        console.error('Logout error:', error);
        localStorage.removeItem('user');
        window.location.reload();
    });
}

function updateCartCount() {
    const cart = getCart();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
        cartCountElement.style.display = totalItems > 0 ? 'inline' : 'none';
    }
}

function getCart() {
    try {
        const cartStr = localStorage.getItem('cart');
        return cartStr ? JSON.parse(cartStr) : [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(productId, quantity = 1, options = {}) {
    const cart = getCart();
    
    // Check if item already exists
    const existingItemIndex = cart.findIndex(item => 
        item.productId === productId && 
        JSON.stringify(item.options) === JSON.stringify(options)
    );
    
    if (existingItemIndex >= 0) {
        cart[existingItemIndex].quantity += quantity;
    } else {
        cart.push({
            productId: productId,
            quantity: quantity,
            options: options,
            addedAt: new Date().toISOString()
        });
    }
    
    saveCart(cart);
    updateCartCount();
    
    // Show success message
    showToast('Item added to cart!');
    
    // Animate cart icon
    animateCartIcon();
}

function removeFromCart(productId) {
    let cart = getCart();
    cart = cart.filter(item => item.productId !== productId);
    saveCart(cart);
    updateCartCount();
    
    showToast('Item removed from cart');
}

function animateCartIcon() {
    const cartIcon = document.querySelector('a[href*="cart"] i');
    if (cartIcon) {
        cartIcon.classList.add('animate__animated', 'animate__bounce');
        setTimeout(() => {
            cartIcon.classList.remove('animate__animated', 'animate__bounce');
        }, 1000);
    }
}

function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

function formatPrice(price, currency = '€') {
    return `${currency}${Math.round(price)}`;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Lazy loading for images
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading if supported
if ('IntersectionObserver' in window) {
    document.addEventListener('DOMContentLoaded', initializeLazyLoading);
}

// Service Worker registration for PWA (optional)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// Export functions for use in other scripts
window.LagoriiApp = {
    addToCart,
    removeFromCart,
    getCart,
    saveCart,
    getCurrentUser,
    showToast,
    formatPrice,
    updateCartCount
};
