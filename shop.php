<?php
$pageTitle = 'Shop';
require_once __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

$where = "WHERE p.status = 1";
$params = [];
$types = '';

$activeGender = isset($_GET['gender']) ? strtolower(trim($_GET['gender'])) : '';
if ($activeGender === 'men' || $activeGender === 'women') {
    $genderCats = $conn->prepare("SELECT cateid FROM categorytbl WHERE catename = ?");
    $genderName = $activeGender === 'men' ? 'MAN' : 'WOMAN';
    $genderCats->bind_param("s", $genderName);
    $genderCats->execute();
    $genderCatIds = [];
    $gResult = $genderCats->get_result();
    while ($gRow = $gResult->fetch_assoc()) {
        $genderCatIds[] = $gRow['cateid'];
    }
    $genderCats->close();
    if (!empty($genderCatIds)) {
        $placeholders = implode(',', array_fill(0, count($genderCatIds), '?'));
        $where .= " AND p.cateid IN ($placeholders)";
        $params = array_merge($params, $genderCatIds);
        $types .= str_repeat('i', count($genderCatIds));
    } else {
        $where .= " AND 0 = 1";
    }
}

if (!empty($_GET['category'])) {
    $where .= " AND p.cateid = ?";
    $params[] = $_GET['category'];
    $types .= 'i';
}
if (!empty($_GET['price_min'])) {
    $where .= " AND p.price >= ?";
    $params[] = $_GET['price_min'];
    $types .= 'd';
}
if (!empty($_GET['price_max'])) {
    $where .= " AND p.price <= ?";
    $params[] = $_GET['price_max'];
    $types .= 'd';
}
if (!empty($_GET['brand'])) {
    $where .= " AND p.brand = ?";
    $params[] = $_GET['brand'];
    $types .= 's';
}
if (isset($_GET['instock']) && $_GET['instock'] == '1') {
    $where .= " AND p.stock > 0";
}

$searchQuery = trim($_GET['q'] ?? '');
if ($searchQuery !== '') {
    $searchVal = "%$searchQuery%";
    $where .= " AND (p.productname LIKE ? OR p.brand LIKE ?)";
    $params[] = $searchVal;
    $params[] = $searchVal;
    $types .= 'ss';
}

$sort = "ORDER BY p.created_at DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc': $sort = "ORDER BY p.price ASC"; break;
        case 'price_desc': $sort = "ORDER BY p.price DESC"; break;
        case 'best_selling': $sort = "ORDER BY p.sales_count DESC"; break;
        case 'name_asc': $sort = "ORDER BY p.productname ASC"; break;
        default: $sort = "ORDER BY p.created_at DESC";
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

$activeCategory = isset($_GET['category']) ? intval($_GET['category']) : 0;
$activeBrand = isset($_GET['brand']) ? $_GET['brand'] : '';
$activePriceMin = isset($_GET['price_min']) ? $_GET['price_min'] : '';
$activePriceMax = isset($_GET['price_max']) ? $_GET['price_max'] : '';
$activeSort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
$activeInStock = isset($_GET['instock']) && $_GET['instock'] == '1';

$categoryName = '';
if ($activeCategory > 0) {
    $catStmt = $conn->prepare("SELECT catename FROM categorytbl WHERE cateid = ?");
    $catStmt->bind_param("i", $activeCategory);
    $catStmt->execute();
    $catRow = $catStmt->get_result()->fetch_assoc();
    if ($catRow) {
        $categoryName = $catRow['catename'];
        $pageTitle = 'Shop - ' . $categoryName;
    }
    $catStmt->close();
}

