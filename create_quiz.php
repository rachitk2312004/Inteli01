<?php
require_once 'db_connect.php';
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Quiz</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
* {
    margin: 0; padding: 0; box-sizing: border-box;
}
body {
    font-family: 'Orbitron', sans-serif;
    background: #0e0e1a;
    color: #f4f4f4;
}
.nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
}
.logo {
    font-size: 32px;
    color: #00ffe7;
    font-weight: bold;
    letter-spacing: 2px;
}
.logo span {
    color: #ff00cc;
}
nav a {
    margin-left: 25px;
    text-decoration: none;
    color: #f4f4f4;
    font-size: 16px;
}
nav a:hover {
    color: #00ffe7;
}
.btn-glow {
    background: linear-gradient(90deg, #ff00cc, #3333ff);
    padding: 10px 20px;
    border-radius: 50px;
    color: white;
    font-weight: bold;
    box-shadow: 0 0 10px #ff00cc, 0 0 40px #3333ff;
    border: none;
    cursor: pointer;
    transition: 0.3s ease;
}
.btn-glow:hover {
    transform: scale(1.05);
}
.container {
    max-width: 600px;
    margin: 40px auto;
    background: rgba(255,255,255,0.05);
    padding: 30px;
    border-radius: 20px;
    border: 1px solid #00ffe7;
}
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #00ffe7;
    text-shadow: 0 0 10px #00ffe7;
}
input[type="text"], input[type="number"], input[type="datetime-local"] {
    width: 100%;
    padding: 10px;
    margin: 12px 0;
    background: #1e1e2f;
    border: 1px solid #333;
    border-radius: 10px;
    color: #fff;
    font-size: 16px;
}
label {
    display: block;
    margin-top: 15px;
    font-size: 14px;
    color: #ccc;
}
.pill-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 10px 0;
}
.winner-pill {
    padding: 8px 16px;
    border-radius: 20px;
    background-color: #333;
    color: #ccc;
    border: 1px solid #666;
    cursor: pointer;
    font-weight: bold;
}
.winner-pill.active {
    background-color: #00ffe7;
    color: black;
    border-color: #00ffe7;
}
.prize-box {
    margin-top: 20px;
    padding: 15px;
    background-color: #f8f8f8;
    border: 1px solid #ddd;
    border-radius: 8px;
    color: #000;
    min-height: 80px;
}
table {
    width: 100%;
    border-collapse: collapse;
}
table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}
footer {
    background: #0a0a16;
    text-align: center;
    padding: 25px;
    color: #888;
    font-size: 14px;
}
#winner-pill-container {
    display: none;
}
</style>
</head>
<body>

<div class="nav">
    <div class="logo">Admin<span>Panel</span></div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>

<div class="container">
    <h2>Create New Quiz</h2>
    <form method="POST" action="create_quiz_backend.php">
        <label>Quiz Title</label>
        <input type="text" name="title" placeholder="Enter Quiz Title" required>

        <label>Entry Fee (₹)</label>
        <input type="number" id="entry_fee" name="entry_fee" step="0.01" required>

        <label>Total Spots</label>
        <input type="number" id="max_spots" name="max_spots" required>

        <label>Commission (%)</label>
        <input type="number" id="commission" name="commission_percent" value="16.00" step="0.01" required>

        <label>Start Time</label>
        <input type="datetime-local" name="start_time" required>

        <label>End Time</label>
        <input type="datetime-local" name="end_time" required>

        <div id="winner-pill-container">
            <label>Select Number of Winners</label>
            <div id="winner-options" class="pill-group"></div>
            <input type="hidden" name="winners" id="winners" value="1">
        </div>

        <div class="prize-box">
            Max Prize Pool: <span id="prize_pool">₹0</span>
        </div>

        <div id="prize_breakdown" class="prize-box">
            <span style="color: #888;">Prize distribution will appear here...</span>
        </div>

        <div class="form-footer" style="text-align:center; margin-top:20px;">
            <button type="submit" class="btn-glow">Create Quiz</button>
        </div>
    </form>
</div>

<footer>
    &copy; <?= date('Y'); ?> Admin Quiz Panel. All rights reserved.
</footer>

<script>
function generateWinnerPills(spots) {
    const percentages = [0.5, 0.3, 0.2, 0.1, 0.05, 0.025, 0.01];
    const pills = [];

    for (let p of percentages) {
        let count = Math.floor(spots * p);
        if (count > 0 && !pills.includes(count)) {
            pills.push(count);
        }
        if (pills.length >= 7) break;
    }

    if (!pills.includes(1)) pills.push(1);
    pills.sort((a, b) => a - b);
    return pills;
}

function renderWinnerPills() {
    const spots = parseInt($('#max_spots').val() || 0);
    if (!spots || spots <= 0) {
        $('#winner-options').html('');
        $('#winner-pill-container').hide();
        return;
    }

    $('#winner-pill-container').show();
    const pills = generateWinnerPills(spots);
    let html = '';
    pills.forEach((count, index) => {
        html += `<button type="button" class="winner-pill${index === pills.length - 1 ? ' active' : ''}" data-count="${count}">${count}</button>`;
    });

    $('#winner-options').html(html);
    $('#winners').val(pills[pills.length - 1]);

    $('.winner-pill').on('click', function () {
        $('.winner-pill').removeClass('active');
        $(this).addClass('active');
        $('#winners').val($(this).data('count'));
        updatePrizePoolAndBreakdown();
    });

    updatePrizePoolAndBreakdown();
}

function updatePrizePoolAndBreakdown() {
    const fee = parseFloat($('#entry_fee').val());
    const spots = parseInt($('#max_spots').val());
    const commission = parseFloat($('#commission').val());
    const winners = parseInt($('#winners').val());

    $('#prize_breakdown').html('<span style="color: #888;">Prize distribution will appear here...</span>');

    if (fee && spots && !isNaN(commission)) {
        $.post('calculate_prize.php', { fee, spots, commission }, function(response) {
            $('#prize_pool').text('₹' + response);
        });
    }

    if (fee && spots && winners && !isNaN(commission)) {
        $.post('prize_breakdown.php', { fee, spots, winners, commission }, function(data) {
            try {
                const prizes = JSON.parse(data);
                if (prizes.error) {
                    $('#prize_breakdown').html('<span style="color:red;">' + prizes.error + '</span>');
                    return;
                }

                let html = '<h4>Prize Distribution</h4><table><tr><th>Rank(s)</th><th>Prize</th></tr>';
                let ranks = Object.entries(prizes);
                let i = 0;

                while (i < ranks.length) {
                    let [startRank, prize] = ranks[i];
                    startRank = parseInt(startRank);
                    let endRank = startRank;

                    while (i + 1 < ranks.length &&
                           parseFloat(ranks[i + 1][1]) === parseFloat(prize) &&
                           parseInt(ranks[i + 1][0]) === endRank + 1) {
                        endRank++;
                        i++;
                    }

                    const rankLabel = startRank === endRank ? `#${startRank}` : `#${startRank}–${endRank}`;
                    html += `<tr><td>${rankLabel}</td><td>₹${prize}</td></tr>`;
                    i++;
                }

                html += '</table>';
                $('#prize_breakdown').html(html);

            } catch (e) {
                $('#prize_breakdown').html('<span style="color:red;">Error loading prize breakdown</span>');
            }
        });
    }
}

$('#entry_fee, #max_spots, #commission').on('input', renderWinnerPills);
</script>

</body>
</html>
