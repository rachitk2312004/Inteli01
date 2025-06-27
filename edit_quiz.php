<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_quizzes.php");
    exit();
}

$quiz_id = intval($_GET['id']);

// Fetch quiz details
$sql = "SELECT * FROM quizzes WHERE id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $quiz_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quiz = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$quiz) {
    header("Location: admin_quizzes.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $entry_fee = (float) $_POST['entry_fee'];
    $max_spots = (int) $_POST['max_spots'];
    $commission_percent = (float) $_POST['commission_percent'];
    $winners = (int) $_POST['winners'];
    $start_time = mysqli_real_escape_string($con, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($con, $_POST['end_time']);

    // Recalculate prize pool
    $gross_pool = $entry_fee * $max_spots;
    $net_pool = round($gross_pool * (1 - $commission_percent / 100), 2);

    // Update quiz
    $sql = "UPDATE quizzes SET 
            title = ?, 
            entry_fee = ?, 
            max_spots = ?, 
            prize_pool = ?, 
            commission_percent = ?, 
            winners = ?, 
            start_time = ?, 
            end_time = ? 
            WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sdiddsssi', 
        $title, $entry_fee, $max_spots, $net_pool, 
        $commission_percent, $winners, $start_time, $end_time, $quiz_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Update prize distribution
        require_once 'create_quiz_backend.php';
        $distribution = calculatePrizeDistribution($net_pool, $winners);
        
        // Delete old distribution
        mysqli_query($con, "DELETE FROM prize_distribution WHERE quiz_id = $quiz_id");
        
        // Insert new distribution
        $stmt = mysqli_prepare($con, "INSERT INTO prize_distribution (quiz_id, rank_start, rank_end, prize_amount) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiid', $quiz_id, $rank_start, $rank_end, $prize_amount);

        $start = $prev = $curr = null;
        foreach ($distribution as $rank => $amount) {
            if ($amount !== $curr) {
                if ($curr !== null) {
                    $rank_start = $start;
                    $rank_end = $prev;
                    $prize_amount = $curr;
                    mysqli_stmt_execute($stmt);
                }
                $start = $rank;
                $curr = $amount;
            }
            $prev = $rank;
        }

        // Final group insert
        if ($curr !== null) {
            $rank_start = $start;
            $rank_end = $prev;
            $prize_amount = $curr;
            mysqli_stmt_execute($stmt);
        }

        mysqli_stmt_close($stmt);
        $success = "Quiz updated successfully!";
    } else {
        $error = "Error updating quiz: " . mysqli_error($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Orbitron', sans-serif; background: #0e0e1a; color: #f4f4f4; }
        .nav { display: flex; justify-content: space-between; align-items: center; padding: 20px; }
        .logo { font-size: 32px; color: #00ffe7; font-weight: bold; letter-spacing: 2px; }
        .logo span { color: #ff00cc; }
        nav a { margin-left: 25px; text-decoration: none; color: #f4f4f4; font-size: 16px; }
        nav a:hover { color: #00ffe7; }
        .container { max-width: 600px; margin: 40px auto; background: rgba(255,255,255,0.05); padding: 30px; border-radius: 20px; border: 1px solid #00ffe7; }
        h2 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        input[type="text"], input[type="number"], input[type="datetime-local"] { width: 100%; padding: 10px; margin: 12px 0; background: #1e1e2f; border: 1px solid #333; border-radius: 10px; color: #fff; font-size: 16px; }
        label { display: block; margin-top: 15px; font-size: 14px; color: #ccc; }
        .btn-glow { background: linear-gradient(90deg, #ff00cc, #3333ff); padding: 10px 20px; border-radius: 50px; color: white; font-weight: bold; border: none; cursor: pointer; transition: 0.3s ease; }
        .btn-glow:hover { transform: scale(1.05); }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #33ff33; color: black; }
        .alert-danger { background-color: #ff3333; color: white; }
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
        <h2>Edit Quiz: <?= htmlspecialchars($quiz['title']) ?></h2>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <label>Quiz Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($quiz['title']) ?>" required>

            <label>Entry Fee (₹)</label>
            <input type="number" id="entry_fee" name="entry_fee" step="0.01" value="<?= $quiz['entry_fee'] ?>" required>

            <label>Total Spots</label>
            <input type="number" id="max_spots" name="max_spots" value="<?= $quiz['max_spots'] ?>" required>

            <label>Commission (%)</label>
            <input type="number" id="commission" name="commission_percent" value="<?= $quiz['commission_percent'] ?>" step="0.01" required>

            <label>Number of Winners</label>
            <input type="number" id="winners" name="winners" value="<?= $quiz['winners'] ?>" required>

            <label>Start Time</label>
            <input type="datetime-local" name="start_time" value="<?= date('Y-m-d\TH:i', strtotime($quiz['start_time'])) ?>" required>

            <label>End Time</label>
            <input type="datetime-local" name="end_time" value="<?= date('Y-m-d\TH:i', strtotime($quiz['end_time'])) ?>" required>

            <div style="text-align:center; margin-top:20px;">
                <button type="submit" class="btn-glow">Update Quiz</button>
            </div>
        </form>
    </div>

    <script>
    // You can add the same JavaScript from create_quiz.php here if you want dynamic prize calculations
    </script>
</body>
</html>