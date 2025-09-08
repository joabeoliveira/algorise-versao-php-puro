Projeto Busca Preços (BuscaPrecos AI)
Arquitetura e Funcionalidades Essenciais
Para garantir que o sistema atenda a todas as exigências, a ideia é modularizá-lo. Ou seja cada parte da IN e do relatório da AGU como um módulo ou uma funcionalidade tipo SaaS.
Módulo 1: Gestão de Processos e Itens
Esta é a entidade principal, o contêiner para cada nova aquisição. Ao criar um "Novo Processo", o usuário preencherá os seguintes campos:

1. Entidade: Processo de Contratação
Número do Processo:
Tipo: Campo de texto livre.
Regra: O sistema garantirá que este número seja único na plataforma, evitando duplicidade.
Finalidade: Permitir a identificação exata do processo administrativo correspondente no órgão (ex: 08020.001234/2025-11).

Nome do Processo:
Tipo: Campo de texto.
Finalidade: Um título amigável para o processo, geralmente o objeto principal (ex: "Aquisição de Material de expediente para o órgão XXX").

Tipo de Contratação:
Tipo: Menu de seleção (Dropdown).
Opções: Pregão Eletrônico, Dispensa de Licitação , Inexigibilidade , Compra Direta (Pequeno Valor).
Finalidade: Essencial para aplicar regras específicas de pesquisa de preços que a IN 65/2021 determina para cada modalidade.

Agente(s) Responsável(is):
Tipo: Campos de texto para "Nome Completo", "Matrícula", órgão, código UASG e região (Estado do órgão). O sistema poderá permitir adicionar mais de um agente.
Finalidade: Atender à exigência de identificação do responsável pela pesquisa.

Status do Processo:
Tipo: Menu de seleção (Dropdown).
Opções Padrão: Em Elaboração, Pesquisa em Andamento, Finalizado, Cancelado.
Finalidade: Organizar o fluxo de trabalho e permitir que o usuário tenha uma visão clara do andamento de todas as suas pesquisas.

2. Entidade: Lote/Agrupamento
Para atender à necessidade de agrupar itens, o sistema terá uma entidade intermediária. Um lote pertence a um processo.

Número do Lote:
Tipo: Campo numérico sequencial (Lote 1, Lote 2, ...).
Nome do Lote (Opcional):
Tipo: Campo de texto (ex: "Equipamentos de TI", "Material de Copa e Cozinha").

3. Entidade: Item de Contratação
Esta entidade representa cada produto ou serviço a ser cotado. Um item sempre pertencerá a um Lote dentro de um Processo.

Número do Item:
Tipo: Campo numérico sequencial.

Código CATMAT/CATSER:
Tipo: Campo de texto/número.
Finalidade: Código padronizado do catálogo de materiais ou serviços do governo federal.

Descrição Detalhada do Item:
Tipo: Área de texto ampla (rich text).
Finalidade: Descrever o objeto a ser contratado com todas as suas especificações, atendendo ao Art. 3º, I, da IN 65/2021.

Unidade de Medida:
Tipo: Campo de texto ou menu de seleção (ex: Unidade, Caixa, Pacote, Hora, Mês).

Quantidade Estimada:
Tipo: Campo numérico.
Fluxo de Tela (User Journey)
O usuário acessa o painel principal e vê uma lista de todos os seus processos com colunas: "Número do Processo", "Nome", "Tipo", "Status".
Clica em "Criar Novo Processo".
Preenche o formulário do Processo e salva. O status inicial será "Em Elaboração".
O sistema o redireciona para a página de detalhes daquele processo. Nesta página, ele verá os dados do processo e uma área para gerenciar Lotes e Itens.
Ele clica em "Adicionar Lote", informa o número e nome, e salva.
Dentro do Lote recém-criado, ele clica em "Adicionar Item" e preenche os detalhes do produto/serviço.
Ele pode repetir o passo 6 para quantos itens forem necessários.


