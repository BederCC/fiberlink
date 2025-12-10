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
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" class="h-6 object-contain">
                        </button>
                        <button type="button" onclick="selectMethod('yape')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="yape">
                            <img src="https://static.cdnlogo.com/logos/y/37/yape-peru.svg" alt="Yape" class="h-8 object-contain">
                        </button>
                        <button type="button" onclick="selectMethod('plin')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="plin">
                            <img src="https://plin.pe/wp-content/themes/plin/imgs/logo.png" alt="Plin" class="h-8 object-contain">
                        </button>
                        <button type="button" onclick="selectMethod('bank')" class="payment-method-btn flex items-center justify-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" data-method="bank_transfer">
                            <img src="https://cdn-icons-png.flaticon.com/512/2830/2830284.png" alt="Transferencia" class="h-6 object-contain opacity-70">
                            <span class="text-sm font-medium text-slate-600">Otros Bancos</span>
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

            document.getElementById('btnText').innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Procesando...
            `;

            // Simulate processing delay
            await new Promise(resolve => setTimeout(resolve, 2000));

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
