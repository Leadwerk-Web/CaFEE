/**
 * CaFEE Brückenmühle - Interactive Website
 * A Magical Café Experience
 */

// ============================================
// DOM Elements
// ============================================
const dom = {
    cursorDot: document.getElementById('cursorDot'),
    cursorTrail: document.getElementById('cursorTrail'),
    fairyDust: document.getElementById('fairyDust'),
    nav: document.getElementById('mainNav'),
    navToggle: document.getElementById('navToggle'),
    navMenu: document.getElementById('navMenu'),
    bookCover: document.getElementById('bookCover'),
    bookPages: document.getElementById('bookPages'),
    bookNav: document.getElementById('bookNav'),
    openBookBtn: document.getElementById('openBookBtn'),
    prevPage: document.getElementById('prevPage'),
    nextPage: document.getElementById('nextPage'),
    currentPageEl: document.getElementById('currentPage'),
    totalPagesEl: document.getElementById('totalPages'),
    heroParallax: document.querySelector('.hero-parallax-bg'),
    experienceBgParallax: document.querySelector('.experience-bg-parallax'),
};

// ============================================
// State
// ============================================
let state = {
    mouseX: 0,
    mouseY: 0,
    trailX: 0,
    trailY: 0,
    currentPage: 1,
    totalPages: 3,
    isBookOpen: false,
    lastScrollY: 0,
    dustParticles: [],
    rafId: null,
};

// ============================================
// Custom Cursor
// ============================================
function initCursor() {
    if (!dom.cursorDot || !dom.cursorTrail) return;

    // Check if device supports hover (not touch-only)
    if (window.matchMedia('(hover: none)').matches) return;

    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseenter', () => {
        dom.cursorDot.style.opacity = '1';
        dom.cursorTrail.style.opacity = '0.6';
    });
    document.addEventListener('mouseleave', () => {
        dom.cursorDot.style.opacity = '0';
        dom.cursorTrail.style.opacity = '0';
    });

    // Add hover effects for interactive elements
    const interactiveElements = document.querySelectorAll('a, button, input, textarea');
    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            dom.cursorDot.classList.add('active');
            dom.cursorTrail.classList.add('active');
        });
        el.addEventListener('mouseleave', () => {
            dom.cursorDot.classList.remove('active');
            dom.cursorTrail.classList.remove('active');
        });
    });

    // Start animation loop
    animateCursor();
}

function handleMouseMove(e) {
    state.mouseX = e.clientX;
    state.mouseY = e.clientY;

    // Create fairy dust particles more frequently for better sparkle effect
    if (Math.random() < 0.5) {
        createFairyParticle(e.clientX, e.clientY);
        // Create additional sparkles for more magical effect
        if (Math.random() < 0.4) {
            setTimeout(() => createFairyParticle(e.clientX, e.clientY), 50);
        }
        if (Math.random() < 0.25) {
            setTimeout(() => createFairyParticle(e.clientX, e.clientY), 100);
        }
    }
}

function animateCursor() {
    // Smooth trailing effect
    state.trailX += (state.mouseX - state.trailX) * 0.15;
    state.trailY += (state.mouseY - state.trailY) * 0.15;

    if (dom.cursorDot) {
        dom.cursorDot.style.left = `${state.mouseX}px`;
        dom.cursorDot.style.top = `${state.mouseY}px`;
    }

    if (dom.cursorTrail) {
        dom.cursorTrail.style.left = `${state.trailX}px`;
        dom.cursorTrail.style.top = `${state.trailY}px`;
    }

    requestAnimationFrame(animateCursor);
}

