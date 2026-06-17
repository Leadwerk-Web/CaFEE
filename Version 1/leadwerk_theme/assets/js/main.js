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
    navBackdrop: document.getElementById('navBackdrop'),
    bookCover: document.getElementById('bookCover'),
    bookPages: document.getElementById('bookPagesContainer'),
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
// Cached Media Queries
// ============================================
const mobileNavQuery = window.matchMedia('(max-width: 900px)');
const mobileQuery = window.matchMedia('(max-width: 767.98px)');
let _isMobile = mobileQuery.matches;
mobileQuery.addEventListener('change', e => { _isMobile = e.matches; });

/** Schmale Viewports: weniger scroll-/CPU-Last (Parallax, RAF, Dust aus). */
function isMobileScrollLite() { return _isMobile; }

// ============================================
// Helpers
// ============================================
function syncMobileNavA11y() {
    if (!dom.navToggle || !dom.navMenu) return;

    const isOpen = dom.navMenu.classList.contains('active');
    const openLabel = dom.navToggle.dataset.labelOpen || 'Navigation öffnen';
    const closeLabel = dom.navToggle.dataset.labelClose || 'Navigation schließen';

    dom.navToggle.setAttribute('aria-controls', dom.navMenu.id || 'navMenu');
    dom.navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    dom.navToggle.setAttribute('aria-label', isOpen ? closeLabel : openLabel);
    dom.navMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

    if (dom.navBackdrop) {
        dom.navBackdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }
}

function setMobileNavOpen(isOpen) {
    if (!dom.navToggle || !dom.navMenu) return;

    dom.navToggle.classList.toggle('active', isOpen);
    dom.navMenu.classList.toggle('active', isOpen);
    document.body.classList.toggle('mobile-nav-open', isOpen);

    if (dom.navBackdrop) {
        dom.navBackdrop.classList.toggle('active', isOpen);
    }

    syncMobileNavA11y();
}

function closeMobileNavIfOpen() {
    if (!dom.navToggle || !dom.navMenu) return;
    setMobileNavOpen(false);
}

function promoteModalToBody(modalEl) {
    if (!modalEl || modalEl.parentElement === document.body) return;
    document.body.appendChild(modalEl);
}

function syncVideoLightboxBodyState() {
    document.body.classList.toggle(
        'video-lightbox-open',
        Boolean(document.querySelector('.video-lightbox.active'))
    );
}

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
// Unified Particle Creator
// ============================================
function createParticle(x, y, { spread = 50, minSize = 3, maxSize = 6, opacity, duration, lifetime } = {}) {
    if (!dom.fairyDust) return;

    const particle = document.createElement('div');
    particle.className = 'fairy-particle';

    const offsetX = (Math.random() - 0.5) * spread;
    const offsetY = (Math.random() - 0.5) * spread;
    const size = Math.random() * (maxSize - minSize) + minSize;

    particle.style.cssText =
        `left:${x + offsetX}px;top:${y + offsetY}px;` +
        `width:${size}px;height:${size}px;` +
        (opacity != null ? `opacity:${opacity};` : '') +
        `animation-duration:${duration || (1.5 + Math.random() * 1.5)}s`;

    dom.fairyDust.appendChild(particle);
    setTimeout(() => particle.remove(), lifetime || 3000);
}

// ============================================
// Custom Cursor
// ============================================
function initCursor() {
    if (!dom.cursorDot || !dom.cursorTrail) return;
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

    // Hover effects for interactive elements
    document.querySelectorAll('a, button, input, textarea').forEach(el => {
        el.addEventListener('mouseenter', () => {
            dom.cursorDot.classList.add('active');
            dom.cursorTrail.classList.add('active');
        });
        el.addEventListener('mouseleave', () => {
            dom.cursorDot.classList.remove('active');
            dom.cursorTrail.classList.remove('active');
        });
    });

    animateCursor();
}

function handleMouseMove(e) {
    state.mouseX = e.clientX;
    state.mouseY = e.clientY;

    // Single random roll decides particle count (0-3) for sparkle effect
    const roll = Math.random();
    if (roll < 0.5) {
        createParticle(e.clientX, e.clientY);
        if (roll < 0.2) setTimeout(() => createParticle(e.clientX, e.clientY), 50);
        if (roll < 0.125) setTimeout(() => createParticle(e.clientX, e.clientY), 100);
    }
}

