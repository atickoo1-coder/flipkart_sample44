// ===== SEARCH SUGGESTIONS =====
const searchInput = document.getElementById('searchInput');
const searchSuggestions = document.getElementById('searchSuggestions');

if (searchInput && searchSuggestions) {
    searchInput.addEventListener('focus', function () {
        if (this.value.length > 0) {
            searchSuggestions.classList.add('active');
        }
    });

    searchInput.addEventListener('input', function () {
        if (this.value.length > 0) {
            searchSuggestions.classList.add('active');
        } else {
            searchSuggestions.classList.remove('active');
        }
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-box')) {
            searchSuggestions.classList.remove('active');
        }
    });

    searchSuggestions.querySelectorAll('li').forEach(function (item) {
        item.addEventListener('click', function () {
            searchInput.value = this.textContent.trim();
            searchSuggestions.classList.remove('active');
        });
    });
}

// ===== NAV SCROLL =====
const navInner = document.querySelector('.nav-inner');
if (navInner) {
    let isDown = false;
    let startX;
    let scrollLeft;

    navInner.addEventListener('mousedown', function (e) {
        isDown = true;
        startX = e.pageX - navInner.offsetLeft;
        scrollLeft = navInner.scrollLeft;
        navInner.style.cursor = 'grabbing';
    });

    navInner.addEventListener('mouseleave', function () {
        isDown = false;
        navInner.style.cursor = 'grab';
    });

    navInner.addEventListener('mouseup', function () {
        isDown = false;
        navInner.style.cursor = 'grab';
    });

    navInner.addEventListener('mousemove', function (e) {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - navInner.offsetLeft;
        const walk = (x - startX) * 2;
        navInner.scrollLeft = scrollLeft - walk;
    });

    navInner.style.cursor = 'grab';
}
