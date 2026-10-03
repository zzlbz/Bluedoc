# BlueDoc

面向产品帮助中心、软件教程与知识库的独立 Typecho 文档主题。

当前版本 **V0.3.1**，兼容 **Typecho 1.3.0 / PHP 8.2**，无需插件或构建步骤。

首页按“选择设备 → 浏览分类 → 阅读指南”组织内容。四个平台卡片按分类 MID 读取分类名称、描述和最新三篇教程，支持原生无限层级分类树、多级面包屑、三栏阅读、H2/H3 目录和移动抽屉。文章只显示所属分类与最后更新时间，不显示作者、评论和博客发布时间。

## 安装

将 `theme/` 内全部内容复制到 Typecho 的 `usr/themes/BlueDoc/`，在后台启用 **BlueDoc 0.3.1**。不要直接安装项目根目录。

- [安装、升级与分类搭建](theme/README.md)
- [架构设计](docs/architecture.md)
- [V0.3 测试记录与复现步骤](docs/v0.3.1-validation.md)
- [更新记录](changelog.md)

主题不自动创建分类，不迁移站点数据。请在 Typecho 后台建立新手入门、客户端教程、订阅配置、软件下载、常见问题、服务说明，并按设备建立子分类。已有 V0.2 平台与热门教程配置保持兼容。

## 开发验证

```sh
php tests/navigation.php
node --check theme/assets/js/main.js
node tests/typecho-smoke.mjs
```

HTTP 检查需要已经安装并启用 V0.3 的隔离测试站，默认地址为 `http://127.0.0.1:18230/`，可用 `BLUEDOC_BASE_URL` 配置。主题运行不需要 Node.js。

后续版本计划增加下载组件、深色模式和更丰富的检索体验。
