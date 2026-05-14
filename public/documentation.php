<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-6 border border-dashed border-slate-700 rounded-xl bg-slate-900/20">
        <header class="mb-10">
            <h1 class="text-3xl font-bold mb-2 bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent">Manual de Usuario FiberLink</h1>
            <p class="text-slate-400 text-lg">Guía completa de los módulos y funcionalidades del sistema.</p>
        </header>
        
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            
            <!-- Dashboard -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Dashboard</h2>
                </div>
                <p class="text-slate-400">Panel principal con indicadores clave de rendimiento (KPIs), resumen de instalaciones del día y estado general de la red.</p>
            </section>

            <!-- Instalaciones -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-violet-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-violet-500/10 rounded-lg text-violet-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
                            <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
                            <path d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Instalaciones</h2>
                </div>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-indigo-400 font-semibold mb-2">Proceso de Nueva Instalación:</h3>
                        <ol class="list-decimal list-inside text-sm text-slate-300 space-y-2">
                            <li><span class="text-white font-medium">Búsqueda de Cliente:</span> Escriba el nombre o DNI en el buscador; el sistema mostrará una lista desplegable con los clientes registrados para su selección.</li>
                            <li><span class="text-white font-medium">Configuración del Plan:</span> Seleccione el plan de internet (velocidad/precio) que el cliente ha contratado.</li>
                            <li><span class="text-white font-medium">Parámetros Técnicos:</span> La <span class="italic">Dirección IP</span> y <span class="italic">MAC Address</span> se generan automáticamente de forma simulada para el registro.</li>
                            <li><span class="text-white font-medium">Facturación Inicial:</span> Defina el costo de mano de obra y marque la casilla si desea incluir el cobro del primer mes de servicio.</li>
                            <li><span class="text-white font-medium">Equipos Adicionales:</span> Puede agregar equipos (ONTs, Routers, etc.) directamente desde el inventario. El sistema descontará automáticamente estas unidades del stock.</li>
                        </ol>
                    </div>

                    <div class="bg-slate-800/40 p-4 rounded-xl border border-slate-700">
                        <h3 class="text-indigo-400 font-semibold mb-2">Panel de Control (Tabla):</h3>
                        <p class="text-xs text-slate-400 mb-3">Muestra un resumen de todos los servicios activos y sus estados.</p>
                        <ul class="text-xs text-slate-300 space-y-2">
                            <li><span class="font-bold">Columnas:</span> Cliente, Plan, IP/MAC y Estado actual.</li>
                            <li><span class="font-bold">Acción Editar:</span> Permite modificar datos técnicos y de facturación.</li>
                            <li><span class="text-amber-400 font-bold">Importante:</span> Si el estado de la instalación es <span class="italic text-white">Activo</span> o <span class="italic text-white">Cortado</span>, el sistema bloqueará la edición de equipos adicionales por seguridad de inventario.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Facturación -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-emerald-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Facturación</h2>
                </div>
                
                <div class="space-y-4">
                    <div class="border-l-2 border-emerald-500 pl-4 py-1">
                        <h3 class="text-white font-medium text-sm">Generación Masiva</h3>
                        <p class="text-xs text-slate-400">El botón <span class="text-emerald-400">"Generar Facturas"</span> permite seleccionar un Mes y Año específicos para crear automáticamente los recibos de todos los clientes que cuentan con una instalación activa.</p>
                    </div>

                    <div class="border-l-2 border-indigo-500 pl-4 py-1">
                        <h3 class="text-white font-medium text-sm">Gestión de Cobros (Simulador)</h3>
                        <p class="text-xs text-slate-400 mb-2">La tabla central muestra el N° de factura, cliente, fechas de emisión/vencimiento y estado actual. Al ser un simulador, puede registrar pagos en tiempo real.</p>
                        <div class="flex gap-2">
                            <span class="text-[10px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded">PDF de Recibo</span>
                            <span class="text-[10px] bg-amber-500/10 text-amber-400 px-2 py-0.5 rounded">XML Sunat</span>
                        </div>
                    </div>

                    <div class="border-l-2 border-amber-500 pl-4 py-1">
                        <h3 class="text-white font-medium text-sm">Recordatorios Automáticos</h3>
                        <p class="text-xs text-slate-400">El botón <span class="text-amber-500">"Enviar Recordatorios"</span> dispara un proceso de correo electrónico hacia los clientes con facturas vencidas o aquellas con vencimiento próximo (dentro de los próximos 3 días).</p>
                    </div>
                </div>
            </section>

            <!-- Clientes -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-blue-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-blue-500/10 rounded-lg text-blue-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 18">
                            <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Clientes</h2>
                </div>
                <p class="text-slate-400 mb-4">Módulo central para la gestión de la cartera de abonados de la empresa.</p>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-blue-400 font-semibold text-sm mb-1">Directorio de Clientes</h3>
                        <p class="text-xs text-slate-400">Visualice la lista completa con DNI/RUC, datos de contacto y estado. Incluye un buscador inteligente por cualquier campo de texto.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-blue-400 font-semibold text-sm mb-1">Registro y Edición</h3>
                        <p class="text-xs text-slate-400">Al registrar un nuevo cliente, se capturan datos críticos como la <span class="text-white">Dirección Exacta</span> y las <span class="text-white">Coordenadas GPS</span>, fundamentales para que el equipo técnico pueda realizar la instalación sin contratiempos.</p>
                    </div>
                </div>
            </section>

            <!-- Planes de Internet -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-pink-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-pink-500/10 rounded-lg text-pink-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Planes de Internet</h2>
                </div>
                <p class="text-slate-400 mb-4">Gestione el catálogo comercial de servicios de fibra óptica que ofrece la empresa.</p>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-pink-400 font-semibold text-sm mb-1">Configuración Comercial</h3>
                        <p class="text-xs text-slate-400">Permite crear y editar planes definiendo el nombre comercial, la <span class="text-white">Velocidad (Mbps)</span> y el <span class="text-white">Costo Mensual</span>. Estos datos se reflejarán automáticamente al momento de registrar nuevas instalaciones.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-pink-400 font-semibold text-sm mb-1">Visualización en Cuadrícula</h3>
                        <p class="text-xs text-slate-400">Los planes se presentan en tarjetas visuales que permiten una rápida identificación de los paquetes más vendidos y sus características principales.</p>
                    </div>
                </div>
            </section>

            <!-- Inventario -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-amber-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-amber-500/10 rounded-lg text-amber-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Inventario de Equipos</h2>
                </div>
                <p class="text-slate-400 mb-4">Control detallado de los activos físicos y equipos adicionales para la venta o instalación.</p>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-amber-400 font-semibold text-sm mb-1">Catálogo de Equipos</h3>
                        <p class="text-xs text-slate-400">Lista de equipos adicionales (Routers, ONTs, Mesh) con su respectiva descripción técnica y <span class="text-white">Precio de Venta</span>.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-amber-400 font-semibold text-sm mb-1">Control de Stock</h3>
                        <p class="text-xs text-slate-400">Gestión de existencias en tiempo real. El sistema permite editar el stock disponible y muestra alertas visuales cuando las unidades son críticas (menos de 5 unidades).</p>
                    </div>
                </div>
            </section>

            <!-- Operaciones Técnicas -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-red-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-red-500/10 rounded-lg text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Operaciones Técnicas</h2>
                </div>
                <p class="text-slate-400 mb-4">Centro de control para el monitoreo de red y la gestión de acceso al servicio en tiempo real.</p>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-red-400 font-semibold text-sm mb-1">Control de Tráfico y CPU</h3>
                        <p class="text-xs text-slate-400">Visualice gráficas de <span class="text-indigo-400">Download</span> y <span class="text-emerald-400">Upload</span> en tiempo real, junto con la carga de procesamiento del router principal.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-red-400 font-semibold text-sm mb-1">Gestión de Corte/Reposición</h3>
                        <p class="text-xs text-slate-400">Lista completa de usuarios navegando actualmente. Permite <span class="text-white">Cortar</span> el servicio de forma manual (bloqueo por IP/MAC) o <span class="text-white">Reponerlo</span> instantáneamente tras confirmar un pago o acuerdo comercial.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-red-400 font-semibold text-sm mb-1">Cortes Programados</h3>
                        <p class="text-xs text-slate-400">Módulo de advertencia que muestra qué clientes están próximos a ser suspendidos automáticamente por falta de pago.</p>
                    </div>
                </div>
            </section>

            <!-- Reportes -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-cyan-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-cyan-500/10 rounded-lg text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Reportes</h2>
                </div>
                <p class="text-slate-400">Módulo de inteligencia de negocios donde se analizan los tiempos de pago, efectividad técnica y proyecciones financieras.</p>
            </section>

            <!-- Usuarios -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-purple-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-purple-500/10 rounded-lg text-purple-400">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Usuarios</h2>
                </div>
                <p class="text-slate-400 mb-4">Control de acceso y gestión de perfiles de usuario dentro de la plataforma.</p>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-purple-400 font-semibold text-sm mb-1">Administración de Cuentas</h3>
                        <p class="text-xs text-slate-400">Permite crear nuevos usuarios definiendo su <span class="text-white">Nombre de Usuario</span> y <span class="text-white">Contraseña Segura</span>.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-purple-400 font-semibold text-sm mb-1">Roles y Permisos</h3>
                        <p class="text-xs text-slate-400">Asigne el rol de <span class="text-white font-medium">Administrador</span> para control total o <span class="text-white font-medium">Técnico</span> para acceso limitado a funciones de campo. También permite activar o inactivar cuentas según sea necesario.</p>
                    </div>
                </div>
            </section>

        </div>

        <!-- Divider Técnico -->
        <div class="my-12 flex flex-col items-center gap-4">
            <div class="flex items-center w-full gap-4">
                <div class="h-px flex-1 bg-slate-800"></div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Módulos para Técnicos de Campo</span>
                <div class="h-px flex-1 bg-slate-800"></div>
            </div>
            <p class="text-[10px] text-slate-500 italic">Acceso restringido: Estos módulos solo son visibles para usuarios con el rol de <span class="text-indigo-400 font-semibold">Técnico</span>.</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Instalaciones Técnico -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Instalaciones (Técnico)</h2>
                </div>
                <p class="text-slate-400 mb-4">Módulo operativo para la ejecución de trabajos en campo.</p>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-indigo-400 font-semibold text-sm mb-1">Mis Instalaciones Pendientes</h3>
                        <p class="text-xs text-slate-400">Muestra en tiempo real las instalaciones asignadas. <span class="text-amber-400 font-bold">Nota:</span> El técnico no crea instalaciones; estas aparecen automáticamente cuando un <span class="text-white">Administrador</span> genera una nueva orden de servicio.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-indigo-400 font-semibold text-sm mb-1">Ejecución y Cierre</h3>
                        <p class="text-xs text-slate-400">El técnico tiene la responsabilidad única de <span class="text-white">"Iniciar Instalación"</span> para abrir el simulador y <span class="text-white">"Completar"</span> el trabajo una vez finalizado el despliegue técnico en el domicilio.</p>
                    </div>
                </div>
            </section>

            <!-- Historial Técnico -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-slate-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-slate-500/10 rounded-lg text-slate-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Historial Técnico</h2>
                </div>
                <p class="text-slate-400 mb-4">Registro personal del técnico sobre las actividades realizadas históricamente.</p>
                <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                    <p class="text-xs text-slate-400">Consulta de instalaciones previas, mantenimientos y equipos entregados, permitiendo un seguimiento del rendimiento individual y auditoría de materiales.</p>
                </div>
            </section>
        </div>
        <!-- Divider Clientes (Portal) -->
        <div class="my-12 flex flex-col items-center gap-4">
            <div class="flex items-center w-full gap-4">
                <div class="h-px flex-1 bg-slate-800"></div>
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Portal de Autogestión (Abonados)</span>
                <div class="h-px flex-1 bg-slate-800"></div>
            </div>
            <p class="text-[10px] text-slate-500 italic">Módulo externo: Interfaz diseñada para que el cliente gestione su propio servicio.</p>
            <a href="../client_login.php" class="mt-2 text-xs text-indigo-400 hover:text-indigo-300 flex items-center gap-1 group">
                Acceder al Portal del Cliente
                <svg class="w-3 h-3 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-12">
            <!-- Registro y Activación -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-emerald-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Registro de Cuenta</h2>
                </div>
                <div class="space-y-4">
                    <p class="text-slate-400 text-xs">El proceso de acceso para abonados sigue estos pasos críticos:</p>
                    <ul class="text-xs text-slate-300 space-y-2">
                        <li><span class="text-emerald-400 font-bold">1. Captura de Datos:</span> El cliente proporciona su información básica al solicitar el servicio.</li>
                        <li><span class="text-emerald-400 font-bold">2. Creación con DNI:</span> El abonado crea su cuenta web utilizando su número de DNI como identificador principal.</li>
                        <li><span class="text-emerald-400 font-bold">3. Verificación de Email:</span> El sistema envía un <span class="text-white">Código de Activación</span> al correo electrónico registrado. La cuenta solo podrá ser utilizada tras validar dicho enlace.</li>
                    </ul>
                </div>
            </section>

            <!-- Autogestión y Pagos -->
            <section class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800 hover:border-amber-500/50 transition-colors">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-amber-500/10 rounded-lg text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-white">Portal de Pagos</h2>
                </div>
                <div class="space-y-3">
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-amber-400 font-semibold text-sm mb-1">Estado de Servicio</h3>
                        <p class="text-xs text-slate-400">El cliente visualiza en tiempo real si su señal de internet se encuentra <span class="text-emerald-400">Activa</span> o <span class="text-red-400">Suspendida</span> por falta de pago.</p>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-lg border border-slate-700">
                        <h3 class="text-amber-400 font-semibold text-sm mb-1">Pagos Digitales (Simulados)</h3>
                        <p class="text-xs text-slate-400">Permite la cancelación de facturas pendientes mediante una pasarela simulada que acepta <span class="text-white font-medium">Tarjeta de Crédito/Débito</span> y pagos rápidos vía <span class="text-white font-medium">Yape</span>.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
