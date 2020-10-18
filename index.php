<?php
require_once('core/QKSManager.php');

/*
 * В данном файла собраны примеры, демонстрирующие работу кассового сервера по формированию различных чеков.
 * В браузер выводится информация запросов и ответов в формате OpenAPI JSON.
 * Если Вам необходимо, к примеру, реализовать свою интеграцию, или задействовать альтернативные каналы управления
 * в QKkmServer.NODE (управление очередью через ваш сервер или управление посредством файлов-заданий на печать), то
 * генерируемые JSON это и есть управляющие структуры, которые надо поместить в выбранный канал.
 * */

set_time_limit(300); // если много документов, то кассе нужно какое-то время их исполнить.

// Первым делом, всегда создается объект кассового менеджера, указывается ИД кассы и данные кассира.
$api = new QKSManager();
$api->setCreItemId("xxxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx");  // !!! ОБЯЗАТЕЛЬНО УКАЖИТЕ СВОЙ ИД КАССЫ !!!    Так же в core/QKSConfig.php надо указать свой ключ для работы с АПИ!

// Раскомментируйте нужные примеры.

//define('openSession','Пример открытия смены');
//define('sale1','Продажа. 2 товара. Разные СНО.');
//define('sale2','Предоплата за товар');
//define('returnSale1','Возврат с учетом предоплаты');
//define('xReport','Промежуточный отчет без гашения.');
//define('print1','Печать текста, в том числе многострочного. Нормальный шрифт.');
//define('print2','Печать текста, в том числе многострочного. Жирный шрифт.'); // работает не на всех ККМ
//define('print3','Печать текста, в том числе многострочного. Номерной шрифт.'); // работает не на всех ККМ
define('feed', 'Протяжка ленты');
//define('printQR','Печать QR-кода');
//define('cut', 'Обрезка, если есть обрезчик');
//define('cashIn', 'Внесение размена в денежный ящик');
//define('cashOut', 'Инкассация (забор наличных денег из денежного ящика)');
//define('closeSession','Пример закрытия смены (Отчет с гашением, Z-отчет)');

function runAndWait(){
    global $api;
    $taskId = $api->addTaskToServerQueue(); // 56c7df60-7d87-433b-b89a-8933b454e8ed
    echo '<b>Заданию присвоен идентификатор в очереди:</b>  ' . $taskId;
    echo '<br> Информация о задании<br/><pre>' . $api->requestTaskInfo($taskId) . '</pre>';
    echo '<br>Ждём окончания исполнения команды на ККМ.<br>';
    $api->waitPrinting($taskId);
    echo '<br>Задание исполнено (или вышел таймаут)<br><pre>' . $api->requestTaskInfo($taskId) . '</pre>';
}

if( defined('openSession')) {
    echo "<h1>".openSession."</h1>";
    $api->createNewDocument("openSession");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    runAndWait();
}

if( defined('cashIn')) {

    echo "<h1>".cashIn."</h1>";
    $api->createNewDocument("cashIn");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->setCashMoney( 500.52 );
    runAndWait();
}


// Добавляем товар в чек.
// Название, Цена, Количество, Налоговая ставка, ПризнакПредметаРасчета, ПризнакСпособаРасчета, №ГрузовойТаможеннойДекларации, СтранаПоГТД, СуммаАкциза, КодПризнакаАгента
//    TAX:  'vat_none', 'vat_0', 'vat_10', 'vat_20', 'vat_110', 'vat_120'
//    PPR:  'goods', 'excise_goods', 'job', 'service', 'bet_in_game', 'winning_in_game', 'lottery_ticket', 'lottery_winning', 'ipr', 'payment','composite', 'agent_commission', 'other'
//    PSR:  'prepayment_100', 'prepayment', 'advance', 'full_settle', 'credit', 'credit_transfer', 'credit_payment';

if( defined('sale1')) {
    echo "<h1>".sale1."</h1>";
    $api->createNewDocument("sale");
    $api->setSNO('sno_envd');
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->addItemToDocument('Лотерейный билет "Миг удачи"', '12.34', '2.0', 'vat_20', 'lottery_ticket',  'full_settle');
    $api->addItemToDocument('Услуга доставки билета', '50.01', '2', 'vat_0', 'service',  'full_settle');
    $api->closeCheck( 500.0,0,0,0,0 );

    runAndWait();
}


