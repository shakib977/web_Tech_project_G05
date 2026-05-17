<?php
// views/student/quiz_take.php — MEMBER 1
require 'views/layout/header.php';
?>

<div class="quiz-wrap">
    <!-- Sticky Timer Bar -->
    <div class="quiz-sticky-bar">
        <div>
            <div class="fw-bold" style="font-size:15px"><?= htmlspecialchars($quiz['title']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($quiz['course_title']) ?></div>
        </div>
        <div class="timer" id="timer_wrap">
            ⏱ <span id="timer_display">--:--</span>
        </div>
        <div class="q-progress">
            <span id="answered_count">0</span> / <?= count($questions) ?> answered
        </div>
    </div>

    <form method="POST" action="index.php?page=student&action=submit_quiz" id="quiz_form">
        <input type="hidden" name="attempt_id" value="<?= $attempt_id ?>">
        <input type="hidden" name="quiz_id"    value="<?= $quiz['id'] ?>">

        <?php foreach ($questions as $i => $q): ?>
        <div class="question-card" id="qcard_<?= $q['id'] ?>">
            <div class="flex-between">
                <div class="question-num">Question <?= $i + 1 ?> of <?= count($questions) ?></div>
                <div class="question-pts text-xs text-muted"><?= $q['marks'] ?> mark<?= $q['marks'] != 1 ? 's' : '' ?></div>
            </div>
            <div class="question-text"><?= nl2br(htmlspecialchars($q['question_text'])) ?></div>
            <ul class="options-list">
                <?php foreach ($q['options'] as $opt): ?>
                <li class="option-item <?= $q['selected'] == $opt['id'] ? 'selected' : '' ?>"
                    id="opt_<?= $opt['id'] ?>">
                    <input type="radio"
                           name="q_<?= $q['id'] ?>"
                           value="<?= $opt['id'] ?>"
                           <?= $q['selected'] == $opt['id'] ? 'checked' : '' ?>
                           onchange="updateCount()">
                    <span><?= htmlspecialchars($opt['option_text']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>

        <div class="card" style="margin-top:20px">
            <div class="card-body flex-between">
                <p class="text-sm text-gray">
                    Make sure you've answered all questions before submitting.
                </p>
                <button type="button"
        class="btn btn-primary btn-lg"
        onclick="askSubmitQuiz()">
    Submit Quiz ✓
</button>
            </div>
        </div>
    </form>
</div>

<?php $end_ms = (time() + $time_remaining) * 1000; ?>

<script>
/* Self-contained quiz script */

// Answer counter
function updateCount() {
    var count = 0;
    document.querySelectorAll('.question-card').forEach(function(card) {
        if (card.querySelector('input[type="radio"]:checked')) count++;
    });
    var el = document.getElementById('answered_count');
    if (el) el.textContent = count;
}

document.querySelectorAll('.option-item').forEach(function(item) {
    item.addEventListener('click', function() {
        var radio = this.querySelector('input[type="radio"]');
        if (!radio) return;
        radio.checked = true;
        this.closest('.question-card')
            .querySelectorAll('.option-item')
            .forEach(function(i) { i.classList.remove('selected'); });
        this.classList.add('selected');
        updateCount();
    });
});
updateCount();

// Timer
var END_MS    = <?= $end_ms ?>;
var SPAN      = document.getElementById('timer_display');
var WRAP      = SPAN ? SPAN.closest('.timer') : null;
var FORM      = document.getElementById('quiz_form');
var submitted = false;

function doSubmit() {
    if (submitted) return;
    submitted = true;
    window.onbeforeunload = null;
    if (FORM) FORM.submit();
}

function tick() {
    if (submitted) return;
    var rem = Math.max(0, Math.floor((END_MS - Date.now()) / 1000));
    var h = Math.floor(rem / 3600);
    var m = Math.floor((rem % 3600) / 60);
    var s = rem % 60;
    var pad = function(n) { return String(n).padStart(2, '0'); };
    if (SPAN) SPAN.textContent = h > 0
        ? pad(h)+':'+pad(m)+':'+pad(s)
        : pad(m)+':'+pad(s);
    if (WRAP) {
        if      (rem <= 60)  WRAP.className = 'timer urgent';
        else if (rem <= 180) WRAP.className = 'timer warn';
        else                 WRAP.className = 'timer';
    }
    if (rem <= 0) { alert('⏰ Time is up! Submitting now.'); doSubmit(); return; }
    setTimeout(tick, 500);
}
tick();

document.addEventListener('visibilitychange', function() {
    if (!document.hidden && !submitted) tick();
});

// ── BACK BUTTON: ask to submit ──────────────────
history.pushState({quiz: true}, '');

window.addEventListener('popstate', function() {
    if (submitted) return;
    var ans = confirm(
        'Do you want to submit your quiz now?\n\n' +
        'OK = Submit quiz\n' +
        'Cancel = Continue quiz'
    );
    if (ans) {
        doSubmit();
    } else {
        // Put the state back so back button works again
        history.pushState({quiz: true}, '');
    }
});

// Normal submit button - remove back button trap
if (FORM) {
    FORM.addEventListener('submit', function() {
        submitted = true;
        window.onbeforeunload = null;
    });
}
document.addEventListener('DOMContentLoaded', function() {
    startTimer(<?= $end_ms ?>, 'timer_display', 'quiz_form');
    updateCount();
});

function askSubmitQuiz() {
    var answered = parseInt(document.getElementById('answered_count').textContent);
    var total    = <?= count($questions) ?>;
    var msg = 'Submit your quiz now?\n\n' + answered + ' of ' + total + ' questions answered.';
    if (answered < total) {
        msg += '\n\n⚠️ ' + (total - answered) + ' unanswered question(s) will score 0.';
    }
    cconfirmShow({
        icon:    '📝',
        title:   'Submit Quiz',
        msg:     msg,
        okText:  'Submit Now',
        okClass: 'btn-success',
        onOk:    function() {
            submitted = true;
            window.onbeforeunload = null;
            document.getElementById('quiz_form').submit();
        }
    });
}
</script>
