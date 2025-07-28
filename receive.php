<?php
// 基础设置
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Shanghai');

// 设置字符编码
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// 记录调试信息
function debug_log($message, $data = '') {
    // 确保字符串是UTF-8编码
    $message = mb_convert_encoding($message, 'UTF-8', 'auto');
    $log = date('Y-m-d H:i:s') . " | " . $message;
    
    if ($data) {
        if (is_string($data)) {
            $data = mb_convert_encoding($data, 'UTF-8', 'auto');
        }
        $log .= "\nData: " . print_r($data, true);
    }
    $log .= "\n" . str_repeat('-', 50) . "\n";
    
    // 使用UTF-8 BOM标记
    if (!file_exists('debug.log')) {
        file_put_contents('debug.log', "\xEF\xBB\xBF", FILE_APPEND | LOCK_EX);
    }
    
    file_put_contents('debug.log', $log, FILE_APPEND | LOCK_EX);
}

// 获取POST数据
$data = file_get_contents('php://input');
debug_log('接收到POST数据', $data);

// 查找XML内容
$pattern = '/<\?xml.*?<\/plist>/s';
if (preg_match($pattern, $data, $matches)) {
    $content = $matches[0];
    // 确保XML是UTF-8编码
    $content = mb_convert_encoding($content, 'UTF-8', 'auto');
    debug_log('XML内容', $content);
} else {
    debug_log('未找到XML内容');
    header('Location: error.html');
    exit;
}

// 解析XML数据
libxml_use_internal_errors(true);
$xml = new SimpleXMLElement($content);

if ($xml === false) {
    $errors = libxml_get_errors();
    debug_log('XML解析失败', $errors);
    libxml_clear_errors();
    header('Location: error.html');
    exit;
}

// 获取设备信息
$deviceInfo = array();

// 使用XPath获取数据
$keys = $xml->xpath('//dict/key');
$strings = $xml->xpath('//dict/string');

for ($i = 0; $i < count($keys); $i++) {
    $key = (string)$keys[$i];
    if (in_array($key, ['UDID', 'PRODUCT', 'VERSION', 'DEVICE_NAME'])) {
        $value = (string)$strings[$i];
        // 确保值是UTF-8编码
        $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        $deviceInfo[$key] = $value;
    }
}

debug_log('设备信息', $deviceInfo);

// 检查必要信息是否获取
if (empty($deviceInfo['UDID'])) {
    debug_log('未获取到UDID');
    header('Location: error.html');
    exit;
}

// 记录设备信息
$logData = array(
    'time' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'info' => $deviceInfo
);

// 使用UTF-8 BOM标记
if (!file_exists('udid_log.txt')) {
    file_put_contents('udid_log.txt', "\xEF\xBB\xBF", FILE_APPEND | LOCK_EX);
}

file_put_contents('udid_log.txt', 
    json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", 
    FILE_APPEND | LOCK_EX
);

// 构建重定向URL
$redirectUrl = 'udid.php?' . http_build_query([
    'udid' => $deviceInfo['UDID'],
    'product' => $deviceInfo['PRODUCT'] ?? '',
    'version' => $deviceInfo['VERSION'] ?? '',
    'device_name' => $deviceInfo['DEVICE_NAME'] ?? '',
    'time' => date('Y-m-d H:i:s')
]);

debug_log('重定向到', $redirectUrl);

// 设置响应头并重定向
header('Content-Type: text/html; charset=UTF-8');
header('HTTP/1.1 301 Moved Permanently');
header('Location: ' . $redirectUrl);
exit;
?> 