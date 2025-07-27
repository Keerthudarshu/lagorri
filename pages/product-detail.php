<?php
require_once '../config.php';

$productId = $_GET['id'] ?? '';
if (!$productId) {
    header('Location: products.php');
    exit;
}

// Load product data
$products = loadJsonData('products.json');
$product = null;

foreach ($products as $p) {
    if ($p['id'] === $productId) {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: products.php');
    exit;
}

$pageTitle = $product['name'];

// Get related products (same category)
$relatedProducts = array_filter($products, function($p) use ($product) {
    return $p['category'] === $product['category'] && $p['id'] !== $product['id'];
});
$relatedProducts = array_slice($relatedProducts, 0, 4);

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="products.php">Products</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?= $product['category'] ?>"><?= ucfirst($product['category']) ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-images">
                <div class="main-image mb-3">
                    <img src="<?= $product['images'][0] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid rounded" id="mainImage">
                    <?php if ($product['newArrival'] ?? false): ?>
                        <span class="badge bg-success position-absolute top-0 start-0 m-3">New Arrival</span>
                    <?php endif; ?>
                    <?php if (isset($product['originalPrice']) && $product['originalPrice'] > $product['price']): ?>
                        <span class="badge bg-danger position-absolute top-0 end-0 m-3">
                            <?= round((($product['originalPrice'] - $product['price']) / $product['originalPrice']) * 100) ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (count($product['images']) > 1): ?>
                    <div class="thumbnail-images">
                        <div class="row g-2">
                            <?php foreach ($product['images'] as $index => $image): ?>
                                <div class="col-3">
                                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                         class="img-fluid rounded thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                         onclick="changeMainImage('<?= $image ?>', this)">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="col-lg-6">
            <div class="product-details">
                <h1 class="product-title mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="product-price mb-4">
                    <span class="current-price h3 text-primary"><?= formatPrice($product['price']) ?></span>
                    <?php if (isset($product['originalPrice']) && $product['originalPrice'] > $product['price']): ?>
                        <span class="original-price h5 text-muted text-decoration-line-through ms-2"><?= formatPrice($product['originalPrice']) ?></span>
                        <span class="badge bg-success ms-2">
                            Save <?= formatPrice($product['originalPrice'] - $product['price']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description mb-4">
                    <p><?= htmlspecialchars($product['description']) ?></p>
                </div>
                
                <!-- Size Selection -->
                <?php if (!empty($product['sizes'])): ?>
                    <div class="size-selection mb-4">
                        <h6>Size:</h6>
                        <div class="size-options">
                            <?php foreach ($product['sizes'] as $size): ?>
                                <input type="radio" class="btn-check" name="size" id="size-<?= $size ?>" value="<?= $size ?>" required>
                                <label class="btn btn-outline-primary" for="size-<?= $size ?>"><?= $size ?></label>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">Size Guide</a>
                        </small>
                    </div>
                <?php endif; ?>
                
                <!-- Color Selection -->
                <?php if (!empty($product['colors'])): ?>
                    <div class="color-selection mb-4">
                        <h6>Color:</h6>
                        <div class="color-options">
                            <?php foreach ($product['colors'] as $color): ?>
                                <input type="radio" class="btn-check" name="color" id="color-<?= strtolower($color) ?>" value="<?= $color ?>" required>
                                <label class="btn btn-outline-secondary" for="color-<?= strtolower($color) ?>"><?= $color ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Quantity -->
                <div class="quantity-selection mb-4">
                    <h6>Quantity:</h6>
                    <div class="input-group" style="max-width: 150px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(-1)">-</button>
                        <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="10">
                        <button class="btn btn-outline-secondary" type="button" onclick="changeQuantity(1)">+</button>
                    </div>
                </div>
                
                <!-- Add to Cart -->
                <div class="product-actions mb-4">
                    <button class="btn btn-primary btn-lg me-3 add-to-cart-detail" 
                            data-product-id="<?= $product['id'] ?>">
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                    <button class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-heart me-2"></i>Add to Wishlist
                    </button>
                </div>
                
                <!-- Product Features -->
                <div class="product-features">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-shipping-fast text-primary me-2"></i>Free worldwide shipping</li>
                        <li><i class="fas fa-shield-alt text-primary me-2"></i>Secure payment</li>
                        <li><i class="fas fa-undo text-primary me-2"></i>30-day return policy</li>
                        <li><i class="fas fa-award text-primary me-2"></i>Premium quality fabric</li>
                    </ul>
                </div>
                
                <!-- Share -->
                <div class="product-share mt-4">
                    <h6>Share:</h6>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm me-2"><i class="fab fa-pinterest"></i></a>
                    <a href="#" class="btn btn-outline-primary btn-sm"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Details Tabs -->
    <div class="row mt-5">
        <div class="col-12">
            <ul class="nav nav-tabs" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="care-tab" data-bs-toggle="tab" data-bs-target="#care" type="button" role="tab">Care Instructions</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab">Shipping & Returns</button>
                </li>
            </ul>
            
            <div class="tab-content" id="productTabsContent">
                <div class="tab-pane fade show active" id="description" role="tabpanel">
                    <div class="p-4">
                        <h5>Product Description</h5>
                        <p><?= htmlspecialchars($product['description']) ?></p>
                        <h6>Features:</h6>
                        <ul>
                            <li>Premium quality fabric</li>
                            <li>Comfortable fit</li>
                            <li>Machine washable</li>
                            <li>Colorfast and shrink-resistant</li>
                            <li>Perfect for special occasions</li>
                        </ul>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="care" role="tabpanel">
                    <div class="p-4">
                        <h5>Care Instructions</h5>
                        <ul>
                            <li>Machine wash cold with like colors</li>
                            <li>Use mild detergent</li>
                            <li>Do not bleach</li>
                            <li>Tumble dry low or hang to dry</li>
                            <li>Iron on low heat if needed</li>
                            <li>Do not dry clean</li>
                        </ul>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <div class="p-4">
                        <h5>Shipping Information</h5>
                        <p><strong>Free Shipping:</strong> On all orders worldwide</p>
                        <p><strong>Processing Time:</strong> 1-2 business days</p>
                        <p><strong>Delivery Time:</strong> 5-7 business days (international)</p>
                        
                        <h6 class="mt-4">Returns & Exchanges</h6>
                        <p>We offer a 30-day return policy. Items must be unworn, unwashed, and in original condition with tags attached.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Related Products</h3>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product-detail.php?id=<?= $relatedProduct['id'] ?>">
                                        <img src="<?= $relatedProduct['images'][0] ?>" alt="<?= htmlspecialchars($relatedProduct['name']) ?>" class="img-fluid">
                                    </a>
                                    <div class="product-actions">
                                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?= $relatedProduct['id'] ?>">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info p-3">
                                    <h6 class="product-title">
                                        <a href="product-detail.php?id=<?= $relatedProduct['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($relatedProduct['name']) ?>
                                        </a>
                                    </h6>
                                    <div class="product-price">
                                        <span class="current-price"><?= formatPrice($relatedProduct['price']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Size Guide Modal -->
<div class="modal fade" id="sizeGuideModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Size Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Size</th>
                                <th>Age</th>
                                <th>Height (cm)</th>
                                <th>Chest (cm)</th>
                                <th>Waist (cm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>2-3Y</td><td>2-3 Years</td><td>92-98</td><td>53-55</td><td>51-53</td></tr>
                            <tr><td>3-4Y</td><td>3-4 Years</td><td>98-104</td><td>55-57</td><td>53-55</td></tr>
                            <tr><td>4-5Y</td><td>4-5 Years</td><td>104-110</td><td>57-59</td><td>55-57</td></tr>
                            <tr><td>5-6Y</td><td>5-6 Years</td><td>110-116</td><td>59-61</td><td>57-59</td></tr>
                            <tr><td>6-7Y</td><td>6-7 Years</td><td>116-122</td><td>61-64</td><td>59-61</td></tr>
                            <tr><td>7-8Y</td><td>7-8 Years</td><td>122-128</td><td>64-67</td><td>61-63</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image gallery
function changeMainImage(src, thumbnail) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Quantity controls
function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    const newValue = parseInt(quantityInput.value) + delta;
    if (newValue >= 1 && newValue <= 10) {
        quantityInput.value = newValue;
    }
}

// Add to cart with options
document.querySelector('.add-to-cart-detail').addEventListener('click', function() {
    const productId = this.dataset.productId;
    const size = document.querySelector('input[name="size"]:checked')?.value;
    const color = document.querySelector('input[name="color"]:checked')?.value;
    const quantity = parseInt(document.getElementById('quantity').value);
    
    // Validate selections
    <?php if (!empty($product['sizes'])): ?>
    if (!size) {
        alert('Please select a size');
        return;
    }
    <?php endif; ?>
    
    <?php if (!empty($product['colors'])): ?>
    if (!color) {
        alert('Please select a color');
        return;
    }
    <?php endif; ?>
    
    // Add to cart
    addToCart(productId, quantity, { size, color });
});
</script>

<?php include '../includes/footer.php'; ?>
