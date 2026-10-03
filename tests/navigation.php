<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
require dirname(__DIR__) . '/theme/functions.php';

function _e(string $message): void
{
    echo $message;
}

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
check(bluedocCategoryKey('01- 客户端教程'), '客户端教程', 'Numbered category normalization');
check(count(bluedocPlatforms()), 4, 'Four platform entry points');
$data['categories'][30] = ['mid' => 30, 'parent' => 0, 'name' => '软件下载', 'slug' => 'downloads', 'description' => '', 'posts' => []];
$data['categories'][31] = ['mid' => 31, 'parent' => 30, 'name' => 'Windows', 'slug' => 'windows-software', 'posts' => []];
$data['categories'] = [30 => $data['categories'][30], 31 => $data['categories'][31]] + $data['categories'];
check(bluedocQuickCategory($data, 'Windows', null)['mid'], 11, 'Client subtree takes precedence over download platform');
$data['categories'][32] = ['mid' => 32, 'parent' => 10, 'name' => 'iOS / iPadOS', 'slug' => 'apple-mobile', 'posts' => []];
$data['categories'][33] = ['mid' => 33, 'parent' => 10, 'name' => '04-macOS', 'slug' => 'apple-desktop', 'posts' => []];
check(bluedocQuickCategory($data, 'iOS', null)['mid'], 32, 'iOS and iPadOS alias discovery');
check(bluedocQuickCategory($data, 'macOS', null)['mid'], 33, 'Numbered macOS discovery');
check(bluedocCurrentCategory($data, 'v2rayn')['mid'], 20, 'Native category route lookup');
check(bluedocCurrentCategory($data, 'missing'), null, 'Unknown native category route');
foreach ($data['categories'] as &$category) { $category['description'] ??= ''; }
unset($category);
$cards = bluedocHomeCategories($data);
check(array_column(array_filter(array_column($cards, 'category')), 'mid'), [1, 10], 'Home retains backend root order');
check(in_array('订阅配置', array_column($cards, 'name'), true), true, 'Missing recommended topic remains visible without fake link');
check(count(bluedocHomeCategories(['categories' => [], 'children' => []])), 6, 'Empty site exposes six planned topics');
$deep = ['categories' => [], 'children' => [], 'documents' => []];
for ($id = 1; $id <= 24; $id++) {
    $deep['categories'][$id] = ['mid' => $id, 'parent' => $id - 1, 'name' => 'Level ' . $id, 'slug' => 'level-' . $id, 'permalink' => '/category/level-' . $id, 'posts' => []];
    $deep['children'][$id - 1] = [$id];
}
check(count(bluedocCategoryPath($deep, 24)), 24, 'Twenty-four levels have no configured depth limit');
check(count(bluedocActiveBranches($deep, [24])), 24, 'Every deep ancestor automatically expands');
$deep['children'][24] = [1];
$deep['categories'][24]['posts'] = [101];
$deep['documents'][101] = ['cid' => 101, 'title' => '<unsafe>', 'permalink' => '/?x="y"&z=1'];
ob_start();
bluedocRenderTree($deep, 0, bluedocActiveBranches($deep, [24]), 101, 24);
$html = ob_get_clean();
check(substr_count($html, 'class="tree-category'), 24, 'Actual renderer supports deep nodes and stops cycles');
check(substr_count($html, ' open>'), 24, 'Actual renderer expands all ancestors');
check(substr_count($html, 'aria-current="page"'), 2, 'Article and category current markers');
check(str_contains($html, '&lt;unsafe&gt;') && str_contains($html, 'x=&quot;y&quot;&amp;z=1'), true, 'Tree labels and attributes escaped');
check(array_column(bluedocCategoryDocuments($data, 11), 'cid'), [101, 100], 'Latest category documents follow global published order and deduplicate');
check(array_column(bluedocCategoryDocuments($data, 20), 'cid'), [100], 'Category pool excludes sibling articles');
check(array_column(bluedocCategoryDocuments($data, 10, false), 'cid'), [], 'Direct-only category pool excludes descendant posts');
check(array_column(bluedocCategoryDocuments($data, 10, true, 1), 'cid'), [101], 'Category latest limit');
check(bluedocCategoryDocuments($data, 999), [], 'Unknown category has no articles');
check(bluedocQuickCategory($data, 'Windows', '999'), null, 'Explicit stale MID never falls back to another category');
$data['categories'][11]['name'] = 'Desktop Help <test>';
$data['categories'][11]['description'] = 'CMS description & instructions';
$device = bluedocDeviceCard($data, 'Windows', '11');
check($device['name'], 'Desktop Help <test>', 'Device name read by MID rather than fixed platform copy');
check($device['description'], 'CMS description & instructions', 'Device description read from Typecho category');
check(array_column($device['documents'], 'cid'), [101, 100], 'Device card uses category descendant article pool');
check(bluedocDeviceCard($data, 'Windows', '999')['documents'], [], 'Deleted device category has no unrelated recommendations');
check(bluedocNavigationRoots($data, [20]), [10], 'Article navigation scoped to its root category');
check(bluedocNavigationRoots($data, [20, 1]), [1, 10], 'Multiple assigned roots retained in backend order');
check(bluedocNavigationRoots($data, []), [], 'Uncategorised article has no arbitrary navigation root');
$data['children'][20] = [11];
check(array_column(bluedocCategoryDocuments($data, 11), 'cid'), [101, 100], 'Category article pool safely terminates cyclic branches');
$data['categories'][10]['parent'] = 20;
check(count(bluedocCategoryPath($data, 20)), 3, 'Corrupt category cycle terminates');
