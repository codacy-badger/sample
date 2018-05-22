<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Delete a post');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/profile/post/search');
$I->click(['xpath'=>'//a[@data-link="/post/remove/8?redirect_uri=%2Fprofile%2Fpost%2Fsearch"]']);
$I->wait(5);
$I->click('.page-profile-post-search .confirm-modal .modal-footer a.btn.btn-primary','#confirm-modal');
$I->seeInCurrentUrl('/profile/post/search');
// $I->wait(2);
// $I->see('Post was Removed');

