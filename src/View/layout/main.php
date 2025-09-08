<?php
// Pega o caminho da URL atual para sabermos qual menu deve ficar ativo
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloPagina ?? 'Algorise' ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/catmat-search/style.css">
    <link rel="stylesheet" href="/css/dashboard.css">

    <style>
        body {
            overflow-x: hidden;
        }
        #sidebar {
            min-height: 100vh;
        }
        .main-content {
            width: 100%;
            padding: 2rem;
            overflow-y: auto;
            height: 100vh;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark" style="width: 280px;" id="sidebar">
            <a href="/dashboard" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="bi bi-graph-up-arrow me-2 fs-4"></i>
                <span class="fs-4">Algorise</span>
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
                        <a href="#" class="nav-link text-white">
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
        Chatbot Buscapreços AI
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

</body>
</html>