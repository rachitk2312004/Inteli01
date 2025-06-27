<?php
require_once 'db_connect.php';
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Prize Distribution Function ---
function calculatePrizeDistribution($pool, $winners) {
    $structure = [];
    $base = 1.2;
    $total_weight = 0;

    for ($i = 1; $i <= $winners; $i++) {
        $total_weight += 1 / pow($i, $base);
    }

    $remaining = $pool;
    for ($i = 1; $i <= $winners; $i++) {
        $weight = 1 / pow($i, $base);
        $amount = floor(($weight / $total_weight) * $pool);
        $structure[$i] = $amount;
        $remaining -= $amount;
    }

    $i = 1;
    while ($remaining > 0) {
        $structure[$i++] += 1;
        $remaining--;
        if ($i > $winners) $i = 1;
    }

    return $structure;
}

// --- Handle POST Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Input and Validation ---
    $title = mysqli_real_escape_string($con, $_POST['title']);
    $entry_fee = (float) $_POST['entry_fee'];
    $max_spots = (int) $_POST['max_spots'];
    $commission_percent = (float) $_POST['commission_percent'];
    $winners = (int) $_POST['winners'];
    $start_time = mysqli_real_escape_string($con, $_POST['start_time']);
    $end_time = mysqli_real_escape_string($con, $_POST['end_time']);

    if ($entry_fee <= 0 || $max_spots <= 0 || $winners <= 0 || $winners > $max_spots || $commission_percent < 0 || $commission_percent >= 100) {
        $_SESSION['error'] = "❌ Invalid input values. Please check entry fee, spots, winners, and commission.";
        header("Location: create_quiz_form.php"); // Redirect back to form
        exit;
    }

    // --- Prize Pool Calculation ---
    $gross_pool = $entry_fee * $max_spots;
    $net_pool = round($gross_pool * (1 - $commission_percent / 100), 2);

    // --- Insert Quiz ---
    $sql = "INSERT INTO quizzes (title, entry_fee, max_spots, prize_pool, commission_percent, winners, start_time, end_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);

    if (!$stmt) {
        $_SESSION['error'] = "❌ Prepare failed: " . mysqli_error($con);
        header("Location: create_quiz_form.php");
        exit;
    }

    // Correct bind types: s (string), d (double), i (integer)
    mysqli_stmt_bind_param($stmt, 'sdidddss',
        $title,
        $entry_fee,
        $max_spots,
        $net_pool,
        $commission_percent,
        $winners,
        $start_time,
        $end_time
    );

    if (!mysqli_stmt_execute($stmt)) {
        $_SESSION['error'] = "❌ Quiz insert failed: " . mysqli_stmt_error($stmt);
        header("Location: create_quiz_form.php");
        exit;
    }

    $quiz_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt);

    // --- Calculate Prize Distribution ---
    $distribution = calculatePrizeDistribution($net_pool, $winners);

    // --- Insert Prize Distribution ---
    $stmt = mysqli_prepare($con, "INSERT INTO prize_distribution (quiz_id, rank_start, rank_end, prize_amount) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = "❌ Prize prepare failed: " . mysqli_error($con);
        header("Location: create_quiz_form.php");
        exit;
    }

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

    // Redirect to add questions page with quiz ID
    header("Location: add_question.php?quiz_id=$quiz_id");
    exit;
}

// If not a POST request, redirect to form
header("Location: create_quiz_form.php");
exit;