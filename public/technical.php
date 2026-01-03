<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Operaciones Técnicas (Simulación)</h1>
            <div class="flex items-center gap-2">
                <span class="flex w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-sm text-slate-400">RouterOS: Conectado (Simulado)</span>
            </div>
        </div>

        <!-- Network Status Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Traffic Graph -->
            <div class="lg:col-span-2 p-4 bg-slate-800 rounded-xl border border-slate-700 flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-white">Tráfico de Red en Tiempo Real</h3>
                    <div class="flex gap-4 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-indigo-500"></span>
                            <span class="text-slate-400">Download</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                            <span class="text-slate-400">Upload</span>
                        </div>
                    </div>
                </div>
                <div class="flex-1 w-full bg-slate-900/50 rounded-lg relative overflow-hidden p-2">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>

            <!-- Stats -->
            <div class="space-y-4">
                <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                    <p class="text-slate-400 text-sm">Servicios Activos</p>
                    <p class="text-2xl font-bold text-emerald-400" id="activeCount">0</p>
                </div>
                <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                    <p class="text-slate-400 text-sm">Servicios Cortados</p>
                    <p class="text-2xl font-bold text-red-400" id="suspendedCount">0</p>
                </div>
                <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                    <p class="text-slate-400 text-sm">Carga de CPU (Router)</p>
                    <p class="text-2xl font-bold text-indigo-400" id="cpuLoad">12%</p>
                </div>
            </div>
        </div>

        <!-- Services Control Table -->
        <div class="relative overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">Cliente</th>
                        <th scope="col" class="px-6 py-3">IP Address</th>
                        <th scope="col" class="px-6 py-3">Plan</th>
                        <th scope="col" class="px-6 py-3">Estado Router</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="techTableBody">
                    <!-- Data -->
                </tbody>
            </table>
        </div>

        <!-- Service Cutoff Countdown -->
        <div class="mt-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-amber-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Próximos Cortes de Servicio</h3>
            </div>
            
            <div class="relative overflow-x-auto rounded-lg border border-slate-700">
                <table class="w-full text-sm text-left text-slate-400">
                    <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                        <tr>
                            <th scope="col" class="px-6 py-3">Cliente</th>
                            <th scope="col" class="px-6 py-3">Dirección</th>
                            <th scope="col" class="px-6 py-3">Vencimiento</th>
                            <th scope="col" class="px-6 py-3">Monto</th>
                            <th scope="col" class="px-6 py-3">Tiempo Restante</th>
                            <th scope="col" class="px-6 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="cutoffTableBody">
                        <!-- Data -->
                    </tbody>
                </table>
            </div>
        </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadServices();
        loadCutoffCandidates();
        initTrafficChart();
    });

    async function loadCutoffCandidates() {
        try {
            const response = await fetch('../api/cutoff_candidates.php');
            const candidates = await response.json();
            const tbody = document.getElementById('cutoffTableBody');
            tbody.innerHTML = '';

            if (candidates.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-slate-500">No hay cortes programados próximos.</td></tr>';
                return;
            }

            candidates.forEach(c => {
                const tr = document.createElement('tr');
                tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors';
                
                let timeText = '';
                let timeClass = '';
                let statusBadge = '';

                if (c.days_remaining > 0) {
                    timeText = `${c.days_remaining} días`;
                    timeClass = 'text-amber-400 font-bold';
                    statusBadge = '<span class="px-2 py-1 rounded-full text-xs bg-amber-500/10 text-amber-400">Por Vencer</span>';
                } else if (c.days_remaining === 0) {
                    timeText = 'HOY';
                    timeClass = 'text-orange-500 font-bold animate-pulse';
                    statusBadge = '<span class="px-2 py-1 rounded-full text-xs bg-orange-500/10 text-orange-400">Vence Hoy</span>';
                } else {
                    timeText = `Vencido hace ${Math.abs(c.days_remaining)} días`;
                    timeClass = 'text-red-500 font-bold';
                    statusBadge = '<span class="px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400">Corte Programado</span>';
                }

                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">${c.client}</td>
                    <td class="px-6 py-4 text-xs">${c.address}</td>
                    <td class="px-6 py-4">${c.due_date}</td>
                    <td class="px-6 py-4 font-mono">S/ ${parseFloat(c.amount).toFixed(2)}</td>
                    <td class="px-6 py-4 ${timeClass}">${timeText}</td>
                    <td class="px-6 py-4">${statusBadge}</td>
                `;
                tbody.appendChild(tr);
            });

        } catch (error) {
            console.error('Error loading cutoff candidates:', error);
        }
    }

    async function loadServices() {
        try {
            const response = await fetch('../api/services.php');
            const services = await response.json();
            const tbody = document.getElementById('techTableBody');
            tbody.innerHTML = '';

            let active = 0, suspended = 0;

            services.forEach(service => {
                if(service.service_status === 'active') active++;
                else suspended++;

                const tr = document.createElement('tr');
                tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors';
                
                let statusBadge = service.service_status === 'active' 
                    ? '<span class="px-2 py-1 rounded-full text-xs bg-emerald-500/10 text-emerald-400">Permitido</span>'
                    : '<span class="px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400">Bloqueado</span>';

                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">${service.fullname}</td>
                    <td class="px-6 py-4 font-mono text-xs">${service.ip_address}</td>
                    <td class="px-6 py-4">${service.plan_name}</td>
                    <td class="px-6 py-4">${statusBadge}</td>
                    <td class="px-6 py-4">
                        ${service.service_status === 'active' 
                            ? `<button onclick="toggleService(${service.id}, 'cut')" class="text-red-400 hover:text-red-300 font-medium border border-red-400 hover:bg-red-400/10 rounded px-3 py-1 transition-colors">Cortar</button>`
                            : `<button onclick="toggleService(${service.id}, 'restore')" class="text-emerald-400 hover:text-emerald-300 font-medium border border-emerald-400 hover:bg-emerald-400/10 rounded px-3 py-1 transition-colors">Reponer</button>`
                        }
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('activeCount').textContent = active;
            document.getElementById('suspendedCount').textContent = suspended;

        } catch (error) {
            console.error('Error loading services:', error);
        }
    }

    async function toggleService(id, action) {
        if(!confirm(`¿Estás seguro de que deseas ${action === 'cut' ? 'CORTAR' : 'REPONER'} este servicio?`)) return;

        try {
            const response = await fetch('../api/router_actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ service_id: id, action: action })
            });

            const data = await response.json();
            if(response.ok) {
                alert(data.message);
                loadServices();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    }

    function initTrafficChart() {
        const canvas = document.getElementById('trafficChart');
        const ctx = canvas.getContext('2d');
        
        // Resize canvas
        function resize() {
            canvas.width = canvas.parentElement.clientWidth;
            canvas.height = canvas.parentElement.clientHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        // Data
        const maxPoints = 100;
        const downloadData = new Array(maxPoints).fill(0);
        const uploadData = new Array(maxPoints).fill(0);

        function draw() {
            const w = canvas.width;
            const h = canvas.height;
            
            ctx.clearRect(0, 0, w, h);
            
            // Grid
            ctx.strokeStyle = '#334155';
            ctx.lineWidth = 1;
            ctx.beginPath();
            for(let i=0; i<5; i++) {
                const y = h - (h/4)*i;
                ctx.moveTo(0, y);
                ctx.lineTo(w, y);
            }
            ctx.stroke();

            // Draw Line Function
            function drawLine(data, color, fillColor) {
                ctx.beginPath();
                ctx.moveTo(0, h);
                
                const step = w / (maxPoints - 1);
                const maxVal = 100; // Max Mbps simulated

                for(let i=0; i<data.length; i++) {
                    const x = i * step;
                    const y = h - (data[i] / maxVal) * h;
                    ctx.lineTo(x, y);
                }
                
                ctx.lineTo(w, h);
                ctx.closePath();
                
                // Gradient Fill
                const gradient = ctx.createLinearGradient(0, 0, 0, h);
                gradient.addColorStop(0, fillColor);
                gradient.addColorStop(1, 'transparent');
                ctx.fillStyle = gradient;
                ctx.fill();

                // Stroke
                ctx.beginPath();
                for(let i=0; i<data.length; i++) {
                    const x = i * step;
                    const y = h - (data[i] / maxVal) * h;
                    if(i===0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                }
                ctx.strokeStyle = color;
                ctx.lineWidth = 2;
                ctx.stroke();
            }

            drawLine(downloadData, '#6366f1', 'rgba(99, 102, 241, 0.2)'); // Indigo
            drawLine(uploadData, '#10b981', 'rgba(16, 185, 129, 0.2)');   // Emerald
        }

        // Simulation Loop
        setInterval(() => {
            // Shift data
            downloadData.shift();
            uploadData.shift();
            
            // New random points (Simulating traffic)
            // Download usually higher than upload
            const dl = Math.random() * 60 + 10; // 10-70 Mbps
            const ul = Math.random() * 20 + 5;  // 5-25 Mbps
            
            downloadData.push(dl);
            uploadData.push(ul);
            
            draw();
            
            // Update CPU
            document.getElementById('cpuLoad').textContent = Math.floor(Math.random() * 30) + 5 + '%';
        }, 500);
    }
</script>

<?php require_once '../includes/footer.php'; ?>