function animateCursor() {
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
// Navigation
// ============================================
function initNavigation() {
    if (!dom.nav) return;

    // Mobile toggle
    if (dom.navToggle && dom.navMenu) {
        syncMobileNavA11y();

        dom.navToggle.addEventListener('click', () => {
            setMobileNavOpen(!dom.navMenu.classList.contains('active'));
        });

        // Close menu on link click
        dom.navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeMobileNavIfOpen);
        });

        if (dom.navBackdrop) {
            dom.navBackdrop.addEventListener('click', closeMobileNavIfOpen);
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeMobileNavIfOpen();
            }
        });

        mobileNavQuery.addEventListener('change', e => {
            if (!e.matches) {
                closeMobileNavIfOpen();
            }
        });
    }

    // Smooth scroll via event delegation (single listener instead of per-anchor)
    document.addEventListener('click', e => {
        const anchor = e.target.closest('a[href^="#"]');
        if (!anchor) return;

        e.preventDefault();
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
            const offsetPosition = target.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
        }
    });
}

// ============================================
// Menu Book (PageFlip Restored)
// ============================================
function initMenuBook() {
    if (!dom.openBookBtn || !dom.bookCover || !dom.bookPages) return;
    if (!window.St?.PageFlip) return;

    const openMenuModalBtn = document.getElementById('openMenuModalBtn');
    const closeMenuModalBtn = document.getElementById('closeMenuModalBtn');
    const menuBookModal = document.getElementById('menuBookModal');
    if (!openMenuModalBtn || !closeMenuModalBtn || !menuBookModal) return;

    promoteModalToBody(menuBookModal);

    const pageElements = Array.from(dom.bookPages.querySelectorAll('.book-page'));
    if (!pageElements.length) return;

    state.totalPages = pageElements.length;
    if (dom.totalPagesEl) dom.totalPagesEl.textContent = String(state.totalPages);

    let pageFlip = null;

    // --- Page Turn Audio ---
    const turnSoundUrl = window.cafeeTheme?.pageTurnSoundUrl || '';
    const turnSound = turnSoundUrl ? new Audio(turnSoundUrl) : null;
    if (turnSound) { turnSound.volume = 0.5; turnSound.preload = 'auto'; }

    let pageTurnAudioContext = null;

    function getAudioCtx() {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        if (!pageTurnAudioContext) pageTurnAudioContext = new Ctx();
        return pageTurnAudioContext;
    }

    function resumeAudioCtx() {
        const ctx = getAudioCtx();
        return (ctx?.state === 'suspended') ? ctx.resume().catch(() => {}) : Promise.resolve();
    }

    function playProceduralTurnSound() {
        const ctx = getAudioCtx();
        if (!ctx) return;

        const go = () => {
            const dur = 0.11, rate = ctx.sampleRate;
            const n = Math.max(1, Math.floor(rate * dur));
            const buf = ctx.createBuffer(1, n, rate);
            const data = buf.getChannelData(0);
            for (let i = 0; i < n; i++) {
                const t = i / n;
                data[i] = (Math.random() * 2 - 1) * Math.sin(Math.PI * t) * Math.pow(1 - t, 0.35) * 0.4;
            }
            const src = ctx.createBufferSource();
            src.buffer = buf;
            const bp = ctx.createBiquadFilter();
            bp.type = 'bandpass'; bp.frequency.value = 2200; bp.Q.value = 0.65;
            const gain = ctx.createGain();
            const t0 = ctx.currentTime;
            gain.gain.setValueAtTime(0.0001, t0);
            gain.gain.exponentialRampToValueAtTime(0.36, t0 + 0.014);
            gain.gain.exponentialRampToValueAtTime(0.0001, t0 + dur);
            src.connect(bp).connect(gain).connect(ctx.destination);
            src.start(t0);
            src.stop(t0 + dur + 0.025);
        };

        ctx.state === 'suspended' ? ctx.resume().then(go).catch(() => {}) : go();
    }

    function unlockPageTurnAudio() {
        resumeAudioCtx();
        if (!turnSound) return;
        const vol = turnSound.volume;
        turnSound.volume = 0;
        const p = turnSound.play();
        if (p !== undefined) {
            p.then(() => { turnSound.pause(); turnSound.currentTime = 0; turnSound.volume = vol; })
             .catch(() => { turnSound.volume = vol; });
        } else {
            turnSound.volume = vol;
        }
    }

    function playPageTurnSound() {
        if (turnSound) {
            turnSound.currentTime = 0;
            turnSound.volume = 0.5;
            const p = turnSound.play();
            if (p !== undefined) p.catch(() => playProceduralTurnSound());
        } else {
            playProceduralTurnSound();
        }
    }

    function updateNavigation() {
        if (!pageFlip) return;
        const idx = pageFlip.getCurrentPageIndex();
        const total = pageFlip.getPageCount();
        state.currentPage = idx + 1;
        if (dom.currentPageEl) dom.currentPageEl.textContent = String(state.currentPage);
        if (dom.prevPage) dom.prevPage.disabled = idx === 0;
        if (dom.nextPage) dom.nextPage.disabled = idx >= total - 1;
    }

    function openBook() {
        closeMobileNavIfOpen();
        unlockPageTurnAudio();
        state.isBookOpen = true;
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';

        dom.bookCover.classList.add('hidden');
        dom.bookPages.classList.add('active');
        if (dom.bookNav) dom.bookNav.classList.add('active');

        menuBookModal.classList.add('active');
        menuBookModal.setAttribute('aria-hidden', 'false');



        // Sadece menü açıkken PageFlip ayağa kalksın (CSS'den stretch alacak şekilde)
        if (!pageFlip) {
            const disableFlipShadow = mobileQuery.matches;
            // "menüyü resize eden js kodunu ekleme css ile çözüm bul" -> Fixed logical resolution, scale stretches to CSS.
            pageFlip = new St.PageFlip(dom.bookPages, {
                width: 720,
                height: 980,
                size: 'stretch',
                minWidth: 260,
                maxWidth: 1440,
                minHeight: 400,
                maxHeight: 1400,
                drawShadow: !disableFlipShadow,
                showCover: false,
                usePortrait: true,
                maxShadowOpacity: disableFlipShadow ? 0 : 0.35,
                mobileScrollSupport: true
            });
            pageFlip.loadFromHTML(pageElements);
            pageFlip.on('flip', () => { 
                playPageTurnSound(); 
                updateNavigation(); 
            });
            updateNavigation();
        }
    }

    function closeBook() {
        state.isBookOpen = false;
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        dom.bookCover.classList.remove('hidden');
        dom.bookPages.classList.remove('active');
        if (dom.bookNav) dom.bookNav.classList.remove('active');

        menuBookModal.classList.remove('active');
        menuBookModal.setAttribute('aria-hidden', 'true');
    }

    // Modal Events
    openMenuModalBtn.addEventListener('click', openBook);
    dom.openBookBtn.addEventListener('click', openBook);

    closeMenuModalBtn.addEventListener('click', closeBook);
    menuBookModal.addEventListener('click', e => { if (e.target === menuBookModal) closeBook(); });

    if (dom.prevPage) dom.prevPage.addEventListener('click', () => { if (pageFlip) pageFlip.flipPrev(); });
    if (dom.nextPage) dom.nextPage.addEventListener('click', () => { if (pageFlip) pageFlip.flipNext(); });

    document.addEventListener('keydown', (e) => {
        if (!state.isBookOpen) return;
        if (e.key === 'ArrowLeft' && pageFlip) pageFlip.flipPrev();
        if (e.key === 'ArrowRight' && pageFlip) pageFlip.flipNext();
        if (e.key === 'Escape') closeBook();
    });

}

