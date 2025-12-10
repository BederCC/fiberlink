    <aside id="logo-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-slate-900 border-r border-slate-800 sm:translate-x-0" aria-label="Sidebar">
        <div class="h-full px-3 pb-4 overflow-y-auto bg-slate-900">
            <ul class="space-y-2 font-medium">
                <?php
                $current_page = strtolower(basename($_SERVER['PHP_SELF']));
                
                function getLinkClass($page, $current) {
                    $base = "flex items-center p-2 rounded-lg group transition-colors ";
                    if ($page === $current) {
                        return $base . "bg-slate-800 text-indigo-400";
                    } else {
                        return $base . "text-slate-300 hover:bg-slate-800 hover:text-indigo-400";
                    }
                }
                function getIconClass($page, $current) {
                    $base = "flex-shrink-0 w-5 h-5 transition duration-75 ";
                    if ($page === $current) {
                        return $base . "text-indigo-400";
                    } else {
                        return $base . "text-slate-400 group-hover:text-indigo-400";
                    }
                }
                ?>
                <li>
                    <a href="dashboard.php" class="<?php echo getLinkClass('dashboard.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('dashboard.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                        </svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="services.php" class="<?php echo getLinkClass('services.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('services.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
                            <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
                            <path d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z"/>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Instalaciones</span>
                        <span class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-slate-800 bg-indigo-400 rounded-full">New</span>
                    </a>
                </li>
                <li>
                    <a href="billing.php" class="<?php echo getLinkClass('billing.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('billing.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Facturación</span>
                        <span class="inline-flex items-center justify-center w-3 h-3 p-3 ms-3 text-sm font-medium text-indigo-400 bg-indigo-900/50 rounded-full">3</span>
                    </a>
                </li>
                <li>
                    <a href="clients.php" class="<?php echo getLinkClass('clients.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('clients.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                            <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Clientes</span>
                    </a>
                </li>
                <li>
                    <a href="plans.php" class="<?php echo getLinkClass('plans.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('plans.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                            <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.166 16.043A2 2 0 0 0 2.133 18h13.734a2 2 0 0 0 1.967-1.957L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z"/>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Planes (Servicios)</span>
                    </a>
                </li>
                <li>
                    <a href="products.php" class="<?php echo getLinkClass('products.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('products.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M1 5h1.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 1 0 0-2H8.576a3.228 3.228 0 0 0-6.152 0H1a1 1 0 1 0 0 2Zm18 4h-1.424a3.228 3.228 0 0 0-6.152 0H1a1 1 0 1 0 0 2h10.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 0 0 0-2Zm0 6H8.576a3.228 3.228 0 0 0-6.152 0H1a1 1 0 0 0 0 2h1.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 0 0 0-2Z"/>
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Inventario (Equipos)</span>
                    </a>
                </li>
                <li>
                    <a href="technical.php" class="<?php echo getLinkClass('technical.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('technical.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Operaciones Técnicas</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="<?php echo getLinkClass('reports.php', $current_page); ?>">
                        <svg class="<?php echo getIconClass('reports.php', $current_page); ?>" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="flex-1 ms-3 whitespace-nowrap">Reportes</span>
                    </a>
                </li>
            </ul>
        </div>
    </aside>
