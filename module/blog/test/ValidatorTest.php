<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Cradle\Module\Blog\Validator;

/**
 * Validator layer test
 *
 * @vendor   Acme
 * @package  Blog
 * @author   John Doe <john@acme.com>
 */
class Cradle_Module_Blog_ValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Cradle\Module\Blog\Validator::getCreateErrors
     */
    public function testGetCreateErrors()
    {
        $actual = Validator::getCreateErrors([]);
        $this->assertEquals('Title is required', $actual['blog_title']);
        $this->assertEquals('Slug is required', $actual['blog_slug']);
        $this->assertEquals('Article is required', $actual['blog_article']);
        $this->assertEquals('Description is required', $actual['blog_description']);
        $this->assertEquals('Facebook Title is required', $actual['blog_facebook_title']);
        $this->assertEquals('Facebook Image is required', $actual['blog_facebook_image']);
        $this->assertEquals('Facebook Description is required', $actual['blog_facebook_description']);
        $this->assertEquals('Twitter Title is required', $actual['blog_twitter_title']);
        $this->assertEquals('Twitter Image is required', $actual['blog_twitter_image']);
        $this->assertEquals('Twitter Description is required', $actual['blog_twitter_description']);
    }

    /**
     * @covers Cradle\Module\Blog\Validator::getUpdateErrors
     */
    public function testGetUpdateErrors()
    {
        $actual = Validator::getUpdateErrors([]);

        $this->assertEquals('Invalid ID', $actual['blog_id']);
    }
}
