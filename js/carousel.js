// ===== HERO CAROUSEL =====
(function () {
    var slides = document.getElementById('carouselSlides');
    var prevBtn = document.getElementById('carouselPrev');
    var nextBtn = document.getElementById('carouselNext');
    var dotsContainer = document.getElementById('carouselDots');

    if (!slides || !prevBtn || !nextBtn || !dotsContainer) return;

    var totalSlides = slides.children.length;
    var currentIndex = 0;
    var autoPlayInterval;
    var isTransitioning = false;

    // Create dots
    for (var i = 0; i < totalSlides; i++) {
        var dot = document.createElement('div');
        dot.className = 'carousel-dot' + (i === 0 ? ' active' : '');
        dot.dataset.index = i;
        dot.addEventListener('click', function () {
            var idx = parseInt(this.dataset.index);
            goToSlide(idx);
        });
        dotsContainer.appendChild(dot);
    }

    function goToSlide(index) {
        if (isTransitioning || index === currentIndex) return;
        isTransitioning = true;

        currentIndex = index;
        slides.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';

        // Update dots
        var dots = dotsContainer.children;
        for (var i = 0; i < dots.length; i++) {
            dots[i].classList.remove('active');
        }
        dots[currentIndex].classList.add('active');

        setTimeout(function () {
            isTransitioning = false;
        }, 500);
    }

    function nextSlide() {
        var next = (currentIndex + 1) % totalSlides;
        goToSlide(next);
    }

    function prevSlide() {
        var prev = (currentIndex - 1 + totalSlides) % totalSlides;
        goToSlide(prev);
    }

    function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, 4000);
    }

    function stopAutoPlay() {
        clearInterval(autoPlayInterval);
    }

    nextBtn.addEventListener('click', function () {
        stopAutoPlay();
        nextSlide();
        startAutoPlay();
    });

    prevBtn.addEventListener('click', function () {
        stopAutoPlay();
        prevSlide();
        startAutoPlay();
    });

    var banner = document.querySelector('.hero-banner');
    if (banner) {
        banner.addEventListener('mouseenter', stopAutoPlay);
        banner.addEventListener('mouseleave', startAutoPlay);
    }

    // Touch support
    var touchStartX = 0;
    var touchEndX = 0;

    slides.addEventListener('touchstart', function (e) {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    slides.addEventListener('touchend', function (e) {
        touchEndX = e.changedTouches[0].screenX;
        var diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
    }, { passive: true });

    startAutoPlay();
})();
