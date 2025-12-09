<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Servicios Activos (Instalaciones)</h1>
            <button onclick="openModal('serviceModal')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Instalación
            </button>
        </div>

        <!-- Table -->
        <div class="relative overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">Cliente</th>
                        <th scope="col" class="px-6 py-3">Plan</th>
                        <th scope="col" class="px-6 py-3">IP / MAC</th>
                        <th scope="col" class="px-6 py-3">Router</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="servicesTableBody">
                    <!-- Data -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="serviceModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="flex items-start justify-between p-4 border-b border-slate-700 rounded-t">
                <h3 class="text-xl font-semibold text-white">Nueva Instalación</h3>
                <button type="button" onclick="closeModal('serviceModal')" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="serviceId">
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Cliente</label>
                    <select id="clientId" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                        <option value="">Cargando clientes...</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Plan de Internet</label>
                    <select id="planId" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                        <option value="">Cargando planes...</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Dirección IP</label>
                        <input type="text" id="ipAddress" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="192.168.1.10">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">MAC Address</label>
                        <input type="text" id="macAddress" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="AA:BB:CC:DD:EE:FF">
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Modelo Router</label>
                    <input type="text" id="routerModel" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="TP-Link Archer C6">
                </div>
                <button onclick="saveService()" type="button" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Guardar Instalación
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadServices();
        loadClients();
        loadPlans();
    });

    async function loadServices() {
        try {
            const response = await fetch('../api/services.php');
            const services = await response.json();
            const tbody = document.getElementById('servicesTableBody');
            tbody.innerHTML = '';

            services.forEach(service => {
                const tr = document.createElement('tr');
                tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">${service.first_name} ${service.last_name}</td>
                    <td class="px-6 py-4">
                        <span class="bg-indigo-500/10 text-indigo-400 text-xs font-medium px-2.5 py-0.5 rounded">${service.plan_name} (${service.speed_mbps} Mbps)</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span>${service.ip_address || '-'}</span>
                            <span class="text-xs text-slate-500">${service.mac_address || '-'}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">${service.router_model || '-'}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs ${service.service_status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'}">
                            ${service.service_status}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="editService(${service.id})" class="font-medium text-indigo-400 hover:underline">Editar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Error loading services:', error);
        }
    }

    async function loadClients() {
        const response = await fetch('../api/clients.php');
        const clients = await response.json();
        const select = document.getElementById('clientId');
        select.innerHTML = '<option value="">Seleccione Cliente</option>';
        clients.forEach(c => {
            const option = document.createElement('option');
            option.value = c.id;
            option.textContent = `${c.first_name} ${c.last_name}`;
            select.appendChild(option);
        });
    }

    async function loadPlans() {
        const response = await fetch('../api/plans.php');
        const plans = await response.json();
        const select = document.getElementById('planId');
        select.innerHTML = '<option value="">Seleccione Plan</option>';
        plans.forEach(p => {
            const option = document.createElement('option');
            option.value = p.id;
            option.textContent = `${p.name} - S/ ${p.price}`;
            select.appendChild(option);
        });
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        if(modalId === 'serviceModal') {
            document.getElementById('serviceId').value = '';
            // Don't reset other fields if we are editing (handled by editService)
            // But if we are opening fresh, we might want to reset. 
            // For simplicity, we'll let editService handle population and manual clear for new.
            // Better approach:
            if(!document.getElementById('serviceId').value) {
                 document.getElementById('clientId').value = '';
                 document.getElementById('planId').value = '';
                 document.getElementById('ipAddress').value = '';
                 document.getElementById('macAddress').value = '';
                 document.getElementById('routerModel').value = '';
            }
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    async function saveService() {
        const id = document.getElementById('serviceId').value;
        const data = {
            client_id: document.getElementById('clientId').value,
            plan_id: document.getElementById('planId').value,
            ip_address: document.getElementById('ipAddress').value,
            mac_address: document.getElementById('macAddress').value,
            router_model: document.getElementById('routerModel').value
        };

        if (id) {
            data.id = id;
        }

        const method = id ? 'PUT' : 'POST';

        try {
            const response = await fetch('../api/services.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('Servicio guardado exitosamente');
                closeModal('serviceModal');
                loadServices();
                // Clear form
                document.getElementById('serviceId').value = '';
                document.getElementById('clientId').value = '';
                document.getElementById('planId').value = '';
                document.getElementById('ipAddress').value = '';
                document.getElementById('macAddress').value = '';
                document.getElementById('routerModel').value = '';
            } else {
                alert('Error al guardar servicio');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    }

    async function editService(id) {
        try {
            const response = await fetch(`../api/services.php?id=${id}`);
            const service = await response.json();
            
            document.getElementById('serviceId').value = service.id;
            document.getElementById('clientId').value = service.client_id;
            document.getElementById('planId').value = service.plan_id;
            document.getElementById('ipAddress').value = service.ip_address;
            document.getElementById('macAddress').value = service.mac_address;
            document.getElementById('routerModel').value = service.router_model;
            
            openModal('serviceModal');
        } catch (error) {
            console.error('Error fetching service:', error);
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
