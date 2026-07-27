/**
 * Main JavaScript - E-Commerce Frontend
 * Handles all client-side interactivity
 * Dependencies: Bootstrap 5.3 (no jQuery)
 */

document.addEventListener('DOMContentLoaded', () => {

  // ========================
  // 1. Sticky Header
  // ========================
  const header = document.querySelector('header, .navbar');

  const handleStickyHeader = () => {
    if (!header) return;
    window.scrollY > 100 ? header.classList.add('scrolled') : header.classList.remove('scrolled');
  };

  window.addEventListener('scroll', handleStickyHeader, { passive: true });
  handleStickyHeader();


  // ========================
  // 2. Mobile Navigation
  // ========================
  const mobileToggle = document.getElementById('mobileToggle') || document.querySelector('.mobile-toggle, .mobile-menu-toggle, .navbar-toggler');
  const mobileOffcanvas = document.getElementById('mobileOffcanvas');
  const mobileClose = document.getElementById('mobileClose');
  const offcanvasOverlay = document.getElementById('offcanvasOverlay');

  const openMobileMenu = () => {
    if (mobileOffcanvas) mobileOffcanvas.classList.add('open');
    if (offcanvasOverlay) offcanvasOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  const closeMobileMenu = () => {
    if (mobileOffcanvas) mobileOffcanvas.classList.remove('open');
    if (offcanvasOverlay) offcanvasOverlay.classList.remove('active');
    document.body.style.overflow = '';
  };

  if (mobileToggle) mobileToggle.addEventListener('click', openMobileMenu);
  if (mobileClose) mobileClose.addEventListener('click', closeMobileMenu);
  if (offcanvasOverlay) offcanvasOverlay.addEventListener('click', closeMobileMenu);


  // ========================
  // 3. Search Functionality
  // ========================
  const searchInput = document.querySelector('#liveSearch, .search-input, #search-input');
  const searchDropdown = document.querySelector('#searchDropdown, .search-dropdown, .search-results, #search-results');
  let searchTimeout;

  const fetchSearchResults = async (query) => {
    if (!searchDropdown) return;
    if (!query || query.trim().length < 2) {
      searchDropdown.style.display = 'none';
      searchDropdown.innerHTML = '';
      return;
    }

    try {
      const response = await fetch(`api/search.php?q=${encodeURIComponent(query.trim())}`);
      if (!response.ok) throw new Error('Search failed');
      const data = await response.json();
      const results = data.results || data;

      if (!results || results.length === 0) {
        searchDropdown.innerHTML = '<div class="p-3 text-muted text-center">No results found</div>';
      } else {
        searchDropdown.innerHTML = results.map(product => `
          <a href="product.php?id=${product.id}" class="d-flex align-items-center p-2 text-decoration-none search-result-item" style="border-bottom:1px solid #f1f5f9;">
            <img src="${product.image}" alt="${product.name}" width="45" height="45" style="object-fit:cover;border-radius:6px;margin-right:12px;">
            <div>
              <div style="font-weight:500;color:#1e293b;font-size:13px;">${product.name}</div>
              <div style="color:#2563eb;font-weight:600;font-size:14px;">${product.price}</div>
            </div>
          </a>
        `).join('');
      }
      searchDropdown.style.display = 'block';
    } catch (error) {
      searchDropdown.innerHTML = '<div class="p-3 text-danger text-center">Search unavailable</div>';
      searchDropdown.style.display = 'block';
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      clearTimeout(searchTimeout);
      const query = e.target.value;
      searchTimeout = setTimeout(() => fetchSearchResults(query), 300);
    });
  }

  const closeSearchDropdown = (e) => {
    if (!searchDropdown || !searchInput) return;
    if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
      searchDropdown.style.display = 'none';
    }
  };

  document.addEventListener('click', closeSearchDropdown);


  // ========================
  // 4. Cart Operations
  // ========================

  const siteBase = (typeof window.siteBaseUrl !== 'undefined') ? window.siteBaseUrl : '';

  const cartApiRequest = async (payload) => {
    const formData = new FormData();
    for (const key in payload) {
      if (payload[key] !== null && payload[key] !== undefined) {
        formData.append(key, payload[key]);
      }
    }
    const response = await fetch(siteBase + 'api/cart.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) throw new Error('Request failed');
    return response.json();
  };

  /**
   * Update the cart badge count in the navbar
   */
  const updateCartBadge = (count) => {
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = count;
  };

  /**
   * Add a product to the cart
   */
  window.addToCart = async (productId, quantity = 1, color = null, size = null) => {
    try {
      const payload = { action: 'add', productid: productId, quantity };
      if (color) payload.color = color;
      if (size) payload.size = size;

      const data = await cartApiRequest(payload);
      updateCartBadge(data.cart_count ?? data.count ?? 0);
      showToast('Product added to cart!', 'success');
    } catch (error) {
      showToast('Failed to add product to cart.', 'error');
    }
  };

  /**
   * Update cart item quantity
   */
  window.updateCartQuantity = async (cartId, quantity) => {
    try {
      const data = await cartApiRequest({ action: 'update', cart_id: cartId, quantity });

      // Update individual item subtotal
      const row = document.querySelector(`[data-cart-id="${cartId}"]`);
      if (row && data.subtotal !== undefined) {
        const subtotalEl = row.querySelector('.item-subtotal');
        if (subtotalEl) subtotalEl.textContent = `$${parseFloat(data.subtotal).toFixed(2)}`;
      }

      // Update order totals
      if (data.total !== undefined) {
        const totalEl = document.querySelector('.cart-total');
        if (totalEl) totalEl.textContent = `$${parseFloat(data.total).toFixed(2)}`;
      }
      if (data.subtotal !== undefined) {
        const subtotalEl = document.querySelector('.cart-subtotal');
        if (subtotalEl) subtotalEl.textContent = `$${parseFloat(data.subtotal).toFixed(2)}`;
      }

      updateCartBadge(data.cart_count ?? data.count ?? 0);
      showToast('Cart updated.', 'info');
    } catch (error) {
      showToast('Failed to update cart.', 'error');
    }
  };

  /**
   * Remove an item from the cart
   */
  window.removeFromCart = async (cartId) => {
    try {
      const data = await cartApiRequest({ action: 'remove', cart_id: cartId });

      const row = document.querySelector(`[data-cart-id="${cartId}"]`);
      if (row) {
        row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
          row.remove();
          const cartBody = document.querySelector('.cart-table tbody, .cart-items');
          if (cartBody && cartBody.children.length === 0) {
            cartBody.innerHTML = '<tr><td colspan="6" class="text-center py-5">Your cart is empty</td></tr>';
          }
        }, 300);
      }

      if (data.total !== undefined) {
        const totalEl = document.querySelector('.cart-total');
        if (totalEl) totalEl.textContent = `$${parseFloat(data.total).toFixed(2)}`;
      }
      if (data.subtotal !== undefined) {
        const subtotalEl = document.querySelector('.cart-subtotal');
        if (subtotalEl) subtotalEl.textContent = `$${parseFloat(data.subtotal).toFixed(2)}`;
      }

      updateCartBadge(data.cart_count ?? data.count ?? 0);
      showToast('Item removed from cart.', 'success');
    } catch (error) {
      showToast('Failed to remove item.', 'error');
    }
  };


  // ========================
  // 5. Wishlist Operations
  // ========================

  /**
   * Toggle a product in the wishlist
   */
  window.toggleWishlist = async (productId) => {
    try {
      const formData = new FormData();
      formData.append('action', 'toggle');
      formData.append('productid', productId);

      const response = await fetch(siteBase + 'api/wishlist.php', {
        method: 'POST',
        body: formData
      });

      if (response.status === 401 || response.status === 302) {
        window.location.href = 'login.php';
        return;
      }

      if (!response.ok) throw new Error('Request failed');
      const data = await response.json();

      if (data.redirect) {
        window.location.href = data.redirect;
        return;
      }

      const hearts = document.querySelectorAll(`[data-wishlist="${productId}"]`);
      hearts.forEach(heart => {
        const icon = heart.querySelector('i');
        if (data.action === 'added') {
          heart.classList.add('active');
          if (icon) { icon.classList.remove('bi-heart'); icon.classList.add('bi-heart-fill'); }
        } else {
          heart.classList.remove('active');
          if (icon) { icon.classList.remove('bi-heart-fill'); icon.classList.add('bi-heart'); }
        }
      });

      const badge = document.querySelector('.wishlist-count');
      if (badge && data.wishlist_count !== undefined) {
        badge.textContent = data.wishlist_count;
      }

      showToast(data.action === 'added' ? 'Added to wishlist!' : 'Removed from wishlist.', 'info');
    } catch (error) {
      showToast('Please log in to manage your wishlist.', 'error');
      window.location.href = 'login.php';
    }
  };


  // ========================
  // 6. Quantity Selector
  // ========================
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.qty-btn-plus, .qty-btn-minus');
    if (!btn) return;

    const container = btn.closest('.quantity-selector, .qty');
    if (!container) return;

    const input = container.querySelector('input[type="number"], input.qty-input');
    if (!input) return;

    let value = parseInt(input.value) || 1;
    const min = parseInt(input.getAttribute('min')) || 1;
    const max = parseInt(input.getAttribute('data-max')) || parseInt(input.max) || 999;

    if (btn.classList.contains('qty-btn-plus')) {
      value = Math.min(value + 1, max);
    } else {
      value = Math.max(value - 1, min);
    }

    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));

    // Update hidden field if present
    const hidden = container.querySelector('input[type="hidden"]');
    if (hidden) hidden.value = value;
  });


  // ========================
  // 7. Product Image Gallery
  // ========================
  const thumbnails = document.querySelectorAll('.product-thumbnail, .gallery-thumb');
  const mainImage = document.querySelector('.product-main-image, .gallery-main-img');

  thumbnails.forEach(thumb => {
    thumb.addEventListener('click', () => {
      if (!mainImage) return;

      const newSrc = thumb.getAttribute('data-img') || thumb.querySelector('img')?.src;
      if (!newSrc) return;

      mainImage.style.opacity = '0';
      setTimeout(() => {
        mainImage.src = newSrc;
        mainImage.style.opacity = '1';
      }, 150);

      thumbnails.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
    });
  });

  // Add smooth transition to main image
  if (mainImage) {
    mainImage.style.transition = 'opacity 0.2s ease';
  }


  // ========================
  // 8. Image Zoom / Lightbox
  // ========================
  const zoomTriggers = document.querySelectorAll('.product-main-image, .img-zoom-trigger');

  zoomTriggers.forEach(img => {
    img.style.cursor = 'zoom-in';

    img.addEventListener('click', () => {
      const src = img.getAttribute('data-full') || img.src;

      // Create lightbox overlay
      const overlay = document.createElement('div');
      overlay.className = 'lightbox-overlay';
      overlay.style.cssText = `
        position:fixed;top:0;left:0;width:100%;height:100%;
        background:rgba(0,0,0,0.9);z-index:9999;display:flex;
        align-items:center;justify-content:center;cursor:zoom-out;
        animation: fadeIn 0.2s ease;
      `;

      const fullImg = document.createElement('img');
      fullImg.src = src;
      fullImg.style.cssText = `
        max-width:90%;max-height:90vh;object-fit:contain;
        border-radius:4px;animation: zoomIn 0.2s ease;
      `;

      const closeBtn = document.createElement('button');
      closeBtn.innerHTML = '&times;';
      closeBtn.style.cssText = `
        position:absolute;top:20px;right:30px;background:none;border:none;
        color:white;font-size:2.5rem;cursor:pointer;line-height:1;
      `;

      overlay.appendChild(fullImg);
      overlay.appendChild(closeBtn);
      document.body.appendChild(overlay);
      document.body.style.overflow = 'hidden';

      const closeLightbox = () => {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.2s ease';
        setTimeout(() => {
          overlay.remove();
          document.body.style.overflow = '';
        }, 200);
      };

      overlay.addEventListener('click', (e) => {
        if (e.target === overlay || e.target === closeBtn) closeLightbox();
      });

      document.addEventListener('keydown', function handler(e) {
        if (e.key === 'Escape') {
          closeLightbox();
          document.removeEventListener('keydown', handler);
        }
      });
    });
  });


  // ========================
  // 9. Product Tabs
  // ========================
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-tab');

      tabBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      tabContents.forEach(content => {
        content.style.display = content.id === target ? 'block' : 'none';
        if (content.id === target) {
          content.classList.add('active');
        } else {
          content.classList.remove('active');
        }
      });
    });
  });

  // Activate first tab by default
  if (tabBtns.length > 0 && tabContents.length > 0) {
    const firstTab = tabBtns[0].getAttribute('data-tab');
    tabBtns[0].classList.add('active');
    tabContents.forEach(content => {
      content.style.display = content.id === firstTab ? 'block' : 'none';
    });
  }


  // ========================
  // 10. Coupon Code
  // ========================
  const couponForm = document.querySelector('.coupon-form, #coupon-form');

  if (couponForm) {
    couponForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const input = couponForm.querySelector('input[name="coupon"], input[name="code"], #coupon-code');
      if (!input || !input.value.trim()) {
        showToast('Please enter a coupon code.', 'error');
        return;
      }

      const submitBtn = couponForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const formData = new FormData();
        formData.append('action', 'apply_coupon');
        formData.append('coupon_code', input.value.trim());

        const response = await fetch(siteBase + 'api/cart.php', {
          method: 'POST',
          body: formData
        });

        if (!response.ok) throw new Error('Coupon request failed');
        const data = await response.json();

        if (data.success) {
          showToast('Coupon applied successfully!', 'success');

          // Update discount display
          const discountEl = document.querySelector('.coupon-discount, .discount-amount');
          if (discountEl && data.discount !== undefined) {
            discountEl.textContent = `-$${parseFloat(data.discount).toFixed(2)}`;
            discountEl.closest('.discount-row')?.classList.remove('d-none');
          }

          // Update totals
          const totalEl = document.querySelector('.cart-total, .order-total');
          if (totalEl && data.total !== undefined) {
            totalEl.textContent = `$${parseFloat(data.total).toFixed(2)}`;
          }
        } else {
          showToast(data.message || 'Invalid coupon code.', 'error');
        }
      } catch (error) {
        showToast('Failed to apply coupon.', 'error');
      } finally {
        if (submitBtn) submitBtn.disabled = false;
      }
    });
  }


  // ========================
  // 11. Toast Notifications
  // ========================
  window.showToast = (message, type = 'success') => {
    const colors = {
      success: '#28a745',
      error: '#dc3545',
      info: '#17a2b8'
    };
    const icons = {
      success: '&#10004;',
      error: '&#10008;',
      info: '&#8505;'
    };

    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type}`;
    toast.innerHTML = `
      <span class="toast-icon" style="color:${colors[type]};margin-right:10px;font-size:1.1rem;">${icons[type]}</span>
      <span class="toast-message">${message}</span>
    `;
    toast.style.cssText = `
      position:fixed;top:20px;right:20px;padding:14px 22px;
      background:#fff;color:#333;border-radius:8px;
      box-shadow:0 4px 20px rgba(0,0,0,0.15);z-index:10000;
      display:flex;align-items:center;font-size:0.95rem;
      transform:translateX(calc(100% + 30px));transition:transform 0.35s cubic-bezier(0.68,-0.55,0.27,1.55);
      border-left:4px solid ${colors[type]};max-width:360px;
    `;

    document.body.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
      });
    });

    // Auto remove after 3 seconds
    setTimeout(() => {
      toast.style.transform = 'translateX(calc(100% + 30px))';
      setTimeout(() => toast.remove(), 400);
    }, 3000);
  };


  // ========================
  // 12. Scroll to Top
  // ========================
  const scrollTopBtn = document.querySelector('.scroll-top, #scroll-top');

  const handleScrollTopVisibility = () => {
    if (!scrollTopBtn) return;
    window.scrollY > 300 ? scrollTopBtn.classList.add('show') : scrollTopBtn.classList.remove('show');
  };

  window.addEventListener('scroll', handleScrollTopVisibility, { passive: true });

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }


  // ========================
  // 13. Newsletter Form
  // ========================
  const newsletterForm = document.querySelector('.newsletter-form, #newsletter-form');

  if (newsletterForm) {
    newsletterForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const input = newsletterForm.querySelector('input[type="email"]');
      if (input && input.value.trim()) {
        showToast('Thank you for subscribing!', 'success');
        input.value = '';
      } else {
        showToast('Please enter a valid email address.', 'error');
      }
    });
  }


  // ========================
  // 14. Quick View Modal
  // ========================
  document.addEventListener('click', async (e) => {
    const quickViewBtn = e.target.closest('.quick-view-btn, [data-quick-view]');
    if (!quickViewBtn) return;

    e.preventDefault();
    const productId = quickViewBtn.getAttribute('data-product-id') || quickViewBtn.getAttribute('data-quick-view');
    if (!productId) return;

    // Find or create the quick view modal
    let modalEl = document.getElementById('quickViewModal');
    if (!modalEl) {
      modalEl = document.createElement('div');
      modalEl.id = 'quickViewModal';
      modalEl.className = 'modal fade';
      modalEl.tabIndex = -1;
      modalEl.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Quick View</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickViewBody">
              <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(modalEl);
    }

    const bsModal = new bootstrap.Modal(modalEl);
    bsModal.show();

    const body = document.getElementById('quickViewBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';

    try {
      const response = await fetch(`api/product.php?id=${productId}`);
      if (!response.ok) throw new Error('Failed to load product');
      const product = await response.json();

      body.innerHTML = `
        <div class="row">
          <div class="col-md-6">
            <img src="${product.image}" alt="${product.name}" class="img-fluid rounded" style="max-height:350px;object-fit:cover;width:100%;">
          </div>
          <div class="col-md-6">
            <h4 class="mb-2">${product.name}</h4>
            <p class="text-primary fs-4 fw-bold mb-2">$${parseFloat(product.price).toFixed(2)}</p>
            ${product.old_price ? `<p class="text-muted text-decoration-line-through mb-2">$${parseFloat(product.old_price).toFixed(2)}</p>` : ''}
            <p class="text-muted mb-4">${product.description || 'No description available.'}</p>
            <button class="btn btn-primary btn-lg w-100" onclick="addToCart(${product.id}); bootstrap.Modal.getInstance(document.getElementById('quickViewModal')).hide();">
              <i class="fas fa-shopping-cart me-2"></i>Add to Cart
            </button>
          </div>
        </div>
      `;
    } catch (error) {
      body.innerHTML = '<div class="text-center py-5 text-danger">Failed to load product details.</div>';
    }
  });


  // ========================
  // 15. Checkout Form Validation
  // ========================
  const checkoutForm = document.querySelector('.checkout-form, #checkout-form');

  if (checkoutForm) {
    checkoutForm.addEventListener('submit', (e) => {
      const requiredFields = checkoutForm.querySelectorAll('[required]');
      let isValid = true;

      requiredFields.forEach(field => {
        // Remove previous error styling
        field.classList.remove('is-invalid');

        if (!field.value.trim()) {
          field.classList.add('is-invalid');
          isValid = false;
        }

        // Email validation
        if (field.type === 'email' && field.value.trim()) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(field.value.trim())) {
            field.classList.add('is-invalid');
            isValid = false;
          }
        }

        // Phone validation
        if (field.type === 'tel' && field.value.trim()) {
          const phoneRegex = /^[\d\s\-\+\(\)]{7,20}$/;
          if (!phoneRegex.test(field.value.trim())) {
            field.classList.add('is-invalid');
            isValid = false;
          }
        }
      });

      if (!isValid) {
        e.preventDefault();
        showToast('Please fill in all required fields correctly.', 'error');

        // Scroll to first invalid field
        const firstInvalid = checkoutForm.querySelector('.is-invalid');
        if (firstInvalid) {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalid.focus();
        }
      }
    });

    // Real-time validation: remove error styling on input
    checkoutForm.querySelectorAll('[required]').forEach(field => {
      field.addEventListener('input', () => {
        if (field.value.trim()) {
          field.classList.remove('is-invalid');
        }
      });
    });
  }


  // ========================
  // 16. Lazy Loading
  // ========================
  const lazyImages = document.querySelectorAll('img[data-src]');

  if ('IntersectionObserver' in window && lazyImages.length > 0) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.getAttribute('data-src');
          img.removeAttribute('data-src');
          img.addEventListener('load', () => img.classList.add('loaded'));
          img.addEventListener('error', () => {
            img.classList.add('load-error');
            img.alt = 'Image failed to load';
          });
          observer.unobserve(img);
        }
      });
    }, {
      rootMargin: '100px 0px',
      threshold: 0.01
    });

    lazyImages.forEach(img => imageObserver.observe(img));
  } else {
    // Fallback for browsers without IntersectionObserver
    lazyImages.forEach(img => {
      img.src = img.getAttribute('data-src');
      img.removeAttribute('data-src');
    });
  }


  // ========================
  // 17. Review Star Rating
  // ========================
  const starContainers = document.querySelectorAll('.star-rating, .review-stars');

  starContainers.forEach(container => {
    const stars = container.querySelectorAll('.star, .rating-star, i');
    const hiddenInput = container.querySelector('input[type="hidden"]');
    if (!stars.length) return;

    const highlightStars = (rating) => {
      stars.forEach((star, index) => {
        if (index < rating) {
          star.classList.add('active', 'text-warning');
          star.classList.remove('text-muted');
        } else {
          star.classList.remove('active', 'text-warning');
          star.classList.add('text-muted');
        }
      });
    };

    stars.forEach((star, index) => {
      star.style.cursor = 'pointer';

      star.addEventListener('mouseenter', () => highlightStars(index + 1));

      star.addEventListener('mouseleave', () => {
        const currentRating = hiddenInput ? parseInt(hiddenInput.value) || 0 : 0;
        highlightStars(currentRating);
      });

      star.addEventListener('click', () => {
        const rating = index + 1;
        if (hiddenInput) hiddenInput.value = rating;
        highlightStars(rating);
        container.dispatchEvent(new CustomEvent('ratingChange', { detail: { rating } }));
      });
    });

    // Initialize with current value
    const initialRating = hiddenInput ? parseInt(hiddenInput.value) || 0 : 0;
    highlightStars(initialRating);
  });


  // ========================
  // Global Event Delegation
  // ========================

  // Handle add-to-cart buttons across the site
  document.addEventListener('click', (e) => {
    const addBtn = e.target.closest('.add-to-cart-btn, [data-add-to-cart]');
    if (!addBtn) return;

    e.preventDefault();
    const productId = addBtn.getAttribute('data-product-id') || addBtn.getAttribute('data-add-to-cart');
    if (!productId) return;

    const quantityInput = addBtn.closest('.product-card, .product-detail, .quick-view')?.querySelector('input[type="number"], .qty-input');
    const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;

    const colorInput = addBtn.closest('.product-card, .product-detail')?.querySelector('select[name="color"], input[name="color"]:checked');
    const sizeInput = addBtn.closest('.product-card, .product-detail')?.querySelector('select[name="size"], input[name="size"]:checked');

    const color = colorInput ? colorInput.value : null;
    const size = sizeInput ? sizeInput.value : null;

    addToCart(parseInt(productId), quantity, color, size);
  });

  // Handle wishlist buttons across the site
  document.addEventListener('click', (e) => {
    const heartBtn = e.target.closest('.wishlist-btn, [data-wishlist]');
    if (!heartBtn) return;

    e.preventDefault();
    const productId = heartBtn.getAttribute('data-product-id') || heartBtn.getAttribute('data-wishlist');
    if (productId) toggleWishlist(parseInt(productId));
  });

  // Handle cart quantity changes (for cart page inputs)
  document.addEventListener('change', (e) => {
    const qtyInput = e.target.closest('.cart-qty-input, .cart .qty-input');
    if (!qtyInput) return;

    const cartId = qtyInput.getAttribute('data-cart-id') || qtyInput.closest('[data-cart-id]')?.getAttribute('data-cart-id');
    const quantity = parseInt(qtyInput.value) || 1;

    if (cartId) updateCartQuantity(parseInt(cartId), quantity);
  });

  // Handle remove-from-cart buttons
  document.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.remove-cart-item, [data-remove-cart]');
    if (!removeBtn) return;

    e.preventDefault();
    const cartId = removeBtn.getAttribute('data-cart-id') || removeBtn.getAttribute('data-remove-cart');
    if (cartId) removeFromCart(parseInt(cartId));
  });

});