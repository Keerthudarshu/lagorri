<?php 
require_once 'config.php';
session_start();

$pageTitle = 'OTP Verification Test';
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3><i class="fas fa-shield-alt me-2"></i>OTP Verification System Test</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-envelope text-primary me-2"></i>Email OTP Test</h5>
                            <form id="emailOtpTestForm">
                                <div class="mb-3">
                                    <label for="testEmail" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="testEmail" value="test@example.com" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Send Email OTP
                                </button>
                            </form>
                            
                            <div id="emailOtpSection" class="mt-4" style="display: none;">
                                <h6>Verify Email OTP</h6>
                                <form id="verifyEmailForm">
                                    <div class="mb-3">
                                        <label for="emailOtpInput" class="form-label">Enter 6-digit OTP</label>
                                        <input type="text" class="form-control" id="emailOtpInput" maxlength="6" placeholder="123456">
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Verify Email
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><i class="fas fa-mobile-alt text-success me-2"></i>Phone OTP Test</h5>
                            <form id="phoneOtpTestForm">
                                <div class="mb-3">
                                    <label for="testPhone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="testPhone" value="+91-9876543210" required>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sms me-2"></i>Send Phone OTP
                                </button>
                            </form>
                            
                            <div id="phoneOtpSection" class="mt-4" style="display: none;">
                                <h6>Verify Phone OTP</h6>
                                <form id="verifyPhoneForm">
                                    <div class="mb-3">
                                        <label for="phoneOtpInput" class="form-label">Enter 6-digit OTP</label>
                                        <input type="text" class="form-control" id="phoneOtpInput" maxlength="6" placeholder="123456">
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check me-2"></i>Verify Phone
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <h5><i class="fas fa-user-plus text-info me-2"></i>Complete Registration Test</h5>
                            <p class="text-muted">After verifying both email and phone, you can test the complete registration.</p>
                            
                            <button class="btn btn-info" onclick="testCompleteRegistration()" id="completeRegBtn" disabled>
                                <i class="fas fa-user-check me-2"></i>Test Complete Registration
                            </button>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Status:</strong>
                                    <span id="emailStatus" class="badge bg-secondary">Email: Not Verified</span>
                                    <span id="phoneStatus" class="badge bg-secondary">Phone: Not Verified</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <h5><i class="fas fa-bug text-warning me-2"></i>Debug Information</h5>
                            <div id="debugOutput" class="bg-light p-3 rounded">
                                <small class="text-muted">OTP debug information will appear here...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast for notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1000">
    <div id="notificationToast" class="toast" role="alert">
        <div class="toast-header">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <strong class="me-auto">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            <!-- Message will be inserted here -->
        </div>
    </div>
</div>

<script>
let emailVerified = false;
let phoneVerified = false;

// Email OTP Test
document.getElementById('emailOtpTestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('testEmail').value;
    const btn = e.target.querySelector('button');
    const originalText = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        btn.disabled = true;
        
        const response = await fetch('api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'send_email_otp',
                email: email
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Email OTP sent successfully!', 'success');
            document.getElementById('emailOtpSection').style.display = 'block';
            
            // Show debug OTP if available
            if (data.debug_otp) {
                updateDebug('Email OTP: ' + data.debug_otp);
            }
        } else {
            showToast('Failed to send email OTP: ' + data.error, 'error');
        }
    } catch (error) {
        showToast('Error sending email OTP', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Verify Email OTP
document.getElementById('verifyEmailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('testEmail').value;
    const otp = document.getElementById('emailOtpInput').value;
    const btn = e.target.querySelector('button');
    const originalText = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
        btn.disabled = true;
        
        const response = await fetch('api/auth.php', {
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
        
        if (data.success) {
            showToast('Email verified successfully!', 'success');
            emailVerified = true;
            updateStatus();
            document.getElementById('emailOtpSection').style.display = 'none';
        } else {
            showToast('Email verification failed: ' + data.error, 'error');
        }
    } catch (error) {
        showToast('Error verifying email OTP', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Phone OTP Test
document.getElementById('phoneOtpTestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const phone = document.getElementById('testPhone').value;
    const btn = e.target.querySelector('button');
    const originalText = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        btn.disabled = true;
        
        const response = await fetch('api/auth.php', {
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
        
        if (data.success) {
            showToast('Phone OTP sent successfully!', 'success');
            document.getElementById('phoneOtpSection').style.display = 'block';
            
            // Show debug OTP if available
            if (data.debug_otp) {
                updateDebug('Phone OTP: ' + data.debug_otp);
            }
        } else {
            showToast('Failed to send phone OTP: ' + data.error, 'error');
        }
    } catch (error) {
        showToast('Error sending phone OTP', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

// Verify Phone OTP
document.getElementById('verifyPhoneForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const phone = document.getElementById('testPhone').value;
    const otp = document.getElementById('phoneOtpInput').value;
    const btn = e.target.querySelector('button');
    const originalText = btn.innerHTML;
    
    try {
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';
        btn.disabled = true;
        
        const response = await fetch('api/auth.php', {
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
        
        if (data.success) {
            showToast('Phone verified successfully!', 'success');
            phoneVerified = true;
            updateStatus();
            document.getElementById('phoneOtpSection').style.display = 'none';
        } else {
            showToast('Phone verification failed: ' + data.error, 'error');
        }
    } catch (error) {
        showToast('Error verifying phone OTP', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

function updateStatus() {
    const emailStatus = document.getElementById('emailStatus');
    const phoneStatus = document.getElementById('phoneStatus');
    const completeBtn = document.getElementById('completeRegBtn');
    
    if (emailVerified) {
        emailStatus.textContent = 'Email: Verified';
        emailStatus.className = 'badge bg-success';
    }
    
    if (phoneVerified) {
        phoneStatus.textContent = 'Phone: Verified';
        phoneStatus.className = 'badge bg-success';
    }
    
    if (emailVerified && phoneVerified) {
        completeBtn.disabled = false;
        completeBtn.classList.remove('btn-info');
        completeBtn.classList.add('btn-success');
    }
}

function testCompleteRegistration() {
    if (emailVerified && phoneVerified) {
        showToast('Both verifications complete! You can now proceed with registration.', 'success');
        updateDebug('Registration ready - both email and phone verified!');
    } else {
        showToast('Please verify both email and phone first.', 'warning');
    }
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastHeader = toast.querySelector('.toast-header i');
    
    toastMessage.textContent = message;
    
    // Update icon based on type
    toastHeader.className = `fas me-2 ${
        type === 'success' ? 'fa-check-circle text-success' :
        type === 'error' ? 'fa-exclamation-triangle text-danger' :
        type === 'warning' ? 'fa-exclamation-circle text-warning' :
        'fa-info-circle text-primary'
    }`;
    
    const bootstrapToast = new bootstrap.Toast(toast);
    bootstrapToast.show();
}

function updateDebug(message) {
    const debugOutput = document.getElementById('debugOutput');
    const timestamp = new Date().toLocaleTimeString();
    debugOutput.innerHTML += `<div><small><strong>[${timestamp}]</strong> ${message}</small></div>`;
    debugOutput.scrollTop = debugOutput.scrollHeight;
}

// Initialize
updateDebug('OTP Verification Test initialized');
</script>

<?php include 'includes/footer.php'; ?>
