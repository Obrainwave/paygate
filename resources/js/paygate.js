/**
 * Paygate JavaScript Integration
 * Provides client-side payment handling and utilities
 */

class Paygate {
    constructor(options = {}) {
        this.baseUrl = options.baseUrl || '/api/paygate';
        this.csrfToken = options.csrfToken || this.getCsrfToken();
        this.defaultProvider = options.defaultProvider || 'paystack';
        this.defaultCurrency = options.defaultCurrency || 'NGN';
        this.onSuccess = options.onSuccess || this.defaultSuccessHandler;
        this.onError = options.onError || this.defaultErrorHandler;
        this.onPending = options.onPending || this.defaultPendingHandler;
    }

    /**
     * Initiate a payment
     */
    async initiatePayment(data) {
        try {
            this.showLoading();
            
            const response = await this.makeRequest('POST', '/initiate', data);
            
            if (response.errors) {
                this.onError(response);
                return response;
            }

            // Store payment reference for verification
            this.storePaymentReference(response.data.reference);
            
            // Redirect to checkout or handle inline payment
            if (response.data.checkout_url) {
                this.redirectToCheckout(response.data.checkout_url);
            } else {
                this.onPending(response);
            }

            return response;
        } catch (error) {
            const errorResponse = {
                errors: true,
                message: 'Network error occurred',
                description: error.message
            };
            this.onError(errorResponse);
            return errorResponse;
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Verify a payment
     */
    async verifyPayment(reference, provider = null) {
        try {
            this.showLoading();
            
            const params = new URLSearchParams();
            if (provider) params.append('provider', provider);
            
            const response = await this.makeRequest('GET', `/verify/${reference}?${params}`);
            
            if (response.errors) {
                this.onError(response);
                return response;
            }

            if (response.status === 'successful') {
                this.onSuccess(response);
            } else if (response.status === 'failed') {
                this.onError(response);
            } else {
                this.onPending(response);
            }

            return response;
        } catch (error) {
            const errorResponse = {
                errors: true,
                message: 'Verification failed',
                description: error.message
            };
            this.onError(errorResponse);
            return errorResponse;
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Get payment status
     */
    async getPaymentStatus(reference) {
        try {
            const response = await this.makeRequest('GET', `/status/${reference}`);
            return response;
        } catch (error) {
            return {
                errors: true,
                message: 'Failed to get payment status',
                description: error.message
            };
        }
    }

    /**
     * Refund a payment
     */
    async refundPayment(data) {
        try {
            this.showLoading();
            
            const response = await this.makeRequest('POST', '/refund', data);
            
            if (response.errors) {
                this.onError(response);
            } else {
                this.onSuccess(response);
            }

            return response;
        } catch (error) {
            const errorResponse = {
                errors: true,
                message: 'Refund failed',
                description: error.message
            };
            this.onError(errorResponse);
            return errorResponse;
        } finally {
            this.hideLoading();
        }
    }

    /**
     * Get available gateways
     */
    async getAvailableGateways() {
        try {
            const response = await this.makeRequest('GET', '/gateways');
            return response;
        } catch (error) {
            return {
                errors: true,
                message: 'Failed to get gateways',
                description: error.message
            };
        }
    }

    /**
     * Handle payment callback
     */
    handleCallback() {
        const urlParams = new URLSearchParams(window.location.search);
        const reference = urlParams.get('reference');
        const provider = urlParams.get('provider');
        
        if (reference) {
            this.verifyPayment(reference, provider);
        }
    }

    /**
     * Make HTTP request
     */
    async makeRequest(method, endpoint, data = null) {
        const url = `${this.baseUrl}${endpoint}`;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'Accept': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Request failed');
        }

        return result;
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    /**
     * Store payment reference in localStorage
     */
    storePaymentReference(reference) {
        localStorage.setItem('paygate_payment_reference', reference);
    }

    /**
     * Get stored payment reference
     */
    getStoredPaymentReference() {
        return localStorage.getItem('paygate_payment_reference');
    }

    /**
     * Clear stored payment reference
     */
    clearStoredPaymentReference() {
        localStorage.removeItem('paygate_payment_reference');
    }

    /**
     * Redirect to checkout URL
     */
    redirectToCheckout(checkoutUrl) {
        window.location.href = checkoutUrl;
    }

    /**
     * Show loading indicator
     */
    showLoading() {
        const loadingElement = document.getElementById('paygate-loading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        
        // Disable form buttons
        const buttons = document.querySelectorAll('button[type="submit"]');
        buttons.forEach(button => {
            button.disabled = true;
            button.classList.add('loading');
        });
    }

    /**
     * Hide loading indicator
     */
    hideLoading() {
        const loadingElement = document.getElementById('paygate-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
        
        // Enable form buttons
        const buttons = document.querySelectorAll('button[type="submit"]');
        buttons.forEach(button => {
            button.disabled = false;
            button.classList.remove('loading');
        });
    }

    /**
     * Default success handler
     */
    defaultSuccessHandler(response) {
        console.log('Payment successful:', response);
        
        // Show success message
        this.showNotification('Payment completed successfully!', 'success');
        
        // Redirect to success page if available
        if (response.reference) {
            const successUrl = `/paygate/success/${response.reference}`;
            setTimeout(() => {
                window.location.href = successUrl;
            }, 2000);
        }
    }

    /**
     * Default error handler
     */
    defaultErrorHandler(response) {
        console.error('Payment failed:', response);
        
        // Show error message
        const message = response.message || 'Payment failed. Please try again.';
        this.showNotification(message, 'error');
    }

    /**
     * Default pending handler
     */
    defaultPendingHandler(response) {
        console.log('Payment pending:', response);
        
        // Show pending message
        this.showNotification('Payment is being processed...', 'info');
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `paygate-notification paygate-notification-${type}`;
        notification.textContent = message;
        
        // Add styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            max-width: 300px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        `;
        
        // Set background color based on type
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            info: '#17a2b8',
            warning: '#ffc107'
        };
        notification.style.backgroundColor = colors[type] || colors.info;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    /**
     * Format amount for display
     */
    formatAmount(amount, currency = null) {
        const currencySymbol = this.getCurrencySymbol(currency || this.defaultCurrency);
        return `${currencySymbol}${parseFloat(amount).toFixed(2)}`;
    }

    /**
     * Get currency symbol
     */
    getCurrencySymbol(currency) {
        const symbols = {
            'NGN': '₦',
            'USD': '$',
            'EUR': '€',
            'GBP': '£',
            'GHS': '₵',
            'KES': 'KSh',
            'ZAR': 'R'
        };
        return symbols[currency] || currency;
    }

    /**
     * Validate email
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Validate amount
     */
    validateAmount(amount) {
        const num = parseFloat(amount);
        return !isNaN(num) && num > 0;
    }

    /**
     * Generate payment reference
     */
    generateReference(prefix = 'TXN') {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substr(2, 9);
        return `${prefix}_${timestamp}_${random}`.toUpperCase();
    }
}

// Auto-initialize if data attributes are present
document.addEventListener('DOMContentLoaded', function() {
    const paygateElement = document.querySelector('[data-paygate]');
    if (paygateElement) {
        const options = {
            baseUrl: paygateElement.dataset.baseUrl || '/api/paygate',
            defaultProvider: paygateElement.dataset.defaultProvider || 'paystack',
            defaultCurrency: paygateElement.dataset.defaultCurrency || 'NGN'
        };
        
        window.paygate = new Paygate(options);
        
        // Handle payment forms
        const forms = document.querySelectorAll('form[data-paygate-form]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                // Add default values
                data.provider = data.provider || options.defaultProvider;
                data.currency = data.currency || options.defaultCurrency;
                
                // Generate reference if not provided
                if (!data.reference) {
                    data.reference = window.paygate.generateReference();
                }
                
                window.paygate.initiatePayment(data);
            });
        });
        
        // Handle callback if on callback page
        if (window.location.pathname.includes('/callback')) {
            window.paygate.handleCallback();
        }
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Paygate;
}
