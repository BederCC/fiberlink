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
                <div class="flex items-center">
                    <div class="flex items-center ms-3 relative">
                        <div>
                            <button type="button" class="flex text-sm bg-slate-800 rounded-full focus:ring-4 focus:ring-slate-700" aria-expanded="false" data-dropdown-toggle="dropdown-user" id="user-menu-button">
                                <span class="sr-only">Open user menu</span>
                                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold" id="user-avatar">A</div>
                            </button>
                        </div>
                        <!-- Dropdown menu -->
                        <div class="z-50 hidden absolute right-0 top-10 my-4 text-base list-none bg-slate-800 divide-y divide-slate-700 rounded shadow-xl border border-slate-700" id="dropdown-user">
                            <div class="px-4 py-3" role="none">
                                <p class="text-sm text-white" role="none" id="user-name-display">
                                    Usuario
                                </p>
                                <p class="text-sm font-medium text-slate-400 truncate" role="none" id="user-role-display">
                                    Rol
                                </p>
                            </div>
                            <ul class="py-1" role="none">
                                <li>
                                    <a href="dashboard.php" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white" role="menuitem">Dashboard</a>
                                </li>
                                <li>
                                    <a href="#" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white" role="menuitem">Configuración</a>
                                </li>
                                <li>
                                    <a href="#" onclick="logout()" class="block px-4 py-2 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300" role="menuitem">Cerrar Sesión</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
