/**
 * Firebase Real-time Signals Client (Optimized)
 * Handles custom token authentication, token caching in sessionStorage (1 hr validity),
 * lightweight dashboard notifications, and instant chat signals without periodic polling.
 */
window.DoctorFirebase = {
    isInitialized: false,
    authPromise: null,
    currentUserId: null,
    activeChatRef: null,

    init: async function() {
        if (this.authPromise) return this.authPromise;
        this.authPromise = (async () => {
            if (typeof firebase === 'undefined') {
                console.warn("[Firebase] SDK not loaded.");
                return null;
            }

            const tInit0 = performance.now();
            let authData = null;

            // 1. Check cached custom token in sessionStorage (valid for ~50 mins)
            try {
                const cached = sessionStorage.getItem('doctor_firebase_auth_cache');
                if (cached) {
                    const parsed = JSON.parse(cached);
                    if (parsed.token && parsed.savedAt && (Date.now() - parsed.savedAt < 50 * 60 * 1000)) {
                        authData = parsed;
                    }
                }
            } catch (e) {
                console.warn("[Firebase] sessionStorage read error:", e);
            }

            // 2. If no valid cache, fetch custom token from backend
            if (!authData) {
                const tToken0 = performance.now();
                try {
                    const res = await fetch('/my_doctor/public/api/get_firebase_token.php');
                    if (!res.ok) {
                        console.warn("[Firebase] Failed to fetch token: HTTP " + res.status);
                        return null;
                    }
                    const data = await res.json();
                    if (!data.status || !data.token) {
                        console.warn("[Firebase] Token error:", data.message);
                        return null;
                    }
                    console.log(`[Perf] Token fetch took: ${(performance.now() - tToken0).toFixed(2)}ms`);

                    authData = {
                        token: data.token,
                        userId: data.userId,
                        apiKey: data.apiKey,
                        authDomain: data.authDomain,
                        projectId: data.projectId,
                        databaseURL: data.databaseURL,
                        appId: data.appId,
                        messagingSenderId: data.messagingSenderId,
                        savedAt: Date.now()
                    };

                    try {
                        sessionStorage.setItem('doctor_firebase_auth_cache', JSON.stringify(authData));
                    } catch (e) {}
                } catch (err) {
                    console.error("[Firebase] Fetch token exception:", err);
                    return null;
                }
            } else {
                console.log("[Firebase] Reusing cached custom token from sessionStorage");
            }

            // 3. Initialize Firebase App & Sign in
            try {
                if (!firebase.apps.length) {
                    firebase.initializeApp({
                        apiKey: authData.apiKey,
                        authDomain: authData.authDomain,
                        projectId: authData.projectId,
                        databaseURL: authData.databaseURL,
                        appId: authData.appId,
                        messagingSenderId: authData.messagingSenderId
                    });
                }

                if (!firebase.auth().currentUser) {
                    const tSignIn0 = performance.now();
                    await firebase.auth().signInWithCustomToken(authData.token);
                    console.log(`[Perf] Firebase sign-in took: ${(performance.now() - tSignIn0).toFixed(2)}ms`);
                }

                this.isInitialized = true;
                this.currentUserId = parseInt(authData.userId, 10);
                console.log(`[Perf] Total Firebase init took: ${(performance.now() - tInit0).toFixed(2)}ms for user:`, this.currentUserId);
                return this.currentUserId;
            } catch (err) {
                console.error("[Firebase] Init/Auth error:", err);
                sessionStorage.removeItem('doctor_firebase_auth_cache');
                return null;
            }
        })();
        return this.authPromise;
    },

    subscribeDashboardAppointments: async function(callback) {
        await this.init();
        if (!this.isInitialized || !this.currentUserId) return;
        const ref = firebase.database().ref(`dashboards/${this.currentUserId}/appointments`);
        ref.on('value', (snap) => {
            if (snap.exists() && typeof callback === 'function') {
                callback(snap.val());
            }
        });
    },

    subscribeUserChatUpdates: async function(callback) {
        await this.init();
        if (!this.isInitialized || !this.currentUserId) return;
        const ref = firebase.database().ref(`dashboards/${this.currentUserId}/chats`);
        ref.on('value', (snap) => {
            if (snap.exists() && typeof callback === 'function') {
                callback(snap.val());
            }
        });
    },

    subscribeChat: async function(convId, onNewMessageSignal) {
        await this.init();
        if (!this.isInitialized) return;
        this.unsubscribeChat();

        const chatRef = firebase.database().ref(`chats/${convId}`);
        this.activeChatRef = chatRef;

        let initialLoaded = false;
        let lastKnownKey = null;

        try {
            const snap = await chatRef.limitToLast(1).once('value');
            if (snap.exists()) {
                snap.forEach(child => {
                    lastKnownKey = child.key;
                });
            }
        } catch (e) {
            console.warn("[Firebase] Initial chat snapshot error:", e);
        }
        initialLoaded = true;

        chatRef.limitToLast(1).on('child_added', (snap) => {
            if (!initialLoaded) return;
            if (lastKnownKey && snap.key === lastKnownKey) return;
            lastKnownKey = snap.key;
            const val = snap.val();
            if (val && typeof onNewMessageSignal === 'function') {
                // If the signal was sent by myself, ignore to avoid redundant fetch
                if (val.sender_id && parseInt(val.sender_id, 10) === this.currentUserId) {
                    return;
                }
                onNewMessageSignal(val);
            }
        });
    },

    unsubscribeChat: function() {
        if (this.activeChatRef) {
            this.activeChatRef.off();
            this.activeChatRef = null;
        }
    }
};

// Start background init immediately on script load so token and auth are warm
if (typeof firebase !== 'undefined') {
    window.DoctorFirebase.init();
}
