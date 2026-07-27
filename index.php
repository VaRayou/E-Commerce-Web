<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

$featured = $conn->query("SELECT p.*, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE p.status = 1 AND p.is_featured = 1 ORDER BY p.created_at DESC LIMIT 8");
$newArrivals = $conn->query("SELECT p.*, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE p.status = 1 AND p.is_new = 1 ORDER BY p.created_at DESC LIMIT 8");
$products = $conn->query("SELECT p.*, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE p.status = 1 ORDER BY p.sales_count DESC LIMIT 12");
$categories = $conn->query("SELECT * FROM categorytbl ORDER BY catename");
?>

<section class="hero-section" id="heroCarousel" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="hero-carousel-inner">
        <div class="hero-slide active">
            <img src="<?= SITE_URL ?>/assets/images/hero-bg1.png" alt="New Collection" class="hero-bg">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="hero-content">
                    <p class="hero-subtitle">New Collection 2026</p>
                    <h1 class="hero-title">Discover Your Style</h1>
                    <p class="hero-desc">Shop the latest trends with amazing deals. Elevate your wardrobe with premium fashion selections curated just for you.</p>
                    <div class="hero-buttons">
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-hero">Shop Now</a>
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-hero-outline">View Collection</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <img src="<?= SITE_URL ?>/assets/images/hero-bg2.png" alt="Summer Deals" class="hero-bg">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="hero-content">
                    <p class="hero-subtitle">Limited Time Offer</p>
                    <h1 class="hero-title">Summer Sale Up To 50% Off</h1>
                    <p class="hero-desc">Don't miss out on our biggest sale of the season. Grab your favorite pieces before they're gone.</p>
                    <div class="hero-buttons">
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-hero">Shop the Sale</a>
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-hero-outline">Browse Deals</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <img src="<?= SITE_URL ?>/assets/images/hero-bg3.png" alt="Premium Quality" class="hero-bg">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="hero-content">
                    <p class="hero-subtitle">Premium Quality</p>
                    <h1 class="hero-title">Elevate Your Everyday</h1>
                    <p class="hero-desc">From casual to formal, find the perfect outfit for every occasion. Crafted with care, designed for you.</p>
                    <div class="hero-buttons">
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-hero">Explore Now</a>
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-hero-outline">New Arrivals</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="hero-carousel-btn hero-carousel-prev" type="button" onclick="heroSlide(-1)">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="hero-carousel-btn hero-carousel-next" type="button" onclick="heroSlide(1)">
        <i class="bi bi-chevron-right"></i>
    </button>
    <div class="hero-carousel-dots">
        <button class="hero-dot active" type="button" onclick="heroGoTo(0)"></button>
        <button class="hero-dot" type="button" onclick="heroGoTo(1)"></button>
        <button class="hero-dot" type="button" onclick="heroGoTo(2)"></button>
    </div>
</section>

<script>
    (function() {
        let current = 0;
        const slides = document.querySelectorAll('#heroCarousel .hero-slide');
        const dots = document.querySelectorAll('#heroCarousel .hero-dot');
        const total = slides.length;
        let timer = null;

        function showSlide(index) {
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            current = (index + total) % total;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
        }

        window.heroSlide = function(dir) {
            showSlide(current + dir);
            resetTimer();
        };

        window.heroGoTo = function(index) {
            showSlide(index);
            resetTimer();
        };

        function resetTimer() {
            clearInterval(timer);
            timer = setInterval(() => showSlide(current + 1), 5000);
        }

        resetTimer();
    })();
</script>


