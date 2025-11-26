<?php
echo "<pre>=== TEST LOCAL → SKYSQL CON PDO NATIVO ===\n\n";

// Configuración SkySQL
$host = "serverless-europe-west9.sysp0000.db2.skysql.com";
$port = 4050;
$dbname = "products_db";
$user = "dbpgf05703062";
$pass = "NuevaPassSegura_2025!!";

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    echo "1. Conectando desde LOCAL a SKYSQL con PDO NATIVO...\n";
    echo "   Host: {$host}\n";
    echo "   Puerto: {$port}\n";
    echo "   Base de datos: {$dbname}\n";
    echo "   Usuario: {$user}\n\n";
    
    // Opciones PDO para SSL
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Conexión LOCAL → SKYSQL con PDO NATIVO exitosa!\n\n";
    
    // Información de la BD
    echo "2. Información de la base de datos...\n";
    $result = $pdo->query("SELECT DATABASE() as db, VERSION() as version");
    $row = $result->fetch();
    echo "   Base de datos: {$row['db']}\n";
    echo "   Versión MariaDB: {$row['version']}\n\n";
    
    // Listar tablas
    echo "3. Listando tablas...\n";
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll();
    foreach ($tables as $table) {
        $tableName = reset($table);
        echo "   - {$tableName}\n";
    }
    echo "\n";
    
    // Consultar productos
    echo "4. Consultando productos...\n";
    $result = $pdo->query("SELECT * FROM products LIMIT 3");
    $products = $result->fetchAll();
    foreach ($products as $p) {
        echo "   - {$p['name']} | €{$p['price']}\n";
    }
    
    echo "\n✅✅✅ PDO NATIVO FUNCIONA DESDE LOCAL A SKYSQL! ✅✅✅\n";
    echo "Si esto funciona, NO NECESITAS EL WRAPPER.\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR CON PDO NATIVO: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n\n";
    echo "Si este test FALLA, entonces SÍ necesitas el wrapper.\n";
}

echo "</pre>";
?>