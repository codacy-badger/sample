<?php //-->
use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);

$I->wantTo('Update Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/post/search');
$I->expectTo('see post list');
$I->see('Posts');

$I->amGoingTo('go to update post page.');

$I->expect('Update Post');

$I->amGoingTo('search profile');
$I->fillField('q[]', 'New Post');
$I->click('form button');

$I->click('i.fa.fa-edit');

$I->amGoingTo('update a profile');
$I->fillField('post_name', 'Post Updated');
$I->click('form button');

$I->expect('Post Updated');
$I->see('Post was Updated');
