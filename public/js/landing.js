'use strict';

// Native <details> works without JavaScript. Add outside-click / Escape support.
const navigation = document.querySelector('[data-nav-menu]');
if (navigation) {
    navigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => { navigation.open = false; });
    });
    document.addEventListener('click', (event) => {
        if (!navigation.contains(event.target)) navigation.open = false;
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && navigation.open) {
            navigation.open = false;
            navigation.querySelector('summary').focus();
        }
    });
}

document.querySelectorAll('[data-review-carousel]').forEach((carousel) => {
    const slides = [...carousel.querySelectorAll('[data-review-slide]')];
    const controls = carousel.querySelector('[data-review-controls]');
    if (slides.length < 2 || !controls) return;
    let active = 0;
    controls.hidden = false;
    const show = (next) => {
        active = (next + slides.length) % slides.length;
        slides.forEach((slide, index) => { slide.hidden = index !== active; });
        carousel.querySelector('[data-review-number]').textContent = String(active + 1);
    };
    carousel.querySelector('[data-review-prev]').addEventListener('click', () => show(active - 1));
    carousel.querySelector('[data-review-next]').addEventListener('click', () => show(active + 1));
    controls.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            show(active + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });
});
