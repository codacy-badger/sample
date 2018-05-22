<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Resume\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Resume SQL Service
 *
 * @vendor   Acme
 * @package  resume
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'resume';

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
            ->setResumeCreated(date('Y-m-d H:i:s'))
            ->setResumeUpdated(date('Y-m-d H:i:s'))
            ->save('resume')
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
        $search = $this->resource->search('resume');

        $search->filterByResumeId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
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
            ->setResumeId($id)
            ->remove('resume');
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

        if (!isset($filter['resume_active'])) {
            $filter['resume_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['resume_active'] == -1) {
            unset($filter['resume_active']);
        }

        $search = $this->resource
            ->search('resume')
            ->setStart($start)
            ->setRange($range);

        //link profile
        if (isset($data['profile_id'])) {
            $search = $search
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->innerJoinUsing('profile', 'profile_id')
                ->filterByProfileId($data['profile_id']);
        }

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
                $where[] = 'LOWER(resume_position) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        if ($order) {
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
        } else {
            $search->addSort('resume_created', 'DESC');
        }


        $rows = $search->getRows();

        foreach($rows as $i => $results) {

        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchPost(array $data = [])
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

        if (!isset($filter['resume_active'])) {
            $filter['resume_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['resume_active'] == -1) {
            unset($filter['resume_active']);
        }

        $search = $this->resource
            ->search('resume')
            ->setStart($start)
            ->setRange($range);

        //link profile
        if (isset($data['profile_id'])) {
            $search = $search
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->innerJoinUsing('profile', 'profile_id')
                ->filterByProfileId($data['profile_id']);
        }

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
                $where[] = 'LOWER(resume_position) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
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

        //link profile
        if (isset($data['post_id'])) {
             $post = $this->resource
                ->search('resume')
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->innerJoinUsing('profile', 'profile_id')
                ->leftJoinUsing('post_resume', 'resume_id')
                ->leftJoinUsing('post', 'post_id')
                ->filterByPostId($data['post_id'])
                ->filterByProfileId($data['profile_id'])
                ->getRow();
        }

        //return response format
        return [
            'rows' => $rows,
            'post' => $post,
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
            ->setResumeUpdated(date('Y-m-d H:i:s'))
            ->save('resume')
            ->get();
    }

    /**
     * Add like count
     *
     * @param *int $resume
     * @param *int $profile
     *
     * @return bool
     */
    public function addDownload($resume, $profile)
    {
        if ($this->alreadyDownloaded($resume, $profile)) {
            return false;
        }

        $this->resource
            ->model()
            ->setResumeId($resume)
            ->setProfileId($profile)
            ->insert('resume_downloaded');

        $this->resource->query('UPDATE resume SET '
            . 'resume_download_count = resume_download_count + 1 '
            . 'WHERE resume_id=:bind0bind', [
            ':bind0bind' => $resume
        ]);

        return true;
    }

    /**
     * Already downloaded
     *
     * @param *int $resume
     * @param *int $profile
     *
     * @return bool
     */
    public function alreadyDownloaded($resume, $profile)
    {
        return !!$this->resource
            ->search('resume_downloaded')
            ->filterByResumeId($resume)
            ->filterByProfileId($profile)
            ->getRow();
    }

    /**
     * Links profile
     *
     * @param *int $resumePrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($resumePrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setResumeId($resumePrimary)
            ->setProfileId($profilePrimary)
            ->insert('profile_resume');
    }

    /**
     * Links post
     *
     * @param *int $resumePrimary
     * @param *int $profilePrimary
     */
    public function linkPost($resumePrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setResumeId($resumePrimary)
            ->setPostId($postPrimary)
            ->insert('post_resume');
    }
}
