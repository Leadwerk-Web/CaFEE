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
// State
// ============================================
let state = {
    mouseX: 0,
    mouseY: 0,
    trailX: 0,
    trailY: 0,
    currentPage: 1,
    totalPages: 6,
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
}

/**
 * Feenstaub an der Mausposition – unabhängig vom Custom Cursor (gleiche Logik auf allen Seiten mit #fairyDust).
 */
function maybeSpawnFairyParticlesAtPointer(e) {
    if (!dom.fairyDust) return;
    if (window.matchMedia('(hover: none)').matches) return;
    if (Math.random() < 0.5) {
        createFairyParticle(e.clientX, e.clientY);
        if (Math.random() < 0.4) {
            setTimeout(() => createFairyParticle(e.clientX, e.clientY), 50);
        }
        if (Math.random() < 0.25) {
            setTimeout(() => createFairyParticle(e.clientX, e.clientY), 100);
        }
    }
}

function initFairyDustFollowMouse() {
    if (!dom.fairyDust) return;
    if (window.matchMedia('(hover: none)').matches) return;
    if (window.__cafeeFairyPointerBound) return;
    window.__cafeeFairyPointerBound = true;
    document.addEventListener('mousemove', maybeSpawnFairyParticlesAtPointer);
}

function initThanksPlaneFairyTrail() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const flyer = document.querySelector('.thanks-paper-plane-flyer');
    if (!flyer || !dom.fairyDust) return;
    if (window.__cafeeThanksPlaneFairyRaf) return;
    window.__cafeeThanksPlaneFairyRaf = true;

    let lastTick = 0;
    function loop(t) {
        if (t - lastTick >= 72) {
            lastTick = t;
            const r = flyer.getBoundingClientRect();
            if (r.width > 0 && r.bottom > -80 && r.top < window.innerHeight + 80) {
                const x = r.left + r.width * 0.14 + (Math.random() - 0.5) * 18;
                const y = r.top + r.height * 0.48 + (Math.random() - 0.5) * 20;
                if (Math.random() < 0.46) {
                    createFairyParticle(x, y);
                    if (Math.random() < 0.38) {
                        setTimeout(() => createFairyParticle(x + (Math.random() - 0.5) * 22, y + (Math.random() - 0.5) * 22), 42);
                    }
                }
            }
        }
        requestAnimationFrame(loop);
    }
    requestAnimationFrame(loop);
}

