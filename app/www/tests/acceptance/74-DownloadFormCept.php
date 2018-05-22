<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Download Form');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

//  redirect page
$I->amOnPage('/profile/tracking/post/detail/12');

$I->click('a[href="/profile/tracking/post/form/12/6"]');

$I->click('a[href="/profile/tracking/post/form/12/6?download"]');