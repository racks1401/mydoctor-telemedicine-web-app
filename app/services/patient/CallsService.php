<?php
/**
 * Patient Calls Service
 * Powers the Patient Calls tab with WhatsApp-style call history, direction indicators,
 * filter support, and real-time consultation metric cards.
 */

require_once __DIR__ . '/../../models/ChatModel.php';

class PatientCallsService {

    public static function getCallsData(mysqli $conn, int $patientId, array $options = []): array {
        date_default_timezone_set('Asia/Kolkata');

        // 1. Overall Metrics (Top Cards)
        $stmt = $conn->prepare("
            SELECT
                COALESCE(SUM(duration_seconds), 0) AS total_seconds,
                COUNT(*) AS total_calls,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_calls,
                SUM(CASE WHEN status IN ('missed', 'cancelled') THEN 1 ELSE 0 END) AS missed_calls,
                SUM(CASE WHEN call_type = 'video_call' THEN 1 ELSE 0 END) AS video_calls,
                SUM(CASE WHEN call_type = 'audio_call' THEN 1 ELSE 0 END) AS audio_calls,
                AVG(CASE WHEN status = 'completed' AND quality_rating > 0 THEN quality_rating ELSE NULL END) AS avg_rating
            FROM call_logs
            WHERE patient_id = ?
        ");
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        $metricsRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $totalSeconds = (int)($metricsRow['total_seconds'] ?? 0);
        $totalMinutes = round($totalSeconds / 60);
        $totalCalls = (int)($metricsRow['total_calls'] ?? 0);
        $completedCalls = (int)($metricsRow['completed_calls'] ?? 0);
        $missedCalls = (int)($metricsRow['missed_calls'] ?? 0);
        $videoCalls = (int)($metricsRow['video_calls'] ?? 0);
        $audioCalls = (int)($metricsRow['audio_calls'] ?? 0);
        $avgRating = (float)($metricsRow['avg_rating'] ?: 4.9);

        $videoPct = ($totalCalls > 0) ? round(($videoCalls / $totalCalls) * 100) : 70;
        $audioPct = ($totalCalls > 0) ? (100 - $videoPct) : 30;
        $completionRate = ($totalCalls > 0) ? round(($completedCalls / $totalCalls) * 100) : 100;

        // 2. Fetch Call Logs
        $typeFilter = $options['type'] ?? 'all'; // all, video, audio, missed
        $search = trim($options['search'] ?? '');

        $whereClause = "WHERE cl.patient_id = ?";
        $params = [$patientId];
        $types = "i";

        if ($typeFilter === 'video') {
            $whereClause .= " AND cl.call_type = 'video_call'";
        } elseif ($typeFilter === 'audio') {
            $whereClause .= " AND cl.call_type = 'audio_call'";
        } elseif ($typeFilter === 'missed') {
            $whereClause .= " AND cl.status IN ('missed', 'cancelled')";
        }

        if ($search !== '') {
            $whereClause .= " AND (u.name LIKE ? OR d.specialization LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }

        $sql = "
            SELECT
                cl.id AS call_id,
                cl.conversation_id,
                cl.appointment_id,
                cl.doctor_id,
                cl.patient_id,
                cl.caller_id,
                cl.receiver_id,
                cl.call_type,
                cl.status,
                cl.duration_seconds,
                cl.quality_rating,
                cl.started_at,
                cl.ended_at,
                u.name AS doctor_name,
                u.profile_url AS doctor_avatar,
                u.phone AS doctor_phone,
                d.specialization AS doctor_specialization,
                a.problem_description,
                a.prescription,
                c.conversation_id AS linked_conv_id
            FROM call_logs cl
            JOIN users u ON cl.doctor_id = u.user_id
            LEFT JOIN doctors d ON u.user_id = d.user_id
            LEFT JOIN appointments a ON cl.appointment_id = a.appointment_id
            LEFT JOIN conversations c ON c.appointment_id = a.appointment_id
            {$whereClause}
            ORDER BY cl.started_at DESC
            LIMIT 50
        ";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rawCalls = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $callsList = [];
        $todayStr = date('Y-m-d');
        $yesterdayStr = date('Y-m-d', strtotime('-1 day'));

        foreach ($rawCalls as $row) {
            $startedTs = strtotime($row['started_at']);
            $callDate = date('Y-m-d', $startedTs);
            $callTimeFormatted = date('h:i A', $startedTs);

            if ($callDate === $todayStr) {
                $formattedDate = "Today";
            } elseif ($callDate === $yesterdayStr) {
                $formattedDate = "Yesterday";
            } else {
                $formattedDate = date('M d, Y', $startedTs);
            }

            $isMissed = in_array($row['status'], ['missed', 'cancelled'], true);
            $durationSec = (int)$row['duration_seconds'];

            if ($isMissed) {
                $durationFormatted = ($row['status'] === 'cancelled') ? 'Cancelled' : 'Missed';
            } elseif ($durationSec >= 60) {
                $durationFormatted = round($durationSec / 60) . ' mins';
            } else {
                $durationFormatted = max(5, $durationSec) . ' secs';
            }

            // Direction: Outgoing = Patient called Doctor; Incoming = Doctor called Patient
            $direction = ((int)$row['caller_id'] === $patientId) ? 'outgoing' : 'incoming';

            // Rating / Quality label
            $ratingVal = (float)$row['quality_rating'];
            if ($ratingVal >= 4.5) {
                $qualityLabel = 'Excellent';
                $qualityClass = 'badge-success';
            } elseif ($ratingVal >= 3.5) {
                $qualityLabel = 'Good';
                $qualityClass = 'badge-blue';
            } else {
                $qualityLabel = 'Fair';
                $qualityClass = 'badge-warning';
            }

            $convId = (int)($row['conversation_id'] ?: $row['linked_conv_id'] ?: 0);
            $docName = $row['doctor_name'];
            if (!empty($docName) && !str_starts_with(strtolower($docName), 'dr.')) {
                $docDisplayName = "Dr. " . $docName;
            } else {
                $docDisplayName = $docName ?: "Doctor";
            }

            $callsList[] = [
                'call_id'               => (int)$row['call_id'],
                'appointment_id'        => (int)$row['appointment_id'],
                'conversation_id'       => $convId,
                'doctor_id'             => (int)$row['doctor_id'],
                'doctor_name'           => $docDisplayName,
                'doctor_specialization' => $row['doctor_specialization'] ?: 'General Physician',
                'doctor_avatar'         => ChatModel::formatProfileUrl($row['doctor_avatar']),
                'doctor_phone'          => $row['doctor_phone'] ?: '',
                'initials'              => ChatModel::getInitials($docName),
                'call_type'             => $row['call_type'], // video_call or audio_call
                'call_type_label'       => ($row['call_type'] === 'video_call') ? 'Video' : 'Audio',
                'call_direction'        => $direction, // outgoing or incoming
                'is_missed'             => $isMissed,
                'status'                => $row['status'],
                'started_at'            => $row['started_at'],
                'date_formatted'        => $formattedDate,
                'time_formatted'        => $callTimeFormatted,
                'duration_seconds'      => $durationSec,
                'duration_formatted'    => $durationFormatted,
                'quality_rating'        => $ratingVal,
                'quality_label'         => $qualityLabel,
                'quality_class'         => $qualityClass,
                'problem'               => $row['problem_description'] ?: 'Medical Consultation',
                'has_prescription'      => !empty($row['prescription'])
            ];
        }

        return [
            'metrics' => [
                'total_minutes'           => number_format($totalMinutes),
                'total_calls'             => $totalCalls,
                'completed_calls'         => $completedCalls,
                'completion_rate'         => $completionRate,
                'missed_calls'            => $missedCalls,
                'avg_quality'             => number_format($avgRating, 1),
                'video_calls'             => $videoCalls,
                'audio_calls'             => $audioCalls,
                'video_percentage'        => $videoPct,
                'audio_percentage'        => $audioPct,
                'subtitle_consultations'  => "Across {$totalCalls} medical consultations"
            ],
            'calls' => $callsList,
            'total_count' => count($callsList)
        ];
    }
}
