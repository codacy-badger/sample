<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter By Profile ID');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/service/search');
$I->amGoingTo('Filter By Service Name');

// filter by profile id
$I->click(['xpath' => '//tr/td/a[@href="?filter[service_name]=A Service"]']);

$I->seeInCurrentUrl('/control/service/search?filter%5Bservice_name%5D=A%20Service');
