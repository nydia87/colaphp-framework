# colaphp-framework

PHP 开发简单的 MVC 框架

# 使用

**注意**：需要自定义常量

```php
// 项目路径
const PROJECT_PATH = 'D:\code\php';

// 框架路径
const FRAME_PATH = 'D:\code\php\vendor\colaphp\framework\src';

// 默认分组
const DEFAULT_GROUP_NAME = 'home';

// 自动加载
require '../vendor/autoload.php';

ColaPHP\Framework\ColaPHP::start();
```