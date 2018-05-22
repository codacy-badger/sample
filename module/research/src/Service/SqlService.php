<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Research\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Research SQL Service
 *
 * @vendor   Acme
 * @package  research
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'research';

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
            ->setResearchCreated(date('Y-m-d H:i:s'))
            ->setResearchUpdated(date('Y-m-d H:i:s'))
            ->save('research')
            ->get();
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
        $search = $this->resource->search('research');

        $search->filterByResearchId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['research_position']) {
            $results['research_position'] = json_decode($results['research_position'], true);
        } else {
            $results['research_position'] = [];
        }

        if($results['research_location']) {
            $results['research_location'] = json_decode($results['research_location'], true);
        } else {
            $results['research_location'] = [];
        }

        return $results;
    }

    /**
     * Get Companies from database
     *
     * @param *array $companies
     *
     * @return array
     */
    public function getCompanies($companies)
    {
        $rows = [];
        foreach ($companies as $key => $value) {
            $post = $this->resource->search('profile')
                ->setColumns(
                    'profile_id',
                    'profile_name',
                    'profile_company',
                    'profile_image'
                )
                ->filterByProfileCompany($value)
                ->getRow();

            if(!empty($post)) {
                $rows[] = $post;
            }

        }

        //return response format
        return [
            'rows' => $rows,
            'total' => count($rows)
        ];
    }

    /**
     * Get Top Companies from database
     *
     * @param *array $companies
     *
     * @return array
     */
    public function getTopCompanies($location)
    {
        // get top companies
        $companies = $this->resource->search('post')
            ->setColumns(
                'COUNT(*) as postings',
                'post_id',
                'profile_name',
                'profile_company',
                'profile_image',
                'profile_slug'
            )
            ->innerJoinUsing('post_profile', 'post_id')
            ->innerJoinUsing('profile', 'profile_id');

        // if location
        if ($location) {
            $or = [];
            $where = [];
            $where[] = 'LOWER(post_location) LIKE %s';
            $or[] = '%' . strtolower($location) . '%';
            $where[] = 'LOWER(post_tags) LIKE %s';
            $or[] = '%' . strtolower($location) . '%';
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$companies, 'addFilter'], ...$or);
        }

        $companies = $companies
            ->setRange(5)
            ->addFilter('post_expires > %s', date(
                'Y-m-d H:i:s',
                strtotime('now')
            ))
            ->filterByPostType('poster')
            ->groupBy('profile_company')
            ->addSort('postings', 'DESC')
            ->getRows();

        //return response format
        return [
            'rows' => $companies,
            'total' => count($companies)
        ];
    }

    /**
     * Get Top Position from database
     *
     * @param *array $companies
     *
     * @return array
     */
    public function getTopPositions($data)
    {
        $rows = [];

        //get posts
        $posts = $this->resource->search('post')
            ->setColumns('DISTINCT(post_position)', 'count(*) as total')
            ->filterByPostType('poster')
            ->filterByPostActive(1)
            ->addSort('total', 'DESC')
            ->addSort('post_salary_max', 'DESC')
            ->addSort('post_position', 'ASC')
            ->groupBy('post_position')
            ->addFilter('post_salary_max IS NOT NULL')
            ->addFilter('post_expires > %s', date(
                'Y-m-d H:i:s',
                 strtotime('now')
             ))
            ->setRange(5);

        // if location
        if (isset($data['location'])) {
            $or = [];
            $where = [];
            $where[] = 'LOWER(post_location) LIKE %s';
            $or[] = '%' . strtolower($data['location']) . '%';
            $where[] = 'LOWER(post_tags) LIKE %s';
            $or[] = '%' . strtolower($data['location']) . '%';
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$posts, 'addFilter'], ...$or);
        }

        $posts = $posts->getRows();

        //return response format
        return [
            'rows' => $posts,
            'total' => count($posts)
        ];
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
            ->setResearchId($id)
            ->remove('research');
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

        if (!isset($filter['research_active'])) {
            $filter['research_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['research_active'] == -1) {
            unset($filter['research_active']);
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        $search = $this->resource
            ->search('research')
            ->setStart($start)
            ->setRange($range);

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //filter by position
        if(isset($data['position']) && !isset($data['location'])) {
            $data['position'] = $this->slugify($data['position']);

            $where[] = "JSON_CONTAINS(research_position, %s)";
            $or[] = '{"slug": "' . $data['position'] . '"}';

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        //filter by location
        } else if(isset($data['location']) && !isset($data['position'])) {
            $data['location'] = $this->slugify($data['location']);

            $where[] = "JSON_CONTAINS(research_location, %s)";
            $or[] = '{"slug": "' . $data['location'] . '"}';

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        //filter by position-location
        } else if(isset($data['position'])
            && isset($data['location'])) {

            $data['position'] = str_replace('-', ' ', trim($data['position']));
            $data['location'] = str_replace('-', ' ', trim($data['location']));
            $where[] = "JSON_CONTAINS(research_position, %s)";
            $or[] = '{"position": "' . $data['position'] . '", "location": "' . $data['location'] . '"}';

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

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];

                $keyword = str_replace(' ', '-', trim(strtolower($keyword)));

                $where[] = "JSON_CONTAINS(LOWER(research_position), %s)";
                $or[] = '{"slug": "' . $keyword . '"}';

                $where[] = "JSON_CONTAINS(LOWER(research_location), %s)";
                $or[] = '{"slug": "' . $keyword . '"}';

                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
            }
        }


        $rows = $search->getRows();

        foreach($rows as $i => $results) {

            if($results['research_position']) {
                $rows[$i]['research_position'] = json_decode($results['research_position'], true);
            } else {
                $rows[$i]['research_position'] = [];
            }

            if($results['research_location']) {
                $rows[$i]['research_location'] = json_decode($results['research_location'], true);
            } else {
                $rows[$i]['research_location'] = [];
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
            ->setResearchUpdated(date('Y-m-d H:i:s'))
            ->save('research')
            ->get();
    }

    /**
     * Slugify a given string
     *
     * @param  string $string
     * @return string
     */
    public function slugify($string)
    {
        $slug = preg_replace("/[^a-zA-Z0-9_\-\s]/i", '', $string);
        $slug = str_replace(' ', '-', trim($slug));
        $slug = preg_replace("/-+/i", '-', $slug);
        $slug = strtolower($slug);
        $slug = substr($slug, 0, 90);
        $slug = str_replace('-', ' ', $slug);
        $slug = ucwords($slug);
        $slug = str_replace(' ', '-', $slug);

        return $slug;
    }
}
