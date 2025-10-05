# 🗑️ Arquivos para Remoção - Algorise PHP Puro

Este arquivo contém comandos PowerShell para remover arquivos desnecessários do projeto.
**IMPORTANTE:** Analise cada comando antes de executar para decidir se realmente quer remover o arquivo.

## 📋 Como usar este arquivo:
1. Abra o PowerShell na pasta do projeto: `c:\xampp\htdocs\algorise-versao-php-puro`
2. Execute apenas os comandos dos arquivos que você quer remover
3. **DICA:** Teste um arquivo por vez para ter certeza

---

## 📁 **CATEGORIA 1: Arquivos de Documentação/Migração Temporários**
*Arquivos criados durante a migração que não são mais necessários*

```powershell
# Remove arquivo de lista de limpeza (este documento substitui ele)
Remove-Item "LISTA-LIMPEZA.md" -Force

# Remove script PowerShell de migração (já foi usado)
Remove-Item "MIGRAR-PARA-PHP-PURO.ps1" -Force

# Remove resumo da migração completa (documentação temporária)
Remove-Item "RESUMO-MIGRACAO-COMPLETA.md" -Force

# Remove documentação de sistema funcionando (temporária)
Remove-Item "SISTEMA-COMPLETO-FUNCIONANDO.md" -Force

# Remove README específico do PHP puro (temos o README.md principal)
Remove-Item "README-PHP-PURO.md" -Force

# Remove script PHP para adaptar controllers (já foi usado)
Remove-Item "adaptar-controllers.php" -Force
```

---

## 📁 **CATEGORIA 2: Arquivos de Backup no /public**
*Backups de arquivos antigos que não são mais necessários*

```powershell
# Remove pasta inteira de backups
Remove-Item "public\backups" -Recurse -Force

# OU remover arquivos individuais dos backups:
Remove-Item "public\backups\index-backup.php" -Force
Remove-Item "public\backups\index-corrigido.php" -Force
Remove-Item "public\backups\index-old.php" -Force
Remove-Item "public\backups\index-php-puro.php" -Force
Remove-Item "public\backups\index-ultra-simples.php" -Force
```

---

## 📁 **CATEGORIA 3: Arquivos de Debug/Teste no /public**
*Arquivos criados para testes e debug que não são mais necessários*

```powershell
# Remove arquivos de configuração de banco (já temos o algorise_db.sql)
Remove-Item "public\criar-banco.php" -Force
Remove-Item "public\import-db.php" -Force
Remove-Item "public\setup-db.php" -Force

# Remove arquivos de debug
Remove-Item "public\debug-sistema.php" -Force
Remove-Item "public\debug-tabela-notas.php" -Force
Remove-Item "public\debug.php" -Force
Remove-Item "public\diagnostico.php" -Force

# Remove arquivos de teste
Remove-Item "public\test.php" -Force
Remove-Item "public\teste-ambiente.php" -Force
Remove-Item "public\teste-completo.php" -Force
Remove-Item "public\teste-conexao.php" -Force
Remove-Item "public\teste-rapido.php" -Force
Remove-Item "public\teste-simples.php" -Force
Remove-Item "public\teste.php" -Force

# Remove arquivos de status e verificação
Remove-Item "public\ok.php" -Force
Remove-Item "public\status.php" -Force
Remove-Item "public\sistema.php" -Force

# Remove login simples (temos o sistema de login principal)
Remove-Item "public\login-simples.php" -Force

# Remove arquivo para ver usuários (debug)
Remove-Item "public\ver-usuarios.php" -Force

# Remove log de erros PHP (será recriado automaticamente se necessário)
Remove-Item "public\php_errors.log" -Force
```

---

## 📁 **CATEGORIA 4: Configurações Docker** 
*⚠️ CUIDADO: Só remova se NÃO estiver usando Docker para deploy*

```powershell
# Remove pasta docker completa (CUIDADO: só se não usar Docker)
Remove-Item "docker" -Recurse -Force

# OU remover arquivos Docker individuais:
Remove-Item "docker-compose.yml" -Force
Remove-Item "portainer.yaml" -Force
Remove-Item "traefik.yaml" -Force
```

---

## 📁 **CATEGORIA 5: Arquivos de Backup/Teste na Raiz**
*Arquivos temporários e backups na pasta principal*

```powershell
# Remove backup do banco SAAS (se não precisar)
Remove-Item "backup_saas.sql" -Force

# Remove backups do composer
Remove-Item "composer-original.json.bak" -Force
Remove-Item "composer-php-puro.json" -Force

# Remove script de criação de usuário (já criamos os usuários)
Remove-Item "criar_usuario.php" -Force

# Remove pasta de notas do projeto antigo
Remove-Item "buscaprecos-notas" -Recurse -Force

# Remove arquivo de teste do XAMPP
Remove-Item "teste-xampp.php" -Force
```

---

## 📁 **CATEGORIA 6: Arquivos do Sistema MacOS**
*Arquivos ocultos do macOS que não são necessários no Windows*

```powershell
# Remove arquivos .DS_Store se existirem
Get-ChildItem -Path . -Recurse -Name ".DS_Store" | Remove-Item -Force
```

---

## 🔒 **ARQUIVOS IMPORTANTES - NÃO REMOVER**

### ✅ **Essenciais do Sistema:**
- `public\index.php` - Entrada principal do sistema
- `src\` - Todo o código fonte (Controllers, Views, Core)
- `vendor\` - Dependências do Composer
- `composer.json` e `composer.lock` - Configuração de dependências
- `.env` e `.env.example` - Configurações de ambiente

### ✅ **Assets Funcionais:**
- `public\css\dashboard.css` - Estilos do sistema
- `public\js\` - Scripts JavaScript funcionais
- `public\img\` - Imagens do sistema (backgrounds, logos)
- `public\catmat-search\` - Funcionalidade de busca CATMAT

### ✅ **Dados e Configuração:**
- `algorise_db.sql` - Estrutura principal do banco
- `migrations\` - Sistema de migrações futuras
- `scripts\` - Scripts de deploy e backup
- `storage\` - Pasta para arquivos de upload
- `.gitignore` - Configuração do Git

---

## 🎯 **Sugestão de Ordem de Remoção:**

1. **Comece com CATEGORIA 1 e 2** (documentação e backups) - Mais seguro
2. **Continue with CATEGORIA 3** (arquivos de debug/teste) - Remove a bagunça
3. **Analise CATEGORIA 4** (Docker) - Só se não usar Docker
4. **Finalize com CATEGORIA 5 e 6** (arquivos diversos)

## 📊 **Estimativa de Espaço Liberado:**
- Categoria 1: ~50KB (documentação)
- Categoria 2: ~200KB (backups)
- Categoria 3: ~150KB (debug/teste)
- Categoria 4: ~500KB (Docker - se remover)
- Categoria 5: ~2MB (backups do banco)
- **Total estimado:** ~3MB de limpeza

---

## ⚠️ **IMPORTANTE - BACKUP ANTES DE REMOVER:**
```powershell
# Crie um commit git antes de remover arquivos
git add .
git commit -m "Backup antes da limpeza de arquivos desnecessários"
```