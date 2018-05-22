<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Edit Blog');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/blog/search');
$I->amGoingTo('Edit Blog Details');

$I->click(['xpath' => '//tr/td/a[@href="/control/blog/update/1?redirect_uri=%2Fcontrol%2Fblog%2Fsearch"]']);
$I->seeInCurrentUrl('/control/blog/update/1?redirect_uri=%2Fcontrol%2Fblog%2Fsearch');

$I->see('Updating Blog');

$I->fillField('.page-developer-blog-update input[name="blog_title"]', 'For Test');
$I->attachFile('div[data-name="blog_author_image"] input', 'images.png');
$I->fillField('.page-developer-blog-update input[name="blog_author"]', 'test blog author');
$I->fillField('.page-developer-blog-update input[name="blog_title"]', 'test blog title');
$I->attachFile('div[data-name="blog_image"] input', 'images.png');

$I->click('.page-developer-blog-update button.btn');

$I->wait(2);
$I->see('Blog was Updated');