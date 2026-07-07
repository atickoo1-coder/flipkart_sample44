(function() {
    'use strict';

    var path = window.location.pathname;
    var baseSegment = path.indexOf('/flipkart_sample44') !== -1 ? '/flipkart_sample44' : '';
    var baseUrl = window.location.protocol + '//' + window.location.host + baseSegment;
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    csrfToken = csrfToken ? csrfToken.getAttribute('content') : '';

    function showToast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(function() {
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 3000);
    }

    function updateWishlistBadge(count) {
        var badge = document.getElementById('wishlistBadge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function fetchWishlistCount() {
        fetch(baseUrl + '/wishlist-count.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateWishlistBadge(data.count || 0);
            })
            .catch(function() {});
    }

    function updateCartBadge(count) {
        var badge = document.getElementById('cartBadge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function fetchCartCount() {
        fetch(baseUrl + '/cart-count.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateCartBadge(data.count || 0);
            })
            .catch(function() {});
    }

    function toggleWishlist(productId, btn) {
        if (!btn) return;

        btn.disabled = true;
        btn.classList.add('loading');

        fetch(baseUrl + '/wishlist-toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId + '&csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (data.action === 'added') {
                    btn.classList.add('active');
                    btn.setAttribute('data-wishlist', '1');
                    btn.title = 'Remove from Wishlist';
                    showToast('added to wishlist', 'success');
                } else {
                    btn.classList.remove('active');
                    btn.setAttribute('data-wishlist', '0');
                    btn.title = 'Add to Wishlist';
                    showToast('removed from wishlist', 'success');
                }
                updateWishlistBadge(data.count);
                var event = new CustomEvent('wishlistUpdate', { detail: { count: data.count } });
                document.dispatchEvent(event);
            } else {
                if (data.message === 'Please login first') {
                    window.location.href = baseUrl + '/customer/login.php';
                } else {
                    showToast(data.message || 'Something went wrong', 'error');
                }
            }
        })
        .catch(function() {
            showToast('Something went wrong', 'error');
        })
        .finally(function() {
            btn.disabled = false;
            btn.classList.remove('loading');
        });
    }

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.wishlist-btn-heart');
        if (!btn) return;

        e.preventDefault();
        e.stopPropagation();

        var productId = btn.getAttribute('data-product-id');
        if (!productId) return;

        toggleWishlist(productId, btn);
    });

    document.addEventListener('mouseenter', function(e) {
        var btn = e.target.closest('.wishlist-btn-heart');
        if (!btn) return;
        var rect = btn.getBoundingClientRect();
        var tooltip = document.createElement('div');
        tooltip.className = 'wishlist-tooltip';
        tooltip.textContent = btn.classList.contains('active') ? 'Remove from Wishlist' : 'Add to Wishlist';
        tooltip.style.cssText = 'position:fixed;top:' + (rect.top - 30) + 'px;left:' + (rect.left + rect.width / 2) + 'px;transform:translateX(-50%);background:#212121;color:#fff;font-size:11px;padding:4px 10px;border-radius:2px;white-space:nowrap;z-index:9999;pointer-events:none;';
        tooltip.id = 'wishlistTooltip';
        document.body.appendChild(tooltip);
    }, true);

    document.addEventListener('mouseleave', function(e) {
        var btn = e.target.closest('.wishlist-btn-heart');
        if (!btn) return;
        var tooltip = document.getElementById('wishlistTooltip');
        if (tooltip) tooltip.parentNode.removeChild(tooltip);
    }, true);

    document.addEventListener('DOMContentLoaded', function() {
        fetchWishlistCount();
        fetchCartCount();
    });

    window.showToast = showToast;
    window.toggleWishlist = toggleWishlist;
    window.updateWishlistBadge = updateWishlistBadge;
    window.fetchWishlistCount = fetchWishlistCount;
    window.updateCartBadge = updateCartBadge;
    window.fetchCartCount = fetchCartCount;
    window.wishlistBaseUrl = baseUrl;
})();
