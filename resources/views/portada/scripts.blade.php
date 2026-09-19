{{-- Script Controlador del Carrusel Principal (Latitud 18) & Ticker --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
  const carousel = document.getElementById('heroNewsCarousel');
  if (!carousel) return;

  const slides = carousel.querySelectorAll('.hero-carousel-slide');
  const dots = carousel.querySelectorAll('.hero-car-dot');
  const prevBtn = document.getElementById('heroPrevBtn');
  const nextBtn = document.getElementById('heroNextBtn');
  const total = slides.length;
  if (total <= 1) return;

  let current = 0;
  let autoplayTimer = null;

  function goToSlide(index) {
    if (index < 0) index = total - 1;
    if (index >= total) index = 0;
    current = index;

    slides.forEach(function(slide, i) {
      slide.classList.toggle('active', i === current);
    });

    dots.forEach(function(dot, i) {
      dot.classList.toggle('active', i === current);
    });
  }

  function nextSlide() {
    goToSlide(current + 1);
  }

  function prevSlide() {
    goToSlide(current - 1);
  }

  function startAutoplay() {
    stopAutoplay();
    autoplayTimer = setInterval(nextSlide, 5000);
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      nextSlide();
      startAutoplay();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      prevSlide();
      startAutoplay();
    });
  }

  dots.forEach(function(dot) {
    dot.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      const idx = parseInt(this.getAttribute('data-index'), 10);
      goToSlide(idx);
      startAutoplay();
    });
  });

  carousel.addEventListener('mouseenter', stopAutoplay);
  carousel.addEventListener('mouseleave', startAutoplay);

  // Soporte Touch Swipe en pantallas móviles / táctiles
  let touchStartX = 0;
  let touchEndX = 0;

  carousel.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
  }, { passive: true });

  carousel.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    if (touchStartX - touchEndX > 45) {
      nextSlide();
      startAutoplay();
    } else if (touchEndX - touchStartX > 45) {
      prevSlide();
      startAutoplay();
    }
  }, { passive: true });

  startAutoplay();
});

// -------------------------------------------------------
// TICKER AUTO-SCROLL: desplaza horizontalmente el ticker
// -------------------------------------------------------
(function() {
  const row = document.getElementById('tickerRow');
  if (!row) return;
  let pos = 0;
  const speed = 0.6; // px/frame
  let paused = false;

  function scroll() {
    if (!paused) {
      pos += speed;
      if (pos >= row.scrollWidth / 2) pos = 0;
      row.scrollLeft = pos;
    }
    requestAnimationFrame(scroll);
  }

  row.addEventListener('mouseenter', function() { paused = true; });
  row.addEventListener('mouseleave', function() { paused = false; });

  // Duplicate items so scrolling loops
  const items = row.innerHTML;
  row.innerHTML = items + items;

  requestAnimationFrame(scroll);
})();
</script>
