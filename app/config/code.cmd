<?php

$config_file = __DIR__ . '/config.yml';
$config_backup_dir = "bck";
if (!file_exists($config_backup_dir)) {
    mkdir($config_backup_dir, 0755, TRUE);
}
copy($config_file, $config_backup_dir . DIRECTORY_SEPARATOR . pathinfo($config_file, PATHINFO_FILENAME) . "--" . date("Y-m-d-H-i-s")) . "." . pathinfo($config_file, PATHINFO_EXTENSION);

$options = getopt("k:v:");
if (!empty($options['k']) && $options['v']) {
    $key = $options['k'];
    $newValue = $options['v'];

    $fileContent = file_get_contents($config_file);
    preg_match_all("/^\s*$key\:\s*(.*)/m", $fileContent, $matches);
    
    if (!empty($matches[1][0])) {
        $currentValue = $matches[1][0];
        $fileContent = str_replace($currentValue, $newValue, $fileContent);
        if (file_put_contents($config_file, $fileContent)) {
            echo "File Updated";
        }
    } else {
        echo "merchant_id not found\n";
    }
} else {
    echo "Key and Value Required Command Example:\n";
    echo "cron -k KEY -v VALUE\n";
}
?>
