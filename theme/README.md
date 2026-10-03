# BlueDoc 0.2.0

适用于 Typecho **1.3.0**、PHP **8.2** 的现代文档中心主题。无需第三方插件、Composer、Node.js 构建或修改 Typecho 核心。

## 安装与升级

1. 新安装：将本目录全部内容复制到 Typecho 的 `usr/themes/BlueDoc/`，确认 `index.php` 直接位于主题目录下。
2. 从 V0.1 升级：先备份旧主题目录，再覆盖主题文件（包含新增的 `inc/` 和 `partials/` 文件），无需切换主题或迁移数据库。
3. 在后台 **控制台 → 外观** 启用 BlueDoc，进入 **设置外观** 可配置热门教程和平台入口。
4. 网站标题和简介沿用 Typecho 站点设置；搜索、固定链接、内容权限和评论沿用原生行为。

## 分类与文章搭建

在 **管理 → 分类** 中建立父子分类，并使用后台排序。主题显示真实分类，不会自动创建或改名分类。建议结构：

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
软件下载
常见问题
服务说明
```

文章可只关联最具体的子分类，例如 v2rayN；不必额外关联 Windows 和客户端教程。主题会沿 parent 自动找到完整祖先路径。支持超过两级的层级。

- 首页文档分类显示全部一级分类，遵循后台顺序；分类卡片的文档数包含所有后代分类，并去重。
- 侧栏显示每个分类直接关联的公开文章和子分类，原生折叠控件支持鼠标及键盘。
- 当前文章的所有关联分类及其祖先自动展开，当前文章高亮。
- 多分类文章的面包屑优先选择最深分类路径；同深度按 Typecho 原生分类顺序选择。
- 同一文章关联多个分类时，会在每个直接关联分类中出现。
- 独立页面也使用阅读布局，面包屑为首页 → 当前页面。
- 树和推荐只列出已发布且已到发布时间的文章。密码保护文章使用 Typecho 的访问与标题保护；不读取或展示受保护正文。

## 首页后台配置

| 设置 | 行为 |
| --- | --- |
| 热门教程文章 CID | 最多 12 个正整数，英文逗号分隔，例如 101,100,106；按填写顺序显示并去重 |
| Windows 快速开始分类 MID | 可选，指定 Windows 卡片链接的分类 |
| Android 快速开始分类 MID | 可选，指定 Android 卡片链接的分类 |
| iOS 快速开始分类 MID | 可选，指定 iOS 卡片链接的分类 |

CID 留空或全部不可公开展示时，回退到最新 6 篇文章；若部分 CID 有效，只展示这些有效文章。草稿、私密和定时未发布内容始终被排除。

平台 MID 留空或不存在时，自动匹配分类名称或缩略名 Windows、Android、iOS（不区分大小写）。没有匹配分类时显示“教程尚未发布”，不会生成无效链接。

首页由网站标题、简介、大型搜索框、快速开始、文档分类和热门教程组成，不再展示普通博客文章流。

## 阅读布局与目录

- 桌面为左侧文档树、中间正文、右侧 H2/H3 目录；左右导航使用 sticky，正文滚动时保持可见，长导航独立滚动。
- 小于 1024px 隐藏右侧目录；小于 768px 左侧变为抽屉。
- 抽屉支持遮罩关闭、Escape、焦点循环、关闭后焦点恢复及跨断点恢复。
- 标题下显示所属分类和最后更新时间（modified），不突出作者、评论数量和发布日期。
- 目录只扫描正文 H2/H3，支持中文标题、重复标题及 H3 缩进；保留唯一已有 ID，修复重复 ID，并生成无冲突的锚点。
- 点击目录跳转到正文，滚动时标记当前章节；无章节时显示提示。
- JavaScript 不可用时，正文、搜索、链接及原生分类折叠仍可使用，移动侧栏退回页面内导航。自动目录和抽屉增强需要 JavaScript。

## 文件结构

```text
BlueDoc/
├── index.php                    # 文档中心首页与主题元信息
├── post.php / page.php          # 阅读布局入口
├── archive.php / search.php     # 归档与搜索结果
├── comments.php                 # 原生评论
├── header.php / footer.php      # 公共头尾与 Typecho 扩展钩子
├── functions.php                # 配置字段、文本转义、模块加载
├── 404.php                      # 未找到页面
├── inc/navigation.php           # 数据索引、分类路径、推荐与树渲染
├── partials/
│   ├── document-layout.php      # 三栏布局与文章信息
│   ├── document-tree.php        # 侧栏与抽屉入口
│   ├── breadcrumbs.php          # 面包屑
│   ├── toc.php                  # 目录容器
│   └── document-list.php        # 归档与搜索共用列表
├── assets/
│   ├── css/style.css            # 布局与响应式样式
│   ├── js/main.js               # 目录、抽屉与键盘交互
│   └── images/.gitkeep          # 图片目录
└── README.md
```

## 实现约定

分类索引来自 Typecho 原生 Category\Rows，沿用其按 order 排序的深度优先结果及 permalink。主题以 mid 建立节点表，再以 parent 分组为树。文章元数据和分类关联分批读取，生成分类到 CID 的映射，请求内缓存复用，不逐分类发起文章查询。

文章 URL 交由原生 Contents\From 生成，并注入与 Category\Related 顺序一致的分类缓存，保留原生固定链接规则。主题不使用硬编码分类 URL，也不创建自定义数据表。

所有 PHP 入口保留根目录访问保护，使用 UTF-8 无 BOM。文本及属性转义；正文与评论 HTML、密码保护和扩展钩子交由 Typecho 原生处理。

## 验证

2026-10-03 在隔离的 Typecho 1.3.0 + PHP 8.2.29 + SQLite 环境检查了主题启用、后台配置保存与校验、三级分类、当前分支、多分类面包屑、公开内容筛选、更新日期、归档、搜索、404 和密码保护。

浏览器检查了目录锚点、章节跳转、高亮、折叠与抽屉键盘操作，以及 1024、1023、768、767、375px 断点。具体记录见项目 docs/v0.2-validation.md。

项目目录下可运行独立逻辑回归检查：

```sh
php tests/navigation.php
node --check theme/assets/js/main.js
```

验证使用的核心、运行时、数据库和示例内容均在系统临时目录，不属于主题发行文件。部署到现有站点后，应按站点的插件和固定链接设置复查。

## 官方参考

- [Typecho 1.3.0 默认主题](https://github.com/typecho/typecho/tree/v1.3.0/usr/themes/default)
- [原生分类层级与排序](https://github.com/typecho/typecho/blob/v1.3.0/var/Widget/Base/TreeTrait.php)
- [文章关联分类顺序](https://github.com/typecho/typecho/blob/v1.3.0/var/Widget/Metas/Category/Related.php)

深色模式、全文索引和下载组件留待后续版本。主题元信息中的 link 暂指向 Typecho 官网。
