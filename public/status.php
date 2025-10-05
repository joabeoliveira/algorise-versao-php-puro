<?php
echo "🚀 Sistema Funcionando!";
echo "<br>URL: " . ($_SERVER['REQUEST_URI'] ?? 'N/A');
echo "<br>Método: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A');
echo "<br><a href='/dashboard'>Ir para Dashboard</a>";
?>