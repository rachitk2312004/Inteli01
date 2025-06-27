<?php
require_once 'db_connect.php';
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// Fetch quiz details
$quizQuery = "SELECT title FROM quizzes WHERE id = ?";
$quizStmt = mysqli_prepare($con, $quizQuery);
mysqli_stmt_bind_param($quizStmt, 'i', $quizId);
mysqli_stmt_execute($quizStmt);
$quizResult = mysqli_stmt_get_result($quizStmt);
$quiz = mysqli_fetch_assoc($quizResult);

if (!$quiz) {
    die("Quiz not found");
}

// Fetch user's quiz result
$resultQuery = "SELECT 
    r.total_score, 
    r.prize_earned, 
    r.rank,
    (SELECT COUNT(DISTINCT user_id) FROM quiz_results WHERE quiz_id = ?) AS total_participants
FROM quiz_results r
WHERE r.user_id = ? AND r.quiz_id = ?";
$resultStmt = mysqli_prepare($con, $resultQuery);
mysqli_stmt_bind_param($resultStmt, 'iii', $quizId, $userId, $quizId);
mysqli_stmt_execute($resultStmt);
$resultData = mysqli_stmt_get_result($resultStmt);
$result = mysqli_fetch_assoc($resultData);

if (!$result) {
    die("Results not available yet");
}

// Fetch entry ID
$entryQuery = "SELECT id FROM quiz_entries WHERE user_id = ? AND quiz_id = ?";
$entryStmt = mysqli_prepare($con, $entryQuery);
mysqli_stmt_bind_param($entryStmt, 'ii', $userId, $quizId);
mysqli_stmt_execute($entryStmt);
$entryResult = mysqli_stmt_get_result($entryStmt);
$entry = mysqli_fetch_assoc($entryResult);
$entryId = $entry['id'] ?? 0;

// Fetch user's answers with correct answers
$answersQuery = "SELECT 
    q.id AS question_id,
    q.question_text, 
    q.option_a, 
    q.option_b, 
    q.option_c, 
    q.option_d, 
    q.correct_option,
    a.selected_option
FROM quiz_answers a
JOIN quiz_questions q ON a.question_id = q.id
WHERE a.entry_id = ?
ORDER BY q.id";
$answersStmt = mysqli_prepare($con, $answersQuery);
mysqli_stmt_bind_param($answersStmt, 'i', $entryId);
mysqli_stmt_execute($answersStmt);
$answersResult = mysqli_stmt_get_result($answersStmt);
$answers = mysqli_fetch_all($answersResult, MYSQLI_ASSOC);

