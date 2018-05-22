<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Sales\Deal\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;
use Cradle\Module\Utility\Validator as UtilityValidator;

/**
 * Deal SQL Service
 *
 * @vendor   Acme
 * @package  deal
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'deal';

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
            ->setDealCreated(date('Y-m-d H:i:s'))
            ->setDealUpdated(date('Y-m-d H:i:s'))
            ->save('deal')
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
        $search = $this->resource->search('deal');

        $search->innerJoinUsing('deal_pipeline', 'deal_id');
        $search->innerJoinUsing('pipeline', 'pipeline_id');

        $search->filterByDealId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        $results['agent'] = $this->resource->search('deal_agent')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByDealId($id)
            ->getRow();

        if ($results['deal_type'] == 'profile') {
            $results['company'] = $this->resource->search('deal_company')
                ->innerJoinUsing('profile', 'profile_id')
                ->filterByDealId($id)
                ->getRow();
        }

        if ($results['deal_type'] == 'lead') {
            $results['company'] = $this->resource->search('deal_company')
                ->innerJoinOn('leads', 'profile_id = lead_id')
                ->filterByDealId($id)
                ->getRow();
        }

        $results['history'] = $this->resource->search('history_deal')
            ->innerJoinUsing('history', 'history_id')
            ->leftJoinUsing('history_profile', 'history_id')
            ->leftJoinUsing('profile', 'profile_id')
            ->leftJoinUsing('history_comment', 'history_id')
            ->leftJoinUsing('history_thread', 'history_id')
            ->filterByDealId($id)
            ->sortByHistoryCreated('DESC')
            ->getRows();

        $results['events'] = $this->resource->search('event_deal')
            ->innerJoinUsing('event', 'event_id')
            ->filterByDealId($id)
            ->sortByEventCreated('DESC')
            ->getRows();

        $comments = $this->resource->search('comment_deal')
            ->innerJoinUsing('comment', 'comment_id')
            ->innerJoinUsing('comment_profile', 'comment_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByDealId($id)
            ->sortByCommentCreated('DESC')
            ->getRows();

        $commentResults = [];
        $ids = [];
        foreach ($comments as $comment) {
            $commentResults[$comment['comment_id']] = $comment;
            $ids[] = '"'.$comment['comment_id'].'"';
        }

        // get files attached to comments
        if ($ids) {
            // pull files
            $files = $this->resource->search('file')
                ->innerJoinUsing('file_comment', 'file_id')
                ->addFilter('comment_id IN ('.implode(',', $ids).')')
                ->getRows();

            foreach ($files as $file) {
                // if no files initialize a container
                if (!isset($commentResults[$file['comment_id']]['files'])) {
                    $commentResults[$file['comment_id']]['files'] = [];
                }

                // add attachments
                $commentResults[$file['comment_id']]['files'][] = $file;
            }
        }

        $threads = $this->resource->search('thread_deal')
            ->innerJoinUsing('thread', 'thread_id')
            ->filterByDealId($id)
            ->sortByThreadCreated('DESC')
            ->getRows();

        $threadResults = [];
        foreach ($threads as $thread) {
            $threadResults[$thread['thread_id']] = $thread;
        }

        // distribute history with comments and files and threads
        foreach ($results['history'] as $key => $history) {
            if (isset($commentResults[$history['comment_id']])) {
                $results['history'][$key] = array_merge($history, $commentResults[$history['comment_id']]);
            }

            if (isset($threadResults[$history['thread_id']])) {
                $results['history'][$key] = array_merge($history, $threadResults[$history['thread_id']]);
            }
        }

        if (isset($results['pipeline_stages'])) {
            $results['pipeline_stages'] = json_decode($results['pipeline_stages'], true);
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
            ->setDealId($id)
            ->remove('deal');
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
        $ranges = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['filter_range']) && is_array($data['filter_range'])) {
            $ranges = $data['filter_range'];
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


        if (!isset($filter['deal_active'])) {
            $filter['deal_active'] = 1;
        }


        $search = $this->resource
            ->search('deal')
            ->setStart($start)
            ->setRange($range);


        //join company
        $search->innerJoinUsing('deal_company', 'deal_id');

        if (isset($data['assignment']) &&
            ($data['assignment'] == 'assigned' ||
            $data['assignment'] == 'own')) {
            $search->innerJoinUsing('deal_agent', 'deal_id');
        } else {
            $search->leftJoinUsing('deal_agent', 'deal_id');
        }

        if (isset($data['assignment']) &&
            $data['assignment'] == 'own' &&
            $data['me']) {
            $search->addFilter('deal_agent.profile_id = %d', $data['me']);
        }

        if (isset($data['assignment']) &&
            $data['assignment'] == 'unassigned') {
            $search->addFilter('deal_agent.profile_id IS NULL ');
        }

        //join pipeline
        $search->innerJoinUsing('deal_pipeline', 'deal_id');
        $search->innerJoinUsing('pipeline', 'pipeline_id');

        if (isset($data['expiration']) && $data['expiration'] != 'all') {
            if ($data['expiration'] == 'active') {
                $search->addFilter('deal_close >= NOW()');
            }

            if ($data['expiration'] == 'expiring') {
                $search->addFilter('deal_close >= NOW()');
                $search->addFilter('deal_close <= %s', date('Y-m-d', strtotime('+1 week')));
            }

            if ($data['expiration'] == 'expired') {
                $search->addFilter('deal_close <= NOW()');
            }
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (empty($value) && $value != 0) {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9-_.]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //add range
        foreach ($ranges as $column => $value) {
            if (empty($value['start']) && $value['start'] != 0) {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9-_.]+$/', $column)) {
                if (UtilityValidator::isDate($value['start']) &&
                    UtilityValidator::isDate($value['end'])) {
                    $search->addFilter($column . ' >= %s', date('Y-m-d 00:00:00', strtotime($value['start'])));
                    $search->addFilter($column . ' <= %s', date('Y-m-d 23:59:59', strtotime($value['end'])));
                }

                if (UtilityValidator::isInteger($value['start']) &&
                    UtilityValidator::isInteger($value['end'])) {
                    $search->addFilter($column . ' >= "%s"', $value['start']);
                    $search->addFilter($column . ' <= "%s"', $value['end']);
                }
            }
        }

        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(deal_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(deal_status) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(deal_name) LIKE %s';
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
            $agent = $this->resource->search('deal_agent')
                ->innerJoinUsing('profile', 'profile_id')
                ->filterByDealId($results['deal_id']);

            $rows[$i]['agent'] = $agent->getRow();

            if ($results['deal_type'] == 'profile') {
                $company = $this->resource->search('deal_company')
                    ->innerJoinUsing('profile', 'profile_id')
                    ->filterByDealId($results['deal_id']);

                $rows[$i]['company'] = $company->getRow();
            }

            if ($results['deal_type'] == 'lead') {
                $company = $this->resource->search('deal_company')
                    ->innerJoinOn('leads', 'profile_id = lead_id')
                    ->filterByDealId($results['deal_id']);

                $rows[$i]['company'] = $company->getRow();
            }
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal(),
            'deals_total_amount' => $search->setColumns('sum(deal_amount) as deals_total')->getRow()['deals_total']
        ];
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function getSummary(array $data = [])
    {
        $deal = [];
        $search = $this->resource
            ->search('deal')
            ->filterByDealActive(1);

        $active = clone $search;
        $expired = clone $search;
        $expiring = clone $search;

        $deal['active'] = $active
            ->addFilter('deal_close >= NOW()')
            ->getTotal();

        $deal['expired'] = $expired
            ->addFilter('deal_close <= NOW()')
            ->getTotal();

        $deal['expiring'] = $expiring
            ->addFilter('deal_close >= NOW()')
            ->addFilter('deal_close <= %s', date('Y-m-d', strtotime('+1 week')))
            ->getTotal();

        return $deal;
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
            ->setDealUpdated(date('Y-m-d H:i:s'))
            ->save('deal')
            ->get();
    }

    /**
     * Links company
     *
     * @param *int $dealPrimary
     * @param *int $companyPrimary
     */
    public function linkCompany($dealPrimary, $companyPrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->setProfileId($companyPrimary)
            ->insert('deal_company');
    }

    /**
     * Unlinks company
     *
     * @param *int $dealPrimary
     * @param *int $companyPrimary
     */
    public function unlinkCompany($dealPrimary, $companyPrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->setProfileId($companyPrimary)
            ->remove('deal_company');
    }


    /**
     * Links agent
     *
     * @param *int $dealPrimary
     * @param *int $agentPrimary
     */
    public function linkAgent($dealPrimary, $agentPrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->setProfileId($agentPrimary)
            ->insert('deal_agent');
    }

    /**
     * Unlinks agent
     *
     * @param *int $dealPrimary
     * @param *int $agentPrimary
     */
    public function unlinkAgent($dealPrimary, $agentPrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->setProfileId($agentPrimary)
            ->remove('deal_agent');
    }

    /**
     * Unlinks All agent
     *
     * @param *int $dealPrimary
     * @param *int $agentPrimary
     */
    public function unlinkAllAgent($dealPrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->remove('deal_agent');
    }


    /**
     * Links pipeline
     *
     * @param *int $dealPrimary
     * @param *int $pipelinePrimary
     */
    public function linkPipeline($dealPrimary, $pipelinePrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->setPipelineId($pipelinePrimary)
            ->insert('deal_pipeline');
    }

    /**
     * Unlinks pipeline
     *
     * @param *int $dealPrimary
     * @param *int $pipelinePrimary
     */
    public function unlinkPipeline($dealPrimary, $pipelinePrimary)
    {
        return $this->resource
            ->model()
            ->setDealId($dealPrimary)
            ->setPipelineId($pipelinePrimary)
            ->remove('deal_pipeline');
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
        $fields = ['deal_active' => $value];
        $filter = ['deal_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('deal', $fields, $filter);
    }
}
