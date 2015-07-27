<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 panjun.liu <http://176code.com lpj163@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class APIModel {

	/**
	 * http multi call
	 */
	public function HttpMuticall($data)
	{
		$datas = json_decode($data);
		$url_all = unserialize($datas->urls);
		$cores = $edges = array();
		$calls = new Swoole\Client\Multi();
		if (!empty($datas)) {
			if (isset($datas->cores)) {
				foreach ($datas->cores as $cip => $cname) {
					$url = "http://" . $cip . "/_api_url";
					$cname = new Swoole\Client\HTTP($url);
		      $send_data = json_encode(array(
		        'type' => 'url',
		        'data' => array_values($url_all),
		        //'globalhitlog' => 1,
		      ));
		      $headers = array(
	          'Content-Type' => 'application/json',
	          'User-Agent' => 'Google (https://www.google.com)'
		      );

					$calls->request($cname->post($url, $send_data, $headers));
				}				
			}
		}
    
    /*$qq = new Swoole\Client\HTTP("http://www.qq.com/");
    $baidu = new Swoole\Client\HTTP("https://www.baidu.com/");

    $calls ->request($qq->get("http://www.qq.com/"));
    $calls ->request($baidu->get("https://www.baidu.com/"));*/
    $this->queueLogTimer(__LINE__ . date('Y-m-d H:i:s') . '  ' . print_r($calls, true) . ' queue');

    yield $calls;
	}

	/**
	 * redis set
	 */
	public function redisSet($key, $value) {
		$redis = new Swoole\Client\Redis();

		$ret = $redis->set($key, $value);
		
		yield $ret;
	}

	public function queueLogTimer($msg)
	{
  	error_log($msg . PHP_EOL, 3, '/tmp/queueLogTimer.log');
	}	

}