// Calculate performance metrics
$totalQuestions = count($answers);
$correctAnswers = 0;
foreach ($answers as $answer) {
    if (strtolower($answer['selected_option']) === strtolower($answer['correct_option'])) {
        $correctAnswers++;
    }
}
$accuracy = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Results - <?= htmlspecialchars($quiz['title']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Orbitron', sans-serif; background: #0e0e1a; color: #f4f4f4; }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 20px; }
        .logo { font-size: 32px; color: #00ffe7; font-weight: bold; letter-spacing: 2px; }
        .logo span { color: #ff00cc; }
        nav a { margin-left: 25px; text-decoration: none; color: #f4f4f4; font-size: 16px; }
        nav a:hover { color: #00ffe7; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        h1, h2 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        
        /* Summary Section */
        .summary-card { 
            background: rgba(255,255,255,0.05); 
            border-radius: 12px; 
            padding: 25px; 
            margin-bottom: 30px;
            border: 1px solid #333;
            text-align: center;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .summary-item {
            padding: 15px;
            border-radius: 8px;
            background: rgba(0, 255, 231, 0.1);
        }
        .summary-item h3 {
            font-size: 16px;
            color: #ccc;
            margin-bottom: 8px;
        }
        .summary-item p {
            font-size: 24px;
            color: #00ffe7;
            font-weight: bold;
        }
        .prize-highlight {
            color: #ffcc00;
            font-size: 28px;
        }
        
        /* Answers Section */
        .answers-container { margin-top: 40px; }
        .question-card { 
            background: rgba(255,255,255,0.05); 
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 10px; 
            border: 1px solid #333; 
        }
        .question-text { font-weight: bold; margin-bottom: 15px; font-size: 18px; }
        .options { margin-left: 20px; }
        .option { margin: 10px 0; padding-left: 25px; position: relative; }
        .option::before { content: "•"; position: absolute; left: 10px; }
        .correct { color: #33ff33; font-weight: bold; }
        .incorrect { color: #ff3333; }
        .user-answer { border-left: 3px solid #00ccff; padding-left: 5px; }
        .correct-answer { border-left: 3px solid #33ff33; padding-left: 5px; }
        .option-label { font-weight: bold; margin-right: 5px; }
        
        /* Performance Meter */
        .performance-meter { 
            height: 20px; 
            background: #333; 
            border-radius: 10px; 
            margin: 15px 0; 
            overflow: hidden;
        }
        .performance-bar { 
            height: 100%; 
            background: linear-gradient(90deg, #ff00cc, #3333ff);
            border-radius: 10px; 
            text-align: center;
            color: white;
            font-size: 12px;
            line-height: 20px;
        }
        
        /* Buttons */
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { 
            padding: 12px 30px; 
            border-radius: 50px; 
            text-decoration: none; 
            display: inline-block; 
            margin: 0 10px;
            background: linear-gradient(90deg, #ff00cc, #3333ff); 
            color: white; 
            font-weight: bold; 
            border: none; 
            cursor: pointer; 
            font-size: 16px;
        }
        .btn:hover { transform: scale(1.05); }
        
        /* Answer Feedback */
        .answer-feedback { 
            margin-top: 15px; 
            padding: 10px; 
            border-radius: 5px;
        }
        .correct-feedback { 
            background: rgba(51, 255, 51, 0.1); 
            color: #33ff33; 
        }
        .incorrect-feedback { 
            background: rgba(255, 51, 51, 0.1); 
            color: #ff3333; 
        }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">Quiz<span>Platform</span></div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="join_quiz.php">Quizzes</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h1>Quiz Results: <?= htmlspecialchars($quiz['title']) ?></h1>
        
        <div class="summary-card">
            <h2>Your Performance Summary</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <h3>Score</h3>
                    <p><?= $result['total_score'] ?> / <?= $totalQuestions ?></p>
                </div>
                <div class="summary-item">
                    <h3>Rank</h3>
                    <p>#<?= $result['rank'] ?> of <?= $result['total_participants'] ?></p>
                </div>
                <div class="summary-item">
                    <h3>Accuracy</h3>
                    <p><?= $accuracy ?>%</p>
                </div>
                <div class="summary-item">
                    <h3>Prize Earned</h3>
                    <p class="prize-highlight">₹<?= number_format($result['prize_earned'], 2) ?></p>
                </div>
            </div>
            
            <div class="performance-meter">
                <div class="performance-bar" style="width: <?= $accuracy ?>%;">
                    <?= $accuracy ?>%
                </div>
            </div>
        </div>
        
        <div class="answers-container">
            <h2>Question-wise Analysis</h2>
            
            <?php foreach ($answers as $index => $answer): ?>
                <?php 
                $isCorrect = (strtolower($answer['selected_option']) === strtolower($answer['correct_option']));
                $optionLabels = ['a', 'b', 'c', 'd'];
                ?>
                
                <div class="question-card">
                    <div class="question-text">Q<?= $index + 1 ?>. <?= htmlspecialchars($answer['question_text']) ?></div>
                    
                    <div class="options">
                        <?php foreach ($optionLabels as $label): ?>
                            <?php if (!empty($answer["option_$label"])): ?>
                                <div class="option <?= $label === $answer['correct_option'] ? 'correct' : '' ?>">
                                    <span class="option-label"><?= strtoupper($label) ?>:</span>
                                    <?= htmlspecialchars($answer["option_$label"]) ?>
                                    
                                    <?php if ($label === $answer['selected_option']): ?>
                                        <span class="user-answer">(Your Answer)</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!$isCorrect && $label === $answer['correct_option']): ?>
                                        <span class="correct-answer">(Correct Answer)</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="answer-feedback <?= $isCorrect ? 'correct-feedback' : 'incorrect-feedback' ?>">
                        <?php if ($isCorrect): ?>
                            ✓ Correct! You earned points for this question.
                        <?php else: ?>
                            ✗ Incorrect. The correct answer was <?= strtoupper($answer['correct_option']) ?>.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="btn-container">
            <a href="dashboard.php" class="btn">Back to Dashboard</a>
            <a href="join_quiz.php" class="btn">Join Another Quiz</a>
            <a href="leaderboard.php?quiz_id=<?= $quizId ?>" class="btn">View Leaderboard</a>
        </div>
    </div>
</body>
</html>