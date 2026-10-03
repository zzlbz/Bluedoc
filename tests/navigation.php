<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__));
require dirname(__DIR__) . '/theme/functions.php';
function _e(string $message): void { echo $message; }
function _t(string $message): string { return $message; }
function check($actual, $expected, string $message): void
{
    if ($actual !== $expected) {
        throw new RuntimeException($message . ': ' . json_encode($actual, JSON_UNESCAPED_UNICODE));
    }
    echo "PASS {$message}\n";
}
$data = [
    'categories' => [
        1 => ['mid' => 1, 'parent' => 0, 'name' => '开始使用', 'slug' => 'start', 'description' => '', 'posts' => [100]],
        10 => ['mid' => 10, 'parent' => 0, 'name' => '产品指南', 'slug' => 'product', 'description' => '来自后台的简介', 'posts' => []],
        11 => ['mid' => 11, 'parent' => 10, 'name' => '账户管理', 'slug' => 'account', 'description' => '', 'posts' => [100, 101]],
        20 => ['mid' => 20, 'parent' => 11, 'name' => '权限设置', 'slug' => 'permissions', 'description' => '', 'posts' => [100]],
    ],
    'children' => [0 => [1, 10], 10 => [11], 11 => [20]],
    'documents' => [101 => ['cid' => 101], 100 => ['cid' => 100]],
];
check(array_column(bluedocCategoryPath($data, 20), 'mid'), [10, 11, 20], 'Three-level category path');
check(array_column(bluedocPrimaryPath($data, [1, 20]), 'mid'), [10, 11, 20], 'Deepest assigned category wins');
check(array_keys(bluedocActiveBranches($data, [1, 20])), [1, 10, 11, 20], 'All assigned branches expanded');
check(bluedocCategoryCount($data, 10), 2, 'Descendant articles counted without duplicates');
check(array_column(bluedocPopularDocuments($data, '100,999,101,100'), 'cid'), [100, 101], 'CID order, missing IDs and deduplication');
check(array_column(bluedocPopularDocuments($data, ''), 'cid'), [101, 100], 'Unconfigured popular articles use latest order');
check(array_column(bluedocPopularDocuments($data, '999'), 'cid'), [101, 100], 'Invalid selections fall back to latest');
check(bluedocCategoryPath($data, 999), [], 'Missing category path');
check(bluedocCurrentCategory($data, 'permissions')['mid'], 20, 'Native category route lookup');
check(bluedocCurrentCategory($data, 'missing'), null, 'Unknown native category route');
$cards = bluedocHomeCategories($data);
check(array_column(array_column($cards, 'category'), 'mid'), [1, 10], 'Home contains only actual roots in backend order');
check(array_column($cards, 'name'), ['开始使用', '产品指南'], 'Arbitrary category names without topic matching');
check($cards[1]['description'], '来自后台的简介', 'Home description comes from category');
check(bluedocHomeCategories(['categories' => [], 'children' => []]), [], 'Empty site never fabricates categories');
check(array_column(array_column(bluedocQuickCards($data, null), 'category'), 'mid'), [1, 10], 'Default quick navigation uses roots');
check(bluedocQuickCards($data, '0'), bluedocQuickCards($data, null), 'Explicit automatic mode equals unset configuration');
check(array_column(array_column(bluedocQuickCards($data, '10'), 'category'), 'mid'), [11], 'Configured parent uses direct children only');
check(array_column(array_column(bluedocQuickCards($data, '11'), 'category'), 'mid'), [20], 'Nested parent also supports scoped navigation');
check(array_column(bluedocQuickCards($data, '10')[0]['documents'], 'cid'), [101, 100], 'Quick navigation includes descendant articles in latest order');
check(bluedocQuickCards($data, '999'), bluedocQuickCards($data, null), 'Deleted parent recovers automatic roots');
check(bluedocQuickCards($data, '20'), [], 'Valid leaf parent produces no fabricated entries');
check(bluedocQuickCards(['categories' => [], 'children' => [], 'documents' => []], null), [], 'Empty site quick navigation is empty');
$data['categories'][11]['name'] = '账户教程 <测试>';
$data['categories'][11]['description'] = 'CMS description & instructions';
check(bluedocQuickCards($data, '10')[0]['name'], '账户教程 <测试>', 'Renamed category is read by MID');
check(bluedocQuickCards($data, '10')[0]['description'], 'CMS description & instructions', 'Quick description comes from category');
for ($id = 30; $id <= 34; $id++) {
    $data['categories'][$id] = ['mid' => $id, 'parent' => 0, 'name' => '分类 ' . $id, 'description' => '', 'posts' => []];
    $data['children'][0][] = $id;
}
check(array_column(array_column(bluedocQuickCards($data, null), 'category'), 'mid'), [1, 10, 30, 31], 'Quick navigation caps four in backend order');
check(count(bluedocHomeCategories($data)), 7, 'All-category section has no four-entry cap');
check(bluedocQuickCards($data, null)[2]['documents'], [], 'Actual empty category has no unrelated articles');
$data['children'][0] = [34, 10, 1, 31, 30, 32, 33];
check(array_column(array_column(bluedocQuickCards($data, null), 'category'), 'mid'), [34, 10, 1, 31], 'Backend reordering changes quick navigation');
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
check(substr_count($html, 'class="tree-category'), 24, 'Renderer supports deep nodes and stops cycles');
check(substr_count($html, ' open>'), 24, 'Renderer expands all ancestors');
check(substr_count($html, 'aria-current="page"'), 2, 'Article and category current markers');
check(str_contains($html, '&lt;unsafe&gt;') && str_contains($html, 'x=&quot;y&quot;&amp;z=1'), true, 'Tree labels and attributes escaped');
check(array_column(bluedocCategoryDocuments($data, 11), 'cid'), [101, 100], 'Latest documents follow published order and deduplicate');
check(array_column(bluedocCategoryDocuments($data, 20), 'cid'), [100], 'Category pool excludes sibling articles');
check(array_column(bluedocCategoryDocuments($data, 10, false), 'cid'), [], 'Direct-only pool excludes descendant posts');
check(array_column(bluedocCategoryDocuments($data, 10, true, 1), 'cid'), [101], 'Category latest limit');
check(bluedocCategoryDocuments($data, 999), [], 'Unknown category has no articles');
check(bluedocNavigationRoots($data, [20]), [10], 'Article navigation scoped to its root category');
check(bluedocNavigationRoots($data, [20, 1]), [10, 1], 'Assigned roots retained in current backend order');
check(bluedocNavigationRoots($data, []), [], 'Uncategorised article has no arbitrary root');
$data['children'][20] = [11];
check(array_column(bluedocCategoryDocuments($data, 11), 'cid'), [101, 100], 'Article pool terminates cyclic branches');
$data['categories'][10]['parent'] = 20;
check(count(bluedocCategoryPath($data, 20)), 3, 'Corrupt category cycle terminates');
