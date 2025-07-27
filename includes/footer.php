<!-- Footer -->
<footer class="footer bg-dark text-light mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>About <?= SITE_NAME ?></h5>
                <p>Premium children's clothing trusted by over 1 lakh parents worldwide. Quality, comfort, and style for your little ones.</p>
                <div class="social-links">
                    <a href="#" class="text-light me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="pages/products.php?category=girls" class="text-light text-decoration-none">Girls Collection</a></li>
                    <li><a href="pages/products.php?category=boys" class="text-light text-decoration-none">Boys Collection</a></li>
                    <li><a href="pages/products.php?category=infants" class="text-light text-decoration-none">Infants Collection</a></li>
                    <li><a href="#" class="text-light text-decoration-none">New Arrivals</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Sale</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Customer Service</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light text-decoration-none">Contact Us</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Size Guide</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Shipping Info</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Returns & Exchanges</a></li>
                    <li><a href="#" class="text-light text-decoration-none">FAQ</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <h5>Contact Info</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-envelope me-2"></i> keerthudarshu06@gmail.com</li>
                    <li><i class="fas fa-phone me-2"></i> +91 7892783668</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i> Worldwide Shipping</li>
                </ul>
                
                <h6 class="mt-3">Newsletter</h6>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="me-3">We Accept:</span>
                <img src="https://cdn.jsdelivr.net/npm/payment-icons@1.2.5/min/flat/visa.svg" alt="Visa" height="24" class="me-2">
                <img src="https://cdn.jsdelivr.net/npm/payment-icons@1.2.5/min/flat/mastercard.svg" alt="Mastercard" height="24" class="me-2">
                <img src="https://cdn.jsdelivr.net/npm/payment-icons@1.2.5/min/flat/razorpay.svg" alt="Razorpay" height="24">
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/cart.js"></script>
<script src="assets/js/auth.js"></script>

</body>
</html>