Módulo 2: Coleta de Preços (Parâmetros do Art. 5º)
Esta é a principal tela de entrada de dados. O sistema deve permitir que o usuário insira preços de múltiplas fontes, conforme o Art. 5º da IN 65/20213. Crie um formulário dinâmico onde o usuário possa adicionar quantas cotações encontrar para cada parâmetro.

Painel de Preços / Banco de Preços em Saúde (Inciso I):
Campos: Link de acesso, data e hora da consulta, valor da média encontrado.

Fluxo de Funcionamento:
Ao lado do item, o usuário clica no botão "Buscar no Painel de Preços".
O sistema utiliza o código CATMAT/CATSER do item para fazer uma chamada automática à API do Painel de Preços.
A resposta (JSON) é processada e exibida ao usuário em uma tabela clara, contendo as informações das contratações encontradas.
O usuário seleciona as cotações que julgar pertinentes e clica em "Adicionar ao Processo". O sistema salvará automaticamente o valor (priorizando a mediana, conforme a norma ), a data/hora da consulta e o link ou identificador da consulta para rastreabilidade.
Relação das URLs das APIs

URL para material últimas licitações

https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?pagina=1&tamanhoPagina=10&codigoItemCatalogo=423033&dataResultado=true


Resposta JSON
https://docs.google.com/document/d/14SoH9jZ3e5XxbNBtygae0wL48oOzO0OnO61I2lB3et8/edit?usp=sharing


URL para serviços ultimas licitações
https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/3_consultarServico?pagina=1&codigoItemCatalogo=5380&dataResultado=true 

Resposta em JSON
https://docs.google.com/document/d/1OXxUDqPdkZTTCgdWUAWgrfIAnFZWjJF3c-wAdHxih5M/edit?usp=sharing 


o que modifica é o codigoItemCatalogo 

pesquisa com estado e codigoUasg
https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?pagina=1&tamanhoPagina=10&codigoItemCatalogo=461828&codigoUasg=250057&estado=RJ&dataResultado=true 

sem uasg (Órgão Licitante)

https://dadosabertos.compras.gov.br/modulo-pesquisa-preco/1_consultarMaterial?pagina=1&tamanhoPagina=10&codigoItemCatalogo=461828&estado=RJ&dataResultado=true 


Contratações Similares da Adm. Pública (Inciso II):


Fluxo de Funcionamento:
O usuário clica no botão "Buscar Contratos Similares".
O sistema utiliza o código CATMAT/CATSER do item 
O sistema solicita ao usuário a sugestão de 3 códigos de UASG que ele conheça e queira utilizar como parâmetro da pesquisa similar da Adm ou caso não queira sugerir escolhe a opção de definir pelo sistema que deve escolher aleatoriamente entre as UASGs coletadas na região do órgão do usuário exemplo: RJ para consultar a API de contratações públicas com base na região ou sugestão do usuário.
Os resultados são exibidos, destacando contratações concluídas no último ano. O sistema pode sinalizar contratações mais antigas, que exigirão justificativa para uso.
O usuário seleciona os contratos relevantes e os adiciona à pesquisa. O sistema armazena os dados essenciais (órgão, data, valor, etc.).

Módulo exclusivo Licitacon
Caso seja necessário




Mídia Especializada / Sites de Domínio Amplo (Inciso III):


Fluxo de Funcionamento:
O usuário clica em "Pesquisar em Sites".
O sistema pode sugerir, em uma nova aba, uma busca no Google com a descrição do item.
Quando o usuário encontra um preço válido em um site de domínio amplo ou especializado, ele retorna ao SaaS e clica em "Adicionar Cotação de Site".
Abre-se um formulário para o usuário preencher os campos: URL da página, Nome do site, e Valor encontrado. O sistema preencherá automaticamente a data e hora do acesso e verificará se a consulta está no intervalo de até 6 meses exigido pela norma.