function initAmbientFlyingFairy() {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const body = document.body;
    if (body.getAttribute('data-ambient-fairy') === 'off' || body.classList.contains('no-ambient-fairy')) {
        return;
    }
    if (!dom.fairyDust || window.getComputedStyle(dom.fairyDust).display === 'none') return;
    const fairyAttr = body.getAttribute('data-ambient-fairy');
    const homeEdgeMode =
        fairyAttr === 'home' || body.classList.contains('has-ambient-fairy-home');
    if (window.__cafeeAmbientFairyStarted) return;
    window.__cafeeAmbientFairyStarted = true;

    const MOBILE_HEADER_FAIRY_MAX_W = 900;

    function isMobileHeaderFairyMode() {
        return window.innerWidth <= MOBILE_HEADER_FAIRY_MAX_W;
    }

    function getHeaderFairyBounds() {
        const nav = dom.nav;
        if (nav) {
            const r = nav.getBoundingClientRect();
            const padX = 26;
            const padY = 10;
            return {
                minX: r.left + padX,
                maxX: r.right - padX,
                minY: r.top + padY,
                maxY: r.bottom - padY,
            };
        }
        const h = Math.min(130, window.innerHeight * 0.16);
        return {
            minX: window.innerWidth * 0.08,
            maxX: window.innerWidth * 0.92,
            minY: 10,
            maxY: h,
        };
    }

    function clampToHeaderBounds() {
        const b = getHeaderFairyBounds();
        let minX = b.minX;
        let maxX = b.maxX;
        let minY = b.minY;
        let maxY = b.maxY;
        if (maxX < minX + 16) {
            const c = (minX + maxX) / 2;
            minX = c - 14;
            maxX = c + 14;
        }
        if (maxY < minY + 12) {
            const c = (minY + maxY) / 2;
            minY = c - 8;
            maxY = c + 8;
        }
        x = Math.min(Math.max(x, minX), maxX);
        y = Math.min(Math.max(y, minY), maxY);
        targetX = Math.min(Math.max(targetX, minX), maxX);
        targetY = Math.min(Math.max(targetY, minY), maxY);
    }

    function pickTargetHeaderStrip() {
        const b = getHeaderFairyBounds();
        const wBand = b.maxX - b.minX;
        const hBand = b.maxY - b.minY;
        if (wBand < 20 || hBand < 16) {
            targetX = window.innerWidth * 0.5;
            targetY = Math.max(24, b.minY);
            return;
        }
        targetX = b.minX + Math.random() * wBand;
        targetY = b.minY + Math.random() * hBand;
    }

    const wrap = document.createElement('div');
    wrap.className = 'ambient-flying-fairy';
    if (homeEdgeMode && !isMobileHeaderFairyMode()) {
        wrap.classList.add('ambient-flying-fairy--home-edges');
    }
    if (isMobileHeaderFairyMode()) {
        wrap.classList.add('ambient-flying-fairy--mobile-header');
    }
    wrap.setAttribute('aria-hidden', 'true');

    const img = document.createElement('img');
    img.className = 'ambient-flying-fairy__img';
    img.alt = '';
    img.setAttribute('aria-hidden', 'true');
    const themeFairy = typeof window !== 'undefined' ? window.cafeeTheme : null;
    const fairySrc =
        themeFairy && themeFairy.fairySvgUrl
            ? themeFairy.fairySvgUrl
            : 'images/Fee CaFEE_favicon_ohne Dampf.svg';
    img.src = fairySrc;
    wrap.appendChild(img);
    document.body.appendChild(wrap);

    let x;
    let y;
    let targetX;
    let targetY;
    let nextTargetTime;
    let nextTeleportTime;
    let side;
    let teleportBusy = false;

    function randomXInBand(s) {
        const w = window.innerWidth;
        if (s === 'left') {
            return (0.032 + Math.random() * 0.1) * w;
        }
        return (0.868 + Math.random() * 0.1) * w;
    }

    function pickTargetY() {
        const my = window.innerHeight * 0.1;
        return my + Math.random() * Math.max(80, window.innerHeight - 2 * my);
    }

    function pickTargetFull() {
        const mx = window.innerWidth * 0.1;
        const my = window.innerHeight * 0.1;
        targetX = mx + Math.random() * Math.max(40, window.innerWidth - 2 * mx);
        targetY = my + Math.random() * Math.max(40, window.innerHeight - 2 * my);
    }

    function clampToViewport() {
        const mx = window.innerWidth * 0.08;
        const my = window.innerHeight * 0.08;
        const maxX = window.innerWidth - mx;
        const maxY = window.innerHeight - my;
        targetX = Math.min(Math.max(targetX, mx), maxX);
        targetY = Math.min(Math.max(targetY, my), maxY);
        x = Math.min(Math.max(x, mx), maxX);
        y = Math.min(Math.max(y, my), maxY);
    }

    function clampHomeResize() {
        const my = window.innerHeight * 0.08;
        const maxY = window.innerHeight - my;
        targetY = Math.min(Math.max(targetY, my), maxY);
        y = Math.min(Math.max(y, my), maxY);
        x = randomXInBand(side);
        targetX = randomXInBand(side);
    }

    function clampHomeToBands() {
        const w = window.innerWidth;
        const my = window.innerHeight * 0.08;
        const maxY = window.innerHeight - my;
        y = Math.min(Math.max(y, my), maxY);
        targetY = Math.min(Math.max(targetY, my), maxY);
        const leftLo = 0.025 * w;
        const leftHi = 0.142 * w;
        const rightLo = 0.858 * w;
        const rightHi = 0.975 * w;
        if (side === 'left') {
            x = Math.min(Math.max(x, leftLo), leftHi);
            targetX = Math.min(Math.max(targetX, leftLo), leftHi);
        } else {
            x = Math.min(Math.max(x, rightLo), rightHi);
            targetX = Math.min(Math.max(targetX, rightLo), rightHi);
        }
    }

    const fairyFleeFromPointer = window.matchMedia('(hover: hover)').matches;
    let fleePointerX = -1e6;
    let fleePointerY = -1e6;
    if (fairyFleeFromPointer) {
        window.addEventListener(
            'pointermove',
            (e) => {
                fleePointerX = e.clientX;
                fleePointerY = e.clientY;
            },
            { passive: true }
        );
    }

    function applyMouseFlee() {
        if (!fairyFleeFromPointer || teleportBusy) return;
        const dx = x - fleePointerX;
        const dy = y - fleePointerY;
        const dist = Math.hypot(dx, dy);
        const fleeRadius = 108;
        if (dist >= fleeRadius || dist < 0.5) return;
        const urgency = (fleeRadius - dist) / fleeRadius;
        const push = 6 * urgency * urgency;
        const ux = dx / dist;
        const uy = dy / dist;
        x += ux * push;
        y += uy * push;
        targetX += ux * push * 2.2;
        targetY += uy * push * 2.2;
        if (isMobileHeaderFairyMode()) {
            clampToHeaderBounds();
        } else if (homeEdgeMode) {
            clampHomeToBands();
        } else {
            clampToViewport();
        }
    }

    if (isMobileHeaderFairyMode()) {
        side = 'left';
        pickTargetHeaderStrip();
        x = targetX;
        y = targetY;
        pickTargetHeaderStrip();
        nextTargetTime = performance.now() + 800 + Math.random() * 1000;
        nextTeleportTime = Infinity;
    } else if (homeEdgeMode) {
        side = Math.random() < 0.5 ? 'left' : 'right';
        x = randomXInBand(side);
        y = pickTargetY();
        targetX = randomXInBand(side);
        targetY = pickTargetY();
        nextTargetTime = performance.now() + 1200 + Math.random() * 1600;
        nextTeleportTime = performance.now() + 5000 + Math.random() * 9000;
    } else {
        x = window.innerWidth * 0.28;
        y = window.innerHeight * 0.38;
        pickTargetFull();
        nextTargetTime = performance.now() + 1800 + Math.random() * 1800;
        nextTeleportTime = Infinity;
    }

    let lastDust = 0;
    const lerpK = 0.032;

    function doTeleport() {
        if (isMobileHeaderFairyMode()) return;
        if (teleportBusy) return;
        teleportBusy = true;
        const burst = 10;
        for (let i = 0; i < burst; i++) {
            createFairyParticle(x + (Math.random() - 0.5) * 36, y + (Math.random() - 0.5) * 36);
        }
        wrap.classList.add('ambient-flying-fairy--teleporting');

        setTimeout(() => {
            side = side === 'left' ? 'right' : 'left';
            x = randomXInBand(side);
            y = pickTargetY();
            targetX = randomXInBand(side);
            targetY = pickTargetY();
            for (let i = 0; i < burst; i++) {
                createFairyParticle(x + (Math.random() - 0.5) * 40, y + (Math.random() - 0.5) * 40);
            }
        }, 260);

        setTimeout(() => {
            wrap.classList.remove('ambient-flying-fairy--teleporting');
            teleportBusy = false;
            nextTeleportTime = performance.now() + 5500 + Math.random() * 9500;
        }, 540);
    }

    function loop(t) {
        const headerStrip = isMobileHeaderFairyMode();

        if (headerStrip) {
            if (t >= nextTargetTime) {
                pickTargetHeaderStrip();
                nextTargetTime = t + 900 + Math.random() * 1400;
            }
            x += (targetX - x) * lerpK;
            y += (targetY - y) * lerpK;
            clampToHeaderBounds();
        } else if (homeEdgeMode) {
            if (!teleportBusy && t >= nextTeleportTime) {
                doTeleport();
            }
            if (!teleportBusy) {
                if (t >= nextTargetTime) {
                    targetX = randomXInBand(side);
                    targetY = pickTargetY();
                    nextTargetTime = t + 1300 + Math.random() * 2200;
                }
                x += (targetX - x) * lerpK;
                y += (targetY - y) * lerpK;
            }
        } else {
            if (t >= nextTargetTime) {
                pickTargetFull();
                nextTargetTime = t + 2000 + Math.random() * 2000;
            }
            x += (targetX - x) * lerpK;
            y += (targetY - y) * lerpK;
        }

        applyMouseFlee();

        wrap.style.transform = `translate3d(${Math.round(x)}px, ${Math.round(y)}px, 0) translate(-50%, -50%)`;

        if (t - lastDust >= 78) {
            lastDust = t;
            if (Math.random() < 0.45) {
                createFairyParticle(x, y);
                if (Math.random() < 0.36) {
                    setTimeout(
                        () =>
                            createFairyParticle(
                                x + (Math.random() - 0.5) * 24,
                                y + (Math.random() - 0.5) * 24
                            ),
                        44
                    );
                }
            }
        }
        requestAnimationFrame(loop);
    }

    window.addEventListener(
        'resize',
        () => {
            if (window.innerWidth <= MOBILE_HEADER_FAIRY_MAX_W) {
                clampToHeaderBounds();
                wrap.classList.add('ambient-flying-fairy--mobile-header');
                wrap.classList.remove('ambient-flying-fairy--home-edges');
            } else {
                wrap.classList.remove('ambient-flying-fairy--mobile-header');
                if (homeEdgeMode) {
                    wrap.classList.add('ambient-flying-fairy--home-edges');
                    clampHomeResize();
                } else {
                    clampToViewport();
                }
            }
        },
        { passive: true }
    );

    requestAnimationFrame(loop);
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
// ============================================
// Menu Book (PageFlip Integration)
// ============================================
function initMenuBook() {
    if (!dom.openBookBtn || !dom.bookCover || !dom.bookPages) return;

    const openMenuModalBtn = document.getElementById('openMenuModalBtn');
    const menuBookModal = document.getElementById('menuBookModal');
    const closeMenuModalBtn = document.getElementById('closeMenuModalBtn');
    if (!openMenuModalBtn || !menuBookModal || !closeMenuModalBtn || !window.St?.PageFlip) return;

    const pageElements = Array.from(dom.bookPages.querySelectorAll('.book-page'));
    const modalParent = menuBookModal.parentElement;
    const inertSiblings = [
        ...Array.from(document.body.children).filter(node => node !== menuBookModal && !node.contains(menuBookModal)),
        ...(modalParent ? Array.from(modalParent.children).filter(node => node !== menuBookModal) : [])
    ].filter((node, index, collection) => collection.indexOf(node) === index);
    const focusableSelector = [
        'a[href]',
        'button:not([disabled])',
        'textarea:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(', ');

    let lastActiveElement = null;
    let lockedScrollY = 0;
    let resizeFrame = null;

    const bookPageWidth = 720;
    const desktopBookPageHeight = 980;
    const tabletBookPageHeight = 1180;
    const mobileBookPageHeight = 1280;
    let pageFlip = null;
    let bookPageLayout = 'default';

    const turnSound = new Audio('page-turn.mp3');
    turnSound.volume = 0.5;
    turnSound.preload = 'auto';

    state.totalPages = pageElements.length;
    if (dom.totalPagesEl) {
        dom.totalPagesEl.textContent = String(state.totalPages);
    }

    function getBookPageLayout() {
        const viewportWidth = window.visualViewport?.width ?? window.innerWidth;
        const isCoarsePointer = window.matchMedia('(pointer: coarse)').matches;

        if (viewportWidth <= 767.98) {
            return 'mobile';
        }

        if (isCoarsePointer && viewportWidth <= 1366) {
            return 'tablet';
        }

        return 'default';
    }

    function buildPageFlip(startPage = 0) {
        const pageLayout = getBookPageLayout();
        const bookPageHeight = pageLayout === 'mobile'
            ? mobileBookPageHeight
            : pageLayout === 'tablet'
                ? tabletBookPageHeight
                : desktopBookPageHeight;
        const safeStartPage = Math.max(0, Math.min(startPage, pageElements.length - 1));

        pageElements.forEach(pageElement => {
            pageElement.style.width = `${bookPageWidth}px`;
            pageElement.style.height = `${bookPageHeight}px`;
        });

        dom.bookPages.dataset.bookLayout = pageLayout;
        dom.bookPages.replaceChildren(...pageElements);

        pageFlip = new St.PageFlip(dom.bookPages, {
            width: bookPageWidth,
            height: bookPageHeight,
            size: 'stretch',
            minWidth: 260,
            maxWidth: bookPageWidth,
            minHeight: pageLayout === 'default' ? 380 : 420,
            maxHeight: bookPageHeight,
            autoSize: false,
            showCover: false,
            mobileScrollSupport: true,
            maxShadowOpacity: 0.35,
            usePortrait: true,
            startPage: safeStartPage
        });

        pageFlip.loadFromHTML(pageElements);
        pageFlip.on('flip', () => {
            turnSound.currentTime = 0;

            const playPromise = turnSound.play();
            if (playPromise !== undefined) {
                playPromise.catch(err => {
                    console.log('Audio play blocked', err);
                });
            }

            updateNavigation();
        });

        bookPageLayout = pageLayout;
    }

    function refreshPageFlipLayoutIfNeeded() {
        const nextPageLayout = getBookPageLayout();

        if (!pageFlip || nextPageLayout !== bookPageLayout) {
            const currentPageIndex = pageFlip ? pageFlip.getCurrentPageIndex() : 0;

            if (pageFlip) {
                pageFlip.destroy();
            }

            buildPageFlip(currentPageIndex);
        }
    }

    function updateNavigation() {
        const currentIdx = pageFlip.getCurrentPageIndex();
        const totalPages = pageFlip.getPageCount();

        state.currentPage = currentIdx + 1;

        if (dom.currentPageEl) {
            dom.currentPageEl.textContent = String(state.currentPage);
        }

        if (dom.prevPage) {
            dom.prevPage.disabled = currentIdx === 0;
        }

        if (dom.nextPage) {
            dom.nextPage.disabled = currentIdx >= totalPages - 1;
        }
    }

    function getFocusableElements() {
        return Array.from(menuBookModal.querySelectorAll(focusableSelector)).filter(element => {
            const style = window.getComputedStyle(element);
            return element.getClientRects().length > 0 &&
                style.visibility !== 'hidden' &&
                !element.hasAttribute('disabled') && element.getAttribute('aria-hidden') !== 'true';
        });
    }

    function setSiblingsInert(shouldInert) {
        inertSiblings.forEach(node => {
            if (shouldInert) {
                const previousAriaHidden = node.getAttribute('aria-hidden');
                if (!node.dataset.modalPrevAriaHidden) {
                    node.dataset.modalPrevAriaHidden = previousAriaHidden ?? '__unset__';
                }

                node.setAttribute('inert', '');
                node.setAttribute('aria-hidden', 'true');
                return;
            }

            node.removeAttribute('inert');

            if (node.dataset.modalPrevAriaHidden === '__unset__') {
                node.removeAttribute('aria-hidden');
            } else if (node.dataset.modalPrevAriaHidden) {
                node.setAttribute('aria-hidden', node.dataset.modalPrevAriaHidden);
            }

            delete node.dataset.modalPrevAriaHidden;
        });
    }

    function lockBackgroundScroll() {
        lockedScrollY = window.scrollY;
        const scrollbarCompensation = Math.max(0, window.innerWidth - document.documentElement.clientWidth);

        document.documentElement.style.setProperty('--scrollbar-compensation', `${scrollbarCompensation}px`);
        document.body.style.top = `-${lockedScrollY}px`;
        document.body.classList.add('modal-open');
    }

    function unlockBackgroundScroll() {
        document.body.classList.remove('modal-open');
        document.body.style.top = '';
        document.documentElement.style.removeProperty('--scrollbar-compensation');
        window.scrollTo(0, lockedScrollY);
    }

    function syncBookA11y() {
        const isOpen = state.isBookOpen;

        dom.bookCover.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
        dom.bookPages.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        dom.openBookBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        if (dom.bookNav) {
            dom.bookNav.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        }
    }

    function scheduleBookUpdate() {
        if (!menuBookModal.classList.contains('active')) return;

        if (resizeFrame) {
            window.cancelAnimationFrame(resizeFrame);
        }

        resizeFrame = window.requestAnimationFrame(() => {
            refreshPageFlipLayoutIfNeeded();
            pageFlip.update();
            updateNavigation();
        });
    }

    function resetBookState() {
        state.isBookOpen = false;
        dom.bookCover.classList.remove('hidden');
        dom.bookPages.classList.remove('active');

        if (dom.bookNav) {
            dom.bookNav.classList.remove('active');
        }

        syncBookA11y();
    }

    function openMenuModal() {
        lastActiveElement = document.activeElement instanceof HTMLElement ? document.activeElement : openMenuModalBtn;
        menuBookModal.classList.add('active');
        menuBookModal.setAttribute('aria-hidden', 'false');

        lockBackgroundScroll();
        setSiblingsInert(true);
        refreshPageFlipLayoutIfNeeded();
        syncBookA11y();
        closeMenuModalBtn.focus({ preventScroll: true });

        window.requestAnimationFrame(() => {
            closeMenuModalBtn.focus({ preventScroll: true });
            window.setTimeout(() => {
                pageFlip.update();
                updateNavigation();
            }, 120);
        });
    }

    function closeMenuModal() {
        if (!menuBookModal.classList.contains('active')) return;

        resetBookState();
        menuBookModal.classList.remove('active');
        menuBookModal.setAttribute('aria-hidden', 'true');

        unlockBackgroundScroll();
        setSiblingsInert(false);

        if (lastActiveElement && document.contains(lastActiveElement)) {
            lastActiveElement.focus();
        }
    }

    function openBook() {
        state.isBookOpen = true;
        refreshPageFlipLayoutIfNeeded();
        dom.bookCover.classList.add('hidden');
        dom.bookPages.classList.add('active');

        if (dom.bookNav) {
            dom.bookNav.classList.add('active');
        }

        syncBookA11y();

        window.setTimeout(() => {
            pageFlip.update();
            updateNavigation();
            closeMenuModalBtn.focus({ preventScroll: true });
        }, 160);
    }

    function handleKeydown(event) {
        if (!menuBookModal.classList.contains('active')) return;

        if (event.key === 'Escape') {
            event.preventDefault();
            closeMenuModal();
            return;
        }

        if (event.key === 'Tab') {
            const focusableElements = getFocusableElements();

            if (!focusableElements.length) {
                event.preventDefault();
                closeMenuModalBtn.focus();
                return;
            }

            const activeIndex = focusableElements.indexOf(document.activeElement);
            const direction = event.shiftKey ? -1 : 1;
            const nextIndex = activeIndex === -1
                ? (event.shiftKey ? focusableElements.length - 1 : 0)
                : (activeIndex + direction + focusableElements.length) % focusableElements.length;

            event.preventDefault();
            focusableElements[nextIndex].focus();
            return;
        }

        if (!state.isBookOpen) return;

        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            pageFlip.flipPrev();
        }

        if (event.key === 'ArrowRight') {
            event.preventDefault();
            pageFlip.flipNext();
        }
    }

    openMenuModalBtn.addEventListener('click', openMenuModal);
    closeMenuModalBtn.addEventListener('click', closeMenuModal);
    dom.openBookBtn.addEventListener('click', openBook);

    if (dom.prevPage) {
        dom.prevPage.addEventListener('click', () => pageFlip.flipPrev());
    }

    if (dom.nextPage) {
        dom.nextPage.addEventListener('click', () => pageFlip.flipNext());
    }

    menuBookModal.addEventListener('click', event => {
        if (event.target === menuBookModal) {
            closeMenuModal();
        }
    });

    document.addEventListener('keydown', handleKeydown);
    window.addEventListener('resize', scheduleBookUpdate, { passive: true });

    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', scheduleBookUpdate);
    }

    if (window.ResizeObserver) {
        const bookStage = menuBookModal.querySelector('.menu-book-stage');
        const resizeObserver = new ResizeObserver(() => scheduleBookUpdate());

        if (bookStage) {
            resizeObserver.observe(bookStage);
        }
    }

    buildPageFlip(0);
    resetBookState();
    updateNavigation();
}

