<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Area\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Area SQL Service
 *
 * @vendor   Acme
 * @package  area
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'area';

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
            ->setAreaCreated(date('Y-m-d H:i:s'))
            ->setAreaUpdated(date('Y-m-d H:i:s'))
            ->save('area')
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
        $columns[] = '*';
        $columns[] = 'ST_X(area_location) as area_location_x';
        $columns[] = 'ST_Y(area_location) as area_location_y';
        $columns = implode(', ', $columns);

        $search = $this->resource
            ->search('area')
            ->setColumns($columns)
            ->filterByAreaId($id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        // Checks for locations
        if (isset($results['area_location']) && !empty($results['area_location'])) {
            $results['area_location'] = array('lat' => $results['area_location_x'],
                'lon' => $results['area_location_y']);
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
            ->setAreaId($id)
            ->remove('area');
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
        $columns = [];
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

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


        if (!isset($filter['area_active'])) {
            $filter['area_active'] = 1;
        }
        
        // Checks if active is set to -1
        if ($filter['area_active'] == -1) {
            unset($filter['area_active']);
        }

        // Checks if there are specific columns
        if (isset($data['columns']) && !empty($data['columns'])) {
            // Checks if the columns is not an array
            if (!is_array($data['columns'])) {
                $data['columns'] = [$data['columns']];
            }

            $columns = $data['columns'];
        }

        $columns[] = '*';
        $columns[] = 'ST_X(area_location) as area_location_x';
        $columns[] = 'ST_Y(area_location) as area_location_y';
        $columns = array_unique($columns);
        $columns = implode(', ', $columns);

        $search = $this->resource
            ->search('area')
            ->setColumns($columns)
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
                $where[] = 'LOWER(area_name) LIKE %s';
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


        // check for area locate
        if (isset($data['area_locate'])) {
            // check if area has city
            if (preg_match('/city/i', $data['area_locate'])) {
                // remove the city for better search
                $data['area_locate'] = trim(preg_replace('/city/i', '', $data['area_locate']));
            }

            // search for location
            $search->setColumns('area_name');
            $search->filterByAreaType('city');
            $search->setRange(1);

            // search first for exact location
            $search->addFilter('area_name = %s', $data['area_locate']);

            // if no results try using like
            if ($search->getTotal() == '0') {
                $search->addFilter('area_name LIKE "% ' .
                    addslashes($data['area_locate']) . ' %" OR area_name LIKE "%' .
                    addslashes($data['area_locate']) . ' %"  OR area_name LIKE "% ' .
                    addslashes($data['area_locate']) . '%"');
            }
        }

        $rows = $search->getRows();

        // Loops through the areas
        foreach($rows as $i => $results) {
            // Checks for locations
            if (isset($results['area_location']) && !empty($results['area_location'])) {
                $rows[$i]['area_location'] = array('lat' => $results['area_location_x'],
                    'lon' => $results['area_location_y']);
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
        // Checks if we are updating the area_location
        if (isset($data['area_location'])) {
            // We need to do a raw query to use the MYSQL Point functions
            $query = "UPDATE `area` SET `area_location`=ST_GeomFromText('POINT("
                .$data['area_location'].")') WHERE `area_id` = ".$data['area_id'];

            $this->resource->query($query);
            unset($data['area_location']);
        }

        return $this->resource
            ->model($data)
            ->setAreaUpdated(date('Y-m-d H:i:s'))
            ->save('area')
            ->get();
    }
}
