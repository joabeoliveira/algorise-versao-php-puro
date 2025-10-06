# 🔍 Algorise AI - Versão PHP Puro

> Sistema de automação de processos de cotação, análise de preços e gestão de fornecedores para compras públicas e privadas.

## 📋 Sobre o Projeto

O **Algorise AI** é um Micro SaaS desenvolvido para automatizar e otimizar processos relacionados à:

- 🚀 Cotações rápidas e públicas
- 📊 Gestão centralizada de processos, itens e fornecedores  
- 💰 Análise comparativa de preços
- 📄 Geração automatizada de relatórios e notas técnicas em PDF
- 🔐 Sistema completo de autenticação e gerenciamento de usuários

## 🚀 Funcionalidades Principais

### 🎯 **Módulos do Sistema**
- **Dashboard**: Visão geral e métricas do sistema
- **Cotação Rápida**: Formulários para solicitação ágil de cotações
- **Gestão de Fornecedores**: CRUD completo com importação em lote
- **Gestão de Itens e Processos**: Controle de produtos/serviços
- **Análise de Preços**: Comparação inteligente entre fornecedores
- **Relatórios**: Geração de documentos em conformidade com normas
- **Interface Pública**: Portal para submissão externa de cotações

### 📐 **Conformidade com Normas**
- ✅ Integração com APIs do Governo (Painel de Preços)
- ✅ Conformidade com IN 65/2021 (Instrução Normativa)
- ✅ Geração automática de Notas Técnicas (Padrão AGU)
- ✅ Validação de prazos e documentação

## 🛠️ Tecnologias Utilizadas

### **Backend**
- 🐘 **PHP 8.2+** - Linguagem principal
- ⚡ **Slim Framework 4.14+** - Microframework para APIs
- 🗄️ **MySQL 8.0** - Banco de dados relacional
- 📦 **Composer** - Gerenciador de dependências

### **Frontend**
- 🎨 **Bootstrap 5.3+** - Framework CSS responsivo
- ⚡ **JavaScript Vanilla** - Interatividade nativa
- 🎯 **Alpine.js** - Reatividade leve

### **Infraestrutura**
- 🐳 **Docker & Docker Compose** - Containerização
- 🌐 **Nginx** - Servidor web
- 🔄 **PHP-FPM** - Processamento PHP otimizado

### **Bibliotecas Principais**
```json
{
  "slim/slim": "^4.14",
  "guzzlehttp/guzzle": "^7.9",
  "phpmailer/phpmailer": "^6.10", 
  "phpoffice/phpspreadsheet": "^4.3",
  "dompdf/dompdf": "^3.1",
  "vlucas/phpdotenv": "^5.6"
}
```

## 📦 Instalação e Configuração

### **Pré-requisitos**
- Docker & Docker Compose
- Git
- PHP 8.0+ (opcional, para desenvolvimento local)
- Composer (opcional, para desenvolvimento local)

### **1. Clone o repositório**
```bash
git clone https://github.com/SEU_USUARIO/buscaprecos-main.git
cd buscaprecos-main
```

### **2. Configure as variáveis de ambiente**
```bash
cp .env.example .env
# Edite o arquivo .env com suas configurações
```

### **3. Inicie o ambiente com Docker**
```bash
# Desenvolvimento
docker-compose -f docker-compose.dev.yml up -d

# Produção  
docker-compose up -d
```

### **4. Restaure o banco de dados**
```bash
docker cp backup_saas.sql buscaprecos-main-db-1:/backup_saas.sql
docker exec buscaprecos-main-db-1 mysql -u root -p[SUA_SENHA] buscaprecos -e "source /backup_saas.sql"
```

### **5. Acesse a aplicação**
- **Desenvolvimento**: http://localhost:8080
- **Produção**: Configurado via Traefik

## 🔐 Credenciais Padrão

### **Banco de Dados**
- **Host**: db (interno) / localhost:3306 (externo)
- **Database**: buscaprecos
- **Usuário**: busca
- **Senha**: [configurar no .env]

### **Usuários do Sistema**
- **Admin**: Definido durante a instalação
- **Usuário**: Criado via interface administrativa

## 🚀 Comandos Úteis

