# colaphp-framework

PHP 开发简单的 MVC 框架

# 使用

**注意**：需要自定义常量

```php
// 项目路径
const PROJECT_PATH = 'D:\code\php';

// 自定义框架路径（非必须），默认值 `PROJECT_PATH` + `\vendor\colaphp\framework\src`
const FRAME_PATH = 'D:\code\framework\src';

// 自定义当前分组名（非必须），若定义则分组功能失效
const GROUP_NAME = 'api';

// 自动加载
require '../vendor/autoload.php';

ColaPHP\Framework\ColaPHP::start();
```