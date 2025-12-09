<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Inventario de Equipos</h1>
            <button onclick="openModal('productModal')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Producto
            </button>
        </div>

        <!-- Table -->
        <div class="relative overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nombre</th>
                        <th scope="col" class="px-6 py-3">Descripción</th>
                        <th scope="col" class="px-6 py-3">Precio</th>
                        <th scope="col" class="px-6 py-3">Stock</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <!-- Data -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="productModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="flex items-start justify-between p-4 border-b border-slate-700 rounded-t">
                <h3 class="text-xl font-semibold text-white">Producto</h3>
                <button type="button" onclick="closeModal('productModal')" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="productId">
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Nombre del Equipo</label>
                    <input type="text" id="productName" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="Router TP-Link">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Descripción</label>
                    <textarea id="productDesc" rows="3" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="Modelo Archer C6, Doble Banda..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Precio (S/)</label>
                        <input type="number" step="0.01" id="productPrice" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="150.00">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Stock Inicial</label>
                        <input type="number" id="productStock" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="10">
                    </div>
                </div>
                <button onclick="saveProduct()" type="button" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Guardar Producto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', loadProducts);

    async function loadProducts() {
        try {
            const response = await fetch('../api/products.php');
            const products = await response.json();
            const tbody = document.getElementById('productsTableBody');
            tbody.innerHTML = '';

            products.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors';
                tr.innerHTML = `
                    <td class="px-6 py-4 font-medium text-white">${p.name}</td>
                    <td class="px-6 py-4 text-slate-400">${p.description || '-'}</td>
                    <td class="px-6 py-4 font-bold text-indigo-400">S/ ${parseFloat(p.price).toFixed(2)}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs ${p.stock > 5 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'}">
                            ${p.stock} un.
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="editProduct(${p.id})" class="font-medium text-indigo-400 hover:underline">Editar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Error loading products:', error);
        }
    }

    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
        if(modalId === 'productModal' && !document.getElementById('productId').value) {
            document.getElementById('productName').value = '';
            document.getElementById('productDesc').value = '';
            document.getElementById('productPrice').value = '';
            document.getElementById('productStock').value = '';
        }
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
        document.getElementById('productId').value = '';
    }

    async function saveProduct() {
        const id = document.getElementById('productId').value;
        const data = {
            name: document.getElementById('productName').value,
            description: document.getElementById('productDesc').value,
            price: document.getElementById('productPrice').value,
            stock: document.getElementById('productStock').value
        };

        if (id) data.id = id;
        const method = id ? 'PUT' : 'POST';

        try {
            const response = await fetch('../api/products.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('Producto guardado exitosamente');
                closeModal('productModal');
                loadProducts();
            } else {
                alert('Error al guardar producto');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    }

    async function editProduct(id) {
        try {
            const response = await fetch(`../api/products.php?id=${id}`);
            const product = await response.json();
            
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDesc').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            
            openModal('productModal');
        } catch (error) {
            console.error('Error fetching product:', error);
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
