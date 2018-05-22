<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Oauth\Role\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Role SQL Service
 *
 * @vendor   Acme
 * @package  role
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'role';

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
            ->setRoleCreated(date('Y-m-d H:i:s'))
            ->setRoleUpdated(date('Y-m-d H:i:s'))
            ->save('role')
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
        $search = $this->resource->search('role');

        $search->filterByRoleId($id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        if ($results['role_permissions']) {
            $results['role_permissions'] = json_decode($results['role_permissions'], true);
        } else {
            $results['role_permissions'] = [];
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
            ->setRoleId($id)
            ->remove('role');
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

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['role_active'])) {
            $filter['role_active'] = 1;
        }

        $search = $this->resource
            ->search('role')
            ->setStart($start)
            ->setRange($range);

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
                $where[] = 'LOWER(role_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach ($rows as $i => $results) {
            if ($results['role_permissions']) {
                $rows[$i]['role_permissions'] = json_decode($results['role_permissions'], true);
            } else {
                $rows[$i]['role_permissions'] = [];
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
            ->setRoleUpdated(date('Y-m-d H:i:s'))
            ->save('role')
            ->get();
    }
    /**
     * Links auth
     *
     * @param *int $rolePrimary
     * @param *int $authPrimary
     */
    public function linkAuth($rolePrimary, $authPrimary)
    {
        return $this->resource
            ->model()
            ->setRoleId($rolePrimary)
            ->setAuthId($authPrimary)
            ->insert('role_auth');
    }

    /**
     * Unlinks auth
     *
     * @param *int $rolePrimary
     * @param *int $authPrimary
     */
    public function unlinkAuth($rolePrimary, $authPrimary)
    {
        return $this->resource
            ->model()
            ->setRoleId($rolePrimary)
            ->setAuthId($authPrimary)
            ->remove('role_auth');
    }

    /**
    * Unlinks All auth
    *
    * @param *int $rolePrimary
    * @param *int $authPrimary
    */
    public function unlinkAllAuth($rolePrimary)
    {
        return $this->resource
            ->model()
            ->setRoleId($rolePrimary)
            ->remove('role_auth');
    }

    /**
     * Check auth if have a role
     *
     * @param *int $rolePrimary
     * @param *int $authPrimary
     */
    public function exists($rolePrimary, $authPrimary)
    {
        $search = $this->resource
            ->search('role_auth')
            ->filterByRoleId($rolePrimary)
            ->filterByAuthId($authPrimary);

        return !!$search->getRow();
    }
}
