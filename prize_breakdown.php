<?php
if (!isset($_POST['fee'], $_POST['spots'], $_POST['winners'], $_POST['commission'])) {
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$fee = floatval($_POST['fee']);
$spots = intval($_POST['spots']);
$winners = intval($_POST['winners']);
$commission = floatval($_POST['commission']);

if ($winners <= 0 || $fee <= 0 || $spots <= 0) {
    echo json_encode(['error' => 'Invalid inputs']);
    exit;
}

// Calculate total prize pool after commission
$totalPrize = floor(($fee * $spots) * ((100 - $commission) / 100));

// Weighted distribution: Rank 1 gets 1.0, Rank 2 gets 0.5, Rank 3 gets 0.33, etc.
$weights = [];
$weightSum = 0;

for ($i = 1; $i <= $winners; $i++) {
    $weight = 1 / $i;
    $weights[$i] = $weight;
    $weightSum += $weight;
}

// Calculate raw prize amounts using floor and track total allocated
$prizes = [];
$allocated = 0;

for ($i = 1; $i <= $winners; $i++) {
    $share = ($weights[$i] / $weightSum) * $totalPrize;
    $prize = floor($share);
    $prizes[$i] = $prize;
    $allocated += $prize;
}

// Distribute remaining amount (due to flooring) starting from top rank
$remaining = $totalPrize - $allocated;
$i = 1;
while ($remaining > 0) {
    $prizes[$i]++;
    $remaining--;
    $i++;
    if ($i > $winners) $i = 1;
}

echo json_encode($prizes);
