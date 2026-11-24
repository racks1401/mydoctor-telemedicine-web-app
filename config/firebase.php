<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;
use Kreait\Firebase\Http\HttpClientOptions;

function getFirebaseFactory(): ?Factory {
    static $factory = null;
    if ($factory !== null) {
        return $factory;
    }
    $saPath = __DIR__ . '/../service-account.json';
    if (!file_exists($saPath)) {
        error_log("Firebase Error: service-account.json not found at " . $saPath);
        return null;
    }
    try {
        $httpOptions = HttpClientOptions::default()
            ->withTimeout(2.5)
            ->withConnectTimeout(2.0);

        $factory = (new Factory())
            ->withServiceAccount($saPath)
            ->withDatabaseUri('https://my-doctor-a20e8-default-rtdb.firebaseio.com')
            ->withHttpClientOptions($httpOptions);
        return $factory;
    } catch (\Throwable $e) {
        error_log("Firebase Factory Init Error: " . $e->getMessage());
        return null;
    }
}

function getFirebaseDatabase(): ?Database {
    static $database = null;
    if ($database !== null) {
        return $database;
    }
    $factory = getFirebaseFactory();
    if (!$factory) return null;
    try {
        $database = $factory->createDatabase();
        return $database;
    } catch (\Throwable $e) {
        error_log("Firebase Database Init Error: " . $e->getMessage());
        return null;
    }
}

function createFirebaseCustomToken(int|string $userId): ?string {
    try {
        $factory = getFirebaseFactory();
        if (!$factory) return null;
        $auth = $factory->createAuth();
        $token = $auth->createCustomToken((string)$userId);
        return method_exists($token, 'toString') ? $token->toString() : (string)$token;
    } catch (\Throwable $e) {
        error_log("Firebase Auth Custom Token Error: " . $e->getMessage());
        return null;
    }
}

function setConversationMembers(int $convId, int $doctorId, int $patientId): bool {
    try {
        $database = getFirebaseDatabase();
        if (!$database) return false;
        $database->getReference('members/' . $convId)->update([
            (string)$doctorId => true,
            (string)$patientId => true
        ]);
        return true;
    } catch (\Throwable $e) {
        error_log("Firebase setConversationMembers Error: " . $e->getMessage());
        return false;
    }
}

function pushChatMessageSignal(int $convId, int $messageId, int $senderId, ?int $receiverId = null, ?int $timestamp = null): bool {
    try {
        $database = getFirebaseDatabase();
        if (!$database) return false;
        $ts = $timestamp ?? time();

        // Generate unique push key locally (no network roundtrip)
        $newKey = $database->getReference('chats/' . $convId)->push()->getKey();

        // Single atomic multi-path update: 1 network roundtrip for active chat + receiver dashboard
        $updates = [
            "chats/{$convId}/{$newKey}" => [
                'id'        => (int)$messageId,
                'sender_id' => (int)$senderId,
                'ts'        => $ts
            ]
        ];

        if ($receiverId && $receiverId > 0) {
            $updates["dashboards/{$receiverId}/chats"] = $ts;
        }

        $database->getReference()->update($updates);
        return true;
    } catch (\Throwable $e) {
        error_log("Firebase pushChatMessageSignal Error: " . $e->getMessage());
        return false;
    }
}

function pushDashboardAppointmentSignal(int $userId): bool {
    try {
        $database = getFirebaseDatabase();
        if (!$database) return false;
        $database->getReference('dashboards/' . $userId)->update([
            'appointments' => time()
        ]);
        return true;
    } catch (\Throwable $e) {
        error_log("Firebase pushDashboardAppointmentSignal Error: " . $e->getMessage());
        return false;
    }
}

function setCallAllowedUntil(int $convId, int $allowedUntilMs): bool {
    try {
        $database = getFirebaseDatabase();
        if (!$database) return false;
        $database->getReference('calls/' . $convId . '/meta')->update([
            'allowedUntil' => $allowedUntilMs,
            'updatedAt'    => time() * 1000
        ]);
        return true;
    } catch (\Throwable $e) {
        error_log("Firebase setCallAllowedUntil Error: " . $e->getMessage());
        return false;
    }
}

function clearCallSignal(int $convId): bool {
    try {
        $database = getFirebaseDatabase();
        if (!$database) return false;
        $database->getReference('calls/' . $convId . '/signal')->remove();
        return true;
    } catch (\Throwable $e) {
        error_log("Firebase clearCallSignal Error: " . $e->getMessage());
        return false;
    }
}
