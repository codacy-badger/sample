<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Sales\Pipeline\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Pipeline SQL Service
 *
 * @vendor   Acme
 * @package  pipeline
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'pipeline';

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
            ->setPipelineCreated(date('Y-m-d H:i:s'))
            ->setPipelineUpdated(date('Y-m-d H:i:s'))
            ->save('pipeline')
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
        $search = $this->resource->search('pipeline');

        $search->filterByPipelineId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if ($results['pipeline_stages']) {
            $results['pipeline_stages'] = json_decode($results['pipeline_stages']);
        }

        return $results;
    }

    /**
     * Get board details from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getBoard($id)
    {
        $search = $this->resource->search('pipeline');

        $search->filterByPipelineId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if ($results['pipeline_stages']) {
            $results['pipeline_stages'] = json_decode($results['pipeline_stages']);
        }

        // pull leads
        $leads = $this->resource->search('lead_pipeline')
            ->innerJoinUsing('leads', 'lead_id')
            ->leftJoinUsing('lead_agent', 'lead_id')
            ->leftJoinOn('profile', 'profile_id = agent_profile_id')
            ->filterByPipelineId($id)
            ->getRows();

        // pull profiles
        $profiles = $this->resource->search('profile_pipeline')
            ->innerJoinUsing('profile as company', 'profile_id')
            ->leftJoinUsing('profile_agent', 'profile_id')
            ->filterByPipelineId($id)
            ->getRows();

        $agentIds = [];
        foreach ($profiles as $profile) {
            if ($profile['agent_profile_id']) {
                $agentIds[] = '"'.$profile['agent_profile_id'].'"';
            }
        }

        $sales = $this->resource->search('profile')
            ->addFilter('profile_id IN ('.implode(',', $agentIds).')')
            ->getRows();

        $agents = [];
        foreach ($sales as $key => $agent) {
            $agents[$agent['profile_id']] = $agent;
        }

        foreach ($leads as $key => $lead) {
            $leads[$key]['agent_name'] = $lead['profile_name'];
            unset($leads[$key]['profile_name']);
            unset($leads[$key]['profile_id']);
        }

        foreach ($profiles as $key => $profile) {
            $profiles[$key]['agent_name'] = $agents[$profile['agent_profile_id']]['profile_name'];
        }

        $results['deals'] = array_merge($leads, $profiles);

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
            ->setPipelineId($id)
            ->remove('pipeline');
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



        if (!isset($filter['pipeline_active'])) {
            $filter['pipeline_active'] = 1;
        }

        $search = $this->resource
            ->search('pipeline');

        if (!isset($data['all']) || !$data['all']) {
            $search->setStart($start)
                ->setRange($range);
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
                $where[] = 'LOWER(pipeline_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(pipeline_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = "JSON_SEARCH(LOWER(pipeline_stages), 'one', %s) IS NOT NULL";
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
            if ($results['pipeline_stages']) {
                $rows[$i]['pipeline_stages'] = json_decode($results['pipeline_stages']);
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
            ->setPipelineUpdated(date('Y-m-d H:i:s'))
            ->save('pipeline')
            ->get();
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function bulkActive(array $ids, $value)
    {
        //please rely on SQL CASCADING ON DELETE
        $fields = ['pipeline_active' => $value];
        $filter = ['pipeline_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('pipeline', $fields, $filter);
    }
}
