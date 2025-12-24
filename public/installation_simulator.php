<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Instalaciones - FiberLink</title>
    <link href="../src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-50 min-h-screen p-4">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-800 pb-6">
            <div>
                <h1 class="text-3xl font-bold text-white bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400">
                    FiberLink Técnico
                </h1>
                <p class="text-slate-400 text-sm mt-1">Simulador de Instalaciones en Campo</p>
            </div>
            <div class="flex items-center gap-3 bg-slate-900 px-4 py-2 rounded-full border border-slate-800">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-sm font-medium text-slate-300">Sistema Online</span>
            </div>
        </div>

        <!-- Pending Installations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="installationsGrid">
            <!-- Cards will be populated here -->
        </div>

        <div id="emptyState" class="hidden flex flex-col items-center justify-center py-20 text-slate-500">
            <div class="bg-slate-900 p-6 rounded-full mb-4">
                <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-xl font-medium text-slate-400 mb-2">Todo al día</h3>
            <p class="text-slate-600">No hay instalaciones pendientes asignadas.</p>
        </div>
    </div>

    <!-- Complete Modal -->
    <div id="completeModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-950/90 backdrop-blur-sm flex items-center justify-center">
        <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-slate-900 rounded-2xl shadow-2xl border border-slate-800 overflow-hidden">
                <div class="bg-indigo-600/10 p-6 border-b border-indigo-500/20">
                    <h3 class="text-xl font-bold text-white">Finalizar Instalación</h3>
                    <p class="text-indigo-300 text-sm mt-1">Confirme los detalles del servicio</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <input type="hidden" id="installId">
                    
                    <div>
                        <label class="block mb-2 text-sm font-medium text-slate-300">Notas Técnicas</label>
                        <textarea id="installNotes" rows="4" class="bg-slate-950 border border-slate-700 text-white text-sm rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block w-full p-3" placeholder="Detalles de la instalación, potencia óptica, observaciones..."></textarea>
                    </div>

                    <div class="bg-slate-950 rounded-lg p-4 border border-slate-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs text-slate-400">
                                Al confirmar, el servicio se activará automáticamente y comenzará la facturación para el cliente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center p-6 space-x-3 border-t border-slate-800 bg-slate-900/50">
                    <button onclick="confirmCompletion()" type="button" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-800 font-medium rounded-xl text-sm px-5 py-3 text-center transition-all shadow-lg shadow-indigo-500/20">
                        Confirmar Instalación
                    </button>
                    <button onclick="closeModal('completeModal')" type="button" class="w-full text-slate-300 bg-transparent hover:bg-slate-800 focus:ring-4 focus:outline-none focus:ring-slate-700 rounded-xl border border-slate-700 text-sm font-medium px-5 py-3 hover:text-white focus:z-10 transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', loadInstallations);
        
        // Poll for updates
        setInterval(loadInstallations, 3000);

        let lastInstallationsHash = '';

        async function loadInstallations() {
            try {
                const response = await fetch('../api/installations.php');
                const installations = await response.json();
                
                // Check if data changed
                const currentHash = JSON.stringify(installations);
                if (currentHash === lastInstallationsHash) return;
                lastInstallationsHash = currentHash;

                const grid = document.getElementById('installationsGrid');
                const empty = document.getElementById('emptyState');
                
                grid.innerHTML = '';
                
                if (installations.length === 0) {
                    empty.classList.remove('hidden');
                } else {
                    empty.classList.add('hidden');
                    installations.forEach(inst => {
                        const isPending = inst.status === 'pending';
                        const isInProgress = inst.status === 'in_progress';
                        
                        let statusLabel = 'Desconocido';
                        let statusClass = 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                        
                        if (isPending) {
                            statusLabel = 'Pendiente';
                            statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
                        } else if (isInProgress) {
                            statusLabel = 'En Progreso';
                            statusClass = 'bg-blue-500/10 text-blue-400 border-blue-500/20';
                        }
                        
                        const actionButton = isPending 
                            ? `<button onclick="startInstallation(${inst.id})" class="mt-auto w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-blue-900/20 flex items-center justify-center gap-2 group-hover:shadow-blue-500/20">
                                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                   Iniciar Instalación
                               </button>`
                            : `<button onclick="openCompleteModal(${inst.id})" class="mt-auto w-full bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-xl transition-all shadow-lg shadow-emerald-900/20 flex items-center justify-center gap-2 group-hover:shadow-emerald-500/20">
                                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                   Completar Instalación
                               </button>`;

                        const card = document.createElement('div');
                        card.className = 'bg-slate-900 rounded-2xl border border-slate-800 p-6 flex flex-col gap-5 hover:border-indigo-500/50 transition-all duration-300 group shadow-lg shadow-black/20';
                        card.innerHTML = `
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-bold text-white text-lg group-hover:text-indigo-400 transition-colors">${inst.fullname}</h3>
                                    <p class="text-slate-400 text-sm mt-1 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        ${inst.address}
                                    </p>
                                </div>
                                <span class="${statusClass} text-xs px-3 py-1 rounded-full uppercase font-bold tracking-wider border">${statusLabel}</span>
                            </div>
                            
                            <div class="space-y-3 text-sm text-slate-300 bg-slate-950/50 p-4 rounded-xl border border-slate-800/50">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Plan Contratado</span>
                                    <span class="font-medium text-indigo-300">${inst.plan_name}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Contacto</span>
                                    <span class="font-mono">${inst.phone || '-'}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Router</span>
                                    <span>${inst.router_model || 'N/A'}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">IP Asignada</span>
                                    <span class="font-mono text-xs bg-slate-800 px-2 py-0.5 rounded">${inst.ip_address || 'DHCP'}</span>
                                </div>
                            </div>

                            ${actionButton}
                        `;
                        grid.appendChild(card);
                    });
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function startInstallation(id) {
            if(!confirm('¿Iniciar el proceso de instalación para este cliente?')) return;
            
            try {
                const response = await fetch('../api/installations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, action: 'start' })
                });

                if (response.ok) {
                    loadInstallations();
                } else {
                    alert('Error al iniciar instalación.');
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión');
            }
        }

        function openCompleteModal(id) {
            document.getElementById('installId').value = id;
            document.getElementById('installNotes').value = 'Instalación exitosa. Potencia óptica -22dBm. Equipos configurados correctamente.';
            document.getElementById('completeModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        async function confirmCompletion() {
            const id = document.getElementById('installId').value;
            const notes = document.getElementById('installNotes').value;
            const btn = document.querySelector('#completeModal button[onclick="confirmCompletion()"]');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Procesando...';

            try {
                const response = await fetch('../api/installations.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, notes, action: 'complete' })
                });

                if (response.ok) {
                    // alert('Instalación registrada y servicio activado.');
                    closeModal('completeModal');
                    loadInstallations();
                } else {
                    alert('Error al procesar instalación.');
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
