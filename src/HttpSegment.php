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
    	$this->setFault(true);
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

		if($this->exception) {
			$data['cause'] = $this->generateCause();
		}

	    return array_filter($data);
    }

	private function generateCause() {
		$cause = [];
		$cause['working_directory'] = basename($this->exception->getFile());
		$cause['paths'] = [];   // not used in PHP exceptions
		$cause['exceptions'] = $this->mapExceptions();
		return $cause;
	}

	private function mapExceptions() {
		$exception = [];
		$exception['message'] = $this->exception->getMessage();
		$exception['type'] = get_class($this->exception);
    	$exception['stack'] = array_map('mapTrace', $this->exception->getTrace());

    	return [ $exception ];
	}

	private function mapTrace($frame) {
		$trace = [];
		$trace['path'] = $frame->file;
		$trace['line'] = $frame->line;
		$trace['label'] = $frame->function;
		return $trace;
	}

}
