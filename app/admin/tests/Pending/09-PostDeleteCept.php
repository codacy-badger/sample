<?php //-->
use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);

$I->wantTo('Delete Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/post/search');
$I->expectTo('see post list');
$I->see('Posts');

$I->amGoingTo('delete post.');
$I->expect('Delete Post');

$I->amGoingTo('search profile');
$I->fillField('q[]', 'Post Updated');
$I->click('form button');

//remove post
$I->click('.text-danger.remove');

$I->expect('Post Deleted');
$I->see('Post was Removed');