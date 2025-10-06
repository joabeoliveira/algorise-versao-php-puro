<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-gear-fill me-2"></i>Configurações do Sistema</h2>
                    <p class="text-muted mb-0">Configurações de email SMTP</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="/configuracoes/geral" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Configurações gerais da empresa">
                        <i class="bi bi-building"></i> Empresa
                    </a>
                    <button type="button" class="btn btn-outline-primary active" data-bs-toggle="tooltip" title="Configurações de email SMTP">
                        <i class="bi bi-envelope"></i> Email
                    </button>
                    <a href="/configuracoes/interface" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Configurações de interface">
                        <i class="bi bi-palette"></i> Interface
                    </a>
                </div>
            </div>

            <!-- Alertas -->
            <div id="alertContainer"></div>

            <!-- Card principal -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-envelope me-2"></i>Configurações de Email SMTP</h5>
                </div>
                <div class="card-body">
                    <form id="formEmail" method="POST">
                        <div class="row">
                            <!-- Configurações do servidor SMTP -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-server me-1"></i>Servidor SMTP</h6>
                                
                                <div class="mb-3">
                                    <label for="email_smtp_host" class="form-label">Servidor SMTP</label>
                                    <input type="text" class="form-control" id="email_smtp_host" name="email_smtp_host" 
                                           value="<?= htmlspecialchars($config['email_smtp_host']['valor'] ?? '') ?>" 
                                           placeholder="smtp.gmail.com">
                                    <div class="invalid-feedback"></div>
                                    <div class="form-text">Ex: smtp.gmail.com, smtp.office365.com</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_smtp_port" class="form-label">Porta</label>
                                            <input type="number" class="form-control" id="email_smtp_port" name="email_smtp_port" 
                                                   value="<?= htmlspecialchars($config['email_smtp_port']['valor'] ?? '587') ?>" 
                                                   min="1" max="65535">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_smtp_security" class="form-label">Criptografia</label>
                                            <select class="form-select" id="email_smtp_security" name="email_smtp_security">
                                                <?php $security = $config['email_smtp_security']['valor'] ?? 'tls'; ?>
                                                <option value="tls" <?= $security === 'tls' ? 'selected' : '' ?>>TLS (587)</option>
                                                <option value="ssl" <?= $security === 'ssl' ? 'selected' : '' ?>>SSL (465)</option>
                                                <option value="none" <?= $security === 'none' ? 'selected' : '' ?>>Nenhuma (25)</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="email_smtp_auth" name="email_smtp_auth" value="1"
                                               <?= ($config['email_smtp_auth']['valor'] ?? '1') === '1' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="email_smtp_auth">
                                            Usar autenticação SMTP
                                        </label>
                                    </div>
                                </div>

                                <div id="authSection" class="<?= ($config['email_smtp_auth']['valor'] ?? '1') === '0' ? 'd-none' : '' ?>">
                                    <div class="mb-3">
                                        <label for="email_smtp_username" class="form-label">Usuário/Email</label>
                                        <input type="email" class="form-control" id="email_smtp_username" name="email_smtp_username" 
                                               value="<?= htmlspecialchars($config['email_smtp_username']['valor'] ?? '') ?>" 
                                               placeholder="seu-email@gmail.com">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email_smtp_password" class="form-label">Senha</label>
                                        <input type="password" class="form-control" id="email_smtp_password" name="email_smtp_password" 
                                               value="<?= htmlspecialchars($config['email_smtp_password']['valor'] ?? '') ?>" 
                                               placeholder="Digite a senha">
                                        <div class="invalid-feedback"></div>
                                        <div class="form-text">
                                            <i class="bi bi-info-circle"></i> Para Gmail, use uma "Senha de app" específica.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configurações de envio -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-send me-1"></i>Configurações de Envio</h6>
                                
                                <div class="mb-3">
                                    <label for="email_from_address" class="form-label">Email Remetente</label>
                                    <input type="email" class="form-control" id="email_from_address" name="email_from_address" 
                                           value="<?= htmlspecialchars($config['email_from_address']['valor'] ?? '') ?>" 
                                           placeholder="noreply@suaempresa.com">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="email_from_name" class="form-label">Nome do Remetente</label>
                                    <input type="text" class="form-control" id="email_from_name" name="email_from_name" 
                                           value="<?= htmlspecialchars($config['email_from_name']['valor'] ?? '') ?>" 
                                           placeholder="Algorise Sistema">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="email_reply_to" class="form-label">Email para Resposta <small class="text-muted">(opcional)</small></label>
                                    <input type="email" class="form-control" id="email_reply_to" name="email_reply_to" 
                                           value="<?= htmlspecialchars($config['email_reply_to']['valor'] ?? '') ?>" 
                                           placeholder="contato@suaempresa.com">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <!-- Configurações avançadas -->
                                <h6 class="text-secondary mt-4 mb-3"><i class="bi bi-sliders me-1"></i>Configurações Avançadas</h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_timeout" class="form-label">Timeout (seg)</label>
                                            <input type="number" class="form-control" id="email_timeout" name="email_timeout" 
                                                   value="<?= htmlspecialchars($config['email_timeout']['valor'] ?? '30') ?>" 
                                                   min="5" max="300">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_debug_level" class="form-label">Debug Level</label>
                                            <select class="form-select" id="email_debug_level" name="email_debug_level">
                                                <?php $debug = $config['email_debug_level']['valor'] ?? '0'; ?>
                                                <option value="0" <?= $debug === '0' ? 'selected' : '' ?>>Desabilitado</option>
                                                <option value="1" <?= $debug === '1' ? 'selected' : '' ?>>Cliente</option>
                                                <option value="2" <?= $debug === '2' ? 'selected' : '' ?>>Servidor</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="email_charset" class="form-label">Codificação</label>
                                    <select class="form-select" id="email_charset" name="email_charset">
                                        <?php $charset = $config['email_charset']['valor'] ?? 'UTF-8'; ?>
                                        <option value="UTF-8" <?= $charset === 'UTF-8' ? 'selected' : '' ?>>UTF-8</option>
                                        <option value="ISO-8859-1" <?= $charset === 'ISO-8859-1' ? 'selected' : '' ?>>ISO-8859-1</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Teste de email -->
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-success mb-3"><i class="bi bi-envelope-check me-1"></i>Teste de Envio</h6>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="email_teste" 
                                           placeholder="Digite um email para teste" 
                                           value="<?= htmlspecialchars($_SESSION['usuario_email'] ?? '') ?>">
                                    <button type="button" class="btn btn-success" id="btnTestarEmail">
                                        <i class="bi bi-send me-1"></i>Enviar Teste
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> Configure e salve primeiro, depois teste o envio.
                                </div>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="btnCancelar">
                                <i class="bi bi-x-lg me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSalvar">
                                <i class="bi bi-check-lg me-1"></i>Salvar Configurações
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card de configurações pré-definidas -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-info">
                        <div class="card-header bg-info bg-opacity-10">
                            <h6 class="mb-0 text-info"><i class="bi bi-lightbulb me-1"></i>Gmail</h6>
                        </div>
                        <div class="card-body small">
                            <strong>Servidor:</strong> smtp.gmail.com<br>
                            <strong>Porta:</strong> 587 (TLS) ou 465 (SSL)<br>
                            <strong>Criptografia:</strong> TLS ou SSL<br>
                            <strong>Dica:</strong> Use uma "Senha de app" específica
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="mb-0 text-warning"><i class="bi bi-lightbulb me-1"></i>Outlook/Hotmail</h6>
                        </div>
                        <div class="card-body small">
                            <strong>Servidor:</strong> smtp.office365.com<br>
                            <strong>Porta:</strong> 587<br>
                            <strong>Criptografia:</strong> TLS<br>
                            <strong>Dica:</strong> Use sua senha normal do Outlook
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));

    // Elementos do formulário
    const form = document.getElementById('formEmail');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnTestarEmail = document.getElementById('btnTestarEmail');
    const authCheckbox = document.getElementById('email_smtp_auth');
    const authSection = document.getElementById('authSection');

    // Controlar seção de autenticação
    authCheckbox.addEventListener('change', function() {
        if (this.checked) {
            authSection.classList.remove('d-none');
        } else {
            authSection.classList.add('d-none');
        }
    });

    // Cancelar alterações
    btnCancelar.addEventListener('click', function() {
        if (confirm('Deseja descartar as alterações?')) {
            location.reload();
        }
    });

    // Submit do formulário
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Salvando...';

        try {
            const formData = new FormData(form);
            
            // Ajustar checkbox value
            if (!authCheckbox.checked) {
                formData.set('email_smtp_auth', '0');
            }
            
            const response = await fetch('/configuracoes/email/atualizar', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message);
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert('danger', result.message);
                
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            input.classList.add('is-invalid');
                            const feedback = input.parentNode.querySelector('.invalid-feedback');
                            if (feedback) {
                                feedback.textContent = result.errors[field];
                            }
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('danger', 'Erro de comunicação. Tente novamente.');
        } finally {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar Configurações';
        }
    });

    // Teste de email
    btnTestarEmail.addEventListener('click', async function() {
        const emailTeste = document.getElementById('email_teste').value;
        
        if (!emailTeste || !emailTeste.includes('@')) {
            showAlert('warning', 'Digite um email válido para o teste.');
            return;
        }

        btnTestarEmail.disabled = true;
        btnTestarEmail.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...';

        try {
            const formData = new FormData();
            formData.append('email_teste', emailTeste);
            
            const response = await fetch('/configuracoes/email/testar', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message + ` Verifique a caixa de entrada de ${emailTeste}.`);
            } else {
                showAlert('danger', result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('danger', 'Erro ao testar email. Verifique as configurações.');
        } finally {
            btnTestarEmail.disabled = false;
            btnTestarEmail.innerHTML = '<i class="bi bi-send me-1"></i>Enviar Teste';
        }
    });

    // Limpar validação ao digitar
    form.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
        }
    });

    // Função para mostrar alertas
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.innerHTML = '';
        alertContainer.appendChild(alert);

        if (type === 'success') {
            setTimeout(() => {
                const alertElement = bootstrap.Alert.getOrCreateInstance(alert);
                alertElement.close();
            }, 5000);
        }
    }
});
</script>