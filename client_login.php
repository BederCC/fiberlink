<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Clientes - FiberLink</title>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-indigo-600/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-600/20 rounded-full blur-[100px]"></div>
    </div>

    <div class="w-full max-w-md p-6 relative z-10">
        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-white mb-2">FiberLink</h1>
                <p class="text-slate-400">Portal de Clientes</p>
            </div>

            <form id="loginForm" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">DNI / RUC</label>
                    <input type="text" id="dni" class="w-full bg-slate-900/50 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 placeholder-slate-500" placeholder="Ingrese su documento" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Contraseña</label>
                    <input type="password" id="password" class="w-full bg-slate-900/50 border border-slate-600 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 placeholder-slate-500" placeholder="••••••••" required>
                </div>

                <div id="errorMessage" class="hidden p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm text-center"></div>

                <button type="submit" class="w-full text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-indigo-500/50 font-medium rounded-lg text-sm px-5 py-3 text-center transition-all shadow-lg shadow-indigo-500/30">
                    Ingresar
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-slate-400 text-sm">¿Primera vez aquí?</p>
                <a href="activate_account.php" class="text-emerald-400 hover:text-emerald-300 font-medium text-sm hover:underline mt-1 inline-block">
                    Activa tu cuenta
                </a>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-700/50"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-slate-800 px-2 text-slate-500">O ingresa como</span>
                    </div>
                </div>

                <a href="index.php" class="block w-full py-3 px-4 bg-slate-900/50 hover:bg-slate-900 text-slate-300 hover:text-white text-center rounded-xl border border-slate-700 transition-all duration-300 text-sm font-medium group">
                    <span class="group-hover:-translate-x-1 inline-block transition-transform duration-300">&larr; Administrativo / Técnico</span>
                </a>
            </div>
        </div>
        
        <div class="text-center mt-8 text-slate-500 text-xs">
            &copy; <?php echo date('Y'); ?> FiberLink Telecomunicaciones
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const dni = document.getElementById('dni').value;
            const password = document.getElementById('password').value;
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('errorMessage');

            btn.disabled = true;
            btn.textContent = 'Verificando...';
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('api/client_auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ dni, password })
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = 'client_dashboard.php';
                } else {
                    errorDiv.textContent = data.message || 'Error de autenticación';
                    errorDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Ingresar';
                }
            } catch (error) {
                console.error(error);
                errorDiv.textContent = 'Error de conexión';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Ingresar';
            }
        });
    </script>
</body>
</html>
