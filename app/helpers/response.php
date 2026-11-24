<?php

function jsonResponse(
    bool $status,
    string $message = '',
    array $data = []
): void {

    echo json_encode([

        "status" => $status,

        "message" => $message,

        "data" => $data

    ]);

    exit();

}