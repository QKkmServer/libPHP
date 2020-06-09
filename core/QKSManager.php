<?php

/*
 * Документация по использованию модуля интеграции с кассовым сервисом QKkmServer
 *
 * Первоначальные настройки:
 * 1. необходимо зарегистрировать организацию на сайте lk.*********.ru -- создаётся личный кабинет
 * 2. с сайта *********.ru скачивается для нужной операционной системы пакет программного обеспечения. Эта программа,
 * называющаяся QKkmServer.NODE осуществляет взаимодействие с кассой, исполняя на ней задания, получаемые по каналам управления.
 * 3. в личном кабинете необходимо зарегистрировать ноду (ИД ноды доступен в меню About)
 * 4. после регистрации в меню ККМ станут доступны кассы, обслуживаемые зарегистрированной программой NODE. Для касс
 * необходимо задать параметры работы и активировать их.
 * 5. для работы по API необходимо сгенерировать секретный ключ (меню Ключи API)
 * 6. полученый ключ заносится в конфигурацию QKSConfig::$USERVER_API_KEY
 *
 * Модуль готов к работе.
 *
 * Далее можно посылать задания на любую доступную организации кассу. Главное - знать её идентификатор в системе.
 *
 * При каждом создании задания рекомендуется указывать ИД кассы и данные кассира. Если их не указать, то сервером будут
 * подставлены значения из карточки ККМ.
 *
 * Подробности на сайте qkkmserver.ru
 *
 * */

require_once('QKSCore.php');
class QKSManager {

    var $_core = '';
    function __construct()
    {
        $this->_core = new QKSCore();
//        set_time_limit(300); // если много документов, то кассе нужно какое-то время их исполнить.
    }

    private function core(){ return $this->_core; }


    // СЕКЦИЯ ЗАПРОСОВ
    /** ставит запрос на исполнение документа в очередь
     * @return string
     */
    public function addTaskToServerQueue( ){
        $res = $this->core()->sendRequestToServer(  $this->core()->generateRequest(), $this->core()->compileScriptPath_AddDocToQueue()  );
        return $this->core()->getTaskIdFromServerAnswer( $res );
    }


