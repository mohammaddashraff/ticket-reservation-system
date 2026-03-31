<?php

declare(strict_types=1);

namespace Users;

use Connection\db_connection;
use Throwable;

class User
{
    public db_connection $db;

    public function __construct()
    {
        $this->db = new db_connection();
    }

    /**
     * Register a new account and return its user id.
     *
     * @return int|null
     */
    public function Register($name, $email, $password, $tel, $type)
    {
        $mysqli = $this->db->connect();

        try {
            $checkQuery = "SELECT id FROM users WHERE email = ? LIMIT 1";
            $checkStmt = $mysqli->prepare($checkQuery);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $checkStmt->close();
                return null;
            }
            $checkStmt->close();

            $hashedPassword = password_hash((string) $password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, email, password, tel, type) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("sssss", $name, $email, $hashedPassword, $tel, $type);
            $result = $stmt->execute();
            $insertedId = $result ? $stmt->insert_id : null;
            $stmt->close();

            return $insertedId;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * Authenticate by email/password.
     * Supports both hashed and legacy plain-text passwords.
     *
     * @return array{id:int, type:string}|null
     */
    public function signIn($email, $password): ?array
    {
        $mysqli = $this->db->connect();

        try {
            $query = "SELECT id, type, password FROM users WHERE email = ? LIMIT 1";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$row) {
                return null;
            }

            $storedPassword = (string) $row['password'];
            $providedPassword = (string) $password;

            // Backward compatibility with old plain-text records.
            $matches = password_verify($providedPassword, $storedPassword)
                || hash_equals($storedPassword, $providedPassword);

            if (!$matches) {
                return null;
            }

            return array(
                'id' => (int) $row['id'],
                'type' => (string) $row['type'],
            );
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return null;
        } finally {
            $this->db->disconnect();
        }
    }
}
