// ===== PRODUCT DATA =====
var products = [
    { id: 1, name: 'Apple iPhone 16 Pro Max', brand: 'Apple', price: 159900, origPrice: 169900, discount: 6, rating: 4.6, reviews: 2431, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/c/l/9/-original-imah8zzgztfmpmhn.jpeg?q=90', category: 'Mobiles' },
    { id: 2, name: 'Samsung Galaxy S25 Ultra', brand: 'Samsung', price: 129999, origPrice: 149999, discount: 13, rating: 4.5, reviews: 1856, ram: '12', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/x/w/o/-original-imah9yh3dspzqzxt.jpeg?q=90', category: 'Mobiles' },
    { id: 3, name: 'OnePlus 13 Pro', brand: 'OnePlus', price: 69999, origPrice: 79999, discount: 12, rating: 4.4, reviews: 3120, ram: '12', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/l/o/g/-original-imah73ymbfvuxher.jpeg?q=90', category: 'Mobiles' },
    { id: 4, name: 'Xiaomi 15 Pro', brand: 'Xiaomi', price: 59999, origPrice: 69999, discount: 14, rating: 4.3, reviews: 4521, ram: '12', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/a/p/v/-original-imah8nshhzmpmpp9.jpeg?q=90', category: 'Mobiles' },
    { id: 5, name: 'Realme GT 8 Pro', brand: 'Realme', price: 34999, origPrice: 42999, discount: 19, rating: 4.2, reviews: 5632, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/t/h/g/-original-imah6zhgrshrnjyg.jpeg?q=90', category: 'Mobiles' },
    { id: 6, name: 'Vivo X300 Pro', brand: 'Vivo', price: 54999, origPrice: 64999, discount: 15, rating: 4.3, reviews: 2187, ram: '12', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/t/i/5/-original-imah4tztnfrykjch.jpeg?q=90', category: 'Mobiles' },
    { id: 7, name: 'Oppo Find X9 Pro', brand: 'Oppo', price: 79999, origPrice: 89999, discount: 11, rating: 4.4, reviews: 1432, ram: '16', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/1/m/p/-original-imah74ggmg5ffncz.jpeg?q=90', category: 'Mobiles' },
    { id: 8, name: 'Apple iPhone 17e', brand: 'Apple', price: 59900, origPrice: 69900, discount: 14, rating: 4.7, reviews: 892, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/v/r/6/-original-imah9y8hzhzznpjy.jpeg?q=90', category: 'Mobiles' },
    { id: 9, name: 'Samsung Galaxy Z Fold 7', brand: 'Samsung', price: 164999, origPrice: 179999, discount: 8, rating: 4.3, reviews: 654, ram: '12', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/p/f/u/-original-imah7hgyfbhzukmf.jpeg?q=90', category: 'Mobiles' },
    { id: 10, name: 'OnePlus Nord 5', brand: 'OnePlus', price: 27999, origPrice: 32999, discount: 15, rating: 4.1, reviews: 8743, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/v/e/3/-original-imah5fzsjhpss7j4.jpeg?q=90', category: 'Mobiles' },
    { id: 11, name: 'Xiaomi Redmi Note 15 Pro', brand: 'Xiaomi', price: 24999, origPrice: 29999, discount: 17, rating: 4.2, reviews: 12453, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/p/h/w/-original-imah6egz5jhhqjpv.jpeg?q=90', category: 'Mobiles' },
    { id: 12, name: 'Realme Narzo 80 Pro', brand: 'Realme', price: 19999, origPrice: 24999, discount: 20, rating: 4.0, reviews: 9876, ram: '6', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/w/v/l/-original-imah4bxfytsafgnh.jpeg?q=90', category: 'Mobiles' },
    { id: 13, name: 'Vivo V70 Pro', brand: 'Vivo', price: 32999, origPrice: 39999, discount: 18, rating: 4.1, reviews: 4321, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/w/a/2/-original-imah7f4zh6fg7yup.jpeg?q=90', category: 'Mobiles' },
    { id: 14, name: 'Oppo Reno 15 Pro', brand: 'Oppo', price: 39999, origPrice: 46999, discount: 15, rating: 4.2, reviews: 3456, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/g/l/x/-original-imah6tfzyzchvfg5.jpeg?q=90', category: 'Mobiles' },
    { id: 15, name: 'Nothing Phone 3', brand: 'OnePlus', price: 44999, origPrice: 49999, discount: 10, rating: 4.3, reviews: 2134, ram: '12', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/c/n/f/-original-imah8nhstktnyreg.jpeg?q=90', category: 'Mobiles' },
    { id: 16, name: 'Samsung Galaxy A56 5G', brand: 'Samsung', price: 32999, origPrice: 37999, discount: 13, rating: 4.1, reviews: 6789, ram: '8', img: 'https://rukminim2.flixcart.com/image/280/374/xif0q/mobile/g/l/x/-original-imah6tfzyzchvfg5.jpeg?q=90', category: 'Mobiles' }
];

var currentProducts = products.slice();
var currentSort = 'popularity';
var currentPage = 1;
var itemsPerPage = 12;

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
updateDisplay();
