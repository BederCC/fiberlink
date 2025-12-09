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
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                <div style='text-align: center; background-color: #4f46e5; padding: 20px; border-radius: 10px 10px 0 0;'>
                    <h1 style='color: white; margin: 0;'>FiberLink</h1>
                    <p style='color: #e0e7ff; margin: 5px 0;'>Comprobante de Pago</p>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <strong>{$toName}</strong>,</p>
                    <p>Hemos recibido tu pago exitosamente. Aquí están los detalles:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>N° Factura:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$paymentData['invoice_number']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Monto Pagado:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee; color: #4f46e5; font-weight: bold;'>S/ {$paymentData['amount']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Fecha:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$paymentData['date']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>Método:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$paymentData['method']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'><strong>N° Operación:</strong></td>
                            <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$paymentData['transaction_id']}</td>
                        </tr>
                    </table>
                    
                    <p style='color: #666; font-size: 14px;'>Gracias por confiar en nuestros servicios.</p>
                </div>
                <div style='text-align: center; padding: 20px; background-color: #f9fafb; border-radius: 0 0 10px 10px; color: #9ca3af; font-size: 12px;'>
                    &copy; " . date('Y') . " FiberLink. Todos los derechos reservados.
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

            // Content
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Recordatorio de Pago - FiberLink';
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                <div style='text-align: center; background-color: #f59e0b; padding: 20px; border-radius: 10px 10px 0 0;'>
                    <h1 style='color: white; margin: 0;'>FiberLink</h1>
                    <p style='color: #fffbeb; margin: 5px 0;'>Recordatorio de Pago</p>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <strong>{$toName}</strong>,</p>
                    <p>Te recordamos que tienes una factura próxima a vencer:</p>
                    
                    <div style='background-color: #fffbeb; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #fcd34d;'>
                        <p style='margin: 5px 0;'><strong>Factura:</strong> {$invoiceData['invoice_number']}</p>
                        <p style='margin: 5px 0;'><strong>Vencimiento:</strong> {$invoiceData['due_date']}</p>
                        <p style='margin: 5px 0; font-size: 18px; color: #d97706;'><strong>Monto: S/ {$invoiceData['amount']}</strong></p>
                    </div>
                    
                    <p>Por favor, realiza el pago antes de la fecha de vencimiento para evitar cortes en el servicio.</p>
                    <p><a href='http://localhost/fiberlink/payment_simulator.php' style='display: inline-block; padding: 10px 20px; background-color: #4f46e5; color: white; text-decoration: none; border-radius: 5px;'>Pagar Ahora</a></p>
                </div>
                <div style='text-align: center; padding: 20px; background-color: #f9fafb; border-radius: 0 0 10px 10px; color: #9ca3af; font-size: 12px;'>
                    &copy; " . date('Y') . " FiberLink. Todos los derechos reservados.
                </div>
            </div>
            ";

            $this->mail->Body    = $body;
            $this->mail->AltBody = "Hola {$toName}, te recordamos que tu factura {$invoiceData['invoice_number']} vence el {$invoiceData['due_date']} por un monto de S/ {$invoiceData['amount']}.";

            $this->mail->send();
            $this->mail->clearAddresses();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>
