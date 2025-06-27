<?php
require_once 'db_connect.php';
session_start();

// Set default timezone to IST
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Handle quiz joining
if (isset($_POST['join_quiz'])) {
    $quizId = intval($_POST['quiz_id']);
    
    // Check if user already joined
    $checkQuery = "SELECT id FROM quiz_entries WHERE user_id = ? AND quiz_id = ?";
    $stmt = mysqli_prepare($con, $checkQuery);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $quizId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "You've already joined this quiz!";
    } else {
        // Check if quiz is joinable
        $quizQuery = "SELECT id, start_time, entry_fee FROM quizzes WHERE id = ? AND start_time > NOW()";
        $quizStmt = mysqli_prepare($con, $quizQuery);
        mysqli_stmt_bind_param($quizStmt, 'i', $quizId);
        mysqli_stmt_execute($quizStmt);
        $quizResult = mysqli_stmt_get_result($quizStmt);
        $quiz = mysqli_fetch_assoc($quizResult);
        
        if ($quiz) {
            // Insert quiz entry
            $insertQuery = "INSERT INTO quiz_entries (user_id, quiz_id, status) VALUES (?, ?, 'active')";
            $insertStmt = mysqli_prepare($con, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, 'ii', $userId, $quizId);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $success = "Successfully joined the quiz!";
            } else {
                $error = "Error joining quiz: " . mysqli_error($con);
            }
        } else {
            $error = "Quiz is no longer available for joining";
        }
    }
}

// Fetch all joinable quizzes (not started yet)
$quizzesQuery = "SELECT 
    q.id, 
    q.title, 
    q.entry_fee, 
    q.max_spots, 
    q.prize_pool, 
    q.start_time, 
    q.end_time,
    COUNT(e.id) AS participants_count
FROM quizzes q
LEFT JOIN quiz_entries e ON q.id = e.quiz_id AND e.status = 'active'
WHERE q.start_time > NOW()
GROUP BY q.id
ORDER BY q.start_time ASC";
$quizzesResult = mysqli_query($con, $quizzesQuery);
$quizzes = mysqli_fetch_all($quizzesResult, MYSQLI_ASSOC);

// Fetch quizzes user has joined
$joinedQuery = "SELECT 
    q.id, 
    q.title, 
    q.start_time, 
    q.end_time,
    e.status
