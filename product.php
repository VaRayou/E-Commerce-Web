<?php
$pageTitle = 'Product';
require_once __DIR__ . '/includes/db.php';

$productid = intval($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT p.*, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE p.productid = ? AND p.status = 1");
$stmt->bind_param("i", $productid);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { header('Location: shop.php'); exit(); }

$ratingInfo = getProductRating($productid);

$reviews = $conn->prepare("SELECT r.*, u.first_name, u.last_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.productid = ? AND r.status = 1 ORDER BY r.created_at DESC");
$reviews->bind_param("i", $productid);
$reviews->execute();
$reviewsResult = $reviews->get_result();

$ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$tempReviews = $conn->prepare("SELECT rating FROM reviews WHERE productid = ? AND status = 1");
$tempReviews->bind_param("i", $productid);
$tempReviews->execute();
$tempResult = $tempReviews->get_result();
while ($rr = $tempResult->fetch_assoc()) {
    $rVal = intval($rr['rating']);
    if ($rVal >= 1 && $rVal <= 5) $ratingCounts[$rVal]++;
}

$related = $conn->query("SELECT p.*, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE p.cateid = {$product['cateid']} AND p.productid != $productid AND p.status = 1 ORDER BY RAND() LIMIT 4");

$reviewError = '';
$reviewSuccess = isset($_GET['reviewed']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isLoggedIn()) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    $userId = $_SESSION['user_id'];

    $check = $conn->prepare("SELECT oi.id FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND oi.productid = ? AND o.order_status = 'delivered' LIMIT 1");
    $check->bind_param("ii", $userId, $productid);
    $check->execute();

    if ($check->get_result()->num_rows > 0 || isAdmin()) {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, productid, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $userId, $productid, $rating, $comment);
        $stmt->execute();
        header("Location: product.php?id=$productid&reviewed=1");
        exit();
    } else {
        $reviewError = "You can only review products you have purchased.";
    }
}

$discountPrice = getDiscountPrice($product['price'], $product['discount']);
$inWishlist = isInWishlist($productid);
$avgRating = round($ratingInfo['avg_rating'] ?? 0, 1);
$totalReviews = $ratingInfo['total'] ?? 0;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="breadcrumbs">
    <div class="container">
        <ul class="breadcrumbs-list">
            <li><a href="<?= SITE_URL ?>/">Home</a></li>
            <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
            <li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li>
            <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
            <li><a href="<?= SITE_URL ?>/shop.php?category=<?= $product['cateid'] ?>"><?= sanitize($product['catename'] ?? 'Category') ?></a></li>
            <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
            <li class="active"><?= sanitize($product['productname']) ?></li>
        </ul>
    </div>
</section>

