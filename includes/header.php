<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FiberLink</title>
    <?php
    require_once __DIR__ . '/../config.php';
    ?>
    <link href="<?php echo ASSETS_URL; ?>/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Auth Check
        if (!localStorage.getItem('token')) {
            window.location.href = '<?php echo BASE_URL; ?>/index.php';
        }

        // Global Fetch Interceptor to sync logged user info with backend audit log
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function(input, init) {
                init = init || {};
                init.headers = init.headers || {};
                
                const userStr = localStorage.getItem('user');
                if (userStr) {
                    try {
                        const user = JSON.parse(userStr);
                        if (user && user.username) {
                            const encodedUser = encodeURIComponent(JSON.stringify(user));
                            if (init.headers instanceof Headers) {
                                init.headers.set('X-Logged-User', encodedUser);
                            } else {
                                init.headers['X-Logged-User'] = encodedUser;
                            }
                        }
                    } catch (e) {
                        console.error('Error parsing user from localStorage:', e);
                    }
                }
                return originalFetch(input, init);
            };
        })();
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a; 
        }
        ::-webkit-scrollbar-thumb {
            background: #334155; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569; 
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-50">
