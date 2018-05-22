<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete UTM');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/utm/search');
$I->amGoingTo('Delete UTM');

// submit form
$I->click(['xpath' => '//tr/td/a[@href="/control/utm/remove/2"]']);

$I->seeInCurrentUrl('/control/utm/search');
$I->see('UTM was Removed');


