# 🚀 INSTRUÇÕES PARA APLICAR MIGRATIONS NO CLOUD SQL

## Método 1: Via Cloud Console (MAIS FÁCIL) ⭐

1. Acesse: https://console.cloud.google.com/sql/instances/algorise-db/overview?project=algorise-producao

2. Clique na aba **"DATABASES"**

3. Clique em **"algorise"**

4. Clique no botão **"OPEN CLOUD SHELL"** (ícone de terminal no topo)

5. No Cloud Shell, execute:
```bash
gcloud sql connect algorise-db --user=root --database=algorise --project=algorise-producao
```

6. Digite a senha do root quando solicitado

7. Copie e cole o conteúdo do arquivo `aplicar-migrations-manual.sql` linha por linha ou tudo de uma vez

8. Verifique se as tabelas foram criadas:
```sql
SHOW TABLES LIKE 'lotes_%';
DESCRIBE notas_tecnicas;
```

---

## Método 2: Via Cloud Shell Editor

1. Acesse: https://console.cloud.google.com/cloudshell/editor?project=algorise-producao

2. Faça upload do arquivo `aplicar-migrations-manual.sql`

3. Execute no terminal:
```bash
gcloud sql connect algorise-db --user=root --database=algorise --project=algorise-producao < aplicar-migrations-manual.sql
```

---

## Método 3: Via SQL Workspace (Interface Gráfica)

1. Acesse: https://console.cloud.google.com/sql/instances/algorise-db/connections/sql-workspace?project=algorise-producao

2. Clique em **"Open SQL Workspace"**

3. Copie e cole o conteúdo de `aplicar-migrations-manual.sql`

4. Clique em **"RUN"**

---

## ✅ Verificação Pós-Migration

Após aplicar, execute para confirmar:

```sql
-- Ver tabelas criadas
SHOW TABLES LIKE 'lotes_%';

-- Resultado esperado:
-- lotes_solicitacao
-- lotes_solicitacao_fornecedores
-- lotes_solicitacao_itens

-- Verificar estrutura de notas_tecnicas
SHOW CREATE TABLE notas_tecnicas;

-- Confirmar que processo_id aceita NULL
SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'notas_tecnicas' 
AND COLUMN_NAME = 'processo_id';
```

---

## 🎯 Após Aplicar as Migrations

O deploy da aplicação já está em andamento. Assim que finalizar:

### Teste as Funcionalidades:

1. **Solicitação de Cotação em Lote**
   - URL: https://algorise-producao.rj.r.appspot.com/processos/3/itens
   - Ação: Enviar solicitação → Deve funcionar sem erro

2. **Busca CATMAT**
   - URL: https://algorise-producao.rj.r.appspot.com/processos/3/itens
   - Ação: Adicionar item → Buscar CATMAT → Sem erro 404

3. **Cotação Rápida**
   - URL: https://algorise-producao.rj.r.appspot.com/cotacao-rapida
   - Ação: Inserir CATMAT → Salvar → Deve funcionar

4. **Pesquisa de Preços**
   - URL: https://algorise-producao.rj.r.appspot.com/processos/3/itens/1/pesquisar
   - Ação: Página deve carregar sem erro

---

## 🆘 Em Caso de Problemas

Se houver erro ao aplicar as migrations:

```sql
-- Ver última mensagem de erro
SHOW WARNINGS;

-- Verificar se as foreign keys existem
SELECT TABLE_NAME, CONSTRAINT_NAME 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_NAME IN ('processos', 'itens', 'fornecedores');
```

---

## 📞 Contato

Deploy em andamento. Tempo estimado: 5-10 minutos.

Versão do deploy: **20251025t182121**
URL: https://algorise-producao.rj.r.appspot.com
