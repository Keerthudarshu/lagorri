<?php 
require_once 'config.php';
session_start();

$pageTitle = 'Auth Test';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-shield me-2"></i>Authentication System Test</h3>
                </div>
                <div class="card-body">
                    <div id="authStatus" class="mb-4">
                        <h5>Current Authentication Status:</h5>
                        <div id="statusDisplay" class="alert alert-info">
                            <i class="fas fa-spinner fa-spin me-2"></i>Checking authentication status...
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Test Actions:</h5>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" onclick="testDropdown()">
                                    <i class="fas fa-caret-down me-2"></i>Test User Dropdown
                                </button>
                                <button class="btn btn-outline-primary" onclick="testProfile()">
                                    <i class="fas fa-user me-2"></i>Test My Account
                                </button>
                                <button class="btn btn-outline-primary" onclick="testOrders()">
                                    <i class="fas fa-shopping-bag me-2"></i>Test Order History
                                </button>
                                <button class="btn btn-outline-primary" onclick="testWishlist()">
                                    <i class="fas fa-heart me-2"></i>Test Wishlist
                                </button>
                                <button class="btn btn-outline-danger" onclick="testLogout()">
                                    <i class="fas fa-sign-out-alt me-2"></i>Test Sign Out
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Quick Login (Test):</h5>
                            <form id="quickLoginForm">
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Email" value="test@example.com">
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" placeholder="Password" value="test123">
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt me-2"></i>Quick Login
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Debug Information:</h5>
                        <div id="debugInfo" class="bg-light p-3 rounded">
                            <small class="text-muted">Authentication debug info will appear here...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Test authentication status
function checkAuthStatus() {
    const statusDisplay = document.getElementById('statusDisplay');
    const debugInfo = document.getElementById('debugInfo');
    
    if (window.authManager) {
        const isLoggedIn = authManager.isLoggedIn();
        const user = authManager.getCurrentUser();
        
        if (isLoggedIn && user) {
            statusDisplay.innerHTML = `
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong>Logged In</strong> as ${user.name} (${user.email})
            `;
            statusDisplay.className = 'alert alert-success';
        } else {
            statusDisplay.innerHTML = `
                <i class="fas fa-times-circle text-danger me-2"></i>
                <strong>Not Logged In</strong>
            `;
            statusDisplay.className = 'alert alert-warning';
        }
        
        debugInfo.innerHTML = `
            <strong>AuthManager:</strong> Available<br>
            <strong>User Object:</strong> ${user ? JSON.stringify(user, null, 2) : 'null'}<br>
            <strong>LocalStorage:</strong> ${localStorage.getItem('lagorii_user') || 'empty'}
        `;
    } else {
        statusDisplay.innerHTML = `
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
            <strong>AuthManager not found!</strong> Check if auth.js is loaded.
        `;
        statusDisplay.className = 'alert alert-danger';
        
        debugInfo.innerHTML = `
            <strong>AuthManager:</strong> NOT AVAILABLE<br>
            <strong>Error:</strong> auth.js may not be loaded or initialized
        `;
    }
}

// Test functions
function testDropdown() {
    if (!authManager) {
        alert('AuthManager not available!');
        return;
    }
    
    if (!authManager.isLoggedIn()) {
        alert('Please log in first to test the dropdown.');
        return;
    }
    
    // Force trigger dropdown creation
    authManager.updateAuthUI();
    alert('Dropdown should be updated. Check the navigation bar.');
}

function testProfile() {
    if (authManager) {
        authManager.showProfile();
    } else {
        alert('AuthManager not available!');
    }
}

function testOrders() {
    if (authManager) {
        authManager.showOrders();
    } else {
        alert('AuthManager not available!');
    }
}

function testWishlist() {
    if (authManager) {
        authManager.showWishlist();
    } else {
        alert('AuthManager not available!');
    }
}

function testLogout() {
    if (authManager) {
        if (confirm('Are you sure you want to log out?')) {
            authManager.logout();
        }
    } else {
        alert('AuthManager not available!');
    }
}

// Quick login for testing
document.getElementById('quickLoginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!authManager) {
        alert('AuthManager not available!');
        return;
    }
    
    // Simulate a successful login
    const testUser = {
        id: 1,
        name: 'Test User',
        email: 'test@example.com',
        created_at: new Date().toISOString()
    };
    
    // Store user and update UI
    localStorage.setItem('lagorii_user', JSON.stringify(testUser));
    authManager.user = testUser;
    authManager.updateAuthUI();
    
    // Refresh status
    setTimeout(checkAuthStatus, 100);
    
    alert('Test login successful! Check the navigation bar.');
});

// Check status when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(checkAuthStatus, 500);
});

// Refresh status every few seconds
setInterval(checkAuthStatus, 3000);
</script>

<?php include 'includes/footer.php'; ?>
