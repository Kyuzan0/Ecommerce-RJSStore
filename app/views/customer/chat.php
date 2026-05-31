<div class="flex flex-col h-[calc(100vh-140px)]">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Live Chat</h1>
            <p class="text-sm text-gray-500">Hubungi admin untuk bantuan</p>
        </div>
        <span class="text-xs px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">Online</span>
    </div>

    <!-- Messages Container -->
    <div id="chat-messages" class="flex-1 overflow-y-auto bg-white rounded-2xl border border-gray-100 p-4 space-y-3 mb-4">
        <?php if (empty($messages)): ?>
            <p class="text-center text-gray-400 text-sm py-8">Belum ada pesan. Mulai percakapan!</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
            <div class="flex <?= $msg['sender_role'] === 'customer' ? 'justify-end' : 'justify-start' ?>" data-msg-id="<?= $msg['id'] ?>">
                <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm <?= $msg['sender_role'] === 'customer' ? 'bg-green-500 text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md' ?>">
                    <p><?= e($msg['message']) ?></p>
                    <p class="text-[10px] mt-1 <?= $msg['sender_role'] === 'customer' ? 'text-green-100' : 'text-gray-400' ?>"><?= date('H:i', strtotime($msg['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Input -->
    <form id="chat-form" onsubmit="sendMessage(event)" class="flex gap-2">
        <input type="text" id="chat-input" placeholder="Ketik pesan..." class="flex-1 px-4 py-3 rounded-xl border border-gray-200 text-sm outline-none focus:border-green-500" autocomplete="off">
        <button type="submit" class="px-5 py-3 rounded-xl text-white font-semibold text-sm transition hover:opacity-90" style="background:#42B549">Kirim</button>
    </form>
</div>

<script>
var CONV_ID = <?= (int) $conversation['id'] ?>;
var LAST_MSG_ID = <?= !empty($messages) ? (int) end($messages)['id'] : 0 ?>;
var POLL_INTERVAL = 4000;

function sendMessage(e) {
    e.preventDefault();
    var input = document.getElementById('chat-input');
    var msg = input.value.trim();
    if (!msg) return;

    var formData = new FormData();
    formData.append('conversation_id', CONV_ID);
    formData.append('message', msg);

    input.value = '';

    fetch('<?= url("/api/chat/send") ?>', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) pollMessages();
        });
}

function pollMessages() {
    fetch('<?= url("/api/chat/poll") ?>?conversation_id=' + CONV_ID + '&after_id=' + LAST_MSG_ID)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.messages.length > 0) {
                var container = document.getElementById('chat-messages');
                // Remove empty state if present
                var empty = container.querySelector('.text-center');
                if (empty) empty.remove();

                data.messages.forEach(function(msg) {
                    var div = document.createElement('div');
                    div.className = 'flex ' + (msg.sender_role === 'customer' ? 'justify-end' : 'justify-start');
                    div.setAttribute('data-msg-id', msg.id);
                    div.innerHTML = '<div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-sm ' +
                        (msg.sender_role === 'customer' ? 'bg-green-500 text-white rounded-br-md' : 'bg-gray-100 text-gray-800 rounded-bl-md') + '">' +
                        '<p>' + escapeHtml(msg.message) + '</p>' +
                        '<p class="text-[10px] mt-1 ' + (msg.sender_role === 'customer' ? 'text-green-100' : 'text-gray-400') + '">' + new Date(msg.created_at).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) + '</p></div>';
                    container.appendChild(div);
                    LAST_MSG_ID = parseInt(msg.id);
                });
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(function() {});
}

function escapeHtml(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

// Auto-scroll to bottom on load
document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;

// Start polling
setInterval(pollMessages, POLL_INTERVAL);
</script>