Pesquisa Direta com Fornecedores (Inciso IV):
Fluxo de Funcionamento:
Solicitação: O usuário clica em "Solicitar Cotação a Fornecedores". Ele seleciona os fornecedores de uma lista pré-cadastrada (um mini-cadastro de fornecedores será necessário).
Envio: O sistema gera um e-mail padrão com a descrição detalhada do item e as condições comerciais, pronto para ser enviado. O sistema registra a data do envio.
Gerenciamento: O sistema cria um painel de controle para aquela solicitação, mostrando: Fornecedor, Status (Aguardando Resposta, Respondido, Prazo Expirado), e uma contagem regressiva para o prazo de 5 dias.
Registro de Resposta: Ao receber uma proposta, o usuário altera o status para "Respondido", preenche um formulário com os dados da cotação e anexa o documento (PDF/imagem).
Não Resposta: Após 5 dias, o status muda para "Prazo Expirado". Esse registro é fundamental para justificar no relatório final quais fornecedores foram consultados, mas não enviaram propostas.

Base Nacional de Notas Fiscais Eletrônicas (Inciso V):


Fluxo de Funcionamento:
Como o acesso a esta base via API é restrito, o sistema oferecerá uma entrada manual.
O usuário clica em "Adicionar a partir de Nota Fiscal".
Ele preenche um formulário simples com: Fornecedor (CNPJ), Data da Nota Fiscal, e Valor. O sistema validará se a data da nota está dentro de 1 ano, conforme a norma.


Módulo 3: Motor de Cálculo e Análise (Art. 6º)


Após finalizar a coleta de preços para um processo, o usuário avança para uma nova tela, a "Mesa de Análise".

Passo 1: Visualização e Análise Crítica
Nesta tela, o usuário verá todos os preços coletados para o item em uma tabela interativa. O objetivo é facilitar a análise crítica exigida pela legislação.
Interface da Mesa de Análise:
Uma tabela listará cada preço com sua Fonte, Valor e Data.
Para ajudar na identificação de discrepâncias, o sistema exibirá automaticamente no topo da tela estatísticas simples do conjunto total de preços: Preço Mínimo, Preço Máximo, Média Simples e Mediana.
O sistema poderá destacar visualmente (ex: em amarelo) os preços que são muito distantes da mediana (outliers), auxiliando o gestor a identificá-los.

Passo 2: Curadoria da Cesta de Preços
Este é o momento de "limpar" a amostra, desconsiderando os preços que não são representativos do mercado.
Ação do Usuário:
Ao lado de cada preço na tabela, haverá um botão para alterar seu status de "Considerar" para "Desconsiderar".
Ao clicar em "Desconsiderar", o sistema exigirá o preenchimento de uma justificativa, explicando por que aquele preço é inexequível, inconsistente ou excessivamente elevado.
Feedback em Tempo Real:
A cada preço desconsiderado, a linha correspondente na tabela pode ficar cinza ou ser riscada.
As estatísticas no topo da tela (Média, Mediana, etc.) serão recalculadas instantaneamente, mostrando o impacto da curadoria na "cesta de preços" final.

Passo 3: Definição do Preço Estimado
Com a cesta de preços "limpa", o usuário definirá o valor de referência para a licitação.
Cálculos Finais: O sistema exibirá de forma clara os valores calculados a partir da cesta de preços final (após as desconsiderações):
Média da Cesta: R$ X.XXX,XX
Mediana da Cesta: R$ Y.YYY,YY
Menor Valor da Cesta: R$ Z.ZZZ,ZZ
Escolha da Metodologia: O usuário deverá selecionar, através de um botão de rádio, qual metodologia usará para definir o preço estimado:
( ) Usar a Média
( ) Usar a Mediana
( ) Usar o Menor Valor
cite_start Outro: (Permite ao usuário inserir um valor manualmente, exigindo justificativa)
Justificativa da Metodologia: Uma caixa de texto obrigatória aparecerá para que o usuário fundamente a escolha do método (ex: "Optou-se pela mediana por ser a medida que melhor representa a tendência central dos dados, mitigando o efeito de valores extremos...").

