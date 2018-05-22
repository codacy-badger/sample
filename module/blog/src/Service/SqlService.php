<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Blog\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Blog SQL Service
 *
 * @vendor   Acme
 * @package  blog
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'blog';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        return $this->resource
            ->model($data)
            ->setBlogCreated(date('Y-m-d H:i:s'))
            ->setBlogUpdated(date('Y-m-d H:i:s'))
            ->save('blog')
            ->get();
    }

    /**
     * Checks to see if the blog slug already exists
     *
     * @param *string      $slug
     * @param string|false $password
     *
     * @return bool
     */
    public function exists($slug)
    {
        $search = $this->resource
            ->search('blog')
            ->filterByBlogSlug($slug);

        return !!$search->getRow();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('blog');

        $search->innerJoinUsing('blog_profile', 'blog_id');
        $search->innerJoinUsing('profile', 'profile_id');

        if (is_numeric($id)) {
            $search->filterByBlogId($id);
        } else {
            $search->filterByBlogSlug($id);
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['blog_keywords']) {
            $results['blog_keywords'] = json_decode($results['blog_keywords'], true);
        } else {
            $results['blog_keywords'] = [];
        }

        return $results;
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setBlogId($id)
            ->remove('blog');
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['blog_active'])) {
            $filter['blog_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['blog_active'] == -1) {
            unset($filter['blog_active']);
        }

        $search = $this->resource
            ->search('blog')
            ->setStart($start)
            ->setRange($range);

        //join profile
        $search->innerJoinUsing('blog_profile', 'blog_id');
        $search->innerJoinUsing('profile', 'profile_id');


        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(blog_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(blog_description) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(blog_article) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //blog_published
        if (!isset($data['blog_published'])) {
             $search->addFilter('blog_published <= %s', date(
                    'Y-m-d H:i:s'
                ));
        }

        //blog_keywords
        if (isset($data['blog_keywords']) && !empty($data['blog_keywords'])) {
            if (!is_array($data['blog_keywords'])) {
                $data['blog_keywords'] = [$data['blog_keywords']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['blog_keywords'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(blog_keywords), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';
                $where[] = "JSON_SEARCH(LOWER(blog_keywords), 'one', %s) IS NOT NULL";
                $or[] = '%all%';
                $where[] = "JSON_SEARCH(LOWER(blog_tags), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';
            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            // Default encasement
            $encase = 'TRIM(LOWER(%s))';

            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false) :
                    break;

                default :
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }


        $rows = $search->getRows();

        foreach($rows as $i => $results) {

            if($results['blog_keywords']) {
                $rows[$i]['blog_keywords'] = json_decode($results['blog_keywords'], true);
            } else {
                $rows[$i]['blog_keywords'] = [];
            }

        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        return $this->resource
            ->model($data)
            ->setBlogUpdated(date('Y-m-d H:i:s'))
            ->save('blog')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $blogPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($blogPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setBlogId($blogPrimary)
            ->setProfileId($profilePrimary)
            ->insert('blog_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $blogPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($blogPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setBlogId($blogPrimary)
            ->setProfileId($profilePrimary)
            ->remove('blog_profile');
    }
}