<section class="product-detail">
    <div class="container">
        <div class="product-detail-grid">
            <div class="product-gallery">
                <div class="product-gallery-main">
                    <img src="<?= getProductImage($product) ?>" alt="<?= sanitize($product['productname']) ?>" class="product-main-image" id="mainProductImage">
                </div>
                <div class="product-gallery-thumbnails" id="productThumbnails">
                    <?php
                    $photos = [];
                    if (!empty($product['photo1'])) $photos[] = $product['photo1'];
                    if (!empty($product['photo2'])) $photos[] = $product['photo2'];
                    if (!empty($product['photo3'])) $photos[] = $product['photo3'];
                    foreach ($photos as $idx => $photo):
                        $photoPath = IMAGE_DIR . '/' . $photo;
                        if (file_exists($photoPath)) {
                            $photoUrl = SITE_URL . '/image/' . $photo;
                        } else {
                            $photoPath = UPLOADS_DIR . '/' . $photo;
                            if (file_exists($photoPath)) {
                                $photoUrl = SITE_URL . '/uploads/products/' . $photo;
                            } else {
                                continue;
                            }
                        }
                    ?>
                    <div class="product-gallery-thumb <?= $idx === 0 ? 'active' : '' ?>" data-img="<?= $photoUrl ?>">
                        <img src="<?= $photoUrl ?>" alt="<?= sanitize($product['productname']) ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="product-info">
                <?php if (!empty($product['catename'])): ?>
                <div class="product-category"><?= sanitize($product['catename']) ?></div>
                <?php endif; ?>

                <h1 class="product-name"><?= sanitize($product['productname']) ?></h1>

                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= $i <= round($avgRating) ? '-fill' : '' ?><?= $i > round($avgRating) && $i - 0.5 <= $avgRating ? '-half' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text"><?= $avgRating ?> <a href="#reviews-tab" onclick="document.querySelector('[data-tab=reviews-content-tab]').click(); return true;">(<?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?>)</a></span>
                </div>

                <div class="product-price">
                    <span class="current"><?= formatPrice($discountPrice) ?></span>
                    <?php if ($product['discount'] > 0): ?>
                    <span class="old"><?= formatPrice($product['price']) ?></span>
                    <span class="discount-badge">-<?= intval($product['discount']) ?>%</span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($product['shortdescription'])): ?>
                <p class="product-short-desc"><?= sanitize($product['shortdescription']) ?></p>
                <?php endif; ?>

                <?php if ($product['stock'] > 0): ?>
                <div class="mb-16" style="font-size:var(--font-size-sm);font-weight:600;color:var(--success);display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-check-circle-fill"></i> In Stock
                </div>
                <?php else: ?>
                <div class="mb-16" style="font-size:var(--font-size-sm);font-weight:600;color:var(--danger);display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-x-circle-fill"></i> Out of Stock
                </div>
                <?php endif; ?>

                <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">

                <?php if ($product['stock'] > 0): ?>
                <div class="product-option-group">
                    <label class="product-option-label">Quantity</label>
                    <div class="quantity-selector">
                        <button type="button" class="qty-btn-minus"><i class="bi bi-dash"></i></button>
                        <input type="number" class="qty-input" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" data-max="<?= $product['stock'] ?>">
                        <button type="button" class="qty-btn-plus"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="product-actions">
                    <?php if ($product['stock'] > 0): ?>
                    <button class="btn btn-primary btn-add-cart add-to-cart-btn" data-product-id="<?= $productid ?>">
                        <i class="bi bi-bag-plus"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline-primary btn-buy-now" onclick="addToCart(<?= $productid ?>, document.querySelector('.qty-input').value || 1); window.location.href='<?= SITE_URL ?>/checkout.php';">
                        <i class="bi bi-lightning-fill"></i> Buy Now
                    </button>
                    <?php else: ?>
                    <button class="btn btn-primary btn-add-cart disabled" disabled>
                        <i class="bi bi-bag-plus"></i> Out of Stock
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-outline btn-icon btn-wishlist-action wishlist-btn <?= $inWishlist ? 'active' : '' ?>" data-product-id="<?= $productid ?>" title="<?= $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                        <i class="bi bi-heart<?= $inWishlist ? '-fill' : '' ?>"></i>
                    </button>
                </div>

                <div class="product-meta">
                    <?php if (!empty($product['catename'])): ?>
                    <div><strong>Category:</strong> <?= sanitize($product['catename']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($product['sku'])): ?>
                    <div><strong>SKU:</strong> <?= sanitize($product['sku']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="product-tabs" id="reviews-tab">
            <div class="product-tabs-nav">
                <button class="product-tab-btn tab-btn active" data-tab="description-tab">Description</button>
                <button class="product-tab-btn tab-btn" data-tab="specifications-tab">Specifications</button>
                <button class="product-tab-btn tab-btn" data-tab="reviews-content-tab">Reviews (<?= $totalReviews ?>)</button>
            </div>

            <div class="product-tab-content tab-content active" id="description-tab">
                <p><?= nl2br(sanitize($product['description'] ?? '')) ?></p>
            </div>

            <div class="product-tab-content tab-content" id="specifications-tab">
                <table class="spec-table">
                    <tr>
                        <td>Category</td>
                        <td><?= sanitize($product['catename'] ?? 'N/A') ?></td>
                    </tr>
                    <?php if (!empty($product['brand'])): ?>
                    <tr>
                        <td>Brand</td>
                        <td><?= sanitize($product['brand']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($product['sku'])): ?>
                    <tr>
                        <td>SKU</td>
                        <td><?= sanitize($product['sku']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Stock</td>
                        <td><?= $product['stock'] > 0 ? $product['stock'] . ' units' : 'Out of Stock' ?></td>
                    </tr>
                    <tr>
                        <td>Price</td>
                        <td><?= formatPrice($discountPrice) ?></td>
                    </tr>
                    <?php if ($product['discount'] > 0): ?>
                    <tr>
                        <td>Discount</td>
                        <td><?= intval($product['discount']) ?>% off</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="product-tab-content tab-content" id="reviews-content-tab">
                <?php if ($totalReviews > 0): ?>
                <div class="review-summary">
                    <div class="review-avg">
                        <div class="number"><?= $avgRating ?></div>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= round($avgRating) ? '-fill' : '' ?><?= $i > round($avgRating) && $i - 0.5 <= $avgRating ? '-half' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="total"><?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?></div>
                    </div>
                    <div class="review-bars">
                        <?php for ($i = 5; $i >= 1; $i--):
                            $count = $ratingCounts[$i];
                            $pct = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        ?>
                        <div class="review-bar-row">
                            <span class="label"><?= $i ?> <i class="bi bi-star-fill" style="font-size:10px;color:var(--warning);"></i></span>
                            <div class="bar"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
                            <span class="count"><?= $count ?></span>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                <?php if (!empty($reviewError)): ?>
                <div class="flash-message error" style="margin-bottom:20px;position:static;transform:none;animation:none;pointer-events:auto;">
                    <i class="bi bi-exclamation-circle-fill icon"></i>
                    <span class="message"><?= sanitize($reviewError) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($reviewSuccess): ?>
                <div class="flash-message success" style="margin-bottom:20px;position:static;transform:none;animation:none;pointer-events:auto;">
                    <i class="bi bi-check-circle-fill icon"></i>
                    <span class="message">Your review has been submitted successfully!</span>
                </div>
                <?php endif; ?>
                <div class="review-form">
                    <h4>Write a Review</h4>
                    <form method="POST" action="product.php?id=<?= $productid ?>">
                        <div class="form-group">
                            <label>Your Rating</label>
                            <div class="star-rating-input star-rating">
                                <input type="hidden" name="rating" value="5" id="ratingInput">
                                <i class="bi bi-star-fill star active" data-value="1"></i>
                                <i class="bi bi-star-fill star active" data-value="2"></i>
                                <i class="bi bi-star-fill star active" data-value="3"></i>
                                <i class="bi bi-star-fill star active" data-value="4"></i>
                                <i class="bi bi-star-fill star active" data-value="5"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reviewComment">Your Review</label>
                            <textarea name="comment" id="reviewComment" required placeholder="Share your experience with this product..."></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary"><i class="bi bi-send-fill"></i> Submit Review</button>
                    </form>
                </div>
                <?php else: ?>
                <div style="padding:24px;background:var(--light);border-radius:var(--radius-lg);text-align:center;margin-bottom:24px;">
                    <p style="font-size:var(--font-size-sm);color:var(--text-secondary);margin-bottom:12px;">Please <a href="<?= SITE_URL ?>/login.php" style="color:var(--primary);font-weight:600;">log in</a> to write a review.</p>
                </div>
                <?php endif; ?>

                <?php if ($reviewsResult->num_rows > 0): ?>
                <div class="reviews-list" style="margin-top:32px;">
                    <?php while ($review = $reviewsResult->fetch_assoc()):
                        $reviewerName = trim(($review['first_name'] ?? '') . ' ' . ($review['last_name'] ?? ''));
                        if (empty($reviewerName)) $reviewerName = 'Anonymous';
                        $initials = mb_strtoupper(mb_substr($reviewerName, 0, 1));
                        $reviewRating = intval($review['rating']);
                    ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-avatar"><?= $initials ?></div>
                            <div class="review-meta">
                                <div class="review-author"><?= sanitize($reviewerName) ?></div>
                                <div class="review-date"><?= date('M d, Y', strtotime($review['created_at'])) ?></div>
                            </div>
                            <div class="review-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $reviewRating ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="review-text"><?= sanitize($review['comment']) ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <div style="padding:40px 0;text-align:center;">
                    <i class="bi bi-chat-dots" style="font-size:32px;color:var(--text-muted);display:block;margin-bottom:12px;"></i>
                    <p style="font-size:var(--font-size-sm);color:var(--text-muted);">No reviews yet. Be the first to review this product!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($related && $related->num_rows > 0): ?>
        <div class="section" style="padding-top:64px;">
            <div class="section-header">
                <h2 class="section-title">Related Products</h2>
            </div>
            <div class="product-grid">
                <?php while ($rp = $related->fetch_assoc()):
                    $rpDiscount = getDiscountPrice($rp['price'], $rp['discount']);
                    $rpRating = getProductRating($rp['productid']);
                    $rpAvg = round($rpRating['avg_rating'] ?? 0, 1);
                    $rpTotal = $rpRating['total'] ?? 0;
                ?>
                <div class="product-card">
                    <div class="product-card-image">
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $rp['productid'] ?>">
                            <img src="<?= getProductImage($rp) ?>" alt="<?= sanitize($rp['productname']) ?>">
                        </a>
                        <div class="product-card-badges">
                            <?php if ($rp['discount'] > 0): ?>
                            <span class="product-badge badge-sale">-<?= intval($rp['discount']) ?>%</span>
                            <?php endif; ?>
                        </div>
                        <button class="product-card-wishlist wishlist-btn <?= isInWishlist($rp['productid']) ? 'active' : '' ?>" data-product-id="<?= $rp['productid'] ?>" title="Wishlist">
                            <i class="bi bi-heart<?= isInWishlist($rp['productid']) ? '-fill' : '' ?>"></i>
                        </button>
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $rp['productid'] ?>" class="product-card-quickview"><i class="bi bi-eye-fill me-1"></i> Quick View</a>
                    </div>
                    <div class="product-card-body">
                        <div class="product-card-category"><?= sanitize($rp['catename'] ?? '') ?></div>
                        <a href="<?= SITE_URL ?>/product.php?id=<?= $rp['productid'] ?>" class="product-card-name"><?= sanitize($rp['productname']) ?></a>
                        <div class="product-card-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= round($rpAvg) ? '-fill' : '' ?><?= $i > round($rpAvg) && $i - 0.5 <= $rpAvg ? '-half' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if ($rpTotal > 0): ?>
                            <span class="rating-count">(<?= $rpTotal ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-price">
                            <span class="current-price"><?= formatPrice($rpDiscount) ?></span>
                            <?php if ($rp['discount'] > 0): ?>
                            <span class="old-price"><?= formatPrice($rp['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="product-card-addtocart add-to-cart-btn" data-product-id="<?= $rp['productid'] ?>">
                            <i class="bi bi-bag-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.querySelectorAll('.star-rating-input .star').forEach(star => {
    star.style.cursor = 'pointer';
    star.addEventListener('click', function() {
        const val = parseInt(this.getAttribute('data-value'));
        document.getElementById('ratingInput').value = val;
        this.closest('.star-rating-input').querySelectorAll('.star').forEach((s, idx) => {
            if (idx < val) { s.classList.add('active'); } else { s.classList.remove('active'); }
        });
    });
    star.addEventListener('mouseenter', function() {
        const val = parseInt(this.getAttribute('data-value'));
        this.closest('.star-rating-input').querySelectorAll('.star').forEach((s, idx) => {
            if (idx < val) { s.classList.add('active'); } else { s.classList.remove('active'); }
        });
    });
});
document.querySelector('.star-rating-input')?.addEventListener('mouseleave', function() {
    const current = parseInt(document.getElementById('ratingInput').value) || 0;
    this.querySelectorAll('.star').forEach((s, idx) => {
        if (idx < current) { s.classList.add('active'); } else { s.classList.remove('active'); }
    });
});

document.querySelectorAll('.product-gallery-thumb').forEach(thumb => {
    thumb.addEventListener('click', function() {
        const mainImg = document.getElementById('mainProductImage');
        const newSrc = this.getAttribute('data-img');
        if (newSrc && mainImg) {
            mainImg.style.opacity = '0';
            setTimeout(() => {
                mainImg.src = newSrc;
                mainImg.style.opacity = '1';
            }, 150);
            document.querySelectorAll('.product-gallery-thumb').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
