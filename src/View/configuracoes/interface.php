<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Cabeçalho da página -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="bi bi-gear-fill me-2"></i>Configurações do Sistema</h2>
                    <p class="text-muted mb-0">Personalização da interface e identidade visual</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="/configuracoes/geral" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Configurações gerais da empresa">
                        <i class="bi bi-building"></i> Empresa
                    </a>
                    <a href="/configuracoes/email" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Configurações de email SMTP">
                        <i class="bi bi-envelope"></i> Email
                    </a>
                    <button type="button" class="btn btn-outline-primary active" data-bs-toggle="tooltip" title="Configurações de interface">
                        <i class="bi bi-palette"></i> Interface
                    </button>
                </div>
            </div>

            <!-- Alertas -->
            <div id="alertContainer"></div>

            <div class="row">
                <!-- Configurações principais -->
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-palette me-2"></i>Personalização da Interface</h5>
                        </div>
                        <div class="card-body">
                            <form id="formInterface" method="POST">
                                <div class="row">
                                    <!-- Identidade Visual -->
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-3"><i class="bi bi-badge-tm me-1"></i>Identidade Visual</h6>
                                        
                                        <div class="mb-3">
                                            <label for="interface_nome_sistema" class="form-label">
                                                Nome do Sistema <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control" id="interface_nome_sistema" name="interface_nome_sistema" 
                                                   value="<?= htmlspecialchars($config['interface_nome_sistema']['valor'] ?? 'Algorise') ?>" 
                                                   maxlength="50" required>
                                            <div class="invalid-feedback"></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Logo do Sistema</label>
                                            <div class="border rounded p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div id="logoPreview" class="me-3">
                                                        <?php if (!empty($config['interface_logo_path']['valor'])): ?>
                                                            <img src="<?= htmlspecialchars($config['interface_logo_path']['valor']) ?>" 
                                                                 alt="Logo atual" class="img-thumbnail" style="max-height: 60px;">
                                                        <?php else: ?>
                                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                                 style="width: 60px; height: 60px; border-radius: 8px;">
                                                                <i class="bi bi-image"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="file" class="form-control form-control-sm" id="logoUpload" 
                                                               accept="image/*" name="logo">
                                                        <div class="form-text">
                                                            <strong>Formato:</strong> PNG, JPG, SVG<br>
                                                            <strong>Tamanho recomendado:</strong> 512x512 px <span class="badge bg-info">Quadrado</span><br>
                                                            <strong>Tamanho máximo:</strong> 2MB<br>
                                                            <small class="text-muted">💡 Imagens quadradas ficam melhor na interface</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnUploadLogo">
                                                        <i class="bi bi-upload me-1"></i>Fazer Upload
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" 
                                                            title="Dicas para melhor resultado: Use imagens quadradas (512x512px), com fundo transparente (PNG) e que sejam legíveis em tamanho pequeno">
                                                        <i class="bi bi-info-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="interface_tema" class="form-label">Tema</label>
                                            <select class="form-select" id="interface_tema" name="interface_tema">
                                                <?php $tema = $config['interface_tema']['valor'] ?? 'claro'; ?>
                                                <option value="claro" <?= $tema === 'claro' ? 'selected' : '' ?>>🌞 Claro</option>
                                                <option value="escuro" <?= $tema === 'escuro' ? 'selected' : '' ?>>🌙 Escuro</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Configurações de Cores -->
                                    <div class="col-md-6">
                                        <h6 class="text-primary mb-3"><i class="bi bi-palette2 me-1"></i>Esquema de Cores</h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="interface_cor_primaria" class="form-label">Cor Primária</label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color" 
                                                               id="interface_cor_primaria" name="interface_cor_primaria" 
                                                               value="<?= htmlspecialchars($config['interface_cor_primaria']['valor'] ?? '#0d6efd') ?>">
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($config['interface_cor_primaria']['valor'] ?? '#0d6efd') ?>"
                                                               readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="interface_cor_secundaria" class="form-label">Cor Secundária</label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color" 
                                                               id="interface_cor_secundaria" name="interface_cor_secundaria" 
                                                               value="<?= htmlspecialchars($config['interface_cor_secundaria']['valor'] ?? '#6c757d') ?>">
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($config['interface_cor_secundaria']['valor'] ?? '#6c757d') ?>"
                                                               readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="interface_cor_sucesso" class="form-label">Cor Sucesso</label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color" 
                                                               id="interface_cor_sucesso" name="interface_cor_sucesso" 
                                                               value="<?= htmlspecialchars($config['interface_cor_sucesso']['valor'] ?? '#198754') ?>">
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($config['interface_cor_sucesso']['valor'] ?? '#198754') ?>"
                                                               readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="interface_cor_perigo" class="form-label">Cor Perigo</label>
                                                    <div class="input-group">
                                                        <input type="color" class="form-control form-control-color" 
                                                               id="interface_cor_perigo" name="interface_cor_perigo" 
                                                               value="<?= htmlspecialchars($config['interface_cor_perigo']['valor'] ?? '#dc3545') ?>">
                                                        <input type="text" class="form-control" 
                                                               value="<?= htmlspecialchars($config['interface_cor_perigo']['valor'] ?? '#dc3545') ?>"
                                                               readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="interface_sidebar_cor" class="form-label">Cor do Menu Lateral</label>
                                            <div class="input-group">
                                                <input type="color" class="form-control form-control-color" 
                                                       id="interface_sidebar_cor" name="interface_sidebar_cor" 
                                                       value="<?= htmlspecialchars($config['interface_sidebar_cor']['valor'] ?? '#212529') ?>">
                                                <input type="text" class="form-control" 
                                                       value="<?= htmlspecialchars($config['interface_sidebar_cor']['valor'] ?? '#212529') ?>"
                                                       readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Configurações Avançadas -->
                                <hr class="my-4">
                                <h6 class="text-secondary mb-3"><i class="bi bi-sliders me-1"></i>Configurações Avançadas</h6>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="interface_sidebar_largura" class="form-label">Largura Sidebar</label>
                                            <div class="input-group">
                                                <input type="range" class="form-range" id="interface_sidebar_largura" 
                                                       name="interface_sidebar_largura" min="200" max="400" step="20"
                                                       value="<?= htmlspecialchars($config['interface_sidebar_largura']['valor'] ?? '280') ?>">
                                                <span class="input-group-text" id="sidebarLarguraValue">
                                                    <?= htmlspecialchars($config['interface_sidebar_largura']['valor'] ?? '280') ?>px
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="interface_fonte_familia" class="form-label">Fonte</label>
                                            <select class="form-select" id="interface_fonte_familia" name="interface_fonte_familia">
                                                <?php $fonte = $config['interface_fonte_familia']['valor'] ?? 'system-ui'; ?>
                                                <option value="system-ui" <?= $fonte === 'system-ui' ? 'selected' : '' ?>>Sistema</option>
                                                <option value="Arial, sans-serif" <?= $fonte === 'Arial, sans-serif' ? 'selected' : '' ?>>Arial</option>
                                                <option value="'Roboto', sans-serif" <?= $fonte === "'Roboto', sans-serif" ? 'selected' : '' ?>>Roboto</option>
                                                <option value="'Inter', sans-serif" <?= $fonte === "'Inter', sans-serif" ? 'selected' : '' ?>>Inter</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Efeitos Visuais</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="interface_bordas_arredondadas" 
                                                           name="interface_bordas_arredondadas" value="1"
                                                           <?= ($config['interface_bordas_arredondadas']['valor'] ?? '1') === '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="interface_bordas_arredondadas">
                                                        Bordas Arredondadas
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="interface_sombras" 
                                                           name="interface_sombras" value="1"
                                                           <?= ($config['interface_sombras']['valor'] ?? '1') === '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="interface_sombras">
                                                        Sombras
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="interface_animacoes" 
                                                           name="interface_animacoes" value="1"
                                                           <?= ($config['interface_animacoes']['valor'] ?? '1') === '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="interface_animacoes">
                                                        Animações
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botões de ação -->
                                <hr class="my-4">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-warning" id="btnResetCores">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Restaurar Padrões
                                    </button>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary" id="btnCancelar">
                                            <i class="bi bi-x-lg me-1"></i>Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary" id="btnSalvar">
                                            <i class="bi bi-check-lg me-1"></i>Salvar Configurações
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preview lateral -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Preview em Tempo Real</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="previewContainer" style="transform: scale(0.6); transform-origin: top left; height: 400px; overflow: hidden;">
                                <!-- Mini preview do sistema -->
                                <div class="d-flex" style="width: 500px; height: 300px; border: 2px solid #ddd;">
                                    <!-- Sidebar preview -->
                                    <div id="previewSidebar" class="text-white p-2" style="width: 120px; background: var(--sidebar-color, #212529);">
                                        <div class="d-flex align-items-center mb-2">
                                            <div id="previewLogoSidebar" class="me-1" style="width: 16px; height: 16px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-graph-up-arrow" style="font-size: 10px;"></i>
                                            </div>
                                            <span id="previewNomeSistema" style="font-size: 12px;">Algorise</span>
                                        </div>
                                        <div style="font-size: 10px; opacity: 0.8;">
                                            <div class="mb-1 p-1 rounded" style="background: var(--cor-primaria, #0d6efd);">Dashboard</div>
                                            <div class="mb-1 p-1">Processos</div>
                                            <div class="mb-1 p-1">Fornecedores</div>
                                            <div class="mb-1 p-1">Relatórios</div>
                                        </div>
                                    </div>
                                    <!-- Conteúdo preview -->
                                    <div class="flex-grow-1 p-2 bg-light">
                                        <div class="mb-2">
                                            <div class="d-flex gap-1 mb-2">
                                                <div class="btn btn-sm" style="background: var(--cor-primaria, #0d6efd); color: white; font-size: 8px;">Primária</div>
                                                <div class="btn btn-sm" style="background: var(--cor-sucesso, #198754); color: white; font-size: 8px;">Sucesso</div>
                                                <div class="btn btn-sm" style="background: var(--cor-perigo, #dc3545); color: white; font-size: 8px;">Perigo</div>
                                            </div>
                                            <div class="card" style="font-size: 10px;">
                                                <div class="card-header" style="background: var(--cor-primaria, #0d6efd); color: white;">
                                                    Exemplo de Card
                                                </div>
                                                <div class="card-body p-2">
                                                    <p class="mb-1">Este é um preview das cores escolhidas.</p>
                                                    <div class="progress mb-2" style="height: 6px;">
                                                        <div class="progress-bar" style="width: 70%; background: var(--cor-sucesso, #198754);"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>Preview atualiza automaticamente
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cards de templates -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5>Templates Rápidos</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card border-primary cursor-pointer template-card" data-template="azul">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center gap-1 mb-2">
                                        <div style="width: 20px; height: 20px; background: #0d6efd; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #198754; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #212529; border-radius: 3px;"></div>
                                    </div>
                                    <h6 class="mb-1">Azul Clássico</h6>
                                    <small class="text-muted">Padrão do sistema</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success cursor-pointer template-card" data-template="verde">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center gap-1 mb-2">
                                        <div style="width: 20px; height: 20px; background: #198754; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #20c997; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #343a40; border-radius: 3px;"></div>
                                    </div>
                                    <h6 class="mb-1">Verde Natureza</h6>
                                    <small class="text-muted">Sustentabilidade</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-dark cursor-pointer template-card" data-template="escuro">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center gap-1 mb-2">
                                        <div style="width: 20px; height: 20px; background: #495057; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #6f42c1; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #212529; border-radius: 3px;"></div>
                                    </div>
                                    <h6 class="mb-1">Modo Escuro</h6>
                                    <small class="text-muted">Elegante</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning cursor-pointer template-card" data-template="laranja">
                                <div class="card-body text-center">
                                    <div class="d-flex justify-content-center gap-1 mb-2">
                                        <div style="width: 20px; height: 20px; background: #fd7e14; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #ffc107; border-radius: 3px;"></div>
                                        <div style="width: 20px; height: 20px; background: #495057; border-radius: 3px;"></div>
                                    </div>
                                    <h6 class="mb-1">Laranja Energia</h6>
                                    <small class="text-muted">Vibrante</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.template-card:hover { transform: translateY(-2px); transition: transform 0.2s; }
