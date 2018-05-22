<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Post Poster or Seeker');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/post/search');
$I->amGoingTo('Filter Post Poster or Seeker');

$I->wantTo('Click Poster');
$I->click(['xpath' => '//tr/td/a[@href="?filter[post_type]=poster"]']);
$I->seeInCurrentUrl('/control/post/search?filter%5Bpost_type%5D=poster');

$I->amOnPage('/control/post/search');

$I->wantTo('Click Seeker');
$I->click(['xpath' => '//tr/td/a[@href="?filter[post_type]=seeker"]']);
$I->seeInCurrentUrl('control/post/search?filter%5Bpost_type%5D=seeker');
