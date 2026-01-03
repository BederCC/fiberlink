<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer Contraseña - FiberLink</title>
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
                <h1 class="text-2xl font-bold text-white mb-2">Crear Contraseña</h1>
                <p class="text-slate-400 text-sm">Ingresa tu nueva contraseña para acceder</p>
            </div>

            <form id="passwordForm" class="space-y-6">
                <input type="hidden" id="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nueva Contraseña</label>
                    <input type="password" id="password" class="w-full bg-slate-900/50 border border-slate-600 text-white text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-3 placeholder-slate-500" placeholder="••••••••" required minlength="6">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" class="w-full bg-slate-900/50 border border-slate-600 text-white text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-3 placeholder-slate-500" placeholder="••••••••" required minlength="6">
                </div>

                <div id="errorDiv" class="hidden p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm text-center"></div>

                <button type="submit" class="w-full text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-emerald-500/50 font-medium rounded-lg text-sm px-5 py-3 text-center transition-all">
                    Guardar Contraseña
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('passwordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const token = document.getElementById('token').value;
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const btn = e.target.querySelector('button');
            const errorDiv = document.getElementById('errorDiv');

            if (password !== confirm) {
                errorDiv.textContent = 'Las contraseñas no coinciden';
                errorDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Guardando...';
            errorDiv.classList.add('hidden');

            try {
                const response = await fetch('api/client_auth.php?action=set_password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token, password })
                });
                const data = await response.json();

                if (response.ok) {
                    alert('Contraseña actualizada correctamente. Ahora puedes iniciar sesión.');
                    window.location.href = 'client_login.php';
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Guardar Contraseña';
                }
            } catch (error) {
                console.error(error);
                errorDiv.textContent = 'Error de conexión';
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Guardar Contraseña';
            }
        });
    </script>
</body>
</html>
