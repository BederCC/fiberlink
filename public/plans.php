<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Planes de Internet</h1>
            <button onclick="openModal('planModal')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Plan
            </button>
        </div>

        <!-- Grid of Plans -->
        <div id="plansGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Plans will be populated here -->
        </div>
    </div>
</div>

<!-- Modal -->
<div id="planModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="flex items-start justify-between p-4 border-b border-slate-700 rounded-t">
                <h3 class="text-xl font-semibold text-white" id="modalTitle">
                    Nuevo Plan
                </h3>
                <button type="button" onclick="closeModal('planModal')" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Cerrar modal</span>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <form id="planForm" class="space-y-4">
                    <input type="hidden" id="planId">
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-white">Nombre del Plan</label>
                        <input type="text" id="name" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" placeholder="Ej. Fibra 50Mbps" required>
                    </div>
                    <div>
                        <label for="speed_mbps" class="block mb-2 text-sm font-medium text-white">Velocidad (Mbps)</label>
                        <input type="number" id="speed_mbps" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    <div>
                        <label for="price" class="block mb-2 text-sm font-medium text-white">Precio Mensual (S/)</label>
                        <input type="number" step="0.01" id="price" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    <div>
                        <label for="description" class="block mb-2 text-sm font-medium text-white">Descripción</label>
                        <textarea id="description" rows="3" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5"></textarea>
                    </div>
                </form>
            </div>
            <div class="flex items-center p-6 space-x-2 border-t border-slate-700 rounded-b">
                <button onclick="savePlan()" type="button" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Guardar</button>
                <button onclick="closeModal('planModal')" type="button" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg border border-slate-600 text-sm font-medium px-5 py-2.5 hover:text-white focus:z-10">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', loadPlans);

    async function loadPlans() {
        try {
            const response = await fetch('../api/plans.php');
            const plans = await response.json();
            const grid = document.getElementById('plansGrid');
            grid.innerHTML = '';

            plans.forEach(plan => {
                const card = document.createElement('div');
                card.className = 'bg-slate-800 border border-slate-700 rounded-xl p-6 hover:border-indigo-500/50 transition-all group relative overflow-hidden';
                card.innerHTML = `
                    <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                        <button onclick="editPlan(${plan.id})" class="text-indigo-400 hover:text-indigo-300 bg-slate-900/50 p-2 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                        <button onclick="deletePlan(${plan.id})" class="text-red-400 hover:text-red-300 bg-slate-900/50 p-2 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">${plan.name}</h3>
                        <span class="px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 text-sm font-medium">${plan.speed_mbps} Mbps</span>
                    </div>
                    <p class="text-3xl font-bold text-white mb-2">S/ ${parseFloat(plan.price).toFixed(2)}<span class="text-sm text-slate-400 font-normal">/mes</span></p>
                    <p class="text-slate-400 text-sm mb-4">${plan.description || 'Sin descripción'}</p>
                    <div class="w-full bg-slate-700 h-1 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-violet-500 h-full w-full"></div>
                    </div>
                `;
                grid.appendChild(card);
            });
        } catch (error) {
            console.error('Error loading plans:', error);
        }
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById('planForm').reset();
        document.getElementById('planId').value = '';
        document.getElementById('modalTitle').textContent = 'Nuevo Plan';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    async function savePlan() {
        const id = document.getElementById('planId').value;
        const data = {
            name: document.getElementById('name').value,
            speed_mbps: document.getElementById('speed_mbps').value,
            price: document.getElementById('price').value,
            description: document.getElementById('description').value
        };

        if (id) {
            data.id = id;
        }

        const method = id ? 'PUT' : 'POST';
        
        try {
            const response = await fetch('../api/plans.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                closeModal('planModal');
                loadPlans();
                alert('Plan guardado exitosamente');
            } else {
                alert('Error al guardar plan');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    }

    async function editPlan(id) {
        try {
            const response = await fetch(`../api/plans.php?id=${id}`);
            const plan = await response.json();
            
            document.getElementById('planId').value = plan.id;
            document.getElementById('name').value = plan.name;
            document.getElementById('speed_mbps').value = plan.speed_mbps;
            document.getElementById('price').value = plan.price;
            document.getElementById('description').value = plan.description;
            
            document.getElementById('modalTitle').textContent = 'Editar Plan';
            document.getElementById('planModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error fetching plan:', error);
        }
    }

    async function deletePlan(id) {
        if(confirm('¿Estás seguro de eliminar este plan?')) {
            try {
                const response = await fetch('../api/plans.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                if (response.ok) {
                    loadPlans();
                } else {
                    alert('Error al eliminar');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
