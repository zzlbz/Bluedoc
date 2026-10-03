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

function bluedocQuickCategory(array $data, string $platform, ?string $configuredMid): ?array
{
    $mid = (int) ($configuredMid ?? 0);
    if (isset($data['categories'][$mid])) {
        return $data['categories'][$mid];
    }
    foreach ($data['categories'] as $category) {
        if (strcasecmp($category['name'], $platform) === 0 || strcasecmp($category['slug'], $platform) === 0) {
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
function bluedocRenderTree(array $data, int $parent, array $active, int $currentCid, array $seen = []): void
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
                <a class="tree-overview" href="<?php echo bluedocEscape($category['permalink']); ?>"><?php _e('分类概览'); ?><span class="sr-only">：<?php echo bluedocEscape($category['name']); ?></span></a>
                <?php if ($category['posts']): ?>
                    <ul class="tree-posts">
                        <?php foreach ($category['posts'] as $cid): $document = $data['documents'][$cid]; ?>
                            <li><a class="tree-document<?php echo $cid === $currentCid ? ' is-current' : ''; ?>" data-document-cid="<?php echo $cid; ?>" href="<?php echo bluedocEscape($document['permalink']); ?>"<?php echo $cid === $currentCid ? ' aria-current="page"' : ''; ?>><?php echo bluedocEscape($document['title']); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php bluedocRenderTree($data, $mid, $active, $currentCid, $branchSeen); ?>
                <?php if (!$category['posts'] && empty($data['children'][$mid])): ?>
                    <p class="tree-empty"><?php _e('暂无公开文档'); ?></p>
                <?php endif; ?>
            </div>
        </details>
        <?php
    }
}
