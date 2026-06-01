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

            # Retornar la conexión
            return $enlace;
        } catch (PDOException $e) {
            # Mostrar un error claro si no se puede conectar
            die("Error al conectar a la base de datos de Supabase: " . $e->getMessage());
        }
    }
}

?>
