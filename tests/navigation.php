<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
require dirname(__DIR__) . '/theme/functions.php';

function check($actual, $expected, string $message): void
{
    if ($actual !== $expected) {
        throw new RuntimeException($message . ': ' . json_encode($actual, JSON_UNESCAPED_UNICODE));
    }
    echo "PASS {$message}\n";
}

$data = [
    'categories' => [
        1 => ['mid' => 1, 'parent' => 0, 'name' => '新手入门', 'slug' => 'start', 'posts' => [100]],
        10 => ['mid' => 10, 'parent' => 0, 'name' => '客户端教程', 'slug' => 'clients', 'posts' => []],
        11 => ['mid' => 11, 'parent' => 10, 'name' => 'Windows', 'slug' => 'windows', 'posts' => [100, 101]],
        20 => ['mid' => 20, 'parent' => 11, 'name' => 'v2rayN', 'slug' => 'v2rayn', 'posts' => [100]],
    ],
    'children' => [0 => [1, 10], 10 => [11], 11 => [20]],
    'documents' => [101 => ['cid' => 101], 100 => ['cid' => 100]],
];
check(array_column(bluedocCategoryPath($data, 20), 'mid'), [10, 11, 20], 'Three-level category path');
check(array_column(bluedocPrimaryPath($data, [1, 20]), 'mid'), [10, 11, 20], 'Deepest assigned category wins');
check(array_keys(bluedocActiveBranches($data, [1, 20])), [1, 10, 11, 20], 'All assigned branches expanded');
check(bluedocCategoryCount($data, 10), 2, 'Descendant articles counted without duplicates');
check(bluedocQuickCategory($data, 'Windows', null)['mid'], 11, 'Platform discovery');
check(bluedocQuickCategory($data, 'Windows', '1')['mid'], 1, 'Configured platform MID takes precedence');
check(bluedocQuickCategory($data, 'Android', '999'), null, 'Missing platform has no fabricated link');
check(array_column(bluedocPopularDocuments($data, '100,999,101,100'), 'cid'), [100, 101], 'CID order, missing IDs and deduplication');
check(array_column(bluedocPopularDocuments($data, ''), 'cid'), [101, 100], 'Unconfigured popular articles use latest order');
check(array_column(bluedocPopularDocuments($data, '999'), 'cid'), [101, 100], 'Invalid selections fall back to latest');
check(bluedocCategoryPath($data, 999), [], 'Missing category path');
$data['categories'][10]['parent'] = 20;
check(count(bluedocCategoryPath($data, 20)), 3, 'Corrupt category cycle terminates');
