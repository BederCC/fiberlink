<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

class Mailer {
    private $mail;

    public function __construct() {
        $config = require '../config/mail.php';
        
        $this->mail = new PHPMailer(true);

        try {
            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = $config['smtp_host'];
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $config['smtp_user'];
            $this->mail->Password   = $config['smtp_pass'];
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // or ENCRYPTION_STARTTLS
            $this->mail->Port       = $config['smtp_port'];

            // Recipients
            $this->mail->setFrom($config['from_email'], $config['from_name']);
        } catch (Exception $e) {
            // Log error
            error_log("Mailer Error: {$this->mail->ErrorInfo}");
        }
    }

    public function sendPaymentReceipt($toEmail, $toName, $paymentData) {
        try {
            $this->mail->addAddress($toEmail, $toName);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Comprobante de Pago - FiberLink';
            
            // Calculations
            $total = floatval(str_replace(',', '', $paymentData['amount']));
            $base = $total / 1.18;
            $igv = $total - $base;
            
            // Items Rows
            $itemsHtml = '';
            if (!empty($paymentData['items'])) {
                foreach ($paymentData['items'] as $item) {
                    $itemTotal = floatval($item['amount']);
                    $itemBase = $itemTotal / 1.18;
                    $itemsHtml .= "
                    <tr>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 12px;'>{$item['description']}</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 12px; text-align: right;'>S/ " . number_format($itemBase, 2) . "</td>
                        <td style='padding: 8px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 12px; text-align: right;'>S/ " . number_format($itemBase, 2) . "</td>
                    </tr>";
                }
            }

            $body = "
            <div style='font-family: Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
                <!-- Header -->
                <div style='text-align: center; padding: 30px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <h1 style='color: #4f46e5; margin: 0; font-size: 24px;'>FiberLink</h1>
                    <p style='color: #64748b; margin: 5px 0; font-size: 14px;'>Comprobante de Pago Electrónico</p>
                </div>

                <div style='padding: 30px 20px;'>
                    <p style='color: #334155; font-size: 14px;'>Hola <strong>{$toName}</strong>,</p>
                    <p style='color: #334155; font-size: 14px;'>Gracias por tu pago. Adjuntamos los detalles de tu transacción.</p>
                    
                    <!-- Invoice Info -->
                    <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin: 20px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 5px 0; color: #64748b; font-size: 12px;'>N° FACTURA</td>
                                <td style='padding: 5px 0; color: #0f172a; font-weight: bold; font-size: 12px; text-align: right;'>{$paymentData['invoice_number']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #64748b; font-size: 12px;'>FECHA PAGO</td>
                                <td style='padding: 5px 0; color: #0f172a; font-weight: bold; font-size: 12px; text-align: right;'>{$paymentData['date']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #64748b; font-size: 12px;'>MÉTODO</td>
                                <td style='padding: 5px 0; color: #0f172a; font-weight: bold; font-size: 12px; text-align: right;'>{$paymentData['method']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #64748b; font-size: 12px;'>TRANSACCIÓN</td>
                                <td style='padding: 5px 0; color: #0f172a; font-weight: bold; font-size: 12px; text-align: right;'>{$paymentData['transaction_id']}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Items Table -->
                    <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                        <thead>
                            <tr>
                                <th style='padding: 8px; background-color: #f1f5f9; color: #475569; font-size: 10px; text-align: left; font-weight: bold; border-radius: 4px 0 0 4px;'>DESCRIPCIÓN</th>
                                <th style='padding: 8px; background-color: #f1f5f9; color: #475569; font-size: 10px; text-align: right; font-weight: bold;'>P. UNIT</th>
                                <th style='padding: 8px; background-color: #f1f5f9; color: #475569; font-size: 10px; text-align: right; font-weight: bold; border-radius: 0 4px 4px 0;'>TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 5px 0; color: #64748b; font-size: 12px; text-align: right;'>Op. Gravada:</td>
                            <td style='padding: 5px 0; color: #334155; font-size: 12px; text-align: right; width: 100px;'>S/ " . number_format($base, 2) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 5px 0; color: #64748b; font-size: 12px; text-align: right;'>I.G.V. (18%):</td>
                            <td style='padding: 5px 0; color: #334155; font-size: 12px; text-align: right;'>S/ " . number_format($igv, 2) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #4f46e5; font-size: 14px; font-weight: bold; text-align: right; border-top: 1px solid #e2e8f0;'>TOTAL PAGADO:</td>
                            <td style='padding: 10px 0; color: #4f46e5; font-size: 14px; font-weight: bold; text-align: right; border-top: 1px solid #e2e8f0;'>S/ " . number_format($total, 2) . "</td>
                        </tr>
                    </table>


                </div>

                <!-- Footer -->
                <div style='text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 11px;'>
                    <p style='margin: 0;'>FiberLink Telecomunicaciones S.A.C.</p>
                    <p style='margin: 5px 0;'>Este correo es automático, por favor no responder.</p>
                    <p style='margin: 0;'>&copy; " . date('Y') . " FiberLink. Todos los derechos reservados.</p>
                </div>
            </div>
            ";

            $this->mail->Body    = $body;
            $this->mail->AltBody = "Hola {$toName}, hemos recibido tu pago de S/ {$paymentData['amount']} para la factura {$paymentData['invoice_number']}. Gracias.";

            $this->mail->send();
            // Clear addresses for next send
            $this->mail->clearAddresses();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }

    public function sendReminder($toEmail, $toName, $invoiceData) {
        try {
            $this->mail->addAddress($toEmail, $toName);

            $days = $invoiceData['days_remaining'];
            $statusColor = '#f59e0b'; // Amber (Warning)
            $statusTitle = 'Recordatorio de Pago';
            
            if ($days > 1) {
                $subject = "Recordatorio: Tu factura vence en {$days} días";
                $message = "Te recordamos que tienes una factura próxima a vencer en <strong>{$days} días</strong>.";
            } elseif ($days == 1) {
                $subject = "Recordatorio: Tu factura vence mañana";
                $message = "Te recordamos que tu factura vence el día de <strong>mañana</strong>.";
            } elseif ($days == 0) {
                $subject = "¡Tu factura vence hoy!";
                $message = "Te recordamos que tu factura vence el día de <strong>hoy</strong>.";
                $statusColor = '#ea580c'; // Orange
            } else {
                $overdueDays = abs($days);
                $subject = "Aviso de Suspensión - Factura Vencida";
                $statusTitle = 'Factura Vencida';
                $message = "Te informamos que tu factura se encuentra vencida por <strong>{$overdueDays} días</strong>. Por favor regulariza tu pago lo antes posible para evitar el corte del servicio.";
                $statusColor = '#dc2626'; // Red
            }

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject . ' - FiberLink';
            
            $body = "
            <div style='font-family: Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
                <!-- Header -->
                <div style='text-align: center; padding: 30px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <h1 style='color: #4f46e5; margin: 0; font-size: 24px;'>FiberLink</h1>
                    <p style='color: {$statusColor}; margin: 5px 0; font-size: 14px; font-weight: bold;'>{$statusTitle}</p>
                </div>

                <div style='padding: 30px 20px;'>
                    <p style='color: #334155; font-size: 14px;'>Hola <strong>{$toName}</strong>,</p>
                    <p style='color: #334155; font-size: 14px;'>{$message}</p>
                    
                    <!-- Invoice Info -->
                    <div style='background-color: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; margin: 20px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 5px 0; color: #92400e; font-size: 12px;'>N° FACTURA</td>
                                <td style='padding: 5px 0; color: #92400e; font-weight: bold; font-size: 12px; text-align: right;'>{$invoiceData['invoice_number']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #92400e; font-size: 12px;'>VENCIMIENTO</td>
                                <td style='padding: 5px 0; color: #92400e; font-weight: bold; font-size: 12px; text-align: right;'>{$invoiceData['due_date']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 5px 0; color: #92400e; font-size: 12px;'>MONTO TOTAL</td>
                                <td style='padding: 5px 0; color: #92400e; font-weight: bold; font-size: 14px; text-align: right;'>S/ {$invoiceData['amount']}</td>
                            </tr>
                        </table>
                    </div>

                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='http://localhost/fiberlink/payment_simulator.php' style='display: inline-block; padding: 12px 24px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px;'>Pagar Ahora</a>
                    </div>
                </div>

                <!-- Footer -->
                <div style='text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 12px;'>
                    <p style='margin: 0;'>&copy; " . date('Y') . " FiberLink. Todos los derechos reservados.</p>
                    <p style='margin: 5px 0;'>Este es un correo automático, por favor no responder.</p>
                </div>
            </div>
            ";

            $this->mail->Body    = $body;
            $this->mail->AltBody = strip_tags($message) . " Factura: {$invoiceData['invoice_number']}, Monto: S/ {$invoiceData['amount']}.";

            $this->mail->send();
            $this->mail->clearAddresses();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
    public function sendInstallationSheet($toEmail, $toName, $pdfContent, $filename) {
        try {
            $this->mail->addAddress($toEmail, $toName);

            $this->mail->isHTML(true);
            $this->mail->Subject = 'Hoja de Instalación - FiberLink';
            
            $body = "
            <div style='font-family: Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
                <div style='text-align: center; padding: 30px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <h1 style='color: #4f46e5; margin: 0; font-size: 24px;'>FiberLink</h1>
                    <p style='color: #64748b; margin: 5px 0; font-size: 14px;'>Constancia de Instalación</p>
                </div>
                <div style='padding: 30px 20px;'>
                    <p style='color: #334155; font-size: 14px;'>Hola <strong>{$toName}</strong>,</p>
                    <p style='color: #334155; font-size: 14px;'>Tu instalación ha sido completada exitosamente.</p>
                    <p style='color: #334155; font-size: 14px;'>Adjuntamos la hoja de instalación con los detalles de tu servicio.</p>
                </div>
                <div style='text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 11px;'>
                    <p style='margin: 0;'>FiberLink Telecomunicaciones S.A.C.</p>
                </div>
            </div>";

            $this->mail->Body = $body;
            $this->mail->AltBody = "Hola {$toName}, tu instalación ha sido completada. Adjuntamos la hoja de instalación.";

            // Attach PDF
            $this->mail->addStringAttachment($pdfContent, $filename, 'base64', 'application/pdf');

            $this->mail->send();
            $this->mail->clearAddresses();
            $this->mail->clearAttachments(); // Clear attachments for next send
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>
