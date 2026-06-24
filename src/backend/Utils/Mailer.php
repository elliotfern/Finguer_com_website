<?php
namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true); // true = excepciones activadas

        $this->mail->isSMTP();
        $this->mail->Host = $_ENV['SMTP_HOST'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['SMTP_USER'];
        $this->mail->Password = $_ENV['SMTP_PASS'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // o SMTPS si usas puerto 465
        $this->mail->Port = $_ENV['SMTP_PORT']; // 587 (TLS) o 465 (SSL)
        $this->mail->CharSet = 'UTF-8';

        $this->mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
    }

    public function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
        string $plainText = '',
        array $bcc = [], // ['email' => 'nombre', ...]
        ?string $replyTo = null,
        array $attachments = [], // ['path' => '...', 'name' => '...']
    ): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($to, $toName);

            foreach ($bcc as $bccEmail => $bccName) {
                $this->mail->addBCC($bccEmail, $bccName);
            }

            if ($replyTo !== null) {
                $this->mail->addReplyTo($replyTo);
            }

            foreach ($attachments as $attachment) {
                $this->mail->addAttachment(
                    $attachment['path'],
                    $attachment['name'],
                );
            }

            $this->mail->Subject = $subject;
            $this->mail->isHTML(true);
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = $plainText ?: strip_tags($htmlBody);

            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $this->mail->ErrorInfo);
            return false;
        }
    }
}
