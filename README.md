# BlueDoc

通用的 Typecho 文档中心主题，适用于产品帮助中心、教程站和知识库。

当前版本 **V0.4.0**，兼容 **Typecho 1.3.0 / PHP 8.2**，无需插件、外部框架或构建步骤。

首页由开始阅读、搜索、快速导航、全部分类、分类文章目录和精选文档组成。全站采用固定左侧导航与顶部路径，入口自动读取原生分类，不预设设备或行业。支持无限级分类、原生文章序号、同分类上下篇、明暗配色、导航状态记忆、代码复制、提示框、H2/H3 目录和移动抽屉。

## 安装

将 `theme/` 内全部内容复制到 Typecho 的 `usr/themes/BlueDoc/`，在后台启用 **BlueDoc 0.4.0**。不要直接安装项目根目录。

- [安装、分类与升级说明](theme/README.md)
- [架构设计](docs/architecture.md)
- [V0.4 测试记录与修改文件](docs/v0.4-validation.md)
- [更新记录](changelog.md)

在后台建立自己的分类即可使用。快速导航默认显示前四个一级分类，也可选择父分类的子分类。文档阅读顺序默认优先使用文章编辑器中的“文档阅读序号”；首页分类文章目录最多展示每个分支前六篇，分类页保留完整列表。最新推荐保持独立排序。

## 开发验证

```sh
php tests/navigation.php
node --check theme/assets/js/main.js
node --check theme/assets/js/color-mode.js
node tests/typecho-smoke.mjs
```


![统一文档首页：隔离测试数据](demo/v0.4-home.png)

后续计划：下载组件、检索增强和代码语法高亮。
