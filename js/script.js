document.addEventListener('DOMContentLoaded', function() {
    if (!window.CONFIG) {
        window.CONFIG = {
            API_BASE: '',
            DEBUG: true,
            TIMEOUTS: {
                AUTH_INIT: 100,
                PAGE_LOAD: 50
            }
        };
    }
    
    initSidebar();
    initNavigation();
    initLazyLoading();
    initSmoothScrolling();
});

function initSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    }
}

function initNavigation() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentPage = urlParams.get('page') || 'index';
    
    document.querySelectorAll('.main-nav a').forEach(link => {
        const href = link.getAttribute('href');
        if (href === `?page=${currentPage}` || 
            (currentPage === 'index' && href === '?page=index') ||
            (currentPage === '' && href === '?page=index')) {
            link.classList.add('active');
        }
    });
}

function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    if (images.length === 0) return;
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('fade-in');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}