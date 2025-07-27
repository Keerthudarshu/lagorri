<?php
require_once 'config.php';
$pageTitle = 'Home';

// Load products and categories from database
$categories = getCategories();
$featuredProducts = getProducts(null, 8, true); // Get 8 featured products
$newArrivals = getProducts(null, 8); // Get 8 latest products (new arrivals)

include 'includes/header.php';
?>

<!-- Hero Carousel -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    </div>
    
    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="hero-slide" style="background-image: url('https://pixabay.com/get/gf304121df798b0917f6d647361ec5eace45eccf8724267306f471b5f10c5ce4eed2caa54fb7e3773fbdb96d9014d125167e478a56a4d01229c521d35f7ab5c72_1280.jpg');">
                <div class="carousel-caption">
                    <div class="container">
                        <h1 class="display-4 fw-bold">New Summer Collection</h1>
                        <p class="lead">Discover the latest in children's fashion</p>
                        <a href="pages/products.php" class="btn btn-primary btn-lg">Shop Now</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="carousel-item">
            <div class="hero-slide" style="background-image: url('https://pixabay.com/get/g6d3a84da1a000be343996daef99105cf4f500db98acd6835ff997ed4410a54d5c7ccd0c33fe24f4561cf598790d050f092907d94e0e0a74ec8db153634f1705f_1280.jpg');">
                <div class="carousel-caption">
                    <div class="container">
                        <h1 class="display-4 fw-bold">Party Wear Collection</h1>
                        <p class="lead">Elegant outfits for special occasions</p>
                        <a href="pages/products.php?subcategory=party-wear" class="btn btn-primary btn-lg">Explore</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="carousel-item">
            <div class="hero-slide" style="background-image: url('https://pixabay.com/get/gd3f0760c1b628f3176d4756acd1c7e049ed5f686366f70e5671d2d2ac150d53f6347371efa2262f55407c60f10cce2ca5ac4acf788be106d026f9539231b623e_1280.jpg');">
                <div class="carousel-caption">
                    <div class="container">
                        <h1 class="display-4 fw-bold">Buy 2 Save 5% | Buy 3+ Save 10%</h1>
                        <p class="lead">Special discount on bulk purchases</p>
                        <a href="pages/products.php" class="btn btn-primary btn-lg">Shop & Save</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
    </button>
</div>

<!-- Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Shop by Category</h2>
        <div class="row g-4">
            <?php foreach($categories as $category): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="category-card">
                        <a href="pages/products.php?category=<?= $category['slug'] ?>">
                            <div class="category-image">
                                <img src="<?= $category['image_url'] ?>" alt="<?= $category['name'] ?>" class="img-fluid">
                                <div class="category-overlay">
                                    <h3><?= $category['name'] ?></h3>
                                    <p>Explore Collection</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products py-5 bg-light">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2>Featured Products</h2>
            </div>
            <div class="col-auto">
                <a href="pages/products.php" class="btn btn-outline-primary">View All</a>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach(array_slice($featuredProducts, 0, 8) as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="product-card">
                        <div class="product-image">
                            <a href="pages/product-detail.php?id=<?= $product['id'] ?>">
                                <img src="<?= $product['primary_image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
                            </a>
                            <?php if ($product['is_new_arrival']): ?>
                                <span class="badge bg-success position-absolute top-0 start-0 m-2">New</span>
                            <?php endif; ?>
                            <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                            <?php endif; ?>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info p-3">
                            <h6 class="product-title">
                                <a href="pages/product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h6>
                            <div class="product-price">
                                <span class="current-price"><?= formatPrice($product['price']) ?></span>
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="original-price"><?= formatPrice($product['original_price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="new-arrivals py-5">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col">
                <h2>New Arrivals</h2>
            </div>
            <div class="col-auto">
                <a href="pages/products.php?filter=new" class="btn btn-outline-primary">View All New</a>
            </div>
        </div>
        
        <div class="row g-4">
            <?php foreach(array_slice($newArrivals, 0, 6) as $product): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="product-card">
                        <div class="product-image">
                            <a href="pages/product-detail.php?id=<?= $product['id'] ?>">
                                <img src="<?= $product['primary_image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
                            </a>
                            <span class="badge bg-success position-absolute top-0 start-0 m-2">New</span>
                            <div class="product-actions">
                                <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?= $product['id'] ?>">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="product-info p-3">
                            <h6 class="product-title">
                                <a href="pages/product-detail.php?id=<?= $product['id'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($product['name']) ?>
                                </a>
                            </h6>
                            <div class="product-price">
                                <span class="current-price"><?= formatPrice($product['price']) ?></span>
                                <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
                                    <span class="original-price"><?= formatPrice($product['original_price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Promo Banner -->
<section class="promo-banner py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-3">Special Offer!</h2>
        <p class="lead mb-4">Buy 2 items and save 5% | Buy 3 or more and save 10%</p>
        <a href="pages/products.php" class="btn btn-light btn-lg">Shop Now & Save</a>
    </div>
</section>

<!-- Trust Indicators -->
<section class="trust-indicators py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-lg-3 col-md-6">
                <div class="trust-item">
                    <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                    <h5>Worldwide Shipping</h5>
                    <p>Express delivery available</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="trust-item">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h5>Secure Payment</h5>
                    <p>100% secure transactions</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="trust-item">
                    <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                    <h5>Easy Returns</h5>
                    <p>30-day return policy</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="trust-item">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5>1 Lakh+ Happy Parents</h5>
                    <p>Trusted worldwide</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