$countSql = "SELECT COUNT(*) as total FROM producttbl p $where";
$countStmt = $conn->prepare($countSql);
if (!empty($types)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalProducts = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, ceil($totalProducts / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$sql = "SELECT p.*, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid $where $sort LIMIT $perPage OFFSET $offset";
$stmt = $conn->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$categories = $conn->query("SELECT c.cateid, c.catename, COUNT(p.productid) as product_count FROM categorytbl c LEFT JOIN producttbl p ON c.cateid = p.cateid AND p.status = 1 GROUP BY c.cateid, c.catename ORDER BY c.catename")->fetch_all(MYSQLI_ASSOC);

$brands = $conn->query("SELECT DISTINCT brand FROM producttbl WHERE brand IS NOT NULL AND brand != '' AND status = 1 ORDER BY brand")->fetch_all(MYSQLI_ASSOC);

$minPriceRow = $conn->query("SELECT MIN(price) as min_price FROM producttbl WHERE status = 1")->fetch_assoc();
$maxPriceRow = $conn->query("SELECT MAX(price) as max_price FROM producttbl WHERE status = 1")->fetch_assoc();
$shopMinPrice = $minPriceRow['min_price'] ?? 0;
$shopMaxPrice = $maxPriceRow['max_price'] ?? 1000;

function buildFilterUrl($overrides = []) {
    $params = ['category', 'price_min', 'price_max', 'brand', 'sort', 'page', 'instock', 'gender', 'q'];
    $query = [];
    foreach ($params as $p) {
        if (isset($overrides[$p])) {
            if ($overrides[$p] !== '' && $overrides[$p] !== false) {
                $query[$p] = $overrides[$p];
            }
        } else {
            if (!empty($_GET[$p])) {
                $query[$p] = $_GET[$p];
            }
        }
    }
    return SITE_URL . '/shop.php' . ($query ? '?' . http_build_query($query) : '');
}

$hasFilters = $activeCategory > 0 || $activePriceMin !== '' || $activePriceMax !== '' || $activeBrand !== '' || $activeInStock || $activeGender !== '';
?>

<style>
.shop-page { padding: 0 0 72px; }
.shop-header-bg { background: linear-gradient(135deg, var(--dark) 0%, var(--dark-lighter) 100%); padding: 48px 0 40px; margin-bottom: 32px; }
.shop-header-bg h1 { font-size: var(--font-size-4xl); font-weight: 800; color: var(--white); margin-bottom: 8px; }
.shop-header-bg p { font-size: var(--font-size-base); color: rgba(255,255,255,0.7); }
.filter-sidebar .filter-option a { display: flex; align-items: center; gap: 10px; width: 100%; font-size: var(--font-size-sm); color: var(--text-secondary); padding: 7px 0; transition: color var(--transition-fast); }
.filter-sidebar .filter-option a:hover { color: var(--primary); }
.filter-sidebar .filter-option.active a { color: var(--primary); font-weight: 600; }
.filter-sidebar .filter-option .count { margin-left: auto; font-size: var(--font-size-xs); color: var(--text-muted); }
.filter-option.active .count { color: var(--primary); font-weight: 600; }
.shop-active-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
.shop-active-filters .filter-tag { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: var(--primary-light); color: var(--primary); border-radius: var(--radius-full); font-size: var(--font-size-xs); font-weight: 600; }
.shop-active-filters .filter-tag a { color: var(--primary); font-size: 14px; line-height: 1; }
.filter-toggle-btn { display: none; width: 100%; padding: 12px; background: var(--white); border: 1px solid var(--border); border-radius: var(--radius); font-size: var(--font-size-sm); font-weight: 600; color: var(--text-primary); margin-bottom: 16px; cursor: pointer; text-align: center; }
.filter-toggle-btn i { margin-right: 6px; }
.shop-no-products { text-align: center; padding: 80px 20px; background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
.shop-no-products i { font-size: 64px; color: var(--border); margin-bottom: 16px; display: block; }
.shop-no-products h3 { font-size: var(--font-size-xl); font-weight: 700; margin-bottom: 8px; }
.shop-no-products p { font-size: var(--font-size-sm); color: var(--text-secondary); margin-bottom: 24px; }
@media (max-width: 992px) { .filter-toggle-btn { display: block; } }
</style>

<section
    class="py-5 mb-5 text-white"
    style="
        background: linear-gradient(rgba(0,0,0,.5), rgba(0,0,0,.5)),
        url('<?= SITE_URL ?>/assets/images/voucher.png') center center / cover no-repeat;
        min-height: 350px;
    "
>
    <div class="container text-center">
        <div class="breadcrumbs bg-transparent border-0 pb-3">
            <ul class="breadcrumbs-list">
                <li><a href="<?= SITE_URL ?>/" class="text-white">Home</a></li>
                <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
                <li class="active text-white"><?= $categoryName ? sanitize($categoryName) : ($activeGender === 'men' ? 'Men' : ($activeGender === 'women' ? 'Women' : 'Shop')) ?></li>

                <?php if ($categoryName && $activeGender !== ''): ?>
                    <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
                    <li class="active text-white"><?= sanitize($categoryName) ?></li>
                <?php endif; ?>
            </ul>
        </div>

        <h1><?= $categoryName ? sanitize($categoryName) : ($activeGender === 'men' ? 'Men' : ($activeGender === 'women' ? 'Women' : 'Shop')) ?></h1>

        <p>
            <?= number_format($totalProducts) ?>
            product<?= $totalProducts != 1 ? 's' : '' ?> found
        </p>
    </div>
</section>

<section class="shop-page">
    <div class="container">
        <button class="filter-toggle-btn" onclick="document.querySelector('.filter-sidebar').classList.toggle('show')">
            <i class="bi bi-funnel"></i> Filters
        </button>

        <?php if ($hasFilters): ?>
        <div class="shop-active-filters">
            <?php if ($activeGender !== ''): ?>
            <span class="filter-tag"><?= $activeGender === 'men' ? 'Men' : 'Women' ?> <a href="<?= buildFilterUrl(['gender' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <?php if ($searchQuery !== ''): ?>
            <span class="filter-tag">"<?php echo sanitize($searchQuery); ?>" <a href="<?= buildFilterUrl(['q' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <?php if ($activeCategory > 0 && $categoryName): ?>
            <span class="filter-tag"><?= sanitize($categoryName) ?> <a href="<?= buildFilterUrl(['category' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <?php if ($activePriceMin !== ''): ?>
            <span class="filter-tag">Min: <?= formatPrice($activePriceMin) ?> <a href="<?= buildFilterUrl(['price_min' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <?php if ($activePriceMax !== ''): ?>
            <span class="filter-tag">Max: <?= formatPrice($activePriceMax) ?> <a href="<?= buildFilterUrl(['price_max' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <?php if ($activeBrand !== ''): ?>
            <span class="filter-tag"><?= sanitize($activeBrand) ?> <a href="<?= buildFilterUrl(['brand' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <?php if ($activeInStock): ?>
            <span class="filter-tag">In Stock <a href="<?= buildFilterUrl(['instock' => false, 'page' => false]) ?>">&times;</a></span>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/shop.php" class="filter-tag" style="background:var(--danger-light);color:var(--danger);text-decoration:none;"><i class="bi bi-x-circle"></i> Clear All</a>
        </div>
        <?php endif; ?>

        <div class="shop-layout">
            <aside class="filter-sidebar">
                <div class="filter-header">
                    <h3><i class="bi bi-funnel" style="font-size:16px;margin-right:6px;"></i>Filters</h3>
                    <?php if ($hasFilters): ?>
                    <a href="<?= SITE_URL ?>/shop.php" class="filter-clear">Clear All</a>
                    <?php endif; ?>
                </div>

                <div class="filter-group">
                    <div class="filter-group-title">
                        Categories <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div>
                        <div class="filter-option <?= $activeCategory == 0 && !$hasFilters ? 'active' : '' ?>">
                            <a href="<?= SITE_URL ?>/shop.php<?= ($activeGender || $activeBrand || $activePriceMin || $activePriceMax || $activeInStock) ? '?' . http_build_query(array_filter(['gender' => $activeGender ?: null, 'brand' => $activeBrand ?: null, 'price_min' => $activePriceMin ?: null, 'price_max' => $activePriceMax ?: null, 'instock' => $activeInStock ? '1' : null])) : '' ?>">
                                All Categories
                            </a>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                        <div class="filter-option <?= $activeCategory == $cat['cateid'] ? 'active' : '' ?>">
                            <a href="<?= buildFilterUrl(['category' => $cat['cateid'], 'page' => false]) ?>">
                                <?= sanitize($cat['catename']) ?>
                                <span class="count"><?= $cat['product_count'] ?></span>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="filter-group">
                    <div class="filter-group-title">
                        Price Range <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div>
                        <form action="<?= SITE_URL ?>/shop.php" method="GET" class="price-range-inputs">
                            <?php if ($activeGender): ?><input type="hidden" name="gender" value="<?= sanitize($activeGender) ?>"><?php endif; ?>
                            <?php if ($activeCategory): ?><input type="hidden" name="category" value="<?= $activeCategory ?>"><?php endif; ?>
                            <?php if ($activeBrand): ?><input type="hidden" name="brand" value="<?= sanitize($activeBrand) ?>"><?php endif; ?>
                            <?php if ($activeSort && $activeSort != 'latest'): ?><input type="hidden" name="sort" value="<?= sanitize($activeSort) ?>"><?php endif; ?>
                            <?php if ($activeInStock): ?><input type="hidden" name="instock" value="1"><?php endif; ?>
                            <input type="number" name="price_min" placeholder="Min" value="<?= sanitize($activePriceMin) ?>" min="0" step="0.01">
                            <span>-</span>
                            <input type="number" name="price_max" placeholder="Max" value="<?= sanitize($activePriceMax) ?>" min="0" step="0.01">
                            <button type="submit" class="btn btn-primary btn-sm" style="flex-shrink:0;padding:8px 14px;"><i class="bi bi-arrow-right"></i></button>
                        </form>
                    </div>
                </div>

                <?php if (!empty($brands)): ?>
                <div class="filter-group">
                    <div class="filter-group-title">
                        Brand <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div>
                        <?php foreach ($brands as $b): ?>
                        <div class="filter-option <?= $activeBrand === $b['brand'] ? 'active' : '' ?>">
                            <a href="<?= buildFilterUrl(['brand' => $b['brand'], 'page' => false]) ?>">
                                <?= sanitize($b['brand']) ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="filter-group">
                    <div class="filter-group-title">
                        Availability <i class="bi bi-chevron-down toggle-icon"></i>
                    </div>
                    <div>
                        <div class="filter-option <?= $activeInStock ? 'active' : '' ?>">
                            <a href="<?= buildFilterUrl(['instock' => '1', 'page' => false]) ?>">
                                <i class="bi bi-check-circle" style="font-size:14px;color:var(--success);"></i> In Stock
                            </a>
                        </div>
                        <div class="filter-option <?= !$activeInStock ? 'active' : '' ?>">
                            <a href="<?= buildFilterUrl(['instock' => false, 'page' => false]) ?>">
                                All Products
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="shop-content">
                <div class="shop-toolbar">
                    <div class="shop-result-count">
                        Showing <strong><?= $offset + 1 ?>-<?= min($offset + $perPage, $totalProducts) ?></strong> of <strong><?= number_format($totalProducts) ?><?= $categoryName ? ' ' . sanitize($categoryName) : '' ?></strong> products
                    </div>
                    <div class="shop-sort">
                        <label>Sort by:</label>
                        <select onchange="window.location.href=this.value">
                            <option value="<?= buildFilterUrl(['sort' => 'latest', 'page' => false]) ?>" <?= $activeSort == 'latest' ? 'selected' : '' ?>>Latest</option>
                            <option value="<?= buildFilterUrl(['sort' => 'price_asc', 'page' => false]) ?>" <?= $activeSort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="<?= buildFilterUrl(['sort' => 'price_desc', 'page' => false]) ?>" <?= $activeSort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="<?= buildFilterUrl(['sort' => 'best_selling', 'page' => false]) ?>" <?= $activeSort == 'best_selling' ? 'selected' : '' ?>>Best Selling</option>
                            <option value="<?= buildFilterUrl(['sort' => 'name_asc', 'page' => false]) ?>" <?= $activeSort == 'name_asc' ? 'selected' : '' ?>>Name: A-Z</option>
                        </select>
                    </div>
                </div>

                <?php if ($result->num_rows > 0): ?>
                <div class="product-grid">
                    <?php while ($product = $result->fetch_assoc()):
                        $finalPrice = getDiscountPrice($product['price'], $product['discount']);
                        $hasDiscount = $product['discount'] > 0;
                        $isNew = !empty($product['is_new']);
                        $isFeatured = !empty($product['is_featured']);
                        $rating = getProductRating($product['productid']);
                        $avgRating = round($rating['avg_rating'] ?? 0);
                        $reviewCount = $rating['total'] ?? 0;
                    ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $product['productid'] ?>">
                                <img src="<?= getProductImage($product) ?>" alt="<?= sanitize($product['productname']) ?>" loading="lazy">
                            </a>
                            <div class="product-card-badges">
                                <?php if ($hasDiscount): ?>
                                <span class="product-badge badge-sale">Sale</span>
                                <?php endif; ?>
                                <?php if ($isNew): ?>
                                <span class="product-badge badge-new">New</span>
                                <?php endif; ?>
                                <?php if ($isFeatured && !$hasDiscount && !$isNew): ?>
                                <span class="product-badge badge-hot">Featured</span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-wishlist <?= isLoggedIn() && isInWishlist($product['productid']) ? 'active' : '' ?>" data-wishlist="<?= $product['productid'] ?>" data-product-id="<?= $product['productid'] ?>" title="Add to Wishlist">
                                <i class="bi <?= isLoggedIn() && isInWishlist($product['productid']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                            <button class="product-card-quickview quick-view-btn" data-product-id="<?= $product['productid'] ?>" data-quick-view="<?= $product['productid'] ?>">
                                <i class="bi bi-eye"></i> Quick View
                            </button>
                        </div>
                        <div class="product-card-body">
                            <?php if (!empty($product['catename'])): ?>
                            <div class="product-card-category"><?= sanitize($product['catename']) ?></div>
                            <?php endif; ?>
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $product['productid'] ?>" class="product-card-name"><?= sanitize($product['productname']) ?></a>
                            <div class="product-card-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $avgRating ? '-fill' : '' ?> <?= $i > $avgRating ? 'empty' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php if ($reviewCount > 0): ?>
                                <span class="rating-count">(<?= $reviewCount ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-card-price">
                                <span class="current-price"><?= formatPrice($finalPrice) ?></span>
                                <?php if ($hasDiscount): ?>
                                <span class="old-price"><?= formatPrice($product['price']) ?></span>
                                <span class="discount">-<?= $product['discount'] ?>%</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($product['stock'] > 0): ?>
                            <button class="product-card-addtocart add-to-cart-btn" data-product-id="<?= $product['productid'] ?>" data-add-to-cart="<?= $product['productid'] ?>">
                                <i class="bi bi-bag-plus"></i> Add to Cart
                            </button>
                            <?php else: ?>
                            <button class="product-card-addtocart" disabled style="background:var(--text-muted);cursor:not-allowed;">
                                <i class="bi bi-x-circle"></i> Out of Stock
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="<?= buildFilterUrl(['page' => $page - 1]) ?>" class="page-btn"><i class="bi bi-chevron-left"></i></a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    if ($startPage > 1): ?>
                    <a href="<?= buildFilterUrl(['page' => 1]) ?>" class="page-btn">1</a>
                    <?php if ($startPage > 2): ?>
                    <span class="page-btn disabled">...</span>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="<?= buildFilterUrl(['page' => $i]) ?>" class="page-btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                    <?php if ($endPage < $totalPages - 1): ?>
                    <span class="page-btn disabled">...</span>
                    <?php endif; ?>
                    <a href="<?= buildFilterUrl(['page' => $totalPages]) ?>" class="page-btn"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <?php if ($page < $totalPages): ?>
                    <a href="<?= buildFilterUrl(['page' => $page + 1]) ?>" class="page-btn"><i class="bi bi-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php else: ?>
                <div class="shop-no-products">
                    <i class="bi bi-search"></i>
                    <h3>No products found</h3>
                    <p>We couldn't find any products matching your filters. Try adjusting your search criteria.</p>
                    <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary"><i class="bi bi-arrow-counterclockwise"></i> Clear Filters</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.filter-group-title').forEach(title => {
    title.addEventListener('click', () => {
        const content = title.nextElementSibling;
        if (!content) return;
        const isOpen = content.style.display !== 'none';
        content.style.display = isOpen ? 'none' : 'block';
        title.classList.toggle('collapsed', isOpen);
    });
});

document.querySelectorAll('.filter-group').forEach(group => {
    const content = group.querySelector('.filter-group-title + div');
    if (content) content.style.display = 'block';
});
</script>

<?php
$stmt->close();
include __DIR__ . '/includes/footer.php';
?>