Passo 4: Validação e Regras Especiais
Antes de finalizar, o sistema realiza uma última verificação automática para garantir a conformidade com regras específicas da norma.
Alerta de Amostra Insuficiente: Se a cesta de preços final tiver menos de 3 preços, o sistema exibirá um alerta destacado, informando que a situação é excepcional e exigirá uma justificativa robusta do gestor responsável para ser validada, conforme o Art. 6º, § 5º da IN 65/2021.
Trava do Painel de Preços: Se a cesta final contiver apenas preços oriundos do Painel de Preços, o sistema verificará se o valor estimado definido pelo usuário é superior à mediana. Caso seja, o sistema impedirá o salvamento ou emitirá um forte alerta, informando que, neste caso específico, o valor não pode ser superior à mediana.
Ao final, o usuário clica em "Salvar Análise e Definir Preço". Todas as suas decisões, cálculos e justificativas são salvos no banco de dados, prontos para alimentar o relatório final.
Este módulo garante que o cálculo do preço estimado seja um processo transparente, justificável e à prova de auditorias. Se aprovado, podemos avançar para o Módulo 4: Geração de Relatórios.


Módulo 4: Geração do Relatório (Padrão AGU)
funcionará como um orquestrador, coletando todas as informações e decisões que você tomou nos módulos 1, 2 e 3 para preencher, de forma automática, o modelo de Nota Técnica exigido pelo Instrumento de Padronização da AGU.

Projeção do Módulo 4: Geração de Relatórios
O princípio é simples: após a conclusão da análise no Módulo 3, um botão "Gerar Relatório Final (Nota Técnica)" ficará disponível. Ao clicar, o sistema fará a "mágica" acontecer.
Veja como cada seção do relatório será preenchida:

I - OBJETO DA CONTRATAÇÃO
De onde vêm os dados: Do Módulo 1.
Como o sistema preenche: O sistema buscará o campo "Nome do Processo" e a "Descrição Detalhada do Item" e os inserirá nesta seção, garantindo que o objeto do relatório seja idêntico ao que foi cadastrado.
II - FONTES CONSULTADAS
De onde vêm os dados: Do Módulo 2 e Módulo 3.
Como o sistema preenche:
Item 2.1: Listará automaticamente quais parâmetros do Art. 5º da IN 65/2021 foram utilizados (Painel de Preços, Pesquisa com Fornecedores, etc.).
Item 2.2: Verificará se os parâmetros I e II foram usados. Caso não tenham sido, buscará a justificativa que o usuário inseriu para a não priorização.
Item 2.3: Preencherá a tabela de fornecedores consultados, buscando no Módulo 2 quem foi contatado, quem respondeu e a justificativa para a escolha de cada um.
Item 2.4: Verificará o número de preços na cesta final do Módulo 3. Se for menor que três, inserirá a justificativa de excepcionalidade que o usuário escreveu.
III - SÉRIE DE PREÇOS COLETADOS
De onde vêm os dados: Do Módulo 2 e Módulo 3.
Como o sistema preenche: O sistema montará a planilha com todos os preços que foram coletados. Para os preços que foram "Desconsiderados" na etapa de curadoria do Módulo 3, ele os destacará e adicionará a justificativa específica que o usuário forneceu para cada um.
IV - METODOLOGIA PARA OBTENÇÃO DO PREÇO ESTIMADO
De onde vêm os dados: Do Módulo 3.
Como o sistema preenche:
Item 4.1: Indicará se foi usada a média, mediana ou menor valor e buscará a justificativa do usuário para a escolha do método.
Item 4.2: Preencherá a tabela de valores desconsiderados, listando cada um e a respectiva justificativa de "inexequível, inconsistente ou excessivamente elevado".
V - MEMÓRIA DE CÁLCULO E CONCLUSÃO
De onde vêm os dados: Do Módulo 3.
Como o sistema preenche:
Item 5.1: Apresentará a memória de cálculo final (a cesta de preços utilizada) e o valor estimado para a contratação.
Item 5.2: Inserirá o texto padrão de conclusão, certificando que o preço é compatível com o mercado ou vantajoso para a Administração.
VI - IDENTIFICAÇÃO DOS AGENTES RESPONSÁVEIS
De onde vêm os dados: Do Módulo 1.
Como o sistema preenche: O sistema inserirá o nome completo e a matrícula do agente responsável pela pesquisa.

