<header class="frontend-header" id="frontendHeader">
    <nav class="navbar-main">
        <div class="navbar-container">

            <div class="navbar-left">
                <button class="mobile-toggle d-lg-none" id="mobileToggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <a href="<?= SITE_URL ?>/shop.php?gender=men" class="nav-link <?= (isset($_GET['gender']) && $_GET['gender'] == 'men') ? 'active' : '' ?>">MEN</a>
                <a href="<?= SITE_URL ?>/shop.php?gender=women" class="nav-link <?= (isset($_GET['gender']) && $_GET['gender'] == 'women') ? 'active' : '' ?>">WOMEN</a>
            </div>

            <div class="navbar-center">
                <a href="<?= SITE_URL ?>/" class="navbar-brand">
                    <img src="<?= SITE_URL ?>/assets/images/logobrand.png" alt="WE YOUNG" style="height:90px; width:auto;">
                    
                </a>
              <a href="<?= SITE_URL ?>/" class="navbar-brand">WE YOUNG</a>
              
            </div>

            <div class="navbar-right">
              <a href="<?= SITE_URL ?>/shop.php" class="lang-selector" title="Language / Region">
    <img
        src="<?= SITE_URL ?>/assets/images/flage.png"
        alt="Cambodia"
        width="30"
        height="30"
        class="rounded-circle border"
    >
</a>

                <div class="search-wrapper" id="searchWrapper">
                    <form action="<?= SITE_URL ?>/shop.php" method="GET" class="header-search-form" style="display:flex;align-items:center;width:100%;">
                        <i class="bi bi-search" style="margin-left:4px;"></i>
                        <input type="text" id="liveSearch" name="q" placeholder="Search products..." autocomplete="off" style="flex:1;">
                        <button type="submit" class="search-btn" title="Search"><i class="bi bi-search"></i></button>
                    </form>
                    <div class="search-dropdown" id="searchDropdown"></div>
                </div>

                <div class="navbar-icons">
                    <?php if (isLoggedIn()): ?>
                    <div class="header-user-menu" id="userMenuWrapper">
                        <a href="#" class="nav-icon-link" title="Account" id="userMenuToggle">
                            <i class="bi bi-person"></i>
                        </a>
                        <div class="header-user-dropdown" id="userDropdown">
                            <a href="<?= SITE_URL ?>/profile.php"><i class="bi bi-person-circle"></i> Account Profile</a>
                            <a href="<?= SITE_URL ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="nav-icon-link" title="Login">
                        <i class="bi bi-person"></i>
                    </a>
                    <?php endif; ?>

                    <a href="#" class="nav-icon-link" title="Notifications">
                        <i class="bi bi-bell"></i>
                    </a>

                    <a href="<?= SITE_URL ?>/cart.php" class="nav-icon-link" title="Cart">
                        <i class="bi bi-bag"></i>
                        <span class="icon-badge cart-count"><?= getCartCount() ?></span>
                    </a>
                </div>
            </div>

        </div>
    </nav>

    <div class="mobile-offcanvas" id="mobileOffcanvas">
        <div class="offcanvas-header d-flex align-items-center justify-content-between">

    <a href="<?= SITE_URL ?>/" class="navbar-brand d-flex align-items-center text-decoration-none">
        <img
            src="<?= SITE_URL ?>/assets/images/logobrand.png"
            alt="WE YOUNG Logo"
            width="45"
            height="45"
            class="me-2 rounded-circle"
        >
        <span class="fw-bold fs-4">WE YOUNG</span>
    </a>

    <button class="offcanvas-close btn btn-link p-0 fs-2" id="mobileClose">
        &times;
    </button>

</div>
        <div class="offcanvas-body">
            <div class="mobile-nav-links">
                <a href="<?= SITE_URL ?>/shop.php?gender=men">MEN</a>
                <a href="<?= SITE_URL ?>/shop.php?gender=women">WOMEN</a>
                <a href="<?= SITE_URL ?>/shop.php">All Products</a>
                <?php
                $catQ2 = $conn->query("SELECT cateid, catename FROM categorytbl ORDER BY catename");
                while ($cat2 = $catQ2->fetch_assoc()):
                ?>
                <a href="<?= SITE_URL ?>/shop.php?category=<?= $cat2['cateid'] ?>"><?= sanitize($cat2['catename']) ?></a>
                <?php endwhile; ?>
            </div>
            <div class="mobile-nav-footer">
                <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/profile.php"><i class="bi bi-person"></i> My Account</a>
                <a href="<?= SITE_URL ?>/wishlist.php"><i class="bi bi-heart"></i> Wishlist</a>
                <?php if (isAdmin()): ?>
                <a href="<?= ADMIN_URL ?>/"><i class="bi bi-speedometer2"></i> Admin Dashboard</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>/logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php"><i class="bi bi-person"></i> Login / Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="offcanvas-overlay" id="offcanvasOverlay"></div>
</header>
