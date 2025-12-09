<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador de Pasarela de Pagos - FiberLink</title>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <!-- Header -->
        <div class="bg-indigo-600 p-6 text-center">
            <h1 class="text-2xl font-bold text-white mb-2">FiberLink Pagos</h1>
            <p class="text-indigo-100 text-sm">Simulador de Pasarela Externa</p>
        </div>

        <div class="p-6 space-y-6">
            
            <!-- Search Section -->
            <div id="searchSection">
                <label class="block text-sm font-medium text-slate-700 mb-2">Ingrese su DNI o RUC</label>
                <div class="flex gap-2">
                    <input type="text" id="dniInput" class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" placeholder="Ej. 12345678">
                    <button onclick="searchInvoices()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        Buscar
                    </button>
                </div>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" class="hidden space-y-4">
                <h3 class="text-sm font-semibold text-slate-500 uppercase">Facturas Pendientes</h3>
                <div id="invoicesList" class="space-y-2">
                    <!-- Invoices will be populated here -->
                </div>
                <button onclick="resetSearch()" class="text-sm text-indigo-600 hover:underline">Buscar otro cliente</button>
            </div>

            <!-- Invoice Details Card (Hidden by default) -->
            <div id="invoiceDetails" class="hidden bg-slate-50 p-4 rounded-lg border border-slate-200">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Cliente</span>
                    <span class="text-sm font-medium text-slate-900" id="detailClient">Juan Perez</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Factura</span>
                    <span class="text-sm font-medium text-slate-900" id="detailNumber">INV-001</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Vencimiento</span>
                    <span class="text-sm font-medium text-slate-900" id="detailDue">2023-10-15</span>
                </div>
                <div class="border-t border-slate-200 my-2 pt-2 flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-700">Total a Pagar</span>
                    <span class="text-xl font-bold text-indigo-600" id="detailAmount">S/ 100.00</span>
                </div>
            </div>

            <!-- Payment Method (Hidden until invoice selected) -->
            <div id="paymentSection" class="hidden space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Método de Pago</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" onclick="selectMethod('visa')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="credit_card">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M14 13.963H12.016L12.492 11H14.476L14 13.963ZM21.908 11.586C21.908 11.586 21.84 11.56 21.728 11.56C21.492 11.56 21.056 11.691 20.876 12.123L19.864 16.896H17.768L19.06 11.08C19.06 11.08 19.34 10.142 17.588 10.142H14.932L14.88 10.386C14.88 10.386 15.688 10.55 15.84 10.644C16.004 10.748 16.056 10.89 16.044 11.022L15.456 13.974L14.82 16.896H12.724L13.344 13.974L13.88 11.022C13.88 11.022 13.968 10.638 13.508 10.422C13.196 10.272 12.78 10.226 12.78 10.226L12.232 12.794L11.604 15.754L10.932 12.428L10.92 12.38C10.768 11.714 10.344 11.226 9.68 10.88C8.968 10.504 8.02 10.354 7.02 10.354H6.808L6.68 10.926C6.68 10.926 7.68 11.124 8.52 11.602C9.18 11.988 9.38 12.598 9.38 12.598L8.144 16.896H10.292L13.208 10.142H11.508L10.932 12.428L10.92 12.38L10.292 16.896H12.44L13.06 13.974L13.596 11.022L14.132 13.974L14.756 16.896H16.852L18.42 10.142H20.364C20.364 10.142 20.664 10.142 20.888 10.638L22.664 16.896H24.764L22.964 11.972C22.964 11.972 22.688 11.136 21.908 11.586Z"/></svg>
                            <span class="text-sm font-medium">Tarjeta</span>
                        </button>
                        <button type="button" onclick="selectMethod('yape')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="yape">
                            <span class="text-sm font-bold text-purple-600">Yape</span>
                        </button>
                        <button type="button" onclick="selectMethod('plin')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="plin">
                            <span class="text-sm font-bold text-cyan-500">Plin</span>
                        </button>
                        <button type="button" onclick="selectMethod('bank')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="bank_transfer">
                            <span class="text-sm font-medium">Transferencia</span>
                        </button>
                    </div>
                    <input type="hidden" id="selectedMethod" value="credit_card">
                </div>

                <!-- Pay Button -->
                <button onclick="processPayment()" id="payBtn" class="w-full py-3.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all duration-300 flex justify-center items-center gap-2">
                    <span id="btnText">Pagar S/ 0.00</span>
                </button>
            </div>

            <p class="text-center text-xs text-slate-400 mt-4">
                Ambiente de Pruebas - No se realizará ningún cargo real
            </p>
        </div>
    </div>

    <script>
        let selectedInvoice = null;
        let searchTimestamp = null;

        async function searchInvoices() {
            const dni = document.getElementById('dniInput').value;
            if(!dni) {
                alert('Por favor ingrese un DNI o RUC');
                return;
            }

            try {
                const response = await fetch(`api/billing.php?dni=${dni}`);
                const invoices = await response.json();
                
                if (invoices.length > 0) {
                    searchTimestamp = Date.now(); // Capture timestamp
                }
                
                const list = document.getElementById('invoicesList');
                list.innerHTML = '';

                if (invoices.length === 0) {
                    list.innerHTML = '<p class="text-sm text-slate-500 text-center">No se encontraron facturas pendientes para este documento.</p>';
                } else {
                    invoices.forEach(inv => {
                        const div = document.createElement('div');
                        div.className = 'p-3 border border-slate-200 rounded-lg hover:border-indigo-500 cursor-pointer transition-colors flex justify-between items-center';
                        div.onclick = () => selectInvoice(inv);
                        div.innerHTML = `
                            <div>
                                <p class="text-sm font-bold text-slate-800">${inv.invoice_number}</p>
                                <p class="text-xs text-slate-500">Vence: ${inv.due_date}</p>
                            </div>
                            <span class="text-sm font-bold text-indigo-600">S/ ${parseFloat(inv.total_amount).toFixed(2)}</span>
                        `;
                        list.appendChild(div);
                    });
                }

                document.getElementById('searchSection').classList.add('hidden');
                document.getElementById('resultsSection').classList.remove('hidden');

            } catch (error) {
                console.error('Error:', error);
                alert('Error al buscar facturas');
            }
        }



        function selectInvoice(inv) {
            selectedInvoice = {
                id: inv.id,
                amount: parseFloat(inv.total_amount),
                client: `${inv.first_name} ${inv.last_name}`,
                due: inv.due_date,
                number: inv.invoice_number
            };

            document.getElementById('detailClient').textContent = selectedInvoice.client;
            document.getElementById('detailNumber').textContent = selectedInvoice.number;
            document.getElementById('detailDue').textContent = selectedInvoice.due;
            document.getElementById('detailAmount').textContent = `S/ ${selectedInvoice.amount.toFixed(2)}`;
            document.getElementById('btnText').textContent = `Pagar S/ ${selectedInvoice.amount.toFixed(2)}`;

            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('invoiceDetails').classList.remove('hidden');
            document.getElementById('paymentSection').classList.remove('hidden');
        }

        function resetSearch() {
            document.getElementById('searchSection').classList.remove('hidden');
            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('invoiceDetails').classList.add('hidden');
            document.getElementById('paymentSection').classList.add('hidden');
            document.getElementById('dniInput').value = '';
            selectedInvoice = null;
        }

        function selectMethod(methodType) {
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('border-indigo-500', 'bg-indigo-50', 'ring-2', 'ring-indigo-500');
                btn.classList.add('border-slate-200');
            });
            
            const btn = document.querySelector(`button[data-method="${methodType === 'bank' ? 'bank_transfer' : (methodType === 'visa' ? 'credit_card' : methodType)}"]`);
            if(btn) {
                btn.classList.remove('border-slate-200');
                btn.classList.add('border-indigo-500', 'bg-indigo-50', 'ring-2', 'ring-indigo-500');
                document.getElementById('selectedMethod').value = btn.dataset.method;
            }
        }

        async function processPayment() {
            if (!selectedInvoice) return;

            const btn = document.getElementById('payBtn');
            const originalText = document.getElementById('btnText').textContent;
            
            btn.disabled = true;
            document.getElementById('btnText').textContent = 'Procesando...';

            try {
                const response = await fetch('api/payments.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        invoice_id: selectedInvoice.id,
                        amount: selectedInvoice.amount,
                        payment_method: document.getElementById('selectedMethod').value,
                        transaction_id: 'SIM-' + Date.now(),
                        notes: 'Pago simulado desde pasarela externa',
                        search_timestamp: searchTimestamp
                    })
                });

                if (response.ok) {
                    alert('¡Pago realizado con éxito! Gracias por su preferencia.');
                    window.location.reload();
                } else {
                    alert('Error al procesar el pago.');
                    btn.disabled = false;
                    document.getElementById('btnText').textContent = originalText;
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión');
                btn.disabled = false;
                document.getElementById('btnText').textContent = originalText;
            }
        }
    </script>
</body>
</html>