if( defined('sale2')) {
    echo "<h1>".sale2."</h1>";
    echo "Чек на предоплату, 500 руб, наличными.  Сам товар стоит 10 тыс. рублей";
    $api->createNewDocument("sale");
    $api->setSNO('sno_envd');
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');
    $api->addItemToDocument('предоплата за Пылесос"', '500', '1.0', 'vat_20', 'payment',  'prepayment');
    $api->closeCheck( 500.0,0,0,0,0 );
    runAndWait();

    echo "Чек на завершение оплаты при получении товара. Доблата 9500 карта + 500 руб зачет предоплаты.  Сам товар стоит 10 тыс. рублей";
    $api->createNewDocument("sale");
    $api->setSNO('sno_envd');
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');
    $api->addItemToDocument('Пылесос"', '10000.0', '1.0', 'vat_20', 'goods',  'full_settle');
    $api->closeCheck( 0.0,9500.0,500.0,0,0 );
    runAndWait();
}


if( defined('returnSale1')) {
    echo "<h1>".sale2."</h1>";
    echo "Чек на предоплату, 500 руб, наличными.  Сам товар стоит 10 тыс. рублей";
    $api->createNewDocument("returnSale");
    $api->setSNO('sno_envd');
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');
    $api->addItemToDocument('предоплата за Пылесос"', '500', '1.0', 'vat_20', 'payment',  'prepayment');
    $api->closeCheck( 500.0,0,0,0,0 );
    runAndWait();

    echo "Чек на завершение оплаты при получении товара. Доблата 9500 карта + 500 руб зачет предоплаты.  Сам товар стоит 10 тыс. рублей";
    $api->createNewDocument("returnSale");
    $api->setSNO('sno_envd');
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');
    $api->addItemToDocument('Пылесос"', '10000.0', '1.0', 'vat_20', 'goods',  'full_settle');
    $api->closeCheck( 0.0,9500.0,500.0,0,0 );
    runAndWait();
}


if( defined('xReport')) {
    echo "<h1>".xReport."</h1>";
    $api->createNewDocument("xReport");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    runAndWait();
}

if( defined('cut')) {
    echo "<h1>".cut."</h1>";
    $api->createNewDocument("cut");
    runAndWait();
}

if( defined('feed')) {
    echo "<h1>".feed."</h1>";
    $api->createNewDocument("feed");
    runAndWait();
}

if( defined('print1')) {
    echo "<h1>".print1."</h1>";
    $api->createNewDocument("print");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->setText(
        'Жил был стрелец, удалой молодец.
Служба у Федота - рыбалка да охота.
Царю: дичь, да рыба;
Федоту - спасибо...');
    runAndWait();
}

if( defined('print2')) {
    echo "<h1>".print2."</h1>";
    $api->createNewDocument("print");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->setText(
        'Жил был стрелец, удалой молодец.
Служба у Федота - рыбалка да охота.
Царю: дичь, да рыба;
Федоту - спасибо...',0,true);
    runAndWait();
}

if( defined('print3')) {
    echo "<h1>".print3."</h1>";
    $api->createNewDocument("print");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->setText(
        'Жил был стрелец, удалой молодец.
Служба у Федота - рыбалка да охота.
Царю: дичь, да рыба;
Федоту - спасибо...',2, false);
    runAndWait();
}

if( defined('printQR')) {
    echo "<h1>".printQR."</h1>";
    $api->createNewDocument("printQR");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->setText(
        'Жил был стрелец, удалой молодец.
Служба у Федота - рыбалка да охота.
Царю: дичь, да рыба;
Федоту - спасибо...');
    runAndWait();
}


if( defined('cashOut')) {

    echo "<h1>".cashOut."</h1>";
    $api->createNewDocument("cashOut");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');

    $api->setCashMoney( 500.52 );
    runAndWait();
}


if( defined('closeSession')) {
    echo "<h1>".closeSession."</h1>";
    $api->createNewDocument("closeSession");
    $api->setCashier("Кассир Талалихин В.В.", "123456789047");
    $api->setClientInfo('ООО Рога и копыта', '123456789047', 'cl@mail.ru');
    runAndWait();
}
