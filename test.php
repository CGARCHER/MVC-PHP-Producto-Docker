<?php
require_once("config/PDO.php");  // ← Así

use MiApp\Database\PDO;

echo "<pre>=== PRUEBA DE CONEXIÓN SKYSQL CON WRAPPER PDO ===\n\n";

// Configuración SkySQL (tus datos reales)
$host = "serverless-europe-west9.sysp0000.db2.skysql.com";
$port = 4050;
$dbname = "products_db";
$user = "dbpgf05703062";
$pass = "NuevaPassSegura_2025!!";

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    echo "1. Intentando conectar a SkySQL con el WRAPPER...\n";
    echo "   Host: {$host}\n";
    echo "   Puerto: {$port}\n";
    echo "   Base de datos: {$dbname}\n";
    echo "   Usuario: {$user}\n\n";
    
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ Conexión exitosa con el WRAPPER PDO!\n\n";
    
    echo "2. Probando consulta simple...\n";
    $result = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $row = $result->fetch();
    echo "   Base de datos actual: {$row['db']}\n";
    echo "   Versión MariaDB: {$row['version']}\n\n";
    
    echo "3. Listando tablas...\n";
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll();
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            echo "   - " . $table[0] . "\n";
        }
    } else {
        echo "   (No hay tablas creadas aún)\n";
    }
    
    echo "\n4. Probando prepared statement...\n";
    $stmt = $pdo->prepare("SELECT ? as test, ? as numero");
    $stmt->execute(['¡Wrapper funciona!', 2025]);
    $row = $stmt->fetch();
    echo "   Test: {$row['test']}\n";
    echo "   Número: {$row['numero']}\n";
    
    echo "\n✅✅✅ EL WRAPPER PDO FUNCIONA PERFECTAMENTE CON SKYSQL! ✅✅✅\n";
    echo "Ahora tu código puede usar sintaxis PDO y por dentro usa MySQLi con SSL.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "\n--- Stack trace ---\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>