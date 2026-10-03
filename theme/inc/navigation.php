<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/** 请求内缓存：原生分类顺序、公开文章元数据与关联。不读取正文，不逐分类查询文章。 */
function bluedocDocumentData(): array
{
    static $data = null;
    if ($data !== null) {
        return $data;
    }
    $data = ['categories' => [], 'children' => [], 'documents' => []];
    $categories = \Widget\Metas\Category\Rows::alloc();
    while ($categories->next()) {
        $mid = (int) $categories->mid;
        $data['categories'][$mid] = [
            'mid' => $mid, 'parent' => (int) $categories->parent,
            'name' => (string) $categories->name, 'slug' => (string) $categories->slug,
            'description' => (string) ($categories->description ?? ''),
            'permalink' => (string) $categories->permalink,
            'level' => (int) $categories->levels, 'posts' => [],
        ];
    }
    foreach ($data['categories'] as $mid => $category) {
        $parent = isset($data['categories'][$category['parent']]) ? $category['parent'] : 0;
        $data['children'][$parent][] = $mid;
    }
    $db = \Typecho\Db::get();
    $options = \Widget\Options::alloc();
    $relations = $db->fetchAll($db->select('table.relationships.cid', 'table.relationships.mid')
        ->from('table.relationships')
        ->join('table.contents', 'table.contents.cid = table.relationships.cid')
        ->join('table.metas', 'table.metas.mid = table.relationships.mid')
        ->where('table.metas.type = ?', 'category')
        ->where('table.contents.type = ?', 'post')
        ->where('table.contents.status = ?', 'publish')
        ->where('table.contents.created < ?', $options->time));
    $assigned = [];
    foreach ($relations as $relation) {
        $assigned[(int) $relation['cid']][(int) $relation['mid']] = true;
    }
    $query = $db->select(
        'cid', 'title', 'slug', 'created', 'modified', 'type', 'status',
        'password', 'authorId', 'parent', 'template', 'allowComment', 'allowPing', 'allowFeed', 'commentsNum'
    )->from('table.contents')->where('type = ?', 'post')->where('status = ?', 'publish')
        ->where('created < ?', $options->time)->order('created', \Typecho\Db::SORT_DESC)
        ->order('cid', \Typecho\Db::SORT_DESC);
    $documents = \Widget\Contents\From::allocWithAlias('bluedoc-document-index', ['query' => $query]);
    while ($documents->next()) {
        $cid = (int) $documents->cid;
        $postCategories = [];
        foreach ($data['categories'] as $mid => $category) {
            if (isset($assigned[$cid][$mid])) {
                // Category\Related 使用同样的原生深度优先顺序，保持自定义固定链接兼容。
                $postCategories[] = $categories->getRow($mid) + ['permalink' => $category['permalink']];
                $data['categories'][$mid]['posts'][] = $cid;
            }
        }
        $documents->categories = $postCategories;
        $data['documents'][$cid] = [
            'cid' => $cid, 'title' => (string) $documents->title,
            'permalink' => (string) $documents->permalink,
            'created' => (int) $documents->created,
            'modified' => (int) ($documents->modified ?: $documents->created),
            'categoryIds' => array_column($postCategories, 'mid'),
        ];
    }
    return $data;
}

/** 根分类到目标分类的路径；防止损坏数据导致无限循环。 */
function bluedocCategoryPath(array $data, int $mid): array
{
    $path = [];
    $seen = [];
    while (isset($data['categories'][$mid]) && !isset($seen[$mid])) {
        $seen[$mid] = true;
        $path[] = $data['categories'][$mid];
        $mid = $data['categories'][$mid]['parent'];
    }
    return array_reverse($path);
}

/** 多分类文章选择最深路径；同深度遵循原生分类顺序。 */
function bluedocPrimaryPath(array $data, array $categoryIds): array
{
    $primary = [];
    foreach ($categoryIds as $mid) {
        $path = bluedocCategoryPath($data, (int) $mid);
        if (count($path) > count($primary)) {
            $primary = $path;
        }
    }
    return $primary;
}

/** 展开所有关联分类及其祖先，而不只是面包屑中的主路径。 */
function bluedocActiveBranches(array $data, array $categoryIds): array
{
    $active = [];
    foreach ($categoryIds as $mid) {
        foreach (bluedocCategoryPath($data, (int) $mid) as $category) {
            $active[$category['mid']] = true;
        }
    }
    return $active;
}

/** 分类分支文章数：跨父子分类的重复关联只统计一次。 */
function bluedocCategoryCount(array $data, int $mid): int
{
    $pending = [$mid];
    $seen = [];
    $posts = [];
    while ($pending) {
        $id = array_pop($pending);
        if (isset($seen[$id]) || !isset($data['categories'][$id])) {
            continue;
        }
        $seen[$id] = true;
        foreach ($data['categories'][$id]['posts'] as $cid) {
            $posts[$cid] = true;
        }
        array_push($pending, ...($data['children'][$id] ?? []));
    }
    return count($posts);
}

