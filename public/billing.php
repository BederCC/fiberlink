<?php require_once '../includes/header.php'; ?>
<?php require_once '../includes/navbar.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="p-4 sm:ml-64 mt-14">
    <div class="p-4 border border-dashed border-slate-700 rounded-xl">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Facturación y Pagos</h1>
            <div class="flex gap-2">
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
    </div>
</div>

<!-- Generate Modal -->
<div id="generateModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-slate-800 rounded-xl shadow-2xl border border-slate-700">
            <div class="p-6 text-center">
                <h3 class="mb-5 text-lg font-normal text-white">Generar Facturación Mensual</h3>
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
                <button onclick="generateInvoices()" type="button" class="text-white bg-indigo-600 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                    Generar
                </button>
                <button onclick="closeModal('generateModal')" type="button" class="text-slate-400 bg-transparent hover:bg-slate-700 hover:text-white rounded-lg border border-slate-600 text-sm font-medium px-5 py-2.5 hover:text-white focus:z-10">
                    Cancelar
                </button>
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        loadInvoices();
        // Set current month/year in modal
        const date = new Date();
        document.getElementById('genMonth').value = date.getMonth() + 1;
        document.getElementById('genYear').value = date.getFullYear();
    });

    let previousStatuses = {};

    async function loadInvoices() {
        try {
            const response = await fetch('../api/billing.php');
            const invoices = await response.json();
            const tbody = document.getElementById('invoicesTableBody');
            
            let unpaid = 0, paid = 0, overdue = 0;

            // Create a map of current rows for easy access
            const currentRows = {};
            tbody.querySelectorAll('tr').forEach(row => {
                currentRows[row.id] = row;
            });

            invoices.forEach(inv => {
                const amount = parseFloat(inv.total_amount);
                if(inv.status === 'paid') paid += amount;
                else if(inv.status === 'overdue') overdue += amount;
                else unpaid += amount;

                let statusColor = 'bg-slate-500/10 text-slate-400';
                if(inv.status === 'paid') statusColor = 'bg-emerald-500/10 text-emerald-400';
                if(inv.status === 'overdue') statusColor = 'bg-red-500/10 text-red-400';
                if(inv.status === 'unpaid') statusColor = 'bg-amber-500/10 text-amber-400';

                const rowId = `invoice-${inv.id}`;
                const rowContent = `
                    <td class="px-6 py-4 font-medium text-white">${inv.invoice_number}</td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            <span class="font-medium text-white">${inv.first_name} ${inv.last_name}</span>
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
                         // Update content if something else changed (rare but possible)
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
                // Remove from currentRows map to track deletions
                delete currentRows[rowId];
            });

            // Remove rows that are no longer in the data
            for (const id in currentRows) {
                currentRows[id].remove();
            }

            document.getElementById('totalUnpaid').textContent = `S/ ${unpaid.toFixed(2)}`;
            document.getElementById('totalPaid').textContent = `S/ ${paid.toFixed(2)}`;
            document.getElementById('totalOverdue').textContent = `S/ ${overdue.toFixed(2)}`;

        } catch (error) {
            console.error('Error loading invoices:', error);
        }
    }

    // Poll every 5 seconds
    setInterval(loadInvoices, 5000);

    function openGenerateModal() {
        document.getElementById('generateModal').classList.remove('hidden');
    }

    function openPaymentModal(id, amount) {
        document.getElementById('payInvoiceId').value = id;
        document.getElementById('payAmount').value = amount.toFixed(2);
        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    async function generateInvoices() {
        const month = document.getElementById('genMonth').value;
        const year = document.getElementById('genYear').value;

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
</script>

<?php require_once '../includes/footer.php'; ?>
