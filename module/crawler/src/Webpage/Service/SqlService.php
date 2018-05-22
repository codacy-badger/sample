<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Crawler\Webpage\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Webpage SQL Service
 *
 * @vendor   Acme
 * @package  webpage
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'webpage';

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
     * Clears the invalid
     *
     * @param *string $root
     */
    public function clearInvalid($root)
    {
        $rows = $this->resource
            ->search('webpage')
            ->filterByWebpageRoot($root)
            ->filterByWebpageType('INVALID')
            ->getRows();

        foreach ($rows as $row) {
            $this->update([
                'webpage_id' => $row['webpage_id'],
                'webpage_type' => 'detail'
            ]);
        }
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
            ->setWebpageCreated(date('Y-m-d H:i:s'))
            ->setWebpageUpdated(date('Y-m-d H:i:s'))
            ->save('webpage')
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
        $search = $this->resource->search('webpage');

        if (is_numeric($id)) {
            $search->filterByWebpageId($id);
        } else {
            $search->filterByWebpageLink($id);
        }

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        return $results;
    }

    /**
     * Returns how many invalid detail pages given root
     *
     * @param *string $root
     */
    public function getInvalidCount($root)
    {
        return $this->resource
            ->search('webpage')
            ->filterByWebpageRoot($root)
            ->filterByWebpageType('invalid')
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
            ->setWebpageId($id)
            ->remove('webpage');
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
            ->search('webpage')
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
                $where[] = 'LOWER(webpage_root) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(webpage_link) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(webpage_type) LIKE %s';
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
            ->setWebpageUpdated(date('Y-m-d H:i:s'))
            ->save('webpage')
            ->get();
    }
}
