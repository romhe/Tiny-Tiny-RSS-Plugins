<?php
class Af_UserFriendly extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.0,
			"Load User Friendly comic into feed.",
			"Joschasa");
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "userfriendly.org/cartoons") !== FALSE) {
			if (strpos($article["plugin_data"], "userfriendly,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML('<?xml encoding="UTF-8">'.fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					$entries = $xpath->query('(//img[@alt])');

					foreach ($entries as $entry) {

						if(strpos($entry->getAttribute('alt'), 'Strip for') !== false) {
							$basenode = $entry;
							break;
						}
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "userfriendly,$owner_uid:" . $article["plugin_data"];
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