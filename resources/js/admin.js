// Admin Panel JavaScript Functions
document.addEventListener('DOMContentLoaded', function () {

    // Initialize admin panel
    initializeAdminPanel();

    // Setup event listeners
    setupEventListeners();

    // Initialize tooltips
    initializeTooltips();

    // Setup auto-refresh for real-time data
    setupAutoRefresh();
});

// Initialize admin panel
function initializeAdminPanel() {
    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('-translate-x-full');
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }

    // Active navigation highlighting
    highlightActiveNavigation();

    // Initialize data tables
    initializeDataTables();
}

// Setup event listeners
function setupEventListeners() {
    // Confirmation dialogs for delete actions
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Status update buttons
    document.querySelectorAll('.status-toggle').forEach(function (button) {
        button.addEventListener('click', handleStatusToggle);
    });

    // Bulk action checkboxes
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButtons();
        });
    }

    document.querySelectorAll('.item-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', updateBulkActionButtons);
    });

    // Search functionality
    const searchInput = document.getElementById('admin-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value);
            }, 300);
        });
    }

    // Modal handlers
    setupModalHandlers();

    // Form validation
    setupFormValidation();
}

// Highlight active navigation
function highlightActiveNavigation() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');

    navLinks.forEach(function (link) {
        const href = link.getAttribute('href');
        if (currentPath.startsWith(href) && href !== '/admin') {
            link.classList.add('bg-blue-700', 'text-white');
            link.classList.remove('text-blue-100', 'hover:bg-blue-700');
        }
    });
}

// Initialize data tables
function initializeDataTables() {
    // Add sorting functionality to tables
    document.querySelectorAll('.sortable-table th[data-sort]').forEach(function (header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function () {
            sortTable(this);
        });
    });
}

// Sort table functionality
function sortTable(header) {
    const table = header.closest('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const column = header.cellIndex;
    const sortType = header.getAttribute('data-sort');
    const isAscending = !header.classList.contains('sort-asc');

    // Remove existing sort classes
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });

    // Add current sort class
    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

    rows.sort((a, b) => {
        let aVal = a.cells[column].textContent.trim();
        let bVal = b.cells[column].textContent.trim();

        if (sortType === 'number') {
            aVal = parseFloat(aVal) || 0;
            bVal = parseFloat(bVal) || 0;
        } else if (sortType === 'date') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        }

        if (aVal < bVal) return isAscending ? -1 : 1;
        if (aVal > bVal) return isAscending ? 1 : -1;
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}

// Handle status toggle
function handleStatusToggle(e) {
    e.preventDefault();
    const button = this;
    const url = button.getAttribute('data-url');
    const currentStatus = button.getAttribute('data-status');

    showLoadingState(button);

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: currentStatus === 'active' ? 'inactive' : 'active'
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatusButton(button, data.newStatus);
                showNotification('Status updated successfully', 'success');
            } else {
                showNotification('Failed to update status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        })
        .finally(() => {
            hideLoadingState(button);
        });
}

// Update bulk action buttons
function updateBulkActionButtons() {
    const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
    const bulkActions = document.getElementById('bulk-actions');

    if (bulkActions) {
        if (checkedBoxes.length > 0) {
            bulkActions.classList.remove('hidden');
            bulkActions.querySelector('.selected-count').textContent = checkedBoxes.length;
        } else {
            bulkActions.classList.add('hidden');
        }
    }
}

// Search functionality
function performSearch(query) {
    const table = document.querySelector('.admin-table tbody');
    if (!table) return;

    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(query.toLowerCase())) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Modal handlers
function setupModalHandlers() {
    // Open modal buttons
    document.querySelectorAll('[data-modal-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal-target');
            const modal = document.getElementById(modalId);
            if (modal) {
                showModal(modal);
            }
        });
    });

    // Close modal buttons
    document.querySelectorAll('[data-modal-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) {
                hideModal(modal);
            }
        });
    });

    // Close modal on backdrop click
    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                hideModal(this);
            }
        });
    });
}

// Show modal
function showModal(modal) {
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

// Hide modal
function hideModal(modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Form validation
function setupFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

// Validate form
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');

    requiredFields.forEach(function (field) {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });

    return isValid;
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('border-red-500');

    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

// Clear field error
function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Utility functions
function showLoadingState(element) {
    element.disabled = true;
    element.innerHTML = '<div class="loading-spinner"></div> Loading...';
}

function hideLoadingState(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText || element.getAttribute('data-original-text') || 'Submit';
}

function updateStatusButton(button, newStatus) {
    button.setAttribute('data-status', newStatus);
    button.textContent = newStatus === 'active' ? 'Deactivate' : 'Activate';
    button.className = newStatus === 'active'
        ? 'px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full hover:bg-red-200'
        : 'px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full hover:bg-green-200';
}

// Notification system
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${getNotificationClass(type)}`;
    notification.innerHTML = `
        <div class="flex items-center">
            <span class="flex-1">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold">&times;</button>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
}

function getNotificationClass(type) {
    const classes = {
        'success': 'bg-green-500 text-white',
        'error': 'bg-red-500 text-white',
        'warning': 'bg-yellow-500 text-white',
        'info': 'bg-blue-500 text-white'
    };
    return classes[type] || classes.info;
}

// Initialize tooltips
function initializeTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(function (element) {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'absolute bg-gray-800 text-white px-2 py-1 rounded text-sm z-50 tooltip';
    tooltip.textContent = e.target.getAttribute('data-tooltip');

    document.body.appendChild(tooltip);

    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Auto-refresh functionality
function setupAutoRefresh() {
    const refreshElements = document.querySelectorAll('[data-auto-refresh]');

    refreshElements.forEach(function (element) {
        const interval = parseInt(element.getAttribute('data-auto-refresh')) * 1000;
        setInterval(() => {
            refreshElement(element);
        }, interval);
    });
}

function refreshElement(element) {
    const url = element.getAttribute('data-refresh-url') || window.location.href;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newElement = doc.querySelector(`[data-auto-refresh="${element.getAttribute('data-auto-refresh')}"]`);

            if (newElement) {
                element.innerHTML = newElement.innerHTML;
            }
        })
        .catch(error => {
            console.error('Auto-refresh error:', error);
        });
}