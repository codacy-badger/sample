<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create Term Details');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/term/search');
$I->amGoingTo('Create Term Details');

$I->wantTo('Click Create');

$I->click(['xpath' => '//div/span/a[@href="/control/term/create"]']);

$I->seeInCurrentUrl('/control/term/create');

// click submit
$I->click('.page-developer-term-create button.btn-primary');

$I->seeInCurrentUrl('/control/term/create');

$I->wait(2);
$I->see('Invalid Parameters');
