<?php
// COMPARAÇÃO ENTRE DESENVOLVIMENTO E PRODUÇÃO
echo "<h2>COMPARAÇÃO DE ESTRUTURAS - DEV vs PRODUÇÃO</h2>\n";

try {
    // Conexão com Cloud SQL (Produção)
    $prodPdo = new PDO("mysql:unix_socket=/cloudsql/algorise-producao:southamerica-east1:algorise-db;dbname=algorise", "algorise-user", "114211Jo@");
    $prodPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>✅ Conectado ao Cloud SQL (Produção)</h3>\n";
    
    // Listar todas as tabelas em produção
    $stmt = $prodPdo->query("SHOW TABLES");
    $tabelasProd = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 TABELAS EM PRODUÇÃO:</h3>\n";
    echo "<ul>\n";
    foreach ($tabelasProd as $tabela) {
        echo "<li>" . $tabela . "</li>\n";
    }
    echo "</ul>\n";
    
    // Verificar estrutura da tabela usuarios em produção
    echo "<h3>🔍 ESTRUTURA DA TABELA 'usuarios' EM PRODUÇÃO:</h3>\n";
    $stmt = $prodPdo->query("DESCRIBE usuarios");
    $colunasUsuariosProd = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>\n";
    foreach ($colunasUsuariosProd as $col) {
        echo "<tr>";
        echo "<td>" . $col['Field'] . "</td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . $col['Extra'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    
    // Verificar estrutura da tabela processos em produção
    echo "<h3>🔍 ESTRUTURA DA TABELA 'processos' EM PRODUÇÃO:</h3>\n";
    try {
        $stmt = $prodPdo->query("DESCRIBE processos");
        $colunasProcessosProd = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>\n";
        foreach ($colunasProcessosProd as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } catch (Exception $e) {
        echo "<p>❌ Erro ao acessar tabela processos: " . $e->getMessage() . "</p>\n";
    }
    
    // Verificar estrutura da tabela fornecedores em produção
    echo "<h3>🔍 ESTRUTURA DA TABELA 'fornecedores' EM PRODUÇÃO:</h3>\n";
    try {
        $stmt = $prodPdo->query("DESCRIBE fornecedores");
        $colunasFornecedoresProd = $stmt->fetchAll();
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>\n";
        foreach ($colunasFornecedoresProd as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . $col['Extra'] . "</td>";
            echo "</tr>\n";
        }
        echo "</table>\n";
    } catch (Exception $e) {
        echo "<p>❌ Erro ao acessar tabela fornecedores: " . $e->getMessage() . "</p>\n";
    }
    
    // Teste de inserção simples
    echo "<h3>🧪 TESTE DE INSERÇÃO SIMPLES NA TABELA USUARIOS:</h3>\n";
    try {
        $stmt = $prodPdo->prepare("INSERT INTO usuarios (nome, email, senha, role) VALUES (?, ?, ?, ?)");
        $testEmail = 'teste_estrutura_' . time() . '@test.com';
        $result = $stmt->execute(['Teste Estrutura', $testEmail, password_hash('123456', PASSWORD_DEFAULT), 'user']);
        
        if ($result) {
            $id = $prodPdo->lastInsertId();
            echo "<p>✅ INSERÇÃO FUNCIONOU! ID criado: " . $id . "</p>\n";
            
            // Verificar se foi salvo
            $stmt = $prodPdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                echo "<p>✅ DADOS CONFIRMADOS: " . $usuario['nome'] . " - " . $usuario['email'] . "</p>\n";
                
                // Limpar teste
                $stmt = $prodPdo->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$id]);
                echo "<p>🧹 Dados de teste removidos</p>\n";
            }
        }
    } catch (Exception $e) {
        echo "<p>❌ ERRO NO TESTE DE INSERÇÃO: " . $e->getMessage() . "</p>\n";
    }
    
    // Contar registros existentes
    echo "<h3>📊 CONTAGEM DE REGISTROS:</h3>\n";
    foreach ($tabelasProd as $tabela) {
        try {
            $stmt = $prodPdo->query("SELECT COUNT(*) FROM " . $tabela);
            $count = $stmt->fetchColumn();
            echo "<p><strong>" . $tabela . ":</strong> " . $count . " registros</p>\n";
        } catch (Exception $e) {
            echo "<p><strong>" . $tabela . ":</strong> Erro - " . $e->getMessage() . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<h3>❌ ERRO DE CONEXÃO COM PRODUÇÃO:</h3>\n";
    echo "<p>" . $e->getMessage() . "</p>\n";
}

echo "<hr>\n";
echo "<h3>📝 ESTRUTURA ESPERADA PELO CÓDIGO (Desenvolvimento):</h3>\n";
echo "<p><strong>usuarios:</strong> id, nome, email, senha, role, created_at</p>\n";
echo "<p><strong>processos:</strong> id, numero_processo, nome_processo, tipo_contratacao, status, agente_responsavel, uasg, regiao</p>\n";
echo "<p><strong>fornecedores:</strong> id, razao_social, cnpj, email, endereco, telefone, ramo_atividade</p>\n";
?>