<?php

declare(strict_types=1);

namespace Flight;

use Connection\db_connection;
use Throwable;

class Flight
{
    public db_connection $db;

    public function __construct()
    {
        $this->db = new db_connection();
    }

    public function add_flight(
        $name,
        $itinerary,
        $fees,
        $start_date,
        $end_date,
        $completed,
        $companyId,
        $numPassengers
    ): bool {
        $mysqli = $this->db->connect();

        try {
            $query = "INSERT INTO flights (name, itinerary, fees, start_time, end_time, completed, companyId, numPassengers)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param(
                "sssssiii",
                $name,
                $itinerary,
                $fees,
                $start_date,
                $end_date,
                $completed,
                $companyId,
                $numPassengers
            );
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
     * @return array<string, mixed>|null
     */
    public function list_flight($flightid): ?array
    {
        $mysqli = $this->db->connect();

        try {
            $query = "SELECT * FROM flights WHERE flightId = ? LIMIT 1";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $flightid);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result ? $result->fetch_assoc() : null;
            $stmt->close();

            if (!$row) {
                return null;
            }

            return array(
                "Flight ID: " => $row['flightId'],
                "Flight Name: " => $row['name'],
                "Itinerary: " => $row['itinerary'],
                'Fees:' => $row['fees'],
                'Start Date: ' => $row['start_time'],
                'End Date: ' => $row['end_time'],
                'Passengers:' => $row['numPassengers'],
                'Completed:' => $row['completed'],
            );
        } finally {
            $this->db->disconnect();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list_flights($companyID): array
    {
        $flights = array();
        $mysqli = $this->db->connect();

        try {
            $query = "SELECT * FROM flights WHERE companyId = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("i", $companyID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $flights[] = array(
                        "Flight ID: " => $row['flightId'],
                        "Flight Name: " => $row['name'],
                        'Fees:' => $row['fees'],
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
}
