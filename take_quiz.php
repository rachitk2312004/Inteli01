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
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Verify user has joined this quiz and it's active/live
$quizQuery = "SELECT 
    q.id, q.title, q.start_time, q.end_time, 
    e.status, e.id AS entry_id
FROM quizzes q
JOIN quiz_entries e ON q.id = e.quiz_id
WHERE e.user_id = ? AND q.id = ? AND e.status = 'active'";
$stmt = mysqli_prepare($con, $quizQuery);
mysqli_stmt_bind_param($stmt, 'ii', $userId, $quizId);
mysqli_stmt_execute($stmt);
$quizResult = mysqli_stmt_get_result($stmt);
$quiz = mysqli_fetch_assoc($quizResult);

if (!$quiz) {
    header("Location: join_quiz.php");
    exit();
}

// Convert times to timestamps
$startTime = strtotime($quiz['start_time']);
$endTime = strtotime($quiz['end_time']);
$currentTime = time();

// Prevent access before start time
if ($currentTime < $startTime) {
    header("Location: join_quiz.php");
    exit();
}

// Auto-submit if time has passed
if ($currentTime > $endTime) {
    // Update status to submitted
    $updateQuery = "UPDATE quiz_entries SET status = 'submitted' WHERE id = ?";
    $updateStmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, 'i', $quiz['entry_id']);
    mysqli_stmt_execute($updateStmt);
    
    header("Location: join_quiz.php");
    exit();
}

