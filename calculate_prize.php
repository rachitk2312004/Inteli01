<?php
if (!isset($_POST['fee'], $_POST['spots'], $_POST['commission'])) {
    echo "0";
    exit;
}

$fee = floatval($_POST['fee']);
$spots = intval($_POST['spots']);
$commission = floatval($_POST['commission']);

$gross = $fee * $spots;
$net = $gross * ((100 - $commission) / 100);

echo number_format($net, 2, '.', '');
