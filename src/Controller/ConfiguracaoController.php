<?php

namespace Joabe\Buscaprecos\Controller;

use Joabe\Buscaprecos\Core\Router;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class ConfiguracaoController
{
    /**
     * Exibe a página principal de configurações gerais
     */
    public function index($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            $_SESSION['flash'] = 'Acesso negado. Apenas administradores podem acessar as configurações.';
            Router::redirect('/dashboard');
            return;
        }

        $pdo = \getDbConnection();
        
        // Buscar todas as configurações da empresa
        $stmt = $pdo->prepare("
            SELECT chave, valor, descricao, tipo, obrigatorio 
            FROM configuracoes 
            WHERE categoria = 'empresa' 
            ORDER BY chave
        ");
        $stmt->execute();
        $configuracoes = $stmt->fetchAll();
        
        // Organizar em array associativo para facilitar no template
        $config = [];
        foreach ($configuracoes as $item) {
            $config[$item['chave']] = [
                'valor' => $item['valor'],
                'descricao' => $item['descricao'],
                'tipo' => $item['tipo'],
                'obrigatorio' => (bool)$item['obrigatorio']
            ];
        }
        
        $tituloPagina = "Configurações - Dados da Empresa";
        $paginaConteudo = __DIR__ . '/../View/configuracoes/geral.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        echo ob_get_clean();
    }

    /**
     * Atualiza as configurações gerais da empresa
     */
    public function atualizar($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            Router::json(['success' => false, 'message' => 'Acesso negado.'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::json(['success' => false, 'message' => 'Método não permitido.'], 405);
            return;
        }

        try {
            $dados = $_POST;
            
            // Validações básicas
            $erros = $this->validarDados($dados);
            
            if (!empty($erros)) {
                Router::json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $erros]);
                return;
            }

            $pdo = \getDbConnection();
            $pdo->beginTransaction();

            // Atualizar cada configuração
            $stmt = $pdo->prepare("
                UPDATE configuracoes 
                SET valor = ?, atualizado_em = NOW() 
                WHERE chave = ? AND categoria = 'empresa'
            ");

            $configuracoesEmpresa = [
                'empresa_nome',
                'empresa_cnpj', 
                'empresa_endereco',
                'empresa_cidade',
                'empresa_estado',
                'empresa_cep',
                'empresa_telefone',
                'empresa_email',
                'empresa_site'
            ];

            foreach ($configuracoesEmpresa as $chave) {
                $valor = isset($dados[$chave]) ? trim($dados[$chave]) : '';
                $stmt->execute([$valor, $chave]);
            }

            $pdo->commit();

            // Log da ação
            \logarEvento('info', 'Configurações da empresa atualizadas', [
                'usuario' => $_SESSION['usuario_nome'] ?? 'Sistema',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
            ]);

            Router::json(['success' => true, 'message' => 'Configurações atualizadas com sucesso!']);

        } catch (\PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            \logarEvento('error', 'Erro de banco de dados ao atualizar configurações: ' . $e->getMessage(), ['code' => $e->getCode()]);
            
            Router::json([
                'success' => false, 
                'message' => 'Erro de banco de dados. Por favor, contate o suporte.'
            ], 500);

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            \logarEvento('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
            
            Router::json([
                'success' => false, 
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Valida os dados das configurações
     */
    private function validarDados($dados)
    {
        $erros = [];

        // Validar nome da empresa (obrigatório)
        if (empty(trim($dados['empresa_nome'] ?? ''))) {
            $erros['empresa_nome'] = 'Nome da empresa é obrigatório.';
        }

        // Validar CNPJ se fornecido
        if (!empty($dados['empresa_cnpj']) && !\validarCnpj($dados['empresa_cnpj'])) {
            $erros['empresa_cnpj'] = 'CNPJ inválido.';
        }

        // Validar email se fornecido
        if (!empty($dados['empresa_email']) && !\validarEmail($dados['empresa_email'])) {
            $erros['empresa_email'] = 'Email inválido.';
        }

        // Validar CEP se fornecido (formato brasileiro)
        if (!empty($dados['empresa_cep'])) {
            $cep = preg_replace('/\D/', '', $dados['empresa_cep']);
            if (strlen($cep) !== 8) {
                $erros['empresa_cep'] = 'CEP deve ter 8 dígitos.';
            }
        }

        // Validar estado (UF)
        if (!empty($dados['empresa_estado']) && strlen($dados['empresa_estado']) !== 2) {
            $erros['empresa_estado'] = 'Estado deve ter 2 letras (UF).';
        }

        // Validar URL do site se fornecida
        if (!empty($dados['empresa_site']) && !filter_var($dados['empresa_site'], FILTER_VALIDATE_URL)) {
            $erros['empresa_site'] = 'URL do site inválida.';
        }

        return $erros;
    }

    /**
     * Exibe a página de configurações de email
     */
    public function emailConfig($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            $_SESSION['flash'] = 'Acesso negado. Apenas administradores podem acessar as configurações.';
            Router::redirect('/dashboard');
            return;
        }

        $pdo = \getDbConnection();
        
        // Buscar todas as configurações de email
        $stmt = $pdo->prepare("
            SELECT chave, valor, descricao, tipo, obrigatorio 
            FROM configuracoes 
            WHERE categoria = 'email' 
            ORDER BY chave
        ");
        $stmt->execute();
        $configuracoes = $stmt->fetchAll();
        
        // Organizar em array associativo
        $config = [];
        foreach ($configuracoes as $item) {
            $config[$item['chave']] = [
                'valor' => $item['valor'],
                'descricao' => $item['descricao'],
                'tipo' => $item['tipo'],
                'obrigatorio' => (bool)$item['obrigatorio']
            ];
        }
        
        $tituloPagina = "Configurações - Email SMTP";
        $paginaConteudo = __DIR__ . '/../View/configuracoes/email.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        echo ob_get_clean();
    }

    /**
     * Atualiza as configurações de email
     */
    public function atualizarEmail($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            Router::json(['success' => false, 'message' => 'Acesso negado.'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::json(['success' => false, 'message' => 'Método não permitido.'], 405);
            return;
        }

        try {
            $dados = $_POST;
            
            // Validações específicas de email
            $erros = $this->validarDadosEmail($dados);
            
            if (!empty($erros)) {
                Router::json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $erros]);
                return;
            }

            $pdo = \getDbConnection();
            $pdo->beginTransaction();

            // Atualizar cada configuração de email
            $stmt = $pdo->prepare("
                UPDATE configuracoes 
                SET valor = ?, atualizado_em = NOW() 
                WHERE chave = ? AND categoria = 'email'
            ");

            $configuracoesEmail = [
                'email_smtp_host',
                'email_smtp_port',
                'email_smtp_security',
                'email_smtp_auth',
                'email_smtp_username',
                'email_smtp_password',
                'email_from_address',
                'email_from_name',
                'email_reply_to',
                'email_timeout',
                'email_charset',
                'email_debug_level'
            ];

            foreach ($configuracoesEmail as $chave) {
                $valor = isset($dados[$chave]) ? trim($dados[$chave]) : '';
                $stmt->execute([$valor, $chave]);
            }

            $pdo->commit();

            // Log da ação
            \logarEvento('info', 'Configurações de email atualizadas', [
                'usuario' => $_SESSION['usuario_nome'] ?? 'Sistema',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
            ]);

            Router::json(['success' => true, 'message' => 'Configurações de email atualizadas com sucesso!']);

        } catch (\PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            \logarEvento('error', 'Erro de banco de dados ao atualizar configurações de email: ' . $e->getMessage(), ['code' => $e->getCode()]);
            
            Router::json([
                'success' => false, 
                'message' => 'Erro de banco de dados. Por favor, contate o suporte.'
            ], 500);

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            \logarEvento('error', 'Erro ao atualizar configurações de email: ' . $e->getMessage());
            
            Router::json([
                'success' => false, 
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Testa o envio de email com as configurações atuais
     */
    public function testarEmail($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            Router::json(['success' => false, 'message' => 'Acesso negado.'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::json(['success' => false, 'message' => 'Método não permitido.'], 405);
            return;
        }

        try {
            $emailTeste = $_POST['email_teste'] ?? '';
            
            if (empty($emailTeste) || !\validarEmail($emailTeste)) {
                Router::json(['success' => false, 'message' => 'Email de teste inválido.']);
                return;
            }

            // Buscar configurações de email
            $configs = self::getConfiguracoesPorCategoria('email');
            
            // Verificar se as configurações básicas estão preenchidas
            if (empty($configs['email_smtp_host']) || empty($configs['email_from_address'])) {
                Router::json(['success' => false, 'message' => 'Configure pelo menos o servidor SMTP e o email remetente antes de testar.']);
                return;
            }

            // Usar PHPMailer para teste
            $mail = new PHPMailer(true);

            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = $configs['email_smtp_host'];
            $mail->SMTPAuth = (bool)($configs['email_smtp_auth'] ?? true);
            $mail->Username = $configs['email_smtp_username'] ?? '';
            $mail->Password = $configs['email_smtp_password'] ?? '';
            $mail->SMTPSecure = $configs['email_smtp_security'] ?? 'tls';
            $mail->Port = (int)($configs['email_smtp_port'] ?? 587);
            $mail->CharSet = $configs['email_charset'] ?? 'UTF-8';
            $mail->Timeout = (int)($configs['email_timeout'] ?? 30);

            // Debug level
            $mail->SMTPDebug = (int)($configs['email_debug_level'] ?? 0);

            // Configurações do email
            $mail->setFrom($configs['email_from_address'], $configs['email_from_name'] ?? 'Algorise');
            $mail->addAddress($emailTeste);
            
            if (!empty($configs['email_reply_to'])) {
                $mail->addReplyTo($configs['email_reply_to']);
            }

            // Conteúdo do email de teste
            $mail->isHTML(true);
            $mail->Subject = 'Teste de Configuração SMTP - Algorise';
            $mail->Body = '
                <h2>Teste de Email</h2>
                <p>Este é um email de teste para validar as configurações SMTP do sistema Algorise.</p>
                <p><strong>Data/Hora:</strong> ' . date('d/m/Y H:i:s') . '</p>
                <p><strong>Usuário:</strong> ' . ($_SESSION['usuario_nome'] ?? 'Sistema') . '</p>
                <p>Se você recebeu este email, as configurações SMTP estão funcionando corretamente!</p>
                <hr>
                <p><small>Este é um email automático gerado pelo sistema Algorise.</small></p>
            ';

            $mail->send();

            Router::json(['success' => true, 'message' => 'Email de teste enviado com sucesso!']);

        } catch (Exception $e) {
            error_log("Erro no teste de email: " . $e->getMessage());
            \logarEvento('error', 'Erro no teste de email: ' . $e->getMessage());
            Router::json(['success' => false, 'message' => 'Erro ao enviar email: ' . $e->getMessage()]);
        }
    }

    /**
     * Valida os dados de configuração de email
     */
    private function validarDadosEmail($dados)
    {
        $erros = [];

        // Validar porta SMTP
        if (!empty($dados['email_smtp_port'])) {
            $porta = (int)$dados['email_smtp_port'];
            if ($porta <= 0 || $porta > 65535) {
                $erros['email_smtp_port'] = 'Porta deve estar entre 1 e 65535.';
            }
        }

        // Validar email remetente se fornecido
        if (!empty($dados['email_from_address']) && !\validarEmail($dados['email_from_address'])) {
            $erros['email_from_address'] = 'Email remetente inválido.';
        }

        // Validar username (geralmente é um email)
        if (!empty($dados['email_smtp_username']) && !\validarEmail($dados['email_smtp_username'])) {
            $erros['email_smtp_username'] = 'Email de usuário SMTP inválido.';
        }

        // Validar reply-to se fornecido
        if (!empty($dados['email_reply_to']) && !\validarEmail($dados['email_reply_to'])) {
            $erros['email_reply_to'] = 'Email de resposta inválido.';
        }

        // Validar tipo de criptografia
        if (!empty($dados['email_smtp_security'])) {
            $tiposValidos = ['tls', 'ssl', 'none'];
            if (!in_array($dados['email_smtp_security'], $tiposValidos)) {
                $erros['email_smtp_security'] = 'Tipo de criptografia inválido.';
            }
        }

        // Validar timeout
        if (!empty($dados['email_timeout'])) {
            $timeout = (int)$dados['email_timeout'];
            if ($timeout < 5 || $timeout > 300) {
                $erros['email_timeout'] = 'Timeout deve estar entre 5 e 300 segundos.';
            }
        }

        // Validar debug level
        if (isset($dados['email_debug_level'])) {
            $debugLevel = (int)$dados['email_debug_level'];
            if ($debugLevel < 0 || $debugLevel > 4) {
                $erros['email_debug_level'] = 'Nível de debug deve estar entre 0 e 4.';
            }
        }

        return $erros;
    }

    /**
     * Exibe a página de configurações de interface
     */
    public function interfaceConfig($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            $_SESSION['flash'] = 'Acesso negado. Apenas administradores podem acessar as configurações.';
            Router::redirect('/dashboard');
            return;
        }

        $pdo = \getDbConnection();
        
        // Buscar todas as configurações de interface
        $stmt = $pdo->prepare("
            SELECT chave, valor, descricao, tipo, obrigatorio 
            FROM configuracoes 
            WHERE categoria = 'interface' 
            ORDER BY chave
        ");
        $stmt->execute();
        $configuracoes = $stmt->fetchAll();
        
        // Organizar em array associativo
        $config = [];
        foreach ($configuracoes as $item) {
            $config[$item['chave']] = [
                'valor' => $item['valor'],
                'descricao' => $item['descricao'],
                'tipo' => $item['tipo'],
                'obrigatorio' => (bool)$item['obrigatorio']
            ];
        }
        
        $tituloPagina = "Configurações - Interface e Personalização";
        $paginaConteudo = __DIR__ . '/../View/configuracoes/interface.php';

        ob_start();
        require __DIR__ . '/../View/layout/main.php';
        echo ob_get_clean();
    }

    /**
     * Atualiza as configurações de interface
     */
    public function atualizarInterface($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            Router::json(['success' => false, 'message' => 'Acesso negado.'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::json(['success' => false, 'message' => 'Método não permitido.'], 405);
            return;
        }

        try {
            $dados = $_POST;
            
            // Validações específicas de interface
            $erros = $this->validarDadosInterface($dados);
            
            if (!empty($erros)) {
                Router::json(['success' => false, 'message' => 'Dados inválidos.', 'errors' => $erros]);
                return;
            }

            $pdo = \getDbConnection();
            $pdo->beginTransaction();

            // Atualizar cada configuração de interface
            $stmt = $pdo->prepare("
                UPDATE configuracoes 
                SET valor = ?, atualizado_em = NOW() 
                WHERE chave = ? AND categoria = 'interface'
            ");

            $configuracoesInterface = [
                'interface_cor_primaria',
                'interface_cor_secundaria',
                'interface_cor_sucesso',
                'interface_cor_perigo',
                'interface_cor_aviso',
                'interface_cor_info',
                'interface_tema',
                'interface_sidebar_cor',
                'interface_navbar_cor',
                'interface_nome_sistema',
                'interface_sidebar_largura',
                'interface_fonte_familia',
                'interface_bordas_arredondadas',
                'interface_sombras',
                'interface_animacoes',
                'interface_transicoes_duracao'
            ];

            foreach ($configuracoesInterface as $chave) {
                $valor = isset($dados[$chave]) ? trim($dados[$chave]) : '';
                $stmt->execute([$valor, $chave]);
            }

            $pdo->commit();

            // Log da ação
            \logarEvento('info', 'Configurações de interface atualizadas', [
                'usuario' => $_SESSION['usuario_nome'] ?? 'Sistema',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
            ]);

            Router::json(['success' => true, 'message' => 'Configurações de interface atualizadas com sucesso!']);

        } catch (\PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            error_log("Erro de banco de dados ao atualizar configurações de interface: " . $e->getMessage());
            \logarEvento('error', 'Erro de banco de dados ao atualizar configurações de interface: ' . $e->getMessage(), ['code' => $e->getCode()]);
            
            Router::json([
                'success' => false, 
                'message' => 'Erro de banco de dados. Por favor, contate o suporte.'
            ], 500);

        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            error_log("Erro ao atualizar configurações de interface: " . $e->getMessage());
            \logarEvento('error', 'Erro ao atualizar configurações de interface: ' . $e->getMessage());
            
            Router::json([
                'success' => false, 
                'message' => 'Erro interno. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Upload de logo/favicon
     */
    public function uploadLogo($params = [])
    {
        // Verificar se o usuário é admin
        if (!isset($_SESSION['usuario_role']) || $_SESSION['usuario_role'] !== 'admin') {
            Router::json(['success' => false, 'message' => 'Acesso negado.'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Router::json(['success' => false, 'message' => 'Método não permitido.'], 405);
            return;
        }

        try {
            if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                Router::json(['success' => false, 'message' => 'Nenhum arquivo foi enviado ou ocorreu um erro.']);
                return;
            }

            $arquivo = $_FILES['logo'];
            $tipo = $_POST['tipo'] ?? 'logo'; // logo ou favicon

            // Validar tipo de arquivo
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            
            if (!in_array($extensao, $extensoesPermitidas)) {
                Router::json(['success' => false, 'message' => 'Tipo de arquivo não permitido. Use: ' . implode(', ', $extensoesPermitidas)]);
                return;
            }

            // Validar tamanho (máx 2MB)
            if ($arquivo['size'] > 2 * 1024 * 1024) {
                Router::json(['success' => false, 'message' => 'Arquivo muito grande. Máximo: 2MB']);
                return;
            }

            // Validar dimensões da imagem (apenas para imagens, não SVG)
            $avisoTamanho = '';
            if (in_array($extensao, ['jpg', 'jpeg', 'png', 'gif'])) {
                $dimensoes = getimagesize($arquivo['tmp_name']);
                if ($dimensoes) {
                    $largura = $dimensoes[0];
                    $altura = $dimensoes[1];
                    
                    if ($largura !== 512 || $altura !== 512) {
                        $avisoTamanho = " Dimensões: {$largura}x{$altura}px. Recomendado: 512x512px para melhor qualidade.";
                    }
                }
            }

            // Criar diretório se não existir
            $uploadDir = __DIR__ . '/../../public/uploads/interface/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Gerar nome único para o arquivo
            $nomeArquivo = $tipo . '_' . time() . '.' . $extensao;
            $caminhoCompleto = $uploadDir . $nomeArquivo;
            $caminhoRelativo = '/uploads/interface/' . $nomeArquivo;

            // Mover arquivo
            if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
                Router::json(['success' => false, 'message' => 'Erro ao salvar arquivo.']);
                return;
            }

            // Atualizar configuração no banco
            $pdo = \getDbConnection();
            $chaveConfig = $tipo === 'favicon' ? 'interface_favicon_path' : 'interface_logo_path';
            
            $stmt = $pdo->prepare("
                UPDATE configuracoes 
                SET valor = ?, atualizado_em = NOW() 
                WHERE chave = ? AND categoria = 'interface'
            ");
            $stmt->execute([$caminhoRelativo, $chaveConfig]);

            // Log da ação
            \logarEvento('info', "Upload de $tipo realizado", [
                'arquivo' => $nomeArquivo,
                'usuario' => $_SESSION['usuario_nome'] ?? 'Sistema'
            ]);

            Router::json([
                'success' => true, 
                'message' => ucfirst($tipo) . ' enviado com sucesso!' . $avisoTamanho,
                'path' => $caminhoRelativo
            ]);

        } catch (\PDOException $e) {
            error_log("Erro de banco de dados no upload: " . $e->getMessage());
            \logarEvento('error', 'Erro de banco de dados no upload: ' . $e->getMessage(), ['code' => $e->getCode()]);
            Router::json(['success' => false, 'message' => 'Erro de banco de dados ao salvar a configuração do arquivo.']);

        } catch (\Exception $e) {
            error_log("Erro no upload: " . $e->getMessage());
            \logarEvento('error', 'Erro no upload: ' . $e->getMessage());
            Router::json(['success' => false, 'message' => 'Erro interno no upload.']);
        }
    }

    /**
     * Valida os dados de configuração de interface
     */
    private function validarDadosInterface($dados)
    {
        $erros = [];

        // Validar cores (formato hexadecimal)
        $camposCor = [
            'interface_cor_primaria', 'interface_cor_secundaria', 
            'interface_cor_sucesso', 'interface_cor_perigo',
            'interface_cor_aviso', 'interface_cor_info',
            'interface_sidebar_cor', 'interface_navbar_cor'
        ];

        foreach ($camposCor as $campo) {
            if (!empty($dados[$campo]) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $dados[$campo])) {
                $erros[$campo] = 'Cor deve estar no formato hexadecimal (#000000).';
            }
        }

        // Validar nome do sistema (obrigatório)
        if (empty(trim($dados['interface_nome_sistema'] ?? ''))) {
            $erros['interface_nome_sistema'] = 'Nome do sistema é obrigatório.';
        }

        // Validar largura da sidebar
        if (!empty($dados['interface_sidebar_largura'])) {
            $largura = (int)$dados['interface_sidebar_largura'];
            if ($largura < 200 || $largura > 500) {
                $erros['interface_sidebar_largura'] = 'Largura deve estar entre 200 e 500 pixels.';
            }
        }

        // Validar duração das transições
        if (!empty($dados['interface_transicoes_duracao'])) {
            $duracao = (float)$dados['interface_transicoes_duracao'];
            if ($duracao < 0.1 || $duracao > 2.0) {
                $erros['interface_transicoes_duracao'] = 'Duração deve estar entre 0.1 e 2.0 segundos.';
            }
        }

        // Validar tema
        if (!empty($dados['interface_tema']) && !in_array($dados['interface_tema'], ['claro', 'escuro'])) {
            $erros['interface_tema'] = 'Tema deve ser "claro" ou "escuro".';
        }

        return $erros;
    }

    /**
     * Busca uma configuração específica
     */
    public static function getConfiguracao($chave, $valorPadrao = null)
    {
        try {
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
            $stmt->execute([$chave]);
            $resultado = $stmt->fetch();
            
            return $resultado ? $resultado['valor'] : $valorPadrao;
        } catch (\PDOException $e) {
            error_log("Erro de banco de dados ao buscar configuração: " . $e->getMessage());
            \logarEvento('error', 'Erro de banco de dados ao buscar configuração: ' . $e->getMessage(), ['code' => $e->getCode()]);
            return $valorPadrao;

        } catch (\Exception $e) {
            error_log("Erro ao buscar configuração: " . $e->getMessage());
            \logarEvento('error', 'Erro ao buscar configuração: ' . $e->getMessage());
            return $valorPadrao;
        }
    }

    /**
     * Busca múltiplas configurações por categoria
     */
    public static function getConfiguracoesPorCategoria($categoria)
    {
        try {
            $pdo = \getDbConnection();
            $stmt = $pdo->prepare("
                SELECT chave, valor 
                FROM configuracoes 
                WHERE categoria = ?
            ");
            $stmt->execute([$categoria]);
            $resultados = $stmt->fetchAll();
            
            $config = [];
            foreach ($resultados as $item) {
                $config[$item['chave']] = $item['valor'];
            }
            
            return $config;
        } catch (\PDOException $e) {
            error_log("Erro de banco de dados ao buscar configurações por categoria: " . $e->getMessage());
            \logarEvento('error', 'Erro de banco de dados ao buscar configurações por categoria: ' . $e->getMessage(), ['code' => $e->getCode()]);
            return [];

        } catch (\Exception $e) {
            error_log("Erro ao buscar configurações: " . $e->getMessage());
            \logarEvento('error', 'Erro ao buscar configurações: ' . $e->getMessage());
            return [];
        }
    }
}
