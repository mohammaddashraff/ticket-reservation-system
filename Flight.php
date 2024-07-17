<?php

namespace Flight;

use Connection\db_connection;

class Flight
{
    public $db;

    public function add_flight(
        $name,
        $itinerary,
        $fees,
        $start_date,
        $end_date,
        $completed,
        $companyId,
        $numPassengers
    )
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();

        $query = "INSERT INTO flights (name, itinerary,fees, start_time, end_time, completed,companyId,numPassengers)
                    value ('$name', 
                    '$itinerary',
                    '$fees',
                    '$start_date',
                    '$end_date',
                    '$completed',
                    '$companyId',
                    '$numPassengers')";

        $result = $mysqli->query($query);

        if ($result) {
            echo 'Data inserted successfully';
        } else {
            // Check for errors in the query
            echo 'Something went wrong: ' . $mysqli->error;
        }

        $this->db->disconnect();

    }

    public function list_flight($flightid){
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "SELECT * FROM flights where flightId = '$flightid'";
        $result = $mysqli->query($query);

        if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $flightData = array(
                "Flight ID: " => $row['flightId'],
                "Flight Name: " => $row['name'],
                "Itinerary: " => $row['itinerary'],
                'Fees:' => $row['fees'],
                'Start Date: '=> $row['start_time'],
                'End Date: ' => $row['end_time'],
                'Passengers:' => $row['numPassengers'],
                'Completed:'=> $row['completed']
            );
        }
        $this->db->disconnect();
        return $flightData;
    }

    }

    public function list_flights($companyID)
    {
        $this->db = new db_connection();
        $mysqli = $this->db->connect();
        $query = "SELECT * FROM flights where companyId = '$companyID'";
        $result = $mysqli->query($query);
        $flights = array();

        if ($result && $result->num_rows > 0) {
            // Output flight data
            while ($row = $result->fetch_assoc()) {
                $flightData = array(
                    "Flight ID: " => $row['flightId'],
                    "Flight Name: " => $row['name'],
                    'Fees:' => $row['fees'],
                    'Start Date: '=> $row['start_time'],
                    'End Date: ' => $row['end_time'],
                    'Passengers:' => $row['numPassengers'],
                    'Completed:'=> $row['completed']
                );
                $flights[] = $flightData;
            }
            // foreach($flights as $flightdata){
            //     foreach($flightdata as $key => $value){
            //         echo $key . ": ". $value . "<br>";
            //     }
            //     echo "<br>";
            // }
            $this->db->disconnect();
            return $flights;
        } 
        $this->db->disconnect();
        
    }
}