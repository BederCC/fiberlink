<?php
require_once '../vendor/autoload.php';

class PdfGenerator {
    private $conn;

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    public function generateInstallationPdf($installation_id, $output = 'I') {
        // Fetch installation details with service and plan info
        $sql = "SELECT i.*, c.fullname as client_name, c.address, c.phone, c.dni_ruc, 
                       u.full_name as tech_name,
                       s.ip_address, s.mac_address, s.router_model,
                       p.name as plan_name, p.price as plan_price, p.speed_mbps
                FROM installations i 
                JOIN clients c ON i.client_id = c.id 
                LEFT JOIN users u ON i.technician_id = u.id 
                LEFT JOIN services s ON i.service_id = s.id
                LEFT JOIN plans p ON s.plan_id = p.id
                WHERE i.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id", $installation_id);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch accessories/materials
        $sql_acc = "SELECT p.name, d.quantity 
                    FROM installation_details d 
                    JOIN products p ON d.product_id = p.id 
                    WHERE d.service_id = :service_id";
        $stmt_acc = $this->conn->prepare($sql_acc);
        $stmt_acc->bindParam(":service_id", $data['service_id']);
        $stmt_acc->execute();
        $accessories = $stmt_acc->fetchAll(PDO::FETCH_ASSOC);

        // Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('FiberLink');
        $pdf->SetTitle('Hoja de Instalación #' . $installation_id);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);

        // Add a page
        $pdf->AddPage();

        // --- HEADER ---
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(0, 150, 200); // FiberLink Blue
        $pdf->Cell(60, 10, 'FIBERLINK', 0, 0, 'L');

        // Barcode
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        $pdf->write1DBarcode(str_pad($installation_id, 8, '0', STR_PAD_LEFT), 'C128', 80, 10, 50, 15, 0.4, $style, 'N');

        // Company Info
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(140, 10);
        $pdf->MultiCell(60, 15, "FIBERLINK EIRL\nRUC: 20602045758\nDirección: Av. Antonio Lorena 15\nCel: 976 366 075\nSoporte Técnico 24x7", 0, 'R', 0, 1, '', '', true);

        $pdf->Ln(5);

        // Title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'HOJA DE INSTALACIÓN', 0, 1, 'C');

