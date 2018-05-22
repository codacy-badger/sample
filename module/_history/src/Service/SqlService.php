<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\History\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * History SQL Service
 *
 * @vendor   Acme
 * @package  history
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'history';

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
            ->setHistoryCreated(date('Y-m-d H:i:s'))
            ->setHistoryUpdated(date('Y-m-d H:i:s'))
            ->save('history')
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
        $search = $this->resource->search('history');

        $search->innerJoinUsing('history_deal', 'history_id');
        $search->innerJoinUsing('deal', 'deal_id');

        $search->filterByHistoryId($id);

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
            ->setHistoryId($id)
            ->remove('history');
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
        $date = [];
        $filterNot = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['filter_not']) && is_array($data['filter_not'])) {
            $filterNot = $data['filter_not'];
        }

        if (isset($data['date']) && is_array($data['date'])) {
            $date = $data['date'];
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

        if (!isset($filter['history_active'])) {
            $filter['history_active'] = 1;
        }

        // Unset the history_active
        if ($filter['history_active'] == -1) {
            unset($filter['history_active']);
        }

        $search = $this->resource
            ->search('history')
            ->setStart($start)
            ->setRange($range);

        //join deal
        $search->leftJoinUsing('history_deal', 'history_id');
        $search->leftJoinUsing('history_profile', 'history_id');
        $search->leftJoinUsing('profile', 'profile_id');
        $search->leftJoinUsing('deal', 'deal_id');

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //add filter nots
        foreach ($filterNot as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' != %s', $value);
            }
        }

        foreach ($date as $column => $values) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column) &&
                isset($values['start_date']) && isset($values['end_date']) &&
                !empty($values['start_date']) && !empty($values['end_date'])) {
                $search
                    ->addFilter($column . ' >= "'. $values['start_date'].'"')
                    ->addFilter($column . ' <= "'. $values['end_date'].'"');
            }
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(history_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%'; $where[] = 'LOWER(history_action) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%'; $where[] = 'LOWER(deal_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%'; $where[] = 'LOWER(deal_status) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%'; $where[] = 'LOWER(deal_type) LIKE %s';
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

        $ids = [];
        $comments = [];
        $history = [];

        foreach($rows as $i => $results) {
            $ids[] = $results['history_id'];
            $history[$results['history_id']] = $results;
        }

        if ($ids) {
            $comments = $this->resource
                ->search('history_comment')
                ->innerJoinUsing('comment', 'comment_id')
                ->addFilter('history_id IN ('.implode(',', $ids).')')
                ->getRows();

            foreach ($comments as $comment) {
                $comments[$comment['comment_id']] = $comment;
            }
        }

        if ($comments) {
            $files = $this->resource
                ->search('file_comment')
                ->innerJoinUsing('file', 'file_id')
                ->addFilter('comment_id IN ('.implode(',', array_keys($comments)).')')
                ->getRows();

            foreach ($files as $file) {
                $comments[$file['comment_id']]['files'][] = $file;
            }
        }

        foreach ($comments as $comment) {
            $history[$comment['history_id']]['comment'] = $comment;
        }

        if ($ids) {
            $threads = $this->resource
                ->search('history_thread')
                ->innerJoinUsing('thread', 'thread_id')
                ->addFilter('history_id IN ('.implode(',', $ids).')')
                ->getRows();


            foreach ($threads as $thread) {
                $history[$thread['history_id']]['thread'] = $thread;
            }
        }

        //return response format
        return [
            'rows' => $history,
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
            ->setHistoryUpdated(date('Y-m-d H:i:s'))
            ->save('history')
            ->get();
    }

    /**
     * Links deal
     *
     * @param *int $historyPrimary
     * @param *int $dealPrimary
     */
    public function linkDeal($historyPrimary, $dealPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setDealId($dealPrimary)
            ->insert('history_deal');
    }

    /**
     * Unlinks deal
     *
     * @param *int $historyPrimary
     * @param *int $dealPrimary
     */
    public function unlinkDeal($historyPrimary, $dealPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setDealId($dealPrimary)
            ->remove('history_deal');
    }

    /**
     * Links comment
     *
     * @param *int $historyPrimary
     * @param *int $commentPrimary
     */
    public function linkComment($historyPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setCommentId($commentPrimary)
            ->insert('history_comment');
    }

    /**
     * Unlinks comment
     *
     * @param *int $historyPrimary
     * @param *int $commentPrimary
     */
    public function unlinkComment($historyPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setCommentId($commentPrimary)
            ->remove('history_comment');
    }

    /**
     * Links thread
     *
     * @param *int $historyPrimary
     * @param *int $threadPrimary
     */
    public function linkThread($historyPrimary, $threadPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setThreadId($threadPrimary)
            ->insert('history_thread');
    }

    /**
     * Unlinks thread
     *
     * @param *int $historyPrimary
     * @param *int $threadPrimary
     */
    public function unlinkThread($historyPrimary, $threadPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setThreadId($threadPrimary)
            ->remove('history_thread');
    }

    /**
     * Links profile
     *
     * @param *int $historyPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($historyPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setProfileId($profilePrimary)
            ->insert('history_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $historyPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($historyPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setProfileId($profilePrimary)
            ->remove('history_profile');
    }
}
