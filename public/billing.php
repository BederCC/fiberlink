<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Facturación y Pagos</h1>
            <div class="flex gap-2">
                <button onclick="openReminderModal()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    Enviar Recordatorios
                </button>
                <button onclick="openGenerateModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Generar Facturas
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                <p class="text-slate-400 text-sm">Pendiente de Cobro</p>
                <p class="text-2xl font-bold text-white" id="totalUnpaid">S/ 0.00</p>
            </div>
            <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                <p class="text-slate-400 text-sm">Cobrado este Mes</p>
                <p class="text-2xl font-bold text-emerald-400" id="totalPaid">S/ 0.00</p>
            </div>
            <div class="p-4 bg-slate-800 rounded-xl border border-slate-700">
                <p class="text-slate-400 text-sm">Vencido</p>
                <p class="text-2xl font-bold text-red-400" id="totalOverdue">S/ 0.00</p>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="relative overflow-x-auto rounded-lg border border-slate-700">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-300 uppercase bg-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-3">N° Factura</th>
                        <th scope="col" class="px-6 py-3">Cliente</th>
                        <th scope="col" class="px-6 py-3">Emisión</th>
                        <th scope="col" class="px-6 py-3">Vencimiento</th>
                        <th scope="col" class="px-6 py-3">Monto</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody id="invoicesTableBody">
                    <!-- Data -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="flex items-center justify-between p-4 border-t border-slate-700 bg-slate-800 rounded-b-xl">
            <span class="text-sm text-slate-400">
                Mostrando <span class="font-semibold text-white" id="pageStart">0</span> a <span class="font-semibold text-white" id="pageEnd">0</span> de <span class="font-semibold text-white" id="totalRecords">0</span> facturas
            </span>
            <div class="inline-flex mt-2 xs:mt-0">
                <button onclick="changePage(-1)" id="prevPageBtn" class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-slate-700 rounded-l hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Anterior
                </button>
                <button onclick="changePage(1)" id="nextPageBtn" class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-slate-700 border-0 border-l border-slate-600 rounded-r hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Generate Modal -->
<div id="generateModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-2xl max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="p-6">
                <h3 class="mb-5 text-lg font-normal text-white text-center">Generar Facturación Mensual</h3>
                
                <!-- Selection Step -->
                <div id="stepSelect">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-white">Mes</label>
                            <select id="genMonth" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                                <option value="1">Enero</option>
                                <option value="2">Febrero</option>
                                <option value="3">Marzo</option>
                                <option value="4">Abril</option>
                                <option value="5">Mayo</option>
                                <option value="6">Junio</option>
                                <option value="7">Julio</option>
                                <option value="8">Agosto</option>
                                <option value="9">Septiembre</option>
                                <option value="10">Octubre</option>
                                <option value="11">Noviembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-white">Año</label>
                            <input type="number" id="genYear" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5" value="2025">
                        </div>
                    </div>
                    <div class="text-center">
                        <button onclick="previewGeneration()" type="button" class="text-white bg-indigo-600 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Consultar
                        </button>
                        <button onclick="closeModal('generateModal')" type="button" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg border border-slate-600 text-sm font-medium px-5 py-2.5 hover:text-white focus:z-10">
                            Cancelar
                        </button>
                    </div>
                </div>

                <!-- Preview Step -->
                <div id="stepPreview" class="hidden">
                    <div class="mb-4">
                        <h4 class="text-white font-medium mb-2">Resumen de Facturación</h4>
                        <div class="bg-slate-900 rounded-lg p-4 border border-slate-700 max-h-60 overflow-y-auto">
                            <table class="w-full text-sm text-left text-slate-400">
                                <thead class="text-xs text-slate-500 uppercase bg-slate-800 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-2">Cliente</th>
                                        <th class="px-4 py-2">Plan</th>
                                        <th class="px-4 py-2 text-right">Monto</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody">
                                    <!-- Preview Rows -->
                                </tbody>
                                <tfoot class="border-t border-slate-700 bg-slate-800 sticky bottom-0">
                                    <tr>
                                        <th class="px-4 py-2 text-white">Total</th>
                                        <th class="px-4 py-2"></th>
                                        <th class="px-4 py-2 text-right text-white" id="previewTotal">S/ 0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 text-center" id="previewCount">0 facturas a generar</p>
                    </div>
                    <div class="text-center">
                        <button onclick="confirmGeneration()" type="button" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                            Confirmar y Generar
                        </button>
                        <button onclick="resetModal()" type="button" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg border border-slate-600 text-sm font-medium px-5 py-2.5 hover:text-white focus:z-10">
                            Atrás
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="flex items-start justify-between p-4 border-b border-slate-700 rounded-t">
                <h3 class="text-xl font-semibold text-white">Registrar Pago</h3>
                <button type="button" onclick="closeModal('paymentModal')" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <input type="hidden" id="payInvoiceId">
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Monto a Pagar (S/)</label>
                    <input type="number" step="0.01" id="payAmount" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">Método de Pago</label>
                    <select id="payMethod" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                        <option value="cash">Efectivo</option>
                        <option value="yape">Yape</option>
                        <option value="plin">Plin</option>
                        <option value="bank_transfer">Transferencia</option>
                    </select>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-white">N° Operación / Referencia</label>
                    <input type="text" id="payTxnId" class="bg-slate-700 border border-slate-600 text-white text-sm rounded-lg block w-full p-2.5">
                </div>
                <button onclick="registerPayment()" type="button" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                    Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reminder Modal -->
