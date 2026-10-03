import assert from 'node:assert/strict';

// 只读 HTTP 冒烟检查；对已安装的隔离 Typecho 测试站运行，不登录或修改数据。
const base = process.env.BLUEDOC_BASE_URL || 'http://127.0.0.1:18230/';
const decode = value => value.replaceAll('&amp;', '&').replaceAll('&quot;', '"');
async function read(path) {
    const response = await fetch(new URL(path, base));
    const html = await response.text();
    assert.equal(response.status, 200, `Unexpected status: ${path}`);
    assert(!/Fatal error|Warning:|Deprecated:|Parse error|Uncaught /i.test(html), path);
    assert(html.includes('name="bluedoc-version" content="0.3.2"'), 'Install and enable V0.3.2 first');
    return html;
}
const home = await read(base);
for (const id of ['hero-title', 'hero-search-input', 'quick-title', 'categories-title', 'popular-title']) {
    assert(home.includes(`id="${id}"`), id);
}
assert(!home.includes('data-platform=') && !home.includes('选择你的设备') && !home.includes('is-unavailable'));
assert(home.includes('快速导航') && home.includes('精选文档'));
assert(!home.includes('class="document-list"') && !home.includes('class="page-navigator"') && !home.includes('<time'));
assert(!home.includes('document-excerpt') && !home.includes('id="comments"'));
console.log('PASS Generic home categories, search and absence of fixed devices/blog stream');
let linkedEntries = 0;
for (const match of home.matchAll(/<article class="quick-card" data-entry-mid="(\d+)"[\s\S]*?<\/article>/g)) {
    const block = match[0];
    const categoryLink = block.match(/class="quick-card-heading" href="([^"]+)"/);
    assert(categoryLink, 'Every entry must point to a real category');
    linkedEntries++;
    const category = await read(decode(categoryLink[1]));
    assert(category.includes('class="document-shell category-shell"'));
    assert(category.includes('id="category-title"') && category.includes('id="bluedoc-sidebar"'));
    assert(category.includes('tree-overview is-current') && category.includes('class="breadcrumbs"'));
    assert(!category.includes('data-document-toc') && !category.includes('<time'));
    const quickCids = [...block.matchAll(/data-quick-cid="(\d+)"/g)].map(item => +item[1]);
    const categoryCids = [...category.matchAll(/data-guide-cid="(\d+)"/g)].map(item => +item[1]);
    assert.deepEqual(quickCids, categoryCids.slice(0, 3), `MID ${match[1]} latest category documents`);
    console.log(`PASS MID ${match[1]} category overview, active tree and latest documents`);
}
assert(linkedEntries <= 4);
if (!linkedEntries) console.log('SKIP Category routing: no actual categories in the selected scope');
const articleLink = home.match(/data-quick-cid="(\d+)" href="([^"]+)"/) || home.match(/data-popular-cid="(\d+)" href="([^"]+)"/);
if (articleLink) {
    const response = await fetch(new URL(decode(articleLink[2]), base));
    const article = await response.text();
    assert([200, 403].includes(response.status));
    assert(article.includes('data-document-content') && article.includes('data-document-toc'));
    assert(article.includes('最后更新：') && article.includes('class="breadcrumbs"'));
    assert(!article.includes('id="comments"') && !article.includes('name="author"'));
    console.log('PASS Document layout, modified date and hidden comments');
} else console.log('SKIP Document routing: publish an article or configure a popular CID');