// ============================================
// Fairy Dust Particles
// ============================================
function createFairyParticle(x, y) {
    if (!dom.fairyDust) return;

    const particle = document.createElement('div');
    particle.className = 'fairy-particle';

    // Random offset and size - increased spread for more visibility
    const offsetX = (Math.random() - 0.5) * 50;
    const offsetY = (Math.random() - 0.5) * 50;
    const size = Math.random() * 6 + 3;

    particle.style.left = `${x + offsetX}px`;
    particle.style.top = `${y + offsetY}px`;
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;

    // Random animation duration for variety
    particle.style.animationDuration = `${1.5 + Math.random() * 1.5}s`;

    dom.fairyDust.appendChild(particle);

    // Remove after animation
    setTimeout(() => {
        particle.remove();
    }, 3000);
}

// ============================================
// Navigation
// ============================================
function initNavigation() {
    if (!dom.nav) return;

    // Scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            dom.nav.classList.add('scrolled');
        } else {
            dom.nav.classList.remove('scrolled');
        }
    });

    // Mobile toggle
    if (dom.navToggle && dom.navMenu) {
        dom.navToggle.addEventListener('click', () => {
            dom.navToggle.classList.toggle('active');
            dom.navMenu.classList.toggle('active');
            document.body.style.overflow = dom.navMenu.classList.contains('active') ? 'hidden' : '';
        });

        // Close menu on link click
        dom.navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                dom.navToggle.classList.remove('active');
                dom.navMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.scrollY - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ============================================
// Menu Book
// ============================================
function initMenuBook() {
    if (!dom.openBookBtn || !dom.bookCover || !dom.bookPages) return;

    // Set total pages
    if (dom.totalPagesEl) {
        dom.totalPagesEl.textContent = state.totalPages;
    }

    // Open book
    dom.openBookBtn.addEventListener('click', openBook);

    // Navigation
    if (dom.prevPage) {
        dom.prevPage.addEventListener('click', () => changePage(-1));
    }
    if (dom.nextPage) {
        dom.nextPage.addEventListener('click', () => changePage(1));
    }

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!state.isBookOpen) return;
        if (e.key === 'ArrowLeft') changePage(-1);
        if (e.key === 'ArrowRight') changePage(1);
        if (e.key === 'Escape') closeBook();
    });
}

function openBook() {
    state.isBookOpen = true;
    dom.bookCover.classList.add('hidden');
    dom.bookPages.classList.add('active');
    dom.bookNav.classList.add('active');
    showPage(1);
}

function closeBook() {
    state.isBookOpen = false;
    dom.bookCover.classList.remove('hidden');
    dom.bookPages.classList.remove('active');
    dom.bookNav.classList.remove('active');
}

function changePage(direction) {
    const newPage = state.currentPage + direction;
    if (newPage < 1 || newPage > state.totalPages) return;
    showPage(newPage);
}

function showPage(pageNum) {
    state.currentPage = pageNum;

    // Update page indicator
    if (dom.currentPageEl) {
        dom.currentPageEl.textContent = pageNum;
    }

    // Update navigation buttons
    if (dom.prevPage) {
        dom.prevPage.disabled = pageNum === 1;
    }
    if (dom.nextPage) {
        dom.nextPage.disabled = pageNum === state.totalPages;
    }

    // Show/hide pages
    const spreads = document.querySelectorAll('.book-spread');
    spreads.forEach((spread, index) => {
        if (index + 1 === pageNum) {
            spread.classList.add('active');
        } else {
            spread.classList.remove('active');
        }
    });
}

// ============================================
// Parallax Effects
// ============================================
function initParallax() {
    window.addEventListener('scroll', handleParallax, { passive: true });
}

function handleParallax() {
    const scrollY = window.scrollY;

    // Hero parallax
    if (dom.heroParallax) {
        const heroSection = document.querySelector('.hero');
        if (heroSection) {
            const rect = heroSection.getBoundingClientRect();
            if (rect.bottom > 0) {
                dom.heroParallax.style.transform = `scale(1.1) translateY(${scrollY * 0.3}px)`;
            }
        }
    }

    // Experience background parallax
    if (dom.experienceBgParallax) {
        const expSection = document.querySelector('.experience');
        if (expSection) {
            const rect = expSection.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                const progress = (window.innerHeight - rect.top) / (window.innerHeight + rect.height);
                dom.experienceBgParallax.style.transform = `scale(1.1) translateY(${progress * 50 - 25}px)`;
            }
        }
    }

    // Element parallax
    document.querySelectorAll('.parallax-element').forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            const speed = parseFloat(el.dataset.speed) || 0.1;
            const yPos = (window.innerHeight - rect.top) * speed;
            el.style.transform = `translateY(${yPos}px)`;
        }
    });
}

