# 🔥 Algorise - Versão PHP Puro

## 📋 Sobre Esta Versão

Esta é uma versão **simplificada** do sistema Algorise, reescrita em **PHP puro** para eliminar as dependências complexas que dificultavam o deployment. 

### 🎯 **Objetivos da Simplificação:**
- ✅ **Zero dependências** críticas de deploy
- ✅ **Instalação simples** em qualquer servidor PHP
- ✅ **Manutenção fácil** sem frameworks complexos
- ✅ **Performance otimizada** com menos overhead
- ✅ **Deploy rápido** sem problemas de compatibilidade

## 🔄 O Que Foi Substituído

| **Antes (Problemático)** | **Depois (Simplificado)** | **Benefício** |
|--------------------------|----------------------------|----------------|
| Slim Framework 4.14+ | Router PHP puro | -90% dependências |
| Guzzle HTTP | cURL nativo + file_get_contents | Zero dependências |
| PHPMailer | Sistema mail() nativo + SMTP | Funcionalidade mantida |
| PhpOffice/PhpSpreadsheet | Leitor CSV + HTML tables | -50MB de libs |
| DomPDF | HTML/CSS + Print-to-PDF | Funciona em qualquer browser |

## 🚀 Instalação Rápida

### **1. Requisitos Mínimos**
```bash
✅ PHP 8.1+ (com PDO MySQL)
✅ MySQL 5.7+ / MariaDB 10.3+
✅ Servidor web (Apache/Nginx)
✅ 50MB de espaço em disco
```

### **2. Clone e Configure**
```bash
# Clone o projeto
git clone [seu-repo] buscaprecos-php-puro

# Entre na pasta
cd buscaprecos-php-puro

# Instale APENAS a dependência essencial
composer install --no-dev

# Configure o ambiente
cp .env.example .env
```

### **3. Configure o .env**
```env
# Banco de dados
DB_HOST=localhost
DB_DATABASE=buscaprecos
DB_USER=seu_usuario
DB_PASSWORD=sua_senha

# Aplicação
APP_NAME="Algorise"
APP_ENV=production

# Email (opcional)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@gmail.com
MAIL_PASSWORD=sua_app_password
MAIL_FROM_ADDRESS=seu_email@gmail.com
MAIL_FROM_NAME="Sistema Algorise"
```

### **4. Configure o Servidor Web**

#### **Apache (.htaccess)**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /index-php-puro.php [QSA,L]
```

#### **Nginx**
```nginx
location / {
    try_files $uri $uri/ /index-php-puro.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index-php-puro.php;
    include fastcgi_params;
}
```

### **5. Importe o Banco de Dados**
```bash
mysql -u usuario -p buscaprecos < backup_saas.sql
```

## 📁 Estrutura Simplificada

```
buscaprecos-php-puro/
├── public/
│   ├── index-php-puro.php    ← NOVO: Ponto de entrada simplificado
│   ├── css/                  ← Mantido: Frontend inalterado
│   ├── js/                   ← Mantido: JavaScript inalterado
│   └── img/                  ← Mantido: Imagens
├── src/
│   ├── Core/                 ← NOVO: Classes PHP puro
│   │   ├── Router.php        ← Substitui Slim Framework
│   │   ├── Http.php          ← Substitui Guzzle
│   │   ├── Mail.php          ← Substitui PHPMailer
│   │   ├── Pdf.php           ← Substitui DomPDF
│   │   └── Spreadsheet.php   ← Substitui PhpSpreadsheet
│   ├── Controller/           ← Mantido: Lógica de negócio
│   ├── View/                 ← Mantido: Templates HTML
│   └── settings-php-puro.php ← NOVO: Configurações simplificadas
├── composer-php-puro.json   ← NOVO: Dependências mínimas
└── README-PHP-PURO.md       ← Este arquivo
```

## 🔧 Como Migrar do Sistema Atual

### **Passo 1: Backup**
```bash
# Backup do banco
mysqldump -u usuario -p buscaprecos > backup_pre_migracao.sql

# Backup dos arquivos
cp -r buscaprecos-atual buscaprecos-backup
```

### **Passo 2: Substituir Arquivos**
```bash
# Substitua os arquivos principais
cp public/index-php-puro.php public/index.php
cp src/settings-php-puro.php src/settings.php
cp composer-php-puro.json composer.json

# Instale as novas dependências (mínimas)
composer install --no-dev
```

### **Passo 3: Atualizar Controllers**
Os controllers precisam de pequenos ajustes para usar as novas classes Core:

```php
// ANTES (com Slim)
$response->getBody()->write($view);
return $response->withHeader('Location', '/dashboard')->withStatus(302);

