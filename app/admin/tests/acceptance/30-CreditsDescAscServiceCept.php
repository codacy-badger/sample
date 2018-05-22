<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Ascend,Descend Profile');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/service/search');
// $I->amGoingTo('Filter By Service Name');


$I->wantTo('Click Ascending');
$I->click('//tr/th/a[@href="?q[]=&order[service_credits]=ASC"]');
// $I->seeInCurrentUrl('/control/service/search?q[]=&order[service_credits]=ASC');
$I->seeInCurrentUrl('/control/service/search');

$I->wantTo('Click Descending');
// $I->click('//tr/th/a[@href="?q[]=&order[service_credits]=DESC"]');
// // $I->seeInCurrentUrl('/control/service/search?q[]=&order[service_credits]=DESC');
// $I->seeInCurrentUrl('/control/service/search');
