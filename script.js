// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    // Dropdown toggle for mobile
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            // Only prevent default on mobile
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                    dropdownMenu.classList.toggle('active');
                }
            }
        });
    });
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link (but not dropdown toggles)
        const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle)');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!navToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });
    }
});

// Smooth Scrolling for Navigation Links (guard against href="#")
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        // Ignore empty/hash-only links like "#"
        if (!href || href === '#') {
            return;
        }
        e.preventDefault();
        try {
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        } catch (err) {
            // Invalid selector (e.g., malformed href) - safely ignore
        }
    });
});

// Navbar Background Change on Scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    if (window.scrollY > 50) {
        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
        navbar.style.backdropFilter = 'blur(10px)';
    } else {
        navbar.style.background = '#fff';
        navbar.style.backdropFilter = 'none';
    }
});

// Intersection Observer for Animation on Scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements for animation
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.feature-card, .course-card, .testimonial-card, .stat-item');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});

// Counter Animation for Stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent.replace(/[^0-9]/g, ''));
        const suffix = counter.textContent.replace(/[0-9]/g, '');
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target + suffix;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current) + suffix;
            }
        }, 20);
    });
}

// Trigger counter animation when stats section is visible
const statsSection = document.querySelector('.stats');
if (statsSection) {
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    statsObserver.observe(statsSection);
}

// Form Validation (for contact forms)
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Email validation
    const emailFields = form.querySelectorAll('input[type="email"]');
    emailFields.forEach(field => {
        if (field.value && !isValidEmail(field.value)) {
            showFieldError(field, 'Please enter a valid email address');
            isValid = false;
        }
    });
    
    // Phone validation
    const phoneFields = form.querySelectorAll('input[type="tel"]');
    phoneFields.forEach(field => {
        if (field.value && !isValidPhone(field.value)) {
            showFieldError(field, 'Please enter a valid phone number');
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('error');
    const errorMessage = field.parentNode.querySelector('.error-message');
    if (errorMessage) {
        errorMessage.remove();
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    return phoneRegex.test(phone.replace(/[\s\-\(\)]/g, ''));
}

// Course Filter and Search Functionality
function initializeCourseFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const courseCards = document.querySelectorAll('.course-card');
    const searchInput = document.querySelector('#courseSearch');
    
    // Ensure each course card has a stable id generated from its title
    courseCards.forEach(card => {
        try {
            const titleEl = card.querySelector('h3');
            if (titleEl && !card.id) {
                const slug = titleEl.textContent.trim().toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-');
                if (slug) card.id = slug;
            }
        } catch(_) {}
    });
    
    // Filter functionality
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Filter courses
            courseCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            let firstVisible = null;
            courseCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                    if (!firstVisible) firstVisible = card;
                } else {
                    card.style.display = 'none';
                }
            });
            // Scroll to first visible course card if any
            if (firstVisible) {
                setTimeout(() => {
                    firstVisible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 200);
            }
        });

        // On Enter, try to navigate to exact title match
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                navigateToExactCourse(this.value);
            }
        });
    }

    // Apply initial filter/search from URL params if provided
    try {
        const params = new URLSearchParams(window.location.search);
        const urlFilter = params.get('filter');
        const urlSearch = params.get('search');

        if (urlFilter) {
            const btn = Array.from(filterButtons).find(b => b.getAttribute('data-filter') === urlFilter);
            if (btn) {
                btn.click();
            }
        }

        if (urlSearch && searchInput) {
            searchInput.value = urlSearch;
            // Trigger filtering based on search value
            const event = new Event('input', { bubbles: true });
            searchInput.dispatchEvent(event);
            // After initial filtering, attempt exact course jump & highlight
            setTimeout(() => {
                try { navigateToExactCourse(urlSearch); } catch(_) {}
            }, 120);
        }
    } catch (e) {
        // ignore URL parsing issues
    }
}

