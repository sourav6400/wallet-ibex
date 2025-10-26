// Main application JavaScript

// Global variables
const SUPPORTED_CURRENCIES = ['BTC', 'ETH', 'LTC', 'USDT', 'XRP', 'DOGE', 'TRX', 'BNB'];

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    console.log('Crypto Wallet App Initialized');
    
    // Make PIN functions globally available immediately
    window.addLoginDigit = addLoginDigit;
    window.deleteLoginDigit = deleteLoginDigit;
    window.clearLoginPin = clearLoginPin;
    window.showCreateRestore = showCreateRestore;
    window.showRestoreOption = showRestoreOption;
    window.updateLoginPinDisplay = updateLoginPinDisplay;
    window.validateLoginPin = validateLoginPin;
    window.clearLoginError = clearLoginError;
    
    console.log('Global functions attached:', {
        addLoginDigit: typeof window.addLoginDigit,
        deleteLoginDigit: typeof window.deleteLoginDigit,
        clearLoginPin: typeof window.clearLoginPin,
        showCreateRestore: typeof window.showCreateRestore,
        showRestoreOption: typeof window.showRestoreOption
    });
    
    // Check if user has active session
    checkUserSession();
});

// Session management
function checkUserSession() {
    const session = localStorage.getItem('walletSession');
    const wallets = localStorage.getItem('cryptoWallets');
    
    // If user has wallets but no active session, show PIN login
    if (wallets && !session) {
        if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/') {
            showPinLogin();
            return;
        }
    }
    
    if (session) {
        try {
            const sessionData = JSON.parse(session);
            const now = Date.now();
            
            if (now < sessionData.expiry) {
                // Session is valid, but only redirect if on index page
                if (window.location.pathname.endsWith('index.html') || window.location.pathname === '/') {
                    window.location.href = 'dashboard.html';
                }
            } else {
                // Session expired but user has wallets, show PIN login
                localStorage.removeItem('walletSession');
                if (wallets && (window.location.pathname.endsWith('index.html') || window.location.pathname === '/')) {
                    showPinLogin();
                }
            }
        } catch (error) {
            console.error('Error parsing session data:', error);
            localStorage.removeItem('walletSession');
            if (wallets && (window.location.pathname.endsWith('index.html') || window.location.pathname === '/')) {
                showPinLogin();
            }
        }
    }
}

// Show PIN login interface
function showPinLogin() {
    const welcomeScreen = document.querySelector('.welcome-screen');
    if (welcomeScreen) {
        welcomeScreen.innerHTML = `
            <div class="logo">
                <i class="fas fa-wallet"></i>
                <h1>Crypto Wallet</h1>
            </div>
            <div class="welcome-content">
                <h2>Welcome Back!</h2>
                <p>Enter your PIN to access your wallet</p>
                
                <div class="pin-login-section">
                    <div class="pin-display">
                        <div class="pin-dots">
                            <span class="pin-dot" id="loginDot1"></span>
                            <span class="pin-dot" id="loginDot2"></span>
                            <span class="pin-dot" id="loginDot3"></span>
                            <span class="pin-dot" id="loginDot4"></span>
                            <span class="pin-dot" id="loginDot5"></span>
                            <span class="pin-dot" id="loginDot6"></span>
                        </div>
                        <div class="pin-error" id="loginPinError"></div>
                    </div>

                    <div class="pin-keypad">
                        <div class="keypad-row">
                            <button class="keypad-btn" data-digit="1">1</button>
                            <button class="keypad-btn" data-digit="2">2</button>
                            <button class="keypad-btn" data-digit="3">3</button>
                        </div>
                        <div class="keypad-row">
                            <button class="keypad-btn" data-digit="4">4</button>
                            <button class="keypad-btn" data-digit="5">5</button>
                            <button class="keypad-btn" data-digit="6">6</button>
                        </div>
                        <div class="keypad-row">
                            <button class="keypad-btn" data-digit="7">7</button>
                            <button class="keypad-btn" data-digit="8">8</button>
                            <button class="keypad-btn" data-digit="9">9</button>
                        </div>
                        <div class="keypad-row">
                            <button class="keypad-btn" data-action="clear" title="Clear PIN">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button class="keypad-btn" data-digit="0">0</button>
                            <button class="keypad-btn" data-action="delete" title="Delete digit">
                                <i class="fas fa-backspace"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="login-actions">
                    <button id="createRestoreBtn" class="btn-secondary">
                        <i class="fas fa-plus"></i> Create New Wallet
                    </button>
                    <button id="restoreOptionBtn" class="btn-secondary">
                        <i class="fas fa-undo"></i> Restore Different Wallet
                    </button>
                </div>
            </div>
        `;
        
        // Initialize login PIN variable
        window.loginPin = '';
        
        // Add event listeners after DOM is updated
        setTimeout(() => {
            initializePinLoginListeners();
        }, 100);
        
        console.log('PIN login interface created');
    }
}

