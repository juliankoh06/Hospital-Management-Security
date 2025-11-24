<?php
/**
 * Automated XSS Security Testing Script
 * Hospital Management System
 * Date: November 15, 2025
 * 
 * This script tests for XSS vulnerabilities by checking if outputs are properly escaped
 * Run this script from command line: php automated_test_script.php
 */

echo "=================================================\n";
echo "   XSS Security Testing - Automated Scanner\n";
echo "   Hospital Management System\n";
echo "   Date: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================\n\n";

// Test payloads
$testPayloads = [
    "<script>alert('XSS')</script>",
    "<img src=x onerror=alert('XSS')>",
    "<svg/onload=alert('XSS')>",
    '"><script>alert(String.fromCharCode(88,83,83))</script>',
    "<iframe src=\"javascript:alert('XSS')\">",
    "<body onload=alert('XSS')>",
    "<input onfocus=alert('XSS') autofocus>",
    "<marquee onstart=alert('XSS')>",
    "<details open ontoggle=alert('XSS')>",
    "javascript:alert('XSS')"
];

// Files to check
$filesToCheck = [
    'search.php',
    'contact.php',
    'prescribe.php',
    'admin-panel.php',
    'doctor-panel.php',
    'appsearch.php',
    'admin-panel1.php',
    'newfunc.php',
    'func1.php',
    'func3.php',
    'include/header.php'
];

$vulnerabilities = [];
$secureOutputs = [];

echo "🔍 Scanning PHP files for XSS vulnerabilities...\n\n";

// Check each file
foreach ($filesToCheck as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file (skipping)\n";
        continue;
    }
    
    echo "📄 Analyzing: $file\n";
    $content = file_get_contents($file);
    
    // Check for vulnerable patterns
    $vulnerablePatterns = [
        '/echo\s+\$_(GET|POST|REQUEST)\[[^\]]+\](?!.*htmlspecialchars)/' => 'Direct echo of user input without escaping',
        '/echo\s+\$[a-zA-Z_]+\s*;(?!.*htmlspecialchars)/' => 'Echo variable without htmlspecialchars',
        '/print\s+\$_(GET|POST|REQUEST)/' => 'Direct print of user input',
        '/<td>\s*<\?php\s+echo\s+\$row\[[^\]]+\]\s*;\s*\?>\s*<\/td>(?!.*htmlspecialchars)/' => 'Table cell with unescaped database value',
        '/\$[a-zA-Z_]+\s*=\s*\$_(GET|POST|REQUEST)\[[^\]]+\];\s*(?!.*htmlspecialchars)(?!.*filter_var)(?!.*mysqli_real_escape_string)/' => 'Unvalidated input stored',
        '/mysqli_query\s*\(\s*\$[^,]+,\s*["\'].*\$[a-zA-Z_]+.*["\']/' => 'SQL query with string concatenation (SQL injection risk)',
    ];
    
    // Check for secure patterns
    $securePatterns = [
        '/htmlspecialchars\s*\(/' => 'Using htmlspecialchars() for output encoding',
        '/htmlentities\s*\(/' => 'Using htmlentities() for output encoding',
        '/\$stmt\s*=\s*\$[^-]+->prepare\s*\(/' => 'Using prepared statements (mysqli)',
        '/\$con->prepare\s*\(/' => 'Using prepared statements',
        '/filter_var\s*\(/' => 'Using filter_var() for input validation',
        '/mysqli_real_escape_string\s*\(/' => 'Using mysqli_real_escape_string()',
        '/urlencode\s*\(/' => 'Using urlencode() for URL parameters',
        '/ENT_QUOTES/' => 'Using ENT_QUOTES flag for comprehensive escaping',
    ];
    
    $fileVulnerabilities = [];
    $fileSecurePractices = [];
    
    // Scan for vulnerable patterns
    foreach ($vulnerablePatterns as $pattern => $description) {
        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $lineNum = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                $fileVulnerabilities[] = [
                    'line' => $lineNum,
                    'code' => trim($match[0]),
                    'issue' => $description
                ];
            }
        }
    }
    
    // Scan for secure patterns
    foreach ($securePatterns as $pattern => $description) {
        if (preg_match($pattern, $content)) {
            $fileSecurePractices[] = $description;
        }
    }
    
    // Report findings
    if (!empty($fileVulnerabilities)) {
        echo "   ❌ VULNERABILITIES FOUND:\n";
        foreach ($fileVulnerabilities as $vuln) {
            echo "      Line {$vuln['line']}: {$vuln['issue']}\n";
            echo "      Code: " . substr($vuln['code'], 0, 60) . "...\n\n";
        }
        $vulnerabilities[$file] = $fileVulnerabilities;
    } else {
        echo "   ✅ No obvious vulnerabilities detected\n";
    }
    
    if (!empty($fileSecurePractices)) {
        echo "   ✅ SECURE PRACTICES FOUND:\n";
        foreach ($fileSecurePractices as $practice) {
            echo "      • $practice\n";
        }
        $secureOutputs[$file] = $fileSecurePractices;
    }
    
    echo "\n";
}

// Summary Report
echo "\n=================================================\n";
echo "                 SUMMARY REPORT\n";
echo "=================================================\n\n";

echo "📊 Files Scanned: " . count($filesToCheck) . "\n";
echo "❌ Files with Potential Vulnerabilities: " . count($vulnerabilities) . "\n";
echo "✅ Files with Security Measures: " . count($secureOutputs) . "\n\n";

