<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>系统发生错误</title>
<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
<meta name="Generator" content="EditPlus"/>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Microsoft Yahei', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 15px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

a {
    text-decoration: none;
    color: #3b82f6;
}
a:hover {
    color: #f97316;
}

h2 {
    font-size: 24px;
    font-weight: 700;
    color: #dc2626;
    padding-bottom: 16px;
    margin-bottom: 20px;
    border-bottom: 2px solid #fecaca;
    display: flex;
    align-items: center;
    gap: 8px;
}

.title {
    margin: 16px 0 8px;
    padding-left: 12px;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    border-left: 3px solid #f97316;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.message {
    padding: 16px 20px;
    margin: 8px 0 16px;
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
    border-radius: 8px;
    line-height: 1.8;
    font-size: 14px;
    word-break: break-word;
}

#trace {
    padding: 16px 20px;
    margin: 8px 0 16px;
    background: #1e293b;
    color: #e2e8f0;
    border: 1px solid #334155;
    border-radius: 8px;
    line-height: 1.8;
    font-size: 13px;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    overflow-x: auto;
    white-space: pre-wrap;
    word-break: break-all;
}

.notice {
    max-width: 1200px;
    min-width: 600px;
    width: 100%;
    padding: 32px;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 10px 30px -5px rgba(0, 0, 0, 0.15);
    border: 1px solid #e5e7eb;
}

.red {
    color: #dc2626;
    font-weight: 700;
    background: #fef2f2;
    padding: 2px 8px;
    border-radius: 4px;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
}
</style>
</head>
<body>
<div class="notice">
<h2>系统发生错误 </h2>
<?php if(isset($e['file'])) {?>
<p><strong>错误位置:</strong>　FILE: <span class="red"><?php echo $e['file'] ;?></span>　LINE: <span class="red"><?php echo $e['line'];?></span></p>
<?php }?>
<p class="title">[ 错误信息 ]</p>
<p class="message"><?php echo strip_tags($e['message']);?></p>
<?php if(isset($e['trace'])) {?>
<p class="title">[ TRACE ]</p>
<p id="trace">
<?php echo nl2br($e['trace']);?>
</p>
<?php }?>
</div>
</body>
</html>