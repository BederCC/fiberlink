<?php
$json_path = '../data/payment_metrics_sim.json';
if (!file_exists($json_path)) {
    die("No metrics data found. Please run the generator first.");
}

$metrics = json_decode(file_get_contents($json_path), true);

// Calculations
$total_payments = count($metrics);
$total_seconds = 0;
$min_seconds = 999999;
$max_seconds = 0;
$by_day = array_fill(1, 31, 0);
$durations = [];

foreach ($metrics as $m) {
    $total_seconds += $m['duration_seconds'];
    if ($m['duration_seconds'] < $min_seconds) $min_seconds = $m['duration_seconds'];
    if ($m['duration_seconds'] > $max_seconds) $max_seconds = $m['duration_seconds'];
    
    $day = (int)date('d', strtotime($m['payment_timestamp']));
    $by_day[$day]++;
    $durations[] = $m['duration_seconds'];
}

$avg_seconds = $total_payments > 0 ? round($total_seconds / $total_payments, 2) : 0;

// Sort by date for the table
usort($metrics, function($a, $b) {
    return strtotime($b['payment_timestamp']) - strtotime($a['payment_timestamp']);
});

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas de Pago - FiberLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --secondary: #a855f7;
            --accent: #22d3ee;
            --bg-dark: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            margin-bottom: 3rem;
            text-align: center;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, var(--primary-light), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        header p {
            color: var(--text-dim);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            padding: 1.5rem;
            border-radius: 1.5rem;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .stat-card h3 {
            color: var(--text-dim);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-card .unit {
            font-size: 1rem;
            color: var(--text-dim);
            margin-left: 0.2rem;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .chart-container {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            padding: 2rem;
            border-radius: 1.5rem;
            height: 400px;
        }

        .table-container {
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.2rem 1.5rem;
            color: var(--text-dim);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.95rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-fast { background: rgba(34, 211, 238, 0.1); color: var(--accent); }
        .badge-medium { background: rgba(99, 102, 241, 0.1); color: var(--primary-light); }
        .badge-slow { background: rgba(244, 63, 94, 0.1); color: #fb7185; }

        .btn-download {
            margin-top: 1.5rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
            filter: brightness(1.1);
        }

        .btn-download:active {
            transform: translateY(0);
        }

        /* Hide button during PDF generation */
        .no-export {
            display: none !important;
        }

        /* PDF / Print Styles */
        body.pdf-mode {
            background: white !important;
            color: #1e293b !important;
            padding: 10mm !important;
        }

        .pdf-mode header h1 {
            -webkit-text-fill-color: #1e293b !important;
            color: #1e293b !important;
            font-size: 1.8rem;
        }

        .pdf-mode .stat-card {
            background: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
            backdrop-filter: none !important;
            color: #1e293b !important;
            padding: 1rem;
        }

        .pdf-mode .stat-card .value {
            color: #1e293b !important;
            font-size: 1.5rem;
        }

        .pdf-mode .chart-container {
            display: none !important; /* User said "no colores solo info y tabla" */
        }

        .pdf-mode .table-container {
            background: white !important;
            border: 1px solid #e2e8f0 !important;
        }

        .pdf-mode table {
            font-size: 0.75rem; /* Small table as requested */
        }

        .pdf-mode th {
            background: #f1f5f9 !important;
            color: #475569 !important;
            padding: 0.5rem;
        }

        .pdf-mode td {
            padding: 0.4rem 0.5rem !important;
            border-bottom: 1px solid #e2e8f0 !important;
            color: #334155 !important;
        }

        .pdf-mode .badge {
            border: 1px solid #cbd5e1 !important;
            background: transparent !important;
            color: #334155 !important;
            padding: 0.1rem 0.4rem;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            .chart-container {
                height: 300px;
            }
            body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Análisis de Tiempos de Pago</h1>
        <p>Métricas simuladas del periodo: Enero 2026</p>
        <button id="downloadPdf" class="btn-download">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Descargar Reporte PDF
        </button>
    </header>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Pagos</h3>
            <div class="value"><?php echo $total_payments; ?></div>
        </div>
        <div class="stat-card">
            <h3>Tiempo Promedio</h3>
            <div class="value"><?php echo round($avg_seconds / 60, 1); ?><span class="unit">min</span></div>
        </div>
        <div class="stat-card">
            <h3>Mínimo</h3>
            <div class="value"><?php echo $min_seconds; ?><span class="unit">seg</span></div>
        </div>
        <div class="stat-card">
            <h3>Máximo</h3>
            <div class="value"><?php echo round($max_seconds / 60, 1); ?><span class="unit">min</span></div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-container">
            <canvas id="lineChart"></canvas>
        </div>
        <div class="chart-container">
            <canvas id="distChart"></canvas>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>DNI</th>
                    <th>Fecha de Pago</th>
                    <th>Duración</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($metrics as $m): 
                    $status_class = 'badge-medium';
                    $status_text = 'Normal';
                    if ($m['duration_seconds'] <= 50) {
                        $status_class = 'badge-fast';
                        $status_text = 'Rápido';
                    } elseif ($m['duration_seconds'] >= 130) {
                        $status_class = 'badge-slow';
                        $status_text = 'Lento';
                    }
                ?>
                <tr>
                    <td style="font-weight: 600;"><?php echo $m['client_name']; ?></td>
                    <td style="color: var(--text-dim);"><?php echo $m['dni']; ?></td>
                    <td><?php echo date('d M, Y H:i', strtotime($m['payment_timestamp'])); ?></td>
                    <td><?php echo $m['duration_seconds']; ?> seg</td>
                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Config Chart.js
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Outfit', sans-serif";

    // Line Chart: Payments per Day
    const ctxLine = document.getElementById('lineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(range(1, 31)); ?>,
            datasets: [{
                label: 'Pagos por Día',
                data: <?php echo json_encode(array_values($by_day)); ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#22d3ee',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Volumen de Pagos en Enero', color: '#f8fafc', font: { size: 16 } }
            },
            scales: {
                y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });

    // Distribution Chart (Buckets)
    const durations = <?php echo json_encode($durations); ?>;
    const buckets = {'35-50s': 0, '51-90s': 0, '91-130s': 0, '130s+': 0};
    durations.forEach(d => {
        if (d <= 50) buckets['35-50s']++;
        else if (d <= 90) buckets['51-90s']++;
        else if (d <= 130) buckets['91-130s']++;
        else buckets['130s+']++;
    });

    const ctxDist = document.getElementById('distChart').getContext('2d');
    new Chart(ctxDist, {
        type: 'bar',
        data: {
            labels: Object.keys(buckets),
            datasets: [{
                data: Object.values(buckets),
                backgroundColor: [
                    'rgba(34, 211, 238, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                    'rgba(244, 63, 94, 0.8)'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Distribución de Tiempos', color: '#f8fafc', font: { size: 16 } }
            },
            scales: {
                y: { grid: { color: 'rgba(255, 255, 255, 0.05)' }, beginAtZero: true },
                x: { grid: { display: false } }
            }
        }
    });

    // PDF Export Logic
    document.getElementById('downloadPdf').addEventListener('click', function() {
        const element = document.body;
        const button = this;
        
        // Prepare for PDF: Add pdf-mode and hide button
        button.classList.add('no-export');
        element.classList.add('pdf-mode');
        
        const opt = {
            margin: [5, 5, 5, 5],
            filename: 'Reporte_Metricas_Pago_Limpio.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                backgroundColor: '#ffffff' 
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Generate PDF
        html2pdf().set(opt).from(element).save().then(() => {
            // Restore original styles
            button.classList.remove('no-export');
            element.classList.remove('pdf-mode');
        });
    });
</script>

</body>
</html>
