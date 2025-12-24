<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Gestión de Clientes</h1>
            <button onclick="openModal('clientModal')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Cliente
            </button>
        </div>

        <!-- Search & Filter -->
        <div class="mb-6 flex gap-4">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" class="bg-slate-800 border border-slate-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5" placeholder="Buscar por nombre, DNI o teléfono...">
            </div>
        </div>

        <!-- Table -->
        <div class="relative overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">Cliente</th>
                        <th scope="col" class="px-6 py-3">DNI/RUC</th>
                        <th scope="col" class="px-6 py-3">Contacto</th>
                        <th scope="col" class="px-6 py-3">Dirección</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody">
                    <!-- Data will be populated here -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex flex-col items-center justify-between mt-4 gap-4 sm:flex-row" id="paginationControls">
            <span class="text-sm text-slate-400">
                Mostrando <span class="font-semibold text-white" id="showingStart">0</span> a <span class="font-semibold text-white" id="showingEnd">0</span> de <span class="font-semibold text-white" id="totalRecords">0</span> entradas
            </span>
            <nav aria-label="Page navigation">
                <ul class="inline-flex -space-x-px text-sm" id="paginationNumbers">
                    <!-- Dynamic content -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="clientModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-2xl max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="flex items-start justify-between p-4 border-b border-slate-700 rounded-t">
                <h3 class="text-xl font-semibold text-white" id="modalTitle">
                    Nuevo Cliente
                </h3>
                <button type="button" onclick="closeModal('clientModal')" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Cerrar modal</span>
                </button>
            </div>
            <div class="p-6 space-y-6">
                <form id="clientForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" id="clientId">
                    <div>
                        <label for="fullname" class="block mb-2 text-sm font-medium text-white">Nombres y Apellidos</label>
                        <input type="text" id="fullname" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    <div>
                        <label for="dni_ruc" class="block mb-2 text-sm font-medium text-white">DNI / RUC</label>
                        <input type="text" id="dni_ruc" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    <div>
                        <label for="phone" class="block mb-2 text-sm font-medium text-white">Teléfono</label>
                        <input type="text" id="phone" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    </div>
                    <div class="md:col-span-2">
                        <label for="email" class="block mb-2 text-sm font-medium text-white">Email</label>
                        <input type="email" id="email" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="block mb-2 text-sm font-medium text-white">Dirección</label>
                        <textarea id="address" rows="3" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label for="coordinates" class="block mb-2 text-sm font-medium text-white">Coordenadas (Lat, Long)</label>
                        <input type="text" id="coordinates" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" placeholder="-12.046374, -77.042793">
                    </div>
                </form>
            </div>
            <div class="flex items-center p-6 space-x-2 border-t border-slate-700 rounded-b">
                <button onclick="saveClient()" type="button" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Guardar</button>
                <button onclick="closeModal('clientModal')" type="button" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg border border-slate-600 text-sm font-medium px-5 py-2.5 hover:text-white focus:z-10">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentLimit = 10;
    let totalPages = 1;

    document.addEventListener('DOMContentLoaded', () => loadClients());

    async function loadClients(page = 1) {
        const search = document.getElementById('searchInput').value;
        currentPage = page;
        
        try {
            const response = await fetch(`../api/clients.php?page=${page}&limit=${currentLimit}&search=${encodeURIComponent(search)}`);
            const result = await response.json();
            
            const clients = result.data;
            const pagination = result.pagination;
            totalPages = pagination.total_pages;

            const tbody = document.getElementById('clientsTableBody');
            tbody.innerHTML = '';

            if (clients.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-slate-500">No se encontraron clientes</td></tr>';
            }

            clients.forEach(client => {
                const tr = document.createElement('tr');
                tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white whitespace-nowrap">
                        ${client.fullname}
                    </td>
                    <td class="px-6 py-4">${client.dni_ruc}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span>${client.phone || '-'}</span>
                            <span class="text-xs text-slate-500">${client.email || ''}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 truncate max-w-xs" title="${client.address}">${client.address}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs ${client.status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'}">
                            ${client.status === 'active' ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="editClient(${client.id})" class="font-medium text-indigo-400 hover:underline mr-3">Editar</button>
                        <button onclick="deleteClient(${client.id})" class="font-medium text-red-400 hover:underline">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Update Pagination UI
            const start = (pagination.current_page - 1) * pagination.limit + 1;
            const end = Math.min(start + clients.length - 1, pagination.total_records);
            
            document.getElementById('showingStart').textContent = clients.length > 0 ? start : 0;
            document.getElementById('showingEnd').textContent = end;
            document.getElementById('totalRecords').textContent = pagination.total_records;

            renderPagination(pagination);

        } catch (error) {
            console.error('Error loading clients:', error);
        }
    }

    function renderPagination(pagination) {
        const container = document.getElementById('paginationNumbers');
        container.innerHTML = '';
        
        const total = pagination.total_pages;
        const current = pagination.current_page;
        
        if (total <= 1) return;

        // Helper to create button
        const addBtn = (page, text, active = false, disabled = false, roundedL = false, roundedR = false) => {
            const li = document.createElement('li');
            const btn = document.createElement('button');
            
            let classes = "flex items-center justify-center px-3 h-8 leading-tight border border-slate-700 ";
            
            if (active) {
                classes += "text-white bg-indigo-600 hover:bg-indigo-700 hover:text-white ";
            } else {
                classes += "text-slate-400 bg-slate-800 hover:bg-slate-700 hover:text-white ";
            }
            
            if (disabled) {
                classes += "cursor-not-allowed opacity-50 ";
            }
            
            if (roundedL) classes += "rounded-l-lg ";
            if (roundedR) classes += "rounded-r-lg ";
            
            btn.className = classes;
            btn.innerHTML = text;
            
            if (!disabled && page !== null) {
                btn.onclick = () => loadClients(page);
            }
            
            li.appendChild(btn);
            container.appendChild(li);
        };

        // Prev
        addBtn(current - 1, 'Anterior', false, current === 1, true, false);

        // Pages logic
        const delta = 2;
        const range = [];
        const rangeWithDots = [];
        let l;

        range.push(1);
        for (let i = current - delta; i <= current + delta; i++) {
            if (i < total && i > 1) {
                range.push(i);
            }
        }
        range.push(total);

        // Filter duplicates and sort
        const uniqueRange = [...new Set(range)].sort((a, b) => a - b);

        for (let i of uniqueRange) {
            if (l) {
                if (i - l === 2) {
                    rangeWithDots.push(l + 1);
                } else if (i - l !== 1) {
                    rangeWithDots.push('...');
                }
            }
            rangeWithDots.push(i);
            l = i;
        }

        rangeWithDots.forEach(p => {
            if (p === '...') {
                addBtn(null, '...', false, true);
            } else {
                addBtn(p, p, p === current);
            }
        });

        // Next
        addBtn(current + 1, 'Siguiente', false, current === total, false, true);
    }

    // Debounce search
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadClients(1); // Reset to page 1 on search
        }, 300);
    });

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        document.getElementById('clientForm').reset();
        document.getElementById('clientId').value = '';
        document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    async function saveClient() {
        const id = document.getElementById('clientId').value;
        const data = {
            fullname: document.getElementById('fullname').value,
            dni_ruc: document.getElementById('dni_ruc').value,
            phone: document.getElementById('phone').value,
            email: document.getElementById('email').value,
            address: document.getElementById('address').value,
            coordinates: document.getElementById('coordinates').value,
            status: 'active' // Default
        };

        if (id) {
            data.id = id;
            // Add PUT logic here if needed, for now using POST for create
        }

        const method = id ? 'PUT' : 'POST';
        
        try {
            const response = await fetch('../api/clients.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                closeModal('clientModal');
                loadClients();
                alert('Cliente guardado exitosamente');
            } else {
                alert('Error al guardar cliente');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        }
    }

    async function editClient(id) {
        try {
            const response = await fetch(`../api/clients.php?id=${id}`);
            const client = await response.json();
            
            document.getElementById('clientId').value = client.id;
            document.getElementById('fullname').value = client.fullname;
            document.getElementById('dni_ruc').value = client.dni_ruc;
            document.getElementById('phone').value = client.phone;
            document.getElementById('email').value = client.email;
            document.getElementById('address').value = client.address;
            document.getElementById('coordinates').value = client.coordinates;
            
            document.getElementById('modalTitle').textContent = 'Editar Cliente';
            document.getElementById('clientModal').classList.remove('hidden');
        } catch (error) {
            console.error('Error fetching client:', error);
        }
    }

    async function deleteClient(id) {
        if(confirm('¿Estás seguro de eliminar este cliente?')) {
            try {
                const response = await fetch('../api/clients.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });

                if (response.ok) {
                    loadClients();
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
