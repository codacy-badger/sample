<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create Post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();

$I->expect('Admin Profile Search');
$I->amOnPage('/admin/post/search');
$I->expectTo('see post list');
$I->see('Posts');

$I->amGoingTo('go to add post page.');
$I->click('Create New Post');

$I->expect('Create Post');
$I->seeInCurrentUrl('/admin/post/create');

$I->amGoingTo('add a new post');
$I->fillField('post_name', 'New Post');
$I->fillField('post_email', 'newpost@gmail.com');
$I->fillField('post_phone', '123456');
$I->fillField('post_position', 'New Post Position');
$I->fillField('post_location', 'New Post Location');
$I->fillField('post_experience', '2');
$I->fillField('post_detail', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In ut ornare nulla, et suscipit mauris.');
$I->checkOption('input[value=matches]');
$I->fillField('post_link', 'http://www.acme.com');
$I->click('form button');

$I->expect('Post Created');
$I->see('Post was Created');
