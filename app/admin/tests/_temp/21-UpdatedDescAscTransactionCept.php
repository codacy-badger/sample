<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Ascend,Descend Updated');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/transaction/search');
$I->amGoingTo('Ascend,Descend Updated');


$I->wantTo('Click Ascending');
$I->click('//tr/th/a[@href="?q[]=&order[profile_updated]=ASC"]');
// $I->seeInCurrentUrl('/control/transaction/search?q[]=&order[profile_created]=ASC');
$I->seeInCurrentUrl('/control/transaction/search');

$I->wantTo('Click Descending');
$I->click('//tr/th/a[@href="?q[]=&order[profile_updated]=DESC"]');
// $I->seeInCurrentUrl('/control/transaction/search?q[]=&order[profile_created]=DESC');
$I->seeInCurrentUrl('/control/transaction/search');
