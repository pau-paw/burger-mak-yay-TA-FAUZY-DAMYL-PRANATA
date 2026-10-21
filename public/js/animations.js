/**
 * Burger Mak Yay - Motion & Animation Engine
 * Handles:
 * - Scroll Reveal via IntersectionObserver
 * - Page Loading Bar & Transition
 * - Dynamic Button Submit Spinners
 */
(function () {
    'use strict';

    /* ==========================================================================
       1. PAGE LOADING BAR (ANIMASI PEMUATAN)
       ========================================================================== */
    let loadingBar = document.getElementById('page-loading-bar');
    if (!loadingBar) {
        loadingBar = document.createElement('div');
        loadingBar.id = 'page-loading-bar';
        document.documentElement.appendChild(loadingBar);
    }

    // Initial load progression
    loadingBar.style.width = '30%';

    function finishLoadingBar() {
        if (!loadingBar) return;
        loadingBar.style.width = '100%';
        setTimeout(() => {
            loadingBar.classList.add('is-finished');
        }, 200);
    }

    if (document.readyState === 'complete') {
        finishLoadingBar();
    } else {
        window.addEventListener('load', finishLoadingBar);
        document.addEventListener('DOMContentLoaded', () => {
            if (loadingBar) loadingBar.style.width = '75%';
        });
    }

    // Trigger loading bar on internal navigation
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Skip anchors, javascript, external links, or new tabs
        if (
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            link.target === '_blank' ||
            link.hasAttribute('download') ||
            e.metaKey || e.ctrlKey || e.shiftKey || e.altKey
        ) {
            return;
        }

        const isSameOrigin = link.origin === window.location.origin;
        if (isSameOrigin && !href.startsWith('mailto:') && !href.startsWith('tel:')) {
            if (loadingBar) {
                loadingBar.classList.remove('is-finished');
                loadingBar.style.width = '0%';
                requestAnimationFrame(() => {
                    loadingBar.style.width = '65%';
                });
            }
        }
    });

    // Handle button submit spinner animation
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;

        if (loadingBar) {
            loadingBar.classList.remove('is-finished');
            loadingBar.style.width = '40%';
            setTimeout(() => {
                loadingBar.style.width = '80%';
            }, 150);
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.querySelector('.spinner-icon')) {
            const spinner = document.createElement('span');
            spinner.className = 'spinner-icon';
            submitBtn.prepend(spinner);
            submitBtn.classList.add('is-loading');
        }
    });


    /* ==========================================================================
       2. SCROLL ANIMATIONS (ANIMASI GULIR)
       ========================================================================== */
    function initScrollAnimations() {
        // Elements to observe for scroll reveal
        const targetSelectors = [
            '.promo-card',
            '.home-product-card',
            '.product-card',
            '.addon-card',
            '.choice-card',
            '.review-card',
            '.footer-teaser',
            '.detail-grid',
            '.auth-card',
            '.panel',
            '.stats .panel',
            '.home-menu-heading',
            '.ingredients',
            '.payment-action-card',
            '[data-animate]'
        ];

        const elements = document.querySelectorAll(targetSelectors.join(','));

        if (!('IntersectionObserver' in window)) {
            // Fallback for browsers without IntersectionObserver
            elements.forEach((el) => el.classList.add('is-visible'));
            return;
        }

        const observerOptions = {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px'
        };

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, observerOptions);

        elements.forEach((el, index) => {
            el.classList.add('scroll-reveal');

            // Apply staggered delays for grid items
            const parentGrid = el.closest('.home-product-grid, .product-grid, .promo-grid, .choice-cards');
            if (parentGrid) {
                const siblings = Array.from(parentGrid.children);
                const itemIndex = siblings.indexOf(el);
                if (itemIndex >= 0) {
                    const delay = (itemIndex % 4) * 0.08;
                    el.style.transitionDelay = `${delay}s`;
                }
            }

            observer.observe(el);
        });
    }

    /* ==========================================================================
       3. BACKGROUND TEXT PARALLAX (DOWN-TO-UP SCROLL ANIMATION)
       ========================================================================== */
    function initBackgroundParallax() {
        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReduced) return;

        const heroTitle = document.querySelector('.hero-title');
        const heroSection = document.querySelector('.hero');
        const ingredientType = document.querySelector('.ingredient-type');
        const ingredientSection = document.querySelector('.ingredients');

        if (!heroTitle && !ingredientType) return;

        let isHeroVisible = true;
        let isIngredientVisible = false;
        let isTicking = false;

        // Use IntersectionObserver so scroll calculations only run when section is visible
        if ('IntersectionObserver' in window) {
            if (heroSection) {
                new IntersectionObserver((entries) => {
                    isHeroVisible = entries[0].isIntersecting;
                }, { rootMargin: '100px 0px' }).observe(heroSection);
            }
            if (ingredientSection) {
                new IntersectionObserver((entries) => {
                    isIngredientVisible = entries[0].isIntersecting;
                }, { rootMargin: '100px 0px' }).observe(ingredientSection);
            }
        }

        function onScrollUpdate() {
            const scrollY = window.pageYOffset || document.documentElement.scrollTop;

            // Hero title background text: smoothly glides upward as you scroll down (down-to-up)
            if (heroTitle && isHeroVisible) {
                const heroOffset = Math.max(-160, Math.min(30, -scrollY * 0.35));
                heroTitle.style.setProperty('--hero-ty', `${heroOffset.toFixed(1)}px`);
            }

            // Ingredient background text: smooth upward parallax
            if (ingredientType && ingredientSection && isIngredientVisible) {
                const rect = ingredientSection.getBoundingClientRect();
                const progress = (window.innerHeight - rect.top);
                if (progress > 0) {
                    const ingOffset = Math.max(-140, Math.min(30, (progress - window.innerHeight) * -0.2));
                    ingredientType.style.setProperty('--ingredient-ty', `${ingOffset.toFixed(1)}px`);
                }
            }

            isTicking = false;
        }

        window.addEventListener('scroll', () => {
            if (!isTicking) {
                window.requestAnimationFrame(onScrollUpdate);
                isTicking = true;
            }
        }, { passive: true });

        // Initial paint
        onScrollUpdate();
    }

    function initAll() {
        initScrollAnimations();
        initBackgroundParallax();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
