<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Ascend,Descend Total');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Ascend,Descend Total');


$I->wantTo('Click Ascending');
$I->click('a[href="?filter[transaction_active]=1&filter[transaction_status]=&date[start_date]=&date[end_date]=&q[]=&order[transaction_total]=ASC"]');
$I->wait(2);
$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_active%5D=1&filter%5Btransaction_status%5D=&date%5Bstart_date%5D=&date%5Bend_date%5D=&q%5B%5D=&order%5Btransaction_total%5D=ASC');
// $I->seeInCurrentUrl('/control/transaction/search');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[transaction_active]=1&filter[transaction_status]=&date[start_date]=&date[end_date]=&q[]=&order[transaction_total]=DESC"]');
$I->wait(2);
$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_active%5D=1&filter%5Btransaction_status%5D=&date%5Bstart_date%5D=&date%5Bend_date%5D=&q%5B%5D=&order%5Btransaction_total%5D=DESC');
// $I->seeInCurrentUrl('/control/transaction/search?q[]=&order[transaction_total]=DESC');