// ============================================
// Unified Scroll Handler (Parallax + Nav Highlight)
// ============================================
let _scrollTicking = false;
// Cache section queries once
let _heroSection = null;
let _expSection = null;
let _parallaxElements = null;
let _navSections = null;
let _navLinks = null;

function initScrollHandler() {
    // Cache DOM lookups
    _heroSection = document.querySelector('.hero');
    _expSection = document.querySelector('.experience');
    _parallaxElements = document.querySelectorAll('.parallax-element');
    _navSections = document.querySelectorAll('section[id]');
    _navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');

    window.addEventListener('scroll', onScroll, { passive: true });
    // Run once to set initial state
    processScroll();
}

function onScroll() {
    if (_scrollTicking) return;
    _scrollTicking = true;
    requestAnimationFrame(() => {
        processScroll();
        _scrollTicking = false;
    });
}

function processScroll() {
    const scrollY = window.scrollY;
    const vh = window.innerHeight;
    const forceScrolledNav = dom.nav && (dom.nav.hasAttribute('data-force-scrolled-header') || document.body.classList.contains('header-scrolled') || document.body.classList.contains('page-404') || document.body.classList.contains('error404'));

    // --- Nav scroll class ---
    if (dom.nav) dom.nav.classList.toggle('scrolled', forceScrolledNav || scrollY > 50);

    // --- Parallax (skip on mobile) ---
    if (!isMobileScrollLite()) {
        // Hero parallax
        if (dom.heroParallax && _heroSection) {
            const rect = _heroSection.getBoundingClientRect();
            if (rect.bottom > 0) {
                dom.heroParallax.style.transform = `scale(1.1) translateY(${scrollY * 0.3}px)`;
            }
        }

        // Experience background parallax
        if (dom.experienceBgParallax && _expSection) {
            const rect = _expSection.getBoundingClientRect();
            if (rect.top < vh && rect.bottom > 0) {
                const progress = (vh - rect.top) / (vh + rect.height);
                dom.experienceBgParallax.style.transform = `scale(1.1) translateY(${progress * 50 - 25}px)`;
            }
        }

        // Element parallax
        _parallaxElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < vh && rect.bottom > 0) {
                const speed = parseFloat(el.dataset.speed) || 0.1;
                el.style.transform = `translateY(${(vh - rect.top) * speed}px)`;
            }
        });
    }

    // --- Active Nav Highlight ---
    let currentSection = '';
    _navSections.forEach(section => {
        const top = section.offsetTop - 100;
        if (scrollY >= top && scrollY < top + section.offsetHeight) {
            currentSection = section.id;
        }
    });

    _navLinks.forEach(link => {
        link.classList.toggle('active', link.getAttribute('href') === `#${currentSection}`);
    });
}