// ============================================
// Scroll Animations
// ============================================
function initScrollAnimations() {
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px -100px 0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = parseInt(entry.target.dataset.delay) || 0;
                setTimeout(() => {
                    entry.target.classList.add('visible');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.scroll-animate').forEach(el => {
        observer.observe(el);
    });
}

// ============================================
// Ambient Fairy Dust (Background)
// ============================================
function initAmbientDust() {
    // Create ambient sparkles more frequently
    setInterval(() => {
        if (Math.random() < 0.6) {
            const x = Math.random() * window.innerWidth;
            const y = Math.random() * window.innerHeight;
            createAmbientParticle(x, y);
        }
    }, 250);
}

function createAmbientParticle(x, y) {
    if (!dom.fairyDust) return;

    const particle = document.createElement('div');
    particle.className = 'fairy-particle';
    const size = Math.random() * 5 + 3;
    particle.style.left = `${x}px`;
    particle.style.top = `${y}px`;
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;
    particle.style.opacity = '0.7';
    particle.style.animationDuration = `${2 + Math.random() * 2}s`;

    dom.fairyDust.appendChild(particle);

    setTimeout(() => {
        particle.remove();
    }, 4000);
}

// ============================================
// Image Lazy Loading Enhancement
// ============================================
function initLazyLoading() {
    const images = document.querySelectorAll('img');

    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                imageObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    images.forEach(img => {
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.5s ease';
        imageObserver.observe(img);

        // Fallback for already loaded images
        if (img.complete) {
            img.style.opacity = '1';
        }
    });
}

// ============================================
// Touch Swipe for Menu Book
// ============================================
function initTouchSwipe() {
    const bookPages = dom.bookPages;
    if (!bookPages) return;

    let touchStartX = 0;
    let touchEndX = 0;

    bookPages.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    bookPages.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });

    function handleSwipe() {
        const diff = touchStartX - touchEndX;
        const threshold = 50;

        if (Math.abs(diff) > threshold) {
            if (diff > 0) {
                // Swipe left - next page
                changePage(1);
            } else {
                // Swipe right - previous page
                changePage(-1);
            }
        }
    }
}

// ============================================
// Performance Optimization
// ============================================
function optimizePerformance() {
    // Reduce fairy dust on low-end devices
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        // Disable fairy dust on low-end devices
        if (dom.fairyDust) {
            dom.fairyDust.style.display = 'none';
        }
    }

    // Reduce animations if user prefers reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('.scroll-animate').forEach(el => {
            el.style.transition = 'none';
            el.classList.add('visible');
        });

        if (dom.fairyDust) {
            dom.fairyDust.style.display = 'none';
        }
    }
}

