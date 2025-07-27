<?php
require_once '../config.php';
$pageTitle = 'Products';

// Get filter parameters
$categoryId = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';
$search = $_GET['search'] ?? '';
$priceRange = $_GET['price'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';

// Load data from database
$categories = getCategories();
$allProducts = getProducts(); // Get all products first

// Get category name from ID for display purposes
$categoryName = '';
if ($categoryId) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            $categoryName = strtolower($cat['name']);
            break;
        }
    }
}
$category = $categoryName; // For backward compatibility with existing template code

// Filter products
$filteredProducts = $allProducts;

if ($categoryId) {
    $filteredProducts = array_filter($filteredProducts, function($product) use ($categoryId) {
        return $product['category_id'] == $categoryId;
    });
}

if ($subcategory) {
    $filteredProducts = array_filter($filteredProducts, function($product) use ($subcategory) {
        return strtolower($product['subcategory']) === strtolower($subcategory);
    });
}

if ($search) {
    // Use database search function for better performance
    $filteredProducts = searchProducts($search);
}

// Apply price range filter if specified
if ($priceRange) {
    $filteredProducts = array_filter($filteredProducts, function($product) use ($priceRange) {
        $price = $product['discount_price'] ?? $product['price'];
        switch ($priceRange) {
            case '0-25':
                return $price < 25;
            case '25-50':
                return $price >= 25 && $price <= 50;
            case '50-100':
                return $price >= 50 && $price <= 100;
            case 'over-100':
                return $price > 100;
            default:
                return true;
        }
    });
}

// Sort products
usort($filteredProducts, function($a, $b) use ($sortBy) {
    switch ($sortBy) {
        case 'price_low':
            $priceA = $a['discount_price'] ?? $a['price'];
            $priceB = $b['discount_price'] ?? $b['price'];
            return $priceA <=> $priceB;
        case 'price_high':
            $priceA = $a['discount_price'] ?? $a['price'];
            $priceB = $b['discount_price'] ?? $b['price'];
            return $priceB <=> $priceA;
        case 'newest':
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        default:
            return $a['name'] <=> $b['name'];
    }
});

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item active">Products</li>
            <?php if ($category): ?>
                <li class="breadcrumb-item active"><?= ucfirst($category) ?></li>
            <?php endif; ?>
            <?php if ($subcategory): ?>
                <li class="breadcrumb-item active"><?= ucfirst(str_replace('-', ' ', $subcategory)) ?></li>
            <?php endif; ?>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="filters-sidebar">
                <h5>Filters</h5>
                
                <!-- Category Filter -->
                <div class="filter-section">
                    <h6>Category</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" value="" id="cat-all" <?= empty($categoryId) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cat-all">All Categories</label>
                    </div>
                    <?php foreach ($categories as $cat): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="category" value="<?= $cat['id'] ?>" id="cat-<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="cat-<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Price Range Filter -->
                <div class="filter-section">
                    <h6>Price Range</h6>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="price" value="" id="price-all" <?= empty($priceRange) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="price-all">All Prices</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="price" value="0-25" id="price-1" <?= $priceRange === '0-25' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="price-1">Under €25</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="price" value="25-50" id="price-2" <?= $priceRange === '25-50' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="price-2">€25 - €50</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="price" value="50-100" id="price-3" <?= $priceRange === '50-100' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="price-3">€50 - €100</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="price" value="over-100" id="price-4" <?= $priceRange === 'over-100' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="price-4">Over €100</label>
                    </div>
                </div>
                
                <button class="btn btn-primary w-100" id="applyFilters">Apply Filters</button>
                <button class="btn btn-outline-secondary w-100 mt-2" id="clearFilters">Clear All</button>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9 col-md-8">
            <!-- Sort and Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>Products</h4>
                    <p class="text-muted"><?= count($filteredProducts) ?> items found</p>
                </div>
                <div class="d-flex align-items-center">
                    <label for="sortSelect" class="me-2">Sort by:</label>
                    <select class="form-select" id="sortSelect" style="width: auto;">
                        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Name</option>
                        <option value="price_low" <?= $sortBy === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sortBy === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    </select>
                </div>
            </div>
            
            <!-- Products Grid -->
            <?php if (empty($filteredProducts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your filters or search terms</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach($filteredProducts as $product): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product-detail.php?id=<?= $product['id'] ?>">
                                        <img src="<?= $product['primary_image'] ?: 'https://via.placeholder.com/300x300?text=No+Image' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid">
                                    </a>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="badge bg-success position-absolute top-0 start-0 m-2">Featured</span>
                                    <?php endif; ?>
                                    <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2">Sale</span>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <button class="btn btn-primary btn-sm add-to-cart" data-product-id="<?= $product['id'] ?>">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <a href="product-detail.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h6><a href="product-detail.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a></h6>
                                    <div class="product-price">
                                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                            <span class="current-price"><?= formatPrice($product['discount_price']) ?></span>
                                            <span class="original-price"><?= formatPrice($product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="current-price"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Filter functionality
document.getElementById('applyFilters').addEventListener('click', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Get selected filters
    const category = document.querySelector('input[name="category"]:checked').value;
    const price = document.querySelector('input[name="price"]:checked').value;
    const sort = document.getElementById('sortSelect').value;
    
    // Update URL parameters
    if (category) urlParams.set('category', category);
    else urlParams.delete('category');
    
    if (price) urlParams.set('price', price);
    else urlParams.delete('price');
    
    if (sort) urlParams.set('sort', sort);
    
    // Redirect with new parameters
    window.location.search = urlParams.toString();
});

document.getElementById('clearFilters').addEventListener('click', function() {
    window.location.href = 'products.php';
});

document.getElementById('sortSelect').addEventListener('change', function() {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('sort', this.value);
    window.location.search = urlParams.toString();
});
</script>

<?php include '../includes/footer.php'; ?>