// ============================================
// Scroll Animations
// ============================================
function initScrollAnimations() {
    if (isMobileScrollLite()) return;

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = parseInt(entry.target.dataset.delay) || 0;
                setTimeout(() => entry.target.classList.add('visible'), delay);
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -100px 0px', threshold: 0.1 });

    document.querySelectorAll('.scroll-animate').forEach(el => observer.observe(el));
}

// ============================================
// Ambient Fairy Dust (Background)
// ============================================
function initAmbientDust() {
    setInterval(() => {
        if (Math.random() < 0.6) {
            createParticle(
                Math.random() * window.innerWidth,
                Math.random() * window.innerHeight,
                { spread: 0, minSize: 3, maxSize: 8, opacity: 0.7, duration: 2 + Math.random() * 2, lifetime: 4000 }
            );
        }
    }, 250);
}

// ============================================
// Image Lazy Loading Enhancement
// ============================================
function initLazyLoading() {
    const images = document.querySelectorAll('img');

    if (isMobileScrollLite()) {
        images.forEach(img => { img.style.opacity = '1'; });
        return;
    }

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    images.forEach(img => {
        // Team-Fotos nicht per Opacity einblenden – sonst wirken sie grisselig
        if (img.closest('.team .card-image')) {
            img.style.opacity = '1';
            return;
        }
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.5s ease';
        observer.observe(img);
        if (img.complete) img.style.opacity = '1';
    });
}

// ============================================
// Performance Optimization
// ============================================
function optimizePerformance() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const lowEnd = navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4;
    const mobile = isMobileScrollLite();

    // Disable fairy dust on low-end devices or reduced motion or mobile
    if ((lowEnd || reduceMotion || mobile) && dom.fairyDust) {
        dom.fairyDust.style.display = 'none';
    }

    // Force-show scroll-animate elements when animations should be reduced
    if (reduceMotion || mobile) {
        document.querySelectorAll('.scroll-animate').forEach(el => {
            el.style.transition = 'none';
            el.classList.add('visible');
        });
    }
}

