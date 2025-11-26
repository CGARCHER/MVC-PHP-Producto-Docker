<?php
/**
 * PDO.php - Wrapper MySQLi con sintaxis PDO
 * 
 * Este archivo implementa clases compatibles con PDO usando MySQLi con SSL
 * Útil para conectar con SkySQL cuando PDO nativo presenta problemas
 * 
 * Fix temporal by cgarcher 
 * Parche temporal generado, pendiente de revision
 */

namespace Cgarcher\Fix\Database;

class PDO {
    private $mysqli;
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $pass;
    private $charset;
    
    // Constantes PDO estándar
    const ATTR_ERRMODE = 3;
    const ERRMODE_SILENT = 0;
    const ERRMODE_WARNING = 1;
    const ERRMODE_EXCEPTION = 2;
    const ATTR_DEFAULT_FETCH_MODE = 19;
    const FETCH_ASSOC = 2;
    const FETCH_NUM = 3;
    const FETCH_BOTH = 4;
    const FETCH_OBJ = 5;
    const FETCH_LAZY = 1;
    const ATTR_EMULATE_PREPARES = 20;
    const MYSQL_ATTR_SSL_VERIFY_SERVER_CERT = 1014;
    const MYSQL_ATTR_SSL_CA = 1005;
    const MYSQL_ATTR_SSL_KEY = 1006;
    const MYSQL_ATTR_SSL_CERT = 1007;
    const MYSQL_ATTR_INIT_COMMAND = 1002;
    const MYSQL_ATTR_MULTI_STATEMENTS = 1011;
    const ATTR_TIMEOUT = 2;
    const ATTR_CASE = 8;
    const ATTR_ORACLE_NULLS = 11;
    const ATTR_PERSISTENT = 12;
    const ATTR_STATEMENT_CLASS = 13;
    const ATTR_FETCH_TABLE_NAMES = 14;
    const ATTR_FETCH_CATALOG_NAMES = 15;
    const ATTR_DRIVER_NAME = 16;
    const ATTR_STRINGIFY_FETCHES = 17;
    const ATTR_MAX_COLUMN_LEN = 18;
    const ATTR_AUTOCOMMIT = 0;
    const ATTR_PREFETCH = 1;
    const ATTR_CURSOR = 10;
    const ATTR_CURSOR_NAME = 9;
    
    private $errorMode = self::ERRMODE_EXCEPTION;
    private $fetchMode = self::FETCH_ASSOC;
    
    /**
     * Constructor compatible con PDO
     * @param string $dsn Data Source Name
     * @param string $user Usuario
     * @param string $pass Contraseña
     * @param array $options Opciones de configuración
     */
    public function __construct($dsn, $user = null, $pass = null, $options = []) {
        // Opciones por defecto para SkySQL
        $defaultOptions = [
            self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION,
            self::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ];
        
        // Combinar opciones por defecto con las proporcionadas
        $options = array_merge($defaultOptions, $options);
        
        $this->parseDSN($dsn);
        $this->user = $user;
        $this->pass = $pass;
        
        // Procesar opciones
        foreach ($options as $key => $value) {
            $this->setAttribute($key, $value);
        }
        
        $this->connect();
    }
    
    /**
     * Parsea el DSN de PDO
     */
    private function parseDSN($dsn) {
        // Formato: mysql:host=X;port=Y;dbname=Z;charset=W
        $parts = explode(':', $dsn, 2);
        
        if (count($parts) !== 2) {
            throw new PDOException("DSN inválido: $dsn");
        }
        
        $params = explode(';', $parts[1]);
        
        foreach ($params as $param) {
            $pair = explode('=', $param, 2);
            if (count($pair) === 2) {
                $key = trim($pair[0]);
                $value = trim($pair[1]);
                
                switch ($key) {
                    case 'host':
                        $this->host = $value;
                        break;
                    case 'port':
                        $this->port = (int)$value;
                        break;
                    case 'dbname':
                        $this->dbname = $value;
                        break;
                    case 'charset':
                        $this->charset = $value;
                        break;
                }
            }
        }
        
        // Valores por defecto
        $this->port = $this->port ?? 3306;
        $this->charset = $this->charset ?? 'utf8mb4';
    }
    
