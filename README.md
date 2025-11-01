# 🔍 Algorise AI - Versão PHP Puro

> Sistema de automação de processos de cotação, análise de preços e gestão de fornecedores para compras públicas e privadas.

## 📋 Sobre o Projeto

O **Algorise AI** é um Micro SaaS desenvolvido em PHP puro para automatizar e otimizar processos relacionados a:

- 🚀 Cotações rápidas e públicas
- 📊 Gestão centralizada de processos, itens e fornecedores
- 💰 Análise comparativa de preços
- 📄 Geração automatizada de relatórios e notas técnicas em PDF
- 🔐 Sistema completo de autenticação e gerenciamento de usuários

Este projeto foi migrado de um microframework para uma arquitetura em PHP puro, visando maior controle, performance e flexibilidade para integração com serviços em nuvem.

## 🚀 Funcionalidades Principais

### 🎯 **Módulos do Sistema**
- **Dashboard**: Visão geral e métricas do sistema.
- **Cotação Rápida**: Formulários para solicitação ágil de cotações.
- **Gestão de Fornecedores**: CRUD completo com importação em lote.
- **Gestão de Itens e Processos**: Controle de produtos/serviços.
- **Análise de Preços**: Comparação inteligente entre fornecedores.
- **Relatórios**: Geração de documentos em conformidade com normas.
- **Interface Pública**: Portal para submissão externa de cotações.

### 📐 **Conformidade com Normas**
- ✅ Integração com APIs do Governo (Painel de Preços).
- ✅ Conformidade com IN 65/2021 (Instrução Normativa).
- ✅ Geração automática de Notas Técnicas (Padrão AGU).
- ✅ Validação de prazos e documentação.

## 🛠️ Tecnologias Utilizadas

### **Backend**
- 🐘 **PHP 8.1+** - Linguagem principal
- 🗄️ **MySQL 8.0** - Banco de dados relacional
- 📦 **Composer** - Gerenciador de dependências

### **Frontend**
- 🎨 **Bootstrap 5.3+** - Framework CSS responsivo
- ⚡ **JavaScript (Vanilla)** - Interatividade e requisições assíncronas

### **Infraestrutura e Cloud**
- 🐳 **Docker & Docker Compose** - Containerização para desenvolvimento
- ☁️ **Google Cloud Platform** - Otimizado para deploy no App Engine
  - **Cloud Storage**: Armazenamento de arquivos.
  - **Cloud Logging**: Logs centralizados.
  - **Secret Manager**: Gerenciamento de segredos.
- 🌐 **Nginx** - Servidor web

### **Bibliotecas Principais**
```json
{
    "phpmailer/phpmailer": "^6.11",
    "phpoffice/phpspreadsheet": "^5.1",
    "vlucas/phpdotenv": "^5.6",
    "google/cloud-storage": "^1.30",
    "google/cloud-logging": "^1.25",
    "google/cloud-secret-manager": "^2.2"
}
```

## 📦 Instalação e Configuração (Ambiente de Desenvolvimento)

