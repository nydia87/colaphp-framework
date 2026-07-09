# colaphp-framework

PHP 开发简单的 MVC 框架

# 使用

**注意**：需要自定义常量

```php
// 自定义当前分组名（非必须），若定义则分组功能失效
const GROUP_NAME = 'api';

// 自动加载
require '../vendor/autoload.php';

ColaPHP\Framework\ColaPHP::start();
```