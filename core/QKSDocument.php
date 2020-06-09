<?php

require_once('QKSConfig.php');

class QKSDocument{
    private $type='';
    private $tagCashierName = '';
    private $tagCashierInn = '';
    private $sno = '';
    private $tagClientTitle = '';
    private $tagClientInn = '';
    private $tagAgentType = 0;
    private $tagClientContact = '';
    private $isPrintable = true;
    private $items = array();
    private $amountCash=0;
    private $amountElectronic=0;
    private $amountPrepaid=0;
    private $amountCredit=0;
    private $amountIncoming=0;
    private $text=array();


    public function clearDocument(){
        $this->type = '';
        $this->sno = '';
        $this->tagClientContact='';
        $this->tagClientInn='';
        $this->tagClientTitle='';
        $this->tagAgentType=0;
        unset( $this->items );     $this->items = array();
        $isPrintable = true;
        $this->amountCash=0;
        $this->amountElectronic=0;
        $this->amountPrepaid=0;
        $this->amountCredit=0;
        $this->amountIncoming=0;

        unset($this->text);  $this->text=array();

    }


    public function setDocumentType( $type ){
        $validDT = array('sale', 'returnSale', 'buy', 'returnBuy', 'correction', 'openSession', 'closeSession',
            'cashIn', 'cashOut', 'xReport', 'print', 'printQR', 'feed', 'cut', 'repeat');
        if( in_array( $type, $validDT ) ){
            $this->type = $type;
        } else {
            $this->type = '';
        }
    }


    public function setCashierName( $cashierName ){
        $this->tagCashierName = $cashierName;
    }

    public function setCashierInn( $cashierInn ){
        $this->tagCashierInn = $cashierInn;
    }

    public function setSNO( $sno ){
        $validSno = array('sno_osn', 'sno_ysn_d', 'sno_ysn_dmr', 'sno_envd', 'sno_esn', 'sno_psn');
        if ( in_array( $sno, $validSno) ) {
            $this->sno = $sno;
        }else{
            $this->sno = '';
        }
    }

    public function setText( $text, $fontNumber, $isBold ){
        $this->text = array('text'=>$text, 'fontNumber'=>$fontNumber,  'font'=>(($isBold===true)?'bold':'normal') );
    }


    public function setTagClientTitle( $title ){
        $this->tagClientTitle = $title;
    }

    public function setTagClientInn( $inn ){
        $this->tagClientInn = $inn;
    }

    public function setAgentType( $type ){
        $this->tagAgentType = $type;
    }


    public function setClientContact( $contact ){
        $this->tagClientContact = $contact;
    }

    // Устанавливает, будет ли распечатываться документ на ККТ, или нет.
    // Так как 54-ФЗ требует, чтобы чек или печатался, или отдавался по почте, то перед
    // отключением печати, требуется чтобы был задан clientContact
    public function setIsPrintable( $isPrintable ){
//        if( empty($this->tagClientContact) ) {
//            $this->isPrintable = true;
//            return;
//        }
        // если контакт указан, то можно печатать, а можно не печатать..
        $this->isPrintable = $isPrintable;

    }



    public function generateDocument_Check( $ar ){
        if( !empty($this->tagCashierName) )     $ar['tagCashierName'] = $this->tagCashierName;
        if( !empty($this->tagCashierInn) )      $ar['tagCashierInn'] = $this->tagCashierInn;
        if( !empty($this->sno) )                $ar['sno'] = $this->sno;
        if( !empty($this->tagClientTitle) )     $ar['tagClientTitle'] = $this->tagClientTitle;
        if( !empty($this->tagClientInn ) )      $ar['tagClientInn'] = $this->tagClientInn;
        if( !empty($this->tagClientContact) )   $ar['tagClientContact'] = $this->tagClientContact;
        if( !empty($this->tagAgentType) )       $ar['tagAgentType'] = $this->tagAgentType;
        if( !empty($this->amountCash) )         $ar['amountCash'] = $this->amountCash;
        if( !empty($this->amountElectronic) )   $ar['amountElectronic'] = $this->amountElectronic;
        if( !empty($this->amountPrepaid) )      $ar['amountPrepaid'] = $this->amountPrepaid;
        if( !empty($this->amountCredit) )       $ar['amountCredit'] = $this->amountCredit;
        if( !empty($this->amountIncoming) )     $ar['amountIncoming'] = $this->amountIncoming;
        $ar['lines'] = $this->items;
        return $ar;
    }


    public function generateDocument_Simple( $ar ){
        if( !empty($this->tagCashierName) ) $ar['tagCashierName'] = $this->tagCashierName;
        if( !empty($this->tagCashierInn) )  $ar['tagCashierInn'] = $this->tagCashierInn;

        if( !empty($this->tagClientTitle) )     $ar['tagClientTitle'] = $this->tagClientTitle;
        if( !empty($this->tagClientInn ) )      $ar['tagClientInn'] = $this->tagClientInn;
        if( !empty($this->tagClientContact) )   $ar['tagClientContact'] = $this->tagClientContact;
        return $ar;
    }


