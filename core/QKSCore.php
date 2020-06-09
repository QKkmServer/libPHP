<?php
//require_once('QKSConfig.php');
require_once('QKSCreItem.php');


class QKSCore extends QKSConfig {

    private $creItem;

    public function getCreItem(){
        return $this->creItem;
    }

    function __construct()
    {
        $this->creItem = new QKSCreItem();
    }

    /** отправка запроса на сервер
     * @param $task
     * @param $url
     * @return mixed
     */
    public function sendRequestToServer( $task, $url ){
        // create curl object
        $_curl = curl_init( );
        if( $_curl === FALSE ){
            $this->debug("Error curl_init: " . curl_error( $_curl ));
        }

        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($_curl, CURLOPT_TIMEOUT, 15);

        curl_setopt($_curl, CURLOPT_URL, $url );
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($_curl, CURLOPT_HEADER, 0);
        curl_setopt($_curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=UTF-8",
            "X-Api-Key: " . $this->USERVER_API_KEY
        ) );
        curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($_curl, CURLOPT_FOLLOWLOCATION, true);

        if ( !empty($task) ){
            curl_setopt($_curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($_curl, CURLOPT_POSTFIELDS, $task);
        } else {
            curl_setopt($_curl, CURLOPT_CUSTOMREQUEST, "GET");
        }
        $result = curl_exec( $_curl );

        if( $result === false ){
            $this->debug("cURL error " . curl_error( $_curl ));
        }else{
            //$this->debug(var_dump($result));
        }
        curl_close( $_curl);
//        $this->debug(var_dump($result));
        return $result;
    }

    /** получить код постановки задания в очередь. Если пустая строка - значит ошибка.
     * Если строка не пустая, то это GUID задания в очереди
     * @param $ans
     * @return string
     */
    public function getTaskIdFromServerAnswer( $ans ){

        //echo "<br/><br/><br/>ANS<br/><br/>" . var_dump( $ans );

        $arr = json_decode( $ans, true );

//
        //echo "<br/><br/><br/>ASSOC<br/><br/>" . var_dump( $arr );
            if( !array_key_exists('code', $arr ) ) return '';
            if( $arr['code'] != 0 ) return '';
        return $arr['object']['id'];
    }

    /**
     * @return string
     */
    public function generateRequest(){
        $rq =   ['creItemId' => $this->getCreItem()->getCreItemId(),
            'commands' => [
                $this->getCreItem()->getDocument()->generateDocument()]

        ];
        $json = json_encode($rq, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->debug( $json );
        return $json;
    }

}
