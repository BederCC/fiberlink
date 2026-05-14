<?php require_once '../includes/header.php'; ?>
<script>
    const userData = JSON.parse(localStorage.getItem('user'));
    if (userData && userData.role === 'technician') {
        window.location.href = 'technician/dashboard.php';
    }
</script>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="p-4 sm:ml-64 mt-14">
        <div class="p-4 border border-dashed border-slate-700 rounded-xl">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                    <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors" id="statsActiveServices">-</p>
                    <p class="text-sm text-slate-400">Instalaciones Activas</p>
                </div>
                <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                    <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors" id="statsPendingPayments">-</p>
                    <p class="text-sm text-slate-400">Pendientes de Pago</p>
                </div>
                <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                    <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors" id="statsMonthlyIncome">-</p>
                    <p class="text-sm text-slate-400">Ingresos del Mes</p>
                </div>
            </div>

            <!-- Chart Area -->
            <div class="rounded-xl bg-slate-800/50 border border-slate-700 mb-4 p-4">
                <h3 class="text-lg font-semibold text-white mb-4">Ingresos Mensuales (Últimos 6 meses)</h3>
                <div class="h-64 w-full">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            <!-- Recent Activity Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="rounded-xl bg-slate-800/50 border border-slate-700 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Últimas Instalaciones</h3>
                    <ul class="space-y-3" id="recentActivityList">
                        <li class="text-center text-slate-500 text-sm">Cargando...</li>
                    </ul>
                </div>
                <div class="rounded-xl bg-slate-800/50 border border-slate-700 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Alertas de Sistema</h3>
                    <ul class="space-y-3" id="systemAlertsList">
                        <li class="text-center text-slate-500 text-sm">Cargando...</li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Widget A: Plan Distribution -->
                <div class="rounded-xl bg-slate-800/50 border border-slate-700 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Distribución de Planes Activos</h3>
                    <div class="space-y-4" id="planDistributionList">
                        <p class="text-center text-slate-500 text-sm">Cargando...</p>
                    </div>
                </div>
                <!-- Widget B: Service Status -->
                <div class="rounded-xl bg-slate-800/50 border border-slate-700 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Estado de Servicios</h3>
                    <div class="flex justify-center items-center h-40">
                         <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetchDashboardData();
        });

        async function fetchDashboardData() {
            try {
                const response = await fetch('../api/dashboard.php');
                const data = await response.json();

                // Update Stats
                document.getElementById('statsActiveServices').textContent = data.stats.active_services;
                document.getElementById('statsPendingPayments').textContent = data.stats.pending_payments;
                document.getElementById('statsMonthlyIncome').textContent = 'S/ ' + parseFloat(data.stats.monthly_income).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                // Update Recent Activity
                const activityList = document.getElementById('recentActivityList');
                activityList.innerHTML = '';
                if (data.recent_activity.length > 0) {
                    data.recent_activity.forEach(item => {
                        let statusColor = 'bg-slate-500/10 text-slate-400';
                        let statusText = item.service_status;
                        
                        if (item.service_status === 'active') {
                            statusColor = 'bg-emerald-500/10 text-emerald-400';
                            statusText = 'Activo';
                        } else if (item.service_status === 'pending') {
                            statusColor = 'bg-amber-500/10 text-amber-400';
                            statusText = 'Pendiente';
                        } else if (item.service_status === 'suspended') {
                            statusColor = 'bg-red-500/10 text-red-400';
                            statusText = 'Suspendido';
                        }

                        activityList.innerHTML += `
                            <li class="flex items-center justify-between text-sm">
                                <span class="text-slate-300">${item.fullname}</span>
                                <span class="px-2 py-1 rounded-full ${statusColor} text-xs capitalize">${statusText}</span>
                            </li>
                        `;
                    });
                } else {
                    activityList.innerHTML = '<li class="text-center text-slate-500 text-sm">No hay actividad reciente</li>';
                }

                // Update Alerts
                const alertsList = document.getElementById('systemAlertsList');
                alertsList.innerHTML = '';
                if (data.alerts.length > 0) {
                    data.alerts.forEach(alert => {
                        let colorClass = 'bg-blue-500';
                        if (alert.type === 'danger') colorClass = 'bg-red-500';
                        if (alert.type === 'warning') colorClass = 'bg-yellow-500';
                        
                        alertsList.innerHTML += `
                            <li class="flex items-center gap-3 text-sm">
                                <span class="w-2 h-2 rounded-full ${colorClass}"></span>
                                <span class="text-slate-300">${alert.message}</span>
                            </li>
                        `;
                    });
                } else {
                    alertsList.innerHTML = '<li class="text-center text-slate-500 text-sm">No hay alertas pendientes</li>';
                }

                // Render Income Chart
                const ctxIncome = document.getElementById('incomeChart').getContext('2d');
                new Chart(ctxIncome, {
                    type: 'line',
                    data: {
                        labels: data.income_history.map(item => item.month),
                        datasets: [{
                            label: 'Ingresos (S/)',
                            data: data.income_history.map(item => item.amount),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#334155' },
                                ticks: { color: '#94a3b8' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });

                // Render Plan Distribution
                const planList = document.getElementById('planDistributionList');
                planList.innerHTML = '';
                if (data.plan_distribution.length > 0) {
                    const totalPlans = data.plan_distribution.reduce((acc, curr) => acc + curr.count, 0);
                    data.plan_distribution.forEach(plan => {
                        const percent = ((plan.count / totalPlans) * 100).toFixed(1);
                        planList.innerHTML += `
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-slate-300">${plan.name}</span>
                                    <span class="text-slate-400">${plan.count} (${percent}%)</span>
                                </div>
                                <div class="w-full bg-slate-700 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: ${percent}%"></div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    planList.innerHTML = '<p class="text-center text-slate-500 text-sm">No hay datos de planes</p>';
                }

                // Render Status Chart (Doughnut)
                const ctxStatus = document.getElementById('statusChart').getContext('2d');
                const statusLabels = data.status_distribution.map(item => {
                    if(item.service_status === 'active') return 'Activo';
                    if(item.service_status === 'suspended') return 'Suspendido';
                    if(item.service_status === 'cut') return 'Cortado';
                    return item.service_status;
                });
                const statusCounts = data.status_distribution.map(item => item.count);
                const statusColors = data.status_distribution.map(item => {
                    if(item.service_status === 'active') return '#10b981'; // Emerald
                    if(item.service_status === 'suspended') return '#f59e0b'; // Amber
                    if(item.service_status === 'cut') return '#ef4444'; // Red
                    return '#64748b';
                });

                new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusCounts,
                            backgroundColor: statusColors,
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                position: 'right',
                                labels: { color: '#cbd5e1', boxWidth: 12 }
                            }
                        }
                    }
                });

            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        }
    </script>

<?php require_once '../includes/footer.php'; ?>