.form-control-color { max-width: 50px; }
</style>

<!-- Scripts específicos da página -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementos do formulário
    const form = document.getElementById('formInterface');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnCancelar = document.getElementById('btnCancelar');
    const btnUploadLogo = document.getElementById('btnUploadLogo');
    const btnResetCores = document.getElementById('btnResetCores');
    const logoUpload = document.getElementById('logoUpload');
    
    // Elementos de preview
    const previewSidebar = document.getElementById('previewSidebar');
    const previewNomeSistema = document.getElementById('previewNomeSistema');
    
    // Templates de cores
    const templates = {
        azul: {
            interface_cor_primaria: '#0d6efd',
            interface_cor_secundaria: '#6c757d',
            interface_cor_sucesso: '#198754',
            interface_cor_perigo: '#dc3545',
            interface_sidebar_cor: '#212529'
        },
        verde: {
            interface_cor_primaria: '#198754',
            interface_cor_secundaria: '#20c997',
            interface_cor_sucesso: '#28a745',
            interface_cor_perigo: '#dc3545',
            interface_sidebar_cor: '#343a40'
        },
        escuro: {
            interface_cor_primaria: '#495057',
            interface_cor_secundaria: '#6f42c1',
            interface_cor_sucesso: '#20c997',
            interface_cor_perigo: '#e74c3c',
            interface_sidebar_cor: '#212529'
        },
        laranja: {
            interface_cor_primaria: '#fd7e14',
            interface_cor_secundaria: '#ffc107',
            interface_cor_sucesso: '#28a745',
            interface_cor_perigo: '#dc3545',
            interface_sidebar_cor: '#495057'
        }
    };

    // Inicializar preview
    updatePreview();

    // Event listeners para cores
    document.querySelectorAll('input[type="color"]').forEach(input => {
        input.addEventListener('input', function() {
            // Atualizar campo de texto correspondente
            const textInput = this.parentNode.querySelector('input[type="text"]');
            if (textInput) {
                textInput.value = this.value;
            }
            updatePreview();
        });
    });

    // Event listener para nome do sistema
    document.getElementById('interface_nome_sistema').addEventListener('input', function() {
        previewNomeSistema.textContent = this.value || 'Algorise';
    });

    // Event listener para largura da sidebar
    document.getElementById('interface_sidebar_largura').addEventListener('input', function() {
        document.getElementById('sidebarLarguraValue').textContent = this.value + 'px';
        updatePreview();
    });

    // Templates de cores
    document.querySelectorAll('.template-card').forEach(card => {
        card.addEventListener('click', function() {
            const template = this.dataset.template;
            if (templates[template]) {
                Object.keys(templates[template]).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.value = templates[template][key];
                        // Atualizar campo de texto correspondente
                        const textInput = input.parentNode.querySelector('input[type="text"]');
                        if (textInput) {
                            textInput.value = templates[template][key];
                        }
                    }
                });
                updatePreview();
            }
        });
    });

    // Upload de logo
    btnUploadLogo.addEventListener('click', async function() {
        const arquivo = logoUpload.files[0];
        if (!arquivo) {
            showAlert('warning', 'Selecione um arquivo primeiro.');
            return;
        }

        // Validar tamanho do arquivo
        if (arquivo.size > 2 * 1024 * 1024) { // 2MB
            showAlert('warning', 'O arquivo é muito grande. Tamanho máximo: 2MB');
            return;
        }

        // Verificar dimensões da imagem (opcional - apenas aviso)
        const img = new Image();
        img.onload = function() {
            if (this.width !== 512 || this.height !== 512) {
                showAlert('info', `Dimensões da imagem: ${this.width}x${this.height}px. Recomendado: 512x512px para melhor qualidade.`);
            }
        };
        img.src = URL.createObjectURL(arquivo);

        const formData = new FormData();
        formData.append('logo', arquivo);
        formData.append('tipo', 'logo');

        btnUploadLogo.disabled = true;
        btnUploadLogo.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Enviando...';

        try {
            const response = await fetch('/configuracoes/interface/upload', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message);
                // Atualizar preview do logo
                document.getElementById('logoPreview').innerHTML = 
                    `<img src="${result.path}" alt="Logo" class="img-thumbnail" style="max-height: 60px;">`;
                
                // Atualizar logo no preview da sidebar
                document.getElementById('previewLogoSidebar').innerHTML = 
                    `<img src="${result.path}" alt="Logo" style="width: 16px; height: 16px; object-fit: contain;">`;
            } else {
                showAlert('danger', result.message);
            }
        } catch (error) {
            console.error('Erro:', error);
            showAlert('danger', 'Erro ao fazer upload.');
        } finally {
            btnUploadLogo.disabled = false;
            btnUploadLogo.innerHTML = '<i class="bi bi-upload me-1"></i>Fazer Upload';
        }
    });

    // Reset para cores padrão
    btnResetCores.addEventListener('click', function() {
        if (confirm('Deseja restaurar as cores padrão?')) {
            Object.keys(templates.azul).forEach(key => {
                const input = document.getElementById(key);
                if (input) {
                    input.value = templates.azul[key];
                    const textInput = input.parentNode.querySelector('input[type="text"]');
                    if (textInput) {
                        textInput.value = templates.azul[key];
                    }
                }
            });
            updatePreview();
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
            
            // Ajustar checkboxes
            ['interface_bordas_arredondadas', 'interface_sombras', 'interface_animacoes'].forEach(name => {
                const checkbox = document.getElementById(name);
                if (!checkbox.checked) {
                    formData.set(name, '0');
                }
            });
            
            const response = await fetch('/configuracoes/interface/atualizar', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                showAlert('success', result.message + ' A página será recarregada para aplicar as mudanças.');
                setTimeout(() => location.reload(), 3000);
            } else {
                showAlert('danger', result.message);
                
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            input.classList.add('is-invalid');
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

    // Função para atualizar preview
    function updatePreview() {
        const root = document.documentElement;
        
        // Atualizar CSS custom properties para preview
        root.style.setProperty('--cor-primaria', document.getElementById('interface_cor_primaria').value);
        root.style.setProperty('--cor-sucesso', document.getElementById('interface_cor_sucesso').value);
        root.style.setProperty('--cor-perigo', document.getElementById('interface_cor_perigo').value);
        root.style.setProperty('--sidebar-color', document.getElementById('interface_sidebar_cor').value);
        
        // Atualizar sidebar no preview
        const sidebarCor = document.getElementById('interface_sidebar_cor').value;
        previewSidebar.style.background = sidebarCor;
    }

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