// Fetch quiz questions
$questionsQuery = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id";
$questionsStmt = mysqli_prepare($con, $questionsQuery);
mysqli_stmt_bind_param($questionsStmt, 'i', $quizId);
mysqli_stmt_execute($questionsStmt);
$questionsResult = mysqli_stmt_get_result($questionsStmt);
$questions = mysqli_fetch_all($questionsResult, MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        $score = 0;
        $totalQuestions = count($questions);
        $entryId = $quiz['entry_id'];
        
        // Store answers and calculate score
        foreach ($questions as $question) {
            $answerKey = 'question_' . $question['id'];
            $selectedOption = isset($_POST[$answerKey]) ? $_POST[$answerKey] : null;
            $isCorrect = 0;
            
            if ($selectedOption && strtolower($selectedOption) === strtolower($question['correct_option'])) {
                $isCorrect = 1;
                $score++;
            }
            
            // Insert into quiz_answers
            $answerInsert = "INSERT INTO quiz_answers 
                (entry_id, question_id, selected_option, is_correct) 
                VALUES (?, ?, ?, ?)";
            $answerStmt = mysqli_prepare($con, $answerInsert);
            mysqli_stmt_bind_param($answerStmt, 'iisi', $entryId, $question['id'], $selectedOption, $isCorrect);
            mysqli_stmt_execute($answerStmt);
        }
        
        // Update quiz entry as submitted
        $updateQuery = "UPDATE quiz_entries SET status = 'submitted' WHERE id = ?";
        $updateStmt = mysqli_prepare($con, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, 'i', $entryId);
        mysqli_stmt_execute($updateStmt);
        
        // Calculate prize (simplified - you'll need real logic)
        $prizeEarned = 0.00;
        $percentage = ($score / $totalQuestions) * 100;
        
        // Insert into quiz_results
        $resultInsert = "INSERT INTO quiz_results 
            (user_id, quiz_id, total_score, prize_earned) 
            VALUES (?, ?, ?, ?)";
        $resultStmt = mysqli_prepare($con, $resultInsert);
        mysqli_stmt_bind_param($resultStmt, 'iiid', $userId, $quizId, $score, $prizeEarned);
        mysqli_stmt_execute($resultStmt);
        
        // Commit transaction
        mysqli_commit($con);
        
        header("Location: quiz_result.php?quiz_id=$quizId");
        exit();
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($con);
        error_log("Quiz submission error: " . $e->getMessage());
        $error = "An error occurred while submitting your quiz. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Quiz: <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Orbitron', sans-serif; background: #0e0e1a; color: #f4f4f4; }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 20px; }
        .logo { font-size: 32px; color: #00ffe7; font-weight: bold; letter-spacing: 2px; }
        .logo span { color: #ff00cc; }
        nav a { margin-left: 25px; text-decoration: none; color: #f4f4f4; font-size: 16px; }
        nav a:hover { color: #00ffe7; }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; }
        h1 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        .quiz-info { text-align: center; margin-bottom: 30px; }
        .timer { font-size: 24px; color: #ff00cc; text-align: center; margin: 20px 0; }
        .question-card { background: rgba(255,255,255,0.05); padding: 20px; margin-bottom: 20px; border-radius: 10px; border: 1px solid #333; }
        .question-text { font-weight: bold; margin-bottom: 15px; font-size: 18px; }
        .options { margin-left: 20px; }
        .option { margin: 10px 0; }
        .option input { margin-right: 10px; }
        .btn { padding: 12px 25px; border-radius: 50px; text-decoration: none; display: inline-block; margin-top: 20px; 
               background: linear-gradient(90deg, #ff00cc, #3333ff); color: white; font-weight: bold; border: none; cursor: pointer; }
        .btn:hover { transform: scale(1.05); }
        .time-warning { color: #ff3333; font-weight: bold; text-align: center; margin: 20px 0; }
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 5px; font-size: 14px; margin-bottom: 10px; }
        .status-live { background-color: rgba(0, 255, 0, 0.2); color: #00ff00; }
        .error { color: #ff3333; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">Quiz<span>Platform</span></div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="join_quiz.php">Quizzes</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="quiz-info">
            <div class="status-badge status-live">LIVE NOW</div>
            <p>Time Remaining: <span class="timer" id="quiz-timer"></span></p>
            <p class="time-warning">Complete the quiz before time runs out!</p>
        </div>
        
        <form method="POST" id="quiz-form">
            <?php foreach ($questions as $index => $question): ?>
                <div class="question-card">
                    <div class="question-text"><?= ($index + 1) ?>. <?= htmlspecialchars($question['question_text']) ?></div>
                    <div class="options">
                        <div class="option">
                            <input type="radio" name="question_<?= $question['id'] ?>" id="q<?= $question['id'] ?>_a" value="a" required>
                            <label for="q<?= $question['id'] ?>_a">A. <?= htmlspecialchars($question['option_a']) ?></label>
                        </div>
                        <div class="option">
                            <input type="radio" name="question_<?= $question['id'] ?>" id="q<?= $question['id'] ?>_b" value="b">
                            <label for="q<?= $question['id'] ?>_b">B. <?= htmlspecialchars($question['option_b']) ?></label>
                        </div>
                        <?php if (!empty($question['option_c'])): ?>
                        <div class="option">
                            <input type="radio" name="question_<?= $question['id'] ?>" id="q<?= $question['id'] ?>_c" value="c">
                            <label for="q<?= $question['id'] ?>_c">C. <?= htmlspecialchars($question['option_c']) ?></label>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($question['option_d'])): ?>
                        <div class="option">
                            <input type="radio" name="question_<?= $question['id'] ?>" id="q<?= $question['id'] ?>_d" value="d">
                            <label for="q<?= $question['id'] ?>_d">D. <?= htmlspecialchars($question['option_d']) ?></label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="text-align: center;">
                <button type="submit" class="btn">Submit Quiz</button>
            </div>
        </form>
    </div>

    <script>
    // Timer countdown
    function updateTimer() {
        const endTime = <?= $endTime ?> * 1000;
        const now = new Date().getTime();
        const distance = endTime - now;
        
        if (distance < 0) {
            document.getElementById("quiz-timer").innerHTML = "TIME EXPIRED";
            document.getElementById("quiz-form").submit();
            return;
        }
        
        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById("quiz-timer").innerHTML = 
            `${hours}h ${minutes}m ${seconds}s`;
    }
    
    updateTimer();
    setInterval(updateTimer, 1000);
    
    // Prevent accidental navigation
    window.addEventListener('beforeunload', function(e) {
        e.preventDefault();
        e.returnValue = 'Are you sure you want to leave? Your progress will be lost.';
    });
    
    // Remove the warning when form is submitted
    document.getElementById('quiz-form').addEventListener('submit', function() {
        window.removeEventListener('beforeunload');
    });
    </script>
</body>
</html>