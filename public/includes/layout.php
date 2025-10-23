<?php
if (!isset($pageContent)) {
    $pageContent = '';
}

include __DIR__ . '/header.php';

echo $pageContent;

include __DIR__ . '/footer.php';
