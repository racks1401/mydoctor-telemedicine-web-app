<?php
/**
 * Patient Prescription Service
 * Retrieves digital prescriptions, medications, lab tests, doctor advice,
 * and handles search/filtering for the patient's Prescriptions tab.
 */

require_once __DIR__ . '/../../models/ChatModel.php';

class PrescriptionService {

    public static function getPatientPrescriptions(mysqli $conn, int $patientId, ?string $search = null): array {
        date_default_timezone_set('Asia/Kolkata');

        $search = trim($search ?? '');
        $where = "WHERE a.patient_id = ?";
        $params = [$patientId];
        $types = "i";

        if ($search !== '') {
            $where .= " AND (doc.name LIKE ? OR pr.diagnosis LIKE ? OR dinfo.specialization LIKE ? OR pr.medicines LIKE ?)";
            $sp = "%{$search}%";
            $params = array_merge($params, [$sp, $sp, $sp, $sp]);
            $types .= "ssss";
        }

        $sql = "
            SELECT 
                pr.prescription_id,
                pr.appointment_id,
                pr.diagnosis,
                pr.medicines,
                pr.lab_tests,
                pr.advice,
                pr.follow_up_date,
                pr.created_at AS prescription_date,
                a.appointment_date,
                a.appointment_time,
                a.appointment_mode,
                a.problem_description,
                doc.user_id AS doctor_id,
                doc.name AS doctor_name,
                doc.profile_url AS doctor_avatar,
                doc.phone AS doctor_phone,
                dinfo.specialization AS doctor_specialization,
                dinfo.qualification AS doctor_qualification,
                dinfo.medical_reg_no AS doctor_reg_no,
                c.conversation_id AS linked_conv_id
            FROM prescriptions pr
            INNER JOIN appointments a ON pr.appointment_id = a.appointment_id
            INNER JOIN users doc ON a.doctor_id = doc.user_id
            LEFT JOIN doctors dinfo ON doc.user_id = dinfo.user_id
            LEFT JOIN conversations c ON c.appointment_id = a.appointment_id
            {$where}
            ORDER BY pr.created_at DESC
        ";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $prescriptionsList = [];
        $totalMedicinesCount = 0;
        $uniqueDoctors = [];

        foreach ($rows as $row) {
            $meds = [];
            if (!empty($row['medicines'])) {
                $decoded = json_decode($row['medicines'], true);
                if (is_array($decoded)) {
                    $meds = $decoded;
                }
            }
            $totalMedicinesCount += count($meds);
            $uniqueDoctors[$row['doctor_id']] = true;

            $createdTs = strtotime($row['prescription_date']);
            $formattedDate = date('M d, Y', $createdTs);
            $formattedTime = date('h:i A', $createdTs);

            $fuFormatted = null;
            if (!empty($row['follow_up_date']) && $row['follow_up_date'] !== '0000-00-00') {
                $fuFormatted = date('M d, Y', strtotime($row['follow_up_date']));
            }

            $docName = $row['doctor_name'];
            if (!empty($docName) && !str_starts_with(strtolower($docName), 'dr.')) {
                $docDisplayName = "Dr. " . $docName;
            } else {
                $docDisplayName = $docName ?: "Doctor";
            }

            $prescriptionsList[] = [
                'prescription_id'       => (int)$row['prescription_id'],
                'appointment_id'        => (int)$row['appointment_id'],
                'conversation_id'       => (int)($row['linked_conv_id'] ?? 0),
                'doctor_id'             => (int)$row['doctor_id'],
                'doctor_name'           => $docDisplayName,
                'doctor_avatar'         => ChatModel::formatProfileUrl($row['doctor_avatar']),
                'doctor_initials'       => ChatModel::getInitials($docName),
                'doctor_phone'          => $row['doctor_phone'] ?: '',
                'doctor_specialization' => $row['doctor_specialization'] ?: 'General Physician',
                'doctor_qualification'  => $row['doctor_qualification'] ?: 'MBBS, MD',
                'doctor_reg_no'         => $row['doctor_reg_no'] ?: 'MED-' . (1000 + (int)$row['doctor_id']),
                'diagnosis'             => $row['diagnosis'] ?: 'Routine Clinical Consultation',
                'medicines'             => $meds,
                'medicines_count'       => count($meds),
                'lab_tests'             => $row['lab_tests'] ?: '',
                'advice'                => $row['advice'] ?: 'Follow standard precautions and complete the medication course.',
                'follow_up_date'        => $fuFormatted,
                'date_formatted'        => $formattedDate,
                'time_formatted'        => $formattedTime,
                'appointment_date'      => date('M d, Y', strtotime($row['appointment_date'])),
                'appointment_time'      => date('h:i A', strtotime($row['appointment_time'])),
                'appointment_mode'      => $row['appointment_mode']
            ];
        }

        return [
            'metrics' => [
                'total_prescriptions'   => count($prescriptionsList),
                'total_medicines'       => $totalMedicinesCount,
                'attending_doctors'     => count($uniqueDoctors)
            ],
            'prescriptions' => $prescriptionsList,
            'total_count'   => count($prescriptionsList)
        ];
    }
}
