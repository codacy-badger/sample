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

        $search->innerJoinUsing('history_profile', 'history_id');
        $search->innerJoinUsing('profile', 'profile_id');

        $search->filterByHistoryId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['history_value']) {
            $results['history_value'] = json_decode($results['history_value'], true);
        } else {
            $results['history_value'] = [];
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

        if (!isset($filter['history_active'])) {
            $filter['history_active'] = 1;
        }

        $search = $this->resource
            ->search('history')
            ->setStart($start)
            ->setRange($range)
            ->addSort('history_created', 'DESC');

        //join profile
        $search->innerJoinUsing('history_profile', 'history_id');
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
                $where[] = 'LOWER(profile_first) LIKE %s';
                $where[] = 'LOWER(profile_last) LIKE %s';
                $where[] = 'LOWER(profile_email) LIKE %s';
                $where[] = 'LOWER(history_attribute) LIKE %s';
                $where[] = 'LOWER(history_note) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $or[] = '%' . strtolower($keyword) . '%';
                $or[] = '%' . strtolower($keyword) . '%';
                $or[] = '%' . strtolower($keyword) . '%';
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

            if($results['history_value']) {
                $rows[$i]['history_value'] = json_decode($results['history_value'], true);
            } else {
                $rows[$i]['history_value'] = [];
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
            ->setHistoryUpdated(date('Y-m-d H:i:s'))
            ->save('history')
            ->get();
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

    /**
    * Unlinks All profile
    *
    * @param *int $historyPrimary
    * @param *int $profilePrimary
    */
    public function unlinkAllProfile($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_profile');
    }
    
    /**
     * Links blog
     *
     * @param *int $historyPrimary
     * @param *int $blogPrimary
     */
    public function linkBlog($historyPrimary, $blogPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setBlogId($blogPrimary)
            ->insert('history_blog');
    }

    /**
     * Unlinks blog
     *
     * @param *int $historyPrimary
     * @param *int $blogPrimary
     */
    public function unlinkBlog($historyPrimary, $blogPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setBlogId($blogPrimary)
            ->remove('history_blog');
    }

    /**
    * Unlinks All blog
    *
    * @param *int $historyPrimary
    * @param *int $blogPrimary
    */
    public function unlinkAllBlog($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_blog');
    }
    
    /**
     * Links feature
     *
     * @param *int $historyPrimary
     * @param *int $featurePrimary
     */
    public function linkFeature($historyPrimary, $featurePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setFeatureId($featurePrimary)
            ->insert('history_feature');
    }

    /**
     * Unlinks feature
     *
     * @param *int $historyPrimary
     * @param *int $featurePrimary
     */
    public function unlinkFeature($historyPrimary, $featurePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setFeatureId($featurePrimary)
            ->remove('history_feature');
    }

    /**
    * Unlinks All feature
    *
    * @param *int $historyPrimary
    * @param *int $featurePrimary
    */
    public function unlinkAllFeature($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_feature');
    }
    
    /**
     * Links position
     *
     * @param *int $historyPrimary
     * @param *int $positionPrimary
     */
    public function linkPosition($historyPrimary, $positionPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setPositionId($positionPrimary)
            ->insert('history_position');
    }

    /**
     * Unlinks position
     *
     * @param *int $historyPrimary
     * @param *int $positionPrimary
     */
    public function unlinkPosition($historyPrimary, $positionPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setPositionId($positionPrimary)
            ->remove('history_position');
    }

    /**
    * Unlinks All position
    *
    * @param *int $historyPrimary
    * @param *int $positionPrimary
    */
    public function unlinkAllPosition($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_position');
    }
    
    /**
     * Links post
     *
     * @param *int $historyPrimary
     * @param *int $postPrimary
     */
    public function linkPost($historyPrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setPostId($postPrimary)
            ->insert('history_post');
    }

    /**
     * Unlinks post
     *
     * @param *int $historyPrimary
     * @param *int $postPrimary
     */
    public function unlinkPost($historyPrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setPostId($postPrimary)
            ->remove('history_post');
    }

    /**
    * Unlinks All post
    *
    * @param *int $historyPrimary
    * @param *int $postPrimary
    */
    public function unlinkAllPost($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_post');
    }
    
    /**
     * Links research
     *
     * @param *int $historyPrimary
     * @param *int $researchPrimary
     */
    public function linkResearch($historyPrimary, $researchPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setResearchId($researchPrimary)
            ->insert('history_research');
    }

    /**
     * Unlinks research
     *
     * @param *int $historyPrimary
     * @param *int $researchPrimary
     */
    public function unlinkResearch($historyPrimary, $researchPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setResearchId($researchPrimary)
            ->remove('history_research');
    }

    /**
    * Unlinks All research
    *
    * @param *int $historyPrimary
    * @param *int $researchPrimary
    */
    public function unlinkAllResearch($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_research');
    }
    
    /**
     * Links role
     *
     * @param *int $historyPrimary
     * @param *int $rolePrimary
     */
    public function linkRole($historyPrimary, $rolePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setRoleId($rolePrimary)
            ->insert('history_role');
    }

    /**
     * Unlinks role
     *
     * @param *int $historyPrimary
     * @param *int $rolePrimary
     */
    public function unlinkRole($historyPrimary, $rolePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setRoleId($rolePrimary)
            ->remove('history_role');
    }

    /**
    * Unlinks All role
    *
    * @param *int $historyPrimary
    * @param *int $rolePrimary
    */
    public function unlinkAllRole($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_role');
    }
    
    /**
     * Links service
     *
     * @param *int $historyPrimary
     * @param *int $servicePrimary
     */
    public function linkService($historyPrimary, $servicePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setServiceId($servicePrimary)
            ->insert('history_service');
    }

    /**
     * Unlinks service
     *
     * @param *int $historyPrimary
     * @param *int $servicePrimary
     */
    public function unlinkService($historyPrimary, $servicePrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setServiceId($servicePrimary)
            ->remove('history_service');
    }

    /**
    * Unlinks All service
    *
    * @param *int $historyPrimary
    * @param *int $servicePrimary
    */
    public function unlinkAllService($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_service');
    }
    
    /**
     * Links transaction
     *
     * @param *int $historyPrimary
     * @param *int $transactionPrimary
     */
    public function linkTransaction($historyPrimary, $transactionPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setTransactionId($transactionPrimary)
            ->insert('history_transaction');
    }

    /**
     * Unlinks transaction
     *
     * @param *int $historyPrimary
     * @param *int $transactionPrimary
     */
    public function unlinkTransaction($historyPrimary, $transactionPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setTransactionId($transactionPrimary)
            ->remove('history_transaction');
    }

    /**
    * Unlinks All transaction
    *
    * @param *int $historyPrimary
    * @param *int $transactionPrimary
    */
    public function unlinkAllTransaction($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_transaction');
    }
    
    /**
     * Links utm
     *
     * @param *int $historyPrimary
     * @param *int $utmPrimary
     */
    public function linkUtm($historyPrimary, $utmPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setUtmId($utmPrimary)
            ->insert('history_utm');
    }

    /**
     * Unlinks utm
     *
     * @param *int $historyPrimary
     * @param *int $utmPrimary
     */
    public function unlinkUtm($historyPrimary, $utmPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->setUtmId($utmPrimary)
            ->remove('history_utm');
    }

    /**
    * Unlinks All utm
    *
    * @param *int $historyPrimary
    * @param *int $utmPrimary
    */
    public function unlinkAllUtm($historyPrimary)
    {
        return $this->resource
            ->model()
            ->setHistoryId($historyPrimary)
            ->remove('history_utm');
    }
    
}