<div id="reminderModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="p-6">
                <h3 class="mb-5 text-lg font-normal text-white text-center">Enviar Recordatorios de Pago</h3>
                
                <div class="mb-6 text-center">
                    <div class="mb-4 text-amber-500">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <p class="text-sm text-slate-300">Se enviarán correos de recordatorio a todos los clientes con facturas <strong>vencidas</strong> o próximas a vencer (3 días).</p>
                    <p class="mt-2 text-xs text-slate-400">Esta acción puede tardar unos segundos dependiendo de la cantidad de correos.</p>
                </div>

                <div class="text-center">
                    <button onclick="sendReminders()" type="button" class="text-white bg-amber-600 hover:bg-amber-700 focus:ring-4 focus:outline-none focus:ring-amber-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                        Confirmar Envío
                    </button>
                    <button onclick="closeModal('reminderModal')" type="button" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg border border-slate-600 text-sm font-medium px-5 py-2.5 hover:text-white focus:z-10">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadInvoices();
        // Set current month/year in modal
        const date = new Date();
        document.getElementById('genMonth').value = date.getMonth() + 1;
        document.getElementById('genYear').value = date.getFullYear();
    });

    let previousStatuses = {};
    let currentPage = 1;
    const limit = 20;

    async function loadInvoices() {
        try {
            const response = await fetch(`../api/billing.php?page=${currentPage}&limit=${limit}`);
            const result = await response.json();
            
            // Handle new response structure
            const invoices = result.data;
            const pagination = result.pagination;
            const stats = result.stats;

            const tbody = document.getElementById('invoicesTableBody');
            
            // Update Stats
            document.getElementById('totalUnpaid').textContent = `S/ ${parseFloat(stats.unpaid).toFixed(2)}`;
            document.getElementById('totalPaid').textContent = `S/ ${parseFloat(stats.paid).toFixed(2)}`;
            document.getElementById('totalOverdue').textContent = `S/ ${parseFloat(stats.overdue).toFixed(2)}`;

            // Update Pagination UI
            const start = pagination.total_records > 0 ? ((pagination.current_page - 1) * pagination.limit) + 1 : 0;
            const end = Math.min(pagination.current_page * pagination.limit, pagination.total_records);
            
            document.getElementById('pageStart').textContent = start;
            document.getElementById('pageEnd').textContent = end;
            document.getElementById('totalRecords').textContent = pagination.total_records;

            document.getElementById('prevPageBtn').disabled = pagination.current_page <= 1;
            document.getElementById('nextPageBtn').disabled = pagination.current_page >= pagination.total_pages;

            // Create a map of current rows for easy access
            const currentRows = {};
            tbody.querySelectorAll('tr').forEach(row => {
                currentRows[row.id] = row;
            });

            // Track which rows are present in the new data
            const newRowIds = new Set();

            invoices.forEach(inv => {
                const amount = parseFloat(inv.total_amount);
                let statusColor = 'bg-slate-500/10 text-slate-400';
                if(inv.status === 'paid') statusColor = 'bg-emerald-500/10 text-emerald-400';
                if(inv.status === 'overdue') statusColor = 'bg-red-500/10 text-red-400';
                if(inv.status === 'unpaid') statusColor = 'bg-amber-500/10 text-amber-400';

                const rowId = `invoice-${inv.id}`;
                newRowIds.add(rowId);

                const rowContent = `
                    <td class="px-6 py-4 font-medium text-white">${inv.invoice_number}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-medium text-white">${inv.fullname}</span>
                            <span class="text-xs text-slate-500">${inv.dni_ruc}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">${inv.issue_date}</td>
                    <td class="px-6 py-4">${inv.due_date}</td>
                    <td class="px-6 py-4 font-bold text-white">S/ ${amount.toFixed(2)}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-xs uppercase ${statusColor}">
                            ${inv.status}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        ${inv.status !== 'paid' ? 
                            `<button onclick="openPaymentModal(${inv.id}, ${amount})" class="font-medium text-emerald-400 hover:underline mr-3">Pagar</button>` : 
                            `<button class="font-medium text-slate-500 cursor-not-allowed mr-3">Pagado</button>`
                        }
                        <a href="../api/pdf_invoice.php?id=${inv.id}" target="_blank" class="font-medium text-indigo-400 hover:underline mr-3">PDF</a>
                        <a href="../api/xml_invoice.php?id=${inv.id}" target="_blank" class="font-medium text-amber-400 hover:underline">XML</a>
                    </td>
                `;

                let tr;
                if (currentRows[rowId]) {
                    tr = currentRows[rowId];
                    // Check if status changed
                    if (previousStatuses[inv.id] && previousStatuses[inv.id] !== inv.status && inv.status === 'paid') {
                        // Status changed to paid!
                        tr.innerHTML = rowContent;
                        // Add highlight class
                        tr.classList.add('bg-emerald-500/20');
                        setTimeout(() => {
                            tr.classList.remove('bg-emerald-500/20');
                        }, 3000);
                    } else if (tr.innerHTML !== rowContent) {
                         // Update content if something else changed
                         tr.innerHTML = rowContent;
                    }
                } else {
                    // New row
                    tr = document.createElement('tr');
                    tr.id = rowId;
                    tr.className = 'bg-slate-800 border-b border-slate-700 hover:bg-slate-700 transition-colors duration-500';
                    tr.innerHTML = rowContent;
                    tbody.appendChild(tr);
                }
                
                // Update status map
                previousStatuses[inv.id] = inv.status;
            });

            // Remove rows that are no longer in the data (e.g., moved to another page)
            for (const id in currentRows) {
                if (!newRowIds.has(id)) {
                    currentRows[id].remove();
                }
            }

        } catch (error) {
            console.error('Error loading invoices:', error);
        }
    }

    function changePage(delta) {
        currentPage += delta;
        loadInvoices();
    }

    // Poll every 5 seconds
    setInterval(loadInvoices, 5000);

    function openGenerateModal() {
        resetModal();
        document.getElementById('generateModal').classList.remove('hidden');
    }

    function resetModal() {
        document.getElementById('stepSelect').classList.remove('hidden');
        document.getElementById('stepPreview').classList.add('hidden');
        document.getElementById('previewTableBody').innerHTML = '';
    }

    function openPaymentModal(id, amount) {
        document.getElementById('payInvoiceId').value = id;
        document.getElementById('payAmount').value = amount.toFixed(2);
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    async function previewGeneration() {
        const month = document.getElementById('genMonth').value;
        const year = document.getElementById('genYear').value;
        const tbody = document.getElementById('previewTableBody');
        const btn = document.querySelector('#stepSelect button[onclick="previewGeneration()"]');
        
        btn.disabled = true;
        btn.textContent = 'Consultando...';

        try {
            const response = await fetch(`../api/billing.php?action=preview&month=${month}&year=${year}`);
            const data = await response.json();
            
            tbody.innerHTML = '';
            let total = 0;

            if (data.length > 0) {
                data.forEach(item => {
                    const price = parseFloat(item.price);
                    total += price;
                    tbody.innerHTML += `
                        <tr class="border-b border-slate-800">
                            <td class="px-4 py-2 text-white">${item.fullname}</td>
                            <td class="px-4 py-2 text-slate-500">${item.plan_name}</td>
                            <td class="px-4 py-2 text-right text-emerald-400 font-medium">S/ ${price.toFixed(2)}</td>
                        </tr>
                    `;
                });
                document.getElementById('previewCount').textContent = `${data.length} facturas a generar`;
            } else {
                tbody.innerHTML = `<tr><td colspan="3" class="px-4 py-4 text-center text-slate-500">No hay servicios pendientes de facturación para este periodo.</td></tr>`;
                document.getElementById('previewCount').textContent = `0 facturas a generar`;
            }

            document.getElementById('previewTotal').textContent = `S/ ${total.toFixed(2)}`;
            
            document.getElementById('stepSelect').classList.add('hidden');
            document.getElementById('stepPreview').classList.remove('hidden');

        } catch (error) {
            console.error(error);
            alert('Error al consultar datos');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Consultar';
        }
    }

    async function confirmGeneration() {
        const month = document.getElementById('genMonth').value;
        const year = document.getElementById('genYear').value;
        const btn = document.querySelector('#stepPreview button[onclick="confirmGeneration()"]');
        
        // Check if there is anything to generate
        const countText = document.getElementById('previewCount').textContent;
        if (countText.startsWith('0')) {
            alert('No hay facturas para generar.');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Generando...';

        try {
            const response = await fetch('../api/billing.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ month, year })
            });

            const data = await response.json();
            if (response.ok) {
                alert(`Generación completada. ${data.generated_count} facturas creadas.`);
                closeModal('generateModal');
                loadInvoices();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Confirmar y Generar';
        }
    }

    async function registerPayment() {
        const id = document.getElementById('payInvoiceId').value;
        const amount = document.getElementById('payAmount').value;
        const method = document.getElementById('payMethod').value;
        const txnId = document.getElementById('payTxnId').value;

        try {
            const response = await fetch('../api/payments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    invoice_id: id, 
                    amount: amount,
                    payment_method: method,
                    transaction_id: txnId
                })
            });

            if (response.ok) {
                alert('Pago registrado exitosamente');
                closeModal('paymentModal');
                loadInvoices();
            } else {
                alert('Error al registrar pago');
            }
        } catch (error) {
            console.error(error);
            alert('Error de conexión');
        }
    }

    function openReminderModal() {
        document.getElementById('reminderModal').classList.remove('hidden');
    }

    async function sendReminders() {
        const btn = document.querySelector('#reminderModal button[onclick="sendReminders()"]');
        
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        try {
            const response = await fetch('../api/cron_reminders.php?manual=true&type=all');
            const text = await response.text();
            
            alert('Proceso completado.\n' + text);
            closeModal('reminderModal');
        } catch (error) {
            console.error(error);
            alert('Error al enviar recordatorios');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Confirmar Envío';
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>
