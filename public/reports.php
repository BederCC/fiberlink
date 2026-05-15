<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <h1 class="text-2xl font-bold text-white">Reportes y Métricas</h1>
            <div class="flex flex-wrap gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:flex-none">
                    <button onclick="document.getElementById('exportDropdown').classList.toggle('hidden')" class="w-full px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors flex items-center justify-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span class="whitespace-nowrap">Exportar Reporte</span>
                    </button>
                    <div id="exportDropdown" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 hidden z-10">
                        <a href="#" onclick="exportData('metrics_excel')" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">Excel (.xls)</a>
                        <a href="#" onclick="exportData('metrics_pdf')" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">PDF (.pdf)</a>
                        <a href="#" onclick="exportData('payment_logs_pdf')" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 border-t border-slate-100">Logs de Pago (MikroTik Style)</a>
                    </div>
                </div>
                
                <button onclick="exportData('debts_excel')" class="flex-1 md:flex-none px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="whitespace-nowrap">Exportar Deudas</span>
                </button>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                <p class="text-slate-400 text-sm">Tiempo Promedio de Pago</p>
                <div class="flex items-end gap-2">
                    <p class="text-3xl font-bold text-indigo-400" id="avgTime">--</p>
                    <span class="text-xs text-slate-500 mb-1">segundos</span>
                </div>
                <p class="text-xs text-slate-500 mt-2">Desde búsqueda de DNI hasta confirmación</p>
            </div>
            <!-- More metrics can go here -->
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Financial Chart -->
            <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                <h3 class="text-lg font-semibold text-white mb-4">Ingresos Mensuales</h3>
                <div class="relative h-64">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            <!-- Operational Chart -->
            <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                <h3 class="text-lg font-semibold text-white mb-4">Estado de Servicios</h3>
                <div class="relative h-64 flex justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Debtors Table -->
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-4">
            <h3 class="text-lg font-semibold text-white mb-4">Top Deudores</h3>
            <div class="relative overflow-x-auto rounded-lg">
                <table class="w-full text-sm text-left text-slate-400">
                    <thead class="text-xs text-slate-300 uppercase bg-slate-700">
                        <tr>
                            <th scope="col" class="px-6 py-3">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-right">Deuda Total</th>
                        </tr>
                    </thead>
                    <tbody id="debtorsTableBody">
                        <!-- Data -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadReportData();
    });

    async function loadReportData() {
        try {
            const response = await fetch('../api/reports.php?action=summary');
            const data = await response.json();

            // 1. Metrics
            document.getElementById('avgTime').textContent = data.avg_payment_time || '0';

            // 2. Income Chart
            const incomeCtx = document.getElementById('incomeChart').getContext('2d');
            new Chart(incomeCtx, {
                type: 'bar',
                data: {
                    labels: data.income.map(i => i.month),
                    datasets: [{
                        label: 'Ingresos (S/)',
                        data: data.income.map(i => i.total),
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { color: '#334155' }, ticks: { color: '#94a3b8' } },
                        x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                    }
                }
            });

            // 3. Status Chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: data.status.map(s => s.service_status),
                    datasets: [{
                        data: data.status.map(s => s.count),
                        backgroundColor: ['#10b981', '#ef4444', '#f59e0b'], // Green, Red, Amber
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'right', labels: { color: '#94a3b8' } } 
                    }
                }
            });

            // 4. Debtors Table
            const tbody = document.getElementById('debtorsTableBody');
            data.debtors.forEach(d => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-700 hover:bg-slate-700/50';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">${d.fullname}</td>
                    <td class="px-6 py-4 text-right text-red-400 font-bold">S/ ${parseFloat(d.debt).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });

        } catch (error) {
            console.error('Error loading reports:', error);
        }
    }

    function exportData(type) {
        document.getElementById('exportDropdown').classList.add('hidden'); // Close dropdown
        if(type === 'debts_excel') {
            window.open('../api/reports.php?action=export_debts', '_blank');
        } else if (type === 'metrics_excel') {
            window.open('../api/reports.php?action=export_metrics_excel', '_blank');
        } else if (type === 'metrics_pdf') {
            window.open('../api/reports.php?action=export_metrics_pdf', '_blank');
        } else if (type === 'payment_logs_pdf') {
            window.open('../api/reports.php?action=export_payment_logs_pdf', '_blank');
        }
    }

    // Close dropdown when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('button') && !event.target.closest('button')) {
            var dropdowns = document.getElementsByClassName("absolute right-0 mt-2 w-64");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (!openDropdown.classList.contains('hidden')) {
                    openDropdown.classList.add('hidden');
                }
            }
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
