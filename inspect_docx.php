<?php
$path = __DIR__ . '/reports/VillageLink_Final_Report.docx';
$z = new ZipArchive();
if ($z->open($path) !== true) {
    echo "Cannot open zip\n";
    exit(1);
}
echo "Files in docx:\n";
for ($i = 0; $i < $z->numFiles; $i++) {
    $stat = $z->statIndex($i);
    echo $stat['name'] . ' (' . $stat['size'] . " bytes)\n";
}
$xml = $z->getFromName('word/document.xml');
$z->close();
if ($xml === false) {
    echo "No document.xml\n";
    exit(1);
}
libxml_use_internal_errors(true);
$doc = simplexml_load_string($xml);
if ($doc === false) {
    echo "document.xml INVALID:\n";
    foreach (libxml_get_errors() as $err) {
        echo trim($err->message) . " line {$err->line}\n";
    }
} else {
    echo "document.xml: valid XML\n";
}
echo 'document.xml size: ' . strlen($xml) . " bytes\n";
