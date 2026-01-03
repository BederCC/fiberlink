<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

class Mailer {
    private $mail;
    private $lastError;

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
            $this->lastError = "Mailer Error: {$this->mail->ErrorInfo}";
            error_log($this->lastError);
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function sendPaymentReceipt($toEmail, $toName, $paymentData, $pdfContent = null, $filename = 'Recibo.pdf') {
        try {
            $this->mail->addAddress($toEmail, $toName);

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Comprobante de Pago - FiberLink';
            
            $body = "
            <div style='font-family: Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
                <!-- Header -->
                <div style='text-align: center; padding: 30px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <h1 style='color: #4f46e5; margin: 0; font-size: 24px;'>FiberLink</h1>
                    <p style='color: #64748b; margin: 5px 0; font-size: 14px;'>Comprobante de Pago Electrónico</p>
                </div>

                <div style='padding: 30px 20px;'>
                    <p style='color: #334155; font-size: 14px;'>Hola <strong>{$toName}</strong>,</p>
                    <p style='color: #334155; font-size: 14px;'>Gracias por tu pago. Adjuntamos tu recibo en formato PDF.</p>
                    
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
                             <tr>
                                <td style='padding: 10px 0; color: #4f46e5; font-size: 14px; font-weight: bold; border-top: 1px solid #e2e8f0;'>TOTAL PAGADO</td>
                                <td style='padding: 10px 0; color: #4f46e5; font-size: 14px; font-weight: bold; text-align: right; border-top: 1px solid #e2e8f0;'>S/ {$paymentData['amount']}</td>
                            </tr>
                        </table>
                    </div>

                    <p style='color: #334155; font-size: 14px;'>Si tienes alguna duda, contáctanos a soporte@fiberlink.com.</p>
                </div>

                <!-- Footer -->
                <div style='text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 11px;'>
                    <p style='margin: 0;'>FiberLink Telecomunicaciones S.A.C.</p>
                    <p style='margin: 5px 0;'>Este correo es automático, por favor no responder.</p>
                </div>
            </div>
            ";

            $this->mail->Body    = $body;
            $this->mail->AltBody = "Hola {$toName}, hemos recibido tu pago de S/ {$paymentData['amount']} para la factura {$paymentData['invoice_number']}. Adjuntamos el recibo.";

            // Attach PDF if provided
            if ($pdfContent) {
                $this->mail->addStringAttachment($pdfContent, $filename, 'base64', 'application/pdf');
            }

            $this->mail->send();
            // Clear addresses for next send
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
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
    public function sendActivationEmail($toEmail, $toName, $link) {
        try {
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Activa tu Cuenta - FiberLink';
            
            $body = "
            <div style='font-family: Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #ffffff;'>
                <div style='text-align: center; padding: 30px 20px; background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <h1 style='color: #4f46e5; margin: 0; font-size: 24px;'>FiberLink</h1>
                    <p style='color: #64748b; margin: 5px 0; font-size: 14px;'>Activación de Cuenta</p>
                </div>
                <div style='padding: 30px 20px; text-align: center;'>
                    <p style='color: #334155; font-size: 16px;'>Hola <strong>{$toName}</strong>,</p>
                    <p style='color: #334155; font-size: 14px; margin-bottom: 30px;'>Para activar tu cuenta y acceder al portal de clientes, por favor haz clic en el siguiente botón:</p>
                    
                    <a href='{$link}' style='display: inline-block; padding: 12px 24px; background-color: #10b981; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 16px;'>Activar Cuenta</a>
                    
                    <p style='color: #64748b; font-size: 12px; margin-top: 30px;'>Si no solicitaste esto, puedes ignorar este correo.</p>
                </div>
                <div style='text-align: center; padding: 20px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 11px;'>
                    <p style='margin: 0;'>FiberLink Telecomunicaciones S.A.C.</p>
                </div>
            </div>";

            $this->mail->Body = $body;
            $this->mail->AltBody = "Hola {$toName}, activa tu cuenta en el siguiente enlace: {$link}";

            $this->mail->send();
            $this->mail->clearAddresses();
            return true;
        } catch (Exception $e) {
            $this->lastError = "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            error_log($this->lastError);
            return false;
        }
    }
}
?>
