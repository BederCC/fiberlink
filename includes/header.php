<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FiberLink</title>
    <link href="../src/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Auth Check
        if (!localStorage.getItem('token')) {
            window.location.href = 'index.php';
        }
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
