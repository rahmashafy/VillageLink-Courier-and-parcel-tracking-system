<?php
$path = __DIR__ . '/reports/VillageLink_Final_Report.docx';
$z = new ZipArchive();
$z->open($path);
$x = $z->getFromName('word/document.xml');
$p = strpos($x, '<w:drawing>');
echo substr($x, $p, 1500);
$z->close();
