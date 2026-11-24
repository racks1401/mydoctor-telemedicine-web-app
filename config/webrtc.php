<?php
/**
 * WebRTC Configuration
 * Provides ICE servers (STUN default, optional TURN) for video/audio consultations.
 */

if (!function_exists('getWebRtcIceServers')) {
    function getWebRtcIceServers(): array {
        $iceServers = [
            [
                'urls' => [
                    'stun:stun.l.google.com:19302',
                    'stun:stun1.l.google.com:19302'
                ]
            ]
        ];

        // Configurable TURN fields (can be set via environment or customized later)
        $turnUrls = getenv('WEBRTC_TURN_URLS') ?: '';
        $turnUsername = getenv('WEBRTC_TURN_USERNAME') ?: '';
        $turnCredential = getenv('WEBRTC_TURN_CREDENTIAL') ?: '';

        if (!empty($turnUrls)) {
            $turnEntry = [
                'urls' => is_array($turnUrls) ? $turnUrls : explode(',', $turnUrls)
            ];
            if (!empty($turnUsername)) {
                $turnEntry['username'] = $turnUsername;
            }
            if (!empty($turnCredential)) {
                $turnEntry['credential'] = $turnCredential;
            }
            $iceServers[] = $turnEntry;
        }

        return $iceServers;
    }
}