Fluxo do Usuário e Formato de Saída
Ação: O usuário clica no botão "Gerar Relatório Final".
Opção de Formato: O sistema poderá perguntar o formato desejado:
PDF (Recomendado): Para um documento final, não editável e pronto para ser anexado ao processo.
DOCX (Word): Como uma opção de flexibilidade, caso o usuário precise fazer algum ajuste manual mínimo antes de oficializar o documento.
Resultado: O sistema gera o arquivo e o disponibiliza para download imediato.
Este módulo é a culminação de todo o processo. Ele elimina horas de trabalho manual de copiar e colar informações, formatação de documentos e, o mais importante, zera o risco de erros de transcrição, garantindo um resultado final profissional e 100% em conformidade com o padrão da AGU.


Sugestões para o Stack de Tecnologia

Stack de Tecnologia Detalhado
Com base nessa projeção detalhada e na sua preferência pelas tecnologias centrais, aqui está uma sugestão detalhada do Stack de Tecnologia ideal para construir, manter e escalar o Micro-SaaS.
Stack de Tecnologia Detalhado
1. Backend (O Coração da Aplicação)
O backend será responsável por toda a lógica de negócio, segurança, interação com o banco de dados e automações.
Linguagem: PHP 8.2+


Motivo: Utilizaremos uma versão moderna do PHP para aproveitar os ganhos de performance, a sintaxe aprimorada e o sistema de tipos mais robusto, o que resulta em um código mais limpo e de fácil manutenção.

Framework / Roteamento: Slim Framework


Motivo: Em vez de PHP "puro" ou um framework complexo como o Laravel, o Slim oferece o equilíbrio perfeito. Ele fornece uma estrutura essencial para organizar a aplicação (roteamento, injeção de dependência, middleware) sem o peso e a curva de aprendizado de um framework full-stack. Isso nos permitirá criar uma API interna limpa para o frontend consumir.

Gerenciador de Dependências: Composer


Motivo: É o padrão de fato do mundo PHP. Com ele, instalaremos e gerenciaremos todas as bibliotecas de forma simples e organizada.

Bibliotecas Essenciais (Instaladas via Composer):


Geração de PDF (Relatórios): dompdf/dompdf. Escolha ideal porque permite criar o PDF a partir de um arquivo HTML e CSS. Ou seja, podemos desenhar a "Nota Técnica da AGU" usando a tecnologia que já conhecemos e o Dompdf a converterá em um PDF fiel.
Geração de DOCX (Relatórios): phpoffice/phpword. É a biblioteca mais robusta e utilizada para criar e manipular arquivos do Microsoft Word, atendendo à necessidade de oferecer o relatório em formato editável.
Captura de Tela (Coletor de URLs): spatie/browsershot. Uma biblioteca fantástica que serve como uma "ponte" entre o PHP e o Puppeteer (um navegador controlado por código). Ela simplifica enormemente a automação da captura de tela. Nota: Exigirá a instalação do Node.js e do Puppeteer no servidor.
Manipulação de HTML (Coletor de URLs): paquettg/di-dom. Uma biblioteca leve e com uma sintaxe muito mais amigável do que as funções nativas do PHP para "ler" o conteúdo das páginas web e extrair os metadados.

2. Frontend (A Interface com o Usuário)
A meta é ser rápido, responsivo e intuitivo.
Estrutura e Estilo: Bootstrap 5.3+


Motivo: A versão mais recente é ideal por não depender mais de jQuery, ser focada em componentes e ter um sistema de grid extremamente poderoso, facilitando a criação de uma interface que funciona bem em desktops e celulares.

CSS Customizado: Sass (Syntactically Awesome Style Sheets)


Motivo: O próprio Bootstrap é construído com Sass. Usar Sass para o seu CSS customizado permite que você aproveite as variáveis e funções do Bootstrap para criar um tema visual coeso de forma muito mais eficiente do que com CSS puro.

JavaScript Interativo: Alpine.js


