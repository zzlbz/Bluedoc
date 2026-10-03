# BlueDoc

面向 Typecho **1.3.0**、PHP **8.2** 的文档主题，当前版本 **0.1.0**，处于第一阶段基础框架。

## 安装与启用

1. 将本目录中的全部文件复制到 Typecho 的 `usr/themes/BlueDoc/` 下。
2. 确认结构为 `usr/themes/BlueDoc/index.php`，不要多嵌套一层 `theme/`。
3. 登录 Typecho 后台，进入 **控制台 → 外观**，找到 **BlueDoc** 并启用。
4. 在站点设置中填写标题与描述，发布文章即可显示在文档首页；公开的独立页面自动加入顶部导航。

无需额外插件、Composer、Node.js 或构建步骤。第一阶段没有主题配置项；沿用 Typecho 的内容、搜索、分页、评论与固定链接设置。

## 当前能力与边界

- 标准主题元信息、直接访问保护、原生模板加载以及 `header()` / `footer()` 扩展钩子。
- 文档首页、文章详情、独立页面、分类/标签/日期/作者归档、原生关键词搜索及 404 页面。
- 原生评论列表、回复与提交表单；尊重后台的评论开关和邮箱/网站必填设置。
- 轻量响应式样式、键盘焦点、跳转正文链接及基础 Markdown 内容排版。
- JavaScript 入口和图片目录已预留；没有文档树、自动目录、深色模式、即时搜索、下载组件或额外 SEO 系统。

搜索使用 Typecho 的关键词查询，不提供前端全文索引。首页按 Typecho 的文章排序显示，文档层级和顺序管理留待后续阶段。

## 目录说明

```text
BlueDoc/
├── index.php                    # 主题元信息、首页
├── post.php                     # 文章详情
├── page.php                     # 独立页面
├── archive.php                  # 分类、标签、日期、作者归档
├── search.php                   # 搜索结果
├── comments.php                 # 原生评论列表与表单
├── header.php                   # HTML 头部、导航、搜索、header 钩子
├── footer.php                   # 页脚、脚本、footer 钩子
├── functions.php                # 配置入口、文本转义助手
├── 404.php                      # 未找到页面
├── partials/
│   └── document-list.php        # 首页、归档、搜索共用列表与分页
├── assets/
│   ├── css/style.css            # 基础布局、内容和移动端样式
│   ├── js/main.js               # 预留脚本入口
│   └── images/.gitkeep          # 保留图片资源目录
└── README.md                    # 安装、范围和开发说明
```

## 开发约定

- PHP 文件使用 UTF-8 无 BOM；保留 `__TYPECHO_ROOT_DIR__` 访问保护。
- 模板通过 `$this->need()` 加载；静态资源通过 `$this->options->themeUrl()` 定位，兼容站点子目录。
- 优先使用 Typecho 1.3.0 的原生命名空间 API；自定义辅助函数使用 `bluedoc` 前缀。
- 纯文本/属性使用 `bluedocEscape()`；正文与评论保留 Typecho 的原生 HTML 输出和权限处理。
- 不修改 Typecho 核心，不增加数据库表，不依赖外部 CDN。

## 验证与后续开发

2026-10-03 已在隔离的本地 **Typecho 1.3.0 + PHP 8.2.29 + SQLite** 环境完成验收：11 个 PHP 文件语法检查、后台主题识别与原生启用操作、配置入口、首页、文章、独立页面、分类/标签/作者/日期归档、搜索有结果/无结果、分页、评论提交与设置、密码保护、空首页及 404 均通过。PHP `E_ALL` 日志没有错误、警告或弃用提示。

已检查桌面与 375px 手机视口的首页和正文排版；手机页面没有横向溢出。静态资源、JavaScript 语法、PHP 直接访问保护及 UTF-8 无 BOM 检查通过。

验证使用的 Typecho 核心、PHP 运行时、数据库与测试内容均位于系统临时目录，不属于主题发行文件。主题复制到现有站点后，仍应按该站点的固定链接与插件配置复查。

后续建议按顺序推进：分类导航与文档树 → 文章目录与阅读导航 → 主题配置与品牌定制 → 深色模式与搜索增强。每阶段先确认数据来源、排序规则和移动端交互，再实现功能。

## 官方参考

- [Typecho 1.3.0 官方默认主题](https://github.com/typecho/typecho/tree/v1.3.0/usr/themes/default)
- [Typecho 1.3.0 模板调度源码](https://github.com/typecho/typecho/blob/v1.3.0/var/Widget/Archive.php)

主题元信息中的 `@link` 当前指向 Typecho 官方网站，尚无 BlueDoc 项目公开主页。
