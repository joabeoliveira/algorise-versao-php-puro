# 🔍 Sistema de Busca CATMAT - Guia do Usuário

## Visão Geral

O Sistema de Busca CATMAT é uma nova funcionalidade que permite aos usuários encontrar códigos CATMAT de forma inteligente usando operadores de busca avançados. Esta funcionalidade foi desenvolvida para facilitar a localização precisa de materiais no catálogo nacional.

## 📋 Funcionalidades Principais

### 1. **Busca Inteligente com Operadores**
- Suporte a operadores lógicos para refinamento de busca
- Auto-complete com sugestões contextuais
- Histórico de buscas salvo localmente

### 2. **Operadores de Busca Disponíveis**

| Operador | Função | Exemplo | Resultado |
|----------|--------|---------|-----------|
| `+` | **E/AND** - Combina termos | `SERINGA + 20ML + DESCARTÁVEL` | Seringas que sejam 20ML E descartáveis |
| `-` | **EXCLUIR** - Remove resultados | `CANETA - GEL` | Canetas exceto as de gel |
| `\|` | **OU/OR** - Alternativas | `PAPEL + A4 \| OFÍCIO` | Papel A4 OU ofício |
| `" "` | **FRASE EXATA** - Busca literal | `"BICO CATETER 14FR"` | Busca pela frase exata |

### 3. **Filtros Avançados**
- **Material**: Filtra por material de fabricação (Aço, Plástico, Papel, Metal)
- **Categoria**: Filtra por categoria principal (Peças, Munição, Medicamentos)
- **Aplicação**: Campo livre para especificar uso do item
- **Ordenação**: Por relevância, código CATMAT ou descrição

### 4. **Recursos da Interface**
- **Sugestões em Tempo Real**: Auto-complete baseado no texto digitado
- **Exemplos Clicáveis**: Botões com exemplos práticos de uso
- **Histórico de Buscas**: Acesso rápido às últimas 50 buscas realizadas
- **Paginação**: Navegação eficiente pelos resultados
- **Integração com Processos**: Adição direta de CATMATs aos processos

## 🚀 Como Usar

### Acesso
1. No menu principal, clique em **"Consulta CATMAT"**
2. Ou acesse diretamente via `/catmat` no navegador

### Realizando uma Busca
1. **Digite sua busca** no campo principal
   - Use os operadores para refinar sua pesquisa
   - Observe as sugestões que aparecem conforme você digita

2. **Aplique filtros (opcional)**
   - Clique em "Filtros Avançados" para expandir
   - Configure material, categoria, aplicação e ordenação

3. **Execute a busca**
   - Pressione Enter ou clique no botão "Buscar CATMATs"
   - Aguarde o carregamento dos resultados

### Exemplos Práticos

#### Exemplo 1: Buscar Seringas Específicas
```
SERINGA + 20ML + DESCARTÁVEL
```
**Resultado**: Encontra seringas que sejam de 20ML E descartáveis

#### Exemplo 2: Papel de Diferentes Tamanhos
```
PAPEL + A4 | OFÍCIO
```
**Resultado**: Encontra papel A4 OU papel ofício

#### Exemplo 3: Canetas Exceto Gel
```
CANETA - GEL
```
**Resultado**: Todas as canetas exceto as de gel

#### Exemplo 4: Busca Exata
```
"BICO CATETER 14FR"
```
**Resultado**: Busca pela frase exata "BICO CATETER 14FR"

### Interpretando os Resultados

Cada resultado mostra:
- **Código CATMAT**: Em destaque azul
- **Relevância**: Percentual de correspondência (verde = alta, amarelo = média, cinza = baixa)
- **Descrição Completa**: Com termos de busca destacados
- **Informações Adicionais**: Material e categoria quando disponíveis
- **Ações**: Ver detalhes ou adicionar ao processo

### Adicionando CATMAT a um Processo

1. Nos resultados, clique em **"Adicionar ao Processo"**
2. Selecione o processo de destino
3. Configure quantidade e unidade
4. Clique em **"Adicionar ao Processo"**

## ⚡ Dicas de Uso

### Para Buscas Mais Efetivas:
- **Seja específico**: Use termos técnicos precisos
- **Combine operadores**: `EQUIPAMENTO + MÉDICO - DESCARTÁVEL`
- **Use filtros**: Especialmente para categorias conhecidas
- **Aproveite o histórico**: Reutilize buscas anteriores
- **Teste variações**: Se não encontrar, tente sinônimos

### Casos de Uso Comuns:

1. **Buscar por especificação técnica**:
   ```
   "CABO DE AÇO" + 6MM + GALVANIZADO
   ```

2. **Encontrar alternativas**:
   ```
   PARAFUSO + M6 | M8 + INOX
   ```

3. **Excluir características indesejadas**:
   ```
   LÂMPADA + LED - DIMMERIZÁVEL
   ```

4. **Buscar por aplicação**:
   ```
   PEÇAS + OBUSEIRO + M56
   ```

## 🔧 Aspectos Técnicos

### Integração com Supabase
- Dados atualizados do catálogo nacional
- Performance otimizada para grandes volumes
- Sincronização automática das atualizações

### Armazenamento Local
- Histórico de buscas salvo no navegador
- Configurações de filtro persistentes
- Cache de sugestões para melhor performance

### Responsividade
- Interface adaptada para desktop e mobile
- Controles otimizados para toque
- Performance mantida em diferentes dispositivos

## 🛠️ Solução de Problemas

### Não aparecem resultados?
1. Verifique a sintaxe dos operadores
2. Tente termos mais genéricos
3. Remova alguns filtros
4. Use sinônimos ou abreviações

### Sugestões não funcionam?
1. Verifique sua conexão com internet
2. Digite pelo menos 2 caracteres
3. Aguarde alguns segundos após digitar

### Erro ao adicionar ao processo?
1. Verifique se você tem permissão no processo
2. Confirme se o processo não está fechado
3. Tente recarregar a página

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte este guia primeiro
2. Verifique o histórico de buscas para exemplos
3. Entre em contato com o administrador do sistema

---

**Desenvolvido para otimizar o processo de busca de materiais e facilitar a elaboração de processos de compra.**