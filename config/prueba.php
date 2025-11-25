<?php
/**
 * Test simple de conexión con PDO nativo
 */

echo "<pre>";
echo "═══════════════════════════════════════════\n";
echo "  TEST DE CONEXIÓN PDO A SKYSQL\n";
echo "═══════════════════════════════════════════\n\n";

// Configuración
$host = "serverless-europe-west9.sysp0000.db2.skysql.com";
$port = 4050;
$dbname = "products_db";
$user = "dbpgf05703062";
$pass = "NuevaPassSegura_2025!!";

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

echo "Configuración:\n";
echo "  Host: $host\n";
echo "  Puerto: $port\n";
echo "  Base de datos: $dbname\n";
echo "  Usuario: $user\n\n";

echo "Intentando conectar con PDO...\n";
echo "─────────────────────────────────────────\n\n";

try {
    // Opciones PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    
    // Intentar conexión
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "✅ ¡CONEXIÓN EXITOSA!\n\n";
    
    // Información de la conexión
    echo "Información del servidor:\n";
    echo "─────────────────────────────────────────\n";
    
    $stmt = $pdo->query("SELECT 
        VERSION() AS version,
        DATABASE() AS db,
        CURRENT_USER() AS user,
        @@ssl_cipher AS ssl
    ");
    
    $info = $stmt->fetch();
    
    echo "  Versión: {$info['version']}\n";
    echo "  Base de datos: {$info['db']}\n";
    echo "  Usuario: {$info['user']}\n";
    echo "  SSL Cipher: " . ($info['ssl'] ?: 'No SSL') . "\n\n";
    
    // Listar tablas
    echo "Tablas disponibles:\n";
    echo "─────────────────────────────────────────\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`");
            $count = $countStmt->fetch();
            
            echo "  - $tableName ({$count['count']} registros)\n";
        }
    } else {
        echo "  (No hay tablas)\n";
    }
    
    echo "\n";
    
    // Test de operaciones
    echo "Pruebas de operaciones:\n";
    echo "─────────────────────────────────────────\n";
    
    // SELECT simple
    $result = $pdo->query("SELECT 1 + 1 AS result");
    $row = $result->fetch();
    echo "  SELECT simple: " . ($row['result'] == 2 ? "✅ OK" : "❌ FALLO") . "\n";
    
    // Prepared statement
    $stmt = $pdo->prepare("SELECT ? + ? AS result");
    $stmt->execute([5, 10]);
    $row = $stmt->fetch();
    echo "  Prepared statement: " . ($row['result'] == 15 ? "✅ OK" : "❌ FALLO") . "\n";
    
    echo "\n═══════════════════════════════════════════\n";
    echo "  ✅ TODO FUNCIONA CORRECTAMENTE\n";
    echo "═══════════════════════════════════════════\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR DE CONEXIÓN\n\n";
    echo "Código: {$e->getCode()}\n";
    echo "Mensaje: {$e->getMessage()}\n\n";
    
    echo "═══════════════════════════════════════════\n";
    echo "  SOLUCIONES POSIBLES\n";
    echo "═══════════════════════════════════════════\n\n";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "⚠️  ERROR DE AUTENTICACIÓN\n\n";
        echo "Este error significa que tu IP no está\n";
        echo "autorizada en el firewall de SkySQL.\n\n";
        echo "Pasos para solucionar:\n";
        echo "  1. Ve a https://app.skysql.com/\n";
        echo "  2. Selecciona tu servicio\n";
        echo "  3. Manage → Security Access\n";
        echo "  4. Click en 'Add my current IP'\n";
        echo "  5. Espera 1-2 minutos\n";
        echo "  6. Vuelve a ejecutar este script\n\n";
        
    } elseif (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), '2002') !== false) {
        echo "⚠️  ERROR DE SSL/CONEXIÓN\n\n";
        echo "Opciones:\n";
        echo "  1. Verifica que OpenSSL esté habilitado\n";
        echo "     en php.ini (extension=openssl)\n";
        echo "  2. Reinicia XAMPP/Apache\n";
        echo "  3. Usa el wrapper MySQLi (PDO.php)\n\n";
        
    } else {
        echo "⚠️  ERROR GENERAL\n\n";
        echo "Verifica:\n";
        echo "  - Host y puerto correctos\n";
        echo "  - Credenciales (usuario/contraseña)\n";
        echo "  - Conexión a internet\n\n";
    }
}

echo "</pre>";
?>