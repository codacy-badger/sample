<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Filter Blog ID');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');
// redirect page

$I->amOnPage('/control/blog/search');
$I->amGoingTo('Filter Blog ID');

$I->wantTo('Click Ascending');
$I->click(['xpath' => '//tr/th/a[@href="?q[]=&order[blog_id]=ASC"]']);
$I->seeInCurrentUrl('/control/blog/search?q%5B%5D=&order%5Bblog_id%5D=ASC');

$I->wantTo('Click Descending');
$I->click(['xpath' => '//tr/th/a[@href="?q[]=&order[blog_id]=DESC"]']);
$I->seeInCurrentUrl('/control/blog/search?q%5B%5D=&order%5Bblog_id%5D=DESC');