<?php
/**
 * Authentication and Authorization Helper
 * Handles session auth, API token auth, and conversation membership checks.
 */

require_once __DIR__ . '/response.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Returns current authenticated user ID or null.
 * Only the server-side PHP session is trusted. Client-supplied headers
 * (X-User-Id, unsigned "Bearer uid_<id>" tokens) are NOT accepted, because
 * anyone could send them and impersonate another user. A future mobile/API
 * client must use a properly signed and verified token instead.
 *
 * @return int|null
 */
function me(): ?int {
    if (!empty($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    return null;
}

/**
 * Requires a logged-in user of the given role ('admin', 'doctor', 'patient'),
 * or halts with a 401/403 JSON error.
 *
 * @param string $role
 * @return int User ID
 */
function requireRole(string $role): int {
    $userId = me();
    if (!$userId) {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access. Please login first.");
    }
    if (strtolower($_SESSION['usertype'] ?? '') !== strtolower($role)) {
        http_response_code(403);
        jsonResponse(false, "Forbidden: this action requires a {$role} account.");
    }
    return $userId;
}

/**
 * Requires an authenticated user, or halts with 401 JSON error
 *
 * @return int User ID
 */
function requireAuth(): int {
    $userId = me();
    if (!$userId) {
        http_response_code(401);
        jsonResponse(false, "Unauthorized access. Please login first.");
    }
    return $userId;
}

/**
 * Requires that the current user is a member of the given conversation
 *
 * @param array $conversation Must contain 'doctor_id' and 'patient_id'
 * @param int|null $userId Optional user ID override
 * @return void
 */
function requireMember(array $conversation, ?int $userId = null): void {
    if ($userId === null) {
        $userId = requireAuth();
    }

    $docId = (int)($conversation['doctor_id'] ?? 0);
    $patId = (int)($conversation['patient_id'] ?? 0);

    if ($userId !== $docId && $userId !== $patId) {
        http_response_code(403);
        jsonResponse(false, "Forbidden: You are not a participant in this consultation conversation.");
    }
}

/**
 * Generates or retrieves the active CSRF token for the session
 *
 * @return string
 */
function getCsrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifies CSRF token from header or request body
 *
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token = null): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $sessionToken = $_SESSION['csrf_token'] ?? '';
    if (empty($sessionToken)) {
        return false;
    }

    $clientToken = $token 
        ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
        ?? $_POST['csrf_token'] 
        ?? '';

    if (empty($clientToken)) {
        return false;
    }

    return hash_equals($sessionToken, $clientToken);
}

/**
 * Enforces CSRF token check for state-changing HTTP requests
 *
 * @return void
 */
function requireCsrf(): void {
    if (!verifyCsrfToken()) {
        http_response_code(403);
        jsonResponse(false, "Invalid or expired CSRF security token.");
    }
}
