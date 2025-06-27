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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = mysqli_real_escape_string($con, $_POST['question_text']);
    $option_a = mysqli_real_escape_string($con, $_POST['option_a']);
    $option_b = mysqli_real_escape_string($con, $_POST['option_b']);
    $option_c = isset($_POST['option_c']) ? mysqli_real_escape_string($con, $_POST['option_c']) : null;
    $option_d = isset($_POST['option_d']) ? mysqli_real_escape_string($con, $_POST['option_d']) : null;
    $correct_option = mysqli_real_escape_string($con, $_POST['correct_option']);
    $difficulty = mysqli_real_escape_string($con, $_POST['difficulty']);

    $sql = "INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'isssssss', 
        $quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $difficulty);
    
    if (mysqli_stmt_execute($stmt)) {
        $success = "Question added successfully!";
    } else {
        $error = "Error adding question: " . mysqli_error($con);
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Orbitron', sans-serif; background: #0e0e1a; color: #f4f4f4; }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 20px; }
        .logo { font-size: 32px; color: #00ffe7; font-weight: bold; letter-spacing: 2px; }
        .logo span { color: #ff00cc; }
        nav a { margin-left: 25px; text-decoration: none; color: #f4f4f4; font-size: 16px; }
        nav a:hover { color: #00ffe7; }
        .container { max-width: 800px; margin: 40px auto; background: rgba(255,255,255,0.05); padding: 30px; border-radius: 20px; border: 1px solid #00ffe7; }
        h1 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        textarea, input[type="text"], select { width: 100%; padding: 10px; margin: 12px 0; background: #1e1e2f; border: 1px solid #333; border-radius: 10px; color: #fff; font-size: 16px; }
        label { display: block; margin-top: 15px; font-size: 14px; color: #ccc; }
        .btn-glow { background: linear-gradient(90deg, #ff00cc, #3333ff); padding: 10px 20px; border-radius: 50px; color: white; font-weight: bold; border: none; cursor: pointer; transition: 0.3s ease; }
        .btn-glow:hover { transform: scale(1.05); }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #33ff33; color: black; }
        .alert-danger { background-color: #ff3333; color: white; }
        .option-container { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">Admin<span>Panel</span></div>
        <nav>
            <a href="admin_quizzes.php">Quizzes</a>
            <a href="manage_questions.php?quiz_id=<?= $quiz_id ?>">Back to Questions</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h1>Add Question to: <?= htmlspecialchars($quiz['title']) ?></h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Question Text</label>
            <textarea name="question_text" rows="3" required></textarea>
            
            <div class="option-container">
                <label>Option A (Required)</label>
                <input type="text" name="option_a" required>
            </div>
            
            <div class="option-container">
                <label>Option B (Required)</label>
                <input type="text" name="option_b" required>
            </div>
            
            <div class="option-container">
                <label>Option C (Optional)</label>
                <input type="text" name="option_c">
            </div>
            
            <div class="option-container">
                <label>Option D (Optional)</label>
                <input type="text" name="option_d">
            </div>
            
            <label>Correct Answer</label>
            <select name="correct_option" required>
                <option value="">Select correct option</option>
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="c">C</option>
                <option value="d">D</option>
            </select>
            
            <label>Difficulty Level</label>
            <select name="difficulty" required>
                <option value="easy">Easy</option>
                <option value="medium">Medium</option>
                <option value="hard">Hard</option>
            </select>
            
            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-glow">Add Question</button>
            </div>
        </form>
    </div>
</body>
</html>