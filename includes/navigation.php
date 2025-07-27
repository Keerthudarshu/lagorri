<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="https://lagorii.com/cdn/shop/files/lagoriilogo_180x.svg?v=1681645627" alt="<?= SITE_NAME ?>" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Girls
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="pages/products.php?category=girls&subcategory=party-wear">Party Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=girls&subcategory=casual-wear">Casual Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=girls&subcategory=ethnic-wear">Ethnic Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=girls&subcategory=frocks">Frocks</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=girls&subcategory=gowns">Gowns</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Boys
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="pages/products.php?category=boys&subcategory=party-wear">Party Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=boys&subcategory=casual-wear">Casual Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=boys&subcategory=ethnic-wear">Ethnic Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=boys&subcategory=kurta-sets">Kurta Sets</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?category=boys&subcategory=suits">Suits</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pages/products.php?category=infants">Infants</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        Occasions
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="pages/products.php?occasion=wedding">Wedding Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?occasion=party">Party Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?occasion=casual">Casual Wear</a></li>
                        <li><a class="dropdown-item" href="pages/products.php?occasion=festive">Festive Wear</a></li>
                    </ul>
                </li>
            </ul>
            
            <!-- Search Bar -->
            <form class="d-flex search-form me-3" id="searchForm">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Search products..." id="searchInput">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <!-- User Actions -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="pages/auth.php" id="authLink">
                        <i class="fas fa-user"></i> Sign In
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="pages/cart.php">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <span class="badge bg-primary position-absolute top-0 start-100 translate-middle" id="cartCount">0</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
