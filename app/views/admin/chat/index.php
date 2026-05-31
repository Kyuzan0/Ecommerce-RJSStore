<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Live Chat</h1>
    <p class="text-sm text-gray-500 mt-1">Percakapan dengan pelanggan</p>
</div>

<?php if (empty($conversations)): ?>
<div class="bg-white rounded-2xl border border-gray-100 p-12 text-center">
    <p class="text-gray-500">Belum ada percakapan.</p>
</div>
<?php else: ?>
<div class="space-y-3">
    <?php foreach ($conversations as $conv): ?>
    <a href="<?= url('/admin-chat/view/' . $conv['id']) ?>" class="block bg-white rounded-2xl border border-gray-100 p-4 hover:shadow-md transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background:#42B549"><?= strtoupper(substr($conv['customer_name'], 0, 1)) ?></div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm"><?= e($conv['customer_name']) ?></p>
                    <p class="text-xs text-gray-500"><?= e($conv['email']) ?></p>
                </div>
            </div>
            <div class="text-right">
                <?php if ((int)($conv['unread_count'] ?? 0) > 0): ?>
                    <span class="inline-block w-5 h-5 text-[10px] font-bold text-white rounded-full flex items-center justify-center" style="background:#E65100"><?= $conv['unread_count'] ?></span>
                <?php endif; ?>
                <p class="text-xs text-gray-400 mt-1"><?= $conv['last_message_at'] ? date('d M H:i', strtotime($conv['last_message_at'])) : '-' ?></p>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
