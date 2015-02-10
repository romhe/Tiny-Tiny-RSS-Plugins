<?php
class Af_Golem extends Plugin {

    private $host;

    function about() {
        return array(1.5,
            "Fetch content of golem feed",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }

    protected function load_page($link){

        $doc = new DOMDocument();
        @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($link), 'HTML-ENTITIES', "UTF-8"));

        $basenode = false;
        $add_content = "";

        if ($doc) {
            $xpath = new DOMXPath($doc);

            $nextpage = $xpath->query('//table[@id="table-jtoc"]/tr/td/a[@id="atoc_next"]');
            if($nextpage && $nextpage->length > 0 && $nextpage->item(0)->hasAttributes()){
                $add_content = $this->load_page("http://www.golem.de".$nextpage->item(0)->attributes->getNamedItem("href")->value);
            }

            // first remove advertisement stuff
            $stuff = $xpath->query('(//script)|(//noscript)|(//div[@class="iqadcenter"])|(//ol[@id="list-jtoc"])|(//table[@id="table-jtoc"])|(//header[@class="cluster-header"]/h1)');

            foreach ($stuff as $removethis) {
                $removethis->parentNode->removeChild($removethis);
            }

            // now get the (cleaned) article
            $entries = $xpath->query('(//article)');

            foreach ($entries as $entry) {
                $basenode = $entry;
                break;
            }

            if ($basenode) {
                return $doc->saveXML($basenode) . $add_content;
            }
            else return false;
        }
    }

    function hook_article_filter($article) {
        if (strpos($article["guid"], "golem.de") !== FALSE) {
            if( ($content = $this->load_page($article["link"])) != FALSE) {
                $article["content"] = $content;
            }

        }
        return $article;
    }
}
?>
