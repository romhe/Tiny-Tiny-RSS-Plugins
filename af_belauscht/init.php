<?php
class Af_Belauscht extends Plugin {

	private $host;

	function about() {
		return array(1.0,
			"Load complete belauscht.de article into feed",
			"Joschasa");
	}

	function api_version() {
		return 2;
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "belauscht.de") !== FALSE) {
			if (strpos($article["plugin_data"], "belauscht,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);


                    // remove category stuff
                    $stuff = $xpath->query('(//div[@class="category_link"])');
                    foreach ($stuff as $removethis) {
                        $removethis->parentNode->removeChild($removethis);
                    }


					$entries = $xpath->query('(//div[@class="entry-content"])');

					foreach ($entries as $entry) {
						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "belauscht,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}
}
?>
