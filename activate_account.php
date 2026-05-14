<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar Cuenta - FiberLink</title>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <div class="w-full max-w-md p-6 relative z-10">
        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">Activar Cuenta</h1>
                <p class="text-slate-400 text-sm">Ingresa tu documento para comenzar</p>
            </div>

            <!-- Role Notification -->
            <div class="mb-6 p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-start gap-3">
                <div class="p-1.5 bg-indigo-500/20 rounded-lg text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-[11px] text-slate-300 leading-relaxed">
                    <span class="text-indigo-400 font-semibold">Nota:</span> Se enviará un enlace de acceso al correo electrónico que proporcionó durante la solicitud de instalación de su servicio.
                </p>
            </div>

            <!-- Step 1: DNI Input -->
            <form id="checkForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">DNI / RUC</label>
                    <input type="text" id="dni" class="w-full bg-slate-900/50 border border-slate-600 text-white text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-3 placeholder-slate-500" placeholder="Ingrese su documento" required>
                </div>
                <button type="submit" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-500/50 font-medium rounded-lg text-sm px-5 py-3 text-center transition-all">
                    Continuar
                </button>
            </form>

            <!-- Step 2: Email Confirmation (Hidden) -->
            <form id="emailForm" class="hidden space-y-6">
                <div class="p-4 bg-slate-700/50 rounded-lg border border-slate-600 mb-4">
                    <p class="text-sm text-slate-300 mb-2" id="emailMessage"></p>
                </div>
                
                <div id="emailInputContainer" class="hidden">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Ingresa tu correo electrónico</label>
                    <input type="email" id="email" class="w-full bg-slate-900/50 border border-slate-600 text-white text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-3 placeholder-slate-500" placeholder="correo@ejemplo.com">
                </div>

                <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-500/50 font-medium rounded-lg text-sm px-5 py-3 text-center transition-all">
                    Enviar Enlace de Activación
                </button>
            </form>

            <!-- Success Message (Hidden) -->
            <div id="successMessage" class="hidden text-center">
                <div class="w-16 h-16 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">¡Enlace Enviado!</h3>
                <p class="text-slate-400 text-sm mb-6">Hemos enviado un enlace a tu correo para que configures tu contraseña.</p>
                <a href="client_login.php" class="text-indigo-400 hover:underline text-sm">Volver al inicio</a>
            </div>

            <div id="errorDiv" class="hidden mt-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm text-center"></div>

            <div class="mt-6 text-center">
                <a href="client_login.php" class="text-slate-400 hover:text-white text-sm transition-colors">
                    ← Volver
                </a>
            </div>
        </div>
    </div>

    <script>
        let currentDni = '';

        document.getElementById('checkForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const dni = document.getElementById('dni').value;
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('errorDiv');

            btn.disabled = true;
            btn.textContent = 'Verificando...';
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('api/client_auth.php?action=check_dni', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ dni })
                });
                const data = await response.json();

                if (response.ok) {
                    currentDni = dni;
                    document.getElementById('checkForm').classList.add('hidden');
                    document.getElementById('emailForm').classList.remove('hidden');
                    
                    if (data.has_email) {
                        document.getElementById('emailMessage').innerHTML = `Hemos encontrado el correo <strong>${data.masked_email}</strong> asociado a tu cuenta.`;
                        document.getElementById('emailInputContainer').classList.add('hidden');
                    } else {
                        document.getElementById('emailMessage').textContent = 'No tienes un correo registrado. Por favor ingresa uno para continuar.';
                        document.getElementById('emailInputContainer').classList.remove('hidden');
                        document.getElementById('email').required = true;
                    }
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Continuar';
                }
            } catch (error) {
                console.error(error);
                errorDiv.textContent = 'Error de conexión';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Continuar';
            }
        });

        document.getElementById('emailForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('errorDiv');

            btn.disabled = true;
            btn.textContent = 'Enviando...';
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('api/client_auth.php?action=send_activation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ dni: currentDni, email: email })
                });
                const data = await response.json();

                if (response.ok) {
                    document.getElementById('emailForm').classList.add('hidden');
                    document.getElementById('successMessage').classList.remove('hidden');
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Enviar Enlace de Activación';
                }
            } catch (error) {
                console.error(error);
                errorDiv.textContent = 'Error de conexión';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Enviar Enlace de Activación';
            }
        });
    </script>
</body>
</html>