// Navigate to exact course by title, highlight it, and update hash
function navigateToExactCourse(query) {
    const term = (query || '').trim().toLowerCase();
    if (!term) return;
    const courseCards = document.querySelectorAll('.course-card');
    let exact = null;

    courseCards.forEach(card => {
        const titleEl = card.querySelector('h3');
        if (!titleEl) return;
        const title = titleEl.textContent.trim().toLowerCase();
        if (title === term && !exact) exact = card;
    });

    // If no exact match, try unique includes
    if (!exact) {
        const matches = Array.from(courseCards).filter(card => {
            const t = (card.querySelector('h3')?.textContent || '').trim().toLowerCase();
            return t.includes(term);
        });
        if (matches.length === 1) exact = matches[0];
    }

    if (exact) {
        // Make sure it's visible (reset filters)
        const filterButtons = document.querySelectorAll('.filter-btn');
        const allBtn = Array.from(filterButtons).find(b => b.getAttribute('data-filter') === 'all');
        if (allBtn) allBtn.click();
        // Show all cards to ensure visibility
        document.querySelectorAll('.course-card').forEach(c => c.style.display = 'block');

        // Update hash id
        if (exact.id) {
            window.location.hash = '#' + exact.id;
        }

        // Highlight and scroll
        exact.classList.add('course-highlight');
        exact.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => exact.classList.remove('course-highlight'), 2500);
    } else {
        if (window.AdeptskilJS && typeof window.AdeptskilJS.showNotification === 'function') {
            window.AdeptskilJS.showNotification('No exact course match found', 'error');
        }
    }
}

// Dashboard Functionality
function initializeDashboard() {
    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.dashboard-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }
    
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Update active tab button
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show target tab content
            tabContents.forEach(content => {
                if (content.getAttribute('data-tab') === targetTab) {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        });
    });
    
    // Modal functionality
    const modalTriggers = document.querySelectorAll('[data-modal]');
    const modals = document.querySelectorAll('.modal');
    const modalCloses = document.querySelectorAll('.modal-close');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            const modal = this.closest('.modal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    });
    
    // Close modal when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });
}

// Notification System
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        hideNotification(notification);
    }, 5000);
    
    // Close button functionality
    notification.querySelector('.notification-close').addEventListener('click', () => {
        hideNotification(notification);
    });
}

function hideNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

// Loading State
function showLoading(button) {
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
}

function hideLoading(button, originalText) {
    button.disabled = false;
    button.innerHTML = originalText;
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize course filters if on courses page
    if (document.querySelector('.course-filters')) {
        initializeCourseFilters();
        // If arriving with a hash (e.g., header search), highlight & scroll to the course
        const initialHash = window.location.hash.replace('#','').trim();
        if (initialHash) {
            const targetCard = document.getElementById(initialHash);
            if (targetCard && targetCard.classList.contains('course-card')) {
                targetCard.classList.add('course-highlight');
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => targetCard.classList.remove('course-highlight'), 2500);
            }
        }
    }
    
    // Initialize dashboard if on dashboard page
    if (document.querySelector('.dashboard')) {
        initializeDashboard();
    }
    
    // Initialize form validation for all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
    // Header search form submit (only for forms inside the fixed navbar)
    document.querySelectorAll('form.header-search-box[role="search"]').forEach(form => {
        if (form.closest('.navbar')) {
            form.addEventListener('submit', function(e){
                performHeaderSearch(e);
            });
        }
    });
});

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Lazy Loading for Images
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
}

// Initialize lazy loading if there are lazy images
if (document.querySelectorAll('img[data-src]').length > 0) {
    initializeLazyLoading();
}

// Export functions for global use
// Header search function (navigate to courses page with optional search parameter)
function performHeaderSearch(ev) {
    if (ev && ev.preventDefault) ev.preventDefault();
    const input = document.getElementById('headerSearchInput');
    if (!input) return;
    const term = input.value.trim();
    if (!term) {
        window.location.href = 'courses.html';
        return;
    }
    // Build slug identical to course card id generation logic
    const slug = term.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-');
    // Include both search param (for filtering) and hash (for direct jump)
    window.location.href = 'courses.html?search=' + encodeURIComponent(term) + '#' + slug;
}

// Courses page top search handler: syncs with on-page filter and exact jump
function coursePageSearch(ev) {
    if (ev && ev.preventDefault) ev.preventDefault();
    const input = document.getElementById('coursePageSearchInput');
    const term = (input && input.value ? input.value.trim() : '');
    const filterInput = document.getElementById('courseSearch');
    if (filterInput) {
        filterInput.value = term;
        const evt = new Event('input', { bubbles: true });
        filterInput.dispatchEvent(evt);
    }
    if (term && typeof navigateToExactCourse === 'function') {
        navigateToExactCourse(term);
    }
}

window.AdeptskilJS = {
    showNotification,
    validateForm,
    showLoading,
    hideLoading,
    debounce,
    throttle,
    performHeaderSearch,
    coursePageSearch
};