<?php
declare(strict_types=1);

$document = new DOMDocument();
if (!$document->load('/coverage/clover.xml')) {
    throw new RuntimeException('Cannot read PHPUnit Clover report');
}

$webHits = [];
$requestFiles = glob('/web-coverage/*.json') ?: [];
if ($requestFiles === []) {
    throw new RuntimeException('No web request coverage was recorded');
}
foreach ($requestFiles as $requestFile) {
    $files = json_decode(file_get_contents($requestFile), true, 512, JSON_THROW_ON_ERROR);
    foreach ($files as $path => $lines) {
        $relative = str_replace('/var/www/html/test/', '', $path);
        foreach ($lines as $line => $hits) {
            if ($hits > 0) {
                $webHits[$relative][(int)$line] = true;
            }
        }
    }
}

$total = 0;
$cliCovered = 0;
$webCovered = 0;
$combinedCovered = 0;
$details = [];
foreach ($document->getElementsByTagName('file') as $file) {
    $relative = str_replace('/app/', '', $file->getAttribute('name'));
    $fileTotal = $fileCli = $fileWeb = $fileCombined = 0;
    foreach ($file->getElementsByTagName('line') as $line) {
        if ($line->getAttribute('type') !== 'stmt') {
            continue;
        }
        $fileTotal++;
        $inCli = (int)$line->getAttribute('count') > 0;
        $inWeb = isset($webHits[$relative][(int)$line->getAttribute('num')]);
        $fileCli += (int)$inCli;
        $fileWeb += (int)$inWeb;
        $fileCombined += (int)($inCli || $inWeb);
    }
    $total += $fileTotal;
    $cliCovered += $fileCli;
    $webCovered += $fileWeb;
    $combinedCovered += $fileCombined;
    $details[$relative] = [
        'executable' => $fileTotal,
        'cli_covered' => $fileCli,
        'web_covered' => $fileWeb,
        'combined_covered' => $fileCombined,
    ];
}

$report = [
    'web_request_samples' => count($requestFiles),
    'executable_lines' => $total,
    'cli_covered' => $cliCovered,
    'web_covered' => $webCovered,
    'combined_covered' => $combinedCovered,
    'combined_percent' => round($combinedCovered * 100 / $total, 2),
    'files' => $details,
];
file_put_contents('/coverage/http-summary.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
$text = sprintf(
    "HTTP request samples: %d\nCLI line coverage: %d/%d\nWeb line coverage: %d/%d\nCombined line coverage: %d/%d (%.2f%%)\n",
    count($requestFiles), $cliCovered, $total, $webCovered, $total,
    $combinedCovered, $total, $report['combined_percent']
);
foreach ($details as $path => $counts) {
    $text .= sprintf("%s: %d/%d combined (%d web)\n", $path,
        $counts['combined_covered'], $counts['executable'], $counts['web_covered']);
}
file_put_contents('/coverage/http-summary.txt', $text);
echo "\n" . $text;
