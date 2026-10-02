<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;

$htmlPath = __DIR__ . '/reports/VillageLink_Final_Report.doc';
$outPath = __DIR__ . '/reports/VillageLink_Final_Report.docx';
$documentsCopy = 'C:\\Users\\user\\Documents\\VillageLink_Final_Report.docx';

if (! is_file($htmlPath)) {
    fwrite(STDERR, "HTML source not found: {$htmlPath}\n");
    exit(1);
}

$html = file_get_contents($htmlPath);
if ($html === false) {
    fwrite(STDERR, "Unable to read source report.\n");
    exit(1);
}

if (! preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $matches)) {
    fwrite(STDERR, "Could not extract HTML body.\n");
    exit(1);
}

$body = $matches[1];

// PhpWord cannot parse headings nested inside paragraphs.
$body = preg_replace('/<p[^>]*>\s*(<h1[^>]*>.*?<\/h1>)\s*<\/p>/is', '$1', $body) ?? $body;
$body = preg_replace('/<p[^>]*>\s*(<h2[^>]*>.*?<\/h2>)\s*<\/p>/is', '$1', $body) ?? $body;

// Convert CSS classes to inline styles PhpWord understands.
$body = str_replace('class="center"', 'style="text-align:center;"', $body);
$body = str_replace('class="caption"', 'style="text-align:center;font-weight:bold;"', $body);
$body = str_replace('class="toc toc-ch"', 'style="font-weight:bold;"', $body);
$body = str_replace('class="toc"', '', $body);
$body = str_replace('<div class="pagebreak"></div>', '<br pagebreak="true"/>', $body);
$body = preg_replace('/<div class="figure-box">.*?<\/div>/s', '<p style="text-align:center;font-style:italic;">[Insert image here]</p>', $body) ?? $body;
$body = preg_replace('/<\/?div[^>]*>/', '', $body) ?? $body;

// Ensure headings are standalone block elements with alignment.
$body = preg_replace('/<h1([^>]*)>/', '<h1$1 style="text-align:center;">', $body) ?? $body;

$phpWord = new PhpWord();
$phpWord->setDefaultFontName('Times New Roman');
$phpWord->setDefaultFontSize(12);

$phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 240]);
$phpWord->addTitleStyle(2, ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'], ['spaceBefore' => 240, 'spaceAfter' => 120]);

$section = $phpWord->addSection([
    'marginTop' => 1440,
    'marginBottom' => 1440,
    'marginLeft' => 2160,
    'marginRight' => 1440,
]);

Html::addHtml($section, $body, false, false);

$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($outPath);

if (is_dir(dirname($documentsCopy))) {
    copy($outPath, $documentsCopy);
}

echo "Report created: {$outPath}\n";
if (is_file($documentsCopy)) {
    echo "Copy saved: {$documentsCopy}\n";
}
