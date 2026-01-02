<?php require_once '../../includes/header.php'; ?>
<?php require_once '../../includes/navbar.php'; ?>
<?php require_once '../../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="max-w-7xl mx-auto">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Historial de Instalaciones</h1>
                <p class="text-slate-400 text-sm">Registro de todas las instalaciones realizadas.</p>
            </div>
        </div>

        <!-- Installations Table -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-xl border border-slate-800">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Cliente</th>
                        <th scope="col" class="px-6 py-3">Dirección</th>
                        <th scope="col" class="px-6 py-3">Fecha Completado</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3 text-center">Hoja Instalación</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    <!-- Rows will be populated by JS -->
                    <tr class="bg-slate-900 border-b border-slate-800">
                        <td colspan="6" class="px-6 py-4 text-center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', loadHistory);

    async function loadHistory() {
        try {
            // Fetch completed installations
            const response = await fetch('../../api/installations.php?status=completed');
            const installations = await response.json();
            
            const tbody = document.getElementById('historyTableBody');
            tbody.innerHTML = '';

            if (installations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center">No hay instalaciones completadas.</td></tr>';
                return;
            }

            installations.forEach(inst => {
                const tr = document.createElement('tr');
                tr.className = 'bg-slate-900 border-b border-slate-800 hover:bg-slate-800 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">#${inst.id}</td>
                    <td class="px-6 py-4 text-white">${inst.fullname}</td>
                    <td class="px-6 py-4">${inst.address}</td>
                    <td class="px-6 py-4">${inst.completed_date || '-'}</td>
                    <td class="px-6 py-4">
                        <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs px-2.5 py-0.5 rounded-full font-bold uppercase">
                            ${inst.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="../../api/generate_installation_pdf.php?id=${inst.id}" target="_blank" class="inline-flex items-center justify-center p-2 text-red-400 hover:text-white hover:bg-red-600 rounded-lg transition-all" title="Ver Hoja de Instalación">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </a>
                    </td>
                `;
                tbody.appendChild(tr);
            });

        } catch (error) {
            console.error('Error loading history:', error);
        }
    }
</script>

<?php require_once '../../includes/footer.php'; ?>