    /** запрос у сервера информации по заданию.
     * @param string $taskId
     * @return string
     */
    public function requestTaskInfo( $taskId ){
        $js= $this->core()->sendRequestToServer(  '', $this->core()->compileScriptPath_GetDocInfoFromQueue( $taskId)  );
        return json_encode(json_decode($js), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    }


    /** Данная функция показывает, как можно организовать ожидание печати чека.
     * @param string $taskId
     * @param int $reqInterval - интервал опроса в секундах
     * @param int $timeout - в секундах
     * @return int
     */
    public function waitPrinting( $taskId, $reqInterval=3, $timeout=20){
        $time_start = time();
        do{
            //$this->core()->debug('<br/>--------------------------------<br/>');

            $info = $this->requestTaskInfo( $taskId );  // запрашиваем полную информацию по задаче
            $arr = json_decode( $info, true );


            //$this->core()->debug(json_encode(json_decode($info), JSON_PRETTY_PRINT) );

            if( !array_key_exists('code', $arr ) ) return -1;
            if( $arr['code'] != 0 ) return $arr['code'];

            $queueStatus = $arr['object']['queueItem']['status'];  // статус документа в очереди
            $itemStatus =  $arr['object']['resultItem']['result'];// статус исполнения документа в очереди

            if( empty($queueStatus) and  empty($itemStatus) ) return -2;

            $time = time() - $time_start;

            $this->core()->debug( '<i>'.$time.'</i> Статус задания в очереди = <b>' . (isset($queueStatus)?$queueStatus:"нет в очереди (исполнен)")
                . '</b>; статус документа = <b>' . (isset($itemStatus)?$itemStatus:"ожидает исполнения") . '</b><br/>' );

            if($time>$timeout) return -3;

            if( empty($queueStatus) ) return 1;

            sleep($reqInterval);
            //$this->core()->debug('<br/>--------------------------------<br/>');
        }while( 1 );
        return 0;
    }


    // НАСТРОЙКИ КАССЫ И ДОКУМЕНТА
    /** выбор кассы. Касса сохраняется при сбросе. ОБЯЗАТЕЛЬНО ДОЛЖНО БЫТЬ УКАЗАНО!
     * @param $id - Если не указать, то будет ошибка приёма команды со стороны сервера.
     */
    public function setCreItemId( $id ){
        $this->core()->getCreItem()->setCreItemId( $id );
    }

    /** задание кассира. Кассир сохраняется при пересоздании документов. Если не указать - возьмет система из карточки ККМ на сервере.
     * @param $name
     * @param string $inn
     */
    public function setCashier( $name, $inn=''){
        $this->core()->getCreItem()->setCashierName( $name );
        $this->core()->getCreItem()->setCashierInn( $inn );
    }

    /** задаётся система налогообложения. Если не указана, то берётся "дефолтная" из карточки настроек ККМ на сервере
     * @param string $sno - система налогоблажения = 'sno_osn', 'sno_ysn_d', 'sno_ysn_dmr', 'sno_envd', 'sno_esn', 'sno_psn'
     */
    public function setSNO( $sno ){
        $this->core()->getCreItem()->getDocument()->setSNO( $sno );
    }

    /** Задаётся информация о клиенте
     * @param $title
     * @param $inn
     * @param $contact
     */
    public function setClientInfo( $title, $inn, $contact ){
        $this->core()->getCreItem()->getDocument()->setTagClientTitle( $title );
        $this->core()->getCreItem()->getDocument()->setTagClientInn( $inn );
        $this->core()->getCreItem()->getDocument()->setClientContact( $contact );
    }

    /** Должен очистить все временные структуры и подготовить новый фискальный документ
     * @param string $docType 'sale', 'returnSale', 'buy', 'returnBuy', 'correction', 'openSession', 'closeSession', 'cashIn', 'cashOut', 'xReport', 'print', 'feed', 'cut', 'repeat'
     */
    public function createNewDocument( $docType ){
        $this->core()->getCreItem()->clearCreItem();
        $this->core()->getCreItem()->getDocument()->clearDocument();
        $this->core()->getCreItem()->getDocument()->setDocumentType( $docType );
    }



    /** Добавляем товар в чек.
     * @param string $title - Название
     * @param float $price - Цена
     * @param float $quantity - Количество
     * @param string $tax - Налоговая ставка: 'vat_none', 'vat_0', 'vat_10', 'vat_20', 'vat_110', 'vat_120'
     * @param string $ppr - ПризнакПредметаРасчета: 'goods', 'excise_goods', 'job', 'service', 'bet_in_game', 'winning_in_game', 'lottery_ticket', 'lottery_winning', 'ipr', 'payment','composite', 'agent_commission', 'other'
     * @param string $psr - ПризнакСпособаРасчета: 'prepayment_100', 'prepayment', 'advance', 'full_settle', 'credit', 'credit_transfer', 'credit_payment';
     * @param string $tagGtdNumber - №ГрузовойТаможеннойДекларации
     * @param string $tagCountry - СтранаПоГТД
     * @param float $tagExcise - СуммаАкциза
     * @param int $tagAgentType - КодПризнакаАгента
     */
    public function addItemToDocument( $title, $price, $quantity, $tax, $ppr, $psr, $tagGtdNumber='', $tagCountry='', $tagExcise=0.0, $tagAgentType=0 ){
        $this->core()->getCreItem()->getDocument()->addItemToDocument( $title, $price, $quantity, $tax, $ppr, $psr, $tagGtdNumber, $tagCountry, $tagExcise, $tagAgentType );
    }


    /** Закрытие чека с одновременной оплатой.
     * @param $amountCash
     * @param $amountElectronic
     * @param $amountPrepaid
     * @param $amountCredit
     * @param $amountIncoming
     */
    public function closeCheck( $amountCash, $amountElectronic, $amountPrepaid, $amountCredit, $amountIncoming){
        $this->core()->getCreItem()->getDocument()->closeCheck( $amountCash, $amountElectronic, $amountPrepaid, $amountCredit, $amountIncoming);
    }

    /** установить текст для команд печати
     * @param $text
     * @param int $fontNumber
     * @param bool $isBold
     */
    public function setText( $text, $fontNumber=0, $isBold=false){
        $this->core()->getCreItem()->getDocument()->setText( $text, $fontNumber, $isBold );
    }

    /** установить сумму для операции внесения или изъятия денег из ящика (инкассация)
     * @param $amountCash
     */
    public function setCashMoney( $amountCash ){
        $this->core()->getCreItem()->getDocument()->setMoney( $amountCash );
    }

}
