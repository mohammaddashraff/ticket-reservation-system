<?php


namespace Users;




use Connection\db_connection;

class User
{
    public $db;

    public function Register($name, $email, $password, $tel, $type)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        // Check if the email already exists
        $checkQuery = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $mysqli->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            echo 'Email already exists';
            $this->db->disconnect();
            return null;
        }
        // Insert into users table
        $query = "INSERT INTO users (name, email, password, tel, type) VALUES ('$name', '$email', '$password', '$tel', '$type')";
        $result = $mysqli->query($query);
        if ($result) {
            // Registration in passengers or companies table successful
            echo 'Registration successful';
        } else {
            // Handle the case where the second insert fails
            echo 'Registration failed';
        }
        $Query = "SELECT id FROM users WHERE email = '$email'";
        $result = $mysqli->query($Query);
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        var_dump($user_id);
        $this->db->disconnect();
        return $user_id;
    }


    public function signIn($email, $password)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "SELECT id, type FROM users WHERE email = '$email' AND password = '$password'";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            // User found, login successful
            $row = $result->fetch_assoc();
            $userData = array(
                'id' => $row['id'],
                'type' => $row['type']
            );
            echo 'Login successful';
            $this->db->disconnect();
            return $userData;
        } else {
            // User not found or incorrect password, login failed
            echo 'Login failed';
            $this->db->disconnect();
            return null;
        }
    }
}