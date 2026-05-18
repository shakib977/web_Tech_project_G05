<?php require 'views/layout/header.php'; ?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">📋 Bookings — <?= htmlspecialchars($session['title']) ?></div>
            <div class="text-xs text-muted">📅 <?= date('D M d, Y H:i', strtotime($session['scheduled_at'])) ?> • <?= count($bookings) ?>/<?= $session['max_attendees'] ?> booked</div>
        </div>
        <a href="index.php?page=ta&action=doubt_sessions" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="card-body" style="padding:0">
        <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Student ID</th><th>Booked At</th></tr></thead>
            <tbody>
            <?php if (empty($bookings)): ?>
            <tr><td colspan="4" class="text-center text-muted" style="padding:30px">No bookings yet.</td></tr>
            <?php else: ?>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td class="fw-600"><?= htmlspecialchars($b['name']) ?></td>
                <td class="text-sm text-gray"><?= htmlspecialchars($b['email']) ?></td>
                <td class="text-sm"><?= htmlspecialchars($b['student_id']) ?></td>
                <td class="text-sm text-muted"><?= date('M d, Y H:i', strtotime($b['booked_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>