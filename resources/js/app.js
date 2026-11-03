import './bootstrap';

const mobileMenuButton = document.querySelector('[data-sidebar-toggle]');
const collapseButton = document.querySelector('[data-sidebar-collapse]');
const backdrop = document.querySelector('[data-sidebar-backdrop]');

function setMobileMenu(open) {
    document.body.classList.toggle('sidebar-open', open);
    mobileMenuButton?.setAttribute('aria-expanded', String(open));
    mobileMenuButton?.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
}

mobileMenuButton?.addEventListener('click', () => {
    setMobileMenu(!document.body.classList.contains('sidebar-open'));
});

backdrop?.addEventListener('click', () => setMobileMenu(false));

collapseButton?.addEventListener('click', () => {
    const collapsed = document.body.classList.toggle('sidebar-collapsed');
    collapseButton.setAttribute('aria-expanded', String(!collapsed));
    collapseButton.setAttribute('aria-label', collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setMobileMenu(false);
    }
});

window.addEventListener('resize', () => {
    if (window.innerWidth > 760) {
        setMobileMenu(false);
    } else {
        document.body.classList.remove('sidebar-collapsed');
        collapseButton?.setAttribute('aria-expanded', 'true');
        collapseButton?.setAttribute('aria-label', 'Ciutkan sidebar');
    }
});

const timeElement = document.querySelector('#current-time');
const dateElement = document.querySelector('#current-date');

if (timeElement && dateElement) {
    const updateClock = () => {
        const now = new Date();
        timeElement.textContent = new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        }).format(now);
        dateElement.textContent = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(now);
    };

    updateClock();
    window.setInterval(updateClock, 1000);
}
