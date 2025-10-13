# 🚀 Algorise - Deploy Guide para Google Cloud Platform

## 🎯 Ambiente Híbrido Implementado

Este projeto agora funciona **automaticamente** tanto no desenvolvimento local (XAMPP) quanto no Google Cloud Platform, usando o mesmo código-fonte!

### ✅ O que foi implementado:

- **🔍 Detecção automática de ambiente** (local vs GCP)
- **🗄️ Conexão híbrida de banco** (MySQL local + Cloud SQL)
- **📝 Sistema de logs adaptativo** (arquivos locais + Cloud Logging)
- **📁 Storage inteligente** (pasta local + Cloud Storage)
- **🔒 Headers de segurança condicionais**
- **⚙️ Scripts de deploy automatizados**

## 🛠️ Desenvolvimento Local (XAMPP)

### Comandos para desenvolvimento:
```bash
# Instalar dependências
composer run dev:install

# Iniciar servidor local
composer run dev:serve
# ou simplesmente:
composer run dev

# Testar ambiente
composer run test:env
```

### Como funciona localmente:
- ✅ Usa MySQL do XAMPP (`localhost:3306`)
- ✅ Salva logs em `storage/logs/`
- ✅ Uploads para `public/uploads/`
- ✅ Debug habilitado
- ✅ Sem headers HTTPS obrigatórios

## ☁️ Deploy para Google Cloud

### 1. Pré-requisitos

#### Instalar Google Cloud CLI:
```bash
# Windows
# Baixe em: https://cloud.google.com/sdk/docs/install

# Verificar instalação
gcloud --version
```

#### Configurar projeto:
```bash
# Login
gcloud auth login

# Configurar projeto
gcloud config set project SEU-PROJETO-ID

# Habilitar APIs necessárias
gcloud services enable appengine.googleapis.com
gcloud services enable sqladmin.googleapis.com
gcloud services enable storage.googleapis.com
```

### 2. Configurar Cloud SQL

```bash
# Criar instância MySQL
gcloud sql instances create algorise-db \
    --database-version=MYSQL_8_0 \
    --tier=db-f1-micro \
    --region=us-central1

# Criar banco de dados
gcloud sql databases create algorise --instance=algorise-db

# Criar usuário
gcloud sql users create algorise-user \
    --instance=algorise-db \
    --password=SUA-SENHA-SEGURA
```

### 3. Configurar Secrets

```bash
# Senha do banco
echo "sua-senha-segura" | gcloud secrets create db-password --data-file=-

# Chave do Supabase
echo "sua-chave-supabase" | gcloud secrets create supabase-anon-key --data-file=-

# Credenciais de email
echo "seu-email@gmail.com" | gcloud secrets create mail-username --data-file=-
echo "sua-senha-app" | gcloud secrets create mail-password --data-file=-

# Dar permissão ao App Engine
gcloud secrets add-iam-policy-binding db-password \
    --member="serviceAccount:SEU-PROJETO@appspot.gserviceaccount.com" \
    --role="roles/secretmanager.secretAccessor"
```

### 4. Configurar app.yaml

Edite o arquivo `app.yaml` e substitua:
```yaml
# Substitua esta linha:
CLOUD_SQL_CONNECTION_NAME: "your-project-id:us-central1:algorise-db"

# Por sua configuração real:
CLOUD_SQL_CONNECTION_NAME: "SEU-PROJETO:us-central1:algorise-db"
```

### 5. Deploy Automático

```bash
# Deploy com script automático
chmod +x deploy-gcp.sh
./deploy-gcp.sh

# OU deploy manual
composer run prod:deploy
```

### 6. Deploy Manual (passo a passo)

```bash
# 1. Build de produção
composer run prod:build

# 2. Deploy
gcloud app deploy app.yaml --promote

# 3. Restaurar dependências de dev
composer install
```

## 🔧 Como Funciona o Ambiente Híbrido

### Detecção Automática:
```php
// O sistema detecta automaticamente onde está rodando:
if (isProduction()) {
    // Produção: Cloud SQL, Cloud Storage, Cloud Logging
} else {
    // Desenvolvimento: MySQL local, pasta local, arquivo de log
}
```

### Banco de Dados:
- **Local**: `mysql:host=localhost;port=3306`
- **GCP**: `mysql:unix_socket=/cloudsql/projeto:regiao:instancia`

### Storage:
- **Local**: `storage/uploads/`
- **GCP**: `gs://bucket-name/uploads/`

### Logs:
- **Local**: `storage/logs/app-YYYY-MM-DD.log`
- **GCP**: Google Cloud Logging

## 📊 Comandos Úteis

### Desenvolvimento:
```bash
composer run dev              # Servidor local
composer run dev:install     # Instalar + configurar .env
composer run test:env        # Testar detecção de ambiente
```

### Produção:
```bash
composer run prod:build      # Build de produção
composer run prod:deploy     # Build + Deploy
gcloud app logs tail -s default  # Ver logs em tempo real
```

### Google Cloud:
```bash
gcloud app browse            # Abrir app no navegador
gcloud app logs tail         # Ver logs
gcloud app versions list     # Listar versões
gcloud sql connect algorise-db  # Conectar ao banco
```

## 🔍 Monitoramento

### URLs importantes:
- **App**: `https://SEU-PROJETO.uc.r.appspot.com`
- **Console**: `https://console.cloud.google.com/appengine`
- **Logs**: `https://console.cloud.google.com/logs`
- **SQL**: `https://console.cloud.google.com/sql`

### Logs de Debug:
```bash
# Ver logs da aplicação
gcloud app logs tail -s default

# Ver logs do banco
gcloud sql operations list --instance=algorise-db

# Ver métricas
gcloud app services describe default
```

## 🛡️ Segurança

### Produção:
- ✅ HTTPS obrigatório
- ✅ Headers de segurança
- ✅ Secrets no Secret Manager
- ✅ Conexão segura ao banco
- ✅ Logs centralizados

### Desenvolvimento:
- ✅ Debug habilitado
- ✅ Logs detalhados
- ✅ Sem HTTPS obrigatório
- ✅ Configurações flexíveis

## 💰 Custos Estimados (GCP)

- **App Engine**: ~$10-30/mês (tráfego baixo)
- **Cloud SQL**: ~$7-25/mês (db-f1-micro)
- **Cloud Storage**: ~$1-5/mês
- **Cloud Logging**: ~$0.50/GB

**Total**: ~$20-60/mês dependendo do uso

## ❓ Troubleshooting

### Erro de conexão com banco:
```bash
# Verificar se Cloud SQL permite conexões
gcloud sql instances describe algorise-db

# Testar conexão
gcloud sql connect algorise-db --user=algorise-user
```

### Logs não aparecem:
```bash
# Verificar permissões
gcloud projects add-iam-policy-binding SEU-PROJETO \
    --member="serviceAccount:SEU-PROJETO@appspot.gserviceaccount.com" \
    --role="roles/logging.logWriter"
```

### Deploy falha:
```bash
# Verificar se APIs estão habilitadas
gcloud services list --enabled

# Ver detalhes do erro
gcloud app logs tail -s default
```

## 🎉 Resultado Final

**✅ MESMO CÓDIGO funciona em ambos ambientes!**

- 🖥️ **Desenvolvimento**: Continue usando XAMPP normalmente
- ☁️ **Produção**: Deploy automático no Google Cloud
- 🔄 **Git**: Um só repositório para tudo
- 🐛 **Debug**: Local com logs detalhados
- 🚀 **Performance**: Produção otimizada e segura

**Você pode desenvolver localmente e fazer deploy para produção sem alterar nenhum código!** 🎯