<?php
/*
* Copyright (c) 2017 Baidu.com, Inc. All Rights Reserved
*
* Licensed under the Apache License, Version 2.0 (the "License"); you may not
* use this file except in compliance with the License. You may obtain a copy of
* the License at
*
* Http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
* WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
* License for the specific language governing permissions and limitations under
* the License.
*/

require_once 'lib/AipBase.php';

/**
 * 百度语音
 */
class AipSpeech extends AipBase
{

    /**
     * url
     * @var string
     */
    public $asrUrl = 'http://vop.baidu.com/server_api';

    /**
     * url
     * @var string
     */
    public $ttsUrl = 'http://tsn.baidu.com/text2audio';

    /**
     * @var string
     */
    public $aasrCreateUrl = 'https://aip.baidubce.com/rpc/2.0/aasr/v1/create';

    /**
     * @var string
     */
    public $aasrQueryUrl = 'https://aip.baidubce.com/rpc/2.0/aasr/v1/query';

    /**
     * 判断认证是否有权限
     * @param array $authObj
     * @return boolean
     */
    protected function isPermission($authObj)
    {
        return true;
    }

    /**
     * 处理请求参数
     * @param string $url
     * @param array $params
     * @param array $data
     * @param array $headers
     */
    protected function proccessRequest($url, &$params, &$data, $headers)
    {

        $token = isset($params['access_token']) ? $params['access_token'] : '';

        if (empty($data['cuid'])) {
//            $data['cuid'] = md5($token);
        }

        if ($url === $this->asrUrl) {
            $data['token'] = $token;
            $data = json_encode($data);
        } else {
//            $data['tok'] = $token;
        }

        unset($params['access_token']);
    }

    /**
     * 格式化结果
     * @param $content string
     * @return mixed
     */
    protected function proccessResult($content)
    {
        $obj = json_decode($content, true);

        if ($obj === null) {
            $obj = array(
                '__json_decode_error' => $content
            );
        }

        return $obj;
    }

    /**
     * @param string $speech
     * @param string $format
     * @param int $rate
     * @param array $options
     * @return array
     */
    public function asr($speech, $format, $rate, $options = array())
    {
        $data = array();

        if (!empty($speech)) {
            $data['speech'] = base64_encode($speech);
            $data['len'] = strlen($speech);
        }

        $data['format'] = $format;
        $data['rate'] = $rate;
        $data['channel'] = 1;

        $data = array_merge($data, $options);

        return $this->request($this->asrUrl, $data, array());
    }

    /**
     * doc:https://ai.baidu.com/ai-doc/SPEECH/Jkfhon3y6
     * @param $speech_url
     * @param string $format : "mp3", "wav", "pcm","m4a","amr"
     * @param int $pid :语言类型[80001（中文语音近场识别模型极速版）, 1737（英文模型）]
     * @param int $rate
     * @param int $channel
     * @param array $options
     * @return array
     */
    public function aasr($speech_url, $format, int $pid = 80001
        , int $rate = 16000, $options = array())
    {
        $data = array();

        $data['speech_url'] = $speech_url;
        $data['pid'] = $pid;
        $data['format'] = $format;
        $data['rate'] = $rate;

        $data = array_merge($data, $options);

        $authObj = $this->auth();
        $url = $this->aasrCreateUrl . '?access_token=' . $authObj['access_token'];
        $reponse = $this->client->post($url, json_encode($data));
        return $this->proccessResult($reponse['content']);
    }

    /**
     * doc: https://ai.baidu.com/ai-doc/SPEECH/7kfhp2nr4
     * @param array $task_ids
     * @return array
     */
    public function aasrTask(array $task_ids)
    {
        $data = array();

        $data['task_ids'] = $task_ids;

        $authObj = $this->auth();
        $url = $this->aasrQueryUrl . '?access_token=' . $authObj['access_token'];
        $reponse = $this->client->post($url, json_encode($data));
        return $this->proccessResult($reponse['content']);
    }

    /**
     * @param string $text
     * @param string $lang
     * @param int $ctp
     * @param array $options
     * @return array
     */
    public function synthesis($text, $lang = 'zh', $ctp = 1, $options = array())
    {
        $data = array();

        $data['tex'] = $text;
        $data['lan'] = $lang;
        $data['ctp'] = $ctp;

        $data = array_merge($data, $options);

        $result = $this->request($this->ttsUrl, $data, array());

        if (isset($result['__json_decode_error'])) {
            return $result['__json_decode_error'];
        }

        return $result;
    }

}
