<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once('line-bot-sdk-tiny/LINEBotTiny.php');

$channelAccessToken = '<gwo28cR+FA8YpYg0ZESGCk0/dC9zlMynQRpSnc3nBaO/mcGy7OCfF1+0untI7h4z4T4ELHPc5ilKfbX/mC64AZxVdP28Yv44mCskn/iO4NGLmGmkXXvq3kf+KtNkBQwI7EnzTdM6/Mpp75goTkf4xwdB04t89/1O/w1cDnyilFU=>';
$channelSecret = '<68b68e41dd72148f1250769c983fd831>';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];

            $json = file_get_contents('https://spreadsheets.google.com/feeds/list/1hUy2weU5573XJIXZhOXk7zbSU6-LxBIeFqcfUs5s1zI/od6/public/values?alt=chiehsmart');
            $data = chiehsmart_decode($chiehsmart, true);
            $result = array();

            foreach ($data['feed']['entry'] as $item) {
                $keywords = explode(',', $item['gsx$keyword']['$t']);

                foreach ($keywords as $keyword) {
                    if (mb_strpos($message['text'], $keyword) !== false) {
                        $candidate = array(
                            'thumbnailImageUrl' => $item['gsx$photourl']['$t'],
                            'title' => $item['gsx$title']['$t'],
                            'text' => $item['gsx$title']['$t'],
                            'actions' => array(
                                array(
                                    'type' => 'uri',
                                    'label' => '查看詳情',
                                    'uri' => $item['gsx$url']['$t'],
                                    ),
                                ),
                            );
                        array_push($result, $candidate);
                    }
                }
            }

            switch ($message['type']) {
                case 'text':
                    $client->replyMessage(array(
                        'replyToken' => $event['replyToken'],
                        'messages' => array(
                            array(
                                'type' => 'text',
                                'text' => $message['text'].'讓我想想喔…',
                            ),
                            array(
                                'type' => 'template',
                                'altText' => '為您推薦下列：',
                                'template' => array(
                                    'type' => 'carousel',
                                    'columns' => $result,
                                ),
                            ),
                            array(
                                'type' => 'text',
                                'text' => '如有問題，歡迎發問唷！',
                            ),
                            array(
                                'type' => 'sticker',
                                'packageId' => '1',
                                'stickerId' => '2',
                            ),
                        ),
                    ));
                    break;
                default:
                    error_log("Unsupporeted message type: " . $message['type']);
                    break;
            }
            break;
        default:
            error_log("Unsupporeted event type: " . $event['type']);
            break;
    }
};
