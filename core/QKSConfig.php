<?php

class QKSConfig {
    var $USERVER_API_HOST   ='https://api.qkkmserver.ru';
    var $USERVER_API_PORT   = '443';
    var $DEBUG_ENABLE       = true;  // включать отладку имеет смысл только для изучения модуля и генерации документации

    // для работы необходимо указать ключ API - генерируется для каждого магазина индивидуально на вкладке Ключи API.
    var $USERVER_API_KEY    = 'QXk***************************Ir';



    var $PARTNER_API_KEY    = ''; // партнерский ключ для функций авторегистраций


    /** вывод отладки если отладка включена
     * @param $msg
     */
    public function debug( $msg ){
        if( $this->DEBUG_ENABLE === true ){
            echo '<pre>';
            echo $msg;
            echo '</pre>';

        }
    }

    /** генерация строки обращения к скрипту для постановки задачи в очередь
     * @return string
     */
    public function compileScriptPath_AddDocToQueue(){
        // https://api.*********.ru/open/api/v1/print-documents
        $r = $this->USERVER_API_HOST  . '/open/api/v1/print-documents';
        $this->debug("<b>API:</b>  " . $r );
        return $r;
    }

    /** генерация строки обращения к скрипту для получения информации о документе в очереди
     * @param $docId
     * @return string
     */
    public function compileScriptPath_GetDocInfoFromQueue( $docId ){
        // https://api.*********.ru/open/api/v1/print-documents/
        $r = $this->USERVER_API_HOST  . '/open/api/v1/print-documents/' . $docId;
        $this->debug("<b>API:</b>  " . $r );
        return $r;
    }


}