        // --- STYLES ---
        $html_styles = '
        <style>
            table { border-collapse: collapse; width: 100%; font-family: helvetica; font-size: 9pt; }
            th { background-color: #f0f0f0; font-weight: bold; padding: 4px; border: 1px solid #ccc; text-align: center; }
            td { padding: 4px; border: 1px solid #ccc; }
            .label { font-weight: bold; background-color: #f9f9f9; width: 30%; }
            .value { width: 70%; }
            .section-header { background-color: #e0e0e0; font-weight: bold; padding: 5px; font-size: 10pt; border: 1px solid #ccc; margin-top: 10px; }
            .small-text { font-size: 8pt; }
        </style>
        ';

        // --- DATOS DEL CLIENTE ---
        $html = $html_styles . '
        <div class="section-header" style="text-align: center;">DATOS DEL CLIENTE</div>
        <table cellpadding="3">
            <tr>
                <td class="label">Nombre y Apellidos:</td>
                <td class="value">' . $data['client_name'] . '</td>
            </tr>
            <tr>
                <td class="label">N° DNI:</td>
                <td class="value">' . $data['dni_ruc'] . '</td>
            </tr>
            <tr>
                <td class="label">Fecha de Instalación:</td>
                <td class="value">' . ($data['completed_date'] ? date('d/m/Y h:i A', strtotime($data['completed_date'])) : 'Pendiente') . '</td>
            </tr>
            <tr>
                <td class="label">N° Teléfono móvil:</td>
                <td class="value">' . $data['phone'] . '</td>
            </tr>
            <tr>
                <td class="label">Dirección:</td>
                <td class="value">' . $data['address'] . '</td>
            </tr>
            <tr>
                <td class="label">Tipo de Servicio:</td>
                <td class="value">DOMICILIARIO</td>
            </tr>
            <tr>
                <td class="label">Asesor Comercial:</td>
                <td class="value">FIBERLINK</td>
            </tr>
            <tr>
                <td class="label">Tipo de Comprobante:</td>
                <td class="value">RECIBO</td>
            </tr>
        </table>
        <br>

        <div class="section-header">SERVICIOS CONTRATADOS</div>
        <table cellpadding="3">
            <tr>
                <th width="30%">Plan</th>
                <th width="20%">N° IP</th>
                <th width="10%">AP</th>
                <th width="40%">Usuario</th>
            </tr>
            <tr>
                <td>' . $data['plan_name'] . ' (' . $data['speed_mbps'] . 'Mbps)</td>
                <td>' . ($data['ip_address'] ?: 'DHCP') . '</td>
                <td>-</td>
                <td>' . $data['client_name'] . '</td>
            </tr>
        </table>
        <br>

        <div class="section-header">EQUIPOS INSTALADOS EN CALIDAD DE PRESTAMO (PRECIO REFERENCIAL S/. 150.00)</div>
        <table cellpadding="3">
            <tr>
                <th width="20%">Equipo</th>
                <th width="30%">Descripción</th>
                <th width="25%">N° Mac</th>
                <th width="25%">N° Serie</th>
            </tr>
            <tr>
                <td>Router</td>
                <td>' . ($data['router_model'] ?: 'ONU Standard') . '</td>
                <td>' . $data['mac_address'] . '</td>
                <td>-</td>
            </tr>
        </table>
        <br>

        <div class="section-header">ACCESORIOS INSTALADOS</div>
        <table cellpadding="3">
            <tr>
                <th width="70%">Materiales</th>
                <th width="30%">Cantidad</th>
            </tr>';

        if (count($accessories) > 0) {
            foreach ($accessories as $acc) {
                $html .= '
                <tr>
                    <td>' . $acc['name'] . '</td>
                    <td style="text-align: center;">' . $acc['quantity'] . '</td>
                </tr>';
            }
        } else {
            $html .= '
            <tr>
                <td colspan="2" style="text-align: center;">No hay ningún accesorio registrado.</td>
            </tr>';
        }

        $html .= '</table>
        <br>

        <div class="section-header">Observaciones</div>
        <table cellpadding="5">
            <tr>
                <td style="background-color: #f9f9f9;">
                    <strong>' . $data['plan_name'] . '</strong><br>
                    MENS. S/. ' . number_format($data['plan_price'], 2) . '<br>
                    IP: ' . ($data['ip_address'] ?: 'Automática') . '<br><br>
                    <span style="color: #0000FF; font-size: 8pt;">(EQUIPOS EN CALIDAD DE PRESTAMO) EVITE SER REPORTADO A LA CENTRAL DE RIESGO DEVOLVIENDO EL EQUIPO ROUTER WI-FI EN CASO DE CORTE O SUSPENSIÓN DEFINITIVA.</span><br>
                    <br>
                    ' . nl2br($data['notes']) . '
                </td>
            </tr>
        </table>
        <br>

        <table style="border: none;">
            <tr>
                <td style="border: none; font-weight: bold;">DATOS:</td>
                <td style="border: none;">NUMERO DE SOPORTE TECNICO: 913 153 730</td>
            </tr>
            <tr>
                <td style="border: none;"></td>
                <td style="border: none;">NUMERO DE AREA DE PAGOS: 942 787 850</td>
            </tr>
        </table>

        <br><br><br><br><br>
        <table style="border: none;">
            <tr>
                <td style="border: none; text-align: center;">
                    __________________________<br>
                    <strong>' . $data['client_name'] . '</strong><br>
                    Cliente
                </td>
                <td style="border: none; text-align: center;">
                    __________________________<br>
                    <strong>' . ($data['tech_name'] ?: 'Técnico Responsable') . '</strong><br>
                    Técnico
                </td>
            </tr>
        </table>
        <br><br>
        <div style="text-align: right; font-size: 8pt;">Fecha de Impresión: ' . date('d/m/Y h:i A') . '</div>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Output PDF
        return $pdf->Output('Hoja_Instalacion_' . $installation_id . '.pdf', $output);
    }
    public function generateInvoicePdf($invoice_id, $output = 'I') {
        // 1. Fetch Invoice & Client Details
        $query = "SELECT i.*, c.fullname, c.dni_ruc, c.email, c.address, c.phone 
                  FROM invoices i 
                  JOIN clients c ON i.client_id = c.id 
                  WHERE i.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $invoice_id);
        $stmt->execute();
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            die('Factura no encontrada');
        }

        // 2. Fetch Invoice Items
        $q_items = "SELECT * FROM invoice_items WHERE invoice_id = :id";
        $s_items = $this->conn->prepare($q_items);
        $s_items->bindParam(":id", $invoice_id);
        $s_items->execute();
        $items = $s_items->fetchAll(PDO::FETCH_ASSOC);

        // 3. Fetch Payments
        $q_payments = "SELECT * FROM payments WHERE invoice_id = :id";
        $s_payments = $this->conn->prepare($q_payments);
        $s_payments->bindParam(":id", $invoice_id);
        $s_payments->execute();
        $payments = $s_payments->fetchAll(PDO::FETCH_ASSOC);

        // 4. Create PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('FiberLink System');
        $pdf->SetAuthor('FiberLink');
        $pdf->SetTitle('Recibo ' . $invoice['invoice_number']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // --- CALCULATIONS ---
        $total = floatval($invoice['total_amount']);
        $base = $total / 1.18;
        $igv = $total - $base;
        $paid_amount = 0;
        foreach ($payments as $p) {
            $paid_amount += floatval($p['amount']);
        }
        $balance = $total - $paid_amount;
        $status_text = ($balance <= 0) ? 'PAGADO' : 'PENDIENTE';
        $header_color = ($balance <= 0) ? '#10b981' : '#ef4444'; // Green or Red

        // --- STYLES ---
        $html_styles = '
        <style>
            table { width: 100%; border-collapse: collapse; font-family: helvetica; font-size: 9pt; }
            th { background-color: #f1f5f9; font-weight: bold; padding: 6px; text-align: center; }
            td { padding: 6px; }
            .header-bar { background-color: ' . $header_color . '; color: white; text-align: center; font-weight: bold; font-size: 14pt; padding: 5px; }
            .section-title { font-weight: bold; background-color: #f1f5f9; padding: 5px; margin-bottom: 5px; }
            .label { font-weight: bold; color: #64748b; font-size: 8pt; }
            .value { color: #0f172a; font-size: 9pt; }
            .total-row { font-weight: bold; }
            .small-text { font-size: 8pt; color: #64748b; }
        </style>
        ';

        // --- CONTENT ---
        $html = $html_styles;

        // Header Bar
        $html .= '<div class="header-bar">' . $status_text . '</div><br>';

        // Top Section (Logo & Receipt Info)
        $html .= '
        <table border="0">
            <tr>
                <td width="50%">
                    <span style="font-size: 20pt; font-weight: bold; color: #0ea5e9;">FIBERLINK</span><br>
                    <span style="font-size: 8pt;">EL FUTURO ES AHORA</span>
                </td>
                <td width="50%" style="text-align: right;">
                    <strong>RECIBO # ' . $invoice['invoice_number'] . '</strong><br>
                    <span class="small-text">Fecha emisión (Documento): ' . date('d/m/Y', strtotime($invoice['issue_date'])) . '</span><br>
                    <span class="small-text">Vencimiento de pago (Límite): ' . date('d/m/Y', strtotime($invoice['due_date'])) . '</span><br>
                    <span style="font-size: 7pt; color: #94a3b8; font-style: italic;">* Fechas de generación del recibo y límite de pago</span>
                </td>
            </tr>
        </table>
        <br><br>
        ';

        // From / To
        $html .= '
        <table border="0">
            <tr>
                <td width="50%" style="border-right: 1px solid #e2e8f0;">
                    <strong>De</strong><br>
                    FIBERLINK EIRL<br>
                    Ruc 20602045758<br>
                    PRO. AV. ANTONIO LORENA 15<br>
                    Teléfono 976 366 075
                </td>
                <td width="50%" style="padding-left: 10px;">
                    <strong>Para</strong><br>
                    ' . strtoupper($invoice['fullname']) . '<br>
                    ' . $invoice['dni_ruc'] . '<br>
                    ' . $invoice['address'] . '<br>
                    N° Cel: ' . $invoice['phone'] . '
                </td>
            </tr>
        </table>
        <br><br>
        ';

        // Items Table
        $html .= '
        <table border="0" cellpadding="5">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th width="50%" style="text-align: left;">Descripción</th>
                    <th width="15%" style="text-align: right;">Precio</th>
                    <th width="10%" style="text-align: right;">Imp%</th>
                    <th width="10%" style="text-align: center;">Cant.</th>
                    <th width="15%" style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($items as $item) {
            $item_total = floatval($item['amount']);
            $item_base = $item_total / 1.18;

            $html .= '
            <tr>
                <td style="border-bottom: 1px solid #f1f5f9;">
                    ' . $item['description'] . '
                    ' . ($invoice['type'] === 'monthly' ? '<br><span class="small-text">Facturación del ' . date('d/m/Y', strtotime($invoice['due_date'] . ' -1 month +1 day')) . ' al ' . date('d/m/Y', strtotime($invoice['due_date'])) . '</span>' : '') . '
                </td>
                <td style="text-align: right; border-bottom: 1px solid #f1f5f9;">S/. ' . number_format($item_base, 2) . '</td>
                <td style="text-align: right; border-bottom: 1px solid #f1f5f9;">18%</td>
                <td style="text-align: center; border-bottom: 1px solid #f1f5f9;">1</td>
                <td style="text-align: right; border-bottom: 1px solid #f1f5f9;">S/. ' . number_format($item_base, 2) . '</td>
            </tr>';
        }

        $html .= '
            </tbody>
        </table>
        ';

        // Amount in Words
        $html .= '
        <div style="background-color: #e2e8f0; padding: 5px; font-weight: bold; font-size: 9pt; margin-top: 10px;">
            SON: ' . $this->numtoletras($total) . ' SOL
        </div>
        <br><br>
        ';
        
        $pdf->writeHTML($html, true, false, true, false, '');
        $html = ''; // Reset HTML

        // Barcode & Totals
        // We need to position them side by side. 
        // Barcode on Left (approx X=15, Y=current), Totals on Right (approx X=100)
        
        $y = $pdf->GetY();
        $x = $pdf->GetX();
        
        // Barcode
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        // Width approx 50mm, Height 15mm
        $pdf->write1DBarcode($invoice['invoice_number'], 'C128', 15, $y, 60, 18, 0.4, $style, 'N');
        
        // Totals Table (Right side)
        // We can use writeHTMLCell for the table to position it
        $totals_html = '
        <table border="0" cellpadding="3">
            <tr>
                <td style="text-align: right; font-weight: bold;">SUBTOTAL :</td>
                <td style="text-align: right;">S/. ' . number_format($base, 2) . '</td>
            </tr>
            <tr>
                <td style="text-align: right; font-weight: bold;">IMPUESTO (18%) :</td>
                <td style="text-align: right;">S/. ' . number_format($igv, 2) . '</td>
            </tr>
            <tr>
                <td style="text-align: right; font-weight: bold;">DESCUENTO :</td>
                <td style="text-align: right;">S/. 0.00</td>
            </tr>
            <tr>
                <td style="text-align: right; font-weight: bold;">TOTAL :</td>
                <td style="text-align: right;">S/. ' . number_format($total, 2) . '</td>
            </tr>
        </table>';
        
        $pdf->writeHTMLCell(90, '', 105, $y, $totals_html, 0, 1, 0, true, '', true);
        
        $pdf->Ln(10); // Spacing after barcode/totals section

        // Transactions
        $html .= '
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">Transacciones</div>
        <table border="0" cellpadding="5">
            <thead>
                <tr style="background-color: #f1f5f9;">
                    <th width="30%">Fecha</th>
                    <th width="30%">Forma pago</th>
                    <th width="20%">Nº transacción</th>
                    <th width="20%" style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>';
        
        if (count($payments) > 0) {
            foreach ($payments as $pay) {
                $html .= '
                <tr>
                    <td style="text-align: center; border-bottom: 1px solid #f1f5f9;">' . $pay['payment_date'] . '</td>
                    <td style="text-align: center; border-bottom: 1px solid #f1f5f9;">' . ucfirst($pay['payment_method']) . '</td>
                    <td style="text-align: center; border-bottom: 1px solid #f1f5f9;">' . ($pay['transaction_id'] ?: '-') . '</td>
                    <td style="text-align: right; border-bottom: 1px solid #f1f5f9;">S/. ' . number_format($pay['amount'], 2) . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="4" style="text-align: center;">No hay pagos registrados</td></tr>';
        }

        $html .= '
                <tr style="background-color: #f1f5f9;">
                    <td colspan="3" style="text-align: right; font-weight: bold;">Balance</td>
                    <td style="text-align: right; font-weight: bold;">S/. ' . number_format($balance, 2) . '</td>
                </tr>
            </tbody>
        </table>
        ';

        // Footer
        $html .= '
        <div style="position: absolute; bottom: 10px; width: 100%; text-align: center; font-size: 8pt; color: #64748b;">
            PDF Generado ' . date('d/m/Y h:i a') . '
        </div>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf->Output('Recibo_' . $invoice['invoice_number'] . '.pdf', $output);
    }

    private function numtoletras($xcifra) {
        $xarray = array(0 => "Cero",
            1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
            "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
            "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
            100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
        );
        $xcifra = trim($xcifra);
        $xaux_int = $xcifra;
        $xdecimales = "00";
        if (strpos($xcifra, ".") !== false) {
            $xaux_int = substr($xcifra, 0, strpos($xcifra, "."));
            $xdecimales = substr($xcifra, strpos($xcifra, ".") + 1);
        }
        
        $valor = (int) $xaux_int;
        if ($valor == 0) return "CERO";
        
        $str = "";
        $miles = floor($valor / 1000);
        $resto = $valor % 1000;
        
        if ($miles > 0) {
            if ($miles == 1) $str .= "MIL ";
            else $str .= $this->centenas($miles) . " MIL ";
        }
        
        if ($resto > 0 || $valor == 0) {
            $str .= $this->centenas($resto);
        }
        
        return trim($str);
    }

    private function centenas($n) {
        $c = floor($n / 100);
        $resto = $n % 100;
        $str = "";
        if ($c > 0) {
            if ($c == 1) {
                if ($resto > 0) $str = "CIENTO ";
                else $str = "CIEN ";
            } else {
                $centenas = ["", "CIENTO", "DOSCIENTOS", "TRESCIENTOS", "CUATROCIENTOS", "QUINIENTOS", "SEISCIENTOS", "SETECIENTOS", "OCHOCIENTOS", "NOVECIENTOS"];
                $str = $centenas[$c] . " ";
            }
        }
        if ($resto > 0) $str .= $this->decenas($resto);
        return trim($str);
    }

    private function decenas($n) {
        if ($n < 30) {
            $unidades = ["", "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE", "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE", "VEINTE", "VEINTIUNO", "VEINTIDOS", "VEINTITRES", "VEINTICUATRO", "VEINTICINCO", "VEINTISEIS", "VEINTISIETE", "VEINTIOCHO", "VEINTINUEVE"];
            return $unidades[$n];
        }
        $d = floor($n / 10);
        $u = $n % 10;
        $decenas = ["", "DIEZ", "VEINTE", "TREINTA", "CUARENTA", "CINCUENTA", "SESENTA", "SETENTA", "OCHENTA", "NOVENTA"];
        $str = $decenas[$d];
        if ($u > 0) $str .= " Y " . ["", "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE"][$u];
        return $str;
    }
    public function generateMetricsPdf($data, $output = 'I') {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('FiberLink System');
        $pdf->SetAuthor('FiberLink');
        $pdf->SetTitle('Reporte de Métricas');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();

        // Title
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->SetTextColor(79, 70, 229); // Indigo-600
        $pdf->Cell(0, 10, 'Reporte de Métricas y Desempeño', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 116, 139); // Slate-500
        $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(10);

        // 1. Average Payment Time
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(30, 41, 59); // Slate-800
        $pdf->Cell(0, 10, 'Tiempo Promedio de Pago', 0, 1);
        
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetFillColor(241, 245, 249); // Slate-100
        $pdf->Cell(0, 15, $data['avg_payment_time'] . ' segundos', 0, 1, 'L', true);
        $pdf->Ln(5);

        // 2. Service Status
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Estado de Servicios', 0, 1);
        
        $html_status = '<table border="1" cellpadding="5">
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <th>Estado</th>
                <th>Cantidad</th>
            </tr>';
        foreach ($data['status'] as $s) {
            $html_status .= '<tr>
                <td>' . ucfirst($s['service_status']) . '</td>
                <td>' . $s['count'] . '</td>
            </tr>';
        }
        $html_status .= '</table>';
        $pdf->writeHTML($html_status, true, false, true, false, '');
        $pdf->Ln(5);

        // 3. Income (Last 6 Months)
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Ingresos Mensuales (Últimos 6 meses)', 0, 1);
        
        $html_income = '<table border="1" cellpadding="5">
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <th>Mes</th>
                <th>Total (S/)</th>
            </tr>';
        foreach ($data['income'] as $inc) {
            $html_income .= '<tr>
                <td>' . $inc['month'] . '</td>
                <td>S/ ' . number_format($inc['total'], 2) . '</td>
            </tr>';
        }
        $html_income .= '</table>';
        $pdf->writeHTML($html_income, true, false, true, false, '');
        $pdf->Ln(5);

        // 4. Top Debtors
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Top Deudores', 0, 1);
        
        $html_debtors = '<table border="1" cellpadding="5">
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <th>Cliente</th>
                <th>Deuda Total (S/)</th>
            </tr>';
        foreach ($data['debtors'] as $d) {
            $html_debtors .= '<tr>
                <td>' . $d['fullname'] . '</td>
                <td style="color: #ef4444;">S/ ' . number_format($d['debt'], 2) . '</td>
            </tr>';
        }
        $html_debtors .= '</table>';
        $pdf->writeHTML($html_debtors, true, false, true, false, '');

        return $pdf->Output('Reporte_Metricas.pdf', $output);
    }

    public function generatePaymentLogPdf($data, $output = 'I') {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('FiberLink System');
        $pdf->SetAuthor('FiberLink');
        $pdf->SetTitle('Log de Tiempos de Pago');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();
        
        // Set Text Color to Black
        $pdf->SetTextColor(0, 0, 0);

        // Header "WinBox" style
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('courier', 'B', 10);
        $pdf->Cell(0, 8, 'Log - Payment Metrics', 1, 1, 'L', true);
        $pdf->Ln(2);

        // Table Header
        $pdf->SetFont('courier', 'B', 9);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(45, 6, 'Time', 1, 0, 'L', true);
        $pdf->Cell(35, 6, 'Topics', 1, 0, 'L', true);
        $pdf->Cell(0, 6, 'Message', 1, 1, 'L', true);

        // Rows
        $pdf->SetFont('courier', '', 9);
        $fill = false;

        $total_seconds = 0;
        $count = 0;

        foreach ($data as $row) {
            $total_seconds += $row['duration_seconds'];
            $count++;

            // Format time like MikroTik: MMM/dd/Y HH:mm:ss
            $time = date('M/d/Y H:i:s', strtotime($row['payment_timestamp']));
            
            // Message: user "Name" paid INV-001 in Xs
            $message = sprintf('user "%s" paid %s in %ss', 
                $row['fullname'], 
                $row['invoice_number'], 
                $row['duration_seconds']
            );

            // Alternating colors (very subtle)
            $bg_color = $fill ? 245 : 255;
            $pdf->SetFillColor($bg_color, $bg_color, $bg_color);

            $pdf->Cell(45, 6, $time, 'LR', 0, 'L', true);
            $pdf->Cell(35, 6, 'payment,info', 'LR', 0, 'L', true);
            $pdf->Cell(0, 6, $message, 'LR', 1, 'L', true);
            
            $fill = !$fill;
        }
        
        // Bottom line
        $pdf->Cell(0, 0, '', 'T', 1, 'L', true);

        // Average Summary
        if ($count > 0) {
            $avg = number_format($total_seconds / $count, 2);
            $pdf->Ln(5);
            $pdf->SetFont('courier', 'B', 10);
            $pdf->SetFillColor(220, 220, 220);
            $pdf->Cell(0, 8, ">>> AVERAGE PAYMENT TIME: {$avg}s <<<", 1, 1, 'C', true);
        }

        return $pdf->Output('Payment_Logs.pdf', $output);
    }

    public function generateInvoicesReportPdf($invoices, $output = 'I') {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('FiberLink System');
        $pdf->SetAuthor('FiberLink');
        $pdf->SetTitle('Reporte de Facturación');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Totals calculations
        $total_count = count($invoices);
        $total_amount = 0;
        $total_paid = 0;
        $total_unpaid = 0;
        foreach ($invoices as $inv) {
            $amount = floatval($inv['total_amount']);
            $total_amount += $amount;
            if ($inv['status'] === 'paid') {
                $total_paid += $amount;
            } else {
                $total_unpaid += $amount;
            }
        }

        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(79, 70, 229); // Indigo-600
        $pdf->Cell(0, 10, 'Reporte de Facturación y Pagos - FiberLink', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(100, 116, 139); // Slate-500
        $pdf->Cell(0, 5, 'Generado el ' . date('d/m/Y h:i a'), 0, 1, 'C');
        $pdf->Ln(5);

        // Summary block HTML
        $html_summary = '
        <table border="0" cellpadding="6" style="width: 100%; font-family: helvetica;">
            <tr style="background-color: #f8fafc;">
                <td width="25%" style="border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 8pt; color: #64748b;">Total Facturas</span><br>
                    <span style="font-size: 13pt; font-weight: bold; color: #1e293b;">' . $total_count . '</span>
                </td>
                <td width="25%" style="border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 8pt; color: #64748b;">Total Facturado</span><br>
                    <span style="font-size: 13pt; font-weight: bold; color: #4f46e5;">S/ ' . number_format($total_amount, 2) . '</span>
                </td>
                <td width="25%" style="border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 8pt; color: #64748b;">Total Cobrado</span><br>
                    <span style="font-size: 13pt; font-weight: bold; color: #10b981;">S/ ' . number_format($total_paid, 2) . '</span>
                </td>
                <td width="25%" style="border: 1px solid #e2e8f0; text-align: center;">
                    <span style="font-size: 8pt; color: #64748b;">Total Pendiente/Vencido</span><br>
                    <span style="font-size: 13pt; font-weight: bold; color: #ef4444;">S/ ' . number_format($total_unpaid, 2) . '</span>
                </td>
            </tr>
        </table>
        <br><br>
        ';
        
        $pdf->writeHTML($html_summary, true, false, true, false, '');

        // Invoices Table
        $html_table = '
        <table border="0" cellpadding="5" style="width: 100%; font-family: helvetica; font-size: 8pt;">
            <thead>
                <tr style="background-color: #4f46e5; color: #ffffff; font-weight: bold;">
                    <th width="15%" style="text-align: left;">N° Factura</th>
                    <th width="28%" style="text-align: left;">Cliente</th>
                    <th width="14%" style="text-align: center;">DNI/RUC</th>
                    <th width="11%" style="text-align: center;">Emisión</th>
                    <th width="11%" style="text-align: center;">Vencimiento</th>
                    <th width="11%" style="text-align: right;">Monto</th>
                    <th width="10%" style="text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>';

        $fill = false;
        foreach ($invoices as $inv) {
            $bg_color = $fill ? '#f8fafc' : '#ffffff';
            
            $status_style = '';
            $status_text = '';
            switch ($inv['status']) {
                case 'paid':
                    $status_style = 'color: #10b981; font-weight: bold;';
                    $status_text = 'PAGADO';
                    break;
                case 'unpaid':
                    $status_style = 'color: #f59e0b; font-weight: bold;';
                    $status_text = 'PENDIENTE';
                    break;
                case 'overdue':
                    $status_style = 'color: #ef4444; font-weight: bold;';
                    $status_text = 'VENCIDO';
                    break;
                case 'cancelled':
                    $status_style = 'color: #94a3b8;';
                    $status_text = 'CANCELADO';
                    break;
                default:
                    $status_text = strtoupper($inv['status']);
            }

            $html_table .= '
            <tr style="background-color: ' . $bg_color . ';">
                <td style="border-bottom: 1px solid #e2e8f0;">' . $inv['invoice_number'] . '</td>
                <td style="border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($inv['fullname']) . '</td>
                <td style="border-bottom: 1px solid #e2e8f0; text-align: center;">' . $inv['dni_ruc'] . '</td>
                <td style="border-bottom: 1px solid #e2e8f0; text-align: center;">' . date('d/m/Y', strtotime($inv['issue_date'])) . '</td>
                <td style="border-bottom: 1px solid #e2e8f0; text-align: center;">' . date('d/m/Y', strtotime($inv['due_date'])) . '</td>
                <td style="border-bottom: 1px solid #e2e8f0; text-align: right;">S/ ' . number_format($inv['total_amount'], 2) . '</td>
                <td style="border-bottom: 1px solid #e2e8f0; text-align: center; ' . $status_style . '">' . $status_text . '</td>
            </tr>';
            $fill = !$fill;
        }

        $html_table .= '
            </tbody>
        </table>
        ';

        $pdf->writeHTML($html_table, true, false, true, false, '');

        // Output
        return $pdf->Output('Reporte_Facturas_' . date('Ymd_His') . '.pdf', $output);
    }
}
?>
