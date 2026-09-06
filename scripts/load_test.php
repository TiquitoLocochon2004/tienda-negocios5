<?php

$options = getopt('', ['url::', 'requests::', 'concurrency::']);
$baseUrl = rtrim($options['url'] ?? 'http://127.0.0.1:8000', '/');
$totalRequests = max(1, (int) ($options['requests'] ?? 20));
$concurrency = max(1, min($totalRequests, (int) ($options['concurrency'] ?? 5)));

$durations = [];
$statusCodes = [];
$completed = 0;
$multiHandle = curl_multi_init();

while ($completed < $totalRequests) {
    $handles = [];
    $batchSize = min($concurrency, $totalRequests - $completed);

    for ($index = 0; $index < $batchSize; $index++) {
        $handle = curl_init($baseUrl . '/api/productos');
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 10,
        ]);
        $handles[] = [$handle, microtime(true)];
        curl_multi_add_handle($multiHandle, $handle);
    }

    do {
        $result = curl_multi_exec($multiHandle, $running);
        if ($running) {
            curl_multi_select($multiHandle, 1.0);
        }
    } while ($running && $result === CURLM_OK);

    foreach ($handles as [$handle, $startedAt]) {
        $durations[] = (microtime(true) - $startedAt) * 1000;
        $statusCodes[] = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_multi_remove_handle($multiHandle, $handle);
        curl_close($handle);
    }

    $completed += $batchSize;
}

curl_multi_close($multiHandle);
sort($durations);
$successful = count(array_filter($statusCodes, static fn (int $status): bool => $status >= 200 && $status < 300));
$p95Index = max(0, (int) ceil(count($durations) * 0.95) - 1);

printf("Requests: %d\n", $totalRequests);
printf("Concurrency: %d\n", $concurrency);
printf("Successful responses: %d/%d\n", $successful, $totalRequests);
printf("Average latency: %.2f ms\n", array_sum($durations) / count($durations));
printf("P95 latency: %.2f ms\n", $durations[$p95Index]);
printf("HTTP status codes: %s\n", json_encode(array_count_values($statusCodes)));
