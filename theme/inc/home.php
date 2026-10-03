<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/** 平台入口定义与文案集中维护；分类和链接仍来自 Typecho。 */
function bluedocPlatforms(): array
{
    return [
        'Windows' => ['name' => 'Windows', 'description' => '安装客户端，完成首次配置。', 'icon' => 'windows'],
        'Android' => ['name' => 'Android', 'description' => '下载应用，导入并更新订阅。', 'icon' => 'android'],
        'Ios' => ['name' => 'iOS', 'description' => '查看 iPhone 与 iPad 使用指南。', 'icon' => 'phone'],
        'Macos' => ['name' => 'macOS', 'description' => '选择适合 Mac 的客户端。', 'icon' => 'desktop'],
    ];
}

function bluedocTopics(): array
{
    return [
        'getting-started' => ['name' => '新手入门', 'aliases' => ['新手入门', '新手指南', 'getting-started', 'start'], 'description' => '从第一次使用开始，了解基本操作。', 'icon' => 'start'],
        'clients' => ['name' => '客户端教程', 'aliases' => ['客户端教程', '使用教程', 'clients', 'client-tutorials'], 'description' => '按设备选择客户端，查看安装与使用指南。', 'icon' => 'desktop'],
        'subscriptions' => ['name' => '订阅配置', 'aliases' => ['订阅配置', '订阅教程', 'subscriptions', 'subscription'], 'description' => '获取、导入和更新订阅，处理订阅异常。', 'icon' => 'link'],
        'downloads' => ['name' => '软件下载', 'aliases' => ['软件下载', 'downloads', 'download'], 'description' => '查找软件安装包与版本说明。', 'icon' => 'download'],
        'faq' => ['name' => '常见问题', 'aliases' => ['常见问题', 'faq'], 'description' => '解决连接、配置和使用中的常见问题。', 'icon' => 'help'],
        'services' => ['name' => '服务说明', 'aliases' => ['服务说明', 'services', 'service'], 'description' => '了解服务规则、使用须知与相关说明。', 'icon' => 'book'],
    ];
}

/** 接受后台常见的 01-分类名称；不改变真实分类名称。 */
function bluedocCategoryKey(string $value): string
{
    $value = preg_replace('/^\s*\d+\s*[-_.、]\s*/u', '', $value);
    return strtolower(preg_replace('/[\s\/&_-]+/u', '', trim($value)));
}

function bluedocCategoryMatches(array $category, array $aliases): bool
{
    $keys = [bluedocCategoryKey($category['name']), bluedocCategoryKey($category['slug'])];
    foreach ($aliases as $alias) {
        if (in_array(bluedocCategoryKey($alias), $keys, true)) {
            return true;
        }
    }
    return false;
}

function bluedocTopicForCategory(array $category): ?array
{
    foreach (bluedocTopics() as $topic) {
        if (bluedocCategoryMatches($category, $topic['aliases'])) {
            return $topic;
        }
    }
    return null;
}

/** 已建一级分类按后台顺序展示；缺少的建议分类以无链接占位卡提示。 */
function bluedocHomeCategories(array $data): array
{
    $cards = [];
    $missing = bluedocTopics();
    foreach ($data['children'][0] ?? [] as $mid) {
        $category = $data['categories'][$mid];
        $topic = bluedocTopicForCategory($category);
        $cards[] = ['category' => $category, 'name' => $category['name'],
            'description' => $category['description'] ?: ($topic['description'] ?? '浏览相关文档与使用指南。'),
            'icon' => $topic['icon'] ?? 'book'];
        foreach ($missing as $key => $candidate) {
            if (bluedocCategoryMatches($category, $candidate['aliases'])) {
                unset($missing[$key]);
            }
        }
    }
    foreach ($missing as $topic) {
        $cards[] = ['category' => null] + $topic;
    }
    return $cards;
}

/** 代码内 SVG 图标，无外部资源或字体依赖。 */
function bluedocIcon(string $name): void
{
    $paths = [
        'windows' => '<path d="M3 5l8-1v7H3zm10-1l8-1v8h-8zM3 13h8v7l-8-1zm10 0h8v8l-8-1z"/>',
        'android' => '<path d="M6 9a6 6 0 0112 0v8H6zM8 4L6 1m10 3l2-3M3 10v6m18-6v6M9 17v4m6-4v4"/><path d="M9 7h.01M15 7h.01"/>',
        'phone' => '<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M10 5h4m-3 14h2"/>',
        'desktop' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8m-4-4v4"/>',
        'start' => '<path d="M5 19c-2 0-2 2-2 2s2 0 2-2zm3-3l-3-3 5-5c3-3 8-5 11-5 0 3-2 8-5 11l-5 5-3-3zM7 10H3l-1 5h5m7 2v5l5-1v-5"/><circle cx="15" cy="9" r="2"/>',
        'link' => '<path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-2 2m3 6a5 5 0 00-7 0l-3 3a5 5 0 007 7l2-2"/>',
        'download' => '<path d="M12 3v12m-5-5l5 5 5-5M4 15v5h16v-5"/>',
        'help' => '<circle cx="12" cy="12" r="10"/><path d="M9 8a3 3 0 016 0c0 2-3 2-3 5m0 4h.01"/>',
        'book' => '<path d="M12 5C8 2 4 3 2 4v16c3-1 6-1 10 1 4-2 7-2 10-1V4c-2-1-6-2-10 1zm0 0v16"/>',
        'search' => '<circle cx="10" cy="10" r="7"/><path d="M15 15l6 6"/>',
    ];
    echo '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['book']) . '</svg>';
}
