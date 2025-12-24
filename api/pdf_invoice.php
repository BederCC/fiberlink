<?php
require_once('../vendor/autoload.php');
include_once '../config/database.php';

if (!isset($_GET['id'])) {
    die('ID de factura no proporcionado');
}

$invoice_id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// 1. Get Invoice & Client Details
$query = "SELECT i.*, c.fullname, c.dni_ruc, c.email, c.address, c.phone 
          FROM invoices i 
          JOIN clients c ON i.client_id = c.id 
          WHERE i.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(":id", $invoice_id);
$stmt->execute();
$invoice = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$invoice) {
    die('Factura no encontrada');
}

// 2. Get Invoice Items
$q_items = "SELECT * FROM invoice_items WHERE invoice_id = :id";
$s_items = $db->prepare($q_items);
$s_items->bindParam(":id", $invoice_id);
$s_items->execute();
$items = $s_items->fetchAll(PDO::FETCH_ASSOC);

// 3. Create PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Meta info
$pdf->SetCreator('FiberLink System');
$pdf->SetAuthor('FiberLink');
$pdf->SetTitle('Factura ' . $invoice['invoice_number']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 15);

$pdf->AddPage();

// --- CALCULATIONS ---
$total = floatval($invoice['total_amount']);
$base = $total / 1.18;
$igv = $total - $base;

// --- STYLES ---
$style_header = 'color: #1e293b; font-family: helvetica; font-weight: bold;';
$style_text = 'color: #475569; font-family: helvetica; font-size: 10px;';
$style_th = 'background-color: #f1f5f9; color: #334155; font-weight: bold; font-family: helvetica; font-size: 10px; border-bottom: 1px solid #e2e8f0;';
$style_td = 'color: #334155; font-family: helvetica; font-size: 10px; border-bottom: 1px solid #f1f5f9;';
$style_total = 'color: #0f172a; font-family: helvetica; font-weight: bold; font-size: 11px;';

// --- CONTENT ---

// 1. Header Section
$html = '
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="60%">
            <h1 style="color: #4f46e5; font-size: 26px; font-family: helvetica; margin-bottom: 5px;">FiberLink</h1>
            <p style="' . $style_text . ' line-height: 1.4;">
                <strong>FiberLink Telecomunicaciones S.A.C.</strong><br>
                Av. Principal 123, Oficina 405<br>
                Lima, Perú<br>
                RUC: 20123456789<br>
                Telf: (01) 123-4567 | Email: facturacion@fiberlink.com
            </p>
        </td>
        <td width="40%" style="text-align: right;">
            <div style="border: 1px solid #e2e8f0; background-color: #f8fafc; padding: 15px; border-radius: 5px;">
                <h2 style="color: #334155; font-size: 16px; font-family: helvetica; margin: 0;">R.U.C. 20123456789</h2>
                <h2 style="color: #4f46e5; font-size: 18px; font-family: helvetica; margin: 5px 0;">FACTURA ELECTRÓNICA</h2>
                <h3 style="color: #64748b; font-size: 14px; font-family: helvetica; margin: 0;">N° ' . $invoice['invoice_number'] . '</h3>
            </div>
        </td>
    </tr>
</table>
<br><br>
';

// 2. Client & Invoice Info
$html .= '
<table border="0" cellpadding="5" cellspacing="0">
    <tr>
        <td width="60%" style="border: 1px solid #e2e8f0; border-radius: 5px;">
            <table border="0" cellpadding="2">
                <tr><td colspan="2" style="' . $style_header . ' font-size: 11px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">DATOS DEL CLIENTE</td></tr>
                <tr>
                    <td width="25%" style="' . $style_text . ' font-weight: bold;">Razón Social:</td>
                    <td width="75%" style="' . $style_text . '">' . $invoice['fullname'] . '</td>
                </tr>
                <tr>
                    <td style="' . $style_text . ' font-weight: bold;">DNI / RUC:</td>
                    <td style="' . $style_text . '">' . $invoice['dni_ruc'] . '</td>
                </tr>
                <tr>
                    <td style="' . $style_text . ' font-weight: bold;">Dirección:</td>
                    <td style="' . $style_text . '">' . $invoice['address'] . '</td>
                </tr>
            </table>
        </td>
        <td width="5%"></td>
        <td width="35%" style="border: 1px solid #e2e8f0; border-radius: 5px;">
             <table border="0" cellpadding="2">
                <tr><td colspan="2" style="' . $style_header . ' font-size: 11px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;">DETALLES DE EMISIÓN</td></tr>
                <tr>
                    <td width="50%" style="' . $style_text . ' font-weight: bold;">Fecha Emisión:</td>
                    <td width="50%" style="' . $style_text . '">' . date('d/m/Y', strtotime($invoice['issue_date'])) . '</td>
                </tr>
                <tr>
                    <td style="' . $style_text . ' font-weight: bold;">Vencimiento:</td>
                    <td style="' . $style_text . '">' . date('d/m/Y', strtotime($invoice['due_date'])) . '</td>
                </tr>
                <tr>
                    <td style="' . $style_text . ' font-weight: bold;">Moneda:</td>
                    <td style="' . $style_text . '">Soles (PEN)</td>
                </tr>
                <tr>
                    <td style="' . $style_text . ' font-weight: bold;">Estado:</td>
                    <td style="' . $style_text . ' color: ' . ($invoice['status'] == 'paid' ? '#10b981' : '#ef4444') . ';">' . strtoupper($invoice['status']) . '</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br><br>
';

