<?php //-->
/**
 * This file is part of a Custom Project.
 * (c) 2017-2019 Acme Inc.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Oauth\Auth\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Auth SQL Service
 *
 * @vendor   Acme
 * @package  Auth
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'auth';

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
            ->setAuthToken(md5(uniqid()))
            ->setAuthSecret(md5(uniqid()))
            ->setAuthCreated(date('Y-m-d H:i:s'))
            ->setAuthUpdated(date('Y-m-d H:i:s'))
            ->save('auth')
            ->get();
    }

    /**
     * Checks to see if the slug already exists
     *
     * @param *string      $slug
     * @param string|false $password
     *
     * @return bool
     */
    public function exists($slug, $password = false)
    {
        $search = $this->resource
            ->search('auth')
            ->filterByAuthSlug($slug);

        if ($password) {
            $search->filterByAuthPassword(md5($password));
        }

        return !!$search->getRow();
    }

    /**
     * Get detail from database
     *
     * @param *int|string $id
     *
     * @return array
     */
    public function get($id, $all = false)
    {
        $search = $this->resource
            ->search('auth')
            ->innerJoinUsing('auth_profile', 'auth_id')
            ->innerJoinUsing('profile', 'profile_id');

        if (!$all) {
            $search->setColumns(
                'auth_id',
                'auth_slug',
                'auth_token',
                'auth_google_token',
                'auth_google_refresh_token',
                'auth_permissions',
                'auth_type',
                'auth_active',
                'auth_created',
                'auth_updated',
                'profile.*'
            );
        }

        if (is_numeric($id)) {
            $search->filterByAuthId($id);
        } else {
            $search->filterByAuthSlug($id);
        }

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        if ($results['profile_package']) {
            $results['profile_package'] = json_decode($results['profile_package'], true);
        } else {
            $results['profile_package'] = [];
        }

        //auth_permissions
        if ($results['auth_permissions']) {
            $results['auth_permissions'] = json_decode($results['auth_permissions'], true);
        } else {
            $results['auth_permissions'] = [];
        }

        return $results;
    }

    /**
     * Links product to profile
     *
     * @param *int $authId
     * @param *int $profileId
     */
    public function linkProfile($authId, $profileId)
    {
        return $this->resource
            ->model()
            ->setAuthId($authId)
            ->setProfileId($profileId)
            ->insert('auth_profile');
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
            ->setAuthId($id)
            ->remove('auth');
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

        if (isset($data['q']) && is_array($data['q'])) {
            $keywords = $data['q'];
        }

        if (!isset($filter['auth_active'])) {
            $filter['auth_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['auth_active'] == -1) {
            unset($filter['auth_active']);
        }

        $search = $this->resource
            ->search('auth')
            ->setStart($start)
            ->setRange($range);

        //join profile table
        if (isset($data['profile'])) {
            $search->leftJoinUsing('auth_profile', 'auth_id');
            $search->leftJoinUsing('profile', 'profile_id');
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

                $where[] = 'LOWER(auth_slug) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';

                if (isset($data['profile'])) {
                    $where[] = 'profile_id = %s';
                    $or[]    = $keyword;
                }

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
                case (strpos($sort, '_id') !== false):
                    break;

                default:
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }


        $rows = $search->getRows();
        foreach ($rows as $i => $row) {
            //auth_permissions
            if ($row['auth_permissions']) {
                $rows[$i]['auth_permissions'] = json_decode($row['auth_permissions'], true);
            } else {
                $rows[$i]['auth_permissions'] = [];
            }

            //dont show this
            unset($rows[$i]['auth_password']);
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Unlinks product to profile
     *
     * @param *int $authId
     * @param *int $profileId
     */
    public function unlinkProfile($authId, $profileId)
    {
        return $this->resource
            ->model()
            ->setAuthId($authId)
            ->setProfileId($profileId)
            ->remove('auth_profile');
    }

    /**
     * Search in database
     *
     * @param $data
     *
     * @return array
     */
    public function getAuthProfile($data)
    {
        return $this->resource
            ->search('auth_profile')
            ->filterByProfileId($data['profile_id'])
            ->getRow();
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
            ->setAuthUpdated(date('Y-m-d H:i:s'))
            ->save('auth')
            ->get();
    }

    /**
     * filter variables to be used in gathering chart data
     *
     * @param array $data
     *
     * @return array
     */
    public function chartFilter(array $data)
    {
        //add query filters
        if (isset($data['chartFilter'])) {
            foreach ($data['chartFilter'] as $column => $value) {
                //custom query for custom date filter
                if ($column === 'date' && !empty($value['start'])) {
                    $where[] = sprintf(
                        'profile_created BETWEEN \'%s 00:00:00\' AND \'%s 23:59:59\'',
                        date('Y-m-d', strtotime($value['start'])),
                        date('Y-m-d', strtotime($value['end']))
                    );
                    continue;
                }

                if ($column === 'type' && $value == 'poster') {
                    $where[] = '(profile_company IS NOT NULL AND profile_company != "")';
                    continue;
                }

                if ($column === 'type' && $value == 'seeker') {
                    $where[] = 'profile_company IS NULL';
                    continue;
                }

                $where[] = sprintf('%s = \'%s\'', $column, $value);
            }

            $where = 'WHERE ' . implode(' AND ', $where);
        } else {
            $where = '';
        }

        return $where;
    }

    /**
     * Select total of signups
     *
     * @param array $data
     *
     * @return array
     */
    public function getChartTotalSignup(array $data)
    {
        $where = $this->chartFilter($data);

        //we have a different query if the filter is custom date
        if (isset($data['chartFilter']['date'])) {
            $sql = $this->resource->query(
                'SELECT COUNT(profile_id) as total,
                date_format(profile_created, \'%d\') as day,
                date_format(profile_created, \'%M\') as month,
                YEAR(profile_created) as year FROM profile LEFT JOIN auth_profile '.
                'USING (profile_id) LEFT JOIN auth USING (auth_id) ' . $where .
                ' GROUP BY day, month, year ORDER BY MIN(auth_created)'
            );

            return $sql;
        }

        $sql = $this->resource->query(
            'SELECT COUNT(profile_id) as total,
            date_format(profile_created, \'%M\') as month,
            YEAR(profile_created) as year FROM profile LEFT JOIN auth_profile' .
            ' USING (profile_id) LEFT JOIN auth USING (auth_id) ' . $where .
            ' GROUP BY month, year ORDER BY MIN(auth_created)'
        );

        return $sql;
    }

    /**
     * Join Auth and Profile Search in database
     *
     * @param $data
     *
     * @return array
     */
    public function getAuthProfileDetail($data)
    {
        $search = $this->resource
            ->search('auth')
            ->innerJoinUsing('auth_profile', 'auth_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByProfileId($data['profile_id']);

        $results = $search->getRow();


        if (!$results) {
            return $results;
        }

        if ($results['profile_package']) {
            $results['profile_package'] = json_decode($results['profile_package'], true);
        } else {
            $results['profile_package'] = [];
        }

        //auth_permissions
        if ($results['auth_permissions']) {
            $results['auth_permissions'] = json_decode($results['auth_permissions'], true);
        } else {
            $results['auth_permissions'] = [];
        }

        return $results;
    }

    /**
     * Join Auth and Profile Search in database
     *
     * @param $data
     *
     * @return array
     */
    public function getProfileDetail($data)
    {
        $search = $this->resource
            ->search('profile')
            ->filterByProfileId($data['profile_id']);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        if ($results['profile_package']) {
            $results['profile_package'] = json_decode($results['profile_package'], true);
        } else {
            $results['profile_package'] = [];
        }

        return $results;
    }
}
