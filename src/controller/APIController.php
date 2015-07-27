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

class APIController extends Controller
{

  public function onRequest($request, $response)
  {
    var_dump($request);
    var_dump($response);
    //$test = new Test($response);
    //$this->scheduler->newTask($test->udptest());
    //$this->scheduler->run();
  }

  /**
   * 压入队列
   */
  public function actionRefreshPush()
  {
    
    // server setting
    // $this->server->setting['redis_host']
    // $this->server->setting['redis_port']
    $response = $this->argv['response'];
    $response->header('Content-Type', 'application/json');
    $response->header('Server', 'VC-SERVER');

    $method = $this->argv['request']['method'];
    if('POST' != strtoupper($method)) {
      $error_message = json_encode(array(
        'code' => 0,
        'message' => 'that should only be accessible via the POST method'
      ));
      $response->end($error_message);
      yield Swoole\Coroutine\SysCall::end('end');
      return;
    }

    // global vars, data is encode
    $data = $this->argv['request']['post']['data'];
    $redis_key = 'refresh_' . md5($data);
    
    $model = new APIModel();
    $res = (yield $model->redisSet($redis_key, $data));
    
    // success 
    if($res == 'OK') {
      $ok_message = json_encode(array(
        'code' => 1,
        'message' => 'success'
      ));

      $this->server->scheduler->newTask($this->RefreshExecute($data));
      $this->server->scheduler->run();
      //$response->end($ok_message);

    }
  }


  public function RefreshExecute($data) {
    $ret = (yield $this->test1($data));    
//var_dump($ret);
    $this->argv['response']->end($ret);
  }

  public function test1($data) {
    $APIModel = new APIModel();
    $ret = '';
    $rets = (yield $APIModel->HttpMuticall($data));
    var_dump($rets);

    yield $ret;   
  }
  
}
