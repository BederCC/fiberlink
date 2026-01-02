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
}
?>
