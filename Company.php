<?php

namespace Users;

use Connection\db_connection;

class Company
{

    public $db;
    public function registerInfo($companyId, $companyName, $bio, $address, $location, $logoImg)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();

        // Use prepared statement to avoid SQL injection
        $query = "INSERT INTO companies (user_id, bio, address, location, companyName, logoImg) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);

        // Bind parameters
        $stmt->bind_param("isssss", $companyId, $bio, $address, $location, $companyName, $logoImg);

        // Execute the statement
        $result = $stmt->execute();

        // Check for success
        if ($result) {
            echo ("Info added successfully");
            return true;
        } else {
            echo ("Error adding the info: " . $stmt->error);
            return false;
        }

        // Close the statement
        $stmt->close();
    }


    public function getInfo($companyId)
    {
        $this->db= new db_connection();
        $mysqli = $this->db->connect();
        $query = "SELECT * FROM companies where user_id = '$companyId'";
        $result = $mysqli->query($query);
        $companyData = array();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $companyData = array(
                    "name" => $row['companyName'],
                    "bio" => $row['bio'],
                    'address' => $row['address'],
                    'location'=> $row['location'],
                    'account' => $row['account'],
                    'logoimg'=> $row['logoImg']
                );
            }
            //var_dump($companyData);
            return $companyData;
        } else {
            echo 'No Company found.';
            return null;
        }
        $this->db->disconnect();
    }

    public function updateInfo($companyId,$companyName, $bio, $address, $location, $logoImg)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();

        // Check if a new image is provided
        if ($logoImg && $logoImg['error'] === UPLOAD_ERR_OK) {
            // Process file upload for logo
            $targetDirectory = "uploads/";
            $targetFile = $targetDirectory . basename($logoImg['name']);

            if (move_uploaded_file($logoImg['tmp_name'], $targetFile)) {
                // File has been uploaded successfully
                $query = "UPDATE companies SET bio = ?, address = ?, location = ?, logoImg = ?, companyName = ? WHERE user_id = ?";
                $stmt = $mysqli->prepare($query);
                $stmt->bind_param("sssssi", $bio, $address, $location, $targetFile, $companyName, $companyId);
            } else {
                echo "Error uploading file.";
                return false;
            }
        } else {
            // No new image provided, update without changing the image path
            $query = "UPDATE companies SET bio = ?, address = ?, location = ?, companyName = ? WHERE user_id = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssssi", $bio, $address, $location, $companyName, $companyId);
        }

        // Execute the statement
        $result = $stmt->execute();

        // Check for success
        if ($result) {
            echo "Data updated correctly";
        } else {
            echo "Failed updating the data: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

    public function getMessages($user_id)
    {
        $messages = array();
        $this->db= new db_connection();
        $mysqli = $this->db->connect();
        $query = "select message from messages where companyId ='$user_id'";
        $result = $mysqli->query($query);
        if($result && $result->num_rows>0){
            while( $row = $result->fetch_assoc()){
                $messageData = array('message' => $row['message']);
                $messages[] = $messageData;
            }
        }
        return $messages;
    }

}