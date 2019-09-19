<?php
namespace wcf\system\background\job;
use wcf\data\unfurl\UnfurlUrl;
use wcf\data\unfurl\UnfurlUrlAction;
use wcf\data\unfurl\UrlAction;
use wcf\util\UnfurlUrlUtil;
use function wcf\functions\exception\logThrowable;

/**
 * Represents a background job to get information for an url.  
 *
 * @author	Joshua Ruesweg
 * @copyright	2016-2019 WCFLabs.de
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UnfurlURLJob extends AbstractBackgroundJob {
	/**
	 * @var UnfurlUrl 
	 */
	private $url;
	
	/**
	 * UnfurlURLJob constructor.
	 *
	 * @param UnfurlUrl $url
	 */
	public function __construct(UnfurlUrl $url) {
		$this->url = $url;
	}
	
	/**
	 * @inheritDoc
	 */
	public function retryAfter() {
		switch ($this->getFailures()) {
			case 1:
				// 5 minutes
				return 5 * 60;
			case 2:
				// 30 minutes
				return 30 * 60;
			case 3:
				// 2 hours
				return 2 * 60 * 60;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function perform() {
		try {
			$url = new UnfurlUrlUtil($this->url->url);
			
			$urlAction = new UnfurlUrlAction([$this->url], 'update', [
				'data' => [
					'title' => $url->getTitle(),
					'description' => $url->getDescription(),
					'status' => 'SUCCESSFUL'
				]
			]);
			$urlAction->executeAction();
		}
		catch (\InvalidArgumentException $e) {
			logThrowable($e);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function onFinalFailure() {
		$urlAction = new UnfurlUrlAction([$this->url], 'update', [
			'data' => [
				'title' => '',
				'description' => '',
				'status' => 'REJECTED'
			]
		]);
		$urlAction->executeAction();
	}
}
