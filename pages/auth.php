<?php
require_once '../config.php';
$pageTitle = 'Sign In / Sign Up';

include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="auth-container">
                <!-- Auth Toggle Buttons -->
                <div class="auth-toggle mb-4">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="signInTab">Sign In</button>
                        <button type="button" class="btn btn-outline-primary" id="signUpTab">Sign Up</button>
                    </div>
                </div>
                
                <!-- Sign In Form -->
                <div class="auth-form" id="signInForm">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title text-center mb-4">Welcome Back!</h3>
                            <form id="loginForm">
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="loginEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="loginPassword" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                                <div class="text-center">
                                    <a href="#" class="text-decoration-none">Forgot your password?</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Sign Up Form -->
                <div class="auth-form" id="signUpForm" style="display: none;">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title text-center mb-4">Create Account</h3>
                            <form id="registerForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstName" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="registerEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="registerEmail" required>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone">
                                </div>
                                <div class="mb-3">
                                    <label for="registerPassword" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="registerPassword" required>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="agreeTerms" required>
                                    <label class="form-check-label" for="agreeTerms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        Subscribe to our newsletter for updates and special offers
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Create Account</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Success Message -->
                <div class="alert alert-success" id="successMessage" style="display: none;">
                    <h5 class="alert-heading">Success!</h5>
                    <p id="successText"></p>
                </div>
                
                <!-- Error Message -->
                <div class="alert alert-danger" id="errorMessage" style="display: none;">
                    <h5 class="alert-heading">Error!</h5>
                    <p id="errorText"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Benefits Section -->
<section class="customer-benefits py-5 bg-light">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-lg-3 col-md-6">
                <div class="benefit-item">
                    <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                    <h5>Free Worldwide Shipping</h5>
                    <p>On all orders, no minimum purchase required</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="benefit-item">
                    <i class="fas fa-medal fa-3x text-primary mb-3"></i>
                    <h5>Premium Quality</h5>
                    <p>Carefully crafted with the finest materials</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="benefit-item">
                    <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                    <h5>Easy Returns</h5>
                    <p>30-day hassle-free return policy</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="benefit-item">
                    <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                    <h5>1 Lakh+ Happy Parents</h5>
                    <p>Trusted by families worldwide</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeAuthPage();
});

function initializeAuthPage() {
    // Tab switching
    document.getElementById('signInTab').addEventListener('click', function() {
        showSignInForm();
    });
    
    document.getElementById('signUpTab').addEventListener('click', function() {
        showSignUpForm();
    });
    
    // Form submissions
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('registerForm').addEventListener('submit', handleRegister);
    
    // Password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', validatePasswordMatch);
}

function showSignInForm() {
    document.getElementById('signInTab').classList.add('active');
    document.getElementById('signUpTab').classList.remove('active');
    document.getElementById('signInForm').style.display = 'block';
    document.getElementById('signUpForm').style.display = 'none';
    hideMessages();
}

function showSignUpForm() {
    document.getElementById('signUpTab').classList.add('active');
    document.getElementById('signInTab').classList.remove('active');
    document.getElementById('signUpForm').style.display = 'block';
    document.getElementById('signInForm').style.display = 'none';
    hideMessages();
}

function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    
    if (!email || !password) {
        showError('Please fill in all fields');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Signing In...';
    submitBtn.disabled = true;
    
    // Make API request
    fetch('../api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'login',
            email: email,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Login successful! Redirecting...');
            
            // Update localStorage
            localStorage.setItem('user', JSON.stringify(data.user));
            
            // Update navigation
            updateAuthNavigation(data.user);
            
            // Redirect after delay
            setTimeout(() => {
                window.location.href = '../index.php';
            }, 1500);
        } else {
            showError(data.error || 'Login failed');
        }
    })
    .catch(error => {
        showError('Network error. Please try again.');
        console.error('Login error:', error);
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function handleRegister(e) {
    e.preventDefault();
    
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('registerEmail').value;
    const phone = document.getElementById('phone').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const agreeTerms = document.getElementById('agreeTerms').checked;
    
    // Validation
    if (!firstName || !lastName || !email || !password || !confirmPassword) {
        showError('Please fill in all required fields');
        return;
    }
    
    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return;
    }
    
    if (password.length < 6) {
        showError('Password must be at least 6 characters long');
        return;
    }
    
    if (!agreeTerms) {
        showError('Please agree to the Terms of Service and Privacy Policy');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creating Account...';
    submitBtn.disabled = true;
    
    // Make API request
    fetch('../api/auth.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'register',
            firstName: firstName,
            lastName: lastName,
            email: email,
            phone: phone,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Account created successfully! Welcome to Lagorii Kids!');
            
            // Update localStorage
            localStorage.setItem('user', JSON.stringify(data.user));
            
            // Update navigation
            updateAuthNavigation(data.user);
            
            // Redirect after delay
            setTimeout(() => {
                window.location.href = '../index.php';
            }, 1500);
        } else {
            showError(data.error || 'Registration failed');
        }
    })
    .catch(error => {
        showError('Network error. Please try again.');
        console.error('Registration error:', error);
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function validatePasswordMatch() {
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const confirmField = document.getElementById('confirmPassword');
    
    if (confirmPassword && password !== confirmPassword) {
        confirmField.classList.add('is-invalid');
        if (!confirmField.nextElementSibling || !confirmField.nextElementSibling.classList.contains('invalid-feedback')) {
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = 'Passwords do not match';
            confirmField.parentNode.appendChild(feedback);
        }
    } else {
        confirmField.classList.remove('is-invalid');
        const feedback = confirmField.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.remove();
        }
    }
}

function showSuccess(message) {
    hideMessages();
    document.getElementById('successText').textContent = message;
    document.getElementById('successMessage').style.display = 'block';
    document.getElementById('successMessage').scrollIntoView({ behavior: 'smooth' });
}

function showError(message) {
    hideMessages();
    document.getElementById('errorText').textContent = message;
    document.getElementById('errorMessage').style.display = 'block';
    document.getElementById('errorMessage').scrollIntoView({ behavior: 'smooth' });
}

function hideMessages() {
    document.getElementById('successMessage').style.display = 'none';
    document.getElementById('errorMessage').style.display = 'none';
}

function updateAuthNavigation(user) {
    const authLink = document.getElementById('authLink');
    if (authLink) {
        authLink.innerHTML = `<i class="fas fa-user"></i> ${user.name}`;
        authLink.href = '#';
        authLink.addEventListener('click', function(e) {
            e.preventDefault();
            // Show user menu or logout functionality
            if (confirm('Do you want to sign out?')) {
                logout();
            }
        });
    }
}

function logout() {
    fetch('../api/auth.php', {
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
</script>

<?php include '../includes/footer.php'; ?>
