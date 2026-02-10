/* ========================================
   CaFEE Brückenmühle - BOLD Interactive Script
   Custom Cursor, Parallax, Scroll Effects
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {
    // Check for touch device
    if ('ontouchstart' in window || navigator.maxTouchPoints > 0) {
        document.body.classList.add('touch-device');
    }

    // Initialize all components
    initPreloader();
    initCustomCursor();
    initNavigation();
    initScrollProgress();
    initScrollReveal();
    initHeroAnimations();
    initCounterAnimation();
    initParallax();
    initSmoothScroll();
    initImageParallax();
    initTiltEffect();
});

/* ----------------------------------------
   Preloader
   ---------------------------------------- */
function initPreloader() {
    const preloader = document.getElementById('preloader');

    if (!preloader) return;

    document.body.classList.add('loading');

    setTimeout(() => {
        preloader.classList.add('hidden');
        document.body.classList.remove('loading');
        triggerHeroReveal();
    }, 2500);
}

/* ----------------------------------------
   Custom Cursor
   ---------------------------------------- */
function initCustomCursor() {
    const cursor = document.getElementById('cursor');
    const follower = document.getElementById('cursorFollower');

    if (!cursor || !follower || document.body.classList.contains('touch-device')) return;

    let mouseX = 0, mouseY = 0;
    let cursorX = 0, cursorY = 0;
    let followerX = 0, followerY = 0;

    // Track mouse position
    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
    });

    // Animate cursor
    function animateCursor() {
        // Main cursor - fast follow
        cursorX += (mouseX - cursorX) * 0.2;
        cursorY += (mouseY - cursorY) * 0.2;
        cursor.style.left = cursorX + 'px';
        cursor.style.top = cursorY + 'px';

        // Follower - slower, smooth follow
        followerX += (mouseX - followerX) * 0.08;
        followerY += (mouseY - followerY) * 0.08;
        follower.style.left = followerX + 'px';
        follower.style.top = followerY + 'px';

        requestAnimationFrame(animateCursor);
    }
    animateCursor();

    // Hover effects on interactive elements
    const interactiveElements = document.querySelectorAll('a, button, input, textarea, [role="button"], .feature-card, .team-card, .menu-category, .mosaic-item');

    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursor.classList.add('hover');
            follower.classList.add('hover');
        });

        el.addEventListener('mouseleave', () => {
            cursor.classList.remove('hover');
            follower.classList.remove('hover');
        });
    });

    // Click effects
    document.addEventListener('mousedown', () => {
        cursor.classList.add('clicking');
        follower.classList.add('clicking');
    });

    document.addEventListener('mouseup', () => {
        cursor.classList.remove('clicking');
        follower.classList.remove('clicking');
    });

    // Hide cursor when leaving window
    document.addEventListener('mouseleave', () => {
        cursor.style.opacity = '0';
        follower.style.opacity = '0';
    });

    document.addEventListener('mouseenter', () => {
        cursor.style.opacity = '1';
        follower.style.opacity = '0.6';
    });
}

/* ----------------------------------------
   Navigation
   ---------------------------------------- */
function initNavigation() {
    const nav = document.getElementById('mainNav');
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-menu a');

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 100) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
        });

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navToggle.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }
}

/* ----------------------------------------
   Scroll Progress Bar
   ---------------------------------------- */
function initScrollProgress() {
    const progressBar = document.getElementById('scrollProgress');

    if (!progressBar) return;

    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    });
}

/* ----------------------------------------
   Scroll Reveal Animations
   ---------------------------------------- */
function initScrollReveal() {
    const revealElements = document.querySelectorAll('.scroll-reveal');

    const revealOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = entry.target.dataset.delay || 0;
                setTimeout(() => {
                    entry.target.classList.add('revealed');
                }, delay);
                revealObserver.unobserve(entry.target);
            }
        });
    }, revealOptions);

    revealElements.forEach(el => revealObserver.observe(el));
}

/* ----------------------------------------
   Hero Animations
   ---------------------------------------- */
function initHeroAnimations() { }

function triggerHeroReveal() {
    const heroElements = document.querySelectorAll('.reveal-up, .reveal-fade');

    heroElements.forEach(el => {
        const delay = parseInt(el.dataset.delay) || 0;
        setTimeout(() => {
            el.classList.add('revealed');
        }, delay);
    });
}

/* ----------------------------------------
   Counter Animation
   ---------------------------------------- */
