# BlueDoc 0.3.0

独立的 Typecho **1.3.0** 文档中心主题，支持 PHP **8.2**。无需插件、Composer、Node.js 构建或修改核心文件。品牌和首页文案保持通用，适用于产品帮助中心、软件教程和知识库。

## 安装与升级

1. 备份当前主题目录。将本目录的全部内容复制到 `usr/themes/BlueDoc/`，使 `index.php` 位于主题目录的第一层；不要把整个项目根目录当作主题安装。
2. 从 V0.1 / V0.2 升级时同时覆盖 `inc/`、`partials/`、`assets/`，并包含新增 `category.php` 和 `inc/home.php`。不迁移数据库。
3. 在 **控制台 → 外观** 确认当前主题是 **BlueDoc 0.3.0**；在 **设置外观** 保存首页与平台入口配置。
4. 清理站点/CDN 页面缓存并刷新浏览器。CSS/JS 自带 `?v=0.3.0` 版本参数。查看首页源代码，应有 `<meta name="bluedoc-version" content="0.3.0">`。
5. Typecho 的 **设置 → 阅读** 如选择了独立页面作为首页，需改为显示最新文章，才能进入本主题的文档中心首页模板。这里的原生选项只控制路由，主题仍显示帮助中心，不渲染博客文章流。

后台显示已启用但页面仍有作者、发布时间和评论时，先核对正在加载的主题目录和缓存，不要仅检查 GitHub 是否已更新。主题升级不会自动重组现有文章分类。

## 分类与内容组织

在 **管理 → 分类** 建立原生父子分类并按后台顺序排列：

```text
新手入门
客户端教程
├── Windows
│   ├── v2rayN
│   ├── FlClash
│   └── Clash Verge Rev
├── Android
├── iOS
└── macOS
订阅配置
├── 获取订阅
├── 更新订阅
└── 订阅异常
软件下载
常见问题
服务说明
```

以上是推荐的信息架构。主题不创建、改名或移动分类，不写入文章和分类数据。已有“使用教程”可先在后台整理为平台子分类，再把文章关联到最具体的分类。

- 支持任意层数，没有固定的两级/三级限制；实际规模仍受 PHP 和数据库资源限制。深层树在第五层后停止累加缩进，保证文字可读。
- 分类顺序来自 Typecho 原生 `order`；每个分支直接关联的文章按创建时间倒序索引，页面不显示该时间。
- 当前文章在直接关联分类中高亮，所有关联分类及祖先展开。面包屑选择最深路径，同深度按原生分类顺序选择。
- 首页展示真实一级分类，并展示它们的直接子分类入口。尚未建立的六个推荐分类以“文档准备中”卡片展示，无伪造链接。
- 分类概览共用左侧文档树，展示直接子分类卡片和本分类直接关联的全部公开文档，不混合后代文章流，也不依赖 Typecho 博客分页。分支总数包含后代并按 CID 去重。
- 独立页面使用阅读布局，面包屑为首页 → 当前页面；搜索和其他原生归档保留简洁的标题、分类及原生分页。
- 只索引已发布且已到发布时间的文章；草稿、私密、未来内容不进入导航和推荐。密码文档由原生标题与密码表单保护，索引不读取正文。

## 首页与后台设置

首页为 Hero 标题/描述/搜索、Windows/Android/iOS/macOS 设备入口、文档分类卡片、热门教程。没有文章正文、摘要、发布日期或首页分页。

| 设置 | 行为 |
| --- | --- |
| 首页标题、首页描述 | 可选纯文本；留空沿用站点标题/简介，不硬编码站点品牌 |
| 热门教程文章 CID | 最多 12 个正整数，英文逗号分隔，按填写顺序展示并去重 |
| Windows / Android / iOS / macOS 快速开始分类 MID | 可选，指定入口分类；新增 macOS 设置沿用旧配置规则 |

