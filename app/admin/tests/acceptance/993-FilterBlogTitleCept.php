<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Blog Title');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/blog/search');
$I->amGoingTo('Filter Blog Title');

$I->wantTo('Click Ascending');
$I->click(['xpath' => '//tr/th/a[@href="?q[]=&order[blog_title]=ASC"]']);
$I->seeInCurrentUrl('/control/blog/search?q%5B%5D=&order%5Bblog_title%5D=ASC');

$I->wantTo('Click Descending');
$I->click(['xpath' => '//tr/th/a[@href="?q[]=&order[blog_title]=DESC"]']);
$I->seeInCurrentUrl('/control/blog/search?q%5B%5D=&order%5Bblog_title%5D=DESC');