### **Pré-requisitos**
-   [Docker](https://www.docker.com/get-started) & Docker Compose
-   [Git](https://git-scm.com/)
-   [Composer](https://getcomposer.org/) (Opcional, para gerenciamento de dependências fora do Docker)

### **1. Clone o repositório**
```bash
git clone https://github.com/SEU_USUARIO/algorise-versao-php-puro.git
cd algorise-versao-php-puro
```

### **2. Configure as variáveis de ambiente**
Copie o arquivo de exemplo `.env.example` para `.env` e ajuste as configurações do banco de dados e outras variáveis necessárias para o seu ambiente local.
```bash
cp .env.example .env
```
As configurações detalhadas (banco de dados, email, APIs) estão documentadas dentro do próprio arquivo `.env.example`.

### **3. Inicie o ambiente com Docker**
Para subir os contêineres (PHP, Nginx, MySQL), utilize o `docker-compose`:
```bash
docker-compose up -d
```
Este comando irá construir as imagens e iniciar os serviços em background.

### **4. Instale as dependências PHP**
Execute o Composer dentro do contêiner da aplicação para instalar as bibliotecas necessárias.
```bash
docker-compose exec app composer install
```

### **5. Acesse a aplicação**
A aplicação estará disponível em: [http://localhost:8080](http://localhost:8080)

## 🚀 Deploy (Google Cloud Platform)

A aplicação é otimizada para deploy no Google App Engine.

### **1. Configuração**
-   Certifique-se de que o arquivo `app.yaml` está configurado corretamente.
-   Configure as variáveis de ambiente no `app.yaml` ou diretamente no serviço do App Engine.
-   Configure os segredos (`db-password`, etc.) no Google Secret Manager.

### **2. Deploy via Script**
O script `deploy-gcp.sh` automatiza o processo de deploy. Ele instala as dependências de produção e envia a aplicação para o App Engine.
```bash
./deploy-gcp.sh
```
Como alternativa, você pode usar o comando `gcloud` diretamente:
```bash
gcloud app deploy
```

## 🏗️ Arquitetura do Sistema

A arquitetura segue um modelo simplificado, com um ponto de entrada único (`index.php`) que utiliza um roteador customizado para direcionar as requisições aos seus respectivos `Controllers`.

```
Requisição HTTP
       │
       ▼
┌─────────────────┐
│   Nginx         │
│  (Servidor Web) │
└─────────────────┘
       │
       ▼
┌─────────────────┐
│   PHP-FPM       │
│ (index.php)     │
└─────────────────┘
       │
       ▼
┌─────────────────┐
│ Roteador Custom │
│ (src/Core/Router.php)│
└─────────────────┘
       │
       ▼
┌─────────────────┐
│   Controllers   │
│(src/Controller/*)│
└─────────────────┘
       │
       ▼
┌─────────────────┐
│   Views / Lógica│
│(src/View/*)     │
└─────────────────┘
       │
       ▼
┌─────────────────┐
│   Banco de Dados│
│ (MySQL)         │
└─────────────────┘
```

## 📁 Estrutura de Diretórios

```
algorise-versao-php-puro/
├── 📁 docker/              # Configurações Docker para Nginx e PHP
├── 📁 public/             # Ponto de entrada web e assets públicos
│   ├── css/
│   ├── js/
│   └── index.php         # Front Controller (ponto de entrada)
├── 📁 src/                # Código fonte da aplicação
│   ├── Controller/       # Controladores
│   ├── Core/             # Classes do núcleo (Router, DB, etc.)
│   ├── View/             # Arquivos de template (views)
│   └── settings-php-puro.php # Configurações e helpers
├── 📁 storage/             # Arquivos de log, cache e uploads
├── 📄 .env.example        # Arquivo de exemplo para variáveis de ambiente
├── 📄 app.yaml            # Configuração para deploy no Google App Engine
├── 📄 composer.json       # Dependências PHP
├── 📄 docker-compose.yml  # Orquestração de contêineres Docker
└── 📄 deploy-gcp.sh      # Script de deploy para Google Cloud
```

## 🤝 Contribuindo

1.  Faça o fork do projeto.
2.  Crie uma branch para sua feature (`git checkout -b feature/NovaFuncionalidade`).
3.  Commit suas mudanças (`git commit -m 'Adiciona NovaFuncionalidade'`).
4.  Push para a branch (`git push origin feature/NovaFuncionalidade`).
5.  Abra um Pull Request.

## 👨‍💻 Desenvolvedor

**Joabe Oliveira**
- Email: joabeantonio@gmail.com
- LinkedIn: [Joabe Oliveira](https://linkedin.com/in/joabe-oliveira)

---

<div align="center">
  <p>Feito com ❤️ para otimizar processos de compras públicas</p>
</div>