// ============================================
// Active Navigation Highlight
// ============================================
function initActiveNavHighlight() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');

    function highlightNav() {
        let currentSection = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.offsetHeight;

            if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
                currentSection = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${currentSection}`) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', highlightNav, { passive: true });
    highlightNav();
}

// ============================================
// Initialize Everything
// ============================================
function init() {
    // Check if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
}

function initAll() {
    optimizePerformance();
    initCursor();
    initNavigation();
    initMenuBook();
    initParallax();
    initScrollAnimations();
    initAmbientDust();
    initLazyLoading();
    initTouchSwipe();
    initActiveNavHighlight();
    initVideoLightbox();
    initInterviewSlider();
    initInterviewLightbox();

    // Trigger initial animations
    setTimeout(() => {
        document.body.classList.add('loaded');
    }, 100);

    console.log('✨ CaFEE Brückenmühle website initialized');
}

// ============================================
// Video Lightbox
// ============================================
function initVideoLightbox() {
    const lightbox = document.getElementById('videoLightbox');
    const openBtn = document.getElementById('openVideoBtn');
    const closeBtn = document.getElementById('closeVideoBtn');
    const video = document.getElementById('lightboxVideo');

    if (!lightbox || !openBtn || !closeBtn || !video) return;

    function openLightbox() {
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
        video.play();
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        video.pause();
        video.currentTime = 0;
    }

    openBtn.addEventListener('click', openLightbox);
    closeBtn.addEventListener('click', closeLightbox);

    // Close on background click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });
}

// ============================================
// Interview Slider
// ============================================
function initInterviewSlider() {
    const slides = document.querySelectorAll('.interview-slide');
    const dots = document.querySelectorAll('.interview-dot');
    const prevBtn = document.getElementById('interviewPrev');
    const nextBtn = document.getElementById('interviewNext');

    if (slides.length === 0) return;

    let currentSlide = 0;
    const totalSlides = slides.length;

    function showSlide(index) {
        // Wrap around
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;

        // Pause all videos
        slides.forEach(slide => {
            slide.classList.remove('active');
            const video = slide.querySelector('.interview-video');
            if (video) {
                video.pause();
                video.currentTime = 0;
            }
        });

        // Update dots
        dots.forEach(dot => dot.classList.remove('active'));

        // Show current slide
        currentSlide = index;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');

        // Auto-play video in current slide (muted)
        const activeVideo = slides[currentSlide].querySelector('.interview-video');
        if (activeVideo) {
            activeVideo.muted = true;
            activeVideo.play().catch(() => { /* autoplay blocked */ });
        }
    }

    // Navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', () => showSlide(currentSlide - 1));
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', () => showSlide(currentSlide + 1));
    }

    // Dot navigation
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const slideIndex = parseInt(dot.dataset.slide);
            showSlide(slideIndex);
        });
    });

    // Touch swipe for interview slider
    const slidesContainer = document.getElementById('interviewSlides');
    if (slidesContainer) {
        let touchStartX = 0;
        let touchEndX = 0;

        slidesContainer.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slidesContainer.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) showSlide(currentSlide + 1);
                else showSlide(currentSlide - 1);
            }
        }, { passive: true });
    }

    // Auto-play first video on load
    const firstVideo = slides[0]?.querySelector('.interview-video');
    if (firstVideo) {
        firstVideo.muted = true;
        firstVideo.play().catch(() => { /* autoplay blocked */ });
    }
}

// ============================================
// Interview Lightbox
// ============================================
function initInterviewLightbox() {
    const lightbox = document.getElementById('interviewLightbox');
    const closeBtn = document.getElementById('closeInterviewBtn');
    const lightboxVideo = document.getElementById('interviewLightboxVideo');

    if (!lightbox || !closeBtn || !lightboxVideo) return;

    function openInterviewLightbox(videoSrc) {
        if (!videoSrc) return;
        lightboxVideo.querySelector('source').src = videoSrc;
        lightboxVideo.load();
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        lightboxVideo.muted = false;
        lightboxVideo.play();
    }

    // Click on interview video wrapper to open lightbox
    document.querySelectorAll('.interview-video-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', () => {
            const videoSrc = wrapper.querySelector('source')?.src;
            openInterviewLightbox(videoSrc);
        });
    });

    // Round play button (like Imagefilm) opens lightbox
    document.querySelectorAll('.interview-play-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const slide = btn.closest('.interview-slide');
            const source = slide?.querySelector('.interview-video source');
            if (source?.src) openInterviewLightbox(source.src);
        });
    });

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        lightboxVideo.pause();
        lightboxVideo.currentTime = 0;
    }

    closeBtn.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox.classList.contains('active')) {
            closeLightbox();
        }
    });
}

// Start initialization
init();

