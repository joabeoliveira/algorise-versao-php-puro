<body>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <a href="/processos/<?= $processo['id'] ?>/itens" class="btn btn-sm btn-outline-secondary mb-2">Voltar para a Lista de Itens</a>
        </div>
        <div class="card bg-light p-3 mb-4">
            <h5 class="mb-1">Pesquisa de Preços para o Item: <strong><?= htmlspecialchars($item['descricao']) ?></strong></h5>
            <p class="mb-0 text-muted">Processo: <?= htmlspecialchars($processo['nome_processo']) ?></p>
            <?php if ($item['catmat_catser']): ?>
                <p class="mb-0 text-muted">CATMAT/CATSER: <?= htmlspecialchars($item['catmat_catser']) ?></p>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="row gx-4">
            <div class="col-md-5">
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 fs-5">Busca Automática no Painel de Preços</h4>
                    </div>
                    <div class="card-body text-center">
                        <p>Buscar preços recentes no Painel de Preços do Governo Federal.</p>
                        <button id="btnBuscarPainel" class="btn btn-primary" data-catmat="<?= htmlspecialchars($item['catmat_catser']) ?>">
                            <i class="fas fa-search-dollar"></i> Buscar no Painel de Preços
                        </button>
                        <div id="loadingPainel" class="spinner-border text-primary mt-3" role="status" style="display: none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0 fs-5">Busca em Contratações Similares</h4>
                    </div>
                    <div class="card-body text-center">
                        <p>Pesquisar contratações de outros órgãos públicos (Inciso II, IN 65/2021).</p>
                        <button id="btnBuscarOrgaos" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#modalBuscaOrgaos">
                            <i class="bi bi-building"></i> Buscar em Órgãos
                        </button>
                    </div>
                </div>

                <h4>Adicionar Cotação Manualmente</h4>
                <div class="card">
                    <div class="card-body">
                        <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/precos" method="POST" id="formCotaManual">
                            <div class="mb-3">
                                <label for="fonte" class="form-label">Fonte da Pesquisa</label>
                                <select class="form-select" id="fonte" name="fonte" required>
                                    <option value="Pesquisa com Fornecedor" selected>Pesquisa Direta com Fornecedor (Inc. IV)</option>
                                    <option value="Site Especializado">Mídia / Site Especializado (Inc. III)</option>
                                    <option value="Nota Fiscal">Base de Notas Fiscais (Inc. V)</option>
                                    <option value="Painel de Preços">Painel de Preços (Inc. I)</option>
                                    <option value="Contratação Similar">Contratação Similar (Inc. II)</option>
                                </select>
                            </div>

                            <div id="group-valor" class="mb-3">
                                <label for="valor" class="form-label">Valor (R$)</label>
                                <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                            </div>
                            <div id="group-unidade" class="mb-3">
                                <label for="unidade_medida" class="form-label">Unidade de Medida</label>
                                <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" required>
                            </div>
                            <div id="group-data" class="mb-3">
                                <label for="data_coleta" class="form-label">Data da Coleta / Nota Fiscal</label>
                                <input type="date" class="form-control" id="data_coleta" name="data_coleta" required>
                            </div>
                            <div id="group-fornecedor-nome" class="mb-3">
                                <label for="fornecedor_nome" class="form-label">Nome do Fornecedor</label>
                                <input type="text" class="form-control" id="fornecedor_nome" name="fornecedor_nome">
                            </div>
                            <div id="group-fornecedor-cnpj" class="mb-3">
                                <label for="fornecedor_cnpj" class="form-label">CNPJ do Fornecedor</label>
                                <input type="text" class="form-control" id="fornecedor_cnpj" name="fornecedor_cnpj">
                            </div>
                            <div id="group-link" class="mb-3">
                                <label for="link_evidencia" class="form-label">Link da Evidência</label>
                                <input type="url" class="form-control" id="link_evidencia" name="link_evidencia" placeholder="https://...">
                            </div>

                            <button type="submit" class="btn btn-success">Salvar Cotação</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <h4>Preços Coletados (<?= count($precos) ?>)</h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Fonte</th>
                                <th>Valor</th>
                                <th>Unidade</th>
                                <th>Data</th>
                                <th>Fornecedor/Órgão</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($precos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center p-3 text-muted">Nenhuma cotação registrada ainda.</td>
                                </tr>
                            <?php endif; ?>
                            
                            <?php foreach ($precos as $preco): ?>
                                <tr>
                                    <td><?= htmlspecialchars($preco['fonte']) ?></td>
                                    <td>R$ <?= number_format($preco['valor'], 2, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($preco['unidade_medida'] ?? $item['unidade_medida']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($preco['data_coleta'])) ?></td>
                                    <td><?= htmlspecialchars($preco['fornecedor_nome']) ?: 'N/A' ?></td>
                                    <td class="text-center">
                                        <form action="/processos/<?= $processo['id'] ?>/itens/<?= $item['id'] ?>/precos/<?= $preco['id'] ?>/excluir" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta cotação?');">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Excluir Cotação">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalResultadosPainel" tabindex="-1" aria-labelledby="modalResultadosPainelLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalResultadosPainelLabel">Resultados do Painel de Preços</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Selecione uma das cotações abaixo para preencher o formulário de adição automaticamente.</p>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width: 5%;"></th>
                            <th>Preço Unit.</th>
                            <th>Unidade Fornecida</th>
                            <th>Capacidade</th>
                            <th>Fornecedor</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody id="tabelaResultadosPainel">
                        </tbody>
                </table>
            </div>
          </div>
           <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnAdicionarSelecionados">Adicionar Cotações Selecionadas</button>
            </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="modalBuscaOrgaos" tabindex="-1" aria-labelledby="modalBuscaOrgaosLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBuscaOrgaosLabel">Pesquisar em Contratações de Outros Órgãos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <p class="mb-1"><b>Opção 1: Busca por UASG específica</b></p>
                        <p class="small text-muted">Informe até 3 códigos de UASG de órgãos que deseja pesquisar.</p>
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" class="form-control" id="uasgInput1" placeholder="UASG 1">
                            <input type="text" class="form-control" id="uasgInput2" placeholder="UASG 2">
                            <input type="text" class="form-control" id="uasgInput3" placeholder="UASG 3">
                        </div>
                        <button class="btn btn-sm btn-primary" id="btnExecutarBuscaUasg">Buscar por UASG</button>
                    </div>
                    <div class="alert alert-secondary">
                        <p class="mb-1"><b>Opção 2: Busca automática pela sua região (<?= htmlspecialchars($processo['regiao']) ?>)</b></p>
                        <p class="small text-muted">O sistema buscará aleatoriamente em 20 contratações realizadas no seu estado.</p>
                        <button class="btn btn-sm btn-secondary" id="btnExecutarBuscaRegiao">Buscar por Região</button>
                    </div>
                    <hr>
                    <div id="loadingOrgaos" class="spinner-border text-primary" role="status" style="display: none;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div id="resultadosOrgaosContainer" class="table-responsive" style="display: none;">
                        <table class="table table-sm table-hover">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width: 5%;"></th>
                                    <th>Preço Unit.</th>
                                    <th>Unidade</th>
                                    <th>Órgão/Fornecedor</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaResultadosOrgaos"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnAdicionarSelecionadosOrgaos">Adicionar Cotações Selecionadas</button>
                </div>
            </div>
        </div>
    </div>
</body>