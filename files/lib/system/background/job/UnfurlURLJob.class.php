<?php
namespace wcf\system\background\job;
use wcf\data\unfurl\UnfurlUrl;
use wcf\data\unfurl\UnfurlUrlAction;
use wcf\data\unfurl\UrlAction;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
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
			
			if (empty(StringUtil::trim($url->getTitle()))) {
				$urlAction = new UnfurlUrlAction([$this->url], 'update', [
					'data' => [
						'title' => '',
						'description' => '',
						'status' => 'REJECTED'
					]
				]);
				$urlAction->executeAction();
			}
			else {
				$data = [
					'title' => $url->getTitle(),
					'description' => $url->getDescription() ?? '',
					'status' => 'SUCCESSFUL'
				];
				
				if ($url->getImageUrl()) {
					if (MODULE_IMAGE_PROXY || IMAGE_ALLOW_EXTERNAL_SOURCE) {
						$image = UnfurlUrlUtil::downloadImageFromUrl($url->getImageUrl());
						
						if ($image !== null) {
							$imageData = @getimagesizefromstring($image);
							
							// image is squared
							if ($imageData[0] == $imageData[1]) {
								$data['imageUrl'] = $url->getImageUrl();
								$data['imageType'] = 'SQUARED';
							}
							else if ($imageData[0] > 300 && $imageData[1] > 150) {
								$data['imageUrl'] = $url->getImageUrl();
								$data['imageType'] = 'COVER';
							}
							else {
								$data['imageType'] = 'NOIMAGE';
							}
						}
					}
					else {
						$image = UnfurlUrlUtil::downloadImageFromUrl($url->getImageUrl());
						
						if ($image !== null) {
							$imageData = @getimagesizefromstring($image);
							
							// image is squared
							if ($imageData[0] == $imageData[1]) {
								$data['imageType'] = 'SQUARED';
							}
							else if ($imageData[0] > 300 && $imageData[1] > 150) {
								$data['imageType'] = 'COVER';
							}
							
							// check whether the image is to large
							if (max($imageData[0], $imageData[1]) > 1500) {
								$data['imageType'] = 'NOIMAGE';
							}
							
							if (isset($data['imageType'])) {
								switch ($imageData[2]) {
									case IMAGETYPE_PNG:
										$extension = 'png';
										break;
									case IMAGETYPE_GIF:
										$extension = 'gif';
										break;
									case IMAGETYPE_JPEG:
										$extension = 'jpg';
										break;
									default:
										throw new \RuntimeException();
								}
								
								$data['imageHash'] = sha1($image) . '.' . $extension;
								
								$path = WCF_DIR.'images/unfurlUrl/'.substr($data['imageHash'], 0, 2);
								FileUtil::makePath($path);
								
								$fileLocation = $path .'/'.$data['imageHash'];
								
								file_put_contents($fileLocation, $image);
								
								// update mtime for correct expiration calculation
								@touch($fileLocation);
							}
						}
					}
				}
				
				$urlAction = new UnfurlUrlAction([$this->url], 'update', [
					'data' => $data
				]);
				$urlAction->executeAction();
			}
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
