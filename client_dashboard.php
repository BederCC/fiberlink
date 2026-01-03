<?php
session_start();
if (!isset($_SESSION['client_id'])) {
    header("Location: client_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - FiberLink</title>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-indigo-600">FiberLink</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-600 hidden sm:block">Hola, <?php echo htmlspecialchars($_SESSION['client_name']); ?></span>
                    <a href="client_logout.php" class="text-sm font-medium text-red-500 hover:text-red-700">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-900">Resumen de tu Servicio</h1>
            <p class="text-slate-500">Gestiona tus pagos y revisa tus facturas.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left Column: Service Info & Invoices -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Service Status Card -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4">Estado del Servicio</h2>
                    <div id="serviceInfo" class="animate-pulse">
                        <div class="h-4 bg-slate-200 rounded w-3/4 mb-2"></div>
                        <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                    </div>
                </div>

                <!-- Invoices List -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-slate-800">Mis Facturas</h2>
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded bg-indigo-100 text-indigo-800">Últimos 6 meses</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-500">
                            <thead class="text-xs text-slate-700 uppercase bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3">N° Factura</th>
                                    <th class="px-6 py-3">Vencimiento</th>
                                    <th class="px-6 py-3">Monto</th>
                                    <th class="px-6 py-3">Estado</th>
                                    <th class="px-6 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="invoicesList">
                                <!-- Invoices populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Right Column: Payment -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg border border-indigo-100 p-6 sticky top-24">
                    <h2 class="text-lg font-bold text-slate-800 mb-4">Realizar Pago</h2>
                    
                    <div id="paymentForm" class="hidden">
                        <div class="mb-4 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                            <p class="text-xs text-indigo-600 uppercase font-bold mb-1">Factura Seleccionada</p>
                            <p class="text-lg font-bold text-indigo-900" id="payInvoiceNum">INV-001</p>
                            <p class="text-2xl font-bold text-indigo-600 mt-2" id="payAmount">S/ 0.00</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Método de Pago</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button onclick="selectMethod('visa')" class="pay-method p-2 border border-slate-200 rounded-lg hover:border-indigo-500 flex items-center justify-center gap-2" data-method="credit_card">
                                        <span class="text-sm font-medium">Tarjeta</span>
                                    </button>
                                    <button onclick="selectMethod('yape')" class="pay-method p-2 border border-slate-200 rounded-lg hover:border-indigo-500 flex items-center justify-center gap-2" data-method="yape">
                                        <span class="text-sm font-medium">Yape/Plin</span>
                                    </button>
                                </div>
                                <input type="hidden" id="selectedMethod" value="credit_card">
                                
                                <!-- Dynamic Fields -->
                                <div id="paymentFields" class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
                                    <!-- Fields injected via JS -->
                                </div>
                            </div>

                            <button onclick="processPayment()" id="payBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition-colors shadow-lg shadow-indigo-500/30">
                                Pagar Ahora
                            </button>
                            
                            <button onclick="cancelPayment()" class="w-full text-slate-500 hover:text-slate-700 text-sm font-medium">
                                Cancelar
                            </button>
                        </div>
                    </div>

                    <div id="noPayment" class="text-center py-8 text-slate-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <p>Selecciona una factura pendiente para pagar.</p>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        const clientId = <?php echo $_SESSION['client_id']; ?>;
        let selectedInvoice = null;
        let paymentStartTime = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadDashboardData();
        });

        async function loadDashboardData() {
            try {
                const response = await fetch(`api/client_data.php?client_id=${clientId}`);
                
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`Server error: ${response.status} ${response.statusText}\n${text}`);
                }

                const data = await response.json();

                // Render Service Info
                const serviceDiv = document.getElementById('serviceInfo');
                if (data.service) {
                    let statusColor = data.service.status === 'active' ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50';
                    let statusText = data.service.status === 'active' ? 'Activo' : 'Suspendido';
                    
                    serviceDiv.className = '';
                    serviceDiv.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-500">Plan Contratado</p>
                                <p class="text-lg font-bold text-slate-900">${data.service.plan_name}</p>
                                <p class="text-sm text-slate-500 mt-1">${data.service.address}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColor}">
                                ${statusText}
                            </span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100 flex gap-6">
                            <div>
                                <p class="text-xs text-slate-400 uppercase">IP Address</p>
                                <p class="font-mono text-sm text-slate-700">${data.service.ip_address}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase">Velocidad</p>
                                <p class="font-mono text-sm text-slate-700">${data.service.speed_mbps} Mbps</p>
                            </div>
                        </div>
                    `;
                } else {
                    serviceDiv.innerHTML = '<p class="text-slate-500">No se encontró servicio activo.</p>';
                    serviceDiv.className = '';
                }

                // Render Invoices
                const tbody = document.getElementById('invoicesList');
                tbody.innerHTML = '';
                if (data.invoices && data.invoices.length > 0) {
                    data.invoices.forEach(inv => {
                        const tr = document.createElement('tr');
                        tr.className = 'bg-white border-b hover:bg-slate-50';
                        
                        let statusBadge = '';
                        if(inv.status === 'paid') statusBadge = '<span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded">Pagado</span>';
                        else if(inv.status === 'overdue') statusBadge = '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Vencido</span>';
                        else statusBadge = '<span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded">Pendiente</span>';

                        let actionBtn = '';
                        if (inv.status !== 'paid') {
                            actionBtn = `<button onclick='selectInvoice(${JSON.stringify(inv)})' class="text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-xs px-3 py-1.5 focus:outline-none">Pagar</button>`;
                        } else {
                            actionBtn = `<a href="api/pdf_invoice.php?id=${inv.id}" target="_blank" class="text-indigo-600 hover:underline text-xs font-medium">Ver Recibo</a>`;
                        }

                        tr.innerHTML = `
                            <td class="px-6 py-4 font-medium text-slate-900">${inv.invoice_number}</td>
                            <td class="px-6 py-4">${inv.due_date}</td>
                            <td class="px-6 py-4 font-bold text-slate-700">S/ ${parseFloat(inv.total_amount).toFixed(2)}</td>
                            <td class="px-6 py-4">${statusBadge}</td>
                            <td class="px-6 py-4">${actionBtn}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-slate-500">No tienes facturas registradas.</td></tr>';
                }

            } catch (error) {
                console.error(error);
                alert('Error al cargar datos: ' + error.message);
            }
        }

        function selectInvoice(inv) {
            selectedInvoice = inv;
            paymentStartTime = Date.now(); // Start timer
            document.getElementById('payInvoiceNum').textContent = inv.invoice_number;
            document.getElementById('payAmount').textContent = `S/ ${parseFloat(inv.total_amount).toFixed(2)}`;
            
            document.getElementById('noPayment').classList.add('hidden');
            document.getElementById('paymentForm').classList.remove('hidden');
            
            // Scroll to payment form on mobile
            if(window.innerWidth < 1024) {
                document.getElementById('paymentForm').scrollIntoView({ behavior: 'smooth' });
            }
            
            // Initialize fields
            selectMethod('visa');
        }

        function cancelPayment() {
            selectedInvoice = null;
            document.getElementById('paymentForm').classList.add('hidden');
            document.getElementById('noPayment').classList.remove('hidden');
        }

        function selectMethod(method) {
            document.querySelectorAll('.pay-method').forEach(el => {
                el.classList.remove('border-indigo-500', 'bg-indigo-50');
                el.classList.add('border-slate-200');
            });
            const btn = document.querySelector(`button[data-method="${method === 'visa' ? 'credit_card' : method}"]`);
            if(btn) {
                btn.classList.remove('border-slate-200');
                btn.classList.add('border-indigo-500', 'bg-indigo-50');
            }
            document.getElementById('selectedMethod').value = method === 'visa' ? 'credit_card' : method;
            
            renderPaymentFields(method);
            validateForm();
        }

        function renderPaymentFields(method) {
            const container = document.getElementById('paymentFields');
            if (method === 'visa' || method === 'credit_card') {
                container.innerHTML = `
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Número de Tarjeta</label>
                            <input type="text" id="cardNumber" class="w-full p-2 text-sm border border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="0000 0000 0000 0000" maxlength="19" oninput="validateForm()">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Vencimiento</label>
                                <input type="text" id="cardExpiry" class="w-full p-2 text-sm border border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="MM/YY" maxlength="5" oninput="validateForm()">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">CVV</label>
                                <input type="text" id="cardCvv" class="w-full p-2 text-sm border border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="123" maxlength="3" oninput="validateForm()">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Titular</label>
                            <input type="text" id="cardName" class="w-full p-2 text-sm border border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nombre como figura en la tarjeta" oninput="validateForm()">
                        </div>
                    </div>
                `;
            } else {
                container.innerHTML = `
                    <div class="text-center space-y-4">
                        <div class="bg-white p-2 inline-block rounded-lg border border-slate-200">
                            <!-- Placeholder QR -->
                            <div class="w-32 h-32 bg-slate-100 flex items-center justify-center text-slate-400 text-xs">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=FIBERLINK-PAGO-${Date.now()}" alt="QR Yape/Plin">
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">Escanea el QR y realiza el pago por el monto exacto.</p>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1 text-left">Código de Operación</label>
                            <input type="text" id="operationCode" class="w-full p-2 text-sm border border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej: 12345678" maxlength="10" oninput="validateForm()">
                        </div>
                    </div>
                `;
            }
        }

        function validateForm() {
            const method = document.getElementById('selectedMethod').value;
            const btn = document.getElementById('payBtn');
            let isValid = false;

            if (method === 'credit_card') {
                const num = document.getElementById('cardNumber')?.value || '';
                const exp = document.getElementById('cardExpiry')?.value || '';
                const cvv = document.getElementById('cardCvv')?.value || '';
                const name = document.getElementById('cardName')?.value || '';
                
                // Basic length checks
                isValid = num.length >= 16 && exp.length >= 4 && cvv.length >= 3 && name.length > 3;
            } else {
                const code = document.getElementById('operationCode')?.value || '';
                isValid = code.length >= 6;
            }

            btn.disabled = !isValid;
            if(isValid) {
                btn.classList.remove('opacity-50', 'cursor-not-allowed');
                btn.classList.add('hover:bg-indigo-700', 'shadow-lg');
            } else {
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                btn.classList.remove('hover:bg-indigo-700', 'shadow-lg');
            }
        }

        async function processPayment() {
            if (!selectedInvoice) return;
            
            // Double check validation
            const btn = document.getElementById('payBtn');
            if (btn.disabled) return;
            
            const method = document.getElementById('selectedMethod').value;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Procesando pago...
            `;

            // Simulate Delay (3 seconds)
            await new Promise(r => setTimeout(r, 3000));

            try {
                const response = await fetch('api/payments.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        invoice_id: selectedInvoice.id,
                        amount: selectedInvoice.total_amount,
                        payment_method: method,
                        transaction_id: method === 'credit_card' ? 'CARD-' + Date.now() : document.getElementById('operationCode').value,
                        notes: 'Pago desde Portal Clientes',
                        search_timestamp: paymentStartTime
                    })
                });

                if (response.ok) {
                    const duration = ((Date.now() - paymentStartTime) / 1000).toFixed(1);
                    alert(`¡Pago realizado con éxito!\nTiempo total: ${duration} segundos.`);
                    cancelPayment();
                    loadDashboardData();
                } else {
                    alert('Error al procesar el pago.');
                }
            } catch (error) {
                console.error(error);
                alert('Error de conexión');
            } finally {
                // Re-validate to set correct state
                validateForm();
                btn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
