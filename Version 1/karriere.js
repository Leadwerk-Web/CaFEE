/**
 * CaFEE Karriere Page – Standalone Interactive Script
 * Matches the patterns used in the main site's script.js
 */

/* ----------------------------------------
   DOM Cache & State
   ---------------------------------------- */
const dom = {};
let state = {
    scrollPosition: 0
};

document.addEventListener('DOMContentLoaded', () => {
    initAll();
});

function initAll() {
    cacheDOM();
    initNavigation();
    initScrollAnimations();
    initJobCards();
    initFAQ();
    initApplicationForm();
    initCounter();
}

function cacheDOM() {
    dom.body = document.body;
    dom.nav = document.getElementById('mainNav');
    dom.navToggle = document.getElementById('navToggle');
    dom.navMenu = document.getElementById('navMenu');
    dom.navLinks = dom.navMenu ? dom.navMenu.querySelectorAll('a') : [];

    dom.scrollAnimateElements = document.querySelectorAll('.scroll-animate');
    dom.jobCards = document.querySelectorAll('.job-card');
    dom.faqItems = document.querySelectorAll('.faq-item');

    dom.form = document.getElementById('applicationForm');
    dom.fileArea = document.getElementById('fileUploadArea');
    dom.fileInput = document.getElementById('fileInput');
    dom.fileContent = dom.fileArea?.querySelector('.file-upload-content');
    dom.fileSelected = document.getElementById('fileSelected');
    dom.fileName = document.getElementById('fileName');
    dom.fileRemove = document.getElementById('fileRemove');
    dom.formSuccess = document.getElementById('formSuccess');

    dom.counters = document.querySelectorAll('.counter');
}

/* ----------------------------------------
   Navigation
   ---------------------------------------- */
function initNavigation() {
    if (!dom.nav) return;

    // Force scrolled state on karriere page (always has solid header)
    dom.nav.classList.add('scrolled');

    // Mobile menu toggle
    dom.navToggle?.addEventListener('click', () => {
        const isActive = dom.navToggle.classList.contains('active');

        if (!isActive) {
            state.scrollPosition = window.scrollY;
            dom.navToggle.classList.add('active');
            dom.navMenu.classList.add('active');
            dom.body.style.overflow = 'hidden';
            dom.body.style.position = 'fixed';
            dom.body.style.top = `-${state.scrollPosition}px`;
            dom.body.style.width = '100%';
        } else {
            closeMobileMenu();
        }
    });

    // Close menu + smooth scroll on link click
    dom.navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            // Close mobile menu if open
            if (dom.navMenu?.classList.contains('active')) {
                closeMobileMenu();
            }

            const href = link.getAttribute('href');

            // External / cross-page links: navigate normally
            if (!href || href.startsWith('index.html') || href.startsWith('http')) {
                return;
            }

            // Internal anchor smooth scroll
            if (href.startsWith('#') && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const offset = 80;
                    const top = target.getBoundingClientRect().top + window.scrollY - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            }
        });
    });

    function closeMobileMenu() {
        dom.navToggle?.classList.remove('active');
        dom.navMenu?.classList.remove('active');
        dom.body.style.removeProperty('overflow');
        dom.body.style.removeProperty('position');
        dom.body.style.removeProperty('top');
        dom.body.style.removeProperty('width');
        window.scrollTo(0, state.scrollPosition);
    }
}

/* ----------------------------------------
   Scroll Animations
   ---------------------------------------- */
function initScrollAnimations() {
    if (!dom.scrollAnimateElements?.length) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isMobile = window.innerWidth <= 767.98;

    // On mobile or reduced-motion: show everything immediately
    if (prefersReducedMotion || isMobile) {
        dom.scrollAnimateElements.forEach(el => el.classList.add('visible'));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const delay = parseInt(el.getAttribute('data-delay'), 10) || 0;

                setTimeout(() => {
                    el.classList.add('visible');
                }, delay);

                obs.unobserve(el);
            }
        });
    }, {
        rootMargin: '0px 0px -100px 0px',
        threshold: 0.1
    });

    dom.scrollAnimateElements.forEach(el => observer.observe(el));
}

/* ----------------------------------------
   Job Cards (Accordion – one at a time)
   ---------------------------------------- */
function initJobCards() {
    if (!dom.jobCards?.length) return;

    dom.jobCards.forEach(card => {
        const header = card.querySelector('.job-card-header');
        const body = card.querySelector('.job-card-body');
        if (!header || !body) return;

        header.addEventListener('click', () => {
            const isExpanded = card.classList.contains('expanded');

            // Close all other cards
            dom.jobCards.forEach(c => {
                if (c !== card && c.classList.contains('expanded')) {
                    c.classList.remove('expanded');
                    const b = c.querySelector('.job-card-body');
                    if (b) b.style.maxHeight = null;
                }
            });

            if (isExpanded) {
                card.classList.remove('expanded');
                body.style.maxHeight = null;
            } else {
                card.classList.add('expanded');
                body.style.maxHeight = body.scrollHeight + 'px';
            }
        });
    });
}

