<?php

namespace Users;

use Connection\db_connection;

class Passenger
{
    public $db;

    public function addInfo($user_id, $photo, $passportImg, $account)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();

        // Use prepared statement to avoid SQL injection
        $query = "INSERT INTO passengers (user_id, photo, passportImg, account) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($query);

        // Bind parameters
        $stmt->bind_param("isss", $user_id, $photo, $passportImg, $account);

        // Execute the statement
        $result = $stmt->execute();

        // Check for success
        if ($result) {
            echo("Data added successfully");
        } else {
            echo("Error adding the data: " . $stmt->error);
        }

        // Close the statement
        $stmt->close();

    }


    public function getAllInfo($user_id)
    {
        $userData = array();
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "select * from passengers, users where users.id = '$user_id'";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $userData = array(
                    "name" => $row['name'],
                    "email" => $row['email'],
                    "password" => $row['password'],
                    "tel" => $row['tel'],
                    "photo" => $row['photo'],
                    "passportImg" => $row['passportImg'],
                    "account" => $row['account']
                );
            }
        }
        return $userData;
    }

    public function editData($user_id, $name, $email, $password, $tel, $photo, $passportImg, $account)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();

        // Check if a new photo is provided
        if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
            // Process file upload for photo
            $targetDirectory = "uploads/";
            $targetFilePhoto = $targetDirectory . basename($photo['name']);

            if (move_uploaded_file($photo['tmp_name'], $targetFilePhoto)) {
                // File has been uploaded successfully
                $queryPhoto = "UPDATE passengers SET photo = ?, account = ? WHERE user_id = ?";
                $stmtPhoto = $mysqli->prepare($queryPhoto);
                $stmtPhoto->bind_param("ssi", $targetFilePhoto, $account, $user_id);
            } else {
                echo "Error uploading photo file.";
                return false;
            }
        } else {
            // No new photo provided, update without changing the photo path
            $queryPhoto = "UPDATE passengers SET account = ? WHERE user_id = ?";
            $stmtPhoto = $mysqli->prepare($queryPhoto);
            $stmtPhoto->bind_param("si", $account, $user_id);
        }

        // Check if a new passport image is provided
        if ($passportImg && $passportImg['error'] === UPLOAD_ERR_OK) {
            // Process file upload for passportImg
            $targetDirectory = "uploads/";
            $targetFilePassport = $targetDirectory . basename($passportImg['name']);

            if (move_uploaded_file($passportImg['tmp_name'], $targetFilePassport)) {
                // File has been uploaded successfully
                $queryPassport = "UPDATE passengers SET passportImg = ? WHERE user_id = ?";
                $stmtPassport = $mysqli->prepare($queryPassport);
                $stmtPassport->bind_param("si", $targetFilePassport, $user_id);
            } else {
                echo "Error uploading passport image file.";
                return false;
            }
        } else {
            // No new passport image provided, update without changing the passportImg path
            $queryPassport = "UPDATE passengers SET account = ? WHERE user_id = ?";
            $stmtPassport = $mysqli->prepare($queryPassport);
            $stmtPassport->bind_param("si", $account, $user_id);
        }

        // Execute the statements
        $resultPhoto = $stmtPhoto->execute();
        $resultPassport = $stmtPassport->execute();

        // Check for success
        if ($resultPhoto && $resultPassport) {
            echo "Data updated successfully";
        } else {
            echo "Failed updating the data: " . $mysqli->error;
        }

        // Close the statements
        $stmtPhoto->close();
        $stmtPassport->close();
    }


    public function listCompletedFlights($user_id)
    {
        $completedFlights = array();
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "select * from passengersOnFlight ,flights where passengerId = '$user_id' AND status = 1 AND flights.flightId = passengersOnFlight.flightId";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $flightsDetails = array(
                    "name" => $row['name'],
                    "itinerary" => $row['itinerary'],
                    'fees' => $row['fees'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'numPassengers' => $row['numPassengers']
                );
                $completedFlights[] = $flightsDetails;
            }
        }
        if (empty($completedFlights)) {
            return "No completed flights";
        } else {
            return $completedFlights;
        }
    }

    public function currentFlights($user_id)
    {
        $currentFlights = array();
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "select * from passengersOnFlight ,flights where passengerId = '$user_id' AND status = 0 AND flights.flightId = passengersOnFlight.flightId";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $flightsDetails = array(
                    "name" => $row['name'],
                    "itinerary" => $row['itinerary'],
                    'fees' => $row['fees'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'numPassengers' => $row['numPassengers']
                );
                $currentFlights[] = $flightsDetails;
            }
        }
        if (empty($currentFlights)) {
            return "No current flights";
        } else {
            return $currentFlights;
        }
    }

    public function searchFlight($from, $to)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "SELECT * FROM flights WHERE itinerary LIKE '%" . $mysqli->real_escape_string($from) . "%' AND itinerary LIKE '%" . $mysqli->real_escape_string($to) . "%'";
        $result = $mysqli->query($query);
        if ($result && $result->num_rows > 0) {
            // Output flight data
            while ($row = $result->fetch_assoc()) {
                $flightData = array(
                    "Flight ID: " => $row['flightId'],
                    "Flight Name: " => $row['name'],
                    'Fees:' => $row['fees'],
                    'itinerary: ' => $row['itinerary'],
                    'Start Date: ' => $row['start_time'],
                    'End Date: ' => $row['end_time'],
                    'Passengers:' => $row['numPassengers'],
                    'Completed:' => $row['completed']
                );
                $flights[] = $flightData;
            }
        }
        return $flights;
    }
    public function makePurchase($passengerId, $flightId, $paymentType)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();

        function updateCompanyAndFlightInfo($mysqli, $companyId, $flightId, $fees, $passengerId)
        {
            // Get and update company account
            $query = "SELECT account FROM companies WHERE user_id ='$companyId'";
            $result = $mysqli->query($query);
            $companyAccount = $result->fetch_assoc()['account'];
            $newAccount = $companyAccount + $fees;
            $query = "UPDATE companies SET account = '$newAccount' WHERE user_id='$companyId'";
            $mysqli->query($query);

            // Get and update flight's numPassengers
            $query = "SELECT numPassengers FROM flights WHERE flightId = '$flightId'";
            $result = $mysqli->query($query);
            $numPassengers = $result->fetch_assoc()['numPassengers'];
            $newPassengers = $numPassengers - 1;
            $query = "UPDATE flights SET numPassengers = '$newPassengers' WHERE flightId='$flightId'";
            $mysqli->query($query);

            // Insert into messages table
            $message = "The passenger $passengerId booked a flight ticket for flight $flightId with fees $fees";
            $query = "INSERT INTO messages (companyId, message) VALUES ('$companyId', '$message')";
            $result = $mysqli->query($query);
        }

        try {
            // Get passenger's account
            $query = "SELECT account FROM passengers WHERE user_id = '$passengerId'";
            $result = $mysqli->query($query);
            $account = $result->fetch_assoc()['account'];

            // Get flight fees
            $query = "SELECT fees FROM flights WHERE flightId = '$flightId'";
            $result = $mysqli->query($query);
            $fees = $result->fetch_assoc()['fees'];

            if ($paymentType == 'account' && $fees <= $account) {
                // Start a transaction
                $mysqli->begin_transaction();

                // Insert into passengersOnFlight table
                $query = "INSERT INTO passengersOnFlight (passengerId, flightId, status) VALUES ('$passengerId', '$flightId', 0)";
                $result = $mysqli->query($query);

                if ($result) {
                    // Get company information
                    $query = "SELECT companyId, numPassengers FROM flights WHERE flightId = '$flightId'";
                    $result = $mysqli->query($query);
                    $row = $result->fetch_assoc();
                    $companyId = $row['companyId'];
                    $numPassengers = $row['numPassengers'];

                    // Get and update company account
                    $query = "SELECT account FROM companies WHERE user_id ='$companyId'";
                    $result = $mysqli->query($query);
                    $companyAccount = $result->fetch_assoc()['account'];
                    $newAccount = $companyAccount + $fees;
                    $query = "UPDATE companies SET account = '$newAccount' WHERE user_id='$companyId'";
                    $mysqli->query($query);

                    // Update flight's numPassengers
                    $newPassengers = $numPassengers - 1;
                    $query = "UPDATE flights SET numPassengers = '$newPassengers' WHERE flightId='$flightId'";
                    $mysqli->query($query);

                    // Insert into messages table
                    $message = "The passenger $passengerId booked a flight ticket for flight $flightId with fees $fees";
                    $query = "INSERT INTO messages (companyId, message) VALUES ('$companyId', '$message')";
                    $result = $mysqli->query($query);

                    $query = "UPDATE passengers SET account = account - ? WHERE user_id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("di", $fees, $passengerId);
                    $stmt->execute();
                    $stmt->close();

                    // Commit the transaction
                    $mysqli->commit();

                    echo "Flight ticket booked successfully";
                } else {
                    // Rollback the transaction if the query fails
                    $mysqli->rollback();
                    echo "Error booking the ticket. Please try again";
                }
            } elseif ($paymentType == 'cash') {
                // Insert into passengersOnFlight table for cash payment
                $query = "INSERT INTO passengersOnFlight (passengerId, flightId, status) VALUES ('$passengerId', '$flightId', 0)";
                $result = $mysqli->query($query);

                if ($result) {
                    // Get company information
                    $query = "SELECT companyId FROM flights WHERE flightId = '$flightId'";
                    $result = $mysqli->query($query);
                    $row = $result->fetch_assoc();
                    $companyId = $row['companyId'];

                    // Call the function to update company and flight info
                    updateCompanyAndFlightInfo($mysqli, $companyId, $flightId, $fees, $passengerId);

                    echo "Flight ticket booked successfully";
                } else {
                    echo "Error booking the ticket. Please try again";
                }
            } else {
                echo "Not enough credit";
            }
        } catch (Exception $e) {
            // Rollback the transaction in case of an exception
            $mysqli->rollback();
            echo "Error booking the ticket. Please try again.";
            // Log the exception for debugging
            error_log($e->getMessage());
        }
    }
}