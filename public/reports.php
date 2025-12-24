<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Reportes y Métricas</h1>
            <div class="flex gap-2">
                <button onclick="exportData('excel')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Exportar Excel
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
            document.getElementById('avgTime').textContent = data.avg_payment_time;

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
        if(type === 'excel') {
            window.open('../api/reports.php?action=export_debts', '_blank');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
