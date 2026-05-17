<?php
// views/student/doubt_sessions.php — MEMBER 1
require 'views/layout/header.php';
?>

<?php if (!empty($_GET['msg']) && $_GET['msg'] === 'booked'): ?>
    <div class="alert alert-success">✓ Session booked successfully!</div>
<?php endif; ?>

<div class="grid-2">
    <!-- Available Sessions -->
    <div>
        <h3 class="fw-bold mb-4" style="margin-bottom:16px">Available Sessions</h3>
        <?php if (empty($sessions)): ?>
            <div class="empty-state card" style="padding:40px">
                <div class="empty-icon">🎓</div>
                <h3>No upcoming sessions</h3>
                <p>Your TAs haven't scheduled any sessions yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($sessions as $s): ?>
            <div class="session-card">
                <div class="session-date">📅 <?= date('D, M d Y • H:i', strtotime($s['scheduled_at'])) ?></div>
                <div class="fw-bold" style="margin-bottom:4px"><?= htmlspecialchars($s['title']) ?></div>
                <p class="text-sm text-muted" style="margin-bottom:12px">
                    Course: <?= htmlspecialchars($s['course_title']) ?> •
                    TA: <?= htmlspecialchars($s['ta_name']) ?> •
                    Duration: <?= $s['duration_minutes'] ?>min
                </p>
                <?php if ($s['location_or_link']): ?>
                    <p class="text-sm text-gray mb-3" style="margin-bottom:10px">📍 <?= htmlspecialchars($s['location_or_link']) ?></p>
                <?php endif; ?>
                <div class="flex-between">
                    <span class="text-sm text-muted">
                        <?= $s['booking_count'] ?> / <?= $s['max_attendees'] ?> booked
                    </span>
                    <?php if ($s['my_booking_id']): ?>
                        <span class="badge badge-success">✓ Booked</span>
                    <?php elseif ($s['booking_count'] >= $s['max_attendees']): ?>
                        <span class="badge badge-danger">Full</span>
                    <?php else: ?>
                        <form method="POST" action="index.php?page=student&action=book_session" style="display:inline">
                            <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Book Slot</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- My Bookings -->
    <div>
        <h3 class="fw-bold mb-4" style="margin-bottom:16px">My Bookings</h3>
        <?php if (empty($my_bookings)): ?>
            <div class="empty-state card" style="padding:40px">
                <div class="empty-icon">📅</div>
                <h3>No bookings yet</h3>
                <p>Book a session from the available list.</p>
            </div>
        <?php else: ?>
            <?php foreach ($my_bookings as $b): ?>
            <?php $upcoming = strtotime($b['scheduled_at']) > time(); ?>
            <div class="session-card" style="<?= !$upcoming ? 'opacity:.7' : '' ?>">
                <div class="session-date">📅 <?= date('D, M d Y • H:i', strtotime($b['scheduled_at'])) ?></div>
                <div class="fw-bold" style="margin-bottom:4px"><?= htmlspecialchars($b['title']) ?></div>
                <p class="text-sm text-muted" style="margin-bottom:8px">
                    <?= htmlspecialchars($b['course_title']) ?> •
                    TA: <?= htmlspecialchars($b['ta_name']) ?>
                    <?php if ($b['is_cancelled']): ?>
                        <span class="badge badge-danger ml-2" style="margin-left:8px">Cancelled</span>
                    <?php elseif (!$upcoming): ?>
                        <span class="badge badge-gray ml-2" style="margin-left:8px">Completed</span>
                    <?php else: ?>
                        <span class="badge badge-success ml-2" style="margin-left:8px">Upcoming</span>
                    <?php endif; ?>
                </p>
                <?php if ($b['location_or_link']): ?>
                    <p class="text-sm text-gray">📍 <?= htmlspecialchars($b['location_or_link']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require 'views/layout/footer.php'; ?>