    /**
     * Establece la conexión usando MySQLi con SSL
     */
    private function connect() {
        $this->mysqli = mysqli_init();
        
        if (!$this->mysqli) {
            throw new PDOException("Error inicializando MySQLi");
        }
        
        // Configuración SSL (compatible con SkySQL)
        // Siempre desactiva la verificación del certificado
        $this->mysqli->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        
        // Intentar conexión con SSL
        $connected = @$this->mysqli->real_connect(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname,
            $this->port,
            NULL,
            MYSQLI_CLIENT_SSL
        );
        
        if (!$connected) {
            if ($this->errorMode === self::ERRMODE_EXCEPTION) {
                throw new PDOException(
                    "SQLSTATE[HY000] [" . $this->mysqli->connect_errno . "] " . $this->mysqli->connect_error,
                    $this->mysqli->connect_errno
                );
            }
            return false;
        }
        
        // Configurar charset
        $this->mysqli->set_charset($this->charset);
        return true;
    }
    
    /**
     * Ejecuta una consulta SQL
     */
    public function query($sql) {
        $result = $this->mysqli->query($sql);
        
        if ($result === false) {
            if ($this->errorMode === self::ERRMODE_EXCEPTION) {
                throw new PDOException(
                    "SQLSTATE[HY000]: " . $this->mysqli->error,
                    $this->mysqli->errno
                );
            }
            return false;
        }
        
        return new PDOStatement($result, $this->fetchMode);
    }
    
    /**
     * Prepara una consulta
     */
    public function prepare($sql, $options = []) {
        $stmt = $this->mysqli->prepare($sql);
        
        if (!$stmt) {
            if ($this->errorMode === self::ERRMODE_EXCEPTION) {
                throw new PDOException(
                    "SQLSTATE[HY000]: " . $this->mysqli->error,
                    $this->mysqli->errno
                );
            }
            return false;
        }
        
        return new PDOStatement($stmt, $this->fetchMode, $this->errorMode);
    }
    
    /**
     * Ejecuta una consulta y devuelve filas afectadas
     */
    public function exec($sql) {
        $result = $this->mysqli->query($sql);
        
        if ($result === false) {
            if ($this->errorMode === self::ERRMODE_EXCEPTION) {
                throw new PDOException(
                    "SQLSTATE[HY000]: " . $this->mysqli->error,
                    $this->mysqli->errno
                );
            }
            return false;
        }
        
        return $this->mysqli->affected_rows;
    }
    
    /**
     * Obtiene el último ID insertado
     */
    public function lastInsertId($name = null) {
        return $this->mysqli->insert_id;
    }
    
    /**
     * Escapa y entrecomilla una cadena
     */
    public function quote($string, $type = self::FETCH_ASSOC) {
        return "'" . $this->mysqli->real_escape_string($string) . "'";
    }
    
    /**
     * Configura un atributo
     */
    public function setAttribute($attribute, $value) {
        switch ($attribute) {
            case self::ATTR_ERRMODE:
                $this->errorMode = $value;
                break;
            case self::ATTR_DEFAULT_FETCH_MODE:
                $this->fetchMode = $value;
                break;
            // Ignorar opciones SSL (ya gestionadas)
            case self::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT:
            case self::MYSQL_ATTR_SSL_CA:
            case self::MYSQL_ATTR_SSL_KEY:
            case self::MYSQL_ATTR_SSL_CERT:
            case self::MYSQL_ATTR_INIT_COMMAND:
            case self::ATTR_EMULATE_PREPARES:
            case self::MYSQL_ATTR_MULTI_STATEMENTS:
                break;
        }
        return true;
    }
    
    /**
     * Obtiene un atributo
     */
    public function getAttribute($attribute) {
        switch ($attribute) {
            case self::ATTR_ERRMODE:
                return $this->errorMode;
            case self::ATTR_DEFAULT_FETCH_MODE:
                return $this->fetchMode;
            case self::ATTR_DRIVER_NAME:
                return 'mysql';
            default:
                return null;
        }
    }
    
    /**
     * Inicia una transacción
     */
    public function beginTransaction() {
        return $this->mysqli->begin_transaction();
    }
    
    /**
     * Confirma una transacción
     */
    public function commit() {
        return $this->mysqli->commit();
    }
    
    /**
     * Revierte una transacción
     */
    public function rollBack() {
        return $this->mysqli->rollback();
    }
    
    /**
     * Verifica si hay transacción activa
     */
    public function inTransaction() {
        return false;
    }
    
    /**
     * Obtiene información del error
     */
    public function errorCode() {
        return $this->mysqli->errno;
    }
    
