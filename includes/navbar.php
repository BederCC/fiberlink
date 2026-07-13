    <nav class="fixed top-0 z-50 w-full bg-slate-900/80 backdrop-blur-md border-b border-slate-800">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar" aria-controls="logo-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-slate-400 rounded-lg sm:hidden hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-600">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                           <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
                        </svg>
                    </button>
                    <a href="#" class="flex ms-2 md:me-24 items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white font-bold text-xl">F</div>
                        <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">FiberLink</span>
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Botón Documentación -->
                    <a href="<?php echo BASE_URL; ?>/reporte_arquitectura.html" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-file-lines text-indigo-400"></i>
                        <span>Documentación</span>
                    </a>

                    <div class="flex items-center relative">
                        <div>
                            <button type="button" class="flex text-sm bg-slate-800 rounded-full focus:ring-4 focus:ring-slate-700" aria-expanded="false" data-dropdown-toggle="dropdown-user" id="user-menu-button">
                                <span class="sr-only">Open user menu</span>
                                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold" id="user-avatar">A</div>
                            </button>
                        </div>
                        <!-- Dropdown menu -->
                        <div class="z-50 hidden absolute right-0 top-11 w-56 text-sm list-none bg-slate-800/95 backdrop-blur-md divide-y divide-slate-700/60 rounded-xl shadow-2xl border border-slate-700/80 transition-all duration-200" id="dropdown-user" style="display: none;">
                            <div class="px-5 py-3.5" role="none">
                                <p class="text-sm font-semibold text-white tracking-wide" role="none" id="user-name-display">
                                    Usuario
                                </p>
                                <p class="text-xs font-medium text-slate-400 truncate mt-0.5 uppercase tracking-wider" role="none" id="user-role-display">
                                    Rol
                                </p>
                            </div>
                            <ul class="py-2 text-slate-300" role="none">
                                <li>
                                    <a href="#" id="navbar-dashboard-link" class="flex items-center gap-2.5 px-5 py-2.5 hover:bg-slate-700/50 hover:text-white transition-colors" role="menuitem">
                                        <i class="fa-solid fa-chart-pie text-slate-400"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/reporte_arquitectura.html" target="_blank" class="flex items-center gap-2.5 px-5 py-2.5 hover:bg-slate-700/50 hover:text-white transition-colors" role="menuitem">
                                        <i class="fa-solid fa-file-lines text-indigo-400"></i>
                                        <span>Documentación</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" class="flex items-center gap-2.5 px-5 py-2.5 hover:bg-slate-700/50 hover:text-white transition-colors" role="menuitem">
                                        <i class="fa-solid fa-gear text-slate-400"></i>
                                        <span>Configuración</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo BASE_URL; ?>/api/logout.php" class="flex items-center gap-2.5 px-5 py-2.5 text-red-400 hover:bg-slate-700/50 hover:text-red-300 transition-colors border-t border-slate-700/40" role="menuitem">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                        <span>Cerrar Sesión</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userMenuBtn = document.getElementById('user-menu-button');
            const userDropdown = document.getElementById('dropdown-user');



            if (userMenuBtn && userDropdown) {
                userMenuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if (userDropdown.style.display === 'none' || userDropdown.style.display === '') {
                        userDropdown.style.display = 'block';
                        userDropdown.classList.remove('hidden'); // Just in case
                    } else {
                        userDropdown.style.display = 'none';
                        userDropdown.classList.add('hidden'); // Just in case
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.style.display = 'none';
                        userDropdown.classList.add('hidden');
                    }
                });
            }

            // Load user info
            const user = JSON.parse(localStorage.getItem('user'));
            if (user) {
                document.getElementById('user-avatar').textContent = user.full_name.charAt(0).toUpperCase();
                document.getElementById('user-name-display').textContent = user.full_name;
                document.getElementById('user-role-display').textContent = user.role;

                // Update dashboard link
                const dashboardLink = document.getElementById('navbar-dashboard-link');
                if (dashboardLink) {
                    if (user.role === 'technician') {
                        // If we are already in technician dir, just dashboard.php, else public/technician/dashboard.php
                        if (window.location.pathname.includes('/technician/')) {
                            dashboardLink.href = 'dashboard.php';
                        } else {
                            dashboardLink.href = 'technician/dashboard.php';
                        }
                    } else {
                        // Admin/Staff
                        if (window.location.pathname.includes('/technician/')) {
                             dashboardLink.href = '../dashboard.php';
                        } else {
                             dashboardLink.href = 'dashboard.php';
                        }
                    }
                }
            }
        });


    </script>
