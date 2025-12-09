<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FiberLink - Iniciar Sesión</title>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        if (localStorage.getItem('token')) {
            window.location.href = 'public/dashboard.php';
        }
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-950 text-slate-50 h-screen flex justify-center items-center overflow-hidden relative selection:bg-indigo-500/30">
    
    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[10%] w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse"></div>
        <div class="absolute bottom-[-10%] right-[10%] w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse delay-1000"></div>
    </div>

    <div class="w-full max-w-md p-8">
        <div class="backdrop-blur-xl bg-slate-900/40 border border-white/10 rounded-3xl p-10 shadow-2xl shadow-black/50 relative overflow-hidden group">
            
            <!-- Shine effect -->
            <div class="absolute inset-0 bg-gradient-to-tr from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

            <div class="text-center mb-10 relative z-10">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent mb-2 tracking-tight">FiberLink</h1>
                <p class="text-slate-400 text-sm font-light">Bienvenido al sistema de gestión</p>
            </div>

            <div id="alertMessage" class="hidden mb-6 p-4 rounded-xl text-sm font-medium border"></div>

            <form id="loginForm" class="space-y-6 relative z-10">
                <div class="space-y-2">
                    <label for="username" class="block text-xs font-medium text-slate-400 uppercase tracking-wider ml-1">Usuario</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within/input:text-indigo-400 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <input type="text" id="username" name="username" 
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-950/50 border border-slate-700/50 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-300" 
                            placeholder="Ingresa tu usuario" required>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label for="password" class="block text-xs font-medium text-slate-400 uppercase tracking-wider ml-1">Contraseña</label>
                    <div class="relative group/input">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500 group-focus-within/input:text-indigo-400 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" 
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-950/50 border border-slate-700/50 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-300" 
                            placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" id="submitBtn" 
                    class="w-full py-4 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transform hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">
                    <svg id="spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="btnText">Ingresar al Sistema</span>
                </button>
            </form>
        </div>
        
        <p class="text-center text-slate-600 text-xs mt-8">
            &copy; <?php echo date('Y'); ?> FiberLink v1.0
        </p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const alertBox = document.getElementById('alertMessage');
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const btnText = document.getElementById('btnText');

            // Reset UI
            alertBox.classList.add('hidden');
            alertBox.classList.remove('bg-red-500/10', 'border-red-500/20', 'text-red-400', 'bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-400');
            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Verificando...';

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: username,
                        password: password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // Success
                    alertBox.textContent = '¡Credenciales correctas! Redirigiendo...';
                    alertBox.classList.remove('hidden');
                    alertBox.classList.add('bg-emerald-500/10', 'border-emerald-500/20', 'text-emerald-400');
                    
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));

                    setTimeout(() => {
                        alert('Bienvenido ' + data.user.full_name);
                        submitBtn.disabled = false;
                        spinner.classList.add('hidden');
                        btnText.textContent = 'Ingresar al Sistema';
                        window.location.href = 'public/dashboard.php';
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Error en el inicio de sesión');
                }
            } catch (error) {
                alertBox.textContent = error.message;
                alertBox.classList.remove('hidden');
                alertBox.classList.add('bg-red-500/10', 'border-red-500/20', 'text-red-400');
                
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Ingresar al Sistema';
            }
        });
    </script>
</body>
</html>
