<?php
$path = __DIR__ . '/reports/VillageLink_Final_Report.docx';
$z = new ZipArchive();
$z->open($path);
foreach (['[Content_Types].xml', 'word/_rels/document.xml.rels', '_rels/.rels'] as $name) {
    echo "=== $name ===\n";
    echo $z->getFromName($name) . "\n\n";
}
$z->close();
