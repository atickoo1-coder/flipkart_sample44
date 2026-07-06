// ===== PRODUCT DATA =====
var products = [];
var currentProducts = [];
var currentSort = 'popularity';
var currentPage = 1;
var itemsPerPage = 12;
var backendBase = '/api';

function loadProducts() {
    fetch(backendBase + '/products.php')
        .then(function (response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(function (data) {
            if (data && data.success && Array.isArray(data.products)) {
                products = data.products.map(function (product) {
                    return {
                        id: product.id,
                        name: product.name,
                        brand: product.brand || 'QuickKart',
                        price: parseFloat(product.price) || 0,
                        origPrice: parseFloat(product.original_price) || parseFloat(product.price) || 0,
                        discount: parseInt(product.discount, 10) || 0,
                        rating: parseFloat(product.rating) || 0,
                        reviews: parseInt(product.reviews, 10) || 0,
                        ram: product.ram || '8',
                        img: product.image_url || product.image || 'uploads/placeholder.png',
                        category: product.category_name || 'General'
                    };
                });
                currentProducts = products.slice();
                updateDisplay();
            } else {
                throw new Error('No products returned');
            }
        })
        .catch(function () {
            var grid = document.getElementById('productGrid');
            if (grid) {
                grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#878787;">Unable to load products right now. Please try again later.</div>';
            }
        });
}

// ===== RENDER PRODUCTS =====
function renderProducts(productsArray) {
    var grid = document.getElementById('productGrid');
    if (!grid) return;

    if (productsArray.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:40px;color:#878787;">No products match your filters.</div>';
        return;
    }

    var html = '';
    for (var i = 0; i < productsArray.length; i++) {
        var p = productsArray[i];
        var stars = '';
        var fullStars = Math.floor(p.rating);
        for (var s = 0; s < fullStars; s++) {
            stars += '\u2605';
        }
        if (p.rating % 1 >= 0.5) stars += '\u00BD';

        html += '<div class="product-card">';
        html += '<div class="product-card-inner">';
        html += '<button class="wishlist-btn-heart" data-product-id="' + p.id + '" data-wishlist="0" title="Add to Wishlist">';
        html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
        html += '</button>';
        html += '<img class="product-img" src="' + p.img + '" alt="' + p.name + '" loading="lazy" onerror="this.src=\'uploads/placeholder.png\'">';
        html += '<div class="product-title">' + p.name + '</div>';
        html += '<div class="product-rating">' + p.rating + ' ' + stars + '</div>';
        html += '<div class="product-price-row">';
        html += '<span class="final-price">\u20B9' + p.price.toLocaleString() + '</span>';
        if (p.origPrice > p.price) {
            html += '<span class="orig-price">\u20B9' + p.origPrice.toLocaleString() + '</span>';
            html += '<span class="discount">' + p.discount + '% off</span>';
        }
        html += '</div>';
        html += '</div>';
    }
    grid.innerHTML = html;
}

// ===== SORT PRODUCTS =====
function sortProducts(productsArray, sortBy) {
    var sorted = productsArray.slice();
    switch (sortBy) {
        case 'popularity':
            sorted.sort(function (a, b) { return b.reviews - a.reviews; });
            break;
        case 'low':
            sorted.sort(function (a, b) { return a.price - b.price; });
            break;
        case 'high':
            sorted.sort(function (a, b) { return b.price - a.price; });
            break;
        case 'newest':
            sorted.sort(function (a, b) { return b.id - a.id; });
            break;
    }
    return sorted;
}

// ===== UPDATE DISPLAY =====
function updateDisplay() {
    var filtered = products.slice();

    // Brand filter
    var brandChecks = document.querySelectorAll('[data-filter="brand"]:checked');
    if (brandChecks.length > 0) {
        var selectedBrands = [];
        for (var i = 0; i < brandChecks.length; i++) {
            selectedBrands.push(brandChecks[i].dataset.value);
        }
        filtered = filtered.filter(function (p) {
            return selectedBrands.indexOf(p.brand) !== -1;
        });
    }

    // Price filter
    var priceChecks = document.querySelectorAll('[data-filter="price"]:checked');
    if (priceChecks.length > 0) {
        var priceFiltered = [];
        for (var j = 0; j < priceChecks.length; j++) {
            var min = parseInt(priceChecks[j].dataset.min) || 0;
            var max = parseInt(priceChecks[j].dataset.max) || Infinity;
            priceFiltered = priceFiltered.concat(
                filtered.filter(function (p) { return p.price >= min && p.price <= max; })
            );
        }
        filtered = priceFiltered;
    }

    // RAM filter
    var ramChecks = document.querySelectorAll('[data-filter="ram"]:checked');
    if (ramChecks.length > 0) {
        var selectedRam = [];
        for (var k = 0; k < ramChecks.length; k++) {
            selectedRam.push(ramChecks[k].dataset.value);
        }
        filtered = filtered.filter(function (p) {
            return selectedRam.indexOf(p.ram) !== -1;
        });
    }

    // Discount filter
    var discountChecks = document.querySelectorAll('[data-filter="discount"]:checked');
    if (discountChecks.length > 0) {
        var maxDiscount = 0;
        for (var l = 0; l < discountChecks.length; l++) {
            var d = parseInt(discountChecks[l].dataset.value);
            if (d > maxDiscount) maxDiscount = d;
        }
        filtered = filtered.filter(function (p) { return p.discount >= maxDiscount; });
    }

    currentProducts = sortProducts(filtered, currentSort);
    renderProducts(currentProducts);
}

// ===== SORT BUTTONS =====
var sortButtons = document.querySelectorAll('.listing-sort button');
for (var si = 0; si < sortButtons.length; si++) {
    (function (btn) {
        btn.addEventListener('click', function () {
            for (var s = 0; s < sortButtons.length; s++) {
                sortButtons[s].classList.remove('active');
            }
            this.classList.add('active');
            currentSort = this.dataset.sort;
            updateDisplay();
        });
    })(sortButtons[si]);
}

// ===== FILTER CHANGE =====
var filterChecks = document.querySelectorAll('#filterSidebar input[type="checkbox"]');
for (var fi = 0; fi < filterChecks.length; fi++) {
    (function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateDisplay();
        });
    })(filterChecks[fi]);
}

// ===== PAGINATION =====
var paginationBtns = document.querySelectorAll('.listing-pagination button');
for (var pi = 0; pi < paginationBtns.length; pi++) {
    (function (btn) {
        btn.addEventListener('click', function () {
            var text = this.textContent.trim();
            if (text === 'Next') {
                var activePage = document.querySelector('.listing-pagination button.active');
                if (activePage && activePage.nextElementSibling && activePage.nextElementSibling.tagName === 'BUTTON') {
                    activePage.classList.remove('active');
                    activePage.nextElementSibling.classList.add('active');
                }
            } else {
                for (var p = 0; p < paginationBtns.length; p++) {
                    paginationBtns[p].classList.remove('active');
                }
                this.classList.add('active');
            }
        });
    })(paginationBtns[pi]);
}

// ===== INIT =====
loadProducts();
