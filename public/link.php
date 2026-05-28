<?php

$target = realpath(__DIR__ . '/../storage/app/public');
$link   = __DIR__ . '/storage';

echo "<pre>";

if (!$target) {
    die("❌ Target path not found: storage/app/public");
}

echo "TARGET: $target\n";
echo "LINK:   $link\n\n";

/*
|--------------------------------------------------------------------------
| Remove REAL folder if exists
|--------------------------------------------------------------------------
*/
if (is_dir($link) && !is_link($link)) {

    echo "⚠️ Real storage folder detected.\n";
    echo "Removing old folder...\n";

    function deleteFolder($folder) {
        foreach (scandir($folder) as $item) {
            if ($item == '.' || $item == '..') continue;

            $path = $folder . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                deleteFolder($path);
            } else {
                unlink($path);
            }
        }

        rmdir($folder);
    }

    deleteFolder($link);

    echo "✅ Old folder removed.\n\n";
}

/*
|--------------------------------------------------------------------------
| Create symlink
|--------------------------------------------------------------------------
*/
if (is_link($link)) {

    echo "✅ Symlink already exists.\n";
    echo "Points to: " . readlink($link);

} else {

    if (@symlink($target, $link)) {

        echo "✅ Symlink created successfully!\n";
        echo "Images should now work.";

    } else {

        echo "❌ Symlink creation failed.\n\n";

        echo "Possible GoDaddy restrictions:\n";
        echo "- Shared hosting blocks symlink()\n";
        echo "- Incorrect file permissions\n";
        echo "- open_basedir restriction enabled\n";
    }
}

echo "</pre>";