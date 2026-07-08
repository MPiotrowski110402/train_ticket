/*
|--------------------------------------------------------------------------
| RailTicket – restored landing / shared interface animations
|--------------------------------------------------------------------------
| Restores loader, navbar state, scroll progress, cursor glow,
| background parallax, counters, reveal and ripple.
|--------------------------------------------------------------------------
*/

(() => {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        initLoader();
        initScrollUi();
        initCursorGlow();
        initBackgroundParallax();
        initCounters();
        initRevealOnScroll();
        initButtonRipple();
        initTicketInputFocus();
    });

    function initLoader() {
        const loader = document.getElementById('loader');
        const progress = loader?.querySelector('.loader-progress');

        if (!loader) return;

        /*
        |--------------------------------------------------------------------------
        | LOADER PROGRESS RESET
        |--------------------------------------------------------------------------
        | Wymuszamy start paska od zera. Dzięki temu nawet po cache/przejściu
        | między stronami animacja nie startuje jako już pełna.
        |--------------------------------------------------------------------------
        */

        if (progress) {
            progress.style.animation = 'none';
            progress.style.width = '0';

            // Wymuszenie reflow — bez tego przeglądarka czasem nie restartuje animacji.
            void progress.offsetWidth;

            progress.style.animation = 'loading 2s forwards';
        }

        /*
        |--------------------------------------------------------------------------
        | HIDE LOADER
        |--------------------------------------------------------------------------
        | Tak jak w pierwszej wersji: loader nie czeka na window.load.
        | Pasek dochodzi do końca i ekran znika po około 2.2 s.
        |--------------------------------------------------------------------------
        */

        window.setTimeout(() => {
            loader.classList.add('is-hiding');
            loader.classList.add('loader-hide');

            loader.style.transition = 'opacity .7s ease, visibility 0s linear .7s';
            loader.style.opacity = '0';
            loader.style.visibility = 'hidden';
            loader.style.pointerEvents = 'none';

            window.setTimeout(() => {
                loader.remove();
            }, 750);
        }, 2200);
    }

    function initScrollUi() {
        const navbar = document.querySelector('.navbar');
        const scrollProgress = document.querySelector('.scroll-progress');

        if (!navbar && !scrollProgress) return;

        let isQueued = false;

        const update = () => {
            const scrollY = window.scrollY;

            if (navbar) {
                navbar.classList.toggle('scrolled', scrollY > 50);
            }

            if (scrollProgress) {
                const scrollableHeight =
                    document.documentElement.scrollHeight -
                    document.documentElement.clientHeight;

                const progress =
                    scrollableHeight > 0
                        ? (scrollY / scrollableHeight) * 100
                        : 0;

                scrollProgress.style.width = `${Math.min(100, Math.max(0, progress))}%`;
            }

            isQueued = false;
        };

        const requestUpdate = () => {
            if (isQueued) return;

            isQueued = true;
            window.requestAnimationFrame(update);
        };

        requestUpdate();

        window.addEventListener('scroll', requestUpdate, { passive: true });
        window.addEventListener('resize', requestUpdate, { passive: true });
    }

    function initCursorGlow() {
        const supportsFinePointer = window.matchMedia(
            '(hover: hover) and (pointer: fine)'
        ).matches;

        if (!supportsFinePointer) return;

        let cursor = document.querySelector('.cursor-glow');

        if (!cursor) {
            cursor = document.createElement('div');
            cursor.className = 'cursor-glow';
            cursor.setAttribute('aria-hidden', 'true');
            document.body.appendChild(cursor);
        }

        let x = window.innerWidth / 2;
        let y = window.innerHeight / 2;
        let targetX = x;
        let targetY = y;
        let frameId = null;

        const render = () => {
            x += (targetX - x) * 0.24;
            y += (targetY - y) * 0.24;

            cursor.style.transform =
                `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`;

            if (
                Math.abs(targetX - x) > 0.15 ||
                Math.abs(targetY - y) > 0.15
            ) {
                frameId = window.requestAnimationFrame(render);
                return;
            }

            x = targetX;
            y = targetY;
            frameId = null;
        };

        document.addEventListener('pointermove', (event) => {
            targetX = event.clientX;
            targetY = event.clientY;

            cursor.classList.add('is-visible');

            if (!frameId) {
                frameId = window.requestAnimationFrame(render);
            }
        }, { passive: true });

        document.addEventListener('pointerleave', () => {
            cursor.classList.remove('is-visible');
        });
    }

    function initBackgroundParallax() {
        const orbs = [...document.querySelectorAll('.orb-motion')];

        if (
            !orbs.length ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            return;
        }

        const state = orbs.map((orb, index) => ({
            element: orb,
            speed: (index + 1) * 12,
            x: 0,
            y: 0,
            targetX: 0,
            targetY: 0,
        }));

        let frameId = null;

        const render = () => {
            let isMoving = false;

            state.forEach((orb) => {
                orb.x += (orb.targetX - orb.x) * 0.08;
                orb.y += (orb.targetY - orb.y) * 0.08;

                orb.element.style.transform =
                    `translate3d(${orb.x}px, ${orb.y}px, 0)`;

                if (
                    Math.abs(orb.targetX - orb.x) > 0.1 ||
                    Math.abs(orb.targetY - orb.y) > 0.1
                ) {
                    isMoving = true;
                }
            });

            frameId = isMoving
                ? window.requestAnimationFrame(render)
                : null;
        };

        document.addEventListener('pointermove', (event) => {
            const relativeX = event.clientX / window.innerWidth - 0.5;
            const relativeY = event.clientY / window.innerHeight - 0.5;

            state.forEach((orb) => {
                orb.targetX = relativeX * orb.speed * 2;
                orb.targetY = relativeY * orb.speed * 2;
            });

            if (!frameId) {
                frameId = window.requestAnimationFrame(render);
            }
        }, { passive: true });
    }

    function initCounters() {
        const counters = [...document.querySelectorAll('.counter')];

        if (!counters.length) return;

        const animateCounter = (counter) => {
            if (counter.dataset.animated === 'true') return;

            counter.dataset.animated = 'true';

            const target = Number(counter.dataset.target);
            const duration = 1150;
            const startTime = performance.now();

            const update = (now) => {
                const progress = Math.min(1, (now - startTime) / duration);
                const easedProgress = 1 - Math.pow(1 - progress, 3);

                counter.textContent = Math.ceil(target * easedProgress).toString();

                if (progress < 1) {
                    window.requestAnimationFrame(update);
                } else {
                    counter.textContent = target.toString();
                }
            };

            window.requestAnimationFrame(update);
        };

        if (!('IntersectionObserver' in window)) {
            counters.forEach(animateCounter);
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                animateCounter(entry.target);
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.35 });

        counters.forEach((counter) => observer.observe(counter));
    }

    function initRevealOnScroll() {
        const elements = [...document.querySelectorAll('.reveal')];

        if (!elements.length) return;

        if (
            !('IntersectionObserver' in window) ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            elements.forEach((element) => element.classList.add('visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.15 });

        elements.forEach((element) => observer.observe(element));
    }

    function initButtonRipple() {
        document.addEventListener('pointerdown', (event) => {
            const button = event.target.closest('.btn, .search-btn');

            if (!button || button.matches(':disabled')) return;

            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();

            ripple.className = 'ripple';
            ripple.style.left = `${event.clientX - rect.left}px`;
            ripple.style.top = `${event.clientY - rect.top}px`;

            button.appendChild(ripple);

            window.setTimeout(() => {
                ripple.remove();
            }, 600);
        }, { passive: true });
    }

    function initTicketInputFocus() {
        const card = document.querySelector('.ticket-card');

        if (!card) return;

        card.addEventListener('focusin', (event) => {
            const group = event.target.closest('.input-group');
            group?.classList.add('active');
        });

        card.addEventListener('focusout', (event) => {
            const group = event.target.closest('.input-group');
            group?.classList.remove('active');
        });
    }
})();

/* =========================
   DEMO AUTH FALLBACK FOR IFRAME
========================= */

document.addEventListener('DOMContentLoaded', () => {
    const demoUser = {
        name: 'Testowy User',
        email: 'testowy.user@test.pl',
        phone: '500 600 700',
    };

    const params = new URLSearchParams(window.location.search);

    if (params.get('demo_logged') === '1') {
        localStorage.setItem('railticket_demo_user', JSON.stringify(demoUser));

        params.delete('demo_logged');

        const cleanQuery = params.toString();
        const cleanUrl = window.location.pathname + (cleanQuery ? `?${cleanQuery}` : '');

        window.history.replaceState({}, '', cleanUrl);
    }

    const storedUserRaw = localStorage.getItem('railticket_demo_user');
    const storedUser = storedUserRaw ? JSON.parse(storedUserRaw) : null;

    if (!storedUser) {
        return;
    }

    const authButton = document.querySelector('#demoAuthButton');

    if (authButton) {
        authButton.textContent = 'Wyloguj';
        authButton.href = '#';
        authButton.addEventListener('click', (event) => {
            event.preventDefault();

            localStorage.removeItem('railticket_demo_user');

            window.location.href = '/';
        });
    }

    const guestName = document.querySelector('[name="guest_name"]');
    const guestEmail = document.querySelector('[name="guest_email"]');
    const guestPhone = document.querySelector('[name="guest_phone"]');

    if (guestName && !guestName.value) {
        guestName.value = storedUser.name;
    }

    if (guestEmail && !guestEmail.value) {
        guestEmail.value = storedUser.email;
    }

    if (guestPhone && !guestPhone.value) {
        guestPhone.value = storedUser.phone;
    }
});