@extends('layouts.app')

@section('content')
<div @if(app()->getLocale() == 'ar') dir="rtl" @endif class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-sm border-b border-gray-200/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-lg">K</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">KORE ERP</h1>
                        <p class="text-sm text-gray-600">{{ $dashboardData['agency']['name'] ?? 'Agence' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3 rtl:space-x-reverse">
                    <button class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                        </svg>
                    </button>
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium">{{ substr($dashboardData['agency']['name'] ?? 'A', 0, 1) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 py-8">
        <!-- Top Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Occupation Rate -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">{{ $dashboardData['statistics']['occupancy_rate'] ?? 0 }}%</span>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Taux d'occupation</h3>
                <div class="flex items-end justify-between">
                    <span class="text-2xl font-semibold text-gray-900">{{ $dashboardData['statistics']['occupancy_rate'] ?? 0 }}%</span>
                    <div class="w-16 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: {{ $dashboardData['statistics']['occupancy_rate'] ?? 0 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">+{{ $dashboardData['statistics']['revenue_growth'] ?? 0 }}%</span>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Revenu mensuel</h3>
                <span class="text-2xl font-semibold text-gray-900">{{ number_format($dashboardData['statistics']['monthly_revenue'] ?? 0, 0, ',', ' ') }} DH</span>
            </div>

            <!-- Pending Maintenance -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded-full">{{ $dashboardData['statistics']['pending_maintenance'] ?? 0 }}</span>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">Maintenance en attente</h3>
                <span class="text-2xl font-semibold text-gray-900">{{ $dashboardData['statistics']['pending_maintenance'] ?? 0 }}</span>
            </div>

            <!-- AI Status -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded-full">Healthy</span>
                </div>
                <h3 class="text-sm font-medium text-gray-600 mb-1">État de l'IA</h3>
                <span class="text-lg font-semibold text-green-600">Opérationnel</span>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Tendance des revenus</h2>
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <button class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">7J</button>
                        <button class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-lg">30J</button>
                        <button class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">90J</button>
                    </div>
                </div>
                <div id="revenueChart" class="h-80"></div>
            </div>

            <!-- AI Widget -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 border border-indigo-100 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Assistant IA</h3>
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                </div>
                <div class="space-y-4">
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <h4 class="font-medium text-gray-900 mb-2">Prédictions immobilières</h4>
                        <p class="text-sm text-gray-600 mb-3">{{ $dashboardData['predictions']['market_trend'] ?? 'Analyse du marché en cours...' }}</p>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">Confiance</span>
                            <span class="font-medium text-indigo-600">{{ $dashboardData['predictions']['confidence'] ?? 0 }}%</span>
                        </div>
                    </div>
                    <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <h4 class="font-medium text-gray-900 mb-2">Opportunités</h4>
                        <p class="text-sm text-gray-600">{{ $dashboardData['predictions']['opportunities'] ?? 'Recherche d\'opportunités...' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Activités récentes</h2>
                <button class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Voir tout</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-gray-600">Type</th>
                            <th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-gray-600">Description</th>
                            <th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-gray-600">Statut</th>
                            <th class="text-left rtl:text-right py-3 px-4 text-sm font-medium text-gray-600">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach(($dashboardData['recentActivity'] ?? []) as $activity)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-100 to-indigo-200 flex items-center justify-center mr-3 rtl:mr-0 rtl:ml-3">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($activity['type'] === 'invoice')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            @elseif($activity['type'] === 'lease')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 01-6.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                            @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            @endif
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ ucfirst($activity['type'] ?? '') }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">{{ $activity['description'] ?? '' }}</td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if(($activity['status'] ?? '') === 'completed') bg-green-100 text-green-800
                                    @elseif(($activity['status'] ?? '') === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif(($activity['status'] ?? '') === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($activity['status'] ?? '') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-500">{{ $activity['date'] ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Status Footer -->
        <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4 border border-gray-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-6 rtl:space-x-reverse">
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Database</span>
                    </div>
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Redis</span>
                    </div>
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">AI Services</span>
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    Dernière mise à jour: {{ now()->format('H:i') }}
                </div>
            </div>
        </div>
    </main>
</div>

<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Revenue Chart
    const revenueOptions = {
        series: [{
            name: 'Revenus',
            data: {!! json_encode($dashboardData['charts']['revenue_trend']['data'] ?? [30, 40, 35, 50, 49, 60, 70, 91, 125]) !!}
        }],
        chart: {
            type: 'area',
            height: 320,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: false
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.3,
                stops: [0, 90, 100]
            }
        },
        colors: ['#4f46e5'],
        xaxis: {
            categories: {!! json_encode($dashboardData['charts']['revenue_trend']['labels'] ?? ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep']) !!},
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            },
            labels: {
                style: {
                    colors: '#6b7280',
                    fontSize: '12px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#6b7280',
                    fontSize: '12px'
                },
                formatter: function(val) {
                    return val + "k DH"
                }
            }
        },
        grid: {
            borderColor: '#f3f4f6',
            strokeDashArray: 3
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + "k DH"
                }
            }
        }
    };

    const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
    revenueChart.render();

    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@endsection