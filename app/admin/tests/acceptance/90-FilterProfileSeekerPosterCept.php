<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Profile Poster or Seeker');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/profile/search');
$I->amGoingTo('Filter Profile Poster or Seeker');

$I->wantTo('Click Poster');
$I->click(['xpath' => '//tr/td/a[@href="?filter[type]=poster"]']);
$I->seeInCurrentUrl('/control/profile/search?filter%5Btype%5D=poster');

$I->amOnPage('/control/profile/search');

$I->wantTo('Click Seeker');
$I->click(['xpath' => '//tr/td/a[@href="?filter[type]=seeker"]']);
$I->seeInCurrentUrl('/control/profile/search?filter%5Btype%5D=seeker');
