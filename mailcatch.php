<?php
require('vendor/autoload.php');

$mailCatch = new \App\Lib\MailCatcher($argv);

$mailCatch->run();
$resultFile = "";
$noWebFile = "\n\n<br /><h3>" . date(DATE_RFC2822) . "</h3>\n <h1>Result $argv[1]</h1>:\n<ol>";
$resultHtml = "<h2>Found mail</h2><ol>";
$resultFile = "\n" . date(DATE_RFC2822) . "\n Result $argv[1]:\n";
echo "\n Result:\n";
$i = 1;
foreach ($mailCatch->resultMails as $mail) {
    echo "$i. $mail\n";
    $resultHtml .= "<li>$mail</li>\n";
    $resultFile .= "$i. $mail\n";
    $i++;
}
file_put_contents("results.txt", $resultFile, FILE_APPEND);
echo "\nNo mail on Website:\n";
$noWebFile .= $resultHtml . "</ol>";
$noWebFile .= "<h2>No Email but Website</h2><ol>";
foreach ($mailCatch->websiteNoEmail as $item) {
    echo ". $item\n";
    $noWebFile .= '<li><a href="' . $item . '" target="_blank">' . $item . '</a></li>' . "\n";
}
$noWebFile .= "</ol>\n";


echo "\nNo Website on YellowPage:\n";
$noWebFile .= "<h2>Not Found mail nor website</h2><ol>";
foreach ($mailCatch->noWebSiteOnYP as $item) {
    echo ". $item\n";
    $noWebFile .= '<li><a href="' . $item . '" target="_blank">' . $item . '</a></li>' . "\n";
}
$noWebFile .= "</ol>\n";

file_put_contents("noWebsite.html", $noWebFile, FILE_APPEND);