FROM quizzes q
JOIN quiz_entries e ON q.id = e.quiz_id
WHERE e.user_id = ?
ORDER BY q.start_time ASC";
$joinedStmt = mysqli_prepare($con, $joinedQuery);
mysqli_stmt_bind_param($joinedStmt, 'i', $userId);
mysqli_stmt_execute($joinedStmt);
$joinedResult = mysqli_stmt_get_result($joinedStmt);
$joinedQuizzes = mysqli_fetch_all($joinedResult, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Orbitron', sans-serif; background: #0e0e1a; color: #f4f4f4; }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 20px; }
        .logo { font-size: 32px; color: #00ffe7; font-weight: bold; letter-spacing: 2px; }
        .logo span { color: #ff00cc; }
        nav a { margin-left: 25px; text-decoration: none; color: #f4f4f4; font-size: 16px; }
        nav a:hover { color: #00ffe7; }
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        h1, h2 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        .quiz-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .quiz-card { background: rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; border: 1px solid #333; }
        .quiz-card h3 { color: #00ffe7; margin-bottom: 15px; font-size: 20px; }
        .quiz-card p { margin: 8px 0; color: #ccc; }
        .quiz-card .highlight { color: #00ffe7; font-weight: bold; }
        .btn { padding: 10px 20px; border-radius: 50px; text-decoration: none; display: inline-block; margin-top: 15px; cursor: pointer; }
        .btn-primary { background: linear-gradient(90deg, #3333ff, #00ccff); color: white; font-weight: bold; }
        .btn-success { background: linear-gradient(90deg, #33ff33, #00cc00); color: black; font-weight: bold; }
        .btn-danger { background: linear-gradient(90deg, #ff3333, #cc0000); color: white; font-weight: bold; }
        .btn-disabled { background: #666; color: #ccc; cursor: not-allowed; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .alert-success { background-color: rgba(51, 255, 51, 0.2); border: 1px solid #33ff33; }
        .alert-danger { background-color: rgba(255, 51, 51, 0.2); border: 1px solid #ff3333; }
        .section { margin-bottom: 40px; }
        .progress-bar { height: 10px; background: #333; border-radius: 5px; margin: 10px 0; }
        .progress { height: 100%; background: linear-gradient(90deg, #ff00cc, #3333ff); border-radius: 5px; }
        .countdown { text-align: center; margin-top: 10px; font-size: 14px; color: #ff00cc; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 5px; font-size: 12px; }
        .status-pending { background-color: rgba(255, 204, 0, 0.2); color: #ffcc00; }
        .status-active { background-color: rgba(0, 255, 0, 0.2); color: #00ff00; }
        .status-ended { background-color: rgba(255, 0, 0, 0.2); color: #ff6666; }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">Inteli<span>01</span></div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="my_entries.php">My Quizzes</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="section">
            <h1>Available Quizzes</h1>
            <?php if (empty($quizzes)): ?>
                <p style="text-align: center;">No quizzes available at the moment. Check back later!</p>
            <?php else: ?>
                <div class="quiz-grid">
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="quiz-card">
                            <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                            <p>Entry Fee: <span class="highlight">₹<?= number_format($quiz['entry_fee'], 2) ?></span></p>
                            <p>Prize Pool: <span class="highlight">₹<?= number_format($quiz['prize_pool'], 2) ?></span></p>
                            <p>Starts: <span class="highlight"><?= date('d M Y H:i', strtotime($quiz['start_time'])) ?> IST</span></p>
                            <p>Ends: <span class="highlight"><?= date('d M Y H:i', strtotime($quiz['end_time'])) ?> IST</span></p>
                            
                            <div class="progress-bar">
                                <div class="progress" style="width: <?= min(100, ($quiz['participants_count'] / $quiz['max_spots']) * 100) ?>%"></div>
                            </div>
                            <p>Spots: <?= $quiz['participants_count'] ?> / <?= $quiz['max_spots'] ?></p>
                            
                            <form method="POST">
                                <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                                <button type="submit" name="join_quiz" class="btn btn-primary">
                                    Join Quiz (₹<?= number_format($quiz['entry_fee'], 2) ?>)
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Your Joined Quizzes</h2>
            <?php if (empty($joinedQuizzes)): ?>
                <p style="text-align: center;">You haven't joined any quizzes yet.</p>
            <?php else: ?>
                <div class="quiz-grid">
                    <?php foreach ($joinedQuizzes as $quiz): ?>
                        <div class="quiz-card">
                            <h3><?= htmlspecialchars($quiz['title']) ?></h3>
                            <?php 
                            // Convert times to IST
                            $startTime = strtotime($quiz['start_time']);
                            $endTime = strtotime($quiz['end_time']);
                            $currentTime = time();
                            
                            $quizLive = ($currentTime >= $startTime && $currentTime <= $endTime);
                            $quizEnded = ($currentTime > $endTime);
                            $quizPending = ($currentTime < $startTime);
                            
                            $statusClass = '';
                            $statusText = '';
                            
                            if ($quizLive) {
                                $statusClass = 'status-active';
                                $statusText = 'Live Now';
                            } elseif ($quizEnded) {
                                $statusClass = 'status-ended';
                                $statusText = 'Ended';
                            } else {
                                $statusClass = 'status-pending';
                                $statusText = 'Upcoming';
                            }
                            ?>
                            
                            <p>Status: <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></p>
                            <p>Starts: <span class="highlight"><?= date('d M Y H:i', $startTime) ?> IST</span></p>
                            <p>Ends: <span class="highlight"><?= date('d M Y H:i', $endTime) ?> IST</span></p>
                            
                            <div class="countdown" id="countdown-<?= $quiz['id'] ?>">
                                <?php if ($quizPending): ?>
                                    Starts in: <span id="timer-<?= $quiz['id'] ?>"></span>
                                <?php elseif ($quizLive): ?>
                                    Ends in: <span id="timer-<?= $quiz['id'] ?>"></span>
                                <?php else: ?>
                                    Quiz has ended
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($quizLive && $quiz['status'] == 'active'): ?>
                                <a href="take_quiz.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-success">Take Quiz Now</a>
                            <?php elseif ($quizPending): ?>
                                <button id="take-btn-<?= $quiz['id'] ?>" class="btn btn-disabled" data-start-time="<?= $startTime ?>">Starts Soon</button>
                            <?php elseif ($quizEnded): ?>
                                <button class="btn btn-disabled">Quiz Ended</button>
                            <?php endif; ?>
                            
                            <?php if ($quizPending && $quiz['status'] == 'active'): ?>
                                <form method="POST" action="cancel_quiz.php" style="margin-top: 10px;">
                                    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                                    <button type="submit" class="btn btn-danger">Cancel Entry</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Function to update countdown timers
    function updateTimers() {
        // Update countdowns for joined quizzes
        <?php foreach ($joinedQuizzes as $quiz): ?>
            <?php 
            $startTime = strtotime($quiz['start_time']);
            $endTime = strtotime($quiz['end_time']);
            ?>
            <?php if (!(time() > $endTime)): ?>
                const quizId = <?= $quiz['id'] ?>;
                const startTime = <?= $startTime ?> * 1000;
                const endTime = <?= $endTime ?> * 1000;
                const now = new Date().getTime();
                
                let distance, timeText;
                
                if (now < startTime) {
                    // Quiz hasn't started yet
                    distance = startTime - now;
                    timeText = "Starts in: ";
                } else if (now < endTime) {
                    // Quiz is live
                    distance = endTime - now;
                    timeText = "Ends in: ";
                } else {
                    // Quiz has ended
                    continue;
                }
                
                const hours = Math.floor(distance / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                const timerElement = document.getElementById(`timer-${quizId}`);
                if (timerElement) {
                    timerElement.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
                }
                
                // Enable take quiz button when start time arrives
                if (now >= startTime) {
                    const takeBtn = document.getElementById(`take-btn-${quizId}`);
                    if (takeBtn) {
                        takeBtn.classList.remove('btn-disabled');
                        takeBtn.classList.add('btn-success');
                        takeBtn.innerHTML = 'Take Quiz Now';
                        takeBtn.onclick = function() {
                            window.location.href = `take_quiz.php?quiz_id=${quizId}`;
                        };
                    }
                }
            <?php endif; ?>
        <?php endforeach; ?>
    }
    
    // Initial update
    updateTimers();
    
    // Update every second
    setInterval(updateTimers, 1000);
    
    // Enable buttons when start time arrives
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('[data-start-time]');
        buttons.forEach(button => {
            const startTime = parseInt(button.getAttribute('data-start-time')) * 1000;
            const now = Date.now();
            
            if (now >= startTime) {
                const quizId = button.id.split('-')[2];
                button.classList.remove('btn-disabled');
                button.classList.add('btn-success');
                button.innerHTML = 'Take Quiz Now';
                button.onclick = function() {
                    window.location.href = `take_quiz.php?quiz_id=${quizId}`;
                };
            }
        });
    });
    </script>
</body>
</html>