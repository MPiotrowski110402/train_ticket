/*
|--------------------------------------------------------------------------
| RailTicket – restored landing / shared interface animations
|--------------------------------------------------------------------------
| This restores every original effect: loader, navbar state, scroll
| progress, cursor glow, background parallax, counters, reveal and ripple.
| Animations use requestAnimationFrame where events fire frequently.
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
        const progressBar = loader?.querySelector('.loader-progress');

        if (!loader || !progressBar) return;

        const minimumVisibleTime = 2400;
        const maximumVisibleTime = 6000;
        const startedAt = performance.now();

        let isHidden = false;
        let targetProgress = 0;
        let currentProgress = 0;
        let animationFrame = null;

        progressBar.style.transform = 'scaleX(0)';

        const animateProgress = () => {
            currentProgress += (targetProgress - currentProgress) * 0.08;

            progressBar.style.transform = `scaleX(${currentProgress / 100})`;

            if (!isHidden) {
                animationFrame = window.requestAnimationFrame(animateProgress);
            }
        };

        const progressInterval = window.setInterval(() => {
            if (targetProgress < 35) {
                targetProgress += 6;
            } else if (targetProgress < 70) {
                targetProgress += 3;
            } else if (targetProgress < 92) {
                targetProgress += 1;
            }

            targetProgress = Math.min(targetProgress, 92);
        }, 120);

        animationFrame = window.requestAnimationFrame(animateProgress);

        const finishLoader = () => {
            if (isHidden) return;

            const elapsed = performance.now() - startedAt;
            const remainingTime = Math.max(0, minimumVisibleTime - elapsed);

            window.setTimeout(() => {
                if (isHidden) return;

                targetProgress = 100;

                const finishAnimationStartedAt = performance.now();
                const finishDuration = 420;

                const finishProgress = () => {
                    const elapsedFinish = performance.now() - finishAnimationStartedAt;
                    const progress = Math.min(elapsedFinish / finishDuration, 1);

                    currentProgress = currentProgress + ((100 - currentProgress) * progress);
                    progressBar.style.transform = `scaleX(${currentProgress / 100})`;

                    if (progress < 1) {
                        window.requestAnimationFrame(finishProgress);
                        return;
                    }

                    progressBar.style.transform = 'scaleX(1)';

                    window.setTimeout(() => {
                        isHidden = true;

                        window.clearInterval(progressInterval);

                        if (animationFrame) {
                            window.cancelAnimationFrame(animationFrame);
                        }

                        loader.classList.add('is-hiding');

                        const removeLoader = () => loader.remove();

                        loader.addEventListener('transitionend', removeLoader, { once: true });
                        window.setTimeout(removeLoader, 900);
                    }, 180);
                };

                window.requestAnimationFrame(finishProgress);
            }, remainingTime);
        };

        if (document.readyState === 'complete') {
            window.setTimeout(finishLoader, 400);
        } else {
            window.addEventListener('load', finishLoader, { once: true });
            window.setTimeout(finishLoader, maximumVisibleTime);
        }
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
                const scrollableHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const progress = scrollableHeight > 0 ? (scrollY / scrollableHeight) * 100 : 0;
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
        const supportsFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

        if (!supportsFinePointer) return;

        const cursor = document.createElement('div');
        cursor.className = 'cursor-glow';
        cursor.setAttribute('aria-hidden', 'true');
        document.body.appendChild(cursor);

        let x = window.innerWidth / 2;
        let y = window.innerHeight / 2;
        let targetX = x;
        let targetY = y;
        let frameId = null;

        const render = () => {
            x += (targetX - x) * 0.24;
            y += (targetY - y) * 0.24;
            cursor.style.transform = `translate3d(${x}px, ${y}px, 0) translate(-50%, -50%)`;

            if (Math.abs(targetX - x) > 0.15 || Math.abs(targetY - y) > 0.15) {
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

        document.addEventListener('pointerleave', () => cursor.classList.remove('is-visible'));
    }

    function initBackgroundParallax() {
        const orbs = [...document.querySelectorAll('.orb-motion')];

        if (!orbs.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

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
                orb.element.style.transform = `translate3d(${orb.x}px, ${orb.y}px, 0)`;

                if (Math.abs(orb.targetX - orb.x) > 0.1 || Math.abs(orb.targetY - orb.y) > 0.1) {
                    isMoving = true;
                }
            });

            frameId = isMoving ? window.requestAnimationFrame(render) : null;
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

        if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
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
            window.setTimeout(() => ripple.remove(), 600);
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
