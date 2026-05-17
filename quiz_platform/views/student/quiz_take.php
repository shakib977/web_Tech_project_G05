<?php
// views/student/quiz_take.php — MEMBER 1
require 'views/layout/header.php';
$end_ms = (time() + $time_remaining) * 1000;
?>

<div class="quiz-wrap">
    <!-- Sticky Timer Bar -->
    <div class="quiz-sticky-bar">
        <div>
            <div class="fw-bold" style="font-size:15px">
                <?= htmlspecialchars($quiz['title']) ?>
            </div>
            <div class="text-xs text-muted">
                <?= htmlspecialchars($quiz['course_title']) ?>
            </div>
        </div>
        <div class="timer" id="timer_wrap">
            ⏱ <span id="timer_display">--:--</span>
        </div>
        <div class="q-progress">
            <span id="answered_count">0</span> / <?= count($questions) ?> answered
        </div>
    </div>

    <form method="POST"
          action="index.php?page=student&action=submit_quiz"
          id="quiz_form">
        <input type="hidden" name="attempt_id" value="<?= $attempt_id ?>">
        <input type="hidden" name="quiz_id"    value="<?= $quiz['id'] ?>">

        <?php foreach ($questions as $i => $q): ?>
        <div class="question-card" id="qcard_<?= $q['id'] ?>">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px">
                <div class="question-num">
                    Question <?= $i+1 ?> of <?= count($questions) ?>
                </div>
                <div class="text-xs text-muted">
                    <?= $q['marks'] ?> mark<?= $q['marks']!=1?'s':'' ?>
                </div>
            </div>
            <div class="question-text">
                <?= nl2br(htmlspecialchars($q['question_text'])) ?>
            </div>
            <ul class="options-list">
                <?php foreach ($q['options'] as $opt): ?>
                <li class="option-item <?= $q['selected']==$opt['id']?'selected':'' ?>"
                    onclick="selectOption(this, '<?= $q['id'] ?>', <?= $opt['id'] ?>)">
                    <input type="radio"
                           name="q_<?= $q['id'] ?>"
                           value="<?= $opt['id'] ?>"
                           <?= $q['selected']==$opt['id']?'checked':'' ?>>
                    <span><?= htmlspecialchars($opt['option_text']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endforeach; ?>

        <!-- Submit area -->
        <div class="card" style="margin-top:20px">
            <div class="card-body flex-between">
                <p class="text-sm text-gray">
                    Answer all questions before submitting.
                </p>
                <!-- type="button" prevents accidental form submit -->
                <button type="button"
                        id="submit_quiz_btn"
                        class="btn btn-success btn-lg"
                        onclick="doAskSubmit()">
                    ✓ Submit Quiz
                </button>
            </div>
        </div>
    </form>
</div>

<!-- All quiz JS is self-contained here — no main.js dependency -->
<script>
var END_MS    = <?= $end_ms ?>;
var TOTAL_Q   = <?= count($questions) ?>;
var submitted = false;

// ── Option selection ──────────────────────────
function selectOption(el, questionId, optionId) {
    var card = el.closest('.question-card');
    card.querySelectorAll('.option-item').forEach(function(i) {
        i.classList.remove('selected');
    });
    el.classList.add('selected');
    var radio = el.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
    updateCount();
}

function updateCount() {
    var count = 0;
    document.querySelectorAll('.question-card').forEach(function(card) {
        if (card.querySelector('input[type="radio"]:checked')) count++;
    });
    var el = document.getElementById('answered_count');
    if (el) el.textContent = count;
}

// ── Submit quiz ───────────────────────────────
function doAskSubmit() {
    var answered = 0;
    document.querySelectorAll('.question-card').forEach(function(card) {
        if (card.querySelector('input[type="radio"]:checked')) answered++;
    });
    var unanswered = TOTAL_Q - answered;
    var msg = answered + ' of ' + TOTAL_Q + ' questions answered.';
    if (unanswered > 0) {
        msg += '\n\n⚠️ ' + unanswered + ' question' +
               (unanswered>1?'s are':' is') + ' unanswered and will score 0.';
    }
    msg += '\n\nYou cannot change answers after submission.';

    // Use cconfirmShow if available (from footer), else fallback
    if (typeof cconfirmShow === 'function') {
        cconfirmShow({
            icon:    '📝',
            title:   'Submit Quiz?',
            msg:     msg,
            okText:  'Submit Now',
            okClass: 'btn-success',
            onOk:    function() { doSubmit(); }
        });
    } else {
        if (confirm('Submit quiz?\n\n' + msg)) doSubmit();
    }
}

function doSubmit() {
    if (submitted) return;
    submitted = true;
    window.onbeforeunload = null;
    document.getElementById('quiz_form').submit();
}

// ── Timer ─────────────────────────────────────
var SPAN = document.getElementById('timer_display');
var WRAP = document.getElementById('timer_wrap');

function tick() {
    if (submitted) return;
    var rem = Math.max(0, Math.floor((END_MS - Date.now()) / 1000));
    var h = Math.floor(rem / 3600);
    var m = Math.floor((rem % 3600) / 60);
    var s = rem % 60;
    var pad = function(n) { return String(n).padStart(2,'0'); };
    if (SPAN) {
        SPAN.textContent = h > 0
            ? pad(h)+':'+pad(m)+':'+pad(s)
            : pad(m)+':'+pad(s);
    }
    if (WRAP) {
        WRAP.className = 'timer' +
            (rem<=60?' urgent': rem<=180?' warn':'');
    }
    if (rem <= 0) {
        window.onbeforeunload = null;
        alert('⏰ Time is up! Your quiz will now be submitted.');
        doSubmit();
        return;
    }
    setTimeout(tick, 500);
}
tick();

// Tab/back handling
document.addEventListener('visibilitychange', function() {
    if (!document.hidden && !submitted) tick();
});

history.pushState({quiz:true}, '');
window.addEventListener('popstate', function() {
    if (submitted) return;
    doAskSubmit();
    setTimeout(function() {
        if (!submitted) history.pushState({quiz:true}, '');
    }, 100);
});

updateCount();
</script>

<?php require 'views/layout/footer.php'; ?>