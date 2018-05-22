<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Widget\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Widget SQL Service
 *
 * @vendor   Acme
 * @package  widget
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'widget';

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
            ->setWidgetCreated(date('Y-m-d H:i:s'))
            ->setWidgetUpdated(date('Y-m-d H:i:s'))
            ->save('widget')
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
        $search = $this->resource->search('widget');

        $search->innerJoinUsing('widget_profile', 'widget_id');
        $search->innerJoinUsing('profile', 'profile_id');

        $search->filterByWidgetId($id);

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
            ->setWidgetId($id)
            ->remove('widget');
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

        if (!isset($filter['widget_active'])) {
            $filter['widget_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['widget_active'] == -1) {
            unset($filter['widget_active']);
        }

        $search = $this->resource
            ->search('widget')
            ->setStart($start)
            ->setRange($range);

        //join profile
        $search->innerJoinUsing('widget_profile', 'widget_id');
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
                $where[] = 'LOWER(widget_button_title) LIKE %s';
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
            ->setWidgetUpdated(date('Y-m-d H:i:s'))
            ->save('widget')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $widgetPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($widgetPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setWidgetId($widgetPrimary)
            ->setProfileId($profilePrimary)
            ->insert('widget_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $widgetPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($widgetPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setWidgetId($widgetPrimary)
            ->setProfileId($profilePrimary)
            ->remove('widget_profile');
    }
}
