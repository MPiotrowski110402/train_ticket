document.addEventListener('DOMContentLoaded', () => {
    const loader = document.getElementById('loader');

    if (loader) {
        window.setTimeout(() => {
            loader.style.transition = 'opacity .55s ease';
            loader.style.opacity = '0';

            window.setTimeout(() => loader.remove(), 550);
        }, 700);
    }

    const navbar = document.querySelector('.navbar');
    const scrollProgress = document.querySelector('.scroll-progress');

    const updateScrollUi = () => {
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        }

        if (scrollProgress) {
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const progress = height > 0 ? (window.scrollY / height) * 100 : 0;
            scrollProgress.style.width = `${progress}%`;
        }
    };

    updateScrollUi();
    window.addEventListener('scroll', updateScrollUi, { passive: true });

    const cursor = document.createElement('div');
    cursor.className = 'cursor-glow';
    document.body.appendChild(cursor);

    document.addEventListener('mousemove', (event) => {
        cursor.style.left = `${event.clientX}px`;
        cursor.style.top = `${event.clientY}px`;
    });

    const orbs = document.querySelectorAll('.gradient-orb');

    document.addEventListener('mousemove', (event) => {
        const x = event.clientX / window.innerWidth;
        const y = event.clientY / window.innerHeight;

        orbs.forEach((orb, index) => {
            const speed = (index + 1) * 12;
            orb.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
        });
    });

    document.querySelectorAll('.btn, .search-btn').forEach((button) => {
        button.addEventListener('click', function (event) {
            if (this.disabled) return;

            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();

            ripple.className = 'ripple';
            ripple.style.left = `${event.clientX - rect.left}px`;
            ripple.style.top = `${event.clientY - rect.top}px`;

            this.appendChild(ripple);
            window.setTimeout(() => ripple.remove(), 600);
        });
    });

    const revealElements = document.querySelectorAll('.reveal');

    if (revealElements.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });

        revealElements.forEach((element) => observer.observe(element));
    } else {
        revealElements.forEach((element) => element.classList.add('visible'));
    }
});
