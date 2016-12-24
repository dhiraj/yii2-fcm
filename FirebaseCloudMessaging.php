<?php

namespace traversient\yii;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
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
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
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
        $this->api_key = $this->myConfig['api_key'];
        if (empty($this->myConfig['project_id'])){
            throw new InvalidCallException("project_id must be present in config array!");
        }
        $this->project_id = $this->myConfig['project_id'];
    }

    public function sendMessage(string $to,array $notificationPayload = null, array $dataPayload = null){
        if (empty($to)){
            throw new ServerErrorHttpException("A recipient MUST be provided as registration token id OR full topic path OR notification key");
        }
        $fullPayload = ["to" => $to];
        if (empty($notificationPayload) && empty($dataPayload)){
            throw new ServerErrorHttpException("At least one of notificationPayload or dataPayload MUST be provided");
        }
        if (!empty($notificationPayload)){
            $fullPayload["notification"] = $notificationPayload;
        }
        if (!empty($dataPayload)){
            $fullPayload["data"] = $dataPayload;
        }
        $sfullPayload = VarDumper::dumpAsString($fullPayload);
        \Yii::info("Sending message to FCM: $sfullPayload","cron");


        $request = $this->apiClient->createRequest();
        $request->setUrl("https://fcm.googleapis.com/fcm/send")
            ->setMethod("POST")
            ->setData($fullPayload)
            ->setHeaders([
            "Accept"=>"application/json",
            "Content-Type"=>"application/json",
            "Authorization"=>"key=$this->api_key"
        ])
        ;

        $response_send = null;
        try{
            $response_send = $request->send();
        }
        catch (InvalidParamException $exception){
            \Yii::error("Exception caught while trying to send FCM request: $exception","cron");
        }
        if (! $response_send->getIsOk()){
            $imploded = VarDumper::dumpAsString($response_send->getData());
            throw new ServerErrorHttpException("Did not get ok response, data: {$imploded}");
        }
        $responseData = $response_send->getData();
        return $responseData;
    }
}
