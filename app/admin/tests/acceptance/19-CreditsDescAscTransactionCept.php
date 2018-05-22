<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Ascend,Descend Credits');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Ascend,Descend Credits');


$I->wantTo('Click Ascending');
$I->click('a[href="?filter[transaction_active]=1&filter[transaction_status]=&date[start_date]=&date[end_date]=&q[]=&order[transaction_credits]=ASC"]');
//$I->seeInCurrentUrl('/control/transaction/search?q[]=&order[transaction_credits]=DESC');
$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_active%5D=1&filter%5Btransaction_status%5D=&date%5Bstart_date%5D=&date%5Bend_date%5D=&q%5B%5D=&order%5Btransaction_credits%5D=ASC');

$I->wantTo('Click Descending');
$I->click('a[href="?filter[transaction_active]=1&filter[transaction_status]=&date[start_date]=&date[end_date]=&q[]=&order[transaction_credits]=DESC"]');
//$I->seeInCurrentUrl('/control/transaction/search?q[]=&order[transaction_credits]=DESC');
$I->seeInCurrentUrl('/control/transaction/search?filter%5Btransaction_active%5D=1&filter%5Btransaction_status%5D=&date%5Bstart_date%5D=&date%5Bend_date%5D=&q%5B%5D=&order%5Btransaction_credits%5D=DESC');
