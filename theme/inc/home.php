<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/** 只展示真实一级分类，名称、简介和顺序均来自后台。 */
function bluedocHomeCategories(array $data): array
{
    $cards = [];
    foreach ($data['children'][0] ?? [] as $mid) {
        if (!isset($data['categories'][$mid])) {
            continue;
        }
        $category = $data['categories'][$mid];
        $cards[] = ['category' => $category, 'name' => $category['name'],
            'description' => $category['description'] ?: _t('浏览本分类下的文档与指南。')];
    }
    return $cards;
}

/** 默认取前四个一级分类；可选父分类时取其前四个直接子分类。 */
function bluedocQuickCards(array $data, ?string $configuredParentMid): array
{
    $parent = (int) ($configuredParentMid ?? 0);
    if (!isset($data['categories'][$parent])) {
        $parent = 0; // 父分类删除后恢复默认一级分类入口。
    }
    $cards = [];
    foreach (array_slice($data['children'][$parent] ?? [], 0, 4) as $mid) {
        if (!isset($data['categories'][$mid])) {
            continue;
        }
        $category = $data['categories'][$mid];
        $cards[] = ['category' => $category, 'name' => $category['name'],
            'description' => $category['description'],
            'documents' => bluedocCategoryDocuments($data, $mid, true, 3)];
    }
    return $cards;
}

/** 代码内 SVG 图标，无外部资源或字体依赖。 */
function bluedocIcon(string $name): void
{
    $paths = [
        'book' => '<path d="M12 5C8 2 4 3 2 4v16c3-1 6-1 10 1 4-2 7-2 10-1V4c-2-1-6-2-10 1zm0 0v16"/>',
        'folder' => '<path d="M3 7V4h6l3 3h9v13H3z"/>',
        'search' => '<circle cx="10" cy="10" r="7"/><path d="M15 15l6 6"/>',
    ];
    echo '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($paths[$name] ?? $paths['book']) . '</svg>';
}
