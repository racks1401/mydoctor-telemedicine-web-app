<div class="chats-container">
    <!-- Chat List -->
    <div class="chat-list">
        <div class="chat-search">
            <input type="text" placeholder="Search messages...">
        </div>
        <div class="chat-items">
            <!-- Dynamic chat items rendered here -->
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-header-info">
                <button class="chat-mobile-back-btn" id="btn-chat-mobile-back" title="Back to chats" type="button">
                    <span class="material-symbols-outlined">arrow_back</span>
                </button>
                <div class="chat-header-avatar-box" id="chat-header-avatar-box">
                    <img alt="User" class="chat-header-avatar" src="" style="display: none;">
                    <div class="chat-header-initials" style="display: none;"></div>
                </div>
                <div class="chat-header-details">
                    <p class="chat-header-name">Select Doctor</p>
                    <p class="chat-header-status">Active now</p>
                </div>
            </div>
            <div class="chat-header-actions">
                <button class="icon-btn btn-chat-call" id="btn-chat-call" title="Start Consultation Call" type="button" style="display: none;">
                    <span class="material-symbols-outlined">videocam</span>
                </button>
                <button class="icon-btn" title="More Options" type="button">
                    <span class="material-symbols-outlined">more_vert</span>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages">
            <!-- Dynamic messages rendered here -->
        </div>

        <!-- Chat Input Area -->
        <div class="chat-input-area">
            <div class="chat-input">
                <button class="chat-input-action" type="button" title="Attach File">
                    <span class="material-symbols-outlined">attach_file</span>
                </button>
                <textarea placeholder="Type a message..." rows="1"></textarea>
                <button class="chat-input-action" type="button" title="Emoji">
                    <span class="material-symbols-outlined">sentiment_satisfied</span>
                </button>
            </div>
            <button class="chat-send-btn" type="button" title="Send Message">
                <span class="material-symbols-outlined" style="font-size: 18px;">send</span>
            </button>
        </div>
    </div>
</div>
