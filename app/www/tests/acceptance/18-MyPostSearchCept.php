<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Search a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page
$I->amOnPage('/profile/post/search');

$I->fillField('//input[@placeholder="Search"]','IT And Software');
$I->click('.page-profile-post-search form.form-inline button.btn.btn-default');

$I->seeInCurrentUrl('/profile/post/search?filter%5Bpost_active%5D=1&post_expires=&q=IT+And+Software');
$I->see('IT and Software');
$I->see('Metro Manila');