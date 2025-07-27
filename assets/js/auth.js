// Authentication functionality for Lagorii Kids

class AuthManager {
    constructor() {
        this.user = null;
        this.init();
    }
    
    init() {
        this.loadUserFromStorage();
        this.updateAuthUI();
        this.bindEvents();
    }
    
    loadUserFromStorage() {
        try {
            const userData = localStorage.getItem('lagorii_user');
            this.user = userData ? JSON.parse(userData) : null;
        } catch (error) {
            console.error('Error loading user data:', error);
            this.user = null;
        }
    }
    
    saveUserToStorage(user) {
        try {
            if (user) {
                localStorage.setItem('lagorii_user', JSON.stringify(user));
            } else {
                localStorage.removeItem('lagorii_user');
            }
            this.user = user;
            this.updateAuthUI();
        } catch (error) {
            console.error('Error saving user data:', error);
        }
    }
    
    async login(email, password) {
        try {
            const response = await fetch('../api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'login',
                    email: email,
                    password: password
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.saveUserToStorage(data.user);
                return { success: true, user: data.user };
            } else {
                return { success: false, error: data.error };
            }
        } catch (error) {
            console.error('Login error:', error);
            return { success: false, error: 'Network error. Please try again.' };
        }
    }
    
    async register(userData) {
        try {
            const response = await fetch('../api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'register',
                    ...userData
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.saveUserToStorage(data.user);
                return { success: true, user: data.user };
            } else {
                return { success: false, error: data.error };
            }
        } catch (error) {
            console.error('Registration error:', error);
            return { success: false, error: 'Network error. Please try again.' };
        }
    }
    
