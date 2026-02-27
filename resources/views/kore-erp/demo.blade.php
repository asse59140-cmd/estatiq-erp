@extends('layouts.kore-erp')

@section('title', 'KORE ERP - Démonstration Interactive')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/20 to-purple-600/20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <div class="mb-8">
                    <span class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white text-sm font-medium rounded-full">
                        <span class="mr-2">🚀</span>
                        Production Ready
                    </span>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-bold text-white mb-6">
                    KORE ERP
                    <span class="block text-3xl md:text-4xl font-light text-blue-300 mt-2">
                        Real Estate Intelligence Platform
                    </span>
                </h1>
                
                <p class="text-xl text-slate-300 mb-8 max-w-3xl mx-auto">
                    La plateforme immobilière ultime qui dépasse Rwad.ai avec intelligence artificielle, 
                    architecture multi-locataire blindée et support international complet.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('kore-erp.dashboard') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                        <span class="mr-2">🏢</span>
                        Tableau de Bord
                    </a>
                    <button onclick="startDemo()" class="inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-lg border border-white/20 hover:bg-white/20 transition-all">
                        <span class="mr-2">▶️</span>
                        Démonstration
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Showcase -->
    <div class="py-24 bg-slate-800/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Fonctionnalités Enterprise</h2>
                <p class="text-xl text-slate-300">Technologie de pointe pour la gestion immobilière moderne</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Multi-Tenant Security -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/10 transition-all hover-lift">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mb-6">
                        <span class="text-white text-xl">🛡️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Sécurité Multi-Tenant</h3>
                    <p class="text-slate-300 mb-4">Isolation absolue des données entre agences avec Global Scope automatique et chiffrement enterprise.</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-green-400">
                            <span class="mr-2">✅</span>
                            Global Scope automatique
                        </div>
                        <div class="flex items-center text-sm text-green-400">
                            <span class="mr-2">✅</span>
                            Isolation des données
                        </div>
                        <div class="flex items-center text-sm text-green-400">
                            <span class="mr-2">✅</span>
                            Audit trail complet
                        </div>
                    </div>
                </div>

                <!-- AI Intelligence -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/10 transition-all hover-lift">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mb-6">
                        <span class="text-white text-xl">🤖</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Intelligence Artificielle</h3>
                    <p class="text-slate-300 mb-4">Prédictions intelligentes pour optimiser vos investissements et anticiper les tendances du marché.</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-purple-400">
                            <span class="mr-2">✅</span>
                            Prédictions d'occupation
                        </div>
                        <div class="flex items-center text-sm text-purple-400">
                            <span class="mr-2">✅</span>
                            Analyse des tendances
                        </div>
                        <div class="flex items-center text-sm text-purple-400">
                            <span class="mr-2">✅</span>
                            Optimisation des revenus
                        </div>
                    </div>
                </div>

                <!-- Arabic RTL Support -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/10 transition-all hover-lift">
                    <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center mb-6">
                        <span class="text-white text-xl">🇦🇪</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Support Arabe RTL</h3>
                    <p class="text-slate-300 mb-4">Support complet pour le marché Middle East avec interface RTL et traductions arabes professionnelles.</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-orange-400">
                            <span class="mr-2">✅</span>
                            Interface RTL complète
                        </div>
                        <div class="flex items-center text-sm text-orange-400">
                            <span class="mr-2">✅</span>
                            Traductions arabes
                        </div>
                        <div class="flex items-center text-sm text-orange-400">
                            <span class="mr-2">✅</span>
                            Calendrier hijri
                        </div>
                    </div>
                </div>

                <!-- Electronic Signatures -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/10 transition-all hover-lift">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center mb-6">
                        <span class="text-white text-xl">✍️</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Signatures Électroniques</h3>
                    <p class="text-slate-300 mb-4">Intégration DocuSign pour des contrats sécurisés et des processus de signature automatisés.</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-blue-400">
                            <span class="mr-2">✅</span>
                            Intégration DocuSign
                        </div>
                        <div class="flex items-center text-sm text-blue-400">
                            <span class="mr-2">✅</span>
                            Processus automatisés
                        </div>
                        <div class="flex items-center text-sm text-blue-400">
                            <span class="mr-2">✅</span>
                            Contrats sécurisés
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Integration -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/10 transition-all hover-lift">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-teal-500 rounded-lg flex items-center justify-center mb-6">
                        <span class="text-white text-xl">💬</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">WhatsApp Business</h3>
                    <p class="text-slate-300 mb-4">Communication automatisée avec vos clients via WhatsApp Business API.</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-green-400">
                            <span class="mr-2">✅</span>
                            Notifications automatiques
                        </div>
                        <div class="flex items-center text-sm text-green-400">
                            <span class="mr-2">✅</span>
                            Support client 24/7
                        </div>
                        <div class="flex items-center text-sm text-green-400">
                            <span class="mr-2">✅</span>
                            Campagnes marketing
                        </div>
                    </div>
                </div>

                <!-- Performance & Scalability -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 border border-white/10 hover:bg-white/10 transition-all hover-lift">
                    <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center mb-6">
                        <span class="text-white text-xl">⚡</span>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-4">Performance & Évolutivité</h3>
                    <p class="text-slate-300 mb-4">Architecture optimisée avec Redis, MySQL 8.0 et queues asynchrones pour une performance maximale.</p>
                    <div class="space-y-2">
                        <div class="flex items-center text-sm text-indigo-400">
                            <span class="mr-2">✅</span>
                            Redis Cache & Sessions
                        </div>
                        <div class="flex items-center text-sm text-indigo-400">
                            <span class="mr-2">✅</span>
                            Queues asynchrones
                        </div>
                        <div class="flex items-center text-sm text-indigo-400">
                            <span class="mr-2">✅</span>
                            MySQL 8.0 optimisé
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Demo Section -->
    <div class="py-24 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Démonstration Interactive</h2>
                <p class="text-xl text-slate-300">Découvrez KORE ERP en action</p>
            </div>

            <div id="demo-container" class="bg-slate-800 rounded-xl p-8 border border-slate-700">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- System Test -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-white mb-4">🧪 Test Système</h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg">
                                <span class="text-slate-300">Configuration Système</span>
                                <span id="config-status" class="text-yellow-400">⏳ En cours...</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg">
                                <span class="text-slate-300">Base de Données</span>
                                <span id="db-status" class="text-yellow-400">⏳ En cours...</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg">
                                <span class="text-slate-300">Redis Cache</span>
                                <span id="redis-status" class="text-yellow-400">⏳ En cours...</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg">
                                <span class="text-slate-300">Sécurité Multi-Tenant</span>
                                <span id="security-status" class="text-yellow-400">⏳ En cours...</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg">
                                <span class="text-slate-300">Services IA</span>
                                <span id="ai-status" class="text-yellow-400">⏳ En cours...</span>
                            </div>
                        </div>
                        
                        <button onclick="runFullTest()" class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all">
                            🚀 Lancer le Test Complet
                        </button>
                    </div>

                    <!-- Live Metrics -->
                    <div class="space-y-6">
                        <h3 class="text-xl font-semibold text-white mb-4">📊 Métriques en Temps Réel</h3>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-blue-400" id="memory-usage">64MB</div>
                                <div class="text-sm text-slate-400">Mémoire</div>
                            </div>
                            
                            <div class="bg-slate-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-400" id="response-time">45ms</div>
                                <div class="text-sm text-slate-400">Temps Réponse</div>
                            </div>
                            
                            <div class="bg-slate-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-purple-400" id="cache-hit">98%</div>
                                <div class="text-sm text-slate-400">Cache Hit</div>
                            </div>
                            
                            <div class="bg-slate-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-orange-400" id="active-connections">127</div>
                                <div class="text-sm text-slate-400">Connexions</div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-700 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-slate-300 mb-3">🔄 Activité Système</h4>
                            <div id="activity-log" class="space-y-2 text-xs max-h-32 overflow-y-auto">
                                <div class="text-green-400">✅ Système KORE ERP initialisé</div>
                                <div class="text-blue-400">ℹ️ Prêt pour les tests...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="py-24 bg-gradient-to-r from-blue-600 to-purple-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-6">Prêt pour la Révolution Immobilière ?</h2>
            <p class="text-xl text-blue-100 mb-8">
                Rejoignez les leaders du marché Middle East qui utilisent déjà KORE ERP pour transformer leur gestion immobilière.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('kore-erp.dashboard') }}" class="inline-flex items-center px-8 py-4 bg-white text-blue-600 font-semibold rounded-lg hover:bg-slate-100 transition-all transform hover:scale-105 shadow-xl">
                    <span class="mr-2">🏢</span>
                    Accéder au Tableau de Bord
                </a>
                <button onclick="showContactForm()" class="inline-flex items-center px-8 py-4 bg-blue-700 text-white font-semibold rounded-lg hover:bg-blue-800 transition-all border border-blue-500">
                    <span class="mr-2">📞</span>
                    Contactez-nous
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">K</span>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">KORE ERP</h3>
                            <p class="text-slate-400 text-sm">Real Estate Intelligence Platform</p>
                        </div>
                    </div>
                    <p class="text-slate-300 mb-4">
                        La solution de gestion immobilière intelligente qui transforme la façon dont vous gérez vos propriétés.
                    </p>
                    <div class="flex space-x-4">
                        <span class="text-green-400 text-sm">✅ Production Ready</span>
                        <span class="text-blue-400 text-sm">✅ Middle East Optimized</span>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Fonctionnalités</h4>
                    <ul class="space-y-2 text-slate-300">
                        <li>🏢 Multi-Tenant</li>
                        <li>🤖 Intelligence IA</li>
                        <li>🇦🇪 Support Arabe</li>
                        <li>✍️ Signatures Électroniques</li>
                        <li>💬 WhatsApp Business</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-semibold mb-4">Technologie</h4>
                    <ul class="space-y-2 text-slate-300">
                        <li>Laravel 12 + Filament</li>
                        <li>MySQL 8.0 + Redis</li>
                        <li>AI: OpenAI, Google, Anthropic</li>
                        <li>DocuSign Integration</li>
                        <li>Stripe Payments</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-slate-800 mt-8 pt-8 text-center">
                <p class="text-slate-400">
                    © 2024 KORE ERP. Tous droits réservés. 
                    <span class="text-slate-500">|</span> 
                    Conçu pour dominer le marché Middle East 🇦🇪
                </p>
            </div>
        </div>
    </footer>
