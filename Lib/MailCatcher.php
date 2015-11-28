<?php

namespace App\Lib;


use App\Lib\Helpers\FakeBrowser;
use App\Lib\Helpers\RegExp;
use Symfony\Component\DomCrawler\Crawler;

class MailCatcher
{
    public $resultMails = [];
    public $noWebSiteOnYP = [];
    public $websiteNoEmail = [];
    private $argument;

    public function __construct($argv)
    {
        $this->argument = $argv;
    }

    public function run()
    {
        $url = $this->argument[1];
        $content = FakeBrowser::get($url);

        $items = [];
        $crawler = new Crawler(null, $url);
        $crawler->addContent($content, "text/html");
        foreach ($crawler->filter('.result_box') as $node) {
            $item = Helpers\Dom::getHtml($node);
            $items[] = $item;
        }

        echo "Found " . count($items) . " items";
        echo "\n";
        echo "parsing items\n";
        $i = 1;
        $withEmail = [];
        $notEmail = [];

        foreach ($items as $item) {
            echo $i . " ";
            $emailUrl = RegExp::getFirstMatch('/;" href="(.+?)\?v=e/', $item);
            if ($emailUrl !== null) {
                $emailUrl .= "?v=e";
                $withEmail[] = $emailUrl;
                echo $emailUrl;
                echo " had email";
            } else {
                $notEmailUrl = RegExp::getFirstMatch('/;" href="(.+?)".+><h2>/', $item);
                $notEmail[] = $notEmailUrl;
                echo $notEmailUrl;
                echo " had no email";
            }
            echo "\n";
            $i++;
        }

        echo "Fetching the email from YellowPage (total:" . count($withEmail) . ")";
        echo "\n";
        $i = 1;

        foreach ($withEmail as $emailUrl) {
            echo "$i. ";
            $content = FakeBrowser::get($emailUrl);
            $idMail = RegExp::getFirstMatch('/href=".+send-email.html\?(.+?)"/', $content);
            if (null !== $idMail) {
                //Get Email
                $ypEmailUrl = 'https://www.yellow.com.mt/development-tools/send-email-iframe.html?';
                $ypEmailUrl .= $idMail;
                $iframe = FakeBrowser::get($ypEmailUrl);
                $email = RegExp::getFirstMatch('/company_email" value="(.+?)"/', $iframe);
                if ($email !== null) {
                    echo " found email: $email\n";
                    $this->resultMails[] = $email;
                } else {
                    echo " error on iframe\n";
                }

            } else {
                echo "Error on" . $emailUrl . "\n";
            }
            $i++;
        }

        $i = 1;
        $websites = [];
        $notWebsite = [];
        echo "\nAttempt to get other email (total:" . count($notEmail) . ")\n";
        foreach ($notEmail as $item) {
            echo "$i. ";
            $websiteUrl = FakeBrowser::get($item);
            $websiteUrl = RegExp::getFirstMatch('/redirector.php\?yelref=[^"](.+?)"/', $websiteUrl);
            if ($websiteUrl !== null) {
                $websiteUrl = "h" . $websiteUrl;
                echo "found webSite: $websiteUrl\n";
                $websites[] = $websiteUrl;
            } else {
                $notWebsite[] = $item;
                echo "$item does not have a website apparently\n";
            }
            $i++;
        }

        $this->noWebSiteOnYP = $notWebsite;

        echo "\nChecking in the company websites (total:" . count($websites) . ")\n";
        $i = 1;
        $notMailOnIndex = [];
        foreach ($websites as $website) {
            echo "$i. ";
            $contactPage = $this->searchContactPage($website);
            if (!empty($contactPage)) {
                echo " contact page not Empty for $website, searching for email\n";
                $email = RegExp::getFirstMatch('/mailto:(.+?)"/', $contactPage);
                if (!empty($email)) {
                    echo "\t found $email\n";
                    $this->resultMails[] = $email;
                } else {
                    echo "\t no mailto found\n";
                    echo "\t trying from homePage\n";
                    $contactPage = FakeBrowser::get($website);
                    $email = RegExp::getFirstMatch('/mailto:(.+?)"/', $contactPage);
                    if (!empty($email)) {
                        echo "\t found $email\n";
                        $this->resultMails[] = $email;
                    } else {
                        echo "\t no mailto found\n";
                        $notMailOnIndex[] = $website;
                    }
                }
            } else {
                $notMailOnIndex[] = $website;
            }

            $i++;
        }

        $this->websiteNoEmail = $notMailOnIndex;
        //now we should crawl those $notMailOnIndex from scratch

    }

    private function searchContactPage($website)
    {
        $contactPages = [
            '/contact',
            '/contact.php',
            '/contactus',
            '/contacts',
            '/en/contacts',
            '/contact-us',
            '/en/contact-us.htm',
            '/about'
        ];

        set_error_handler(
            create_function(
                '$severity, $message, $file, $line',
                'throw new ErrorException($message, $severity, $severity, $file, $line);'
            )
        );


        foreach ($contactPages as $contact) {
            $html = null;
            try {
                $html = FakeBrowser::get($website . $contact);
            } catch (\Exception $e) {
                echo "\t contact page not $contact\n";;
            }
            if ($html !== null) {
                restore_error_handler();
                return $html;
            }
        }

        restore_error_handler();
        return false;
    }


} 