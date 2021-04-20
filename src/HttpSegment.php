<?php

namespace Pkerrigan\Xray;

use Exception;

/**
 *
 * @author Patrick Kerrigan (patrickkerrigan.uk)
 * @since 13/05/2018
 */
class HttpSegment extends RemoteSegment
{
    use HttpTrait;

	/**
	 * @var Exception
	 */
    protected $exception;

    public function setException(Exception $ex) {
    	$this->exception = $ex;
    	return $this;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['http'] = $this->serialiseHttpData();
		$data['cause'] = $this->generateCause();

	    return array_filter($data);
    }

	private function generateCause() {
		if(!$this->exception) {
			return false;
		}

    	$cause = [];
		$cause['working_directory'] = dirname($this->exception->getFile());
		$cause['paths'] = [];   // not used in PHP exceptions
		$cause['exceptions'] = $this->mapExceptions();
		return $cause;
	}

	private function mapExceptions() {
		$exception = [];
		$exception['message'] = $this->exception->getMessage();
		$exception['type'] = get_class($this->exception);

		$exception['stack'] = [];

		foreach($this->exception->getTrace() as $frame) {
			$thisFrame = [
				'path' => $frame['file'],
				'line' => $frame['line'],
				'label' => $frame['function']
			];

			if(isset($frame['class'])) {
				$thisFrame['label'] = "{$frame['class']}::{$thisFrame['label']}";
			}
			$exception['stack'][] = $thisFrame;
		}

    	return [ $exception ];
	}
}
