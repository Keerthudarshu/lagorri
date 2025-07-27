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
            const response = await fetch(this.getApiPath('auth.php'), {
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
            const response = await fetch(this.getApiPath('auth.php'), {
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
            await fetch(this.getApiPath('auth.php'), {
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
            window.location.href = this.getBasePath() + 'index.php';
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
        
        // Create dropdown container
        const dropdown = document.createElement('ul');
        dropdown.id = 'userDropdown';
        dropdown.className = 'dropdown-menu dropdown-menu-end';
        dropdown.innerHTML = `
            <li>
                <h6 class="dropdown-header">
                    <i class="fas fa-user me-2"></i>Welcome, ${this.user.name}!
                </h6>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#" id="profileDropdownBtn">
                <i class="fas fa-user me-2"></i>My Account
            </a></li>
            <li><a class="dropdown-item" href="#" id="ordersDropdownBtn">
                <i class="fas fa-shopping-bag me-2"></i>Order History
            </a></li>
            <li><a class="dropdown-item" href="#" id="wishlistDropdownBtn">
                <i class="fas fa-heart me-2"></i>My Wishlist
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" id="logoutDropdownBtn">
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
        authLink.classList.add('dropdown-toggle');
        
        // Add event listeners after DOM update
        setTimeout(() => {
            const profileBtn = document.getElementById('profileDropdownBtn');
            const ordersBtn = document.getElementById('ordersDropdownBtn');
            const wishlistBtn = document.getElementById('wishlistDropdownBtn');
            const logoutBtn = document.getElementById('logoutDropdownBtn');
            
            if (profileBtn) {
                profileBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showProfile();
                });
            }
            
            if (ordersBtn) {
                ordersBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showOrders();
                });
            }
            
            if (wishlistBtn) {
                wishlistBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.showWishlist();
                });
            }
            
            if (logoutBtn) {
                logoutBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.logout();
                });
            }
        }, 100);
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

        // Registration form email field - trigger OTP on blur
        const registerEmail = document.getElementById('registerEmail');
        if (registerEmail) {
            registerEmail.addEventListener('blur', async (e) => {
                const email = e.target.value.trim();
                if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    // Send OTP and show modal
                    try {
                        const result = await this.sendEmailOTP(email);
                        if (result.success) {
                            // Store temp registration data with just email for modal
                            this.tempRegistrationData = { email };
                            this.showEmailVerificationModal();
                        } else {
                            this.showError(result.error || 'Failed to send verification code');
                        }
                    } catch (err) {
                        this.showError('Network error. Please try again.');
                    }
                }
            });
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
                    window.location.href = this.getBasePath() + 'index.php';
                }, 1500);
            } else {
                this.showError(result.error);
            }
        } catch (error) {
            this.showError('An unexpected error occurred');
        }
    }
    
    async startVerificationProcess(formData) {
        // Store registration data temporarily
        this.tempRegistrationData = formData;
        
        // Step 1: Send Email OTP
        const emailOtpResult = await this.sendEmailOTP(formData.email);
        
        if (emailOtpResult.success) {
            this.showEmailVerificationModal();
        } else {
            throw new Error(emailOtpResult.error);
        }
    }
    
    async sendEmailOTP(email) {
        try {
            const apiUrl = this.getApiPath('auth.php');
            console.log('Sending email OTP to:', apiUrl); // Debug log
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_email_otp',
                    email: email
                })
            });
            
            console.log('Response status:', response.status); // Debug log
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('Email OTP response:', data); // Debug log
            return data;
        } catch (error) {
            console.error('Email OTP error:', error);
            return { success: false, error: 'Failed to send email OTP: ' + error.message };
        }
    }
    
    async sendPhoneOTP(phone) {
        try {
            const response = await fetch(this.getApiPath('auth.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'send_phone_otp',
                    phone: phone
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Phone OTP error:', error);
            return { success: false, error: 'Failed to send phone OTP' };
        }
    }
    
    async verifyEmailOTP(email, otp) {
        try {
            const response = await fetch(this.getApiPath('auth.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'verify_email_otp',
                    email: email,
                    otp: otp
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Email verification error:', error);
            return { success: false, error: 'Failed to verify email OTP' };
        }
    }
    
    async verifyPhoneOTP(phone, otp) {
        try {
            const response = await fetch(this.getApiPath('auth.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'verify_phone_otp',
                    phone: phone,
                    otp: otp
                })
            });
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Phone verification error:', error);
            return { success: false, error: 'Failed to verify phone OTP' };
        }
    }
    
    showEmailVerificationModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'emailVerificationModal';
        modal.setAttribute('data-bs-backdrop', 'static');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 text-center">
                        <div class="w-100">
                            <div class="mb-3">
                                <i class="fas fa-envelope-open fa-3x text-primary"></i>
                            </div>
                            <h4 class="modal-title">Verify Your Email</h4>
                            <p class="text-muted mb-0">We've sent a 6-digit code to</p>
                            <p class="text-primary fw-bold">${this.tempRegistrationData.email}</p>
                        </div>
                    </div>
                    <div class="modal-body px-4">
                        <form id="emailOtpForm">
                            <div class="mb-4">
                                <label class="form-label">Enter 6-digit code</label>
                                <div class="d-flex justify-content-between mb-3">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check me-2"></i>Verify Email
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="mb-2">Didn't receive the code?</p>
                                <button type="button" class="btn btn-link p-0" id="resendEmailOtp">
                                    <i class="fas fa-redo me-1"></i>Resend Code
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Setup OTP input behavior
        this.setupOTPInputs(modal);
        
        // Handle form submission
        const form = modal.querySelector('#emailOtpForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleEmailOTPVerification(modal, bootstrapModal);
        });
        
        // Handle resend
        const resendBtn = modal.querySelector('#resendEmailOtp');
        resendBtn.addEventListener('click', async () => {
            await this.resendEmailOTP(resendBtn);
        });
        
        // Remove modal from DOM when closed
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    showPhoneVerificationModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'phoneVerificationModal';
        modal.setAttribute('data-bs-backdrop', 'static');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 text-center">
                        <div class="w-100">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-3x text-success"></i>
                            </div>
                            <h4 class="modal-title">Verify Your Phone</h4>
                            <p class="text-muted mb-0">We've sent a 6-digit code to</p>
                            <p class="text-success fw-bold">${this.tempRegistrationData.phone}</p>
                        </div>
                    </div>
                    <div class="modal-body px-4">
                        <form id="phoneOtpForm">
                            <div class="mb-4">
                                <label class="form-label">Enter 6-digit code</label>
                                <div class="d-flex justify-content-between mb-3">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                    <input type="text" class="form-control text-center fs-4 otp-input" maxlength="1" style="width: 50px; height: 50px;">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-check me-2"></i>Verify Phone
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="mb-2">Didn't receive the code?</p>
                                <button type="button" class="btn btn-link p-0" id="resendPhoneOtp">
                                    <i class="fas fa-redo me-1"></i>Resend Code
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Setup OTP input behavior
        this.setupOTPInputs(modal);
        
        // Handle form submission
        const form = modal.querySelector('#phoneOtpForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handlePhoneOTPVerification(modal, bootstrapModal);
        });
        
        // Handle resend
        const resendBtn = modal.querySelector('#resendPhoneOtp');
        resendBtn.addEventListener('click', async () => {
            await this.resendPhoneOTP(resendBtn);
        });
        
        // Remove modal from DOM when closed
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    setupOTPInputs(modal) {
        const otpInputs = modal.querySelectorAll('.otp-input');
        
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Only allow numbers
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                // Move to next input if current is filled
                if (e.target.value && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
                
                // Auto-submit when all inputs are filled
                const allFilled = Array.from(otpInputs).every(input => input.value);
                if (allFilled) {
                    const form = modal.querySelector('form');
                    form.dispatchEvent(new Event('submit'));
                }
            });
            
            input.addEventListener('keydown', (e) => {
                // Move to previous input on backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
            
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text/plain').replace(/[^0-9]/g, '');
                
                if (pastedData.length === 6) {
                    otpInputs.forEach((input, i) => {
                        input.value = pastedData[i] || '';
                    });
                    // Auto-submit
                    const form = modal.querySelector('form');
                    form.dispatchEvent(new Event('submit'));
                }
            });
        });
        
        // Focus first input
        otpInputs[0].focus();
    }
    
    async handleEmailOTPVerification(modal, bootstrapModal) {
        const otpInputs = modal.querySelectorAll('.otp-input');
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        
        if (otp.length !== 6) {
            this.showOTPError(modal, 'Please enter all 6 digits');
            return;
        }
        
        const submitBtn = modal.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
            submitBtn.disabled = true;
            
            const result = await this.verifyEmailOTP(this.tempRegistrationData.email, otp);
            
            if (result.success) {
                // Close email verification modal
                bootstrapModal.hide();
                
                // Start phone verification
                setTimeout(async () => {
                    const phoneOtpResult = await this.sendPhoneOTP(this.tempRegistrationData.phone);
                    
                    if (phoneOtpResult.success) {
                        this.showPhoneVerificationModal();
                    } else {
                        this.showError('Failed to send phone OTP: ' + phoneOtpResult.error);
                        this.resetRegistrationForm();
                    }
                }, 500);
                
            } else {
                this.showOTPError(modal, result.error || 'Invalid OTP. Please try again.');
                // Clear inputs
                otpInputs.forEach(input => input.value = '');
                otpInputs[0].focus();
            }
        } catch (error) {
            this.showOTPError(modal, 'Verification failed. Please try again.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    async handlePhoneOTPVerification(modal, bootstrapModal) {
        const otpInputs = modal.querySelectorAll('.otp-input');
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        
        if (otp.length !== 6) {
            this.showOTPError(modal, 'Please enter all 6 digits');
            return;
        }
        
        const submitBtn = modal.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
            submitBtn.disabled = true;
            
            const result = await this.verifyPhoneOTP(this.tempRegistrationData.phone, otp);
            
            if (result.success) {
                // Both verifications complete, proceed with registration
                bootstrapModal.hide();
                
                setTimeout(async () => {
                    await this.completeRegistration();
                }, 500);
                
            } else {
                this.showOTPError(modal, result.error || 'Invalid OTP. Please try again.');
                // Clear inputs
                otpInputs.forEach(input => input.value = '');
                otpInputs[0].focus();
            }
        } catch (error) {
            this.showOTPError(modal, 'Verification failed. Please try again.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    async completeRegistration() {
        try {
            const result = await this.register(this.tempRegistrationData);
            
            if (result.success) {
                this.showSuccess('Account created successfully! Welcome to Lagorii Kids!');
                setTimeout(() => {
                    window.location.href = this.getBasePath() + 'index.php';
                }, 1500);
            } else {
                this.showError(result.error);
                this.resetRegistrationForm();
            }
        } catch (error) {
            this.showError('Registration failed. Please try again.');
            this.resetRegistrationForm();
        }
    }
    
    async resendEmailOTP(btn) {
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
            btn.disabled = true;
            
            const result = await this.sendEmailOTP(this.tempRegistrationData.email);
            
            if (result.success) {
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Sent!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 2000);
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            btn.innerHTML = '<i class="fas fa-times me-1"></i>Failed';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
    }
    
    async resendPhoneOTP(btn) {
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
            btn.disabled = true;
            
            const result = await this.sendPhoneOTP(this.tempRegistrationData.phone);
            
            if (result.success) {
                btn.innerHTML = '<i class="fas fa-check me-1"></i>Sent!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }, 2000);
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            btn.innerHTML = '<i class="fas fa-times me-1"></i>Failed';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }
    }
    
    showOTPError(modal, message) {
        let errorDiv = modal.querySelector('.otp-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger otp-error mt-2';
            const otpContainer = modal.querySelector('.d-flex.justify-content-between');
            otpContainer.parentNode.insertBefore(errorDiv, otpContainer.nextSibling);
        }
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (errorDiv) {
                errorDiv.remove();
            }
        }, 3000);
    }
    
    resetRegistrationForm() {
        // Reset the registration form to allow user to try again
        this.tempRegistrationData = null;
        
        const submitBtn = document.querySelector('#registerForm button[type="submit"]');
        if (submitBtn) {
            submitBtn.textContent = 'Create Account';
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
            submitBtn.textContent = 'Verifying...';
            submitBtn.disabled = true;
            
            // Start the verification process
            await this.startVerificationProcess(formData);
            
        } catch (error) {
            this.showError('An unexpected error occurred');
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
        if (!this.isLoggedIn()) {
            alert('Please log in to view your profile.');
            return;
        }
        
        // Create a simple profile modal or redirect to profile page
        this.showProfileModal();
    }
    
    showOrders() {
        if (!this.isLoggedIn()) {
            alert('Please log in to view your orders.');
            return;
        }
        
        // Create orders modal or redirect to orders page
        this.showOrdersModal();
    }
    
    showWishlist() {
        if (!this.isLoggedIn()) {
            alert('Please log in to view your wishlist.');
            return;
        }
        
        // Create wishlist modal or redirect to wishlist page
        this.showWishlistModal();
    }
    
    showProfileModal() {
        const user = this.getCurrentUser();
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'profileModal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user me-2"></i>My Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12">
                                <h6>Personal Information</h6>
                                <p><strong>Name:</strong> ${user.name}</p>
                                <p><strong>Email:</strong> ${user.email}</p>
                                <p><strong>Member Since:</strong> ${user.created_at || 'Recently'}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h6>Account Actions</h6>
                                <button class="btn btn-outline-primary btn-sm me-2" onclick="authManager.editProfile()">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="authManager.changePassword()">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Remove modal from DOM when closed
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    showOrdersModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'ordersModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-shopping-bag me-2"></i>My Orders</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="ordersContent">
                            <div class="text-center p-4">
                                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                                <p>Loading your orders...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Load orders
        this.loadUserOrders();
        
        // Remove modal from DOM when closed
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    showWishlistModal() {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'wishlistModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-heart me-2"></i>My Wishlist</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="wishlistContent">
                            <div class="text-center p-4">
                                <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                                <p>Loading your wishlist...</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();
        
        // Load wishlist
        this.loadUserWishlist();
        
        // Remove modal from DOM when closed
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
    
    async loadUserOrders() {
        try {
            // Simulate loading orders - in real app, this would fetch from API
            setTimeout(() => {
                const ordersContent = document.getElementById('ordersContent');
                if (ordersContent) {
                    ordersContent.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5>No Orders Yet</h5>
                            <p class="text-muted">You haven't placed any orders yet. Start shopping to see your orders here!</p>
                            <a href="${this.getBasePath()}pages/products.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    `;
                }
            }, 1000);
        } catch (error) {
            console.error('Error loading orders:', error);
            const ordersContent = document.getElementById('ordersContent');
            if (ordersContent) {
                ordersContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>Error Loading Orders</h6>
                        <p>We couldn't load your orders right now. Please try again later.</p>
                    </div>
                `;
            }
        }
    }
    
    async loadUserWishlist() {
        try {
            // Simulate loading wishlist - in real app, this would fetch from API  
            setTimeout(() => {
                const wishlistContent = document.getElementById('wishlistContent');
                if (wishlistContent) {
                    wishlistContent.innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <h5>Your Wishlist is Empty</h5>
                            <p class="text-muted">Save items you love to your wishlist. Browse our collections and click the heart icon!</p>
                            <a href="${this.getBasePath()}pages/products.php" class="btn btn-outline-primary">Browse Products</a>
                        </div>
                    `;
                }
            }, 1000);
        } catch (error) {
            console.error('Error loading wishlist:', error);
            const wishlistContent = document.getElementById('wishlistContent');
            if (wishlistContent) {
                wishlistContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h6>Error Loading Wishlist</h6>
                        <p>We couldn't load your wishlist right now. Please try again later.</p>
                    </div>
                `;
            }
        }
    }
    
    editProfile() {
        alert('Profile editing feature coming soon! You can contact customer service for profile updates.');
    }
    
    changePassword() {
        alert('Password change feature coming soon! Please contact customer service for password reset.');
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
    
    // Helper methods for path resolution
    getBasePath() {
        const path = window.location.pathname;
        if (path.includes('/pages/')) {
            return '../';
        }
        return '';
    }
    
    getApiPath(endpoint) {
        const basePath = this.getBasePath();
        return basePath + 'api/' + endpoint;
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