    async logout() {
        try {
            await fetch('../api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'logout'
                })
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            this.saveUserToStorage(null);
            window.location.href = '../index.php';
        }
    }
    
    updateAuthUI() {
        const authLink = document.getElementById('authLink');
        if (!authLink) return;
        
        if (this.user) {
            // User is logged in
            authLink.innerHTML = `<i class="fas fa-user"></i> ${this.user.name}`;
            authLink.href = '#';
            authLink.classList.add('dropdown-toggle');
            
            // Create or update user dropdown
            this.createUserDropdown(authLink);
        } else {
            // User is not logged in
            authLink.innerHTML = '<i class="fas fa-user"></i> Sign In';
            authLink.href = 'pages/auth.php';
            authLink.classList.remove('dropdown-toggle');
            
            // Remove dropdown if it exists
            this.removeUserDropdown();
        }
    }
    
    createUserDropdown(authLink) {
        // Remove existing dropdown
        this.removeUserDropdown();
        
        const dropdown = document.createElement('ul');
        dropdown.id = 'userDropdown';
        dropdown.className = 'dropdown-menu';
        dropdown.innerHTML = `
            <li><a class="dropdown-item" href="#" onclick="authManager.showProfile()">
                <i class="fas fa-user me-2"></i>My Profile
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="authManager.showOrders()">
                <i class="fas fa-shopping-bag me-2"></i>My Orders
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="authManager.showWishlist()">
                <i class="fas fa-heart me-2"></i>Wishlist
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" onclick="authManager.logout()">
                <i class="fas fa-sign-out-alt me-2"></i>Sign Out
            </a></li>
        `;
        
        // Make parent li a dropdown
        const parentLi = authLink.parentElement;
        parentLi.classList.add('dropdown');
        parentLi.appendChild(dropdown);
        
        // Add Bootstrap dropdown behavior
        authLink.setAttribute('data-bs-toggle', 'dropdown');
        authLink.setAttribute('aria-expanded', 'false');
    }
    
    removeUserDropdown() {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown) {
            dropdown.remove();
        }
        
        const authLink = document.getElementById('authLink');
        if (authLink) {
            const parentLi = authLink.parentElement;
            parentLi.classList.remove('dropdown');
            authLink.removeAttribute('data-bs-toggle');
            authLink.removeAttribute('aria-expanded');
        }
    }
    
    bindEvents() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }
        
        // Registration form
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => this.handleRegister(e));
        }
        
        // Password confirmation validation
        const confirmPassword = document.getElementById('confirmPassword');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', this.validatePasswordMatch);
        }
        
        // Form switching
        const signInTab = document.getElementById('signInTab');
        const signUpTab = document.getElementById('signUpTab');
        
        if (signInTab) {
            signInTab.addEventListener('click', () => this.showSignInForm());
        }
        
        if (signUpTab) {
            signUpTab.addEventListener('click', () => this.showSignUpForm());
        }
    }
    
    async handleLogin(e) {
        e.preventDefault();
        
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;
        
        if (!email || !password) {
            this.showError('Please fill in all fields');
            return;
        }
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            submitBtn.textContent = 'Signing In...';
            submitBtn.disabled = true;
            
            const result = await this.login(email, password);
            
            if (result.success) {
                this.showSuccess('Login successful! Redirecting...');
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 1500);
            } else {
                this.showError(result.error);
            }
        } catch (error) {
            this.showError('An unexpected error occurred');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
    
    async handleRegister(e) {
        e.preventDefault();
        
        const formData = {
            firstName: document.getElementById('firstName').value.trim(),
            lastName: document.getElementById('lastName').value.trim(),
            email: document.getElementById('registerEmail').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            password: document.getElementById('registerPassword').value,
            confirmPassword: document.getElementById('confirmPassword').value
        };
        
        // Validation
        if (!this.validateRegistrationForm(formData)) {
            return;
        }
        
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        try {
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;
            
            const result = await this.register(formData);
            
            if (result.success) {
                this.showSuccess('Account created successfully! Welcome to Lagorii Kids!');
                setTimeout(() => {
                    window.location.href = '../index.php';
                }, 1500);
            } else {
                this.showError(result.error);
            }
        } catch (error) {
            this.showError('An unexpected error occurred');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
    
    validateRegistrationForm(data) {
        // Check required fields
        if (!data.firstName || !data.lastName || !data.email || !data.password) {
            this.showError('Please fill in all required fields');
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(data.email)) {
            this.showError('Please enter a valid email address');
            return false;
        }
        
        // Password validation
        if (data.password.length < 6) {
            this.showError('Password must be at least 6 characters long');
            return false;
        }
        
        // Password confirmation
        if (data.password !== data.confirmPassword) {
            this.showError('Passwords do not match');
            return false;
        }
        
        // Terms agreement
        const agreeTerms = document.getElementById('agreeTerms');
        if (!agreeTerms.checked) {
            this.showError('Please agree to the Terms of Service and Privacy Policy');
            return false;
        }
        
        return true;
    }
    
    validatePasswordMatch() {
        const password = document.getElementById('registerPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const confirmField = document.getElementById('confirmPassword');
        
        if (confirmPassword && password !== confirmPassword) {
            confirmField.classList.add('is-invalid');
            
            // Add or update error message
            let feedback = confirmField.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                confirmField.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'Passwords do not match';
        } else {
            confirmField.classList.remove('is-invalid');
            
            // Remove error message
            const feedback = confirmField.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.remove();
            }
        }
    }
    
    showSignInForm() {
        document.getElementById('signInTab').classList.add('active');
        document.getElementById('signUpTab').classList.remove('active');
        document.getElementById('signInForm').style.display = 'block';
        document.getElementById('signUpForm').style.display = 'none';
        this.hideMessages();
    }
    
    showSignUpForm() {
        document.getElementById('signUpTab').classList.add('active');
        document.getElementById('signInTab').classList.remove('active');
        document.getElementById('signUpForm').style.display = 'block';
        document.getElementById('signInForm').style.display = 'none';
        this.hideMessages();
    }
    
    showSuccess(message) {
        this.hideMessages();
        const successElement = document.getElementById('successMessage');
        const successText = document.getElementById('successText');
        if (successElement && successText) {
            successText.textContent = message;
            successElement.style.display = 'block';
            successElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    showError(message) {
        this.hideMessages();
        const errorElement = document.getElementById('errorMessage');
        const errorText = document.getElementById('errorText');
        if (errorElement && errorText) {
            errorText.textContent = message;
            errorElement.style.display = 'block';
            errorElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    hideMessages() {
        const successElement = document.getElementById('successMessage');
        const errorElement = document.getElementById('errorMessage');
        if (successElement) successElement.style.display = 'none';
        if (errorElement) errorElement.style.display = 'none';
    }
    
    // User profile methods
    showProfile() {
        alert('Profile page coming soon!');
    }
    
    showOrders() {
        alert('Order history page coming soon!');
    }
    
    showWishlist() {
        alert('Wishlist page coming soon!');
    }
    
    // Utility methods
    isLoggedIn() {
        return this.user !== null;
    }
    
    getCurrentUser() {
        return this.user;
    }
    
    getUserId() {
        return this.user ? this.user.id : null;
    }
    
    getUserEmail() {
        return this.user ? this.user.email : null;
    }
    
    getUserName() {
        return this.user ? this.user.name : null;
    }
}

// Initialize auth manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.authManager = new AuthManager();
    
    // Expose auth methods globally for backwards compatibility
    window.getCurrentUser = () => window.authManager.getCurrentUser();
    window.isLoggedIn = () => window.authManager.isLoggedIn();
    window.logout = () => window.authManager.logout();
});

// Handle authentication redirect for protected pages
function requireAuth() {
    if (!window.authManager || !window.authManager.isLoggedIn()) {
        const currentPath = window.location.pathname;
        const loginUrl = currentPath.includes('/pages/') ? 'auth.php' : 'pages/auth.php';
        window.location.href = `${loginUrl}?redirect=${encodeURIComponent(currentPath)}`;
        return false;
    }
    return true;
}

// Auto-redirect after login if redirect parameter exists
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const redirectPath = urlParams.get('redirect');
    
    if (redirectPath && window.authManager && window.authManager.isLoggedIn()) {
        window.location.href = redirectPath;
    }
});
