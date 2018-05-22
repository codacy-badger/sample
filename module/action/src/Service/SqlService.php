<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Action\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Action SQL Service
 *
 * @vendor   Acme
 * @package  action
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'action';

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
            ->setActionCreated(date('Y-m-d H:i:s'))
            ->setActionUpdated(date('Y-m-d H:i:s'))
            ->save('action')
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
        $search = $this->resource->search('action');

        $search->leftJoinUsing('action_template', 'action_id');
        $search->leftJoinUsing('template', 'template_id');

        $search->filterByActionId($id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        // action_tags
        if ($results['action_tags']) {
            $results['action_tags'] = json_decode($results['action_tags'], true);
        } else {
            $results['action_tags'] = [];
        }

        // action_when
        if ($results['action_when']) {
            $results['action_when'] = json_decode($results['action_when'], true);
        } else {
            $results['action_when'] = [];
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
            ->setActionId($id)
            ->remove('action');
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
        $fields = ['action_'.$field => $value];
        $filter = ['action_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('action', $fields, $filter);
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
        $dateType = 'created';

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

        if (isset($data['date_type'])) {
            $dateType = $data['date_type'];
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['action_active'])) {
            $filter['action_active'] = 1;
        }


        $search = $this->resource
            ->search('action')
            ->setStart($start)
            ->setRange($range);


        //join template
        $search->leftJoinUsing('action_template', 'action_id');
        $search->leftJoinUsing('template', 'template_id');


        //add filters
        foreach ($filter as $column => $value) {
            if ($column != 'action_active' && empty($value)) {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        if (isset($data['date_start'])) {
            $date = date('Y-m-d 00:00:00', strtotime($data['date_start']));
            $search->addFilter('action_'.$dateType.' >= "'.$date.'"');
        }

        if (isset($data['date_end'])) {
            $date = date('Y-m-d 23:59:59', strtotime($data['date_end']));
            $search->addFilter('action_'.$dateType.' <= "'.$date.'"');
        }


        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                if(!$keyword) {
                    continue;
                }

                $or = [];
                $where = [];
                $where[] = 'LOWER(action_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(action_event) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(action_when) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(template_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        if (isset($data['template_keyword']) && $data['template_keyword']) {
            $search->addFilter(
                'LOWER(template_title) LIKE %s',
                '%'.$data['template_keyword'].'%'
            );
        }


        // action_tags
        if (isset($data['action_tags']) && !empty($data['action_tags'])) {
            if (!is_array($data['action_tags'])) {
                $data['action_tags'] = [$data['action_tags']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['action_tags'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(action_tags), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';

            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        // action_when
        if (isset($data['action_when']) && !empty($data['action_when'])) {
            if (!is_array($data['action_when'])) {
                $data['action_when'] = [$data['action_when']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the when
            foreach ($data['action_when'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(action_when), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';

            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {
            // action_tags
            if ($results['action_tags']) {
                $rows[$i]['action_tags'] = json_decode($results['action_tags'], true);
            } else {
                $rows[$i]['action_tags'] = [];
            }

            // action_when
            if ($results['action_when']) {
                $rows[$i]['action_when'] = json_decode($results['action_when'], true);
            } else {
                $rows[$i]['action_when'] = [];
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
            ->setActionUpdated(date('Y-m-d H:i:s'))
            ->save('action')
            ->get();
    }

    /**
     * Links template
     *
     * @param *int $actionPrimary
     * @param *int $templatePrimary
     */
    public function linkTemplate($actionPrimary, $templatePrimary)
    {
        return $this->resource
            ->model()
            ->setActionId($actionPrimary)
            ->setTemplateId($templatePrimary)
            ->insert('action_template');
    }

    /**
     * Unlinks template
     *
     * @param *int $actionPrimary
     * @param *int $templatePrimary
     */
    public function unlinkTemplate($actionPrimary, $templatePrimary)
    {
        return $this->resource
            ->model()
            ->setActionId($actionPrimary)
            ->setTemplateId($templatePrimary)
            ->remove('action_template');
    }

}