<?php if ($featured->num_rows > 0): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">On-Trend Tops 🔥</h2>
                <a href="<?= SITE_URL ?>/shop.php" class="section-link">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="product-grid">
                <?php while ($p = $featured->fetch_assoc()):
                    $img = getProductImage($p);
                    $salePrice = getDiscountPrice($p['price'], $p['discount']);
                    $rating = getProductRating($p['productid']);
                    $avg = round($rating['avg_rating'] ?? 0);
                ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $p['productid'] ?>">
                                <img src="<?= $img ?>" alt="<?= sanitize($p['productname']) ?>" loading="lazy">
                            </a>
                            <div class="product-card-badges">
                                <?php if ($p['discount'] > 0): ?>
                                    <span class="product-badge badge-sale">-<?= $p['discount'] ?>%</span>
                                <?php endif; ?>
                                <?php if ($p['is_new']): ?>
                                    <span class="product-badge badge-new">New</span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-wishlist <?= isInWishlist($p['productid']) ? 'active' : '' ?>" data-wishlist="<?= $p['productid'] ?>" title="Add to Wishlist">
                                <i class="bi <?= isInWishlist($p['productid']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                            <button class="product-card-quickview" data-quick-view="<?= $p['productid'] ?>">
                                <i class="bi bi-eye me-1"></i> Quick View
                            </button>
                        </div>
                        <div class="product-card-body">
                            <p class="product-card-category"><?= sanitize($p['catename'] ?? 'General') ?></p>
                            <h3 class="product-card-name">
                                <a href="<?= SITE_URL ?>/product.php?id=<?= $p['productid'] ?>"><?= sanitize($p['productname']) ?></a>
                            </h3>
                            <div class="product-card-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill <?= $i <= $avg ? '' : 'empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?= $rating['total'] ?? 0 ?>)</span>
                            </div>
                            <div class="product-card-price">
                                <span class="current-price"><?= formatPrice($salePrice) ?></span>
                                <?php if ($p['discount'] > 0): ?>
                                    <span class="old-price"><?= formatPrice($p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-addtocart" data-add-to-cart="<?= $p['productid'] ?>">
                                <i class="bi bi-bag-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- <?php if ($categories->num_rows > 0): ?>
    <section class="section" style="background: var(--light);">
        <div class="container">
            <div class="section-header" style="justify-content: center; text-align: center;">
                <div>
                    <h2 class="section-title" style="text-align: center;">Shop by Category</h2>
                </div>
            </div>
            <div class="categories-grid">
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <a href="<?= SITE_URL ?>/shop.php?category=<?= $cat['cateid'] ?>" class="category-card">
                        <img src="<?= SITE_URL ?>/assets/images/category-<?= strtolower(str_replace(' ', '-', $cat['catename'])) ?>.jpg" alt="<?= sanitize($cat['catename']) ?>">
                        <div class="category-card-overlay">
                            <h3 class="category-card-title"><?= sanitize($cat['catename']) ?></h3>
                            <span class="category-card-count">Browse Collection</span>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?> -->

<?php if ($newArrivals->num_rows > 0): ?>
    <section class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">New Arrivals</h2>
                <a href="<?= SITE_URL ?>/shop.php" class="section-link">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="product-grid">
                <?php while ($p = $newArrivals->fetch_assoc()):
                    $img = getProductImage($p);
                    $salePrice = getDiscountPrice($p['price'], $p['discount']);
                    $rating = getProductRating($p['productid']);
                    $avg = round($rating['avg_rating'] ?? 0);
                ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $p['productid'] ?>">
                                <img src="<?= $img ?>" alt="<?= sanitize($p['productname']) ?>" loading="lazy">
                            </a>
                            <div class="product-card-badges">
                                <?php if ($p['discount'] > 0): ?>
                                    <span class="product-badge badge-sale">-<?= $p['discount'] ?>%</span>
                                <?php endif; ?>
                                <?php if ($p['is_new']): ?>
                                    <span class="product-badge badge-new">New</span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-wishlist <?= isInWishlist($p['productid']) ? 'active' : '' ?>" data-wishlist="<?= $p['productid'] ?>" title="Add to Wishlist">
                                <i class="bi <?= isInWishlist($p['productid']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                            <button class="product-card-quickview" data-quick-view="<?= $p['productid'] ?>">
                                <i class="bi bi-eye me-1"></i> Quick View
                            </button>
                        </div>
                        <div class="product-card-body">
                            <p class="product-card-category"><?= sanitize($p['catename'] ?? 'General') ?></p>
                            <h3 class="product-card-name">
                                <a href="<?= SITE_URL ?>/product.php?id=<?= $p['productid'] ?>"><?= sanitize($p['productname']) ?></a>
                            </h3>
                            <div class="product-card-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill <?= $i <= $avg ? '' : 'empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?= $rating['total'] ?? 0 ?>)</span>
                            </div>
                            <div class="product-card-price">
                                <span class="current-price"><?= formatPrice($salePrice) ?></span>
                                <?php if ($p['discount'] > 0): ?>
                                    <span class="old-price"><?= formatPrice($p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-addtocart" data-add-to-cart="<?= $p['productid'] ?>">
                                <i class="bi bi-bag-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php if ($products->num_rows > 0): ?>
    <section class="section" style="background: var(--light);">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Products</h2>
                <a href="<?= SITE_URL ?>/shop.php" class="section-link">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="product-grid">
                <?php while ($p = $products->fetch_assoc()):
                    $img = getProductImage($p);
                    $salePrice = getDiscountPrice($p['price'], $p['discount']);
                    $rating = getProductRating($p['productid']);
                    $avg = round($rating['avg_rating'] ?? 0);
                ?>
                    <div class="product-card">
                        <div class="product-card-image">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= $p['productid'] ?>">
                                <img src="<?= $img ?>" alt="<?= sanitize($p['productname']) ?>" loading="lazy">
                            </a>
                            <div class="product-card-badges">
                                <?php if ($p['discount'] > 0): ?>
                                    <span class="product-badge badge-sale">-<?= $p['discount'] ?>%</span>
                                <?php endif; ?>
                                <?php if ($p['is_new']): ?>
                                    <span class="product-badge badge-new">New</span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-wishlist <?= isInWishlist($p['productid']) ? 'active' : '' ?>" data-wishlist="<?= $p['productid'] ?>" title="Add to Wishlist">
                                <i class="bi <?= isInWishlist($p['productid']) ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            </button>
                            <button class="product-card-quickview" data-quick-view="<?= $p['productid'] ?>">
                                <i class="bi bi-eye me-1"></i> Quick View
                            </button>
                        </div>
                        <div class="product-card-body">
                            <p class="product-card-category"><?= sanitize($p['catename'] ?? 'General') ?></p>
                            <h3 class="product-card-name">
                                <a href="<?= SITE_URL ?>/product.php?id=<?= $p['productid'] ?>"><?= sanitize($p['productname']) ?></a>
                            </h3>
                            <div class="product-card-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill <?= $i <= $avg ? '' : 'empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?= $rating['total'] ?? 0 ?>)</span>
                            </div>
                            <div class="product-card-price">
                                <span class="current-price"><?= formatPrice($salePrice) ?></span>
                                <?php if ($p['discount'] > 0): ?>
                                    <span class="old-price"><?= formatPrice($p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="product-card-addtocart" data-add-to-cart="<?= $p['productid'] ?>">
                                <i class="bi bi-bag-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- <section class="newsletter-section">
    <div class="container">
        <div class="newsletter-content">
            <h2 class="newsletter-title">Stay in the Loop</h2>
            <p class="newsletter-desc">Subscribe for exclusive deals and new arrivals delivered straight to your inbox.</p>
            <form class="newsletter-form" id="newsletter-form">
                <input type="email" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </div>
</section> -->

<?php include __DIR__ . '/includes/footer.php'; ?>