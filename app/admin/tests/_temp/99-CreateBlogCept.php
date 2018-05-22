<?php //-->
use Page\Login as LoginPage;
$I = new AcceptanceTester($scenario);

$I->wantTo('Create Blog');

//Login
$loginPage = new LoginPage($I);
$loginPage->login();


$I->expect('Homepage');
$I->seeInCurrentUrl('/');

// redirect page

$I->amOnPage('/control/blog/search');
$I->amGoingTo('Create Blog Details');

$I->click(['xpath' => '//span/a[@href="/control/blog/create"]']);

$I->seeInCurrentUrl('/control/blog/create');
$I->see('Create Blog');

// fill fields
$I->selectOption('//select[@data-do="blog-type"]', 'Post');
$I->attachFile('div[data-name="blog_author_image"] input', 'images.png');
$I->fillField('.page-developer-blog-create input[name="blog_author"]', 'test blog author');
$I->fillField('.page-developer-blog-create input[name="blog_title"]', 'test blog title');
$I->fillField('.page-developer-blog-create input[name="blog_author_title"]', 'test blog author title');
$I->attachFile('div[data-name="blog_image"] input', 'images.png');
$I->fillField('.page-developer-blog-create input[name="blog_description"]', 'test blog description');
$I->fillField('.page-developer-blog-create input[name="blog_slug"]', 'test blog slug');
// tags
$I->executeJS('$(\'.keyword-field\').append(\'<input type="hidden" name="blog_keywords[]" value="IT" />\')');
$I->executeJS('$(\'.keyword-field\').append(\'<input type="hidden" name="blog_keywords[]" value="Technology" />\')');

$I->executeJS('$(\'iframe.wysihtml5-sandbox\').append(\'<textarea data-do="wysiwyg" name="blog_article" class="form-control" placeholder="Article" style="display: none;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent nulla nunc, pulvinar vel metus eu, ultricies eleifend sem. Quisque sit amet nisl nec sapien pellentesque porta sed ac enim. Nulla sed sagittis risus. Vestibulum lobortis faucibus orci, eu rutrum odio. In tempus sit amet elit imperdiet luctus. Aliquam sodales porttitor feugiat. Donec tellus orci, vulputate sed magna quis, ultrices fringilla nisl.</textarea>\')');

$I->fillField('.page-developer-blog-create input[name="blog_published"]', '30/01/2018');
$I->attachFile('div[data-name="blog_facebook_image"] input', 'images.png');
$I->fillField('.page-developer-blog-create input[name="blog_facebook_title"]', 'txzcxzcech labs');
$I->fillField('.page-developer-blog-create input[name="blog_facebook_description"]', 'tech labs');

$I->attachFile('div[data-name="blog_twitter_image"] input', 'images.png');
$I->fillField('.page-developer-blog-create input[name="blog_twitter_title"]', 'tech labs');
$I->fillField('.page-developer-blog-create input[name="blog_twitter_description"]', 'tech labs');

$I->click('.page-developer-blog-create button.btn');

$I->wait(2);
$I->see('Blog was Created');
