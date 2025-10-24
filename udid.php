<?php
// 基础设置
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// 设置缓存控制
header('Cache-Control: public, max-age=0');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 0) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// 强制HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// 获取并验证参数
$udid = trim($_GET['udid'] ?? '');
if (empty($udid)) {
    header('Location: error.html', true, 302);
    exit;
}

// 获取设备信息
$product_name = $_GET['product'] ?? '';
$device_name = $_GET['device_name'] ?? '';
$version = $_GET['version'] ?? '';
$device_map = [
    // iPhone 16 系列
    'iPhone16,3' => 'iPhone 16',
    'iPhone16,4' => 'iPhone 16 Plus',
    'iPhone16,5' => 'iPhone 16 Pro',
    'iPhone16,6' => 'iPhone 16 Pro Max',
    
    // iPhone 15 系列
    'iPhone15,4' => 'iPhone 15',
    'iPhone15,5' => 'iPhone 15 Plus',
    'iPhone16,1' => 'iPhone 15 Pro',
    'iPhone16,2' => 'iPhone 15 Pro Max',
    
    // iPhone 14 系列
    'iPhone14,7' => 'iPhone 14',
    'iPhone14,8' => 'iPhone 14 Plus',
    'iPhone15,2' => 'iPhone 14 Pro',
    'iPhone15,3' => 'iPhone 14 Pro Max',
    
    // iPhone 13 系列
    'iPhone14,4' => 'iPhone 13 mini',
    'iPhone14,5' => 'iPhone 13',
    'iPhone14,2' => 'iPhone 13 Pro',
    'iPhone14,3' => 'iPhone 13 Pro Max',
    
    // iPhone 12 系列
    'iPhone13,1' => 'iPhone 12 mini',
    'iPhone13,2' => 'iPhone 12',
    'iPhone13,3' => 'iPhone 12 Pro',
    'iPhone13,4' => 'iPhone 12 Pro Max',
    
    // iPhone 11 系列
    'iPhone12,1' => 'iPhone 11',
    'iPhone12,3' => 'iPhone 11 Pro',
    'iPhone12,5' => 'iPhone 11 Pro Max',
    
    // iPhone XS/XR 系列
    'iPhone11,8' => 'iPhone XR',
    'iPhone11,2' => 'iPhone XS',
    'iPhone11,4' => 'iPhone XS Max',
    'iPhone11,6' => 'iPhone XS Max',
    
    // iPhone X 系列
    'iPhone10,3' => 'iPhone X',
    'iPhone10,6' => 'iPhone X',
    
    // iPhone SE 系列
    'iPhone12,8' => 'iPhone SE (2nd gen)',
    'iPhone14,6' => 'iPhone SE (3rd gen)',
    'iPhone8,4' => 'iPhone SE (1st gen)',
    
    // iPhone 8 系列
    'iPhone10,1' => 'iPhone 8',
    'iPhone10,4' => 'iPhone 8',
    'iPhone10,2' => 'iPhone 8 Plus',
    'iPhone10,5' => 'iPhone 8 Plus',
    
    // iPad Pro 系列
    'iPad14,3' => 'iPad Pro 11" (4th gen)',
    'iPad14,4' => 'iPad Pro 11" (4th gen)',
    'iPad14,5' => 'iPad Pro 12.9" (6th gen)',
    'iPad14,6' => 'iPad Pro 12.9" (6th gen)',
    'iPad13,8' => 'iPad Pro 12.9" (5th gen)',
    'iPad13,9' => 'iPad Pro 12.9" (5th gen)',
    'iPad13,10' => 'iPad Pro 12.9" (5th gen)',
    'iPad13,11' => 'iPad Pro 12.9" (5th gen)',
    'iPad13,4' => 'iPad Pro 11" (3rd gen)',
    'iPad13,5' => 'iPad Pro 11" (3rd gen)',
    'iPad13,6' => 'iPad Pro 11" (3rd gen)',
    'iPad13,7' => 'iPad Pro 11" (3rd gen)',
    
    // iPad Air 系列
    'iPad13,16' => 'iPad Air (5th gen)',
    'iPad13,17' => 'iPad Air (5th gen)',
    'iPad13,1' => 'iPad Air (4th gen)',
    'iPad13,2' => 'iPad Air (4th gen)',
    
    // iPad 系列
    'iPad13,18' => 'iPad 10',
    'iPad13,19' => 'iPad 10',
    'iPad12,1' => 'iPad 9',
    'iPad12,2' => 'iPad 9',
    
    // iPad mini 系列
    'iPad14,1' => 'iPad mini (6th gen)',
    'iPad14,2' => 'iPad mini (6th gen)',
    'iPad11,1' => 'iPad mini (5th gen)',
    'iPad11,2' => 'iPad mini (5th gen)'
];

