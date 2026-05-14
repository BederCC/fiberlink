<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Gestión de Usuarios</h1>
            <button onclick="openModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Usuario
            </button>
        </div>

        <!-- Users Table -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-500 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nombre Completo</th>
                        <th scope="col" class="px-6 py-3">Usuario</th>
                        <th scope="col" class="px-6 py-3">Rol</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <!-- Data will be populated here -->
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Create/Edit Modal -->
<div id="userModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <button type="button" onclick="closeModal()" class="absolute top-3 right-2.5 text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
            </button>
            <div class="p-6 text-center">
                <h3 class="mb-5 text-lg font-normal text-white" id="modalTitle">Nuevo Usuario</h3>
                <form id="userForm" class="space-y-4 text-left">
                    <input type="hidden" id="userId">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Nombre Completo</label>
                        <input type="text" id="fullName" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" required>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Nombre de Usuario</label>
                        <input type="text" id="username" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" required>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Contraseña</label>
                        <input type="password" id="password" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" placeholder="Dejar en blanco para mantener actual">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Rol</label>
                        <select id="role" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                            <option value="admin">Administrador</option>
                            <option value="technician">Técnico</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-white">Estado</label>
                        <select id="status" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', loadUsers);

    async function loadUsers() {
        try {
            const response = await fetch('../api/users.php');
            const users = await response.json();
            const tbody = document.getElementById('usersTableBody');
            tbody.innerHTML = '';

            users.forEach(user => {
                const statusBadge = user.status == 1 
                    ? '<span class="px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs">Activo</span>'
                    : '<span class="px-2 py-1 rounded-full bg-slate-500/10 text-slate-400 text-xs">Inactivo</span>';

                tbody.innerHTML += `
                    <tr class="bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors">
                        <td class="px-6 py-4 font-medium text-white">${user.full_name}</td>
                        <td class="px-6 py-4">${user.username}</td>
                        <td class="px-6 py-4 capitalize">${user.role}</td>
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4">
                            <button onclick='editUser(${JSON.stringify(user)})' class="font-medium text-indigo-400 hover:underline mr-3">Editar</button>
                            <button onclick="deleteUser(${user.id})" class="font-medium text-red-400 hover:underline">Eliminar</button>
                        </td>
                    </tr>
                `;
            });
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    function openModal() {
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
        document.getElementById('userModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('userModal').classList.add('hidden');
    }

    function editUser(user) {
        document.getElementById('userId').value = user.id;
        document.getElementById('fullName').value = user.full_name;
        document.getElementById('username').value = user.username;
        document.getElementById('role').value = user.role;
        document.getElementById('status').value = user.status;
        document.getElementById('password').value = ''; // Don't show password
        
        document.getElementById('modalTitle').textContent = 'Editar Usuario';
        document.getElementById('userModal').classList.remove('hidden');
    }

    document.getElementById('userForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const id = document.getElementById('userId').value;
        const data = {
            full_name: document.getElementById('fullName').value,
            username: document.getElementById('username').value,
            role: document.getElementById('role').value,
            status: document.getElementById('status').value,
            password: document.getElementById('password').value
        };

        if (id) data.id = id;

        const method = id ? 'PUT' : 'POST';

        try {
            const response = await fetch('../api/users.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                closeModal();
                loadUsers();
                alert('Usuario guardado exitosamente');
            } else {
                alert('Error al guardar usuario');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    });

    async function deleteUser(id) {
        if (!confirm('¿Está seguro de eliminar este usuario?')) return;

        try {
            const response = await fetch('../api/users.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });

            if (response.ok) {
                loadUsers();
            } else {
                alert('Error al eliminar usuario');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
