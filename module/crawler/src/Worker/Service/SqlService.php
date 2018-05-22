<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Crawler\Worker\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Worker SQL Service
 *
 * @vendor   Acme
 * @package  worker
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'worker';

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
     * Removes all workers that are working for over an hour
     *
     * @return array
     */
    public function cleanWorkers()
    {
        $threshold = date('Y-m-d H:i:s', strtotime('-1 hour'));
        return $this->resource->deleteRows('worker', [
            ['worker_updated < %s', $threshold]
        ]);
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
            ->setWorkerCreated(date('Y-m-d H:i:s'))
            ->setWorkerUpdated(date('Y-m-d H:i:s'))
            ->insert('worker')
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
        $search = $this->resource->search('worker');

        $search->addFilter('(worker_id=%s OR worker_link=%s)', $id, $id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        return $results;
    }

    /**
     * Returns how many workers are working on this root
     *
     * @param *string $root
     */
    public function getRootCount($root)
    {
        return $this->resource
            ->search('worker')
            ->filterByWorkerRoot($root)
            ->getTotal();
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
            ->setWorkerId($id)
            ->remove('worker');
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




        $search = $this->resource
            ->search('worker')
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

                $where[] = 'LOWER(worker_status) LIKE %s';
                $where[] = 'LOWER(worker_link) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
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

        foreach ($rows as $i => $results) {
            $rows[$i] = $results;
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
            ->setWorkerUpdated(date('Y-m-d H:i:s'))
            ->update('worker')
            ->get();
    }
}
