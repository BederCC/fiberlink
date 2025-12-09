<?php
include_once '../config/database.php';

if (!isset($_GET['id'])) {
    die('ID de factura no proporcionado');
}

$invoice_id = $_GET['id'];

$database = new Database();
$db = $database->getConnection();

// 1. Get Invoice & Client Details
$query = "SELECT i.*, c.first_name, c.last_name, c.dni_ruc, c.address, c.email 
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

// 2. Get Items
$q_items = "SELECT * FROM invoice_items WHERE invoice_id = :id";
$s_items = $db->prepare($q_items);
$s_items->bindParam(":id", $invoice_id);
$s_items->execute();
$items = $s_items->fetchAll(PDO::FETCH_ASSOC);

// 3. Generate XML (UBL 2.1 Standard Structure)
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"/>');

// Header
$xml->addChild('cbc:UBLVersionID', '2.1');
$xml->addChild('cbc:CustomizationID', '2.0');
$xml->addChild('cbc:ID', $invoice['invoice_number']);
$xml->addChild('cbc:IssueDate', $invoice['issue_date']);
$xml->addChild('cbc:InvoiceTypeCode', '01'); // 01 = Factura, 03 = Boleta
$xml->addChild('cbc:DocumentCurrencyCode', 'PEN');

// Signature (Placeholder)
$sig = $xml->addChild('cac:Signature');
$sig->addChild('cbc:ID', 'SIG-' . $invoice['invoice_number']);
$sig->addChild('cac:SignatoryParty')->addChild('cac:PartyIdentification')->addChild('cbc:ID', '20123456789'); // Company RUC

// Supplier (Company)
$supplier = $xml->addChild('cac:AccountingSupplierParty');
$party = $supplier->addChild('cac:Party');
$party->addChild('cac:PartyIdentification')->addChild('cbc:ID', '20123456789')->addAttribute('schemeID', '6'); // 6 = RUC
$party->addChild('cac:PartyName')->addChild('cbc:Name', 'FiberLink S.A.C.');
$party->addChild('cac:PartyLegalEntity')->addChild('cbc:RegistrationName', 'FiberLink S.A.C.');

// Customer
$customer = $xml->addChild('cac:AccountingCustomerParty');
$party = $customer->addChild('cac:Party');
$party->addChild('cac:PartyIdentification')->addChild('cbc:ID', $invoice['dni_ruc'])->addAttribute('schemeID', strlen($invoice['dni_ruc']) == 11 ? '6' : '1'); // 6=RUC, 1=DNI
$party->addChild('cac:PartyLegalEntity')->addChild('cbc:RegistrationName', $invoice['first_name'] . ' ' . $invoice['last_name']);

// Tax Total (IGV 18%)
$total_amount = $invoice['total_amount'];
$base_amount = $total_amount / 1.18;
$igv_amount = $total_amount - $base_amount;

$taxTotal = $xml->addChild('cac:TaxTotal');
$taxTotal->addChild('cbc:TaxAmount', number_format($igv_amount, 2, '.', ''))->addAttribute('currencyID', 'PEN');
$taxSubtotal = $taxTotal->addChild('cac:TaxSubtotal');
$taxSubtotal->addChild('cbc:TaxableAmount', number_format($base_amount, 2, '.', ''))->addAttribute('currencyID', 'PEN');
$taxSubtotal->addChild('cbc:TaxAmount', number_format($igv_amount, 2, '.', ''))->addAttribute('currencyID', 'PEN');
$taxScheme = $taxSubtotal->addChild('cac:TaxCategory')->addChild('cac:TaxScheme');
$taxScheme->addChild('cbc:ID', '1000');
$taxScheme->addChild('cbc:Name', 'IGV');
$taxScheme->addChild('cbc:TaxTypeCode', 'VAT');

// Legal Monetary Total
$legal = $xml->addChild('cac:LegalMonetaryTotal');
$legal->addChild('cbc:LineExtensionAmount', number_format($base_amount, 2, '.', ''))->addAttribute('currencyID', 'PEN');
$legal->addChild('cbc:TaxInclusiveAmount', number_format($total_amount, 2, '.', ''))->addAttribute('currencyID', 'PEN');
$legal->addChild('cbc:PayableAmount', number_format($total_amount, 2, '.', ''))->addAttribute('currencyID', 'PEN');

// Invoice Lines
foreach ($items as $index => $item) {
    $line = $xml->addChild('cac:InvoiceLine');
    $line->addChild('cbc:ID', $index + 1);
    $line->addChild('cbc:InvoicedQuantity', '1')->addAttribute('unitCode', 'NIU');
    
    $item_total = $item['amount'];
    $item_base = $item_total / 1.18;
    
    $line->addChild('cbc:LineExtensionAmount', number_format($item_base, 2, '.', ''))->addAttribute('currencyID', 'PEN');
    
    $pricing = $line->addChild('cac:PricingReference')->addChild('cac:AlternativeConditionPrice');
    $pricing->addChild('cbc:PriceAmount', number_format($item_total, 2, '.', ''))->addAttribute('currencyID', 'PEN');
    $pricing->addChild('cbc:PriceTypeCode', '01'); // 01 = Precio Unitario con IGV

    $taxTotal = $line->addChild('cac:TaxTotal');
    $taxTotal->addChild('cbc:TaxAmount', number_format($item_total - $item_base, 2, '.', ''))->addAttribute('currencyID', 'PEN');
    $taxSub = $taxTotal->addChild('cac:TaxSubtotal');
    $taxSub->addChild('cbc:TaxableAmount', number_format($item_base, 2, '.', ''))->addAttribute('currencyID', 'PEN');
    $taxSub->addChild('cbc:TaxAmount', number_format($item_total - $item_base, 2, '.', ''))->addAttribute('currencyID', 'PEN');
    $taxScheme = $taxSub->addChild('cac:TaxCategory')->addChild('cac:TaxScheme');
    $taxScheme->addChild('cbc:ID', '1000');
    $taxScheme->addChild('cbc:Name', 'IGV');
    $taxScheme->addChild('cbc:TaxTypeCode', 'VAT');

    $line->addChild('cac:Item')->addChild('cbc:Description', $item['description']);
    $line->addChild('cac:Price')->addChild('cbc:PriceAmount', number_format($item_base, 2, '.', ''))->addAttribute('currencyID', 'PEN');
}

// Output
header('Content-type: text/xml');
header('Content-Disposition: attachment; filename="Factura_' . $invoice['invoice_number'] . '.xml"');
echo $xml->asXML();
?>
