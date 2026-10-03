import assert from 'node:assert/strict';

// 只读 HTTP 冒烟检查；对已安装的隔离 Typecho 测试站运行，不登录或修改数据。
const base = process.env.BLUEDOC_BASE_URL || 'http://127.0.0.1:18230/';
const decode = value => value.replaceAll('&amp;', '&').replaceAll('&quot;', '"');
async function read(path) {
    const response = await fetch(new URL(path, base));
    const html = await response.text();
    assert.equal(response.status, 200, `Unexpected status: ${path}`);
    assert(!/Fatal error|Warning:|Deprecated:|Parse error|Uncaught /i.test(html), path);
    assert(html.includes('name="bluedoc-version" content="0.3.1"'), 'Install and enable V0.3.1 first');
    return html;
}
const home = await read(base);
for (const id of ['hero-title', 'hero-search-input', 'quick-title', 'categories-title', 'popular-title']) {
    assert(home.includes(`id="${id}"`), id);
}
for (const name of ['Windows', 'Android', 'iOS', 'macOS']) assert(home.includes(`data-platform="${name}"`), name);
for (const name of ['新手入门', '客户端教程', '订阅配置', '软件下载', '常见问题', '服务说明']) {
    assert(home.includes(name), `Create the recommended category: ${name}`);
}
assert(!home.includes('class="document-list"') && !home.includes('class="page-navigator"') && !home.includes('<time'));
assert(!home.includes('document-excerpt') && !home.includes('id="comments"'));
console.log('PASS Home devices, topics, search and absence of blog stream');
let linkedDevices = 0;
for (const match of home.matchAll(/<article class="quick-card[^>]*data-platform="([^"]+)"[\s\S]*?<\/article>/g)) {
    const block = match[0];
    const categoryLink = block.match(/class="quick-card-heading" href="([^"]+)"/);
    if (!categoryLink) {
        assert(!block.includes('data-device-cid='));
        continue;
    }
    linkedDevices++;
    const category = await read(decode(categoryLink[1]));
    assert(category.includes('class="document-shell category-shell"'));
    assert(category.includes('id="category-title"') && category.includes('id="bluedoc-sidebar"'));
    assert(category.includes('tree-overview is-current') && category.includes('class="breadcrumbs"'));
    assert(!category.includes('data-document-toc') && !category.includes('<time'));
    const deviceCids = [...block.matchAll(/data-device-cid="(\d+)"/g)].map(item => +item[1]);
    const categoryCids = [...category.matchAll(/data-guide-cid="(\d+)"/g)].map(item => +item[1]);
    assert.deepEqual(deviceCids, categoryCids.slice(0, 3), `${match[1]} latest category documents`);
    console.log(`PASS ${match[1]} category overview, active tree and latest device articles`);
}
if (!linkedDevices) console.log('SKIP Category routing: create a platform category or configure its MID');
const articleLink = home.match(/data-device-cid="(\d+)" href="([^"]+)"/) || home.match(/data-popular-cid="(\d+)" href="([^"]+)"/);
if (articleLink) {
    const response = await fetch(new URL(decode(articleLink[2]), base));
    const article = await response.text();
    assert([200, 403].includes(response.status));
    assert(article.includes('data-document-content') && article.includes('data-document-toc'));
    assert(article.includes('最后更新：') && article.includes('class="breadcrumbs"'));
    assert(!article.includes('id="comments"') && !article.includes('name="author"'));
    console.log('PASS Document layout, modified date and hidden comments');
} else console.log('SKIP Document routing: publish an article or configure a popular CID');
