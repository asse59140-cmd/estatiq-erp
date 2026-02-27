<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('arabic.default_direction', 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'KORE ERP') }} - @yield('title')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Styles -->
    <style>
        .kore-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hover-lift {
            transition: transform 0.2s ease-in-out;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
        }
        
        .rtl {
            direction: rtl;
        }
        
        .apple-like {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>
    
    @stack('styles')
</head>
<body class="font-sans antialiased apple-like bg-slate-50">
    <div class="min-h-screen bg-slate-50">
        @yield('content')
    </div>
    
    @stack('scripts')
    
    <!-- Global KORE ERP Script -->
    <script>
        // KORE ERP Global Configuration
        window.KoreErp = {
            config: {
                locale: '{{ app()->getLocale() }}',
                direction: '{{ config("arabic.default_direction", "ltr") }}',
                currency: '{{ config("app.currency", "MAD") }}',
                timezone: '{{ config("app.timezone", "Asia/Riyadh") }}',
                version: '1.0.0'
            },
            
            // Utility functions
            utils: {
                formatCurrency: function(amount, currency = 'MAD') {
                    return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                        style: 'currency',
                        currency: currency
                    }).format(amount);
                },
                
                formatDate: function(date, format = 'long') {
                    return new Intl.DateTimeFormat('{{ app()->getLocale() }}', {
                        dateStyle: format
                    }).format(new Date(date));
                },
                
                formatNumber: function(number) {
                    return new Intl.NumberFormat('{{ app()->getLocale() }}').format(number);
                },
                
                // RTL support
                isRTL: function() {
                    return this.config.direction === 'rtl';
                },
                
                // Arabic number conversion
                toArabicNumbers: function(number) {
                    if (this.config.locale === 'ar') {
                        const arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
                        return number.toString().replace(/\d/g, d => arabicNumbers[d]);
                    }
                    return number.toString();
                }
            },
            
            // Notification system
            notify: function(message, type = 'info') {
                const colors = {
                    success: 'bg-green-500',
                    error: 'bg-red-500',
                    warning: 'bg-yellow-500',
                    info: 'bg-blue-500'
                };
                
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
                notification.textContent = message;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            },
            
            // Loading states
            loading: {
                show: function() {
                    const loader = document.createElement('div');
                    loader.id = 'kore-loading';
                    loader.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                    loader.innerHTML = `
                        <div class="bg-white rounded-lg p-6 shadow-xl">
                            <div class="flex items-center space-x-3">
                                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                                <span class="text-slate-700">Chargement...</span>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(loader);
                },
                
                hide: function() {
                    const loader = document.getElementById('kore-loading');
                    if (loader) {
                        loader.remove();
                    }
                }
            },
            
            // AI Integration
            ai: {
                analyze: function(data, callback) {
                    // Simulate AI analysis
                    setTimeout(() => {
                        callback({
                            success: true,
                            data: {
                                prediction: Math.random() * 100,
                                confidence: Math.random(),
                                insights: ['Insight 1', 'Insight 2']
                            }
                        });
                    }, 1000);
                }
            }
        };
        
        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            // Add RTL class if needed
            if (KoreErp.utils.isRTL()) {
                document.documentElement.classList.add('rtl');
            }
            
            // Add smooth scrolling
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Welcome message
            console.log('%c🏢 KORE ERP - Real Estate Intelligence Platform', 'color: #667eea; font-size: 16px; font-weight: bold;');
            console.log('%cVersion: ' + KoreErp.config.version, 'color: #764ba2; font-size: 12px;');
            console.log('%cReady for production deployment 🚀', 'color: #10b981; font-size: 12px;');
        });
        
        // Handle form submissions with loading states
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.classList.contains('kore-form')) {
                KoreErp.loading.show();
                
                // Hide loading after form submission (adjust timing as needed)
                setTimeout(() => {
                    KoreErp.loading.hide();
                }, 2000);
            }
        });
    </script>
</body>
</html>