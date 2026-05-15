<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FiberLink</title>
    <link href="./src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .perspective-1000 { perspective: 1000px; }
        .rotate-y-180 { transform: rotateY(180deg); }
        .backface-hidden { backface-visibility: hidden; }
        .transform-style-3d { transform-style: preserve-3d; }
    </style>
</head>
<body class="bg-slate-950 text-slate-50 h-screen flex justify-center items-center overflow-hidden relative selection:bg-indigo-500/30">
    
    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none transition-colors duration-700" id="bgContainer">
        <div id="blob1" class="absolute top-[-10%] left-[10%] w-[500px] h-[500px] bg-indigo-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse transition-colors duration-700"></div>
        <div id="blob2" class="absolute bottom-[-10%] right-[10%] w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] mix-blend-screen animate-pulse delay-1000 transition-colors duration-700"></div>
    </div>

    <!-- Flip Container -->
    <div class="w-full max-w-md p-4 perspective-1000">
        <div id="cardContainer" class="relative w-full transition-transform duration-700 transform-style-3d">
            
            <!-- FRONT: Admin Login -->
            <div class="w-full backface-hidden bg-slate-900/40 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl relative overflow-hidden">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent mb-2">FiberLink</h1>
                    <p class="text-slate-400 text-sm">Portal Administrativo</p>
                    <div class="mt-2 space-y-1">
                        <p class="text-[10px] text-slate-500 italic">User: <span class="text-indigo-400/80">admin</span></p>
                        <p class="text-[10px] text-slate-500 italic">Pass: <span class="text-indigo-400/80">admin123</span></p>
                    </div>
                </div>

                <form id="adminForm" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-slate-400 uppercase ml-1">Usuario</label>
                        <input type="text" id="adminUser" class="w-full px-4 py-3 bg-slate-950/50 border border-slate-700/50 rounded-xl text-slate-200 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none" placeholder="Usuario">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-slate-400 uppercase ml-1">Contraseña</label>
                        <input type="password" id="adminPass" class="w-full px-4 py-3 bg-slate-950/50 border border-slate-700/50 rounded-xl text-slate-200 focus:border-indigo-500/50 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none" placeholder="••••••••">
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold rounded-xl shadow-lg shadow-indigo-500/20 transition-all">
                        Ingresar
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-white/5 text-center">
                    <p class="text-slate-500 text-sm mb-3">¿Eres cliente?</p>
                    <button onclick="flipCard()" class="text-emerald-400 hover:text-emerald-300 text-sm font-medium flex items-center justify-center gap-2 mx-auto group transition-colors">
                        Ir al Portal de Clientes
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </button>
                </div>
            </div>

            <!-- BACK: Client Login -->
            <div class="absolute top-0 left-0 w-full h-full backface-hidden rotate-y-180 bg-slate-900/40 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl overflow-hidden">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent mb-2">FiberLink</h1>
                    <p class="text-slate-400 text-sm">Portal de Clientes</p>
                </div>

                <form id="clientForm" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-slate-400 uppercase ml-1">DNI / RUC</label>
                        <input type="text" id="clientDni" class="w-full px-4 py-3 bg-slate-950/50 border border-slate-700/50 rounded-xl text-slate-200 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none" placeholder="Documento">
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-medium text-slate-400 uppercase ml-1">Contraseña</label>
                        <input type="password" id="clientPass" class="w-full px-4 py-3 bg-slate-950/50 border border-slate-700/50 rounded-xl text-slate-200 focus:border-emerald-500/50 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none" placeholder="••••••••">
                    </div>
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold rounded-xl shadow-lg shadow-emerald-500/20 transition-all">
                        Ingresar
                    </button>
                </form>

                <div class="mt-6 text-center space-y-4">
                    <a href="activate_account.php" class="text-xs text-slate-400 hover:text-white transition-colors">¿Primera vez? Activa tu cuenta</a>
                    
                    <div class="pt-4 border-t border-white/5">
                        <button onclick="flipCard()" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium flex items-center justify-center gap-2 mx-auto group transition-colors">
                            <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path></svg>
                            Volver al Admin
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        const card = document.getElementById('cardContainer');
        const blob1 = document.getElementById('blob1');
        const blob2 = document.getElementById('blob2');
        let isFlipped = false;

        function flipCard() {
            isFlipped = !isFlipped;
            if(isFlipped) {
                // Client Mode (Green/Teal)
                card.classList.add('rotate-y-180');
                blob1.classList.remove('bg-indigo-600/20');
                blob1.classList.add('bg-emerald-600/20');
                
                blob2.classList.remove('bg-violet-600/20');
                blob2.classList.add('bg-teal-600/20');
            } else {
                // Admin Mode (Indigo/Violet)
                card.classList.remove('rotate-y-180');
                blob1.classList.remove('bg-emerald-600/20');
                blob1.classList.add('bg-indigo-600/20');
                
                blob2.classList.remove('bg-teal-600/20');
                blob2.classList.add('bg-violet-600/20');
            }
        }

        // Admin Login Logic
        document.getElementById('adminForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.textContent;
            btn.textContent = 'Verificando...';
            btn.disabled = true;

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        username: document.getElementById('adminUser').value,
                        password: document.getElementById('adminPass').value
                    })
                });
                const data = await response.json();
                
                if(response.ok) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('user', JSON.stringify(data.user));
                    window.location.href = data.user.role === 'technician' ? 'public/technician/dashboard.php' : 'public/dashboard.php';
                } else {
                    alert(data.message || 'Error de acceso');
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch(err) {
                console.error(err);
                alert('Error de conexión');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });

        // Client Login Logic
        document.getElementById('clientForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            const originalText = btn.textContent;
            btn.textContent = 'Verificando...';
            btn.disabled = true;

            try {
                const response = await fetch('api/client_auth.php?action=login', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        dni: document.getElementById('clientDni').value,
                        password: document.getElementById('clientPass').value
                    })
                });
                const data = await response.json();
                
                if(response.ok) {
                    window.location.href = 'client_dashboard.php';
                } else {
                    alert(data.message || 'Error de acceso');
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            } catch(err) {
                console.error(err);
                alert('Error de conexión');
                btn.textContent = originalText;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
