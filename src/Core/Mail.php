<?php

namespace Joabe\Buscaprecos\Core;

/**
 * Sistema simples de email em PHP puro
 * Substitui o PHPMailer com funcionalidades básicas
 */
class Mail
{
    private string $from;
    private string $fromName;
    private string $subject;
    private string $body;
    private array $to = [];
    private array $headers = [];
    private bool $isHtml = false;
    
    // Configuração SMTP (se necessário)
    private static ?array $smtpConfig = null;
    
    public function __construct()
    {
        $this->headers[] = 'MIME-Version: 1.0';
        $this->headers[] = 'X-Mailer: PHP/' . phpversion();
    }
    
    /**
     * Configura SMTP (opcional - para casos mais avançados)
     */
    public static function setSmtpConfig(array $config): void
    {
        self::$smtpConfig = $config;
    }
    
    /**
     * Define o remetente
     */
    public function setFrom(string $email, string $name = ''): self
    {
        $this->from = $email;
        $this->fromName = $name;
        return $this;
    }
    
    /**
     * Adiciona um destinatário
     */
    public function addTo(string $email, string $name = ''): self
    {
        $this->to[] = $name ? "$name <$email>" : $email;
        return $this;
    }
    
    /**
     * Define o assunto
     */
    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Define o corpo do email
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Define se o email é HTML
     */
    public function isHtml(bool $html = true): self
    {
        $this->isHtml = $html;
        return $this;
    }
    
    /**
     * Adiciona um header customizado
     */
    public function addHeader(string $header): self
    {
        $this->headers[] = $header;
        return $this;
    }
    
    /**
     * Envia o email
     */
    public function send(): bool
    {
        if (empty($this->to) || empty($this->from) || empty($this->subject)) {
            return false;
        }
        
        // Monta os headers
        $headers = $this->buildHeaders();
        
        // Destinatários
        $recipients = implode(', ', $this->to);
        
        // Se há configuração SMTP, usa envio via SMTP
        if (self::$smtpConfig) {
            return $this->sendViaSMTP($recipients, $headers);
        }
        
        // Caso contrário, usa mail() nativo
        return mail($recipients, $this->subject, $this->body, $headers);
    }
    
    /**
     * Constrói os headers do email
     */
    private function buildHeaders(): string
    {
        $headers = $this->headers;
        
        // From header
        $fromHeader = $this->fromName ? 
            "{$this->fromName} <{$this->from}>" : 
            $this->from;
        $headers[] = "From: $fromHeader";
        
        // Content-Type
        if ($this->isHtml) {
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }
        
        return implode("\r\n", $headers);
    }
    
    /**
     * Envia email via SMTP usando socket
     */
    private function sendViaSMTP(string $recipients, string $headers): bool
    {
        $config = self::$smtpConfig;
        
        // Conecta ao servidor SMTP
        $socket = fsockopen($config['host'], $config['port'], $errno, $errstr, 30);
        if (!$socket) {
            return false;
        }
        
        try {
            // Lê resposta inicial
            $this->readSMTPResponse($socket);
            
            // EHLO
            fwrite($socket, "EHLO localhost\r\n");
            $this->readSMTPResponse($socket);
            
            // STARTTLS (se necessário)
            if ($config['encryption'] === 'tls') {
                fwrite($socket, "STARTTLS\r\n");
                $this->readSMTPResponse($socket);
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                
                // EHLO novamente após TLS
                fwrite($socket, "EHLO localhost\r\n");
                $this->readSMTPResponse($socket);
            }
            
            // AUTH LOGIN
            if (isset($config['username']) && isset($config['password'])) {
                fwrite($socket, "AUTH LOGIN\r\n");
                $this->readSMTPResponse($socket);
                
                fwrite($socket, base64_encode($config['username']) . "\r\n");
                $this->readSMTPResponse($socket);
                
                fwrite($socket, base64_encode($config['password']) . "\r\n");
                $this->readSMTPResponse($socket);
            }
            
            // MAIL FROM
            fwrite($socket, "MAIL FROM: <{$this->from}>\r\n");
            $this->readSMTPResponse($socket);
            
            // RCPT TO para cada destinatário
            foreach ($this->to as $to) {
                // Extrai apenas o email do formato "Nome <email>"
                preg_match('/<([^>]+)>/', $to, $matches);
                $email = $matches[1] ?? $to;
                
                fwrite($socket, "RCPT TO: <$email>\r\n");
                $this->readSMTPResponse($socket);
            }
            
            // DATA
            fwrite($socket, "DATA\r\n");
            $this->readSMTPResponse($socket);
            
            // Conteúdo do email
            $content = "To: $recipients\r\n";
            $content .= "Subject: {$this->subject}\r\n";
            $content .= "$headers\r\n\r\n";
            $content .= $this->body;
            $content .= "\r\n.\r\n";
            
            fwrite($socket, $content);
            $this->readSMTPResponse($socket);
            
            // QUIT
            fwrite($socket, "QUIT\r\n");
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        } finally {
            fclose($socket);
        }
    }
    
    /**
     * Lê resposta do servidor SMTP
     */
    private function readSMTPResponse($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 1024)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
    
    /**
     * Método estático para envio rápido
     */
    public static function quickSend(
        string $to, 
        string $subject, 
        string $body, 
        string $from, 
        string $fromName = '',
        bool $isHtml = false
    ): bool {
        $mail = new self();
        return $mail
            ->setFrom($from, $fromName)
            ->addTo($to)
            ->setSubject($subject)
            ->setBody($body)
            ->isHtml($isHtml)
            ->send();
    }
    
    /**
     * Envia email usando configuração do .env
     */
    public static function sendWithEnvConfig(
        string $to,
        string $subject,
        string $body,
        bool $isHtml = false
    ): bool {
        // Configura SMTP se as variáveis estiverem definidas
        if (isset($_ENV['MAIL_HOST'])) {
            self::setSmtpConfig([
                'host' => $_ENV['MAIL_HOST'],
                'port' => $_ENV['MAIL_PORT'] ?? 587,
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? ''
            ]);
        }
        
        return self::quickSend(
            $to,
            $subject,
            $body,
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@localhost',
            $_ENV['MAIL_FROM_NAME'] ?? 'Sistema',
            $isHtml
        );
    }
}