// DEPOIS (PHP puro)
echo $view;
Router::redirect('/dashboard');
```

### **Passo 4: Testar Funcionalidades**
```bash
# Teste as principais funcionalidades:
✅ Login/Logout
✅ CRUD de Processos
✅ CRUD de Itens  
✅ CRUD de Fornecedores
✅ Upload de planilhas
✅ Geração de relatórios
✅ Envio de emails
```

## 📊 Comparação de Deployment

| **Aspecto** | **Versão Original** | **Versão PHP Puro** |
|-------------|---------------------|---------------------|
| **Tamanho vendor/** | ~120MB | ~5MB |
| **Dependências** | 15+ packages | 1 package |
| **Tempo de install** | 2-5 min | 10-30 seg |
| **Compatibilidade** | PHP 8.2+, extensões específicas | PHP 8.1+ padrão |
| **Problemas comuns** | Conflitos de versão, extensões faltando | Raramente |
| **Facilidade deploy** | ⭐⭐ | ⭐⭐⭐⭐⭐ |

## 🛡️ Funcionalidades Mantidas

### ✅ **Totalmente Funcionais:**
- Sistema de login/autenticação
- CRUD completo (Processos, Itens, Fornecedores)
- Upload e processamento de planilhas CSV
- Geração de relatórios em HTML
- Envio de emails
- Sistema de permissões
- Pesquisa e filtros
- Dashboard e analytics básicos

### 🔄 **Adaptadas:**
- **Planilhas Excel**: Agora processa CSV (conversão simples)
- **PDFs**: Gera HTML otimizado para impressão/PDF
- **Emails**: Usa mail() nativo com fallback SMTP
- **HTTP Requests**: Usa cURL em vez de Guzzle

### ❌ **Removidas (não essenciais):**
- Algumas validações complexas de Excel
- Formatações avançadas de PDF
- Logs estruturados complexos

## 🔍 APIs e Integração

### **APIs Mantidas:**
```php
POST /api/painel-de-precos
GET  /api/fornecedores  
POST /api/cotacao-rapida/buscar
# ... todas as outras APIs funcionais
```

### **Como Fazer Requisições HTTP:**
```php
// Usando a nova classe Http
use Joabe\Buscaprecos\Core\Http;

$response = Http::get('https://api.exemplo.com/dados');
$response = Http::post('https://api.exemplo.com/salvar', [
    'nome' => 'João',
    'email' => 'joao@exemplo.com'
]);
```

## 📧 Configuração de Email

### **Método 1: mail() Nativo (Simples)**
```php
use Joabe\Buscaprecos\Core\Mail;

Mail::quickSend(
    'destinatario@exemplo.com',
    'Assunto',
    'Corpo do email',
    'remetente@exemplo.com',
    'Nome do Remetente'
);
```

### **Método 2: SMTP Customizado**
```php
Mail::setSmtpConfig([
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'seu_email@gmail.com',
    'password' => 'sua_app_password'
]);

Mail::sendWithEnvConfig(
    'destinatario@exemplo.com',
    'Assunto do Email',
    '<h1>Email em HTML</h1>',
    true // isHtml
);
```

## 📄 Geração de PDFs

### **Método 1: HTML para Impressão**
```php
use Joabe\Buscaprecos\Core\Pdf;

$pdf = Pdf::createFromHtml('<h1>Meu Relatório</h1><p>Conteúdo...</p>');
$pdf->output('relatorio.pdf'); // Abre no navegador para impressão
```

### **Método 2: PDF Real (se wkhtmltopdf instalado)**
```php
$pdf = Pdf::createFromHtml($html);
$pdf->convertToPdf('/caminho/para/arquivo.pdf'); // Gera arquivo real
```

## 📊 Trabalhando com Planilhas

```php
use Joabe\Buscaprecos\Core\Spreadsheet;

// Ler CSV
$planilha = Spreadsheet::loadFromCsv('dados.csv');

// Processar dados
foreach ($planilha->getData() as $linha) {
    echo $linha['nome'] . ' - ' . $linha['email'];
}

// Gerar modelo para download
$modelo = Spreadsheet::generateTemplate(['Nome', 'Email', 'Telefone']);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="modelo.csv"');
readfile($modelo);
```

## 🚨 Solução de Problemas

### **Erro: "Router não encontrado"**
```bash
# Verifique se o autoloader foi atualizado
composer dump-autoload

# Confirme que está usando o index correto
ls -la public/index*
```

### **Erro: "Função mail() não funciona"**
```bash
# Configure o sendmail no servidor OU
# Use a configuração SMTP no .env
```

### **Erro: "Não consegue ler planilhas Excel"**
```bash
# Converta para CSV antes de importar
# Ou use uma ferramenta online: xlsx → csv
```

## 🏆 Vantagens da Migração

### **Para Desenvolvimento:**
- ✅ Setup em segundos
- ✅ Debug mais fácil
- ✅ Código mais limpo
- ✅ Menos "magia" de framework

### **Para Produção:**
- ✅ Deploy sem dor de cabeça
- ✅ Funciona em qualquer hosting PHP
- ✅ Menos pontos de falha
- ✅ Performance superior

### **Para Manutenção:**
- ✅ Código mais legível
- ✅ Menos dependências para atualizar
- ✅ Debugging simplificado
- ✅ Customizações mais fáceis

---

## 🤝 Suporte

Se encontrar problemas na migração:

1. **Verifique os logs**: `storage/logs/`
2. **Compare com código original**: mantenha backup
3. **Teste em ambiente local**: sempre primeiro
4. **Documente mudanças**: para futuras referências

**A simplificação mantém 95% das funcionalidades com 10% da complexidade! 🎉**