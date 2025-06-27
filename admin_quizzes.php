<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Handle quiz activation/deactivation
if (isset($_POST['toggle_quiz'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $sql = "UPDATE quizzes SET is_active = NOT is_active WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $quiz_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: admin_quizzes.php");
    exit();
}

// Fetch all quizzes
$sql = "SELECT * FROM quizzes ORDER BY created_at DESC";
$result = mysqli_query($con, $sql);
$quizzes = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Quizzes</title>
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
        h1 { text-align: center; margin-bottom: 30px; color: #00ffe7; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { background-color: #1a1a2e; color: #00ffe7; }
        tr:hover { background-color: #1a1a2e; }
        .btn { padding: 8px 16px; border-radius: 4px; text-decoration: none; margin-right: 5px; }
        .btn-primary { background-color: #3333ff; color: white; }
        .btn-danger { background-color: #ff3333; color: white; }
        .btn-success { background-color: #33ff33; color: black; }
        .btn-warning { background-color: #ffcc00; color: black; }
        .status-active { color: #33ff33; }
        .status-inactive { color: #ff3333; }
        .create-btn { display: block; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="nav">
        <div class="logo">Admin<span>Panel</span></div>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="create_quiz.php">Create Quiz</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

    <div class="container">
        <h1>Manage Quizzes</h1>
        
        <a href="create_quiz.php" class="btn btn-primary create-btn">Create New Quiz</a>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Entry Fee</th>
                    <th>Spots</th>
                    <th>Prize Pool</th>
                    <th>Winners</th>
                    <th>Status</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $quiz): ?>
                <tr>
                    <td><?= htmlspecialchars($quiz['id']) ?></td>
                    <td><?= htmlspecialchars($quiz['title']) ?></td>
                    <td>₹<?= number_format($quiz['entry_fee'], 2) ?></td>
                    <td><?= $quiz['max_spots'] ?></td>
                    <td>₹<?= number_format($quiz['prize_pool'], 2) ?></td>
                    <td><?= $quiz['winners'] ?></td>
                    <td class="<?= $quiz['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $quiz['is_active'] ? 'Active' : 'Inactive' ?>
                    </td>
                    <td><?= date('d M Y H:i', strtotime($quiz['start_time'])) ?></td>
                    <td><?= date('d M Y H:i', strtotime($quiz['end_time'])) ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
                            <button type="submit" name="toggle_quiz" class="btn <?= $quiz['is_active'] ? 'btn-danger' : 'btn-success' ?>">
                                <?= $quiz['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <a href="edit_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-warning">Edit</a>
                        <a href="manage_questions.php?quiz_id=<?= $quiz['id'] ?>" class="btn btn-primary">Questions</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>