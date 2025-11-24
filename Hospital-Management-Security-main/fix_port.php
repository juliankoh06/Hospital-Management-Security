<?php
// Script to fix MySQL port in all PHP files

$files = [
    'search.php',
    'doctor-panel.php',
    'admin-panel.php',
    'admin-panel1.php',
    'newfunc.php',
    'func1.php',
    'func2.php',
    'func3.php',
    'func.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace(
            'mysqli_connect("localhost","root"',
            'mysqli_connect("localhost:3307","root"',
            $content
        );
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "All files updated!\n";
?>