    public function generateDocument_Text( $ar ){
        if( !empty($this->tagCashierName) ) $ar['tagCashierName'] = $this->tagCashierName;
        if( !empty($this->tagCashierInn) )  $ar['tagCashierInn'] = $this->tagCashierInn;

        if( !empty($this->tagClientTitle) )     $ar['tagClientTitle'] = $this->tagClientTitle;
        if( !empty($this->tagClientInn ) )      $ar['tagClientInn'] = $this->tagClientInn;
        if( !empty($this->tagClientContact) )   $ar['tagClientContact'] = $this->tagClientContact;
        return array_merge($ar, $this->text);
    }

    public function generateDocument_TextQR( $ar ){
        if( !empty($this->tagCashierName) ) $ar['tagCashierName'] = $this->tagCashierName;
        if( !empty($this->tagCashierInn) )  $ar['tagCashierInn'] = $this->tagCashierInn;

        if( !empty($this->tagClientTitle) )     $ar['tagClientTitle'] = $this->tagClientTitle;
        if( !empty($this->tagClientInn ) )      $ar['tagClientInn'] = $this->tagClientInn;
        if( !empty($this->tagClientContact) )   $ar['tagClientContact'] = $this->tagClientContact;
        $ar['text'] = $this->text['text'];
        return $ar;
    }

    public function generateDocument_CashInCashOut( $ar ){
        if( !empty($this->tagCashierName) )     $ar['tagCashierName'] = $this->tagCashierName;
        if( !empty($this->tagCashierInn) )      $ar['tagCashierInn'] = $this->tagCashierInn;
        if( !empty($this->amountCash) )         $ar['amountCash'] = $this->amountCash;
        return $ar;
    }


    public function generateDocument(){
        $ar = array("type" => $this->type);
        $ar['isPrintable'] = $this->isPrintable;
        if ( in_array($this->type, array( 'sale', 'returnSale', 'buy', 'returnBuy' ) ) ){
            return $this->generateDocument_Check( $ar );
        }
        if ( in_array($this->type, array( 'openSession', 'closeSession', 'xReport', 'cut', 'repeat', 'feed' ) ) ){
            return $this->generateDocument_Simple( $ar );
        }

        if ( in_array($this->type, array( 'print' ) ) ){
            return $this->generateDocument_Text( $ar );
        }
        if ( in_array($this->type, array( 'printQR' ) ) ){
            return $this->generateDocument_TextQR( $ar );
        }
        if ( in_array($this->type, array( 'cashIn', 'cashOut' ) ) ){
            return $this->generateDocument_CashInCashOut( $ar );
        }


    }



    public function addItemToDocument( $title, $price, $quantity, $tax, $ppr, $psr, $tagGtdNumber, $tagCountry, $tagExcise, $tagAgentType ){
        $ar = array();
        $ar['title'] = $title;
        $ar['price'] = $price;
        $ar['quantity'] = $quantity;
        $ar['total'] = $price * $quantity;

        $valTax = array('vat_none', 'vat_0', 'vat_10', 'vat_20', 'vat_110', 'vat_120');
        if( in_array($tax, $valTax) )
            $ar['tax'] = $tax;

        $valPPR = array('goods', 'excise_goods', 'job', 'service', 'bet_in_game', 'winning_in_game', 'lottery_ticket', 'lottery_winning', 'ipr', 'payment',
            'composite', 'agent_commission', 'other');
        if( in_array($ppr, $valPPR) )
            $ar['ppr'] = $ppr;

        $valPSR = array('prepayment_100', 'prepayment', 'advance', 'full_settle', 'credit', 'credit_transfer', 'credit_payment');
        if( in_array($psr, $valPSR) )
            $ar['psr'] = $psr;

        if( !empty($tagGtdNumber) ){
            $ar['tagGtdNumber']     = $tagGtdNumber;
            $ar['tagCountry']       = $tagCountry;
            $ar['tagExcise']        = $tagExcise;
        }

        if( !empty( $tagAgentType ) )
            $ar['tagAgentType']     = $tagAgentType;



        array_push($this->items, $ar);
    }

    public function closeCheck($amountCash, $amountElectronic, $amountPrepaid, $amountCredit, $amountIncoming){
        if( $amountCash>0.0 ){          $this->amountCash       =   $amountCash; }
        if( $amountElectronic>0 ){      $this->amountElectronic =   $amountElectronic; }
        if( $amountPrepaid>0 ){         $this->amountPrepaid    =   $amountPrepaid; }
        if( $amountCredit>0 ){          $this->amountCredit     =   $amountCredit; }
        if( $amountIncoming>0 ){        $this->amountIncoming   =   $amountIncoming; }
    }

    // устанавливаем сумму для внесения/изъятия денег из денежного ящика
    public function setMoney($amountCash){
        if( $amountCash>0.0 ){          $this->amountCash       =   $amountCash; }
    }

}




