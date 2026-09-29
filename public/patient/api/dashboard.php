<?php

session_start();

header("Content-Type: application/json");


require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/patient/DashboardService.php";


try {

    if (!isset($_SESSION['user_id'])) {

        jsonResponse(
            false,
            "Unauthorized access"
        );

    }

    $data =
        DashboardService::getDashboardData(
            $conn,
            $_SESSION['user_id']
        );


    jsonResponse(
        true,
        "Dashboard data fetched successfully",
        $data
    );

} catch (Exception $e) {

    jsonResponse(
        false,
        $e->getMessage()
    );

}