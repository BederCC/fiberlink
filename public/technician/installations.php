<?php require_once '../../includes/header.php'; ?>
<?php require_once '../../includes/navbar.php'; ?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Mis Instalaciones Pendientes</h1>
            <div class="flex items-center gap-2">
                <span class="flex w-3 h-3 bg-amber-500 rounded-full animate-pulse"></span>
                <span class="text-sm text-slate-400">Trabajos por Realizar</span>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="mb-6 flex gap-4">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5" placeholder="Buscar por cliente o dirección...">
            </div>
        </div>

        <!-- Installations Grid -->
        <div id="installationsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Data will be populated here -->
            <div class="col-span-full text-center py-10 text-slate-500">Cargando instalaciones...</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => loadPendingInstallations());

    async function loadPendingInstallations() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        
        try {
            const response = await fetch('../../api/installations.php');
            const installations = await response.json();
            
            const grid = document.getElementById('installationsGrid');
            grid.innerHTML = '';

            // Filter for pending/in_progress and search query
            const filtered = installations.filter(i => 
                (i.status === 'pending' || i.status === 'in_progress') && 
                (i.client_name.toLowerCase().includes(search) || i.address.toLowerCase().includes(search))
            );

            if (filtered.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center py-10 text-slate-500">No tienes instalaciones pendientes.</div>';
                return;
            }

            filtered.forEach(item => {
                const card = document.createElement('div');
                card.className = 'bg-slate-800 border border-slate-700 rounded-xl p-5 hover:border-indigo-500/50 transition-all group';
                
                let statusBadge = item.status === 'in_progress' 
                    ? '<span class="px-2 py-1 rounded-full text-[10px] bg-blue-500/10 text-blue-400">En Proceso</span>'
                    : '<span class="px-2 py-1 rounded-full text-[10px] bg-amber-500/10 text-amber-400">Pendiente</span>';

                card.innerHTML = `
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-slate-900 rounded-lg text-indigo-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        ${statusBadge}
                    </div>
                    <h3 class="text-lg font-bold text-white mb-1">${item.client_name}</h3>
                    <p class="text-xs text-slate-400 mb-4 line-clamp-2">${item.address}</p>
                    
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Plan:</span>
                            <span class="text-slate-300 font-medium">${item.plan_name}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Fecha Prog:</span>
                            <span class="text-slate-300 font-medium">${item.scheduled_date}</span>
                        </div>
                    </div>

                    <a href="installation_simulator.php?id=${item.id}" class="block w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-center text-sm font-semibold rounded-lg transition-colors">
                        Iniciar Instalación
                    </a>
                `;
                grid.appendChild(card);
            });

        } catch (error) {
            console.error('Error loading installations:', error);
        }
    }

    // Search logic
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('keyup', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(loadPendingInstallations, 300);
    });
</script>

<?php require_once '../../includes/footer.php'; ?>
