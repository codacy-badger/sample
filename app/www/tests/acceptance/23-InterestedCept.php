<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Interested');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/likes/search');

// $I->click('.page-profile-post-search li.menu-item a.interested');
// $I->wait(5);
// $I->click('.page-profile-post-search div#match-likes-restrict div.modal-footer a');