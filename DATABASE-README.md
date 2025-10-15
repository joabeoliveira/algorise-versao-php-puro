# 📊 Banco de Dados - Algorise

## ✅ Arquivo Oficial

**`schema-producao.sql`** - Este é o ÚNICO arquivo SQL oficial do projeto.

- Reflete exatamente o schema do banco Cloud SQL em produção
- Atualizado em: 2025-10-14
- Baseado em: `studio_results_20251014_2051.json` (export do banco real)

## 🚀 Como Usar

### Criar banco local para desenvolvimento:
```bash
mysql -u root -p < schema-producao.sql
```

### Aplicar no Cloud SQL:
```bash
gcloud sql import sql algorise-db gs://seu-bucket/schema-producao.sql --database=algorise --project=algorise-producao
```

## 📋 Estrutura das Tabelas

### Principais:
- **usuarios** - Autenticação e controle de acesso
- **processos** - Processos licitatórios (TEM campo `orgao`)
- **itens** - Itens dos processos (usa `catmat_catser`, `quantidade`)
- **fornecedores** - Cadastro de fornecedores
- **precos** - Cotações de preços coletadas

### Auxiliares:
- **solicitacoes_cotacao** - Solicitações enviadas a fornecedores
- **cotacoes_rapidas** - Cotações rápidas sem processo
- **cotacoes_rapidas_itens** - Itens das cotações rápidas
- **cotacoes_rapidas_precos** - Preços das cotações rápidas
- **notas_tecnicas** - Notas técnicas geradas
- **configuracoes** - Configurações do sistema
- **logs_sistema** - Logs de auditoria

## ⚠️ Observações Importantes

### Campo `orgao` em `processos`:
- **EXISTE** no banco de produção
- É obrigatório no formulário
- Tipo: VARCHAR(255)

### ENUMs importantes:
- `tipo_contratacao`: Aceita valores em PT-BR ('Pregão Eletrônico', 'Dispensa de Licitação', etc.) E snake_case ('pregao_eletronico', etc.)
- `status`: Aceita valores em PT-BR ('Em Elaboração', 'Finalizado', etc.) E snake_case ('planejamento', 'concluido', etc.)

### Colunas timestamp:
- Tabelas principais usam: `data_criacao`, `data_atualizacao`
- Tabela `usuarios` usa: `criado_em`, `atualizado_em`
- Tabela `solicitacoes_cotacao` usa: `criada_em`, `atualizada_em`

## 🗑️ Arquivos Obsoletos (foram removidos)

- ~~algorise_db.sql~~
- ~~algorise_db_atualizado.sql~~
- ~~algorise-cloud-sql.sql~~
- ~~backup_saas.sql~~
- ~~fix-database-schema.sql~~
- ~~corrigir-admin.sql~~
- ~~create_admin.sql~~
- ~~insert-admin.sql~~
- ~~verificar-usuario.sql~~

Esses arquivos causavam confusão porque tinham schemas diferentes do banco real.