if (!empty($vulnerabilities)) {
    echo "⚠️  CRITICAL ISSUES REQUIRING ATTENTION:\n";
    echo "─────────────────────────────────────────\n";
    foreach ($vulnerabilities as $file => $issues) {
        echo "📄 $file (" . count($issues) . " issues)\n";
        foreach ($issues as $issue) {
            echo "   • Line {$issue['line']}: {$issue['issue']}\n";
        }
        echo "\n";
    }
    
    echo "🔧 RECOMMENDED FIXES:\n";
    echo "─────────────────────────────────────────\n";
    echo "1. Apply htmlspecialchars() to all output:\n";
    echo "   echo htmlspecialchars(\$variable, ENT_QUOTES, 'UTF-8');\n\n";
    echo "2. Use prepared statements for database queries:\n";
    echo "   \$stmt = \$con->prepare(\"SELECT * FROM table WHERE id = ?\");\n";
    echo "   \$stmt->bind_param(\"i\", \$id);\n\n";
    echo "3. Validate all user inputs:\n";
    echo "   \$id = filter_var(\$_POST['id'], FILTER_VALIDATE_INT);\n\n";
}

if (count($vulnerabilities) === 0) {
    echo "🎉 EXCELLENT! No obvious XSS vulnerabilities detected!\n";
    echo "   All analyzed files appear to use proper output encoding.\n\n";
}

// Generate test report
echo "\n=================================================\n";
echo "          GENERATING TEST REPORT\n";
echo "=================================================\n\n";

$reportContent = generateHTMLReport($vulnerabilities, $secureOutputs, $filesToCheck);
$reportFile = 'security_testing/test_report_' . date('Y-m-d_H-i-s') . '.html';

if (!is_dir('security_testing')) {
    mkdir('security_testing', 0755, true);
}

file_put_contents($reportFile, $reportContent);
echo "✅ Detailed HTML report saved to: $reportFile\n\n";

// Test payload safety
echo "=================================================\n";
echo "          TESTING PAYLOAD ENCODING\n";
echo "=================================================\n\n";

echo "Testing if htmlspecialchars properly encodes XSS payloads:\n\n";

foreach (array_slice($testPayloads, 0, 5) as $payload) {
    $encoded = htmlspecialchars($payload, ENT_QUOTES, 'UTF-8');
    $isSafe = ($encoded !== $payload);
    
    echo "Payload: " . substr($payload, 0, 40) . "...\n";
    echo "Encoded: " . substr($encoded, 0, 40) . "...\n";
    echo "Status: " . ($isSafe ? "✅ SAFE" : "❌ NOT ENCODED") . "\n\n";
}

echo "\n=================================================\n";
echo "              SCAN COMPLETE\n";
echo "=================================================\n";

// Function to generate HTML report
function generateHTMLReport($vulnerabilities, $secureOutputs, $filesScanned) {
    $totalVulns = array_sum(array_map('count', $vulnerabilities));
    $timestamp = date('Y-m-d H:i:s');
    
    $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>XSS Security Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; }
        .summary { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .vulnerability { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; }
        .secure { background: #d4edda; border-left: 4px solid #28a745; padding: 15px; margin: 10px 0; }
        .file-section { margin: 20px 0; }
        .stats { display: flex; gap: 20px; margin: 20px 0; }
        .stat-box { flex: 1; padding: 20px; background: white; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .critical { color: #dc3545; }
        .success { color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>🔒 XSS Security Test Report</h1>
        <p>Hospital Management System</p>
        <p><strong>Generated:</strong> $timestamp</p>
    </div>
    
    <div class='summary'>
        <h2>Executive Summary</h2>
        <div class='stats'>
            <div class='stat-box'>
                <div class='stat-number'>" . count($filesScanned) . "</div>
                <div>Files Scanned</div>
            </div>
            <div class='stat-box'>
                <div class='stat-number critical'>$totalVulns</div>
                <div>Vulnerabilities</div>
            </div>
            <div class='stat-box'>
                <div class='stat-number success'>" . count($secureOutputs) . "</div>
                <div>Secure Files</div>
            </div>
        </div>
    </div>
    
    <div class='summary'>
        <h2>Detailed Findings</h2>";
    
    if (!empty($vulnerabilities)) {
        foreach ($vulnerabilities as $file => $issues) {
            $html .= "<div class='file-section'>
                <h3>📄 $file</h3>";
            foreach ($issues as $issue) {
                $html .= "<div class='vulnerability'>
                    <strong>Line {$issue['line']}:</strong> {$issue['issue']}<br>
                    <code>" . htmlspecialchars($issue['code']) . "</code>
                </div>";
            }
            $html .= "</div>";
        }
    } else {
        $html .= "<div class='secure'>
            <h3>✅ No Vulnerabilities Detected</h3>
            <p>All scanned files appear to use proper output encoding and security practices.</p>
        </div>";
    }
    
    $html .= "
    </div>
    
    <div class='summary'>
        <h2>Security Measures Detected</h2>
        <table>
            <thead>
                <tr>
                    <th>File</th>
                    <th>Security Practices</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($secureOutputs as $file => $practices) {
        $html .= "<tr>
            <td>$file</td>
            <td>" . implode(', ', $practices) . "</td>
        </tr>";
    }
    
    $html .= "
            </tbody>
        </table>
    </div>
    
    <div class='summary'>
        <h2>Recommendations</h2>
        <ol>
            <li>Apply <code>htmlspecialchars(\$var, ENT_QUOTES, 'UTF-8')</code> to all user inputs before output</li>
            <li>Use prepared statements for all database queries</li>
            <li>Implement input validation using <code>filter_var()</code></li>
            <li>Set Content-Security-Policy headers</li>
            <li>Regular security audits and penetration testing</li>
        </ol>
    </div>
</body>
</html>";
    
    return $html;
}
?>
