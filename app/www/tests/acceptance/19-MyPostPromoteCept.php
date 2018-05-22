<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Promote a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

// $I->amOnPage('/profile/post/search');
// $I->click('.page-profile-post-search #post-1 .post-booster button');
// $I->wait(5);
// $I->click('.page-profile-post-search #post-1 .dropdown-menu li a[data-action="promote-post"]');
// $I->wait(5);
// $I->see('Boost this Post');
// $I->click('.page-profile-post-search #boost-modal .modal-footer a.boost-button');
// $I->wait(1);
// $I->see('You earned 500 experience points');
// $I->see('Promote Post Success');