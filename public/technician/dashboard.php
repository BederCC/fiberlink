<?php require_once '../../includes/header.php'; ?>
<?php require_once '../../includes/navbar.php'; ?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white mb-2">Panel Técnico</h1>
            <p class="text-slate-400">Bienvenido a tu espacio de trabajo.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors" id="pendingCount">-</p>
                <p class="text-sm text-slate-400">Instalaciones Pendientes</p>
            </div>
            <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors" id="completedCount">-</p>
                <p class="text-sm text-slate-400">Completadas Hoy</p>
            </div>
            <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors">Online</p>
                <p class="text-sm text-slate-400">Estado del Sistema</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="installation_simulator.php" class="block p-6 bg-slate-800 rounded-xl border border-slate-700 hover:bg-slate-750 hover:border-indigo-500 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-indigo-500/10 rounded-lg text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-1">Simulador de Instalaciones</h3>
                        <p class="text-slate-400 text-sm">Acceder al módulo de gestión de instalaciones en campo.</p>
                    </div>
                </div>
            </a>
            
            <div class="block p-6 bg-slate-800 rounded-xl border border-slate-700 opacity-50 cursor-not-allowed">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-slate-700 rounded-lg text-slate-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-1">Reportes Técnicos</h3>
                        <p class="text-slate-400 text-sm">Próximamente disponible.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Simple fetch to populate stats (mocked for now or reuse existing APIs)
    document.addEventListener('DOMContentLoaded', async () => {
        try {
            const response = await fetch('../../api/installations.php');
            const installations = await response.json();
            
            const pending = installations.filter(i => i.status === 'pending' || i.status === 'in_progress').length;
            const completed = installations.filter(i => i.status === 'completed').length; // Ideally filter by date too

            document.getElementById('pendingCount').textContent = pending;
            document.getElementById('completedCount').textContent = completed;
        } catch (e) {
            console.error(e);
        }
    });
</script>

<?php require_once '../../includes/footer.php'; ?>
