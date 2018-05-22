<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */
namespace Cradle\Module\Blog;

use Cradle\Module\Blog\Service as BlogService;

use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Validator layer
 *
 * @vendor   Acme
 * @package  blog
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class Validator
{
    /**
     * Returns Create Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getCreateErrors(array $data, array $errors = [])
    { 
        if(!isset($data['blog_type']) || empty($data['blog_type'])) {
            $errors['blog_type'] = 'Type is required';
        }

        // validate by type | "keyword"
        if(isset($data['blog_type']) && $data['blog_type'] == 'keyword') {
            if(!isset($data['blog_title']) || empty($data['blog_title'])) {
                $errors['blog_title'] = 'Title is required';
            }

            if(isset($data['blog_title']) && strlen($data['blog_title']) > 200) {
                $errors['blog_title'] = 'Title must be less than 200 characters';
            }

            //blog_slug        Required
            if (!isset($data['blog_slug']) || empty($data['blog_slug'])) {
                $errors['blog_slug'] = 'Cannot be empty';
            } else if (BlogService::get('sql')->exists($data['blog_slug'])) {
                $errors['blog_slug'] = 'Slug already exists';
            }

            if(!isset($data['blog_article']) || empty($data['blog_article'])) {
                $errors['blog_article'] = 'Article is required';
            }

            if(!isset($data['blog_image']) && empty($data['blog_image'])) {
                $errors['blog_image'] = 'Blog image is required. ';
            }

        // validate by type | "post"
        } else {
            if(!isset($data['blog_title']) || empty($data['blog_title'])) {
                $errors['blog_title'] = 'Title is required';
            }

            if(isset($data['blog_title']) && strlen($data['blog_title']) > 200) {
                $errors['blog_title'] = 'Title must be less than 200 characters';
            }

            //blog_slug        Required
            if (!isset($data['blog_slug']) || empty($data['blog_slug'])) {
                $errors['blog_slug'] = 'Cannot be empty';
            } else if (BlogService::get('sql')->exists($data['blog_slug'])) {
                $errors['blog_slug'] = 'Slug already exists';
            }

            if(!isset($data['blog_article']) || empty($data['blog_article'])) {
                $errors['blog_article'] = 'Article is required';
            }
                    
            if(!isset($data['blog_description']) || empty($data['blog_description'])) {
                $errors['blog_description'] = 'Description is required';
            }

            if(isset($data['blog_description']) && strlen($data['blog_description']) > 200) {
                $errors['blog_description'] = 'Description must be less than 200 characters';
            }
                    
            if(!isset($data['blog_facebook_title']) || empty($data['blog_facebook_title'])) {
                $errors['blog_facebook_title'] = 'Facebook Title is required';
            }
                    
            if(!isset($data['blog_facebook_image']) || empty($data['blog_facebook_image'])) {
                $errors['blog_facebook_image'] = 'Facebook Image is required';
            }
                    
            if(!isset($data['blog_facebook_description']) || empty($data['blog_facebook_description'])) {
                $errors['blog_facebook_description'] = 'Facebook Description is required';
            }
                    
            if(!isset($data['blog_twitter_title']) || empty($data['blog_twitter_title'])) {
                $errors['blog_twitter_title'] = 'Twitter Title is required';
            }
                    
            if(!isset($data['blog_twitter_image']) || empty($data['blog_twitter_image'])) {
                $errors['blog_twitter_image'] = 'Twitter Image is required';
            }
                    
            if(!isset($data['blog_twitter_description']) || empty($data['blog_twitter_description'])) {
                $errors['blog_twitter_description'] = 'Twitter Description is required';
            }

            if(!isset($data['blog_image']) && empty($data['blog_image'])) {
                $errors['blog_image'] = 'Blog image is required. ';
            }

             if(isset($data['blog_author']) && empty($data['blog_author'])) {
                $errors['blog_author'] = 'Blog author is required';
            }

             if(!isset($data['blog_author_image']) && empty($data['blog_author_image'])) {
                $errors['blog_author_image'] = 'Blog author image is required';
            }

            if(isset($data['blog_author_title']) && empty($data['blog_author_title'])) {
                $errors['blog_author_title'] = 'Blog author title is required';
            }
        }

        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Update Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getUpdateErrors(array $data, array $errors = [])
    {
        if(!isset($data['blog_type']) || empty($data['blog_type'])) {
            $errors['blog_type'] = 'Type is required';
        }

        // validate by type | "keyword"
        if(isset($data['blog_type']) && $data['blog_type'] == 'keyword') {
            if(!isset($data['blog_id']) || !is_numeric($data['blog_id'])) {
                $errors['blog_id'] = 'Invalid ID';
            }

            if(isset($data['blog_title']) && strlen($data['blog_title']) > 200) {
                $errors['blog_title'] = 'Title must be less than 200 characters';
            }
            
            if(isset($data['blog_title']) && empty($data['blog_title'])) {
                $errors['blog_title'] = 'Title is required';
            }
                    
            //blog_slug
            if (!isset($data['blog_slug']) || empty($data['blog_slug'])) {
                $errors['blog_slug'] = 'Cannot be empty';
            } 
                    
            if(isset($data['blog_article']) && empty($data['blog_article'])) {
                $errors['blog_article'] = 'Article is required';
            }

            if(!isset($data['blog_image']) && empty($data['blog_image'])) {
                $errors['blog_image'] = 'Blog image is required. ';
            }

        // validate by type | "post"
        } else {

            if(!isset($data['blog_id']) || !is_numeric($data['blog_id'])) {
                $errors['blog_id'] = 'Invalid ID';
            }

            if(isset($data['blog_title']) && strlen($data['blog_title']) > 200) {
                $errors['blog_title'] = 'Title must be less than 200 characters';
            }
            
            if(isset($data['blog_title']) && empty($data['blog_title'])) {
                $errors['blog_title'] = 'Title is required';
            }
                    
            //blog_slug
            if (!isset($data['blog_slug']) || empty($data['blog_slug'])) {
                $errors['blog_slug'] = 'Cannot be empty';
            } 
                    
            if(isset($data['blog_article']) && empty($data['blog_article'])) {
                $errors['blog_article'] = 'Article is required';
            }
                    
            if(isset($data['blog_description']) && empty($data['blog_description'])) {
                $errors['blog_description'] = 'Description is required';
            }

            if(isset($data['blog_description']) && strlen($data['blog_description']) > 200) {
                $errors['blog_description'] = 'Description must be less than 200 characters';
            }
                    
            if(isset($data['blog_facebook_title']) && empty($data['blog_facebook_title'])) {
                $errors['blog_facebook_title'] = 'Facebook Title is required';
            }
                    
            if(isset($data['blog_facebook_image']) && empty($data['blog_facebook_image'])) {
                $errors['blog_facebook_image'] = 'Facebook Image is required';
            }
                    
            if(isset($data['blog_facebook_description']) && empty($data['blog_facebook_description'])) {
                $errors['blog_facebook_description'] = 'Facebook Description is required';
            }
                    
            if(isset($data['blog_twitter_title']) && empty($data['blog_twitter_title'])) {
                $errors['blog_twitter_title'] = 'Twitter Title is required';
            }
                    
            if(isset($data['blog_twitter_image']) && empty($data['blog_twitter_image'])) {
                $errors['blog_twitter_image'] = 'Twitter Image is required';
            }
                    
            if(isset($data['blog_twitter_description']) && empty($data['blog_twitter_description'])) {
                $errors['blog_twitter_description'] = 'Twitter Description is required';
            }

            if(isset($data['blog_image']) && empty($data['blog_image'])) {
                $errors['blog_image'] = 'Blog image is required. ';
            }

            if(isset($data['blog_author']) && empty($data['blog_author'])) {
                $errors['blog_author'] = 'Blog author is required';
            }

            if(isset($data['blog_author_image']) && empty($data['blog_author_image'])) {
                $errors['blog_author_image'] = 'Blog author image is required';
            }

            if(isset($data['blog_author_title']) && empty($data['blog_author_title'])) {
                $errors['blog_author_title'] = 'Blog author title is required';
            }
        }
                
        return self::getOptionalErrors($data, $errors);
    }

    /**
     * Returns Optional Errors
     *
     * @param *array $data
     * @param array  $errors
     *
     * @return array
     */
    public static function getOptionalErrors(array $data, array $errors = [])
    {
        //validations
        
        if (isset($data['blog_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['blog_image'])) {
            $errors['blog_image'] = 'Should be a valid image';
        }

        if (isset($data['blog_facebook_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['blog_facebook_image'])) {
            $errors['blog_facebook_image'] = 'Should be a valid image';
        }

        if (isset($data['blog_twitter_image']) && !preg_match('/(^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?)|(^data:image\/[a-z]+;base64,)/i', $data['blog_twitter_image'])) {
            $errors['blog_twitter_image'] = 'Should be a valid image';
        }

        if (!isset($data['blog_published']) || empty($data['blog_published'])) {
            $errors['blog_published'] = 'Publish Date is required';
        }
                
        return $errors;
    }
}