    /**
     * Obtiene información detallada del error
     */
    public function errorInfo() {
        return [
            $this->mysqli->sqlstate ?? '00000',
            $this->mysqli->errno,
            $this->mysqli->error
        ];
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}

/**
 * PDOStatement compatible
 */
class PDOStatement {
    private $result;
    private $stmt;
    private $fetchMode;
    private $errorMode;
    private $params = [];
    private $isExecuted = false;
    
    public function __construct($resource, $fetchMode = PDO::FETCH_ASSOC, $errorMode = PDO::ERRMODE_EXCEPTION) {
        if ($resource instanceof \mysqli_result) {
            $this->result = $resource;
            $this->isExecuted = true;
        } elseif ($resource instanceof \mysqli_stmt) {
            $this->stmt = $resource;
        }
        
        $this->fetchMode = $fetchMode;
        $this->errorMode = $errorMode;
    }
    
    public function bindValue($param, $value, $type = null) {
        if (is_int($param)) {
            $this->params[$param - 1] = $value;
        } else {
            $this->params[$param] = $value;
        }
        return true;
    }
    
    public function bindParam($param, &$variable, $type = null, $length = null, $options = null) {
        if (is_int($param)) {
            $this->params[$param - 1] = &$variable;
        } else {
            $this->params[$param] = &$variable;
        }
        return true;
    }
    
    public function execute($params = null) {
        if ($params !== null) {
            $this->params = $params;
        }
        
        if (!$this->stmt) {
            return false;
        }
        
        if (!empty($this->params)) {
            $types = '';
            $values = [];
            
            foreach ($this->params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } elseif (is_string($param)) {
                    $types .= 's';
                } else {
                    $types .= 'b';
                }
                $values[] = $param;
            }
            
            $this->stmt->bind_param($types, ...$values);
        }
        
        $executed = $this->stmt->execute();
        
        if (!$executed) {
            if ($this->errorMode === PDO::ERRMODE_EXCEPTION) {
                throw new PDOException(
                    "SQLSTATE[HY000]: " . $this->stmt->error,
                    $this->stmt->errno
                );
            }
            return false;
        }
        
        $this->result = $this->stmt->get_result();
        $this->isExecuted = true;
        
        return true;
    }
    
    public function fetch($mode = null, $orientation = null, $offset = null) {
        if (!$this->result) {
            return false;
        }
        
        $fetchMode = $mode ?? $this->fetchMode;
        
        switch ($fetchMode) {
            case PDO::FETCH_ASSOC:
                return $this->result->fetch_assoc();
            case PDO::FETCH_NUM:
                return $this->result->fetch_row();
            case PDO::FETCH_BOTH:
                return $this->result->fetch_array(MYSQLI_BOTH);
            case PDO::FETCH_OBJ:
                return $this->result->fetch_object();
            default:
                return $this->result->fetch_assoc();
        }
    }
    
    public function fetchAll($mode = null, $arg = null, $args = []) {
        if (!$this->result) {
            return [];
        }
        
        $rows = [];
        while ($row = $this->fetch($mode)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function fetchColumn($column = 0) {
        if (!$this->result) {
            return false;
        }
        
        $row = $this->result->fetch_row();
        return $row ? $row[$column] : false;
    }
    
    public function fetchObject($class = "stdClass", $params = []) {
        if (!$this->result) {
            return false;
        }
        
        return $this->result->fetch_object($class, $params);
    }
    
    public function rowCount() {
        if ($this->result) {
            return $this->result->num_rows;
        }
        if ($this->stmt) {
            return $this->stmt->affected_rows;
        }
        return 0;
    }
    
    public function columnCount() {
        if ($this->result) {
            return $this->result->field_count;
        }
        return 0;
    }
    
    public function closeCursor() {
        if ($this->result) {
            $this->result->free();
        }
        return true;
    }
    
    public function setFetchMode($mode, ...$args) {
        $this->fetchMode = $mode;
        return true;
    }
    
    public function errorCode() {
        if ($this->stmt) {
            return $this->stmt->errno;
        }
        return null;
    }
    
    public function errorInfo() {
        if ($this->stmt) {
            return [
                $this->stmt->sqlstate ?? '00000',
                $this->stmt->errno,
                $this->stmt->error
            ];
        }
        return ['00000', null, null];
    }
    
    public function __destruct() {
        if ($this->stmt) {
            $this->stmt->close();
        }
        if ($this->result) {
            $this->result->free();
        }
    }
}

/**
 * PDOException compatible
 */
class PDOException extends \Exception {
    public $errorInfo;
    
    public function __construct($message, $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errorInfo = [$code, $code, $message];
    }
}