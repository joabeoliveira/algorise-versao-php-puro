# 🚀 GUIA COMPLETO - DEPLOY ALGORISE NO GOOGLE CLOUD

## ✅ STATUS: ARQUIVOS PRONTOS PARA DEPLOY

Todos os arquivos necessários já foram criados e configurados:

- ✅ **Dados exportados** do XAMPP local
- ✅ **Scripts de configuração** automatizados  
- ✅ **Estrutura do banco** otimizada para Cloud SQL
- ✅ **Configuração híbrida** (funciona local + produção)

## 🎯 PROCESSO SIMPLIFICADO - 3 PASSOS

### **PASSO 1: INSTALAR GOOGLE CLOUD CLI**

Escolha uma opção:

**Opção A - Automática (recomendada):**
```
1. Abra PowerShell como Administrador
2. Execute: winget install Google.CloudSDK
3. Reinicie o terminal
```

**Opção B - Manual:**
```
1. Baixe: https://dl.google.com/dl/cloudsdk/channels/rapid/GoogleCloudSDKInstaller.exe
2. Execute o instalador
3. Marque "Add to PATH"
4. Reinicie o terminal
```

### **PASSO 2: EXECUTAR CONFIGURAÇÃO AUTOMÁTICA**

Abra PowerShell no diretório do projeto e execute:

```powershell
.\configurar-gcp-automatico.ps1
```

**O que este script faz automaticamente:**
- 🔐 Login no Google Cloud
- 🏗️ Configura projeto `algorise-producao`
- 🔌 Habilita todas as APIs necessárias
- 🗄️ Cria Cloud SQL com MySQL
- 🔐 Configura secrets seguros
- 📤 Faz upload dos arquivos SQL
- 📥 Importa estrutura e dados
- 🚀 Faz deploy da aplicação

### **PASSO 3: ACESSAR A APLICAÇÃO**

Após o script concluir:

**URL da aplicação:** `https://algorise-producao.uc.r.appspot.com`

**Login inicial:**
- Email: `admin@algorise.com`
- Senha: `admin123`

## 📋 INFORMAÇÕES QUE O SCRIPT VAI PEDIR

Durante a execução, você precisará fornecer:

1. **Login Google Cloud** (abre no navegador)
2. **Supabase Anon Key** - Sua chave atual do Supabase
3. **Email Gmail** - Para envio de emails do sistema
4. **App Password** - Senha de app do Gmail (não a senha normal)

### Como obter App Password do Gmail:
1. Gmail → Configurações → Segurança
2. Ativação em duas etapas → Senhas de app
3. Gere uma senha para "Algorise"

## 🔍 MONITORAMENTO APÓS DEPLOY

**Links úteis:**
- **Console GCP:** https://console.cloud.google.com/appengine?project=algorise-producao
- **Logs:** https://console.cloud.google.com/logs/query?project=algorise-producao  
- **Cloud SQL:** https://console.cloud.google.com/sql?project=algorise-producao

**Comandos úteis:**
```bash
# Ver logs em tempo real
gcloud app logs tail -s default

# Status da aplicação  
gcloud app versions list

# Conectar ao banco
gcloud sql connect algorise-db --user=algorise-user
```

## 💰 CUSTOS ESTIMADOS

**Mensais aproximados:**
- App Engine: $0-20 (free tier + uso baixo)
- Cloud SQL f1-micro: $7
- Cloud Storage: $1
- **Total: ~$8-28/mês**

## 🆘 SUPORTE E TROUBLESHOOTING

### Erro: "gcloud não encontrado"
```powershell
# Reinstalar
winget uninstall Google.CloudSDK
winget install Google.CloudSDK
# Reiniciar terminal
```

### Erro: "Projeto não encontrado"
Verifique se o projeto `algorise-producao` existe no Console GCP

### Erro: "Billing não ativado"
Configure billing no Console GCP (necessário mesmo no free tier)

### Aplicação não carrega
```bash
# Ver logs de erro
gcloud app logs tail -s default
```

## 🎯 PRÓXIMOS PASSOS APÓS DEPLOY

1. **✅ Alterar senha do admin** (primeiro login)
2. **✅ Configurar dados da empresa** (menu Configurações)
3. **✅ Testar funcionalidade CATMAT**
4. **✅ Importar fornecedores** (se necessário)
5. **✅ Configurar domínio personalizado** (opcional)

---

## 🚀 EXECUTAR AGORA

**Comando único para começar:**
```powershell
.\configurar-gcp-automatico.ps1
```

**Tempo estimado:** 10-20 minutos (dependendo da velocidade da internet)

**Resultado:** Algorise funcionando 100% no Google Cloud! 🎉