/** 分类文章池：可包含所有后代，按公开索引的 created/CID 倒序，跨分类去重。 */
function bluedocCategoryDocuments(array $data, int $mid, bool $includeDescendants = true, int $limit = 0): array
{
    $pending = [$mid];
    $seen = [];
    $assigned = [];
    while ($pending) {
        $id = array_pop($pending);
        if (isset($seen[$id]) || !isset($data['categories'][$id])) {
            continue;
        }
        $seen[$id] = true;
        foreach ($data['categories'][$id]['posts'] as $cid) {
            $assigned[$cid] = true;
        }
        if ($includeDescendants) {
            array_push($pending, ...($data['children'][$id] ?? []));
        }
    }
    $documents = [];
    foreach ($data['documents'] as $cid => $document) {
        if (isset($assigned[$cid])) {
            $documents[] = $document;
            if ($limit > 0 && count($documents) >= $limit) {
                break;
            }
        }
    }
    return $documents;
}

/** 仅选取当前位置关联的顶级分支，顺序仍遵循 Typecho 后台。 */
function bluedocNavigationRoots(array $data, array $categoryIds): array
{
    $selected = [];
    foreach ($categoryIds as $mid) {
        $path = bluedocCategoryPath($data, (int) $mid);
        if ($path) {
            $selected[$path[0]['mid']] = true;
        }
    }
    return array_values(array_filter($data['children'][0] ?? [], fn ($mid) => isset($selected[$mid])));
}

function bluedocQuickCategory(array $data, string $platform, ?string $configuredMid): ?array
{
    $mid = (int) ($configuredMid ?? 0);
    if (isset($data['categories'][$mid])) {
        return $data['categories'][$mid];
    }
    if (trim($configuredMid ?? '') !== '') {
        return null; // 明确指定但不存在的 MID 不应悄悄跳到同名分类。
    }
    $aliases = match ($platform) {
        'iOS' => ['iOS', 'iOS / iPadOS', 'iPhone / iPad', 'iPhone', 'iPadOS'],
        'macOS' => ['macOS', 'Mac', 'OS X'],
        default => [$platform],
    };
    $fallback = null;
    foreach ($data['categories'] as $category) {
        if (!bluedocCategoryMatches($category, $aliases)) {
            continue;
        }
        $fallback ??= $category;
        // 同名平台优先使用客户端教程分支，避免误入软件下载分类。
        foreach (bluedocCategoryPath($data, $category['mid']) as $ancestor) {
            if (bluedocCategoryMatches($ancestor, bluedocTopics()['clients']['aliases'])) {
                return $category;
            }
        }
    }
    return $fallback;
}

/** Typecho 已校验原生分类路由；以其 slug 找到同一导航节点。 */
function bluedocCurrentCategory(array $data, ?string $slug): ?array
{
    foreach ($data['categories'] as $category) {
        if ($category['slug'] === $slug) {
            return $category;
        }
    }
    return null;
}

function bluedocPopularDocuments(array $data, ?string $configuredCids): array
{
    $ids = preg_split('/\s*,\s*/', trim($configuredCids ?? ''), -1, PREG_SPLIT_NO_EMPTY);
    $selected = [];
    foreach (array_slice($ids, 0, 12) as $id) {
        if (ctype_digit($id) && isset($data['documents'][(int) $id])) {
            $selected[(int) $id] = $data['documents'][(int) $id];
        }
    }
    return $selected ? array_values($selected) : array_slice(array_values($data['documents']), 0, 6);
}

/** details/summary 无需脚本也能折叠任意深度的分类。 */
function bluedocRenderTree(array $data, int $parent, array $active, int $currentCid, int $currentMid = 0, array $seen = []): void
{
    foreach ($data['children'][$parent] ?? [] as $mid) {
        if (isset($seen[$mid])) {
            continue;
        }
        $category = $data['categories'][$mid];
        $branchSeen = $seen + [$mid => true];
        ?>
        <details class="tree-category<?php echo isset($active[$mid]) ? ' is-active-branch' : ''; ?>" data-category-mid="<?php echo $mid; ?>"<?php echo isset($active[$mid]) ? ' open' : ''; ?>>
            <summary><span><?php echo bluedocEscape($category['name']); ?></span><span class="tree-chevron" aria-hidden="true">›</span></summary>
            <div class="tree-branch">
                <a class="tree-overview<?php echo $mid === $currentMid ? ' is-current' : ''; ?>" href="<?php echo bluedocEscape($category['permalink']); ?>"<?php echo $mid === $currentMid ? ' aria-current="page"' : ''; ?>><?php _e('分类概览'); ?><span class="sr-only">：<?php echo bluedocEscape($category['name']); ?></span></a>
                <?php bluedocRenderTree($data, $mid, $active, $currentCid, $currentMid, $branchSeen); ?>
                <?php if ($category['posts']): ?>
                    <ul class="tree-posts">
                        <?php foreach ($category['posts'] as $cid): $document = $data['documents'][$cid]; ?>
                            <li><a class="tree-document<?php echo $cid === $currentCid ? ' is-current' : ''; ?>" data-document-cid="<?php echo $cid; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>"<?php echo $cid === $currentCid ? ' aria-current="page"' : ''; ?>><?php echo bluedocEscape($document['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if (!$category['posts'] && empty($data['children'][$mid])): ?>
                    <p class="tree-empty"><?php _e('暂无公开文档'); ?></p>
                <?php endif; ?>
            </div>
        </details>
        <?php
    }
}
