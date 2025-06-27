<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
$userId = $_SESSION['user_id'];

// Enable error reporting (you can disable this in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch user data
$userQuery = mysqli_query($con, "
    SELECT username, wallet_balance, is_admin
    FROM users
    WHERE id = $userId
");
$user = mysqli_fetch_assoc($userQuery);

$username      = $user['username'] ?? 'User';
$walletBalance = $user['wallet_balance'] ?? 0;
$isAdmin       = (bool)($user['is_admin'] ?? false);

// Stats
$quizzesPlayed = mysqli_fetch_assoc(
    mysqli_query($con, "SELECT COUNT(*) as total FROM quiz_entries WHERE user_id = $userId")
)['total'] ?? 0;

$totalWinnings = mysqli_fetch_assoc(
    mysqli_query($con, "SELECT SUM(prize_earned) as total FROM quiz_results WHERE user_id = $userId")
)['total'] ?? 0.00;

// Admin-specific stats
if ($isAdmin) {
    $quizzesCreated = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT COUNT(*) as total FROM quizzes")
    )['total'] ?? 0;
    
    $totalCommission = mysqli_fetch_assoc(
        mysqli_query($con, "SELECT SUM(entry_fee * max_spots * (commission_percent/100)) as total FROM quizzes")
    )['total'] ?? 0.00;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Inteli01</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Orbitron', sans-serif;
    background-color: #0e0e1a;
    color: #f4f4f4;
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background-color: #1a1a2e;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: fixed;
    height: 100vh;
    box-shadow: 2px 0 10px rgba(0,0,0,0.5);
    padding: 30px 0;
}
.sidebar h2 {
    color: #00ffe7;
    font-size: 20px;
    margin-bottom: 30px;
    text-align: center;
}
.nav-links {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.sidebar a {
    width: 100%;
    padding: 15px 30px;
    text-decoration: none;
    color: #f4f4f4;
    font-size: 15px;
    transition: 0.3s;
}
.sidebar a:hover {
    background-color: #2e2e48;
    color: #00ffe7;
}
.nav-links a.admin-only {
    display: none;
}
.sidebar .admin-visible .admin-only {
    display: block;
}
.logout {
    margin-top: auto;
    padding-top: 30px;
}

/* Main Content */
.main-content {
    margin-left: 250px;
    padding: 40px;
    width: 100%;
}
h1 {
    font-size: 28px;
    color: #00ffe7;
    margin-bottom: 25px;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.stat-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 0 12px rgba(0, 255, 231, 0.1);
}
.stat-card h3 {
    font-size: 16px;
    color: #ccc;
    margin-bottom: 10px;
}
.stat-card p {
    font-size: 22px;
    color: #00ffe7;
    margin: 0;
}
.admin-stat {
    background: rgba(0, 255, 231, 0.1);
    border: 1px solid #00ffe7;
}

/* Info Card */
.card {
    background: rgba(255, 255, 255, 0.05);
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 0 12px rgba(0, 255, 231, 0.15);
}
.card ul {
    list-style: none;
    margin-top: 10px;
}
.card li {
    margin-bottom: 14px;
    font-size: 16px;
    line-height: 1.6;
    padding-left: 25px;
    position: relative;
}
.card li::before {
    content: "✔️";
    position: absolute;
    left: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        position: relative;
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        height: auto;
        padding: 10px;
    }
    .sidebar h2 {
        flex: 1 100%;
        text-align: center;
        margin-bottom: 10px;
    }
    .nav-links a, .logout a {
        flex: 1 1 50%;
        text-align: center;
        padding: 10px;
    }
    .main-content {
        margin-left: 0;
        padding: 20px;
    }
}
</style>
</head>
<body>
<div class="sidebar">
    <div class="nav-links <?= $isAdmin ? 'admin-visible' : '' ?>">
        <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
        <a href="dashboard.php">🏠 Dashboard</a>
        <?php if ($isAdmin): ?>
            <a href="admin_quizzes.php" class="admin-only">📋 Created Quizzes</a>
            <a href="create_quiz.php" class="admin-only">✨ Create Quiz</a>
            <a href="admin_results.php" class="admin-only">📊 Quiz Results</a>
        <?php else: ?>
            <a href="join_quiz.php">🎮 Join Quiz</a>
            <a href="wallet.php">💼 Wallet</a>
            <a href="my_entries.php">🧾 My Quiz History</a>
        <?php endif; ?>
    </div>
    <div class="logout">
        <a href="logout.php">🚪 Logout</a>
    </div>
</div>

<div class="main-content">
    <h1>Dashboard</h1>
    <div class="stats-grid">
        <?php if ($isAdmin): ?>
            <div class="stat-card admin-stat">
                <h3>Quizzes Created</h3>
                <p><?= $quizzesCreated ?></p>
            </div>
            <div class="stat-card admin-stat">
                <h3>Total Commission</h3>
                <p>₹ <?= number_format($totalCommission, 2) ?></p>
            </div>
        <?php else: ?>
            <div class="stat-card">
                <h3>Wallet Balance</h3>
                <p>₹ <?= number_format($walletBalance, 2) ?></p>
            </div>
            <div class="stat-card">
                <h3>Quizzes Played</h3>
                <p><?= $quizzesPlayed ?></p>
            </div>
            <div class="stat-card">
                <h3>Prizes Won</h3>
                <p>₹ <?= number_format($totalWinnings, 2) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <p>Welcome to your Inteli01 dashboard! Here's what you can do:</p>
        <ul>
            <?php if ($isAdmin): ?>
                <li>Create and manage quizzes</li>
                <li>View detailed quiz results and statistics</li>
                <li>Monitor commissions earned from quizzes</li>
            <?php else: ?>
                <li>Join paid or free quizzes to test your skills</li>
                <li>Manage your wallet: deposit, withdraw, or view transactions</li>
                <li>View your quiz history: entries, scores, ranks & prizes</li>
            <?php endif; ?>
        </ul>
    </div>
</div>
</body>
</html>