function initCounterAnimation() {
    const counters = document.querySelectorAll('.counter');

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));
}

function animateCounter(counter) {
    const target = parseInt(counter.dataset.target);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const updateCounter = () => {
        current += step;
        if (current < target) {
            counter.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
        } else {
            counter.textContent = target;
        }
    };
    updateCounter();
}

/* ----------------------------------------
   Parallax Effects
   ---------------------------------------- */
function initParallax() {
    const parallaxElements = document.querySelectorAll('.parallax-img, .parallax-bg');

    if (parallaxElements.length === 0) return;

    let ticking = false;

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateParallax(parallaxElements);
                ticking = false;
            });
            ticking = true;
        }
    });
}

function updateParallax(elements) {
    const scrollY = window.pageYOffset;

    elements.forEach(el => {
        const rect = el.getBoundingClientRect();
        const elementTop = rect.top + scrollY;
        const elementVisible = scrollY + window.innerHeight > elementTop && scrollY < elementTop + rect.height;

        if (elementVisible) {
            const speed = 0.3;
            const yPos = (scrollY - elementTop) * speed;
            el.style.transform = `translateY(${yPos}px)`;
        }
    });
}

/* ----------------------------------------
   Image Parallax on Scroll
   ---------------------------------------- */
function initImageParallax() {
    const parallaxImages = document.querySelectorAll('.visual-img, .feature-image img, .team-image img');

    if (parallaxImages.length === 0) return;

    window.addEventListener('scroll', () => {
        parallaxImages.forEach(img => {
            const rect = img.getBoundingClientRect();
            const windowHeight = window.innerHeight;

            if (rect.top < windowHeight && rect.bottom > 0) {
                const scrollPercent = (windowHeight - rect.top) / (windowHeight + rect.height);
                const translateY = (scrollPercent - 0.5) * 30;
                img.style.transform = `translateY(${translateY}px) scale(1.05)`;
            }
        });
    });
}

/* ----------------------------------------
   Tilt Effect for Cards
   ---------------------------------------- */
function initTiltEffect() {
    const cards = document.querySelectorAll('.feature-card, .menu-category');

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = (y - centerY) / 25;
            const rotateY = (centerX - x) / 25;

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

/* ----------------------------------------
   Smooth Scroll
   ---------------------------------------- */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href === '#') return;

            const target = document.querySelector(href);

            if (target) {
                e.preventDefault();
                const navHeight = document.querySelector('.nav').offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/* ----------------------------------------
   Video Background Optimization
   ---------------------------------------- */
function initVideoBackground() {
    const heroVideo = document.querySelector('.hero-video');

    if (heroVideo) {
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    heroVideo.play();
                } else {
                    heroVideo.pause();
                }
            });
        }, { threshold: 0.25 });

        videoObserver.observe(heroVideo);

        if (navigator.connection && navigator.connection.effectiveType === '2g') {
            heroVideo.style.display = 'none';
        }
    }

    // Video Lightbox functionality
    initVideoLightbox();
}

/* ----------------------------------------
   Video Lightbox
   ---------------------------------------- */
function initVideoLightbox() {
    const playButton = document.getElementById('playButton');
    const lightbox = document.getElementById('videoLightbox');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxVideo = document.getElementById('lightboxVideo');
    const previewVideo = document.getElementById('previewVideo');

    if (!playButton || !lightbox) return;

    // Start preview video on scroll into view
    if (previewVideo) {
        const previewObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    previewVideo.play();
                } else {
                    previewVideo.pause();
                }
            });
        }, { threshold: 0.3 });
        previewObserver.observe(previewVideo);
    }

    // Open lightbox
    playButton.addEventListener('click', () => {
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        if (lightboxVideo) {
            lightboxVideo.currentTime = 0;
            lightboxVideo.play();
        }
        if (previewVideo) {
            previewVideo.pause();
        }
    });

    // Close lightbox
    const closeLightbox = () => {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        if (lightboxVideo) {
            lightboxVideo.pause();
        }
    };

    if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
    }

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

document.addEventListener('DOMContentLoaded', initVideoBackground);

/* ----------------------------------------
   Magnetic Effect for Buttons
   ---------------------------------------- */
function initMagneticButtons() {
    const buttons = document.querySelectorAll('.btn-bold, .btn-cafee-insta');

    buttons.forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            btn.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
        });

        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
}

// Uncomment to enable magnetic buttons
// initMagneticButtons();
