<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Utm\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Utm SQL Service
 *
 * @vendor   Acme
 * @package  utm
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'utm';

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
            ->setUtmCreated(date('Y-m-d H:i:s'))
            ->setUtmUpdated(date('Y-m-d H:i:s'))
            ->save('utm')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *mixed $id
     *
     * @return array
     */
    public function get($data)
    {
        $search = $this->resource->search('utm');

        // if it is an id
        if (is_numeric($data)) {
            $search->filterByUtmId($data);
        }

        if (isset($data['utm_source']) &&
            isset($data['utm_medium']) &&
            isset($data['utm_campaign'])) {
            $search->filterByUtmSource($data['utm_source'])
                ->filterByUtmMedium($data['utm_medium'])
                ->filterByUtmCampaign($data['utm_campaign']);
        }

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
            ->setUtmId($id)
            ->remove('utm');
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

        if (!isset($filter['utm_active'])) {
            $filter['utm_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['utm_active'] == -1) {
            unset($filter['utm_active']);
        }

        if (isset($data['export'])) {
            $range = 0;
        }

        $search = $this->resource
            ->search('utm')
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
                $where[] = 'LOWER(utm_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(utm_source) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(utm_medium) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(utm_campaign) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(utm_detail) LIKE %s';
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

        foreach($rows as $i => $results) {

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
            ->setUtmUpdated(date('Y-m-d H:i:s'))
            ->save('utm')
            ->get();
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function bulkAction(array $ids, $value, $field = 'active')
    {
        //please rely on SQL CASCADING ON DELETE
        $fields = ['utm_'.$field => $value];
        $filter = ['utm_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('utm', $fields, $filter);
    }
}