// ============================================
// Common Lightbox Controller
// ============================================
function createLightboxController(lightboxEl, videoEl, closeBtn, { onOpen, onClose } = {}) {
    promoteModalToBody(lightboxEl);

    function open(src) {
        if (src !== undefined) {
            const sourceEl = videoEl.querySelector('source');
            if (sourceEl) { sourceEl.src = src; videoEl.load(); }
        }
        closeMobileNavIfOpen();
        lightboxEl.classList.add('active');
        syncVideoLightboxBodyState();
        document.body.style.overflow = 'hidden';
        if (onOpen) onOpen(videoEl);
        else videoEl.play();
    }

    function close() {
        lightboxEl.classList.remove('active');
        syncVideoLightboxBodyState();
        document.body.style.overflow = '';
        videoEl.pause();
        videoEl.currentTime = 0;
        if (onClose) onClose(videoEl);
    }

    closeBtn.addEventListener('click', close);
    lightboxEl.addEventListener('click', e => { if (e.target === lightboxEl) close(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && lightboxEl.classList.contains('active')) close();
    });

    return { open, close };
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

    const ctrl = createLightboxController(lightbox, video, closeBtn);
    openBtn.addEventListener('click', () => {
        const src = document.querySelector('.story-image source')?.src;
        if (src) ctrl.open(src);
        else ctrl.open();
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
    if (!slides.length) return;

    let current = 0;
    const total = slides.length;

    function showSlide(index) {
        current = ((index % total) + total) % total; // modular wrap-around

        slides.forEach(slide => {
            slide.classList.remove('active');
            const v = slide.querySelector('.interview-video');
            if (v) { v.pause(); v.currentTime = 0; }
        });
        dots.forEach(d => d.classList.remove('active'));

        slides[current].classList.add('active');
        if (dots[current]) dots[current].classList.add('active');

        const activeVideo = slides[current].querySelector('.interview-video');
        if (activeVideo) {
            activeVideo.muted = true;
            activeVideo.play().catch(() => {});
        }
    }

    if (prevBtn) prevBtn.addEventListener('click', () => showSlide(current - 1));
    if (nextBtn) nextBtn.addEventListener('click', () => showSlide(current + 1));
    dots.forEach(dot => dot.addEventListener('click', () => showSlide(parseInt(dot.dataset.slide))));

    // Touch swipe
    const container = document.getElementById('interviewSlides');
    if (container) {
        let startX = 0;
        container.addEventListener('touchstart', e => { startX = e.changedTouches[0].screenX; }, { passive: true });
        container.addEventListener('touchend', e => {
            const diff = startX - e.changedTouches[0].screenX;
            if (Math.abs(diff) > 50) showSlide(current + (diff > 0 ? 1 : -1));
        }, { passive: true });
    }

    // Auto-play first video
    const firstVideo = slides[0]?.querySelector('.interview-video');
    if (firstVideo) { firstVideo.muted = true; firstVideo.play().catch(() => {}); }
}

// ============================================
// Interview Lightbox
// ============================================
function initInterviewLightbox() {
    const lightbox = document.getElementById('interviewLightbox');
    const closeBtn = document.getElementById('closeInterviewBtn');
    const video = document.getElementById('interviewLightboxVideo');
    if (!lightbox || !closeBtn || !video) return;

    const ctrl = createLightboxController(lightbox, video, closeBtn, {
        onOpen(v) { v.muted = false; v.play(); }
    });

    // Click on interview video wrapper to open lightbox
    document.querySelectorAll('.interview-video-wrapper').forEach(wrapper => {
        wrapper.addEventListener('click', () => {
            const src = wrapper.querySelector('source')?.src;
            if (src) ctrl.open(src);
        });
    });

    // Round play button opens lightbox
    document.querySelectorAll('.interview-play-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            const source = btn.closest('.interview-slide')?.querySelector('.interview-video source');
            if (source?.src) ctrl.open(source.src);
        });
    });
}

// ============================================
// Initialize Everything
// ============================================
function init() {
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
    initScrollHandler(); // unified: parallax + nav highlight + nav scroll class
    initScrollAnimations();
    if (!isMobileScrollLite()) initAmbientDust();
    initLazyLoading();
    initVideoLightbox();
    initInterviewSlider();
    initInterviewLightbox();

    setTimeout(() => document.body.classList.add('loaded'), 100);
    console.log('✨ CaFEE Brückenmühle website initialized');
}

// Start initialization
init();
