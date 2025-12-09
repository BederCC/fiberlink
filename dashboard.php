<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/navbar.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="p-4 sm:ml-64 mt-14">
        <div class="p-4 border border-dashed border-slate-700 rounded-xl">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                    <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors">150</p>
                    <p class="text-sm text-slate-400">Instalaciones Activas</p>
                </div>
                <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                    <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors">24</p>
                    <p class="text-sm text-slate-400">Pendientes de Pago</p>
                </div>
                <div class="flex flex-col items-center justify-center h-32 rounded-xl bg-slate-800/50 border border-slate-700 hover:border-indigo-500/50 transition-all group">
                    <p class="text-3xl font-bold text-white mb-2 group-hover:text-indigo-400 transition-colors">S/ 12,450</p>
                    <p class="text-sm text-slate-400">Ingresos del Mes</p>
                </div>
            </div>

            <!-- Chart Area Placeholder -->
            <div class="flex items-center justify-center h-64 rounded-xl bg-slate-800/50 border border-slate-700 mb-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/5 to-violet-500/5"></div>
                <p class="text-slate-500 z-10">Gráfico de Rendimiento (Próximamente)</p>
            </div>

            <!-- Recent Activity Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="rounded-xl bg-slate-800/50 border border-slate-700 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Últimas Instalaciones</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-slate-300">Juan Pérez</span>
                            <span class="px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs">Completado</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-slate-300">Empresa ABC S.A.C.</span>
                            <span class="px-2 py-1 rounded-full bg-amber-500/10 text-amber-400 text-xs">En Proceso</span>
                        </li>
                        <li class="flex items-center justify-between text-sm">
                            <span class="text-slate-300">María López</span>
                            <span class="px-2 py-1 rounded-full bg-blue-500/10 text-blue-400 text-xs">Agendado</span>
                        </li>
                    </ul>
                </div>
                <div class="rounded-xl bg-slate-800/50 border border-slate-700 p-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Alertas de Sistema</h3>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            <span class="text-slate-300">Pago vencido: Cliente #402</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm">
                            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                            <span class="text-slate-300">Stock bajo: Router Huawei</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-slate-300">Nueva solicitud de instalación</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center justify-center h-24 rounded-xl bg-slate-800/50 border border-slate-700">
                    <p class="text-slate-500">Widget A</p>
                </div>
                <div class="flex items-center justify-center h-24 rounded-xl bg-slate-800/50 border border-slate-700">
                    <p class="text-slate-500">Widget B</p>
                </div>
            </div>
        </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
