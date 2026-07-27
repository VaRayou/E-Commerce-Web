<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-bag-fill"></i>
        </div>
        <div class="sidebar-brand-text">
            <span class="brand-name">WE YOUNG</span>
            <span class="brand-sub">Shop</span>
        </div>
    </div>

    <hr class="sidebar-divider">

    <div class="sidebar-label">Main</div>
    <ul class="sidebar-nav">
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="index.php">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-label">Management</div>
    <ul class="sidebar-nav">
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'category.php' || basename($_SERVER['PHP_SELF']) == 'category-form.php' ? 'active' : '' ?>" href="category.php">
                <i class="bi bi-tag-fill"></i>
                <span>Categories</span>
            </a>
        </li>
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'product.php' || basename($_SERVER['PHP_SELF']) == 'product-form.php' ? 'active' : '' ?>" href="product.php">
                <i class="bi bi-box-seam-fill"></i>
                <span>Products</span>
            </a>
        </li>
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                <i class="bi bi-receipt"></i>
                <span>Orders</span>
            </a>
        </li>
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>" href="customers.php">
                <i class="bi bi-people-fill"></i>
                <span>Customers</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-label">Marketing</div>
    <ul class="sidebar-nav">
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'coupons.php' ? 'active' : '' ?>" href="coupons.php">
                <i class="bi bi-percent"></i>
                <span>Coupons</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-label">Content</div>
    <ul class="sidebar-nav">
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>" href="reviews.php">
                <i class="bi bi-star-fill"></i>
                <span>Reviews</span>
            </a>
        </li>
    </ul>

    <hr class="sidebar-divider">

    <div class="sidebar-label">Account</div>
    <ul class="sidebar-nav">
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                <i class="bi bi-gear-fill"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="sidebar-nav-item">
            <a class="sidebar-nav-link text-danger" href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<nav class="topbar" id="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" type="button">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search anything...">
        </div>
    </div>
    <div class="topbar-right">
        <div class="topbar-icon" title="Notifications">
            <i class="bi bi-bell-fill"></i>
            <span class="badge-dot"></span>
        </div>
        <div class="topbar-divider"></div>
        <div class="topbar-user dropdown">
            <div class="user-avatar" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;">
                <i class="bi bi-person-circle"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2">
                <li><h6 class="dropdown-header fw-semibold">Administrator</h6></li>
                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="main-wrapper" id="mainWrapper">