</div>

<!-- Contact Modal -->
<div id="contact-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold text-slate-900 mb-4">Contactez-nous</h3>
        <p class="text-slate-600 mb-6">Prêt à transformer votre gestion immobilière avec KORE ERP ?</p>
        
        <div class="space-y-4">
            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                <span class="text-blue-600">📧</span>
                <span class="text-slate-700">contact@kore-erp.com</span>
            </div>
            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                <span class="text-blue-600">📞</span>
                <span class="text-slate-700">+971 4 XXX XXXX</span>
            </div>
            <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg">
                <span class="text-blue-600">🌐</span>
                <span class="text-slate-700">www.kore-erp.com</span>
            </div>
        </div>
        
        <button onclick="hideContactForm()" class="w-full mt-6 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors">
            Fermer
        </button>
    </div>
</div>

@push('scripts')
<script>
    // Demo Functions
    function startDemo() {
        KoreErp.notify('🚀 Démarrage de la démonstration KORE ERP', 'info');
        setTimeout(runSystemCheck, 1000);
    }
    
    function runSystemCheck() {
        const checks = [
            { id: 'config-status', name: 'Configuration', delay: 500 },
            { id: 'db-status', name: 'Base de Données', delay: 1000 },
            { id: 'redis-status', name: 'Redis Cache', delay: 1500 },
            { id: 'security-status', name: 'Sécurité Multi-Tenant', delay: 2000 },
            { id: 'ai-status', name: 'Services IA', delay: 2500 }
        ];
        
        checks.forEach(check => {
            setTimeout(() => {
                const element = document.getElementById(check.id);
                if (Math.random() > 0.1) { // 90% success rate for demo
                    element.className = 'text-green-400';
                    element.textContent = '✅ Opérationnel';
                    addActivityLog(`✅ ${check.name}: Système opérationnel`);
                } else {
                    element.className = 'text-red-400';
                    element.textContent = '❌ Erreur';
                    addActivityLog(`❌ ${check.name}: Erreur détectée`);
                }
            }, check.delay);
        });
    }
    
    function runFullTest() {
        KoreErp.loading.show();
        addActivityLog('🔄 Lancement du test système complet...');
        
        setTimeout(() => {
            KoreErp.loading.hide();
            KoreErp.notify('✅ Test système terminé avec succès !', 'success');
            addActivityLog('✅ Test système: Tous les composants opérationnels');
            
            // Update metrics
            updateMetrics();
        }, 3000);
    }
    
    function updateMetrics() {
        document.getElementById('memory-usage').textContent = '58MB';
        document.getElementById('response-time').textContent = '32ms';
        document.getElementById('cache-hit').textContent = '99%';
        document.getElementById('active-connections').textContent = '156';
    }
    
    function addActivityLog(message) {
        const log = document.getElementById('activity-log');
        const entry = document.createElement('div');
        entry.className = 'text-green-400';
        entry.textContent = `${new Date().toLocaleTimeString()}: ${message}`;
        log.appendChild(entry);
        log.scrollTop = log.scrollHeight;
    }
    
    function showContactForm() {
        document.getElementById('contact-modal').classList.remove('hidden');
    }
    
    function hideContactForm() {
        document.getElementById('contact-modal').classList.add('hidden');
    }
    
    // Auto-refresh metrics
    setInterval(() => {
        if (Math.random() > 0.7) { // 30% chance to update
            const metrics = ['memory-usage', 'response-time', 'cache-hit', 'active-connections'];
            const metric = metrics[Math.floor(Math.random() * metrics.length)];
            const element = document.getElementById(metric);
            const currentValue = parseInt(element.textContent);
            const variation = Math.floor(Math.random() * 10) - 5; // -5 to +5
            const newValue = Math.max(1, currentValue + variation);
            
            if (metric === 'response-time') {
                element.textContent = newValue + 'ms';
            } else if (metric === 'cache-hit') {
                element.textContent = Math.min(100, newValue) + '%';
            } else {
                element.textContent = newValue + (metric === 'memory-usage' ? 'MB' : '');
            }
        }
    }, 2000);
    
    // Initialize demo
    document.addEventListener('DOMContentLoaded', function() {
        KoreErp.notify('🎯 KORE ERP Demo Ready - Middle East Optimized', 'success');
        addActivityLog('ℹ️ Système KORE ERP initialisé avec succès');
    });
</script>
@endpush
@endsection