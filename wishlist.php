<?php
$pageTitle = 'Wishlist';
require_once __DIR__ . '/includes/db.php';

if (isset($_GET['remove']) && isLoggedIn()) {
    $pid = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND productid = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $pid);
    $stmt->execute();
    header('Location: wishlist.php');
    exit();
}

$wishlistItems = [];
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT w.*, p.productname, p.price, p.discount, p.photo1, p.stock, c.catename FROM wishlist w JOIN producttbl p ON w.productid = p.productid LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE w.user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $wishlistItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <ul class="breadcrumbs-list">
            <li><a href="<?= SITE_URL ?>/">Home</a></li>
            <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
            <li class="active">Wishlist</li>
        </ul>
    </div>
</div>

<div class="wishlist-page">
    <div class="container">
        <?php if (!isLoggedIn()): ?>
        <div class="empty-state">
            <div class="icon"><i class="bi bi-heart"></i></div>
            <h3>Please login to view your wishlist</h3>
            <p>You need to be logged in to save and manage your wishlist items.</p>
            <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Login</a>
            <a href="<?= SITE_URL ?>/register.php" class="btn btn-outline" style="margin-left:12px;"><i class="bi bi-person-plus"></i> Register</a>
        </div>

        <?php elseif (empty($wishlistItems)): ?>
        <div class="empty-state">
            <div class="icon"><i class="bi bi-heart"></i></div>
            <h3>Your wishlist is empty</h3>
            <p>Save your favorite products to your wishlist and come back to them anytime.</p>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
        </div>

        <?php else: ?>
        <div class="wishlist-header">
            <div>
                <h1>My Wishlist</h1>
                <span class="wishlist-count"><?= count($wishlistItems) ?> item<?= count($wishlistItems) !== 1 ? 's' : '' ?></span>
            </div>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline btn-sm"><i class="bi bi-plus"></i> Add More</a>
        </div>

        <div class="product-grid">
            <?php foreach ($wishlistItems as $item):
                $itemPrice = getDiscountPrice($item['price'], $item['discount']);
                $hasDiscount = $item['discount'] > 0;
            ?>
            <div class="product-card">
                <div class="product-card-image">
                    <a href="<?= SITE_URL ?>/product.php?id=<?= $item['productid'] ?>">
                        <img src="<?= getProductImage($item) ?>" alt="<?= sanitize($item['productname']) ?>" loading="lazy">
                    </a>
                    <div class="product-card-badges">
                        <?php if ($hasDiscount): ?>
                        <span class="product-badge badge-sale">-<?= intval($item['discount']) ?>%</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?= SITE_URL ?>/wishlist.php?remove=<?= $item['productid'] ?>" class="product-card-wishlist active" title="Remove from Wishlist" onclick="return confirm('Remove this item from your wishlist?')">
                        <i class="bi bi-heart-fill"></i>
                    </a>
                </div>
                <div class="product-card-body">
                    <?php if (!empty($item['catename'])): ?>
                    <div class="product-card-category"><?= sanitize($item['catename']) ?></div>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/product.php?id=<?= $item['productid'] ?>" class="product-card-name"><?= sanitize($item['productname']) ?></a>
                    <div class="product-card-price">
                        <span class="current-price"><?= formatPrice($itemPrice) ?></span>
                        <?php if ($hasDiscount): ?>
                        <span class="old-price"><?= formatPrice($item['price']) ?></span>
                        <span class="discount">-<?= intval($item['discount']) ?>%</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($item['stock'] > 0): ?>
                    <button class="product-card-addtocart add-to-cart-btn" data-product-id="<?= $item['productid'] ?>">
                        <i class="bi bi-bag-plus"></i> Add to Cart
                    </button>
                    <?php else: ?>
                    <button class="product-card-addtocart" disabled style="background:var(--text-muted);cursor:not-allowed;">
                        <i class="bi bi-x-circle"></i> Out of Stock
                    </button>
                    <?php endif; ?>
                    <a href="<?= SITE_URL ?>/wishlist.php?remove=<?= $item['productid'] ?>" class="product-card-addtocart" style="background:var(--white);color:var(--text-secondary);border:1px solid var(--border);margin-top:8px;" onclick="return confirm('Remove this item from your wishlist?')">
                        <i class="bi bi-trash"></i> Remove
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>