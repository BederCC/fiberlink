<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <h1 class="text-2xl font-bold text-white text-center sm:text-left">Servicios Activos (Instalaciones)</h1>
            <button onclick="openNewServiceModal()" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center justify-center gap-2">
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
                <div class="relative">
                    <label class="block mb-2 text-sm font-medium text-white">Cliente</label>
                    <input type="hidden" id="clientId">
                    <input type="text" id="clientSearch" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="Buscar por nombre o DNI..." autocomplete="off">
                    <div id="clientSearchResults" class="absolute z-10 w-full bg-slate-800 border border-slate-600 rounded-lg mt-1 max-h-48 overflow-y-auto hidden shadow-lg">
                        <!-- Results -->
                    </div>
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
                <!-- Installation Costs -->
                <div class="border-t border-slate-700 pt-4 mt-4">
                    <h4 class="text-white font-medium mb-3">Facturación de Instalación</h4>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-white">Costo Mano de Obra (S/)</label>
                            <input type="number" id="installCost" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="50.00" value="0">
                        </div>
                        <div class="flex items-center pt-6">
                            <input id="firstMonth" type="checkbox" class="w-4 h-4 text-indigo-600 bg-slate-700 border-slate-600 rounded focus:ring-indigo-600 ring-offset-slate-800">
                            <label for="firstMonth" class="ml-2 text-sm font-medium text-slate-300">Cobrar Primer Mes</label>
                        </div>
                    </div>
                </div>

                <!-- Equipment -->
                <div class="border-t border-slate-700 pt-4 mt-4">
                    <h4 class="text-white font-medium mb-3">Equipos Utilizados</h4>
                    <div id="productsContainer" class="space-y-2 mb-2">
                        <!-- Dynamic Product Rows -->
                    </div>
                    <button id="btnAddProduct" type="button" onclick="addProductRow()" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Agregar Equipo
                    </button>
                </div>

                <button onclick="saveService()" type="button" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center mt-4">
                    Guardar Instalación
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadServices();

        loadPlans();
        loadProductsList();
    });

    let availableProducts = [];

    async function loadProductsList() {
        try {
            const response = await fetch('../api/products.php');
            availableProducts = await response.json();
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    function addProductRow() {
        const container = document.getElementById('productsContainer');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center product-row';
        
        let options = '<option value="">Seleccionar Equipo</option>';
        availableProducts.forEach(p => {
            options += `<option value="${p.id}" data-price="${p.price}">${p.name} (S/ ${p.price})</option>`;
        });

        div.innerHTML = `
            <select class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5 product-select">
                ${options}
            </select>
            <input type="number" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-20 p-2.5 product-qty" placeholder="Cant." value="1" min="1">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        `;
        container.appendChild(div);
    }

    // Poll for updates
    setInterval(loadServices, 3000);

    let lastServicesHash = '';

    async function loadServices() {
        try {
            const response = await fetch('../api/services.php?_=' + new Date().getTime());
            const services = await response.json();
            
            // Check if data changed to avoid unnecessary re-renders
            const currentHash = JSON.stringify(services);
            if (currentHash === lastServicesHash) return;
            lastServicesHash = currentHash;

            const tbody = document.getElementById('servicesTableBody');
            tbody.innerHTML = '';

            services.forEach(service => {
                const tr = document.createElement('tr');
                tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">${service.fullname}</td>
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
                        <span class="px-2 py-1 rounded-full text-xs ${service.service_status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : (service.service_status === 'pending' ? 'bg-amber-500/10 text-amber-400' : (service.service_status === 'in_progress' ? 'bg-blue-500/10 text-blue-400' : 'bg-red-500/10 text-red-400'))}">
                            ${service.service_status === 'active' ? 'Activo' : (service.service_status === 'pending' ? 'Pendiente' : (service.service_status === 'in_progress' ? 'En Progreso' : 'Cortado'))}
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

    // Client Search Logic
    const clientSearchInput = document.getElementById('clientSearch');
    const clientSearchResults = document.getElementById('clientSearchResults');

    clientSearchInput.addEventListener('input', debounce(async (e) => {
        const term = e.target.value;
        if (term.length < 2) {
            clientSearchResults.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`../api/clients.php?search=${encodeURIComponent(term)}&limit=5`);
            const data = await response.json();
            const clients = data.data || []; // Handle pagination structure

            clientSearchResults.innerHTML = '';
            if (clients.length > 0) {
                clients.forEach(client => {
                    const div = document.createElement('div');
                    div.className = 'p-2 hover:bg-slate-700 cursor-pointer text-sm text-slate-300 border-b border-slate-700 last:border-0';
                    div.innerHTML = `<span class="font-bold text-white">${client.fullname}</span> <span class="text-xs">(${client.dni_ruc})</span>`;
                    div.onclick = () => {
                        document.getElementById('clientId').value = client.id;
                        clientSearchInput.value = client.fullname;
                        clientSearchResults.classList.add('hidden');
                    };
                    clientSearchResults.appendChild(div);
                });
                clientSearchResults.classList.remove('hidden');
            } else {
                clientSearchResults.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error searching clients:', error);
        }
    }, 300));

    // Close search results when clicking outside
    document.addEventListener('click', (e) => {
        if (!clientSearchInput.contains(e.target) && !clientSearchResults.contains(e.target)) {
            clientSearchResults.classList.add('hidden');
        }
    });

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Random IP & MAC Generator
    function generateRandomIP() {
        return `192.168.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 253) + 2}`;
    }

    function generateRandomMAC() {
        const hex = "0123456789ABCDEF";
        let mac = "";
        for (let i = 0; i < 6; i++) {
            mac += hex.charAt(Math.floor(Math.random() * 16));
            mac += hex.charAt(Math.floor(Math.random() * 16));
            if (i < 5) mac += ":";
        }
        return mac;
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
    }

    function openNewServiceModal() {
        // Reset fields
        document.getElementById('serviceId').value = '';
        document.getElementById('clientId').value = '';
        document.getElementById('clientSearch').value = '';
        document.getElementById('planId').value = '';
        document.getElementById('ipAddress').value = generateRandomIP();
        document.getElementById('macAddress').value = generateRandomMAC();
        document.getElementById('routerModel').value = '';
        document.getElementById('installCost').value = '0';
        document.getElementById('firstMonth').checked = false;
        document.getElementById('productsContainer').innerHTML = '';
        
        // Enable fields
        document.getElementById('installCost').disabled = false;
        document.getElementById('firstMonth').disabled = false;
        document.getElementById('btnAddProduct').style.display = 'flex';

        openModal('serviceModal');
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
            router_model: document.getElementById('routerModel').value,
            installation_cost: parseFloat(document.getElementById('installCost').value) || 0,
            include_first_month: document.getElementById('firstMonth').checked,
            products: []
        };

        // Collect products
        document.querySelectorAll('.product-row').forEach(row => {
            const select = row.querySelector('.product-select');
            const qty = row.querySelector('.product-qty');
            if(select.value && qty.value) {
                data.products.push({
                    id: select.value,
                    quantity: parseInt(qty.value)
                });
            }
        });

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
                document.getElementById('clientSearch').value = '';
                document.getElementById('planId').value = '';
                document.getElementById('ipAddress').value = '';
                document.getElementById('macAddress').value = '';
                document.getElementById('routerModel').value = '';
                document.getElementById('installCost').value = '0';
                document.getElementById('firstMonth').checked = false;
                document.getElementById('productsContainer').innerHTML = '';
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
            document.getElementById('clientSearch').value = service.fullname; // Set name in search input
            document.getElementById('planId').value = service.plan_id;
            document.getElementById('ipAddress').value = service.ip_address;
            document.getElementById('macAddress').value = service.mac_address;
            document.getElementById('routerModel').value = service.router_model;
            
            // Populate Installation Details
            document.getElementById('installCost').value = service.installation_cost || 0;
            document.getElementById('firstMonth').checked = service.include_first_month == 1;

            // Lock fields if not pending or in_progress
            const isEditable = service.service_status === 'pending' || service.service_status === 'in_progress';
            
            document.getElementById('installCost').disabled = !isEditable;
            document.getElementById('firstMonth').disabled = !isEditable;
            document.getElementById('btnAddProduct').style.display = isEditable ? 'flex' : 'none';

            // Populate Products
            const container = document.getElementById('productsContainer');
            container.innerHTML = '';
            
            if (service.products && service.products.length > 0) {
                service.products.forEach(prod => {
                    const div = document.createElement('div');
                    div.className = 'flex gap-2 items-center product-row';
                    
                    let options = '<option value="">Seleccionar Equipo</option>';
                    availableProducts.forEach(p => {
                        const selected = p.id == prod.id ? 'selected' : '';
                        options += `<option value="${p.id}" data-price="${p.price}" ${selected}>${p.name} (S/ ${p.price})</option>`;
                    });

                    const disabledAttr = !isEditable ? 'disabled' : '';
                    const deleteBtn = isEditable ? `
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>` : '';

                    div.innerHTML = `
                        <select class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5 product-select" ${disabledAttr}>
                            ${options}
                        </select>
                        <input type="number" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-20 p-2.5 product-qty" placeholder="Cant." value="${prod.quantity}" min="1" ${disabledAttr}>
                        ${deleteBtn}
                    `;
                    container.appendChild(div);
                });
            }
            
            openModal('serviceModal');
        } catch (error) {
            console.error('Error fetching service:', error);
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
