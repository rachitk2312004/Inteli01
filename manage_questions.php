<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['quiz_id'])) {
    header("Location: admin_quizzes.php");
    exit();
}

$quiz_id = intval($_GET['quiz_id']);

// Fetch quiz details
$quiz_sql = "SELECT title FROM quizzes WHERE id = ?";
$quiz_stmt = mysqli_prepare($con, $quiz_sql);
mysqli_stmt_bind_param($quiz_stmt, 'i', $quiz_id);
mysqli_stmt_execute($quiz_stmt);
$quiz_result = mysqli_stmt_get_result($quiz_stmt);
$quiz = mysqli_fetch_assoc($quiz_result);
mysqli_stmt_close($quiz_stmt);

if (!$quiz) {
    header("Location: admin_quizzes.php");
    exit();
}

// Handle question deletion
if (isset($_GET['delete'])) {
    $question_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM quiz_questions WHERE id = ? AND quiz_id = ?";
    $delete_stmt = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, 'ii', $question_id, $quiz_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    header("Location: manage_questions.php?quiz_id=$quiz_id");
    exit();
}

// Fetch all questions for this quiz
$questions_sql = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id";
$questions_stmt = mysqli_prepare($con, $questions_sql);
mysqli_stmt_bind_param($questions_stmt, 'i', $quiz_id);
mysqli_stmt_execute($questions_stmt);
$questions_result = mysqli_stmt_get_result($questions_stmt);
$questions = mysqli_fetch_all($questions_result, MYSQLI_ASSOC);
mysqli_stmt_close($questions_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Questions</title>
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
        h1 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; margin-right: 5px; }
        .btn-primary { background-color: #3333ff; color: white; }
        .btn-danger { background-color: #ff3333; color: white; }
        .btn-success { background-color: #33ff33; color: black; }
        .question-card { background: rgba(255,255,255,0.05); padding: 20px; margin-bottom: 20px; border-radius: 10px; border: 1px solid #333; }
        .question-text { font-weight: bold; margin-bottom: 15px; }
        .options { margin-left: 20px; }
        .correct { color: #33ff33; font-weight: bold; }
        .difficulty { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .easy { background-color: #33ff33; color: black; }
        .medium { background-color: #ffcc00; color: black; }
        .hard { background-color: #ff3333; color: white; }
        .actions { margin-top: 15px; }
        .add-question { text-align: center; margin: 30px 0; }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">Admin<span>Panel</span></div>
        <nav>
            <a href="admin_quizzes.php">Quizzes</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h1>Manage Questions: <?= htmlspecialchars($quiz['title']) ?></h1>
        
        <div class="add-question">
            <a href="add_question.php?quiz_id=<?= $quiz_id ?>" class="btn btn-success">Add New Question</a>
        </div>
        
        <?php if (empty($questions)): ?>
            <p style="text-align:center;">No questions found for this quiz.</p>
        <?php else: ?>
            <?php foreach ($questions as $question): ?>
                <div class="question-card">
                    <div class="question-text"><?= htmlspecialchars($question['question_text']) ?></div>
                    <div class="difficulty <?= $question['difficulty'] ?>"><?= ucfirst($question['difficulty']) ?></div>
                    
                    <div class="options">
                        <div>A. <?= htmlspecialchars($question['option_a']) ?></div>
                        <div>B. <?= htmlspecialchars($question['option_b']) ?></div>
                        <?php if (!empty($question['option_c'])): ?>
                            <div>C. <?= htmlspecialchars($question['option_c']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($question['option_d'])): ?>
                            <div>D. <?= htmlspecialchars($question['option_d']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="correct">Correct Answer: <?= strtoupper($question['correct_option']) ?></div>
                    
                    <div class="actions">
                        <a href="edit_question.php?id=<?= $question['id'] ?>&quiz_id=<?= $quiz_id ?>" class="btn btn-primary">Edit</a>
                        <a href="manage_questions.php?quiz_id=<?= $quiz_id ?>&delete=<?= $question['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>