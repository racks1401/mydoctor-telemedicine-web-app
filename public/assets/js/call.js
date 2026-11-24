/**
 * MyDoctor - WebRTC 1-to-1 Video/Audio Call Client
 * Handles signaling via Firebase RTDB, ICE candidate queuing, audio-only fallback,
 * multi-tab detection, and consultation window auto-termination.
 * Vanilla JavaScript (No innerHTML for dynamic text).
 */

(function (window) {
    'use strict';

    class DoctorCallSession {
        constructor(options = {}) {
            this.conversationId = options.conversationId ? parseInt(options.conversationId, 10) : null;
            this.csrfToken = options.csrfToken || window.csrfToken || this._getMetaCsrf();
            this.apiBase = options.apiBase || '/my_doctor/public/api';
            
            // Callbacks
            this.onLocalStream = options.onLocalStream || null;
            this.onRemoteStream = options.onRemoteStream || null;
            this.onStateChange = options.onStateChange || null;
            this.onStatus = options.onStatus || null;
            this.onPeerPresence = options.onPeerPresence || null;
            this.onError = options.onError || null;
            this.onCallEnd = options.onCallEnd || null;

            // Runtime state
            this.state = 'IDLE'; // IDLE, JOINING, MEDIA_ACQUIRED, WAITING_FOR_DOCTOR, SIGNALING, CONNECTING, CONNECTED, DISCONNECTED, ENDED, FAILED
            this.role = null; // 'caller' (Doctor) or 'callee' (Patient)
            this.userId = null;
            this.allowedUntil = null;
            this.iceServers = [];
            this.tabId = 'tab_' + Math.random().toString(36).substring(2, 10);

            // WebRTC & Media
            this.pc = null;
            this.localStream = null;
            this.remoteStream = null;
            this.isAudioOnly = false;
            this.isAudioMuted = false;
            this.isVideoDisabled = false;

            // Queued ICE candidates prior to setRemoteDescription
            this.remoteCandidateQueue = [];
            this.remoteDescriptionSet = false;

            // Firebase refs & listeners
            this.fbDb = null;
            this.callRef = null;
            this.presenceRef = null;
            this.myPresenceRef = null;
            this.peerPresenceRef = null;
            this.offerRef = null;
            this.answerRef = null;
            this.callerCandRef = null;
            this.calleeCandRef = null;

            this.offerHandled = false;
            this.answerHandled = false;
            this.autoEndTimer = null;
            this.autoEndWarnTimer = null;
            this.waitDoctorTimer = null;
            this.peerLeftTimer = null;
            this.heartbeatTimer = null;
            this.reconnectRef = null;
            this._beforeUnloadHandler = null;
        }

        _getMetaCsrf() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        }

        _setState(newState) {
            const oldState = this.state;
            this.state = newState;
            if (typeof this.onStateChange === 'function') {
                this.onStateChange(newState, oldState);
            }
        }

        _setStatus(message, level = 'info') {
            if (typeof this.onStatus === 'function') {
                this.onStatus(message, level);
            }
        }

        _setError(message, code = 'ERROR') {
            this._setState('FAILED');
            if (typeof this.onError === 'function') {
                this.onError(message, code);
            }
        }

        /**
         * Join consultation call
         */
        async join(conversationId = null) {
            if (conversationId) {
                this.conversationId = parseInt(conversationId, 10);
            }

            if (!this.conversationId) {
                this._setError("Conversation ID is required to join a call.", "INVALID_CONV_ID");
                return false;
            }

            if (this.state !== 'IDLE' && this.state !== 'ENDED' && this.state !== 'FAILED') {
                console.warn("[Call] Session is already active in state:", this.state);
                return false;
            }

            try {
                this._setState('JOINING');
                this._setStatus("Joining consultation call...", "info");

                // 1. Call Backend call_join.php
                const joinResp = await fetch(`${this.apiBase}/call_join.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        conversation_id: this.conversationId,
                        csrf_token: this.csrfToken
                    })
                });

                const joinData = await joinResp.json();
                if (!joinResp.ok || !joinData.status) {
                    const msg = joinData.message || "Failed to join call. Consultation may not be active.";
                    this._setError(msg, "JOIN_FORBIDDEN");
                    return false;
                }

                this.role = joinData.data.role; // 'caller' (Doctor) or 'callee' (Patient)
                this.allowedUntil = joinData.data.allowedUntil;
                this.iceServers = joinData.data.iceServers || [{ urls: ['stun:stun.l.google.com:19302'] }];
                this.userId = joinData.data.user_id;

                // Validate window has not already expired
                const remainingInitialMs = this.allowedUntil - Date.now();
                if (remainingInitialMs <= 0) {
                    this._setError("Consultation time window has ended.", "WINDOW_EXPIRED");
                    return false;
                }

                this._setStatus(`Authorized as ${this.role === 'caller' ? 'Doctor (Caller)' : 'Patient (Answerer)'}. Initializing Firebase...`, "info");

                // 2. Initialize Firebase Client
                if (typeof window.DoctorFirebase === 'undefined') {
                    throw new Error("Firebase client library not loaded.");
                }
                await window.DoctorFirebase.init();
                this.fbDb = firebase.database();
                this.callRef = this.fbDb.ref(`calls/${this.conversationId}`);

                // 3. Multi-Tab Presence Check
                this.myPresenceRef = this.fbDb.ref(`calls/${this.conversationId}/presence/${this.userId}`);
                const presenceSnap = await this.myPresenceRef.once('value');
                if (presenceSnap.exists()) {
                    const val = presenceSnap.val();
                    if (val && val.active && val.tabId && val.tabId !== this.tabId) {
                        // Check if heartbeat is very recent (< 12 seconds)
                        if (val.heartbeat && (Date.now() - val.heartbeat < 12000)) {
                            this._setError("Call is already open in another browser tab. Please close that tab first.", "MULTI_TAB_REFUSED");
                            return false;
                        }
                    }
                }

                // 4. Acquire Media (Audio + Video, fallback to Audio-only)
                this._setState('ACQUIRING_MEDIA');
                this._setStatus("Requesting camera and microphone access...", "info");
                await this._acquireMedia();

                // 5. Setup RTCPeerConnection
                this._setupPeerConnection();

                // 6. Register Presence in Firebase with onDisconnect cleanup
                await this._registerPresence();

                // 7. Schedule auto-termination at allowedUntil
                this._scheduleAutoEnd();

                // 8. Begin Role-Specific Signaling
                this._setState('SIGNALING');
                if (this.role === 'caller') {
                    await this._startCallerSignaling();
                } else {
                    await this._startCalleeSignaling();
                }

                return true;
            } catch (err) {
                console.error("[Call] Join error:", err);
                this._setError(err.message || "Failed to initialize call.", "INIT_EXCEPTION");
                this._cleanupMedia();
                return false;
            }
        }

        /**
         * Media Acquisition with Audio-Only Fallback
         */
        async _acquireMedia() {
            let stream = null;
            this.isAudioOnly = false;

            try {
                // Try video + audio first
                stream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    },
                    video: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: 'user'
                    }
                });
            } catch (camErr) {
                console.warn("[Call] Video access failed, attempting audio-only fallback:", camErr);
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            autoGainControl: true
                        },
                        video: false
                    });
                    this.isAudioOnly = true;
                    this._setStatus("Camera unavailable or permission denied. Connected in audio-only mode.", "warning");
                } catch (micErr) {
                    throw new Error(this._formatMediaError(micErr || camErr));
                }
            }

            this.localStream = stream;
            if (typeof this.onLocalStream === 'function') {
                this.onLocalStream(this.localStream, { isAudioOnly: this.isAudioOnly });
            }
        }

        _formatMediaError(err) {
            const name = err.name || '';
            if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
                return "Camera or microphone permission was denied. Please allow access in browser site settings.";
            }
            if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
                return "No camera or microphone found on your device.";
            }
            if (name === 'NotReadableError' || name === 'TrackStartError') {
                return "Camera or microphone is already in use by another application.";
            }
            if (name === 'OverconstrainedError') {
                return "Device camera does not meet requested resolution constraints.";
            }
            return err.message || "Could not access media devices.";
        }

        /**
         * WebRTC RTCPeerConnection Setup
         */
        _setupPeerConnection() {
            this.remoteCandidateQueue = [];
            this.remoteDescriptionSet = false;
            this.offerHandled = false;
            this.answerHandled = false;

            const config = {
                iceServers: this.iceServers,
                iceCandidatePoolSize: 2
            };

            this.pc = new RTCPeerConnection(config);

            // Add local tracks to PeerConnection
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    this.pc.addTrack(track, this.localStream);
                });
            }

            // Remote Track Handler
            this.pc.ontrack = (event) => {
                console.log("[Call] Remote track received:", event.track.kind);
                if (event.streams && event.streams[0]) {
                    this.remoteStream = event.streams[0];
                } else {
                    if (!this.remoteStream) {
                        this.remoteStream = new MediaStream();
                    }
                    this.remoteStream.addTrack(event.track);
                }

                if (typeof this.onRemoteStream === 'function') {
                    this.onRemoteStream(this.remoteStream);
                }
            };

            // Local ICE Candidate Generation
            this.pc.onicecandidate = (event) => {
                if (!event.candidate || !this.fbDb) return;
                const candData = event.candidate.toJSON();
                if (!candData.candidate) return;

                if (this.role === 'caller') {
                    this.callerCandRef.push(candData).catch(err => {
                        console.warn("[Call] Error pushing caller ICE candidate:", err);
                    });
                } else {
                    this.calleeCandRef.push(candData).catch(err => {
                        console.warn("[Call] Error pushing callee ICE candidate:", err);
                    });
                }
            };

            // Connection State Change
            this.pc.onconnectionstatechange = () => {
                const state = this.pc.connectionState;
                console.log(`[Call] RTCPeerConnection state: ${state}`);
                if (state === 'connecting') {
                    this._setState('CONNECTING');
                    this._setStatus("Establishing peer-to-peer connection...", "info");
                } else if (state === 'connected') {
                    this._setState('CONNECTED');
                    this._setStatus("Call connected", "success");
                } else if (state === 'disconnected') {
                    this._setState('DISCONNECTED');
                    this._setStatus("Connection interrupted. Attempting ICE restart...", "warning");
                    if (this.role === 'caller') {
                        this.restartIce();
                    } else {
                        this._requestIceRestart();
                    }
                } else if (state === 'failed') {
                    this._setStatus("Connection failed. Click Retry to reconnect.", "error");
                    this._setState('FAILED');
                } else if (state === 'closed') {
                    this._setState('ENDED');
                }
            };

            // ICE Connection State Change
            this.pc.oniceconnectionstatechange = () => {
                const iceState = this.pc.iceConnectionState;
                console.log(`[Call] ICE state: ${iceState}`);
                if (iceState === 'disconnected') {
                    if (this.role === 'caller') {
                        this.restartIce();
                    } else {
                        this._requestIceRestart();
                    }
                } else if (iceState === 'failed') {
                    this._setStatus("Connection failed. Click Retry to reconnect.", "error");
                    this._setState('FAILED');
                }
            };
        }

        /**
         * Register presence in Firebase with heartbeat and onDisconnect
         */
        async _registerPresence() {
            const presencePayload = {
                uid: this.userId,
                role: this.role,
                tabId: this.tabId,
                active: true,
                heartbeat: Date.now()
            };

            await this.myPresenceRef.set(presencePayload);
            await this.myPresenceRef.onDisconnect().remove();

            // Window close / refresh handler
            if (!this._beforeUnloadHandler) {
                this._beforeUnloadHandler = () => {
                    if (navigator.sendBeacon) {
                        try {
                            const data = new FormData();
                            data.append('conversation_id', this.conversationId);
                            data.append('csrf_token', this.csrfToken);
                            navigator.sendBeacon(`${this.apiBase}/call_end.php`, data);
                        } catch (e) {}
                    }
                    this.end("Window closed");
                };
                window.addEventListener('beforeunload', this._beforeUnloadHandler);
                window.addEventListener('pagehide', this._beforeUnloadHandler);
            }

            // Heartbeat every 5 seconds
            if (this.heartbeatTimer) clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = setInterval(() => {
                if (this.myPresenceRef && this.state !== 'ENDED' && this.state !== 'FAILED') {
                    this.myPresenceRef.update({ heartbeat: Date.now() }).catch(() => {});
                }
            }, 5000);

            // Listen to my presence to detect if another tab supersedes this one
            this.myPresenceRef.on('value', (snap) => {
                if (!snap.exists()) return;
                const val = snap.val();
                if (val && val.tabId && val.tabId !== this.tabId) {
                    this._setError("Call opened in another window. Disconnected.", "SUPERSEDED_BY_ANOTHER_TAB");
                    this.end("Session taken over by another tab");
                }
            });

            // Listen to peer presence
            const presenceRootRef = this.fbDb.ref(`calls/${this.conversationId}/presence`);
            presenceRootRef.on('value', (snap) => {
                if (!snap.exists()) {
                    if (typeof this.onPeerPresence === 'function') {
                        this.onPeerPresence({ peerPresent: false, peerRole: null });
                    }
                    if (this.state === 'CONNECTED') {
                        this._handlePeerLeft();
                    }
                    return;
                }
                const presences = snap.val();
                let peerFound = false;
                let peerRole = null;
                for (const uid in presences) {
                    if (parseInt(uid, 10) !== this.userId && presences[uid].active) {
                        peerFound = true;
                        peerRole = presences[uid].role;
                        break;
                    }
                }
                if (typeof this.onPeerPresence === 'function') {
                    this.onPeerPresence({ peerPresent: peerFound, peerRole });
                }

                if (!peerFound && this.state === 'CONNECTED') {
                    this._handlePeerLeft();
                } else if (peerFound && this.peerLeftTimer) {
                    clearTimeout(this.peerLeftTimer);
                    this.peerLeftTimer = null;
                    if (this.state === 'CONNECTED') {
                        this._setStatus("Peer reconnected", "success");
                    }
                }
            });
        }

        _handlePeerLeft() {
            this._setStatus("The other participant has left or closed the call window.", "warning");
            if (this.peerLeftTimer) clearTimeout(this.peerLeftTimer);
            this.peerLeftTimer = setTimeout(() => {
                if (this.state === 'CONNECTED' || this.state === 'DISCONNECTED') {
                    this.end("Call ended: other participant closed the window");
                }
            }, 10000);
        }

        /**
         * Doctor (Caller) Signaling Flow
         */
        async _startCallerSignaling() {
            this.offerRef = this.fbDb.ref(`calls/${this.conversationId}/signal/offer`);
            this.answerRef = this.fbDb.ref(`calls/${this.conversationId}/signal/answer`);
            this.callerCandRef = this.fbDb.ref(`calls/${this.conversationId}/signal/callerCandidates`);
            this.calleeCandRef = this.fbDb.ref(`calls/${this.conversationId}/signal/calleeCandidates`);

            this._setStatus("Doctor joined. Creating call offer...", "info");

            // 1. Create SDP Offer
            const offer = await this.pc.createOffer({
                offerToReceiveAudio: true,
                offerToReceiveVideo: true
            });
            await this.pc.setLocalDescription(offer);

            // 2. Publish offer to Firebase
            await this.offerRef.set({
                type: offer.type,
                sdp: offer.sdp,
                senderId: this.userId
            });

            this._setStatus("Offer sent. Waiting for patient to answer...", "info");

            // 3. Listen for Answer from Patient
            this.answerRef.on('value', async (snap) => {
                if (!snap.exists() || this.answerHandled) return;
                const val = snap.val();
                if (val && val.type === 'answer' && val.sdp) {
                    this.answerHandled = true;
                    console.log("[Call] Answer received from patient");
                    this._setStatus("Patient answered. Finalizing connection...", "info");

                    try {
                        if (this.pc.signalingState === 'have-local-offer') {
                            await this.pc.setRemoteDescription(new RTCSessionDescription(val));
                            this.remoteDescriptionSet = true;
                            await this._flushCandidateQueue();
                        }
                    } catch (err) {
                        console.error("[Call] Error setting remote description for answer:", err);
                    }
                }
            });

            // 4. Listen for Callee ICE Candidates
            this.calleeCandRef.on('child_added', async (snap) => {
                const candData = snap.val();
                if (!candData || !candData.candidate) return;
                await this._handleRemoteCandidate(candData);
            });

            // 5. Listen for ICE restart requests from Callee
            this.reconnectRef = this.fbDb.ref(`calls/${this.conversationId}/signal/iceRestartRequest`);
            this.reconnectRef.on('value', (snap) => {
                if (snap.exists() && snap.val() && snap.val().requestedBy !== this.userId) {
                    console.log("[Call] Peer requested ICE restart. Triggering restartIce...");
                    this.restartIce();
                }
            });
        }

        /**
         * Patient (Callee) Signaling Flow
         */
        async _startCalleeSignaling() {
            this.offerRef = this.fbDb.ref(`calls/${this.conversationId}/signal/offer`);
            this.answerRef = this.fbDb.ref(`calls/${this.conversationId}/signal/answer`);
            this.callerCandRef = this.fbDb.ref(`calls/${this.conversationId}/signal/callerCandidates`);
            this.calleeCandRef = this.fbDb.ref(`calls/${this.conversationId}/signal/calleeCandidates`);

            this._setState('WAITING_FOR_DOCTOR');
            this._setStatus("Waiting for doctor to join and start the call...", "info");

            // Doctor not joined notice after 30 seconds
            if (this.waitDoctorTimer) clearTimeout(this.waitDoctorTimer);
            this.waitDoctorTimer = setTimeout(() => {
                if (this.state === 'WAITING_FOR_DOCTOR') {
                    this._setStatus("The doctor has not joined the call yet. You may wait here or send a message in chat.", "warning");
                }
            }, 30000);

            // 1. Listen for Caller Candidates
            this.callerCandRef.on('child_added', async (snap) => {
                const candData = snap.val();
                if (!candData || !candData.candidate) return;
                await this._handleRemoteCandidate(candData);
            });

            // 2. Listen for Doctor's Offer
            this.offerRef.on('value', async (snap) => {
                if (!snap.exists()) return;
                const val = snap.val();
                if (val && val.type === 'offer' && val.sdp) {
                    if (this.waitDoctorTimer) {
                        clearTimeout(this.waitDoctorTimer);
                        this.waitDoctorTimer = null;
                    }
                    if (this.offerHandled && !val.iceRestart) return;
                    this.offerHandled = true;
                    console.log("[Call] Doctor offer received (iceRestart=" + Boolean(val.iceRestart) + ")");
                    this._setState('SIGNALING');
                    this._setStatus(val.iceRestart ? "Reconnecting call..." : "Doctor is calling. Answering...", "info");

                    try {
                        await this.pc.setRemoteDescription(new RTCSessionDescription(val));
                        this.remoteDescriptionSet = true;
                        await this._flushCandidateQueue();

                        const answer = await this.pc.createAnswer();
                        await this.pc.setLocalDescription(answer);

                        await this.answerRef.set({
                            type: answer.type,
                            sdp: answer.sdp,
                            senderId: this.userId
                        });

                        this._setStatus("Answer sent to doctor. Connecting...", "info");
                    } catch (err) {
                        console.error("[Call] Error processing offer and generating answer:", err);
                        this._setError("Failed to answer doctor's call: " + err.message, "ANSWER_ERROR");
                    }
                }
            });
        }

        /**
         * Handle incoming remote ICE Candidate with queuing
         */
        async _handleRemoteCandidate(candData) {
            if (!candData || !candData.candidate) return;

            if (this.remoteDescriptionSet && this.pc && this.pc.remoteDescription && this.pc.remoteDescription.type) {
                try {
                    await this.pc.addIceCandidate(new RTCIceCandidate(candData));
                } catch (err) {
                    console.warn("[Call] Error adding ICE candidate:", err);
                }
            } else {
                // Queue candidate until setRemoteDescription completes
                this.remoteCandidateQueue.push(candData);
            }
        }

        /**
         * Drain queued ICE candidates
         */
        async _flushCandidateQueue() {
            if (!this.remoteCandidateQueue.length || !this.pc) return;
            console.log(`[Call] Flushing ${this.remoteCandidateQueue.length} queued ICE candidates`);
            while (this.remoteCandidateQueue.length > 0) {
                const cand = this.remoteCandidateQueue.shift();
                try {
                    await this.pc.addIceCandidate(new RTCIceCandidate(cand));
                } catch (err) {
                    console.warn("[Call] Error adding queued ICE candidate:", err);
                }
            }
        }

        /**
         * Restart ICE (e.g. after network glitch or reconnection)
         */
        async restartIce() {
            if (!this.pc) return;
            if (this.role !== 'caller') {
                await this._requestIceRestart();
                return;
            }
            try {
                this._setStatus("Restarting ICE connection...", "warning");
                this.answerHandled = false;
                const offer = await this.pc.createOffer({ iceRestart: true });
                await this.pc.setLocalDescription(offer);
                if (this.offerRef) {
                    await this.offerRef.set({
                        type: offer.type,
                        sdp: offer.sdp,
                        senderId: this.userId,
                        iceRestart: true,
                        ts: firebase.database.ServerValue.TIMESTAMP
                    });
                }
            } catch (err) {
                console.warn("[Call] ICE restart failed:", err);
            }
        }

        async _requestIceRestart() {
            if (!this.fbDb || !this.conversationId) return;
            try {
                this._setStatus("Requesting reconnection...", "warning");
                await this.fbDb.ref(`calls/${this.conversationId}/signal/iceRestartRequest`).set({
                    requestedBy: this.userId,
                    ts: firebase.database.ServerValue.TIMESTAMP
                });
            } catch (err) {
                console.warn("[Call] Error requesting ICE restart:", err);
            }
        }

        /**
         * Mute / Unmute Audio
         */
        toggleAudio(forceState = null) {
            if (!this.localStream) return this.isAudioMuted;
            const audioTracks = this.localStream.getAudioTracks();
            if (!audioTracks.length) return false;

            const targetMuted = (forceState !== null) ? !forceState : !this.isAudioMuted;
            audioTracks.forEach(track => {
                track.enabled = !targetMuted;
            });
            this.isAudioMuted = targetMuted;
            return this.isAudioMuted;
        }

        /**
         * Enable / Disable Camera Video
         */
        toggleVideo(forceState = null) {
            if (this.isAudioOnly || !this.localStream) return false;
            const videoTracks = this.localStream.getVideoTracks();
            if (!videoTracks.length) return false;

            const targetDisabled = (forceState !== null) ? !forceState : !this.isVideoDisabled;
            videoTracks.forEach(track => {
                track.enabled = !targetDisabled;
            });
            this.isVideoDisabled = targetDisabled;
            return !this.isVideoDisabled;
        }

        /**
         * Auto-terminate call at slot end with 1-minute warning
         */
        _scheduleAutoEnd() {
            if (this.autoEndTimer) clearTimeout(this.autoEndTimer);
            if (this.autoEndWarnTimer) clearTimeout(this.autoEndWarnTimer);
            if (!this.allowedUntil) return;

            const remainingMs = this.allowedUntil - Date.now();
            if (remainingMs > 0) {
                // 1-minute remaining warning
                if (remainingMs > 65000) {
                    this.autoEndWarnTimer = setTimeout(() => {
                        this._setStatus("Notice: Consultation slot ends in 1 minute.", "warning");
                    }, remainingMs - 60000);
                }

                // Final termination at slot end
                this.autoEndTimer = setTimeout(() => {
                    this._setStatus("Consultation slot time has ended. Terminating call.", "warning");
                    this.end("Consultation time expired");
                }, remainingMs);
            } else {
                this.end("Consultation time expired");
            }
        }

        /**
         * Cleanup Local Media Tracks
         */
        _cleanupMedia() {
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    try { track.stop(); } catch (e) {}
                });
                this.localStream = null;
            }
            this.remoteStream = null;
        }

        /**
         * End and cleanup the call
         */
        async end(reason = "Call ended") {
            console.log("[Call] Ending call session. Reason:", reason);
            if (this.state === 'ENDED') return;
            this._setState('ENDED');
            this._setStatus(reason, "info");

            if (this.autoEndTimer) clearTimeout(this.autoEndTimer);
            if (this.autoEndWarnTimer) clearTimeout(this.autoEndWarnTimer);
            if (this.waitDoctorTimer) clearTimeout(this.waitDoctorTimer);
            if (this.peerLeftTimer) clearTimeout(this.peerLeftTimer);
            if (this.heartbeatTimer) clearInterval(this.heartbeatTimer);

            if (this._beforeUnloadHandler) {
                window.removeEventListener('beforeunload', this._beforeUnloadHandler);
                window.removeEventListener('pagehide', this._beforeUnloadHandler);
                this._beforeUnloadHandler = null;
            }

            // 1. Remove my presence
            if (this.myPresenceRef) {
                try {
                    await this.myPresenceRef.remove();
                    this.myPresenceRef.off();
                } catch (e) {}
            }

            // 2. Detach Firebase listeners
            if (this.callRef) {
                try {
                    this.callRef.child('presence').off();
                    this.callRef.child('signal/offer').off();
                    this.callRef.child('signal/answer').off();
                    this.callRef.child('signal/callerCandidates').off();
                    this.callRef.child('signal/calleeCandidates').off();
                    this.callRef.child('signal/iceRestartRequest').off();
                } catch (e) {}
            }

            if (this.reconnectRef) {
                try { this.reconnectRef.off(); } catch (e) {}
                this.reconnectRef = null;
            }

            // 3. Inform backend call_end.php (deletes signal)
            try {
                await fetch(`${this.apiBase}/call_end.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify({
                        conversation_id: this.conversationId,
                        csrf_token: this.csrfToken
                    })
                });
            } catch (err) {
                console.warn("[Call] call_end API request error:", err);
            }

            // 4. Close RTCPeerConnection
            if (this.pc) {
                try {
                    this.pc.ontrack = null;
                    this.pc.onicecandidate = null;
                    this.pc.onconnectionstatechange = null;
                    this.pc.oniceconnectionstatechange = null;
                    this.pc.close();
                } catch (e) {}
                this.pc = null;
            }

            // 5. Stop media tracks
            this._cleanupMedia();

            // 6. Callback
            if (typeof this.onCallEnd === 'function') {
                this.onCallEnd(reason);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DoctorChatCallUI: Chat Window Call Interface Controller
    // ─────────────────────────────────────────────────────────────
    class DoctorChatCallUI {
        constructor() {
            this.isDoctor = false;
            this.activeConv = null;
            this.session = null;
            this.timerInterval = null;
            this.startTime = null;
            this.isMounted = false;
            this.incomingOfferRef = null;

            // DOM elements
            this.overlay = null;
            this.topBar = null;
            this.peerAvatar = null;
            this.peerName = null;
            this.statusDot = null;
            this.statusText = null;
            this.timerEl = null;
            this.remoteVideo = null;
            this.localVideo = null;
            this.remotePlaceholder = null;
            this.placeholderAvatarImg = null;
            this.remoteStatusDesc = null;
            this.localPip = null;
            this.localMutedIcon = null;
            this.localCamOffIcon = null;

            this.btnMute = null;
            this.btnCamera = null;
            this.btnEnd = null;
            this.btnRetry = null;
            this.btnMinimize = null;
            this.btnExpand = null;

            this.minimizedPill = null;
            this.miniTimer = null;
            this.btnRestore = null;
            this.btnMiniEnd = null;

            this.incomingBanner = null;
            this.incomingBannerText = null;
            this.btnBannerJoin = null;
            this.btnHeaderCall = null;
        }

        init(options = {}) {
            this.isDoctor = (typeof options.isDoctor === 'boolean') 
                ? options.isDoctor 
                : window.location.pathname.includes('/doctor/');

            // Mount UI elements into .chat-window
            this.mount();

            // Observe chat container in case it loads asynchronously
            if (!this.isMounted) {
                const observer = new MutationObserver(() => {
                    const chatWindow = document.querySelector('.chat-window');
                    if (chatWindow && !this.isMounted) {
                        this.mount();
                        observer.disconnect();
                    }
                });
                observer.observe(document.body, { childList: true, subtree: true });
            }
        }

        mount() {
            const chatWindow = document.querySelector('.chat-window');
            if (!chatWindow) return;

            if (chatWindow.querySelector('#chat-call-overlay')) {
                this.isMounted = true;
                this._bindElements(chatWindow);
                return;
            }

            // 1. Build Incoming Call Banner (under header)
            this.incomingBanner = document.createElement('div');
            this.incomingBanner.className = 'incoming-call-banner';
            this.incomingBanner.id = 'incoming-call-banner';
            this.incomingBanner.style.display = 'none';

            const bannerInfo = document.createElement('div');
            bannerInfo.className = 'incoming-call-info';

            const ringIcon = document.createElement('span');
            ringIcon.className = 'material-symbols-outlined';
            ringIcon.textContent = 'ring_volume';

            this.incomingBannerText = document.createElement('span');
            this.incomingBannerText.textContent = 'Doctor has started the consultation call';

            bannerInfo.appendChild(ringIcon);
            bannerInfo.appendChild(this.incomingBannerText);

            this.btnBannerJoin = document.createElement('button');
            this.btnBannerJoin.type = 'button';
            this.btnBannerJoin.className = 'btn-banner-join';
            this.btnBannerJoin.textContent = 'Join Call';
            this.btnBannerJoin.addEventListener('click', () => {
                if (this.activeConv) {
                    this.startCall(this.activeConv.conversation_id);
                }
            });

            this.incomingBanner.appendChild(bannerInfo);
            this.incomingBanner.appendChild(this.btnBannerJoin);

            const chatMessages = chatWindow.querySelector('.chat-messages');
            if (chatMessages) {
                chatWindow.insertBefore(this.incomingBanner, chatMessages);
            } else {
                chatWindow.appendChild(this.incomingBanner);
            }

            // 2. Build Call Overlay
            this.overlay = document.createElement('div');
            this.overlay.className = 'chat-call-overlay';
            this.overlay.id = 'chat-call-overlay';
            this.overlay.style.display = 'none';

            // Top Bar
            this.topBar = document.createElement('div');
            this.topBar.className = 'call-top-bar';

            const peerInfo = document.createElement('div');
            peerInfo.className = 'call-peer-info';

            this.peerAvatar = document.createElement('img');
            this.peerAvatar.className = 'call-top-avatar';
            this.peerAvatar.src = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150';
            this.peerAvatar.alt = 'Peer Avatar';

            const peerTextContainer = document.createElement('div');

            this.peerName = document.createElement('h4');
            this.peerName.className = 'call-peer-name';
            this.peerName.textContent = 'Consultation Call';

            const statusLine = document.createElement('div');
            statusLine.className = 'call-status-line';

            this.statusDot = document.createElement('span');
            this.statusDot.className = 'call-status-dot';

            this.statusText = document.createElement('span');
            this.statusText.textContent = 'Connecting...';

            this.timerEl = document.createElement('span');
            this.timerEl.className = 'call-timer';
            this.timerEl.textContent = '00:00';
            this.timerEl.style.display = 'none';

            statusLine.appendChild(this.statusDot);
            statusLine.appendChild(this.statusText);
            statusLine.appendChild(this.timerEl);

            peerTextContainer.appendChild(this.peerName);
            peerTextContainer.appendChild(statusLine);

            peerInfo.appendChild(this.peerAvatar);
            peerInfo.appendChild(peerTextContainer);

            const topActions = document.createElement('div');
            topActions.className = 'call-top-actions';

            this.btnMinimize = document.createElement('button');
            this.btnMinimize.type = 'button';
            this.btnMinimize.className = 'call-top-btn';
            this.btnMinimize.title = 'Minimize call';
            const miniIcon = document.createElement('span');
            miniIcon.className = 'material-symbols-outlined';
            miniIcon.textContent = 'branding_watermark';
            this.btnMinimize.appendChild(miniIcon);
            this.btnMinimize.addEventListener('click', () => this.minimizeCall());

            this.btnExpand = document.createElement('button');
            this.btnExpand.type = 'button';
            this.btnExpand.className = 'call-top-btn';
            this.btnExpand.title = 'Toggle Fullscreen';
            const expIcon = document.createElement('span');
            expIcon.className = 'material-symbols-outlined';
            expIcon.textContent = 'fullscreen';
            this.btnExpand.appendChild(expIcon);
            this.btnExpand.addEventListener('click', () => this.toggleFullscreen());

            topActions.appendChild(this.btnMinimize);
            topActions.appendChild(this.btnExpand);

            this.topBar.appendChild(peerInfo);
            this.topBar.appendChild(topActions);

            // Video Viewport
            const viewport = document.createElement('div');
            viewport.className = 'call-video-viewport';

            this.remoteVideo = document.createElement('video');
            this.remoteVideo.className = 'call-remote-video';
            this.remoteVideo.autoplay = true;
            this.remoteVideo.playsInline = true;

            // Remote Placeholder
            this.remotePlaceholder = document.createElement('div');
            this.remotePlaceholder.className = 'call-remote-placeholder';

            const phAvatarWrapper = document.createElement('div');
            phAvatarWrapper.className = 'call-placeholder-avatar';

            this.placeholderAvatarImg = document.createElement('img');
            this.placeholderAvatarImg.src = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150';
            this.placeholderAvatarImg.alt = 'Avatar';

            const pulseRing = document.createElement('div');
            pulseRing.className = 'call-pulse-ring';

            phAvatarWrapper.appendChild(this.placeholderAvatarImg);
            phAvatarWrapper.appendChild(pulseRing);

            this.remoteStatusDesc = document.createElement('p');
            this.remoteStatusDesc.className = 'call-remote-status-desc';
            this.remoteStatusDesc.textContent = 'Waiting for doctor to join and start call...';

            this.remotePlaceholder.appendChild(phAvatarWrapper);
            this.remotePlaceholder.appendChild(this.remoteStatusDesc);

            // Local PiP
            this.localPip = document.createElement('div');
            this.localPip.className = 'call-local-pip';

            this.localVideo = document.createElement('video');
            this.localVideo.className = 'call-local-video mirrored';
            this.localVideo.autoplay = true;
            this.localVideo.playsInline = true;
            this.localVideo.muted = true;

            const pipBadges = document.createElement('div');
            pipBadges.className = 'call-local-badges';

            const pipLabel = document.createElement('span');
            pipLabel.className = 'call-pip-label';
            pipLabel.textContent = 'You';

            this.localMutedIcon = document.createElement('span');
            this.localMutedIcon.className = 'material-symbols-outlined pip-badge';
            this.localMutedIcon.textContent = 'mic_off';
            this.localMutedIcon.style.display = 'none';

            this.localCamOffIcon = document.createElement('span');
            this.localCamOffIcon.className = 'material-symbols-outlined pip-badge';
            this.localCamOffIcon.textContent = 'videocam_off';
            this.localCamOffIcon.style.display = 'none';

            pipBadges.appendChild(pipLabel);
            pipBadges.appendChild(this.localMutedIcon);
            pipBadges.appendChild(this.localCamOffIcon);

            this.localPip.appendChild(this.localVideo);
            this.localPip.appendChild(pipBadges);

            viewport.appendChild(this.remoteVideo);
            viewport.appendChild(this.remotePlaceholder);
            viewport.appendChild(this.localPip);

            // Bottom Dock
            const dock = document.createElement('div');
            dock.className = 'call-bottom-dock';

            // Mute Button
            this.btnMute = document.createElement('button');
            this.btnMute.type = 'button';
            this.btnMute.className = 'call-dock-btn';
            this.btnMute.title = 'Mute Microphone';
            const muteIcon = document.createElement('span');
            muteIcon.className = 'material-symbols-outlined';
            muteIcon.textContent = 'mic';
            this.btnMute.appendChild(muteIcon);
            this.btnMute.addEventListener('click', () => this.toggleAudio());

            // Camera Button
            this.btnCamera = document.createElement('button');
            this.btnCamera.type = 'button';
            this.btnCamera.className = 'call-dock-btn';
            this.btnCamera.title = 'Turn Camera Off';
            const camIcon = document.createElement('span');
            camIcon.className = 'material-symbols-outlined';
            camIcon.textContent = 'videocam';
            this.btnCamera.appendChild(camIcon);
            this.btnCamera.addEventListener('click', () => this.toggleVideo());

            // End Call Button
            this.btnEnd = document.createElement('button');
            this.btnEnd.type = 'button';
            this.btnEnd.className = 'call-dock-btn call-btn-end';
            this.btnEnd.title = 'End Call';
            const endIcon = document.createElement('span');
            endIcon.className = 'material-symbols-outlined';
            endIcon.textContent = 'call_end';
            this.btnEnd.appendChild(endIcon);
            this.btnEnd.addEventListener('click', () => this.endCall());

            // Retry Button
            this.btnRetry = document.createElement('button');
            this.btnRetry.type = 'button';
            this.btnRetry.className = 'call-dock-btn call-btn-retry';
            this.btnRetry.title = 'Retry Connection';
            this.btnRetry.style.display = 'none';
            const retryIcon = document.createElement('span');
            retryIcon.className = 'material-symbols-outlined';
            retryIcon.textContent = 'refresh';
            this.btnRetry.appendChild(retryIcon);
            this.btnRetry.addEventListener('click', () => {
                if (this.session) {
                    this.session.restartIce();
                }
            });

            dock.appendChild(this.btnMute);
            dock.appendChild(this.btnCamera);
            dock.appendChild(this.btnEnd);
            dock.appendChild(this.btnRetry);

            this.overlay.appendChild(this.topBar);
            this.overlay.appendChild(viewport);
            this.overlay.appendChild(dock);

            chatWindow.appendChild(this.overlay);

            // 3. Minimized Pill
            this.minimizedPill = document.createElement('div');
            this.minimizedPill.className = 'chat-call-minimized-pill';
            this.minimizedPill.id = 'chat-call-minimized-pill';
            this.minimizedPill.style.display = 'none';

            const miniPulse = document.createElement('span');
            miniPulse.className = 'material-symbols-outlined call-pulse-icon';
            miniPulse.textContent = 'call';

            this.miniTimer = document.createElement('span');
            this.miniTimer.textContent = '00:00';

            this.btnRestore = document.createElement('button');
            this.btnRestore.type = 'button';
            this.btnRestore.className = 'btn-restore-call';
            this.btnRestore.title = 'Expand Call';
            const restoreIcon = document.createElement('span');
            restoreIcon.className = 'material-symbols-outlined';
            restoreIcon.textContent = 'open_in_full';
            this.btnRestore.appendChild(restoreIcon);
            this.btnRestore.addEventListener('click', () => this.restoreCall());

            this.btnMiniEnd = document.createElement('button');
            this.btnMiniEnd.type = 'button';
            this.btnMiniEnd.className = 'btn-mini-end';
            this.btnMiniEnd.title = 'End Call';
            const miniEndIcon = document.createElement('span');
            miniEndIcon.className = 'material-symbols-outlined';
            miniEndIcon.textContent = 'call_end';
            this.btnMiniEnd.appendChild(miniEndIcon);
            this.btnMiniEnd.addEventListener('click', () => this.endCall());

            this.minimizedPill.appendChild(miniPulse);
            this.minimizedPill.appendChild(this.miniTimer);
            this.minimizedPill.appendChild(this.btnRestore);
            this.minimizedPill.appendChild(this.btnMiniEnd);

            chatWindow.appendChild(this.minimizedPill);

            this.isMounted = true;
            this._bindElements(chatWindow);
        }

        _bindElements(chatWindow) {
            this.btnHeaderCall = document.getElementById('btn-chat-call') || chatWindow.querySelector('.btn-chat-call');
            if (this.btnHeaderCall) {
                this.btnHeaderCall.addEventListener('click', () => {
                    if (this.activeConv) {
                        this.startCall(this.activeConv.conversation_id);
                    }
                });
            }
        }

        /**
         * Triggered when a conversation is opened in the chat sidebar
         */
        onConversationSelected(conv) {
            this.activeConv = conv;
            this.mount();

            if (!conv) {
                if (this.btnHeaderCall) this.btnHeaderCall.style.display = 'none';
                if (this.incomingBanner) this.incomingBanner.style.display = 'none';
                return;
            }

            // Detach previous passive offer listener
            if (this.incomingOfferRef) {
                this.incomingOfferRef.off();
                this.incomingOfferRef = null;
            }
            if (this.incomingBanner) {
                this.incomingBanner.style.display = 'none';
            }

            // Check if call is permitted for this appointment:
            // Online mode ('video_call', 'chat', 'audio_call') AND status === 'confirmed'
            const isOnlineMode = ['video_call', 'chat', 'audio_call'].includes(conv.appointment_mode);
            const isConfirmed = (conv.appointment_status === 'confirmed');

            if (isOnlineMode && isConfirmed) {
                if (this.btnHeaderCall) {
                    this.btnHeaderCall.style.display = 'inline-flex';
                    this.btnHeaderCall.title = this.isDoctor ? 'Start Consultation Video Call' : 'Join Consultation Call';
                }

                // If user is patient and not currently in a call, listen for doctor's offer
                if (!this.isDoctor && typeof window.DoctorFirebase !== 'undefined') {
                    window.DoctorFirebase.init().then(() => {
                        const db = firebase.database();
                        this.incomingOfferRef = db.ref(`calls/${conv.conversation_id}/signal/offer`);
                        this.incomingOfferRef.on('value', (snap) => {
                            if (snap.exists() && snap.val() && snap.val().type === 'offer') {
                                // If overlay is not currently open, notify patient
                                if (!this.session || this.session.state === 'ENDED' || this.session.state === 'FAILED') {
                                    if (this.incomingBanner) {
                                        this.incomingBannerText.textContent = `${conv.other_user_name || 'Doctor'} has started the consultation call.`;
                                        this.incomingBanner.style.display = 'flex';
                                    }
                                    if (this.btnHeaderCall) {
                                        this.btnHeaderCall.classList.add('calling');
                                    }
                                }
                            } else {
                                if (this.incomingBanner) {
                                    this.incomingBanner.style.display = 'none';
                                }
                                if (this.btnHeaderCall) {
                                    this.btnHeaderCall.classList.remove('calling');
                                }
                            }
                        });
                    });
                }
            } else {
                if (this.btnHeaderCall) {
                    this.btnHeaderCall.style.display = 'none';
                }
            }
        }

        /**
         * Launch Call UI & Start Signaling Session
         */
        async startCall(convId) {
            if (!convId) return;

            // If another call is ongoing, prompt or end it
            if (this.session && this.session.state !== 'ENDED' && this.session.state !== 'FAILED') {
                if (this.session.conversationId === convId) {
                    this.restoreCall();
                    return;
                } else {
                    const confirmSwitch = confirm("Another call is currently active. End it and start this call?");
                    if (!confirmSwitch) return;
                    await this.session.end("Switched to another consultation call");
                }
            }

            this.mount();

            // Setup peer info in overlay
            const peerNameStr = this.activeConv ? this.activeConv.other_user_name : (this.isDoctor ? 'Patient' : 'Doctor');
            const peerAvatarUrl = (this.activeConv && this.activeConv.other_user_avatar) 
                ? this.activeConv.other_user_avatar 
                : 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&q=80&w=150';

            this.peerName.textContent = peerNameStr;
            this.peerAvatar.src = peerAvatarUrl;
            this.placeholderAvatarImg.src = peerAvatarUrl;

            // Reset UI states
            this.remotePlaceholder.style.display = 'flex';
            this.remoteVideo.srcObject = null;
            this.localVideo.srcObject = null;
            this.localPip.style.display = 'block';
            this.btnRetry.style.display = 'none';
            this.timerEl.style.display = 'none';
            this.timerEl.textContent = '00:00';
            this.statusDot.className = 'call-status-dot';
            this.statusText.textContent = 'Connecting...';
            this.remoteStatusDesc.textContent = this.isDoctor 
                ? "Starting consultation call as Doctor..." 
                : "Waiting for doctor to join and start call...";

            this._resetDockButtons();

            // Display overlay
            this.overlay.style.display = 'flex';
            if (this.minimizedPill) this.minimizedPill.style.display = 'none';
            if (this.incomingBanner) this.incomingBanner.style.display = 'none';

            // Create WebRTC Call Session
            this.session = new DoctorCallSession({
                conversationId: convId,

                onLocalStream: (stream, { isAudioOnly }) => {
                    this.localVideo.srcObject = stream;
                    if (isAudioOnly) {
                        this.btnCamera.classList.add('active');
                        this.btnCamera.disabled = true;
                        this.localCamOffIcon.style.display = 'inline-block';
                    }
                },

                onRemoteStream: (stream) => {
                    this.remoteVideo.srcObject = stream;
                    this.remotePlaceholder.style.display = 'none';
                    this.statusDot.className = 'call-status-dot connected';
                },

                onStateChange: (newState) => {
                    console.log("[ChatCallUI] State changed:", newState);

                    if (newState === 'WAITING_FOR_DOCTOR') {
                        this.statusDot.className = 'call-status-dot';
                        this.statusText.textContent = 'Waiting for Doctor';
                        this.remoteStatusDesc.textContent = 'Waiting for doctor to join and start the call...';
                    } else if (newState === 'CONNECTING') {
                        this.statusDot.className = 'call-status-dot';
                        this.statusText.textContent = 'Connecting...';
                        this.remoteStatusDesc.textContent = 'Establishing secure peer-to-peer connection...';
                    } else if (newState === 'CONNECTED') {
                        this.statusDot.className = 'call-status-dot connected';
                        this.statusText.textContent = 'Call Connected';
                        this.remotePlaceholder.style.display = 'none';
                        this.btnRetry.style.display = 'none';
                        this._startTimer();
                    } else if (newState === 'DISCONNECTED') {
                        this.statusDot.className = 'call-status-dot error';
                        this.statusText.textContent = 'Disconnected';
                        this.remoteStatusDesc.textContent = 'Connection interrupted. Reconnecting...';
                    } else if (newState === 'FAILED') {
                        this.statusDot.className = 'call-status-dot error';
                        this.statusText.textContent = 'Connection Failed';
                        this.remoteStatusDesc.textContent = 'Failed to connect. Click Retry below.';
                        this.btnRetry.style.display = 'inline-flex';
                    } else if (newState === 'ENDED') {
                        this._handleCallEnded("Call ended");
                    }
                },

                onStatus: (msg, level) => {
                    this.remoteStatusDesc.textContent = msg;
                    if (level === 'warning' && this.statusText) {
                        this.statusText.textContent = msg;
                        if (this.statusDot) this.statusDot.className = 'call-status-dot';
                    }
                },

                onError: (errMessage, code) => {
                    let friendlyMsg = errMessage;
                    if (code === 'MULTI_TAB_REFUSED') {
                        friendlyMsg = "Call is already open in another browser tab. Please close that tab first.";
                    } else if (code === 'WINDOW_EXPIRED') {
                        friendlyMsg = "Consultation time window has ended.";
                    }
                    alert(friendlyMsg);
                    this._handleCallEnded(friendlyMsg);
                },

                onCallEnd: (reason) => {
                    this._handleCallEnded(reason);
                }
            });

            const ok = await this.session.join();
            if (!ok) {
                this.overlay.style.display = 'none';
            }
        }

        toggleAudio() {
            if (!this.session) return;
            const isMuted = this.session.toggleAudio();
            const icon = this.btnMute.querySelector('.material-symbols-outlined');
            if (isMuted) {
                this.btnMute.classList.add('active');
                if (icon) icon.textContent = 'mic_off';
                this.localMutedIcon.style.display = 'inline-block';
            } else {
                this.btnMute.classList.remove('active');
                if (icon) icon.textContent = 'mic';
                this.localMutedIcon.style.display = 'none';
            }
        }

        toggleVideo() {
            if (!this.session) return;
            const isCameraEnabled = this.session.toggleVideo();
            const icon = this.btnCamera.querySelector('.material-symbols-outlined');
            if (!isCameraEnabled) {
                this.btnCamera.classList.add('active');
                if (icon) icon.textContent = 'videocam_off';
                this.localCamOffIcon.style.display = 'inline-block';
            } else {
                this.btnCamera.classList.remove('active');
                if (icon) icon.textContent = 'videocam';
                this.localCamOffIcon.style.display = 'none';
            }
        }

        minimizeCall() {
            if (!this.session || this.session.state === 'ENDED' || this.session.state === 'FAILED') return;
            this.overlay.style.display = 'none';
            this.minimizedPill.style.display = 'flex';
        }

        restoreCall() {
            if (!this.session || this.session.state === 'ENDED' || this.session.state === 'FAILED') return;
            this.minimizedPill.style.display = 'none';
            this.overlay.style.display = 'flex';
        }

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                if (this.overlay.requestFullscreen) {
                    this.overlay.requestFullscreen().catch(() => {});
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            }
        }

        async endCall() {
            if (this.session) {
                await this.session.end("User ended call");
            }
            this._handleCallEnded("Call ended");
        }

        _handleCallEnded(reason = "Call ended") {
            this._stopTimer();
            if (document.fullscreenElement) {
                try { document.exitFullscreen(); } catch (e) {}
            }
            if (this.overlay) this.overlay.style.display = 'none';
            if (this.minimizedPill) this.minimizedPill.style.display = 'none';
            if (this.remoteVideo) this.remoteVideo.srcObject = null;
            if (this.localVideo) this.localVideo.srcObject = null;
            if (this.btnHeaderCall) this.btnHeaderCall.classList.remove('calling');
        }

        _resetDockButtons() {
            this.btnMute.classList.remove('active');
            const muteIcon = this.btnMute.querySelector('.material-symbols-outlined');
            if (muteIcon) muteIcon.textContent = 'mic';

            this.btnCamera.classList.remove('active');
            this.btnCamera.disabled = false;
            const camIcon = this.btnCamera.querySelector('.material-symbols-outlined');
            if (camIcon) camIcon.textContent = 'videocam';

            this.localMutedIcon.style.display = 'none';
            this.localCamOffIcon.style.display = 'none';
        }

        _startTimer() {
            this.startTime = Date.now();
            this.timerEl.style.display = 'inline-block';
            this._updateTimerDisplay();
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = setInterval(() => this._updateTimerDisplay(), 1000);
        }

        _stopTimer() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            this.timerInterval = null;
            if (this.timerEl) {
                this.timerEl.style.display = 'none';
                this.timerEl.textContent = '00:00';
            }
            if (this.miniTimer) {
                this.miniTimer.textContent = '00:00';
            }
        }

        _updateTimerDisplay() {
            if (!this.startTime) return;
            const diffSec = Math.floor((Date.now() - this.startTime) / 1000);
            const mins = String(Math.floor(diffSec / 60)).padStart(2, '0');
            const secs = String(diffSec % 60).padStart(2, '0');
            const formatted = `${mins}:${secs}`;
            if (this.timerEl) this.timerEl.textContent = formatted;
            if (this.miniTimer) this.miniTimer.textContent = formatted;
        }
    }

    // Expose Singletons to window
    window.DoctorCallSession = DoctorCallSession;
    window.DoctorChatCallUI = new DoctorChatCallUI();

    // Auto-init Chat Call UI when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            window.DoctorChatCallUI.init();
        });
    } else {
        window.DoctorChatCallUI.init();
    }

})(window);
