<?php

declare(strict_types=1);

namespace Connection;

use mysqli;
use RuntimeException;

class db_connection
{
    private ?mysqli $connection = null;

    public function connect(): mysqli
    {
        if ($this->connection instanceof mysqli) {
            return $this->connection;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('DB_PORT') ?: 3306);
        $name = getenv('DB_NAME') ?: 'ticket_reservation';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        $this->connection = @new mysqli($host, $user, $pass, $name, $port);

        if ($this->connection->connect_errno) {
            throw new RuntimeException(
                sprintf(
                    'Database connection failed (%d): %s',
                    $this->connection->connect_errno,
                    $this->connection->connect_error
                )
            );
        }

        $this->connection->set_charset($charset);

        return $this->connection;
    }

    public function disconnect(): void
    {
        if ($this->connection instanceof mysqli) {
            $this->connection->close();
            $this->connection = null;
        }
    }
}
