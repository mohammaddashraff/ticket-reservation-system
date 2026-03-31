<?php

declare(strict_types=1);

namespace Users;

use Connection\db_connection;
use mysqli;
use Throwable;

class Passenger
{
    public db_connection $db;

    public function __construct()
    {
        $this->db = new db_connection();
    }

    public function addInfo($user_id, $photo, $passportImg, $account): bool
    {
        $mysqli = $this->db->connect();

        try {
            $query = "INSERT INTO passengers (user_id, photo, passportImg, account) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("isss", $user_id, $photo, $passportImg, $account);
            $result = $stmt->execute();
            $stmt->close();

            return (bool) $result;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getAllInfo($user_id): array
    {
        $userData = array();
        $mysqli = $this->db->connect();

        try {
            $query = "SELECT users.name, users.email, users.password, users.tel, passengers.photo, passengers.passportImg, passengers.account
                      FROM users
                      INNER JOIN passengers ON passengers.user_id = users.id
                      WHERE users.id = ?
                      LIMIT 1";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if ($row) {
                $userData = array(
                    "name" => $row['name'],
                    "email" => $row['email'],
                    "password" => $row['password'],
                    "tel" => $row['tel'],
                    "photo" => $row['photo'],
                    "passportImg" => $row['passportImg'],
                    "account" => $row['account'],
                );
            }

            return $userData;
        } finally {
            $this->db->disconnect();
        }
    }

    public function editData($user_id, $name, $email, $password, $tel, $photo, $passportImg, $account): bool
    {
        $mysqli = $this->db->connect();

        try {
            $mysqli->begin_transaction();

            if (!empty($password)) {
                $hashedPassword = password_hash((string) $password, PASSWORD_BCRYPT);
                $queryUser = "UPDATE users SET name = ?, email = ?, password = ?, tel = ? WHERE id = ?";
                $stmtUser = $mysqli->prepare($queryUser);
                $stmtUser->bind_param("ssssi", $name, $email, $hashedPassword, $tel, $user_id);
            } else {
                $queryUser = "UPDATE users SET name = ?, email = ?, tel = ? WHERE id = ?";
                $stmtUser = $mysqli->prepare($queryUser);
                $stmtUser->bind_param("sssi", $name, $email, $tel, $user_id);
            }

            $userUpdated = $stmtUser->execute();
            $stmtUser->close();

            if (!$userUpdated) {
                $mysqli->rollback();
                return false;
            }

            $photoPath = $this->tryUploadFile($photo);
            $passportPath = $this->tryUploadFile($passportImg);

            $queryPassenger = "UPDATE passengers SET account = ?, photo = COALESCE(?, photo), passportImg = COALESCE(?, passportImg) WHERE user_id = ?";
            $stmtPassenger = $mysqli->prepare($queryPassenger);
            $stmtPassenger->bind_param("sssi", $account, $photoPath, $passportPath, $user_id);
            $passengerUpdated = $stmtPassenger->execute();
            $stmtPassenger->close();

            if (!$passengerUpdated) {
                $mysqli->rollback();
                return false;
            }

            $mysqli->commit();
            return true;
        } catch (Throwable $exception) {
            $mysqli->rollback();
            error_log($exception->getMessage());
            return false;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<int, array<string, mixed>>|string
     */
    public function listCompletedFlights($user_id)
    {
        try {
            $completedFlights = $this->listFlightsByStatus($user_id, 1);

            if (empty($completedFlights)) {
                return "No completed flights";
            }

            return $completedFlights;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<int, array<string, mixed>>|string
     */
    public function currentFlights($user_id)
    {
        try {
            $currentFlights = $this->listFlightsByStatus($user_id, 0);

            if (empty($currentFlights)) {
                return "No current flights";
            }

            return $currentFlights;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchFlight($from, $to): array
    {
        $flights = array();
        $mysqli = $this->db->connect();

        try {
            $fromPattern = '%' . (string) $from . '%';
            $toPattern = '%' . (string) $to . '%';

            $query = "SELECT * FROM flights WHERE itinerary LIKE ? AND itinerary LIKE ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ss", $fromPattern, $toPattern);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $flights[] = array(
                        "Flight ID: " => $row['flightId'],
                        "Flight Name: " => $row['name'],
                        'Fees:' => $row['fees'],
                        'itinerary: ' => $row['itinerary'],
                        'Start Date: ' => $row['start_time'],
                        'End Date: ' => $row['end_time'],
                        'Passengers:' => $row['numPassengers'],
                        'Completed:' => $row['completed'],
                    );
                }
            }
            $stmt->close();

            return $flights;
        } finally {
            $this->db->disconnect();
        }
    }

    public function makePurchase($passengerId, $flightId, $paymentType): bool
    {
        $mysqli = $this->db->connect();

        try {
            $flightQuery = "SELECT companyId, fees, numPassengers FROM flights WHERE flightId = ? LIMIT 1";
            $flightStmt = $mysqli->prepare($flightQuery);
            $flightStmt->bind_param("i", $flightId);
            $flightStmt->execute();
            $flightResult = $flightStmt->get_result();
            $flightRow = $flightResult ? $flightResult->fetch_assoc() : null;
            $flightStmt->close();

            if (!$flightRow) {
                return false;
            }

            $companyId = (int) $flightRow['companyId'];
            $fees = (float) $flightRow['fees'];
            $availableSeats = (int) $flightRow['numPassengers'];

            if ($availableSeats <= 0) {
                return false;
            }

            if ($paymentType === 'account') {
                $accountQuery = "SELECT account FROM passengers WHERE user_id = ? LIMIT 1";
                $accountStmt = $mysqli->prepare($accountQuery);
                $accountStmt->bind_param("i", $passengerId);
                $accountStmt->execute();
                $accountResult = $accountStmt->get_result();
                $accountRow = $accountResult ? $accountResult->fetch_assoc() : null;
                $accountStmt->close();

                if (!$accountRow || (float) $accountRow['account'] < $fees) {
                    return false;
                }
            }

            if ($paymentType !== 'account' && $paymentType !== 'cash') {
                return false;
            }

            $mysqli->begin_transaction();

            $insertQuery = "INSERT INTO passengersOnFlight (passengerId, flightId, status) VALUES (?, ?, 0)";
            $insertStmt = $mysqli->prepare($insertQuery);
            $insertStmt->bind_param("ii", $passengerId, $flightId);
            $inserted = $insertStmt->execute();
            $insertStmt->close();

            if (!$inserted) {
                $mysqli->rollback();
                return false;
            }

            if (!$this->updateCompanyAndFlightInfo($mysqli, $companyId, $flightId, $fees, $passengerId)) {
                $mysqli->rollback();
                return false;
            }

            if ($paymentType === 'account') {
                $debitQuery = "UPDATE passengers SET account = account - ? WHERE user_id = ?";
                $debitStmt = $mysqli->prepare($debitQuery);
                $debitStmt->bind_param("di", $fees, $passengerId);
                $debited = $debitStmt->execute();
                $debitStmt->close();

                if (!$debited) {
                    $mysqli->rollback();
                    return false;
                }
            }

            $mysqli->commit();
            return true;
        } catch (Throwable $exception) {
            $mysqli->rollback();
            error_log($exception->getMessage());
            return false;
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listFlightsByStatus($user_id, int $status): array
    {
        $flights = array();
        $mysqli = $this->db->connect();

        $query = "SELECT flights.name, flights.itinerary, flights.fees, flights.start_time, flights.end_time, flights.numPassengers
                  FROM passengersOnFlight
                  INNER JOIN flights ON flights.flightId = passengersOnFlight.flightId
                  WHERE passengersOnFlight.passengerId = ? AND passengersOnFlight.status = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ii", $user_id, $status);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $flights[] = array(
                    "name" => $row['name'],
                    "itinerary" => $row['itinerary'],
                    'fees' => $row['fees'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'numPassengers' => $row['numPassengers'],
                );
            }
        }
        $stmt->close();

        return $flights;
    }

    private function updateCompanyAndFlightInfo(mysqli $mysqli, int $companyId, int $flightId, float $fees, int $passengerId): bool
    {
        $companyQuery = "UPDATE companies SET account = account + ? WHERE user_id = ?";
        $companyStmt = $mysqli->prepare($companyQuery);
        $companyStmt->bind_param("di", $fees, $companyId);
        $companyUpdated = $companyStmt->execute();
        $companyStmt->close();

        if (!$companyUpdated) {
            return false;
        }

        $seatQuery = "UPDATE flights SET numPassengers = numPassengers - 1 WHERE flightId = ? AND numPassengers > 0";
        $seatStmt = $mysqli->prepare($seatQuery);
        $seatStmt->bind_param("i", $flightId);
        $seatUpdated = $seatStmt->execute();
        $affectedRows = $seatStmt->affected_rows;
        $seatStmt->close();

        if (!$seatUpdated || $affectedRows <= 0) {
            return false;
        }

        $message = sprintf(
            'Passenger %d booked a flight ticket for flight %d with fees %.2f',
            $passengerId,
            $flightId,
            $fees
        );
        $messageQuery = "INSERT INTO messages (companyId, message) VALUES (?, ?)";
        $messageStmt = $mysqli->prepare($messageQuery);
        $messageStmt->bind_param("is", $companyId, $message);
        $messageInserted = $messageStmt->execute();
        $messageStmt->close();

        return (bool) $messageInserted;
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