### **Docker**
```bash
# Iniciar containers
docker-compose -f docker-compose.dev.yml up -d

# Parar containers
docker-compose -f docker-compose.dev.yml down

# Ver logs
docker-compose -f docker-compose.dev.yml logs -f

# Acessar container PHP
docker exec -it buscaprecos-main-app-1 sh

# Backup do banco
docker exec db_db.1.fsj9ro7t25vgne46puzabtfxy mysqldump -u root -pbusca_password  buscaprecos > backup_$(date +%Y%m%d).sql
```

volume atual do db 	db_db.1.fsj9ro7t25vgne46puzabtfxy
nome banco de dados buscaprecos

senha root db root_password_123


### **Composer (no container)**
```bash
docker exec buscaprecos-main-app-1 composer install
docker exec buscaprecos-main-app-1 composer update
```

## 🏗️ Arquitetura do Sistema

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Nginx         │    │   PHP-FPM        │    │   MySQL         │
│   (Web Server)  │◄──►│   (Application)  │◄──►│   (Database)    │
│   Port: 8080    │    │   Port: 9000     │    │   Port: 3306    │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         ▲                        ▲                        ▲
         │                        │                        │
    ┌─────────┐              ┌─────────┐              ┌─────────┐
    │ Static  │              │ Slim    │              │ PDO     │
    │ Assets  │              │ Router  │              │ MySQL   │
    └─────────┘              └─────────┘              └─────────┘
```

## 📁 Estrutura de Diretórios

```
buscaprecos-main/
├── 📁 docker/              # Configurações Docker
│   ├── nginx/             # Configuração Nginx
│   └── php/               # Dockerfile PHP
├── 📁 public/             # Ponto de entrada web
│   ├── css/              # Estilos CSS
│   ├── js/               # Scripts JavaScript
│   └── index.php         # Front Controller
├── 📁 src/               # Código fonte da aplicação
│   ├── Controller/       # Controladores MVC
│   ├── View/            # Templates PHP
│   └── settings.php     # Configurações Slim
├── 📄 composer.json      # Dependências PHP
├── 📄 docker-compose.yml # Orquestração produção
├── 📄 docker-compose.dev.yml # Ambiente desenvolvimento  
└── 📄 backup_saas.sql   # Dump inicial do banco
```

## 🤝 Contribuindo

1. Faça o fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença [MIT](LICENSE).

## 👨‍💻 Desenvolvedor

**Joabe Oliveira**
- Email: joabeantonio@gmail.com / joabeoliveiradev@gmail.com
- LinkedIn: [Joabe Oliveira](https://linkedin.com/in/joabe-oliveira)

## 🆘 Suporte

Para suporte, envie um email para joabeantonio@gmail.com ou abra uma issue no GitHub.

---

<div align="center">
  <p>Feito com ❤️ para otimizar processos de compras públicas</p>
  <p>⭐ Deixe uma estrela se este projeto te ajudou!</p>
</div>
=======
# Algorise AI

Micro SaaS para automação de processos de cotação, análise de preços e gestão de fornecedores.

## 🚀 Acesso ao Sistema

### Ambiente de Produção
- **Aplicação:** https://algorise.com.br
- **Portainer:** https://portainer.algorise.com.br
  - Usuário: `algoadmin`
  - Senha: `dsfkjh3h2j%21DW`

## 🛠️ Desenvolvimento

### Iniciar ambiente de desenvolvimento
```bash
docker-compose -f docker-compose.dev.yml up -d
```

### Parar ambiente de desenvolvimento
```bash
docker-compose -f docker-compose.dev.yml down
```

### Debug com dados de produção
```bash
docker-compose -f docker-compose.production-debug.yml up -d
```

## 📋 Tecnologias
- PHP 8.2
- Slim Framework 4
- MySQL 8.0
- Docker & Docker Compose
- Nginx
- Traefik (produção)

## 🔧 Deploy
1. Faça suas alterações localmente
2. Commit e push para GitHub
3. Acesse o Portainer em https://portainer.algorise.com.br
4. Atualize os stacks conforme necessário

## ✅ Migração PHP Puro Concluída

Este projeto foi migrado do Slim Framework para **PHP puro**, mantendo todas as funcionalidades originais:

- ✅ Sistema de roteamento customizado
- ✅ Middleware de autenticação
- ✅ Geração de PDFs com notas técnicas
- ✅ Sistema de email SMTP
- ✅ Chatbot integrado
- ✅ Interface responsiva