// Initialize PIN login event listeners
function initializePinLoginListeners() {
    console.log('Initializing PIN login listeners...');
    
    // Add event listeners to keypad buttons
    const keypadButtons = document.querySelectorAll('.keypad-btn');
    console.log('Found keypad buttons:', keypadButtons.length);
    
    keypadButtons.forEach((button, index) => {
        const digit = button.getAttribute('data-digit');
        const action = button.getAttribute('data-action');
        
        console.log(`Button ${index}: digit=${digit}, action=${action}`);
        
        // Remove any existing listeners
        button.removeEventListener('click', handleKeypadClick);
        
        // Add new listener
        button.addEventListener('click', handleKeypadClick);
        
        // Also add onclick as fallback
        if (digit) {
            button.onclick = function() {
                console.log('Onclick fallback - digit:', digit);
                addLoginDigit(digit);
            };
        } else if (action === 'clear') {
            button.onclick = function() {
                console.log('Onclick fallback - clear');
                clearLoginPin();
            };
        } else if (action === 'delete') {
            button.onclick = function() {
                console.log('Onclick fallback - delete');
                deleteLoginDigit();
            };
        }
    });
    
    // Add event listeners to action buttons
    const createRestoreBtn = document.getElementById('createRestoreBtn');
    const restoreOptionBtn = document.getElementById('restoreOptionBtn');
    
    if (createRestoreBtn) {
        createRestoreBtn.onclick = function() {
            console.log('Create/Restore button clicked');
            showCreateRestore();
        };
        console.log('Create/Restore button listener added');
    }
    
    if (restoreOptionBtn) {
        restoreOptionBtn.onclick = function() {
            console.log('Restore option button clicked');
            showRestoreOption();
        };
        console.log('Restore option button listener added');
    }
    
    console.log('PIN login listeners initialized successfully');
}

// Handle keypad button clicks
function handleKeypadClick(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const button = event.target.closest('.keypad-btn');
    if (!button) return;
    
    const digit = button.getAttribute('data-digit');
    const action = button.getAttribute('data-action');
    
    console.log('Keypad clicked:', { digit, action });
    
    if (digit) {
        addLoginDigit(digit);
    } else if (action === 'clear') {
        clearLoginPin();
    } else if (action === 'delete') {
        deleteLoginDigit();
    }
}

// PIN login functions
let loginPin = '';

function addLoginDigit(digit) {
    console.log('addLoginDigit called with digit:', digit);
    
    // Ensure loginPin exists
    if (!window.loginPin) {
        window.loginPin = '';
    }
    
    if (window.loginPin.length < 6) {
        window.loginPin += digit;
        console.log('PIN updated to:', window.loginPin);
        updateLoginPinDisplay();
        
        // Auto-validate when PIN is complete
        if (window.loginPin.length === 6) {
            setTimeout(() => {
                validateLoginPin();
            }, 300);
        }
    } else {
        console.log('PIN already 6 digits long');
    }
}

function deleteLoginDigit() {
    console.log('deleteLoginDigit called');
    
    if (!window.loginPin) {
        window.loginPin = '';
    }
    
    if (window.loginPin.length > 0) {
        window.loginPin = window.loginPin.slice(0, -1);
        console.log('PIN updated to:', window.loginPin);
        updateLoginPinDisplay();
        clearLoginError();
    } else {
        console.log('PIN is already empty');
    }
}

function clearLoginPin() {
    console.log('clearLoginPin called');
    window.loginPin = '';
    updateLoginPinDisplay();
    clearLoginError();
    console.log('PIN cleared');
}

function updateLoginPinDisplay() {
    console.log('updateLoginPinDisplay called, PIN length:', (window.loginPin || '').length);
    
    for (let i = 1; i <= 6; i++) {
        const dot = document.getElementById(`loginDot${i}`);
        if (dot) {
            if (i <= (window.loginPin || '').length) {
                dot.classList.add('filled');
                console.log(`Dot ${i} filled`);
            } else {
                dot.classList.remove('filled');
                console.log(`Dot ${i} empty`);
            }
        } else {
            console.log(`Dot ${i} element not found`);
        }
    }
}

function validateLoginPin() {
    const wallets = JSON.parse(localStorage.getItem('cryptoWallets') || '[]');
    const matchingWallet = wallets.find(wallet => wallet.pin === window.loginPin);
    
    if (matchingWallet) {
        // PIN is correct, create session and redirect
        const expiryDate = new Date();
        expiryDate.setFullYear(expiryDate.getFullYear() + 1); // 1 year
        
        localStorage.setItem('walletSession', JSON.stringify({
            username: matchingWallet.username,
            expiry: expiryDate.getTime(),
            lastLogin: Date.now()
        }));
        
        showNotification('Login successful!', 'success');
        window.location.href = 'dashboard.html';
    } else {
        // Wrong PIN
        const errorElement = document.getElementById('loginPinError');
        if (errorElement) {
            errorElement.textContent = 'Incorrect PIN. Please try again.';
            
            // Clear PIN after error
            setTimeout(() => {
                clearLoginPin();
            }, 1500);
        }
    }
}

