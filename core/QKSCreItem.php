<?php

require_once('QKSConfig.php');
require_once('QKSDocument.php');

class  QKSCreItem extends  QKSConfig {
    // класс содержит настройки ККМ
    private $CREITEM_ID = '';
    private $document;
    function __construct()
    {
        $this->document = new QKSDocument();
        //$this->debug("__construct QKSCreItem");

    }

    public function getDocument(){
        return $this->document;
    }


    // устанавливаем идентификатор кассы, с которой будем работать
    public function setCreItemId( $creItemId ){
        $this->CREITEM_ID = $creItemId;
    }
    public function getCreItemId(){
        return $this->CREITEM_ID;
    }


    public function setCashierName( $name ){
        $this->document->setCashierName( $name );
    }
    public function setCashierInn( $inn ){
        $this->document->setCashierInn( $inn );
    }



    public function clearCreItem(){
        $this->document->setSNO('');
        $this->document->clearDocument();
    }

}