// 3. Items Table
$html .= '
<table border="0" cellpadding="8" cellspacing="0">
    <thead>
        <tr>
            <th width="10%" style="' . $style_th . ' text-align: center;">CANT.</th>
            <th width="60%" style="' . $style_th . '">DESCRIPCIÓN</th>
            <th width="15%" style="' . $style_th . ' text-align: right;">P. UNIT</th>
            <th width="15%" style="' . $style_th . ' text-align: right;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
';

foreach ($items as $item) {
    // Assuming quantity 1 for service items if not specified, or calculate unit price
    // Since we only store total amount in invoice_items, we assume qty 1 for simplicity or derive it.
    // For this display, let's treat amount as total and calculate unit price backwards or just show total.
    // To match the "Base + IGV" logic, the item amount in DB is the TOTAL (inc IGV).
    
    $item_total = floatval($item['amount']);
    $item_base = $item_total / 1.18;
    
    $html .= '
    <tr>
        <td style="' . $style_td . ' text-align: center;">1</td>
        <td style="' . $style_td . '">' . $item['description'] . '</td>
        <td style="' . $style_td . ' text-align: right;">' . number_format($item_base, 2) . '</td>
        <td style="' . $style_td . ' text-align: right;">' . number_format($item_base, 2) . '</td>
    </tr>
    ';
}

// Fill empty rows to push footer down if needed (optional, skipping for now)

$html .= '
    </tbody>
</table>
<br>
';

// 4. Totals Section
// Helper function for number to words (since intl might be missing)
function numtoletras($xcifra)
{
    $xarray = array(0 => "Cero",
        1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
        "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
        "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
        100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
    );

    $xcifra = trim($xcifra);
    $xlength = strlen($xcifra);
    $xpos_punto = strpos($xcifra, ".");
    $xaux_int = $xcifra;
    $xdecimales = "00";
    if (!($xpos_punto === false)) {
        if ($xpos_punto == 0) {
            $xcifra = "0" . $xcifra;
            $xpos_punto = strpos($xcifra, ".");
        }
        $xaux_int = substr($xcifra, 0, $xpos_punto); // integer part
        $xdecimales = substr($xcifra, $xpos_punto + 1);
        if (strlen($xdecimales) < 2) {
            $xdecimales = $xdecimales . "0";
        }
    }

    $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // Adjust length
    $xcadena = "";
    for ($xz = 0; $xz < 3; $xz++) {
        $xaux = substr($XAUX, $xz * 6, 6);
        $xi = trim($xaux);
        if ($xi != "") {
            $xi = (int)$xi;
            if ($xz == 0) $xcalificador = "TRILLON";
            elseif ($xz == 1) $xcalificador = "BILLON";
            elseif ($xz == 2) $xcalificador = "MILLON";
            
            if ($xi > 1) $xcalificador .= "ES";
            else $xcalificador = substr($xcalificador, 0, -2); // Singular

            // This part is complex for a quick fix. Let's use a simpler recursive approach or a standard library copy-paste.
            // Actually, for this specific user request, a simpler function for < 1 million is safer and cleaner.
        }
    }
    
    // SIMPLIFIED VERSION FOR BILLING (0 - 999,999)
    $valor = (int) $xaux_int;
    if ($valor == 0) return "CERO";
    
    $str = "";
    
    // Thousands
    $miles = floor($valor / 1000);
    $resto = $valor % 1000;
    
    if ($miles > 0) {
        if ($miles == 1) $str .= "MIL ";
        else $str .= centenas($miles) . " MIL ";
    }
    
    if ($resto > 0 || $valor == 0) {
        $str .= centenas($resto);
    }
    
    return trim($str);
}

function centenas($n) {
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
    
    if ($resto > 0) {
        $str .= decenas($resto);
    }
    
    return trim($str);
}

function decenas($n) {
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

// ... inside the HTML generation ...
$html .= '
<table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td width="60%">
            <p style="' . $style_text . '">
                <strong>SON:</strong> ' . numtoletras($total) . ' Y ' . explode('.', number_format($total, 2))[1] . '/100 SOLES
            </p>
            <br>
            <p style="' . $style_text . ' font-size: 9px; color: #94a3b8;">
                Observaciones:<br>
                Servicio sujeto a cortes por falta de pago.
            </p>
        </td>
        <td width="40%">
            <table border="0" cellpadding="5" cellspacing="0">
                <tr>
                    <td style="' . $style_text . ' text-align: right; font-weight: bold;">OP. GRAVADA:</td>
                    <td style="' . $style_text . ' text-align: right;">S/ ' . number_format($base, 2) . '</td>
                </tr>
                <tr>
                    <td style="' . $style_text . ' text-align: right; font-weight: bold;">I.G.V. (18%):</td>
                    <td style="' . $style_text . ' text-align: right;">S/ ' . number_format($igv, 2) . '</td>
                </tr>
                <tr>
                    <td style="border-top: 2px solid #4f46e5; padding-top: 10px; text-align: right; font-weight: bold; font-size: 12px; color: #4f46e5;">IMPORTE TOTAL:</td>
                    <td style="border-top: 2px solid #4f46e5; padding-top: 10px; text-align: right; font-weight: bold; font-size: 12px; color: #4f46e5;">S/ ' . number_format($total, 2) . '</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
';

// 5. Footer
$html .= '
<div style="position: absolute; bottom: 20px; width: 100%; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 10px;">
    <p style="font-size: 8px; color: #94a3b8; font-family: helvetica;">
        Representación impresa de la Factura Electrónica.<br>
        Autorizado mediante Resolución de Intendencia N° 034-005-0005315<br>
        Consulte su documento en <strong>www.fiberlink.com/facturacion</strong>
    </p>
</div>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Output
$pdf->Output('Factura_' . $invoice['invoice_number'] . '.pdf', 'I');
?>
