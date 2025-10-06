<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-gear-fill me-2"></i>Configurações do Sistema</h2>
                    <p class="text-muted mb-0">Dados gerais da órgão/empresa</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-bs-toggle="tooltip" title="Configurações gerais do órgão">
                        <i class="bi bi-building"></i> Órgão
                    </button>
                    <a href="/configuracoes/email" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Configurações de email SMTP">
                        <i class="bi bi-envelope"></i> Email
                    </a>
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
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Dados do Órgão/Empresa</h5>
                </div>
                <div class="card-body">
                    <form id="formConfiguracoes" method="POST">
                        <div class="row">
                            <!-- Informações básicas -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-info-circle me-1"></i>Informações Básicas</h6>
                                
                                <div class="mb-3">
                                    <label for="empresa_nome" class="form-label">
                                        Nome do Órgão/Empresa <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="empresa_nome" name="empresa_nome" 
                                           value="<?= htmlspecialchars($config['empresa_nome']['valor'] ?? '') ?>" 
                                           maxlength="100" required>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="empresa_cnpj" class="form-label">CNPJ</label>
                                    <input type="text" class="form-control" id="empresa_cnpj" name="empresa_cnpj" 
                                           value="<?= htmlspecialchars($config['empresa_cnpj']['valor'] ?? '') ?>" 
                                           data-mask="00.000.000/0000-00" placeholder="00.000.000/0000-00">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="empresa_email" class="form-label">Email Principal</label>
                                    <input type="email" class="form-control" id="empresa_email" name="empresa_email" 
                                           value="<?= htmlspecialchars($config['empresa_email']['valor'] ?? '') ?>" 
                                           maxlength="100">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="empresa_telefone" class="form-label">Telefone</label>
                                    <input type="text" class="form-control" id="empresa_telefone" name="empresa_telefone" 
                                           value="<?= htmlspecialchars($config['empresa_telefone']['valor'] ?? '') ?>" 
                                           data-mask="(00) 00000-0000" placeholder="(00) 00000-0000">
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="empresa_site" class="form-label">Website</label>
                                    <input type="url" class="form-control" id="empresa_site" name="empresa_site" 
                                           value="<?= htmlspecialchars($config['empresa_site']['valor'] ?? '') ?>" 
                                           placeholder="https://www.exemplo.com.br">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Endereço -->
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="bi bi-geo-alt me-1"></i>Endereço</h6>
                                
                                <div class="mb-3">
                                    <label for="empresa_endereco" class="form-label">Endereço Completo</label>
                                    <textarea class="form-control" id="empresa_endereco" name="empresa_endereco" 
                                              rows="2" maxlength="255"><?= htmlspecialchars($config['empresa_endereco']['valor'] ?? '') ?></textarea>
                                    <div class="invalid-feedback"></div>
                                    <div class="form-text">Rua, número, complemento</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="empresa_cidade" class="form-label">Cidade</label>
                                            <input type="text" class="form-control" id="empresa_cidade" name="empresa_cidade" 
                                                   value="<?= htmlspecialchars($config['empresa_cidade']['valor'] ?? '') ?>" 
                                                   maxlength="100">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="empresa_estado" class="form-label">Estado</label>
                                            <select class="form-select" id="empresa_estado" name="empresa_estado">
                                                <option value="">Selecione...</option>
                                                <?php
                                                $estados = [
                                                    'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
                                                    'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal', 'ES' => 'Espírito Santo',
                                                    'GO' => 'Goiás', 'MA' => 'Maranhão', 'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul',
                                                    'MG' => 'Minas Gerais', 'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná',
                                                    'PE' => 'Pernambuco', 'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
                                                    'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima', 'SC' => 'Santa Catarina',
                                                    'SP' => 'São Paulo', 'SE' => 'Sergipe', 'TO' => 'Tocantins'
                                                ];
                                                $estadoAtual = $config['empresa_estado']['valor'] ?? '';
                                                foreach ($estados as $uf => $nome): ?>
                                                    <option value="<?= $uf ?>" <?= $estadoAtual === $uf ? 'selected' : '' ?>>
                                                        <?= $uf ?> - <?= $nome ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="empresa_cep" class="form-label">CEP</label>
                                    <input type="text" class="form-control" id="empresa_cep" name="empresa_cep" 
                                           value="<?= htmlspecialchars($config['empresa_cep']['valor'] ?? '') ?>" 
                                           data-mask="00000-000" placeholder="00000-000">
                                    <div class="invalid-feedback"></div>
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

            <!-- Card de ajuda -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info bg-opacity-10">
                    <h6 class="mb-0 text-info"><i class="bi bi-info-circle me-1"></i>Informações</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li>Os campos marcados com <span class="text-danger">*</span> são obrigatórios.</li>
                        <li>O CNPJ, se preenchido, deve ser válido.</li>
                        <li>Essas informações podem ser usadas nos relatórios e documentos gerados pelo sistema.</li>
                        <li>As alterações são salvas imediatamente após confirmação.</li>
                    </ul>
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

    // Aplicar máscaras
    const masks = {
        'data-mask="00.000.000/0000-00"': '00.000.000/0000-00',
        'data-mask="(00) 00000-0000"': '(00) 00000-0000', 
        'data-mask="00000-000"': '00000-000'
    };

    // Form validation e submit
    const form = document.getElementById('formConfiguracoes');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnCancelar = document.getElementById('btnCancelar');

    // Cancelar alterações
    btnCancelar.addEventListener('click', function() {
        if (confirm('Deseja descartar as alterações?')) {
            location.reload();
        }
    });

    // Submit do formulário
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Desabilitar botão durante o envio
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Salvando...';

        try {
            const formData = new FormData(form);
            
            const response = await fetch('/configuracoes/atualizar', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message);
                
                // Recarregar após 2 segundos para mostrar dados atualizados
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert('danger', result.message);
                
                // Mostrar erros nos campos específicos
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
            // Reabilitar botão
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-check-lg me-1"></i>Salvar Configurações';
        }
    });

    // Limpar validação quando o usuário digitar
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

        // Auto-dismiss após 5 segundos para alertas de sucesso
        if (type === 'success') {
            setTimeout(() => {
                const alertElement = bootstrap.Alert.getOrCreateInstance(alert);
                alertElement.close();
            }, 5000);
        }
    }
});
</script>