// 格式化设备型号显示
$display_name = '';
if (!empty($product_name)) {
    // 提取设备标识符（例如：iPhone15,4）
    if (preg_match('/^(iPhone|iPad)(\d+,\d+)/', $product_name, $matches)) {
        $device_identifier = $matches[1] . $matches[2];
        // 查找映射表中的设备名称
        $display_name = $device_map[$device_identifier] ?? $product_name;
    } else {
        $display_name = $product_name;
    }
} else if (!empty($device_name)) {
    $display_name = $device_name;
}

// 组合显示设备型号和系统版本
$device_info = $display_name;
if (!empty($version)) {
    $device_info .= ' ' . $version;
}

// 格式化时间
$time = date('Y-m-d H:i:s', strtotime($_GET['time'] ?? 'now'));

// 压缩输出
ob_start("ob_gzhandler");
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="styles/styles.css">
    <title>您的UDID</title>
</head>
<body>
    <div class="container">
        <div class="result-content">
            <div class="result-header">
                <div class="success-icon">✓</div>
                <h2>获取成功！</h2>
            </div>
            <div class="device-info">
                <p><strong>设备信息：</strong><span><?php echo htmlspecialchars($device_info, ENT_QUOTES, 'UTF-8'); ?></span></p>
                <p><strong>获取时间：</strong><span><?php echo $time; ?></span></p>
            </div>
            <div class="udid-section">
                <h3>设备 UDID</h3>
                <div class="udid-wrapper">
                    <div class="udid" id="udid"><?php echo htmlspecialchars($udid, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
            <div class="button-group">
                <button class="copy-btn" onclick="copyUDID()">复制 UDID</button>
                <button class="copy-btn back-btn" onclick="window.location.href='index.html'">返回首页</button>
            </div>
        </div>
    </div>

    <script defer>
    async function copyUDID() {
        const udid = document.getElementById('udid').innerText;
        let success = false;

        // 尝试使用现代API
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(udid);
                success = true;
            } catch (err) {
                console.error('剪贴板API失败:', err);
            }
        }

        // 如果现代API失败，使用兼容方案
        if (!success) {
            const textArea = document.createElement('textarea');
            textArea.value = udid;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                success = document.execCommand('copy');
            } catch (err) {
                console.error('复制失败:', err);
            }
            document.body.removeChild(textArea);
        }

        // 显示操作结果
        showToast(success ? 'UDID已复制到剪贴板！' : '复制失败，请手动复制');
    }

    function showToast(message) {
        // 移除现有的toast
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }

        // 创建新的toast
        const toast = document.createElement('div');
        toast.className = 'toast';
        
        // 创建消息容器
        const messageSpan = document.createElement('span');
        messageSpan.textContent = message;
        toast.appendChild(messageSpan);
        
        // 添加到页面
        document.body.appendChild(toast);

        // 设置消失动画
        setTimeout(() => {
            toast.style.animation = 'toastSlideDown 0.3s ease forwards';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 2000);
    }
    </script>
</body>
</html> 