/* ----------------------------------------
   FAQ Accordion (multiple can be open)
   ---------------------------------------- */
function initFAQ() {
    if (!dom.faqItems?.length) return;

    dom.faqItems.forEach(item => {
        const header = item.querySelector('.faq-header');
        const body = item.querySelector('.faq-body');
        if (!header || !body) return;

        header.addEventListener('click', () => {
            const isActive = item.classList.contains('active');

            if (isActive) {
                item.classList.remove('active');
                header.setAttribute('aria-expanded', 'false');
                body.style.maxHeight = null;
            } else {
                item.classList.add('active');
                header.setAttribute('aria-expanded', 'true');
                body.style.maxHeight = body.scrollHeight + 'px';
            }
        });
    });
}

/* ----------------------------------------
   Application Form & File Upload
   ---------------------------------------- */
function initApplicationForm() {
    if (!dom.form) return;

    // --- File Upload ---
    if (dom.fileArea && dom.fileInput) {
        // Click to open file picker
        dom.fileArea.addEventListener('click', (e) => {
            // Don't trigger if clicking the remove button
            if (e.target.closest('.file-remove')) return;
            dom.fileInput.click();
        });

        // Drag & drop events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(evt => {
            dom.fileArea.addEventListener(evt, (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ['dragenter', 'dragover'].forEach(evt => {
            dom.fileArea.addEventListener(evt, () => {
                dom.fileArea.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            dom.fileArea.addEventListener(evt, () => {
                dom.fileArea.classList.remove('dragover');
            });
        });

        dom.fileArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer?.files;
            if (files?.length > 0) {
                // Assign to file input so the form submission includes them
                dom.fileInput.files = files;
                handleFileSelection(files);
            }
        });

        dom.fileInput.addEventListener('change', () => {
            if (dom.fileInput.files?.length > 0) {
                handleFileSelection(dom.fileInput.files);
            }
        });

        // Remove file button
        dom.fileRemove?.addEventListener('click', (e) => {
            e.stopPropagation();
            clearFile();
        });
    }

    // --- Form Submit ---
    dom.form.addEventListener('submit', (e) => {
        e.preventDefault();

        // Validate required fields
        const required = dom.form.querySelectorAll('[required]');
        let isValid = true;

        required.forEach(field => {
            if (field.type === 'checkbox') {
                if (!field.checked) {
                    isValid = false;
                    field.closest('.checkbox-label')?.classList.add('error');
                } else {
                    field.closest('.checkbox-label')?.classList.remove('error');
                }
            } else if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });

        if (!isValid) return;

        // Show success message
        dom.form.style.display = 'none';
        if (dom.formSuccess) {
            dom.formSuccess.style.display = 'block';
        }

        // Reset form
        dom.form.reset();
        clearFile();
    });

    /* Helper functions */
    function handleFileSelection(files) {
        const maxSize = 10 * 1024 * 1024; // 10 MB per file
        const allowedExtensions = ['pdf', 'doc', 'docx'];
        
        let validFiles = 0;
        
        // Convert FileList to Array for iteration
        Array.from(files).forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
    
            if (file.size > maxSize) {
                alert(`Die Datei ${file.name} ist zu groß. Maximal 10 MB sind erlaubt.`);
                return;
            }
    
            if (!allowedExtensions.includes(ext)) {
                alert(`Ungültiges Dateiformat bei ${file.name}. Bitte lade eine PDF-, DOC- oder DOCX-Datei hoch.`);
                return;
            }
            validFiles++;
        });

        if (validFiles === 0) {
            clearFile();
            return;
        }

        // Show selected files
        if (dom.fileName) {
            if (files.length === 1) {
                dom.fileName.textContent = files[0].name;
            } else {
                dom.fileName.textContent = `${files.length} Dateien ausgewählt`;
            }
        }
        
        if (dom.fileContent) dom.fileContent.style.display = 'none';
        if (dom.fileSelected) dom.fileSelected.style.display = 'flex';
    }

    function clearFile() {
        if (dom.fileInput) dom.fileInput.value = '';
        if (dom.fileName) dom.fileName.textContent = '';
        if (dom.fileContent) dom.fileContent.style.display = 'flex';
        if (dom.fileSelected) dom.fileSelected.style.display = 'none';
    }
}

/* ----------------------------------------
   Counter Animation (scroll-triggered)
   ---------------------------------------- */
function initCounter() {
    if (!dom.counters?.length) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.getAttribute('data-target'), 10) || 0;
                animateCounter(el, target);
                obs.unobserve(el);
            }
        });
    }, { threshold: 0.3 });

    dom.counters.forEach(counter => observer.observe(counter));

    function animateCounter(el, target) {
        const duration = 2000;
        let start = null;

        const step = (timestamp) => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            // easeOutQuad
            const eased = progress * (2 - progress);
            el.textContent = Math.floor(eased * target);

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target;
            }
        };

        requestAnimationFrame(step);
    }
}