热门 CID 留空或全部不可公开展示时，以最新六篇公开文章作为推荐候选，仍使用简洁推荐卡片。部分有效时只显示有效内容。

平台入口先使用有效的配置 MID，否则匹配名称或缩略名；支持 `01-Windows` 等数字前缀、`iOS / iPadOS`、`iPhone / iPad`、`Mac`、`OS X`。有同名分类时优先选择“客户端教程/使用教程”分支；未创建时显示“教程准备中”，不会链接到搜索或编造路径。分类实际名称保持原样。

## 阅读与交互

- 桌面：左侧分类树、中间正文、右侧 H2/H3 目录。两侧 sticky 且可独立滚动。
- 小于 1024px 隐藏右侧目录，小于 768px 左侧为抽屉；分类概览同样支持移动抽屉。
- 抽屉支持 Escape、遮罩、焦点循环、关闭后焦点恢复和跨断点恢复。
- 文章顶部只有标题、所属分类和原生 `modified` 最后更新时间；不显示作者、发布时间、评论数、评论及表单。
- 保留 `comments.php` 兼容文件，但它不输出内容，也不修改已有评论或后台评论权限。如需停止接收评论，应在 Typecho 后台关闭文章评论权限。
- 目录支持中文、H3 缩进、重复标题及重复 ID 修复，保留唯一已有锚点；滚动时标记当前章节。
- 无 JavaScript 时正文、分类树折叠、分类入口和搜索仍可用；移动侧栏退回页面内导航。自动目录和抽屉增强需要 JavaScript。

## 文件与架构

| 文件 | 作用 |
| --- | --- |
| index.php | 文档中心首页与主题元信息 |
| category.php | 原生分类路由的文档概览 |
| post.php / page.php | 共用阅读布局入口 |
| archive.php / search.php | 其他归档和搜索 |
| functions.php | 原生主题设置、版本、转义和模块加载 |
| inc/home.php | 设备定义、推荐主题匹配、卡片数据和内置 SVG 图标 |
| inc/navigation.php | 分类/文章索引、路径、计数、推荐及无限层级树渲染 |
| partials/document-layout.php | 三栏正文与更新时间 |
| partials/document-tree.php / breadcrumbs.php / toc.php | 文档树、当前位置和目录容器 |
| partials/document-list.php | 不含摘要和发布时间的检索结果列表 |
| comments.php | 无输出的兼容入口 |
| header.php / footer.php | 公共头尾、版本标识和 Typecho 钩子 |
| assets/css/style.css / assets/js/main.js | 响应式布局、自动目录和抽屉交互 |

完整架构见项目 [docs/architecture.md](../docs/architecture.md)。安装时只复制 `theme/` 内容，上述链接是仓库文档链接。

## 测试

从项目根目录运行：

```sh
php tests/navigation.php
node --check theme/assets/js/main.js
node tests/typecho-smoke.mjs
```

前两项无需 Typecho 站点。HTTP 冒烟需要先启用隔离测试站的 BlueDoc V0.3，默认 URL 为 `http://127.0.0.1:18230/`；可用环境变量 `BLUEDOC_BASE_URL` 指定其他测试站。测试不登录、不修改数据，缺少平台分类或公开文章时明确输出 SKIP。

PHP 全量语法检查、测试内容搭建和交互验收步骤见 [docs/v0.3-validation.md](../docs/v0.3-validation.md)。PHP 运行时、核心、数据库和测试账号不属于主题发行文件。

## 官方实现参考

- [Typecho 1.3.0 默认主题](https://github.com/typecho/typecho/tree/v1.3.0/usr/themes/default)
- [原生分类层级](https://github.com/typecho/typecho/blob/v1.3.0/var/Widget/Base/TreeTrait.php)
- [原生分类路由](https://github.com/typecho/typecho/blob/v1.3.0/var/Widget/Archive.php)

下载组件、深色模式和独立全文检索留待后续版本。