// Helper functions openBook/closeBook/changePage/showPage are removed as they are integrated above or unused.

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
        // Team-Fotos nicht per Opacity einblenden – sonst wirken sie grisselig
        if (img.closest('.team .card-image')) {
            img.style.opacity = '1';
            return;
        }
        img.style.opacity = '0';
        img.style.transition = 'opacity 0.5s ease';
        imageObserver.observe(img);

        if (img.complete) {
            img.style.opacity = '1';
        }
    });
}

// ============================================
// Touch Swipe for Menu Book
// ============================================
// initTouchSwipe handled by PageFlip

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
    initFairyDustFollowMouse();
    initThanksPlaneFairyTrail();
    initAmbientFlyingFairy();
    initCursor();
    initNavigation();
    initMenuBook();
    initParallax();
    initScrollAnimations();
    initAmbientDust();
    initLazyLoading();
    initLazyLoading();
    // initTouchSwipe(); // Handled by PageFlip
    initActiveNavHighlight();
    initVideoLightbox();
    initInterviewSlider();
    initInterviewLightbox();
    initContactFormRedirect();
    initOpenTableOverlayFallback();

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

    function getStoryVideoSrc() {
        const storySource = document.querySelector('#story .story-visual video source');
        if (!storySource) {
            return '';
        }
        return storySource.src || storySource.getAttribute('src') || '';
    }

    function openLightbox() {
        const srcUrl = getStoryVideoSrc();
        const lbSource = video.querySelector('source');
        if (lbSource && srcUrl) {
            const next = new URL(srcUrl, document.baseURI).href;
            const cur = lbSource.src || '';
            if (!cur || cur !== next) {
                lbSource.src = srcUrl;
                video.load();
            }
        }

        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        const p = video.play();
        if (p !== undefined) {
            p.catch(() => {});
        }
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');
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

// ============================================
// OpenTable Overlay Fallback
// ============================================
function initOpenTableOverlayFallback() {
    const fallbackModal = document.getElementById('otFallbackModal');
    const fallbackFrame = document.getElementById('otFallbackFrame');
    const fallbackClose = document.getElementById('otFallbackClose');

    if (!fallbackModal || !fallbackFrame) return;

    const nativeWindowOpen = window.open.bind(window);
    const otAvailabilityPattern = /^https:\/\/www\.opentable\.[^/]+\/booking\/restref\/availability\?/i;

    const closeFallback = () => {
        fallbackModal.classList.remove('active');
        document.body.classList.remove('ot-overlay-open');

        // Keep the current content visible during fade-out, then release iframe.
        setTimeout(() => {
            if (!fallbackModal.classList.contains('active')) {
                fallbackFrame.removeAttribute('src');
            }
        }, 200);
    };

    const openFallback = (url) => {
        fallbackFrame.setAttribute('src', url);
        fallbackModal.classList.add('active');
        document.body.classList.add('ot-overlay-open');
    };

    if (!window.__otFallbackOpenPatched) {
        window.open = function patchedWindowOpen(url, target, features) {
            if (typeof url === 'string' && otAvailabilityPattern.test(url)) {
                openFallback(url);
                return window;
            }
            return nativeWindowOpen(url, target, features);
        };
        window.__otFallbackOpenPatched = true;
    }

    if (fallbackClose) {
        fallbackClose.addEventListener('click', closeFallback);
    }

    fallbackModal.addEventListener('click', (e) => {
        if (e.target === fallbackModal) {
            closeFallback();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && fallbackModal.classList.contains('active')) {
            closeFallback();
        }
    });
}

// Start initialization
init();
