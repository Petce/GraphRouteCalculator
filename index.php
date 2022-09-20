<?php

error_reporting(E_ERROR | E_PARSE);

// https://github.com/DonVictor/PHP-Dijkstra
require_once('Dijkstra.php');

// Read in and validate CSV file
$csvArray = [];
if (isset($argv[1]) && !empty($argv[1])) {
    if (str_ends_with($argv[1], '.csv')) {
        $fp = fopen($argv[1], 'r');

        while ($row = fgetcsv($fp)) {
            $csvArray[] = [$row[0], $row[1], (int)$row[2]];
        }

        fclose($fp);

        // Validate the CSV
        if (!isCsvValid($csvArray)) {
            $csvArray = [];
        }
    }
}

if (empty($csvArray)) {
    echo "Please provide a valid CSV!\n";
    exit();
}

// Duplicate paths for reverse routing and sort
$paths = [];
foreach($csvArray as $row) {
    $paths[] = $row;
    $paths[] = [$row[1], $row[0], $row[2]];
}

usort($paths, function ($a, $b) {
    return $a[0] <=> $b[0];
});

// Initialise Graph
$g = new Graph();
foreach ($paths as $path) {
    $g->addedge($path[0], $path[1], $path[2]);
}

// Present Input Loop
$handle = fopen ("php://stdin","r");
$input = null;
do {
    echo "Input: ";
    $input = trim(fgets($handle));
    $iArr = explode(' ', $input);

    // Validate the input
    if (strtolower($input) === 'quit') {
        continue;
    } else if (!isInputValid($iArr)) {
        echo "Output: Input Error... Syntax: \"[from] [to] [latency]\", type \"QUIT\" to terminate script.\n";
        continue;
    }

    // Prepare the Input Data
    $from = $iArr[0];
    $to = $iArr[1];
    $latency = (int)$iArr[2];

    // Calculate shortest route
    list($distances, $prev) = $g->paths_from($from);
    $path = $g->paths_to($prev, $to);
    $duration = $distances[$to];

    // Process Output
    if (!empty($path) && $duration <= $latency) {
        echo "Output: " . implode(" => ", $path) . " => {$duration}\n";
    } else {
        echo "Output: Path not found\n";
    }
} while (strtolower($input) !== 'quit');

echo "Ending script...\n";

/* Helper Functions */

function isCsvValid($arr)
{
    foreach ($arr as $row) {
        if (count($row) !== 3) {
            return false;
        }

        if (!preg_match('/^[a-z]{1}$/i', $row[0])) {
            return false;
        }

        if (!preg_match('/^[a-z]{1}$/i', $row[1])) {
            return false;
        }

        if (!is_int($row[2]) || $row[2] <= 0) {
            return false;
        }
    }

    return true;
}

function isInputValid($arr)
{
    if (count($arr) !== 3) {
        return false;
    }

    if (!is_numeric($arr[2])) {
        return false;
    }

    return true;
}
