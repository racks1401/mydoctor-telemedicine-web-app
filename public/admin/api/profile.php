<?php

session_start();

header("Content-Type: application/json");


require_once __DIR__ . "/../../../app/helpers/response.php";
require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/../../../app/services/admin/DashboardService.php";


try {

    if (!isset($_SESSION['user_id'])) {

        jsonResponse(
            false,
            "Unauthorized access"
        );

    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case "getProfileData":
            $data =
                DashboardService::getProfileData(
                    $conn,
                    $_SESSION['user_id']
                );


            jsonResponse(
                true,
                "Profile data fetched successfully",
                $data
            );
            break;

        case "updatePreference":
            $data = json_decode(file_get_contents("php://input"), true);
            $result  = DashboardService::updatePreference($conn, $_SESSION['user_id'], $data);
            jsonResponse(
                $result["status"],
                $result["message"]
            );
            break;

        case "changePassword":
            $data = json_decode(
                file_get_contents("php://input"),
                true
            );
            $result = DashboardService::changePassword($conn, $_SESSION['user_id'], $data);
            jsonResponse(
                $result["status"],
                $result["message"]
            );
            break;

        case "uploadPhoto":
            // ...
            break;
    }

    

} catch (Exception $e) {

    jsonResponse(
        false,
        $e->getMessage()
    );

}