Motivo: Para a interatividade que precisamos (mostrar/ocultar campos, atualizar cálculos na tela do Módulo 3), o Alpine.js é perfeito. Ele é extremamente leve e permite adicionar comportamento dinâmico diretamente no HTML, sem a necessidade de um framework pesado como React ou Vue.js. Ele se integra perfeitamente a uma aplicação renderizada pelo PHP.

3. Banco de Dados (Onde Tudo Fica Guardado)
Sistema de Gerenciamento: MySQL 8.0+
Motivo: É robusto, confiável, amplamente suportado por todas as hospedagens e totalmente compatível com PHP. A versão 8+ oferece melhorias de performance e segurança.
Interação com PHP: Utilizaremos a extensão PDO (PHP Data Objects), com ênfase total em Prepared Statements para todas as consultas, garantindo proteção máxima contra ataques de injeção de SQL.

4. Ambiente e Servidor (Onde a Aplicação Roda)
Servidor Web: Nginx


Motivo: É reconhecido por sua alta performance, baixo consumo de memória e excelente capacidade de servir tanto conteúdo estático (CSS, JS, imagens) quanto de se comunicar com o PHP de forma eficiente.
Processador PHP: PHP-FPM (FastCGI Process Manager)


Motivo: É a maneira moderna e de melhor desempenho para rodar aplicações PHP em conjunto com o Nginx.
Hospedagem: VPS (Virtual Private Server) - Exemplos: DigitalOcean, Linode, Vultr.


Motivo: Uma VPS lhe dará o controle total necessário para instalar todo o stack (Nginx, PHP, MySQL, Node.js, Composer) e configurar os cron jobs para as automações do TCE-RS, por um custo-benefício excelente.

Ambiente de Desenvolvimento Local: Docker e Docker Compose


Motivo: Para garantir que o ambiente em que você desenvolve seja idêntico ao ambiente de produção, evitando o clássico problema do "na minha máquina funciona". O Docker cria contêineres isolados para cada serviço (Nginx, PHP, MySQL), garantindo consistência e facilitando a configuração inicial.

Fluxo de Trabalho Sugerido
Modele o Banco de Dados: Desenhe as tabelas (Processos, Itens, FontesDePreco, Fornecedores, Usuarios, etc.).
Desenvolva o Backend: Crie os scripts PHP para as operações CRUD (Criar, Ler, Atualizar, Deletar) de cada módulo.
Construa o Frontend: Use o Bootstrap para criar as páginas e formulários. Faça com que pareçam limpos e fáceis de usar.
Integre com JavaScript: Adicione a interatividade para melhorar a experiência do usuário.
Implemente a Lógica de Cálculo: Crie a função em PHP que recebe a cesta de preços e retorna a média, mediana e o menor valor.
Desenvolva o Gerador de Relatório: Crie o script que utiliza a biblioteca de PDF para montar o documento final com os dados do banco.
Seguindo essa estrutura, você construirá uma ferramenta poderosa e direcionada, com grandes chances de sucesso no nicho de contratações públicas.


Ajustes em 15/06
correções feitas hoje
2) correções nos formularios
3) inclusão do campo região nos processos ok
4) corrigir a inclusão das cotações escolhidas no formulário de buscar no painel de preços ok
5) implementando a funcionalidade de pesquisa entre órgãos ok
6) implementação da funcionalidade de pesquisa com base em sites similares e com base em notas fiscais

Ajuste em 16/06
1) implementação da funcionalidade de pesquisa a fornecedores ok
2) implementação da funcionalidade de cálculos para decisão

Em 19/06

1) ajustes realizados:




Próximos ajustes
1) Ajustes na funcionalidade de cálculos para decisão
2) Revisão das funcionalidades com base na IN 65/2021
3)  

Correções feitas em 20/06

Correções de Regras de Negócio e Conformidade
Implementamos validações e funcionalidades críticas para garantir que o sistema siga as regras da norma:

Validação de Prazos: O sistema agora valida automaticamente se as cotações manuais estão dentro dos prazos legais (6 meses ou 1 ano, dependendo da fonte) .

