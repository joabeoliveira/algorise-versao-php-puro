<?php
// Pega o caminho da URL atual para sabermos qual menu deve ficar ativo
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';

// Carregar configurações de interface
use Joabe\Buscaprecos\Controller\ConfiguracaoController;
$configsInterface = ConfiguracaoController::getConfiguracoesPorCategoria('interface');

// Definir valores padrão se não estiverem configurados
$nomeSystem = $configsInterface['interface_nome_sistema'] ?? 'Algorise';
$corPrimaria = $configsInterface['interface_cor_primaria'] ?? '#0d6efd';
$corSecundaria = $configsInterface['interface_cor_secundaria'] ?? '#6c757d';
$corSucesso = $configsInterface['interface_cor_sucesso'] ?? '#198754';
$corPerigo = $configsInterface['interface_cor_perigo'] ?? '#dc3545';
$corAviso = $configsInterface['interface_cor_aviso'] ?? '#ffc107';
$corInfo = $configsInterface['interface_cor_info'] ?? '#0dcaf0';
$corSidebar = $configsInterface['interface_sidebar_cor'] ?? '#212529';
$larguraSidebar = $configsInterface['interface_sidebar_largura'] ?? '280';
$fonteFamilia = $configsInterface['interface_fonte_familia'] ?? 'system-ui';
$tema = $configsInterface['interface_tema'] ?? 'claro';
$logoPath = $configsInterface['interface_logo_path'] ?? '';
$bordasArredondadas = ($configsInterface['interface_bordas_arredondadas'] ?? '1') === '1';
$sombras = ($configsInterface['interface_sombras'] ?? '1') === '1';
$animacoes = ($configsInterface['interface_animacoes'] ?? '1') === '1';
$duracaoTransicoes = $configsInterface['interface_transicoes_duracao'] ?? '0.3';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? $nomeSystem ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/catmat-search/style.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    
    <?php if (isset($cssExtra)) echo $cssExtra; ?>

    <style>
        /* ==========  CONFIGURAÇÕES DINÂMICAS DA INTERFACE ========== */
        :root {
            --bs-primary: <?= $corPrimaria ?>;
            --bs-secondary: <?= $corSecundaria ?>;
            --bs-success: <?= $corSucesso ?>;
            --bs-danger: <?= $corPerigo ?>;
            --bs-warning: <?= $corAviso ?>;
            --bs-info: <?= $corInfo ?>;
            --sidebar-bg: <?= $corSidebar ?>;
            --sidebar-width: <?= $larguraSidebar ?>px;
            --font-family: <?= $fonteFamilia ?>;
            --border-radius: <?= $bordasArredondadas ? '0.375rem' : '0' ?>;
            --box-shadow: <?= $sombras ? '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)' : 'none' ?>;
            --transition-duration: <?= $duracaoTransicoes ?>s;
        }

        body {
            overflow-x: hidden;
            font-family: var(--font-family);
            <?php if ($tema === 'escuro'): ?>background-color: #121212; color: #e0e0e0;<?php endif; ?>
        }
        
        #sidebar {
            min-height: 100vh;
            width: var(--sidebar-width) !important;
            background-color: var(--sidebar-bg) !important;
        }
        
        .main-content {
            width: 100%;
            padding: 2rem;
            overflow-y: auto;
            height: 100vh;
        }

        /* Aplicar cores personalizadas */
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .btn-primary:hover {
            background-color: color-mix(in srgb, var(--bs-primary) 85%, black);
            border-color: color-mix(in srgb, var(--bs-primary) 85%, black);
        }

        .bg-primary {
            background-color: var(--bs-primary) !important;
        }
        .text-primary {
            color: var(--bs-primary) !important;
        }
        .border-primary {
            border-color: var(--bs-primary) !important;
        }

        .btn-success {
            background-color: var(--bs-success);
            border-color: var(--bs-success);
        }
        .btn-danger {
            background-color: var(--bs-danger);
            border-color: var(--bs-danger);
        }
        .btn-warning {
            background-color: var(--bs-warning);
            border-color: var(--bs-warning);
        }

        .alert-success {
            background-color: color-mix(in srgb, var(--bs-success) 15%, white);
            border-color: var(--bs-success);
            color: color-mix(in srgb, var(--bs-success) 80%, black);
        }
        .alert-danger {
            background-color: color-mix(in srgb, var(--bs-danger) 15%, white);
            border-color: var(--bs-danger);
            color: color-mix(in srgb, var(--bs-danger) 80%, black);
        }

        /* ==========  ESTILOS PERSONALIZADOS PARA TABELAS ========== */
        .table-primary th {
            background-color: var(--bs-primary) !important;
            color: white !important;
            border-color: color-mix(in srgb, var(--bs-primary) 80%, black) !important;
        }
        .table-primary td {
            border-color: color-mix(in srgb, var(--bs-primary) 30%, white) !important;
        }
        .table-primary tbody tr:hover {
            background-color: color-mix(in srgb, var(--bs-primary) 10%, white) !important;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: color-mix(in srgb, var(--bs-primary) 5%, white) !important;
        }

        .table-bordered {
            border-color: color-mix(in srgb, var(--bs-primary) 30%, white) !important;
        }
        .table-bordered th,
        .table-bordered td {
            border-color: color-mix(in srgb, var(--bs-primary) 30%, white) !important;
        }

        /* Estilos para paginação */
        .page-link {
            color: var(--bs-primary) !important;
            border-color: color-mix(in srgb, var(--bs-primary) 30%, white) !important;
        }
        .page-link:hover {
            background-color: color-mix(in srgb, var(--bs-primary) 10%, white) !important;
            border-color: var(--bs-primary) !important;
        }
        .page-item.active .page-link {
            background-color: var(--bs-primary) !important;
            border-color: var(--bs-primary) !important;
        }

        /* Estilos para badges */
        .badge.bg-primary {
            background-color: var(--bs-primary) !important;
        }
        .badge.bg-success {
            background-color: var(--bs-success) !important;
        }
        .badge.bg-danger {
            background-color: var(--bs-danger) !important;
        }
        .badge.bg-warning {
            background-color: var(--bs-warning) !important;
        }

        /* Aplicar bordas arredondadas */
        <?php if ($bordasArredondadas): ?>
        .card, .btn, .form-control, .form-select, .alert, .badge {
            border-radius: var(--border-radius) !important;
        }
        <?php endif; ?>

        /* Aplicar sombras */
        <?php if ($sombras): ?>
        .card, .dropdown-menu {
            box-shadow: var(--box-shadow) !important;
        }
        .card-header {
            box-shadow: inset 0 -1px 0 rgba(0,0,0,.125) !important;
        }
        <?php endif; ?>

        /* Aplicar animações */
        <?php if ($animacoes): ?>
        .btn, .card, .nav-link, .dropdown-item {
            transition: all var(--transition-duration) ease-in-out;
        }
        .btn:hover, .nav-link:hover {
            transform: translateY(-1px);
        }
        <?php endif; ?>

        /* Tema escuro */
        <?php if ($tema === 'escuro'): ?>
        .card {
            background-color: #1e1e1e;
            border-color: #333;
        }
        .card-header {
            background-color: #2d2d2d !important;
            border-color: #333;
        }
        .form-control, .form-select {
            background-color: #2d2d2d;
            border-color: #444;
            color: #e0e0e0;
        }
        .form-control:focus, .form-select:focus {
            background-color: #2d2d2d;
            border-color: var(--bs-primary);
            color: #e0e0e0;
        }
        .table {
            color: #e0e0e0;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > td {
            background-color: #252525;
        }
        <?php endif; ?>
        
        /* ===== CHATBOT STYLES ===== */
        #open-chat {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        #open-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }

        #chatbot-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
            z-index: 1001;
            overflow: hidden;
        }

        #chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #close-button {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
        }

        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        #chat-input-container {
            padding: 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        #user-input {
            flex: 1;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 25px;
            outline: none;
        }

        #send-button {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }

        .message {
            margin-bottom: 15px;
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .message.user {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-left: auto;
        }

        .message.bot {
            background: #e9ecef;
            color: #495057;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            background: #e9ecef;
            border-radius: 18px;
            max-width: 80%;
            margin-bottom: 15px;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6c757d;
            animation: bounce 1.4s ease-in-out infinite both;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0);
            } 40% {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;" id="sidebar">
            <a href="/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <?php if (!empty($logoPath)): ?>
                    <img src="<?= htmlspecialchars($logoPath) ?>" alt="Logo" class="me-2" style="width: 32px; height: 32px; object-fit: contain;">
                <?php else: ?>
                    <i class="bi bi-graph-up-arrow me-2 fs-4"></i>
                <?php endif; ?>
                <span class="fs-4"><?= htmlspecialchars($nomeSystem) ?></span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="/dashboard" class="nav-link <?= str_starts_with($currentPath, '/dashboard') || $currentPath == '/' ? 'active' : 'text-white' ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="/processos" class="nav-link <?= str_starts_with($currentPath, '/processos') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-folder2-open me-2"></i> Processos
                    </a>
                </li>
                
                <li>
                    <a href="/catmat" class="nav-link <?= str_starts_with($currentPath, '/catmat') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-search me-2"></i> Consulta CATMAT
                    </a>
                </li>

                <li>
                    <a href="/fornecedores" class="nav-link <?= str_starts_with($currentPath, '/fornecedores') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-truck me-2"></i> Fornecedores
                    </a>
                </li>

                <li>
                    <a href="/acompanhamento" class="nav-link <?= str_starts_with($currentPath, '/acompanhamento') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-stopwatch me-2"></i> Acompanhamento
                    </a>
                </li>

                <li>
                    <a href="/cotacao-rapida" class="nav-link <?= str_starts_with($currentPath, '/cotacao-rapida') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-lightning-charge-fill me-2"></i> Cotação Rápida
                    </a>
                </li>

                 <li>
                    <a href="/relatorio-gestao" class="nav-link <?= str_starts_with($currentPath, '/relatorio-gestao') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-bar-chart-fill me-2"></i> Relatório de Gestão <span class="badge bg-warning text-dark ms-2">Em Breve</span>
                    </a>
                </li>
                <li>
                    <a href="/relatorios" class="nav-link <?= str_starts_with($currentPath, '/relatorios') ? 'active' : 'text-white' ?>">
                        <i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Histórico de Relatórios
                    </a>
                </li>
                
                <?php if (isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'admin'): ?>
                    <li>
                        <a href="/usuarios" class="nav-link <?= str_starts_with($currentPath, '/usuarios') ? 'active' : 'text-white' ?>">
                            <i class="bi bi-people me-2"></i> Usuários
                        </a>
                    </li>
                    <li>
                        <a href="/configuracoes" class="nav-link <?= str_starts_with($currentPath, '/configuracoes') ? 'active' : 'text-white' ?>">
                            <i class="bi bi-gear me-2"></i> Configurações
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <hr>
            <div>
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <strong><?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="/logout">Sair</a></li>
                </ul>
            </div>
        </div>
        <main class="main-content">
            <?php 
                if (isset($paginaConteudo) && file_exists($paginaConteudo)) {
                    include $paginaConteudo;
                } else {
                    echo "<h1>Erro: Conteúdo da página não encontrado.</h1>";
                }
            ?>
        </main>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script> 
    <script src="/js/dashboard.js"></script>
    <script src="/catmat-search/search.js"></script>
    <script src="/js/pesquisa-precos.js"></script>
    <script src="/js/analise-precos.js"></script>
    <script src="/js/pesquisa-orgaos.js"></script>
    <script src="/js/formulario-dinamico.js"></script>
    <script src="/js/solicitacao-lote.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="/js/masks.js"></script>
    <script src="/js/cotacao-rapida.js"></script>
    <script src="https://unpkg.com/read-excel-file@5.7.1/bundle/read-excel-file.min.js"></script>


    <button id="open-chat">
    <i class="fas fa-robot" style="font-size: 24px;"></i>
</button>

<div id="chatbot-container" style="display: none;">
    <div id="chat-header">
        Chatbot Algorise
        <button id="close-button"><i class="fas fa-times"></i></button>
    </div>
    <div id="chat-messages"></div>
    <div id="chat-input-container">
        <input type="text" id="user-input" placeholder="Digite sua mensagem...">
        <button id="send-button">Enviar</button>
    </div>
</div>

<script>
    // Configurações
    const INACTIVITY_TIME = 30000; // 30 segundos
    const WEBHOOK_URL = 'https://n8n-n8n.yg64ke.easypanel.host/webhook/chatbotBP'; // URL do seu webhook

    // Elementos do DOM
    const chatContainer = document.getElementById('chatbot-container');
    const openChatBtn = document.getElementById('open-chat');
    const closeButton = document.getElementById('close-button');
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    // Controle de tempo de inatividade
    let inactivityTimer;

    // Event Listeners
    openChatBtn.addEventListener('click', openChat);
    closeButton.addEventListener('click', closeChat);
    userInput.addEventListener('keypress', (e) => e.key === 'Enter' && sendMessage());
    sendButton.addEventListener('click', sendMessage);
    document.addEventListener('mousemove', resetInactivityTimer);
    document.addEventListener('keypress', resetInactivityTimer);

    // Funções principais
    function openChat() {
        chatContainer.style.display = 'flex';
        openChatBtn.style.display = 'none';
        resetInactivityTimer();
        userInput.focus();
    }

    function closeChat() {
        chatContainer.style.display = 'none';
        openChatBtn.style.display = 'block';
        clearTimeout(inactivityTimer);
    }

    function resetInactivityTimer() {
        if (chatContainer.style.display === 'none') return;
        clearTimeout(inactivityTimer);
        inactivityTimer = setTimeout(closeChat, INACTIVITY_TIME);
    }

    async function sendMessage() {
        const message = userInput.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        userInput.value = '';
        resetInactivityTimer();

        // Mostra indicador de "digitando"
        const typingIndicator = createTypingIndicator();
        chatMessages.appendChild(typingIndicator);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const response = await fetch(WEBHOOK_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chatInput: message }),
            });

            // Remove o indicador
            chatMessages.removeChild(typingIndicator);

            // Processa resposta
            const responseText = await response.text();
            let botReply = processResponse(responseText);
            addMessage(botReply, 'bot');
        } catch (error) {
            chatMessages.removeChild(typingIndicator);
            addMessage("Erro ao conectar com o chatbot. Tente novamente.", 'bot');
            console.error('Erro:', error);
        }
    }

    function processResponse(responseText) {
        try {
            const data = JSON.parse(responseText);
            return data.response || data.message || responseText;
        } catch {
            return responseText;
        }
    }

    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${sender}-message`;

        if (sender === 'bot') {
            messageDiv.innerHTML = `
                <div class="bot-icon"><i class="fas fa-robot"></i></div>
                <div class="message-content">${text}</div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-content">${text}</div>
            `;
        }

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function createTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'message bot-message';
        typingDiv.innerHTML = `
            <div class="bot-icon"><i class="fas fa-robot"></i></div>
            <div class="typing-indicator">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        `;
        return typingDiv;
    }
</script>

<?php if (isset($jsExtra)) echo $jsExtra; ?>

</body>
</html>