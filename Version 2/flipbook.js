/* ========================================
   CaFEE Brückenmühle - Flipbook Viewer
   PDF-based page-flip menu viewer
   ======================================== */

(function () {
    'use strict';

    // State
    let pdfDoc = null;
    let currentSpread = 0; // which spread (pair of pages) we're viewing
    let totalPages = 0;
    let isAnimating = false;
    let isOpen = false;
    const RENDER_SCALE = 2; // High-res rendering

    // DOM Elements
    const openBtn = document.getElementById('openFlipbook');
    const lightbox = document.getElementById('flipbookLightbox');
    const closeBtn = document.getElementById('flipbookClose');
    const prevBtn = document.getElementById('flipbookPrev');
    const nextBtn = document.getElementById('flipbookNext');
    const canvasLeft = document.getElementById('flipbookCanvasLeft');
    const canvasRight = document.getElementById('flipbookCanvasRight');
    const canvasFront = document.getElementById('flipCanvasFront');
    const canvasBack = document.getElementById('flipCanvasBack');
    const flipOverlay = document.getElementById('flipOverlay');
    const pageCounter = document.getElementById('flipbookPageCounter');
    const pageLeftEl = document.getElementById('flipbookPageLeft');
    const pageRightEl = document.getElementById('flipbookPageRight');

    if (!openBtn || !lightbox) return;

    // ----------------------------------------
    // Load PDF
    // ----------------------------------------
    async function loadPDF() {
        if (pdfDoc) return; // Already loaded

        try {
            const loadingTask = pdfjsLib.getDocument('Speisekarte.pdf');
            pdfDoc = await loadingTask.promise;
            totalPages = pdfDoc.numPages;
            await renderSpread(0);
        } catch (err) {
            console.error('PDF konnte nicht geladen werden:', err);
        }
    }

    // ----------------------------------------
    // Render a single page to a canvas
    // ----------------------------------------
    async function renderPage(pageNum, canvas) {
        if (pageNum < 1 || pageNum > totalPages) {
            // Clear canvas for empty page
            const ctx = canvas.getContext('2d');
            canvas.width = 100;
            canvas.height = 141;
            ctx.fillStyle = '#faf8f5';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            return;
        }

        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: RENDER_SCALE });

        canvas.width = viewport.width;
        canvas.height = viewport.height;

        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        await page.render({
            canvasContext: ctx,
            viewport: viewport
        }).promise;
    }

    // ----------------------------------------
    // Render a spread (two facing pages)
    // ----------------------------------------
    async function renderSpread(spreadIndex) {
        const leftPage = spreadIndex * 2 + 1;
        const rightPage = spreadIndex * 2 + 2;

        await Promise.all([
            renderPage(leftPage, canvasLeft),
            renderPage(rightPage, canvasRight)
        ]);

        // Update counter
        const totalSpreads = Math.ceil(totalPages / 2);
        if (leftPage <= totalPages && rightPage <= totalPages) {
            pageCounter.textContent = `Seite ${leftPage}–${rightPage} von ${totalPages}`;
        } else if (leftPage <= totalPages) {
            pageCounter.textContent = `Seite ${leftPage} von ${totalPages}`;
        }

        // Update button states
        prevBtn.style.opacity = spreadIndex === 0 ? '0.3' : '1';
        prevBtn.style.pointerEvents = spreadIndex === 0 ? 'none' : 'auto';

        const maxSpread = Math.ceil(totalPages / 2) - 1;
        nextBtn.style.opacity = spreadIndex >= maxSpread ? '0.3' : '1';
        nextBtn.style.pointerEvents = spreadIndex >= maxSpread ? 'none' : 'auto';
    }

    // ----------------------------------------
    // Page Flip Animation - Forward
    // ----------------------------------------
    async function flipForward() {
        const maxSpread = Math.ceil(totalPages / 2) - 1;
        if (currentSpread >= maxSpread || isAnimating) return;

        isAnimating = true;
        const nextSpread = currentSpread + 1;

        // Prepare flip canvases: front shows current right page, back shows next left page
        const currentRightPage = currentSpread * 2 + 2;
        const nextLeftPage = nextSpread * 2 + 1;
        const nextRightPage = nextSpread * 2 + 2;

        await Promise.all([
            renderPage(currentRightPage, canvasFront),
            renderPage(nextLeftPage, canvasBack)
        ]);

        // Pre-render the next right page behind
        await renderPage(nextRightPage, canvasRight);

        // Show overlay and animate
        flipOverlay.classList.add('active', 'flip-forward');

        // Wait for animation to complete
        await new Promise(resolve => setTimeout(resolve, 600));

        // Update state
        currentSpread = nextSpread;
        await renderSpread(currentSpread);

        // Remove animation
        flipOverlay.classList.remove('active', 'flip-forward');
        isAnimating = false;
    }

    // ----------------------------------------
    // Page Flip Animation - Backward
    // ----------------------------------------
    async function flipBackward() {
        if (currentSpread <= 0 || isAnimating) return;

        isAnimating = true;
        const prevSpread = currentSpread - 1;

        // Prepare flip canvases: front shows next right page (the page flipping back), back shows current left
        const currentLeftPage = currentSpread * 2 + 1;
        const prevLeftPage = prevSpread * 2 + 1;
        const prevRightPage = prevSpread * 2 + 2;

        await Promise.all([
            renderPage(currentLeftPage, canvasBack),
            renderPage(prevRightPage, canvasFront)
        ]);

        // Pre-render prev left page behind
        await renderPage(prevLeftPage, canvasLeft);

        // Show overlay and animate
        flipOverlay.classList.add('active', 'flip-backward');

        // Wait for animation to complete
        await new Promise(resolve => setTimeout(resolve, 600));

        // Update state
        currentSpread = prevSpread;
        await renderSpread(currentSpread);

        // Remove animation
        flipOverlay.classList.remove('active', 'flip-backward');
        isAnimating = false;
    }

    // ----------------------------------------
    // Open / Close Lightbox
    // ----------------------------------------
    function openFlipbook() {
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        isOpen = true;
        loadPDF();
    }

    function closeFlipbook() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
        isOpen = false;
    }

    // ----------------------------------------
    // Event Listeners
    // ----------------------------------------
    openBtn.addEventListener('click', openFlipbook);
    closeBtn.addEventListener('click', closeFlipbook);
    prevBtn.addEventListener('click', flipBackward);
    nextBtn.addEventListener('click', flipForward);

    // Click on left page to go back, right page to go forward
    pageLeftEl.addEventListener('click', flipBackward);
    pageRightEl.addEventListener('click', flipForward);

    // Close on background click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeFlipbook();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!isOpen) return;

        switch (e.key) {
            case 'Escape':
                closeFlipbook();
                break;
            case 'ArrowLeft':
                flipBackward();
                break;
            case 'ArrowRight':
                flipForward();
                break;
        }
    });

    // Touch / Swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    lightbox.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    lightbox.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                flipForward();
            } else {
                flipBackward();
            }
        }
    }, { passive: true });

})();
