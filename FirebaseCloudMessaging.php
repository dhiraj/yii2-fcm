<?php

namespace traversient\yii;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\helpers\VarDumper;
use yii\httpclient\Client;
use yii\web\ServerErrorHttpException;

/**
 * Cloud Messaging components that provides easy-to-use mechanism to send messages from your Yii2 code through to FCM
 */
class FirebaseCloudMessaging extends Component
{

    protected $myConfig;
    protected $apiClient;
    protected $api_key;
    protected $project_id;

    public function __construct(array $config)
    {
        parent::__construct();
        $this->myConfig = $config;
        $this->apiClient = new Client([
            'transport' => 'yii\httpclient\CurlTransport',
            'baseUrl' => 'https://fcm.googleapis.com',

            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);

        if (empty($this->myConfig)){
            throw new InvalidCallException("Config must be passed!");
        }
        if (empty($this->myConfig['api_key'])){
            throw new InvalidCallException("api_key must be present in config array!");
        }
        if (empty($this->myConfig['project_id'])){
            throw new InvalidCallException("project_id must be present in config array!");
        }
    }

    public function sendMessage(string $to,array $notificationPayload = null, array $dataPayload = null){
        $fullPayload = [];
        if (empty($to)){
            throw new ServerErrorHttpException("A recipient MUST be provided as registration token id OR full topic path OR notification key");
        }
        $fullPayload["to"] = $to;
        if (empty($notificationPayload) && empty($dataPayload)){
            throw new ServerErrorHttpException("At least one of notificationPayload or dataPayload MUST be provided")
        }
//        $fullPayload[]
//        $response_accesstoken = $this->apiClient->post("/fcm",[])->send();
//        if (! $response_accesstoken->getIsOk()){
//            $imploded = VarDumper::dumpAsString($response_accesstoken->getData());
//            throw new ServerErrorHttpException("Did not get ok response, data: {$imploded}");
//        }
//        $responseData = $response_accesstoken->getData();
//        return $responseData;
    }
}