function clearLoginError() {
    const errorElement = document.getElementById('loginPinError');
    if (errorElement) {
        errorElement.textContent = '';
    }
}

function showCreateRestore() {
    location.reload(); // Reload to show original create/restore options
}

function showRestoreOption() {
    window.location.href = 'restore-wallet.html';
}

// Utility functions
function formatCurrency(amount, currency) {
    const decimals = getDecimalPlaces(currency);
    return parseFloat(amount).toFixed(decimals);
}

function getDecimalPlaces(currency) {
    const decimals = {
        'BTC': 8,
        'ETH': 8,
        'LTC': 8,
        'USDT': 2,
        'XRP': 6,
        'DOGE': 8,
        'TRX': 6,
        'BNB': 8
    };
    return decimals[currency] || 8;
}

function generateRandomId() {
    return Math.random().toString(36).substr(2, 9);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function validateAddress(address, currency) {
    // Basic address validation - in production, use proper validation libraries
    const patterns = {
        'BTC': /^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$|^bc1[a-z0-9]{39,59}$/,
        'ETH': /^0x[a-fA-F0-9]{40}$/,
        'LTC': /^[LM3][a-km-zA-HJ-NP-Z1-9]{26,33}$|^ltc1[a-z0-9]{39,59}$/,
        'USDT': /^0x[a-fA-F0-9]{40}$/,
        'XRP': /^r[0-9a-zA-Z]{24,34}$/,
        'DOGE': /^D{1}[5-9A-HJ-NP-U]{1}[1-9A-HJ-NP-Za-km-z]{32}$/,
        'TRX': /^T[A-Za-z1-9]{33}$/,
        'BNB': /^bnb[0-9a-z]{39}$/
    };
    
    const pattern = patterns[currency];
    return pattern ? pattern.test(address) : false;
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Remove notification after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function showLoading(element, text = 'Loading...') {
    const originalContent = element.innerHTML;
    element.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
    element.disabled = true;
    
    return {
        hide: () => {
            element.innerHTML = originalContent;
            element.disabled = false;
        }
    };
}

function copyToClipboard(text, successMessage = 'Copied to clipboard!') {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(successMessage, 'success');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showNotification('Failed to copy to clipboard', 'error');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            showNotification(successMessage, 'success');
        } catch (err) {
            console.error('Fallback copy failed: ', err);
            showNotification('Failed to copy to clipboard', 'error');
        }
        document.body.removeChild(textArea);
    }
}

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

// Local storage helpers
function getStorageItem(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Error reading from localStorage:', error);
        return defaultValue;
    }
}

function setStorageItem(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error('Error writing to localStorage:', error);
        return false;
    }
}

function removeStorageItem(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('Error removing from localStorage:', error);
        return false;
    }
}

// PIN validation
function isValidPin(pin) {
    return pin && pin.length === 6 && /^\d{6}$/.test(pin);
}

// Amount validation
function isValidAmount(amount, currency) {
    const num = parseFloat(amount);
    if (isNaN(num) || num <= 0) return false;
    
    const decimals = getDecimalPlaces(currency);
    const regex = new RegExp(`^\\d+(\\.\\d{1,${decimals}})?$`);
    return regex.test(amount.toString());
}

// Error handling
function handleError(error, userMessage = 'An error occurred') {
    console.error('Application error:', error);
    showNotification(userMessage, 'error');
}

// Network status
function checkNetworkStatus() {
    return navigator.onLine;
}

// Add network status listeners
window.addEventListener('online', () => {
    showNotification('Connection restored', 'success');
});

window.addEventListener('offline', () => {
    showNotification('No internet connection', 'warning');
});

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        formatCurrency,
        getDecimalPlaces,
        generateRandomId,
        formatDate,
        validateAddress,
        showNotification,
        showLoading,
        copyToClipboard,
        debounce,
        throttle,
        getStorageItem,
        setStorageItem,
        removeStorageItem,
        isValidPin,
        isValidAmount,
        handleError,
        checkNetworkStatus
    };
}

// Export PIN login functions globally
if (typeof window !== 'undefined') {
    window.addLoginDigit = addLoginDigit;
    window.deleteLoginDigit = deleteLoginDigit;
    window.clearLoginPin = clearLoginPin;
    window.validateLoginPin = validateLoginPin;
    window.clearLoginError = clearLoginError;
    window.showCreateRestore = showCreateRestore;
    window.showRestoreOption = showRestoreOption;
    window.showPinLogin = showPinLogin;
}
