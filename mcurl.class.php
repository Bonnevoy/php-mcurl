<?php
/**
 * A PHP class for doing parallel cURL requests
 *
 * @author     Roland Boon
 */
class Mcurl {
	
	/*
	 * The max connections per second setting
	 * Make sure to set this before adding urls
	 */
    public $max_per_second = 5;
	
	/*
	 * Enable (TRUE) of disable (FALSE) debug logging
	 */ 
	public $debug = FALSE;
	
	/*
	 * Enable GZIP-compression (both client and server need to support this)
	 */
	public $gzip = TRUE;
	
	/*
	 * Used to store curl_multi_init() handles
	 */
	private $handle = array();
	
	/*
	 * Used to store individual curl_init() threads 
	 */
    private $threads = array();
    

    public static function factory()
    {
        return new Mcurl();
    }
    
    public function __construct()
    {
        $this->handle[0] = curl_multi_init();
    }

    public function __destruct()
	{
		/*foreach ($this->threads as $thread)
        {
            curl_multi_remove_handle($this->handle, $thread['handle']);
        }*/
        for ($i=0; $i<count($this->handle); $i++)
        {
            curl_multi_close($this->handle[$i]);
        }
	}
	
	/*
	 * bool add_curl ( string $url = NULL )
	 * Add a URL to perform the cURL on
	 *
	 * Parameters
	 * $url
	 *
	 * Return values
	 * Returns TRUE on success or FALSE on failure
	 */
    public function add_url($url)
    {
        $handle = curl_init($url);
		if ($handle)
		{
        	curl_setopt($handle, CURLOPT_HEADER, false);
        	curl_setopt($handle, CURLOPT_FAILONERROR, false);
        	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        	$this->gzip AND curl_setopt($handle, CURLOPT_ENCODING , 'gzip');
	
			/*
			 * Can the current multi cURL handler hold any more threads?
			 */
			if (count(end($this->threads)) < $this->max_per_second)
			{
				$i = count($this->handle) - 1;
				curl_multi_add_handle($this->handle[$i], $handle);
			}
			/*
			 * Current multi cURL handler passed connections per second limit, create a new one
			 */
			else
			{
				$i = count($this->handle);
				$this->handle[$i] = curl_multi_init();
				curl_multi_add_handle($this->handle[$i], $handle);
			}
			
			/*
			 * Store individual handlers for future reference
			 */
			$this->threads[$i][] = array(
				'url' => $url,
				'handle' => $handle,
			);
			
			$this->_debug('Added to handler #' . $i . ': ' . $url);
		}
		else
		{
			$this->_debug('Failed to add ' . $url);
			return false;
		}
    }
	
	/*
	 * void execute ()
	 * Perform all the requests
	 *
	 * The do while contains the magic of the max connections per second limit
	 * First execute multi cURL handler 1, next iteration start 2, and so on
	 * Each iteration takes 1 second
	 */
    public function execute()
    {
        $s = 1;
        do {
			$active = null;
            for ($i=0; $i<min($s, count($this->handle)); $i++)
			{
				curl_multi_exec($this->handle[$i], $handle_active);
                if ($handle_active > 0)
				{
                    $this->_debug('Running handler #' . $i . ' with ' . count($this->threads[$i]) .' threads');
					$active = $handle_active;
                }
            }
            sleep(1);
            $s++;
        } while ($active > 0);
    }
	
	/*
	 * array get_contents ([ string url = FALSE ])
	 * Retrieve the responses from the cURL requests
	 *
	 * Parameters
	 * $url If provided, only the response of the provided URL will be returned
	 *
	 * Return values
	 * Returns an array with responses in the following format
	 * array (
	 *		[0] => array (
	 *			'url' => 'http://example.com/foo'
	 *			'response' => 'Response of this request
	 *		)
	 *	)
	 */
    public function get_content($url = FALSE)
    {
        $return = array();
        foreach ($this->threads as $thread)
        {
            if ($url AND $thread['url'] == $url)
            {
                $return = curl_multi_getcontent($thread['handle']);
                break;
            }
            else if (!$url)
            {
                $return[] = array(
                    'url' => $thread['url'],
                    'content' => curl_multi_getcontent($thread['handle'])
                );
            }
        }
        return $return;
    }
	
	/*
	 * Internal debug method
	 */
	protected function _debug($message)
	{
		if ($this->debug)
		{
			echo date('H:i:s') . ' - '.$message.PHP_EOL;
			flush();
		}
	}
	
}