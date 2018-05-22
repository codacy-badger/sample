<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Template\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Template SQL Service
 *
 * @vendor   Acme
 * @package  template
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'template';

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
            ->setTemplateCreated(date('Y-m-d H:i:s'))
            ->setTemplateUpdated(date('Y-m-d H:i:s'))
            ->save('template')
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
        $search = $this->resource->search('template');

        $search->filterByTemplateId($id);

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
            ->setTemplateId($id)
            ->remove('template');
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

        if (!isset($filter['template_active'])) {
            $filter['template_active'] = 1;
        }

        if (isset($data['export'])) {
            $range = 0;
        }

        $search = $this->resource
            ->search('template')
            ->setStart($start)
            ->setRange($range);
       
        if (isset($data['listing']) && $data['listing']) {
            $search
                ->leftJoinUsing('campaign_template', 'template_id')
                ->leftJoinUsing('campaign', 'campaign_id')
                ->setColumns(
                    'count(distinct template.template_id) as total',
                    'template.*',
                    'SUM(campaign_unopened) as template_unopened',
                    'SUM(campaign_opened) as template_opened',
                    'SUM(campaign_clicked) as template_clicked',
                    'SUM(campaign_spam) as template_spam',
                    'SUM(campaign_unsubscribed) as template_unsubscribed',
                    'SUM(campaign_bounced) as template_bounced',
                    'SUM(campaign_sent) as template_sent',
                    'campaign_created'
                )
                ->groupBy('template.template_id')
                ->sortByCampaignCreated('DESC');
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
                $where[] = 'LOWER(template_title) LIKE %s';
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

        foreach($rows as $i => $results) {

        }

        //return response format
        return [
            'rows' => $rows,
            'total' => isset($rows[0]['total']) ? $rows[0]['total'] : 0
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
            ->setTemplateUpdated(date('Y-m-d H:i:s'))
            ->save('template')
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
        $fields = ['template_'.$field => $value];
        $filter = ['template_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('template', $fields, $filter);
    }
}
