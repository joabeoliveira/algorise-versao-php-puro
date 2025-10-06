<?php
require_once __DIR__ . '/src/settings.php';

try {
    $pdo = getDbConnection();
    
    echo "=== CORRIGINDO CAMPO GERADA_POR ===\n\n";
    
    // Verificar quantas notas têm gerada_por NULL
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM notas_tecnicas WHERE gerada_por IS NULL");
    $total = $stmt->fetch()['total'];
    
    echo "📊 Notas com gerada_por NULL: {$total}\n";
    
    if ($total > 0) {
        // Atualizar todas as notas com gerada_por NULL
        $stmt = $pdo->prepare("UPDATE notas_tecnicas SET gerada_por = 'Admin' WHERE gerada_por IS NULL");
        $result = $stmt->execute();
        $updated = $stmt->rowCount();
        
        echo "✅ Notas atualizadas: {$updated}\n";
    }
    
    // Verificar resultado
    $stmt = $pdo->query("
        SELECT id, numero_nota, ano_nota, gerada_por, gerada_em 
        FROM notas_tecnicas 
        ORDER BY gerada_em DESC
    ");
    $notas = $stmt->fetchAll();
    
    echo "\n📋 Estado atual das notas:\n";
    foreach ($notas as $nota) {
        echo "- Nota #{$nota['numero_nota']}/{$nota['ano_nota']} | Gerada por: {$nota['gerada_por']} | Em: {$nota['gerada_em']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?>