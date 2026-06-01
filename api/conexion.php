<?php

class Conexion
{
    #Atributos
    private $host;
    private $port;
    private $db;
    private $usuario;
    private $pass;

    # Cargar variables de entorno desde el archivo .env
    private function loadEnv($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            
            $line = trim($line);
            if (strpos($line, '=') === false) continue;
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
        return true;
    }

    #Constructor
    public function __construct()
    {
        // Cargar las credenciales desde el archivo .env
        $this->loadEnv(__DIR__ . '/../.env');

        $this->host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'aws-1-us-east-1.pooler.supabase.com');
        $this->port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '5432');
        $this->db = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'postgres');
        $this->usuario = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'postgres.kcpvrblggatiuctdcmqz');
        $this->pass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? '96HOEWAsrx2acd7d');

        # Restaurar sesión desde la cookie segura en Vercel
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['idUsuario']) && isset($_COOKIE['ch_user_session'])) {
            try {
                $key = 'ConcertHubSecretKey2026';
                $cipher = 'aes-256-cbc';
                $cookieValue = base64_decode($_COOKIE['ch_user_session']);
                $ivlen = openssl_cipher_iv_length($cipher);
                if (strlen($cookieValue) > $ivlen) {
                    $iv = substr($cookieValue, 0, $ivlen);
                    $ciphertext = substr($cookieValue, $ivlen);
                    $sessionData = openssl_decrypt($ciphertext, $cipher, $key, 0, $iv);
                    $data = json_decode($sessionData, true);
                    if (is_array($data)) {
                        $_SESSION['idUsuario'] = $data['idUsuario'];
                        $_SESSION['correo'] = $data['correo'];
                        $_SESSION['nombre'] = $data['nombre'];
                        $_SESSION['Rol'] = $data['Rol'];
                    }
                }
            } catch (Exception $e) {
                // Ignorar fallos de decodificación
            }
        }
    }

    #Método para conectar
    public function conectar()
    {
        try {
            # Configuración de PDO para PostgreSQL
            $com = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db;
            $enlace = new PDO($com, $this->usuario, $this->pass);

            # Configurar excepciones para errores de conexión
            $enlace->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $enlace->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            # Forzar compatibilidad de nombres de columnas camelCase en PostgreSQL
            $enlace->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['SafePDOStatement', []]);

            # Retornar la conexión
            return $enlace;
        } catch (PDOException $e) {
            # Mostrar un error claro si no se puede conectar
            die("Error al conectar a la base de datos de Supabase: " . $e->getMessage());
        }
    }
}

class SafePDOStatement extends PDOStatement
{
    protected function __construct() {}

    private function mapKeys(&$row)
    {
        if (!is_array($row)) {
            return;
        }
        $mapping = [
            'idusuario' => 'idUsuario',
            'idconcierto' => 'idConcierto',
            'idcodigo' => 'idCodigo',
            'idartista' => 'idArtista',
            'idcancion' => 'idCancion',
            'idcompra' => 'idCompra',
            'rol' => 'Rol'
        ];
        foreach ($mapping as $lower => $camel) {
            if (array_key_exists($lower, $row) && !array_key_exists($camel, $row)) {
                $row[$camel] = $row[$lower];
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function fetch($mode = PDO::FETCH_DEFAULT, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        $row = parent::fetch($mode, $cursorOrientation, $cursorOffset);
        if (is_array($row)) {
            $this->mapKeys($row);
        }
        return $row;
    }

    #[\ReturnTypeWillChange]
    public function fetchAll($mode = PDO::FETCH_DEFAULT, ...$args)
    {
        $rows = parent::fetchAll($mode, ...$args);
        if (is_array($rows)) {
            foreach ($rows as &$row) {
                if (is_array($row)) {
                    $this->mapKeys($row);
                }
            }
        }
        return $rows;
    }
}

?>
