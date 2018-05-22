<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Comment\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Comment SQL Service
 *
 * @vendor   Acme
 * @package  comment
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'comment';

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
            ->setCommentCreated(date('Y-m-d H:i:s'))
            ->setCommentUpdated(date('Y-m-d H:i:s'))
            ->save('comment')
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
        $search = $this->resource->search('comment');
        
        $search->innerJoinUsing('comment_profile', 'comment_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->innerJoinUsing('comment_deal', 'comment_id');
        $search->innerJoinUsing('deal', 'deal_id');
        
        $search->filterByCommentId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['profile_tags']) {
            $results['profile_tags'] = json_decode($results['profile_tags'], true);
        } else {
            $results['profile_tags'] = [];
        }

        if($results['profile_story']) {
            $results['profile_story'] = json_decode($results['profile_story'], true);
        } else {
            $results['profile_story'] = [];
        }

        if($results['profile_campaigns']) {
            $results['profile_campaigns'] = json_decode($results['profile_campaigns'], true);
        } else {
            $results['profile_campaigns'] = [];
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
            ->setCommentId($id)
            ->remove('comment');
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

        

        
        if (!isset($filter['comment_active'])) {
            $filter['comment_active'] = 1;
        }
        

        $search = $this->resource
            ->search('comment')
            ->setStart($start)
            ->setRange($range);

        
        //join profile
        $search->innerJoinUsing('comment_profile', 'comment_id');
        $search->innerJoinUsing('profile', 'profile_id');
        //join deal
        $search->innerJoinUsing('comment_deal', 'comment_id');
        $search->innerJoinUsing('deal', 'deal_id');
        

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {
            
            if($results['profile_tags']) {
                $rows[$i]['profile_tags'] = json_decode($results['profile_tags'], true);
            } else {
                $rows[$i]['profile_tags'] = [];
            }
            
            if($results['profile_story']) {
                $rows[$i]['profile_story'] = json_decode($results['profile_story'], true);
            } else {
                $rows[$i]['profile_story'] = [];
            }
            
            if($results['profile_campaigns']) {
                $rows[$i]['profile_campaigns'] = json_decode($results['profile_campaigns'], true);
            } else {
                $rows[$i]['profile_campaigns'] = [];
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
            ->setCommentUpdated(date('Y-m-d H:i:s'))
            ->save('comment')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $commentPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($commentPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setCommentId($commentPrimary)
            ->setProfileId($profilePrimary)
            ->insert('comment_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $commentPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($commentPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setCommentId($commentPrimary)
            ->setProfileId($profilePrimary)
            ->remove('comment_profile');
    }
    

    /**
     * Links deal
     *
     * @param *int $commentPrimary
     * @param *int $dealPrimary
     */
    public function linkDeal($commentPrimary, $dealPrimary)
    {
        return $this->resource
            ->model()
            ->setCommentId($commentPrimary)
            ->setDealId($dealPrimary)
            ->insert('comment_deal');
    }

    /**
     * Unlinks deal
     *
     * @param *int $commentPrimary
     * @param *int $dealPrimary
     */
    public function unlinkDeal($commentPrimary, $dealPrimary)
    {
        return $this->resource
            ->model()
            ->setCommentId($commentPrimary)
            ->setDealId($dealPrimary)
            ->remove('comment_deal');
    }
    
}
