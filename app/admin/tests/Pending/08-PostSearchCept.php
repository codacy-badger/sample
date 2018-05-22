<?php //-->
use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);

$I->wantTo('Search Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/post/search');
$I->expectTo('see post list');
$I->see('Posts');

$I->amGoingTo('search post.');
$I->expect('Search Post');

$I->amGoingTo('search profile');
$I->fillField('q[]', 'Post Updated');
$I->click('form button');

$I->expectTo('see the post name');
$I->see('Post Updated');
