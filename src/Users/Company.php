<?php

declare(strict_types=1);

namespace Users;

use Connection\db_connection;
use Throwable;

class Company
{
    public db_connection $db;

    public function __construct()
    {
        $this->db = new db_connection();
    }

    public function registerInfo($companyId, $companyName, $bio, $address, $location, $logoImg): bool
    {
        $mysqli = $this->db->connect();

        try {
            $query = "INSERT INTO companies (user_id, bio, address, location, companyName, logoImg) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("isssss", $companyId, $bio, $address, $location, $companyName, $logoImg);
            $result = $stmt->execute();
            $stmt->close();

            return (bool) $result;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getInfo($companyId): ?array
    {
        $mysqli = $this->db->connect();

        try {
            $query = "SELECT companyName, bio, address, location, account, logoImg FROM companies WHERE user_id = ? LIMIT 1";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $companyId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$row) {
                return null;
            }

            return array(
                "name" => $row['companyName'],
                "bio" => $row['bio'],
                'address' => $row['address'],
                'location' => $row['location'],
                'account' => $row['account'],
                'logoimg' => $row['logoImg'],
            );
        } finally {
            $this->db->disconnect();
        }
    }

    public function updateInfo($companyId, $companyName, $bio, $address, $location, $logoImg): bool
    {
        $mysqli = $this->db->connect();

        try {
            $newLogoPath = $this->tryUploadFile($logoImg);

            if ($newLogoPath !== null) {
                $query = "UPDATE companies SET bio = ?, address = ?, location = ?, logoImg = ?, companyName = ? WHERE user_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("sssssi", $bio, $address, $location, $newLogoPath, $companyName, $companyId);
            } else {
                $query = "UPDATE companies SET bio = ?, address = ?, location = ?, companyName = ? WHERE user_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("ssssi", $bio, $address, $location, $companyName, $companyId);
            }

            $result = $stmt->execute();
            $stmt->close();

            return (bool) $result;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return false;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<int, array{message:string}>
     */
    public function getMessages($user_id): array
    {
        $messages = array();
        $mysqli = $this->db->connect();

        try {
            $query = "SELECT message FROM messages WHERE companyId = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $messages[] = array('message' => (string) $row['message']);
                }
            }
            $stmt->close();

            return $messages;
        } finally {
            $this->db->disconnect();
        }
    }

    private function tryUploadFile($file): ?string
    {
        if (!is_array($file)) {
            return null;
        }

        if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if (!isset($file['tmp_name'], $file['name'])) {
            return null;
        }

        $targetDirectory = "uploads/";

        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $safeFilename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename((string) $file['name']));
        $targetFile = $targetDirectory . time() . '_' . $safeFilename;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetFile)) {
            return null;
        }

        return $targetFile;
    }
}
