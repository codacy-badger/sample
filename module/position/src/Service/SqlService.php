<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Position\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Position SQL Service
 *
 * @vendor   Acme
 * @package  position
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'position';

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
            ->setPositionCreated(date('Y-m-d H:i:s'))
            ->setPositionUpdated(date('Y-m-d H:i:s'))
            ->save('position')
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
        $search = $this->resource->search('position');


        $search->filterByPositionId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['position_skills']) {
            $results['position_skills'] = json_decode($results['position_skills'], true);
        } else {
            $results['position_skills'] = [];
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
            ->setPositionId($id)
            ->remove('position');
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

        if (!isset($filter['position_active'])) {
            $filter['position_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['position_active'] == -1) {
            unset($filter['position_active']);
        }

        $search = $this->resource
            ->search('position')
            ->setStart($start)
            ->setRange($range);

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //position_skills
        if (isset($data['position_skills']) && !empty($data['position_skills'])) {
            if (!is_array($data['position_skills'])) {
                $data['position_skills'] = [$data['position_skills']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['position_skills'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(position_skills), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';

            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(position_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(position_description) LIKE %s';
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
            if($results['position_skills']) {
                $rows[$i]['position_skills'] = json_decode($results['position_skills'], true);
            } else {
                $rows[$i]['position_skills'] = [];
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
            ->setPositionUpdated(date('Y-m-d H:i:s'))
            ->save('position')
            ->get();
    }

    /**
     * Links skills
     *
     * @param *int $positionPrimary
     * @param *int $skillsPrimary
     */
    public function linkSkills($positionPrimary, $skillsPrimary)
    {
        return $this->resource
            ->model()
            ->setPositionId($positionPrimary)
            ->setSkillId($skillsPrimary)
            ->insert('position_skills');
    }

    /**
     * Unlinks skills
     *
     * @param *int $positionPrimary
     * @param *int $skillsPrimary
     */
    public function unlinkSkills($positionPrimary, $skillsPrimary)
    {
        return $this->resource
            ->model()
            ->setPositionId($positionPrimary)
            ->setSkillId($skillsPrimary)
            ->remove('position_skills');
    }

    /**
     * Unlinks All skills
     *
     * @param *int $positionPrimary
     * @param *int $skillsPrimary
     */
    public function unlinkAllSkills($positionPrimary)
    {
        return $this->resource
            ->model()
            ->setPositionId($positionPrimary)
            ->remove('position_skills');
    }
}