Alerta de Amostra Insuficiente: A "Mesa de Análise" agora exibe um alerta e exige uma justificativa especial se o cálculo do preço for baseado em menos de três cotações.
Trava de Segurança do Painel de Preços: Foi adicionada uma regra que impede o agente de salvar um preço estimado superior à mediana quando a análise usa apenas cotações do Painel de Preços.

Justificativa e Condições Contratuais: O formulário de solicitação a fornecedores agora possui campos obrigatórios para o agente justificar a escolha dos fornecedores e detalhar as condições comerciais da compra .

Registro de Propostas: O sistema agora permite que o fornecedor anexe sua proposta formal em PDF, e foi criada uma nova página de "Acompanhamento" que serve como registro formal dos fornecedores que responderam e dos que não responderam no prazo.

3. Melhorias na Experiência do Fornecedor
Focamos em tornar a página pública de cotação mais profissional, funcional e fácil de usar:

Pré-preenchimento de Dados: O formulário de cotação agora busca os dados do fornecedor (Razão Social, CNPJ, Endereço, etc.) do banco de dados e os pré-preenche, permitindo que o fornecedor apenas confirme ou edite as informações.
Informações Completas: O formulário agora exibe a quantidade de cada item a ser cotado e inclui campos para o fornecedor preencher todos os dados necessários para uma proposta formal (dados do responsável, validade da proposta, etc.).
Cálculo Automático: O campo "Valor Total" de cada item agora é calculado automaticamente em tempo real assim que o fornecedor digita o preço unitário.
Geração de Proposta Profissional: Substituímos a geração de PDF por uma função de impressão nativa do navegador, que se mostrou mais estável. Para isso:
Refatoramos a lista de itens para usar uma <table>, garantindo um alinhamento perfeito.
Aprimoramos o CSS (@media print) para que, ao imprimir (ou "Salvar como PDF"), o resultado seja um documento limpo e com aparência profissional, sem os elementos do formulário.
Adicionamos um bloco de assinatura que é preenchido dinamicamente.


Fornecedores 
1) Ativar o botão de ações e colocar o CRUD para funcionar ok
2) Permitir a importação de fornecedores por meio de uma planilha ok

em 21/06
3) Permitir a importação de itens por meio de uma planilha ==== ok implementado
4) Correção da duplicidade ne lista de itens da mesa de análise ==== ok

5) Implantação do módulo de relatório para fechamento da pesquisa de preços ok implantado

em 22/06
6) Implantação da cotação rápida ok, mas, precisa ajustar
7) Ajuste no relatório final ok
8) Ajuste na cotação rápida:
    a) Possibitar múltiplos itens ok
    b) Possibilitar importação de itens ok
    c) Tornar mais visível o que é preços do painel de preços (inciso I) e o que é similares por região (inciso II) ok
    d) Deve ser possível desconsiderar valores inexequíveis ou excessivos para fins de cálculo ok
    e) Deve ser possível gerar um relatório para a cotação rápida igual o relatório final, com as devidas adequações por ser cotação rápida, e deve entrar também na lista de Histórico de Relatórios ok
      Fase 1, 2 e 3

      em 23/06

    9) Implementação do módulo de autenticação e usuários ok
    10) Implementação de recuperação de senha pendente
    11) Implementado chatbot, visualização do item analisado na mesa de análise de itens
    12) 

    em 28/06
    13) inclusão de imagem no background do login ok
    14) implementação de autocomplete e operadores no busca inteligente e catmat com descrição automática ok

    em 29/06
    15) correção da visualização da nota técnica ok
    16) implementação de usuários ok
    17) ajustes nas permissões de usuários ok
    18) adição de novos gráficos ok
    19) iplantação do autocomplete e busca da descrição no supabase

 
 

Ações futuras





para configurações:
1) colocar a opção de deixar a Região pre definida para o Estado do órgão
2) opção de paleta de cores
3) 

<<<<<<< HEAD
Subindo a aplicação no easypanel:
=======
Aplicação implantada no portainer 

>>>>>>> ef0bad352813ec9d1d8023bae5f0e81307cc7bfb

