<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracking\Applicant\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Applicant SQL Service
 *
 * @vendor   Acme
 * @package  applicant
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'applicant';

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
            ->setApplicantCreated(date('Y-m-d H:i:s'))
            ->setApplicantUpdated(date('Y-m-d H:i:s'))
            ->save('applicant')
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
        $search = $this->resource->search('applicant');


        $search->filterByApplicantId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['applicant_status']) {
            $results['applicant_status'] = json_decode($results['applicant_status'], true);
        } else {
            $results['applicant_status'] = [];
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
            ->setApplicantId($id)
            ->remove('applicant');
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

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['applicant_active'])) {
            $filter['applicant_active'] = 1;
        }

        $search = $this->resource
            ->search('applicant')
            ->setStart($start)
            ->setRange($range);

        $search->innerJoinUsing('applicant_profile', 'applicant_id');
        $search->innerJoinUsing('profile', 'profile_id');

        $search->innerJoinUsing('applicant_post', 'applicant_id');
        $search->innerJoinUsing('post', 'post_id');

        // Checks if we should not exclude the form
        if (!isset($data['exclude']['form'])) {
            $search->innerJoinUsing('applicant_form', 'applicant_id');
            $search->innerJoinUsing('form', 'form_id');
        }

        // filter applicant status
        if (isset($filter['applicant_status']) &&
            !empty($filter['applicant_status'])) {
                $search->addFilter('JSON_SEARCH(LOWER(applicant_status),
                    "one" ,"%'.strtolower($filter['applicant_status']).'%") IS NOT NULL');

                unset($filter['applicant_status']);
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

                $where[] = 'LOWER(profile_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_position) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //add sorting
        if ($order) {
            foreach ($order as $sort => $direction) {
                $search->addSort($sort, $direction);
            }
        } else {
            $search->addSort('applicant_created', 'DESC');
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {
            if($results['applicant_status']) {
                $rows[$i]['applicant_status'] = json_decode($results['applicant_status'], true);
            } else {
                $rows[$i]['applicant_status'] = [];
            }

            // get resume link
            $resume = $this->resource
                ->search('resume')
                ->setColumns('resume_id', 'resume_link')
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->innerJoinUsing('post_resume', 'resume_id')
                ->filterByPostId($results['post_id'])
                ->filterByProfileId($results['profile_id'])
                ->getRow();

            // merge resume to applicant
            if ($resume) {
                 $rows[$i] = array_merge($rows[$i], $resume);
            }
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    public function searchPosterApplicants(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];

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

        // We need the posts associated to the user deleting the tags
        $search = $this->resource
            ->search('post')
            ->setColumns('post.post_id')
            ->innerJoinUsing('post_profile', 'post_id')
            ->filterByProfileId($data['filter']['profile_id']);

        $rows = $search->getRows();

        // Checks if there are results
        if (!empty($rows)) {
            // We need the IDS of the post only
            $posts = [];

            // Loops through the results
            foreach ($rows as $row) {
                $posts[] = $row['post_id'];
            }

            // Now that we have a list of posts
            // We need the applicants of these posts
            // Only get applicants with the label being deleted
            $search = $this->resource
                ->search('applicant')
                ->setColumns('applicant_id, applicant_status')
                ->innerJoinUsing('applicant_post', 'applicant_id')
                ->addFilter('post_id IN ('. implode(',', $posts) .')')
                ->addFilter('JSON_SEARCH(LOWER(applicant_status),
                    "one" ,"%'.strtolower($data['label_name']).'%") IS NOT NULL');

            $rows = $search->getRows();

            return [
                'rows' => $rows,
                'total' => $search->getTotal()
            ];
        }

        // Nothing was returned
        return [
            'rows' => array(),
            'total' => 0
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
            ->setApplicantUpdated(date('Y-m-d H:i:s'))
            ->save('applicant')
            ->get();
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function viewForm(array $data = [])
    {
        $order = [];

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }
        // get applicant by post id
        $applicant = $this->resource
            ->search('applicant')
            ->innerJoinUsing('applicant_profile', 'applicant_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->innerJoinUsing('applicant_post', 'applicant_id')
            ->innerJoinUsing('post', 'post_id')
            ->innerJoinUsing('post_form', 'post_id')
            ->innerJoinUsing('form', 'form_id')
            ->filterByPostId($data['post_id'])
            ->filterByProfileId($data['profile_id'])
            ->getRow();

        if ($applicant) {
            // get question and answer
            $questions = $this->resource
                ->search('applicant')
                ->setColumns(
                    'question_id',
                    'question_name',
                    'question_choices',
                    'question_type',
                    'question_flag',
                    'question_priority',
                    'answer_id',
                    'answer_name'
                )
                ->innerJoinUsing('applicant_post', 'applicant_id')
                ->innerJoinUsing('post', 'post_id')
                ->innerJoinUsing('applicant_answer', 'applicant_id')
                ->innerJoinUsing('answer', 'answer_id')
                ->innerJoinUsing('question_answer', 'answer_id')
                ->innerJoinUsing('question', 'question_id')
                ->filterByPostId($data['post_id'])
                ->filterByApplicantId($applicant['applicant_id'])
                ->addSort('question_priority','ASC')
                ->getRows();
                
            foreach($questions as $i => $results) {
                if($results['question_choices']) {
                    $questions[$i]['question_choices'] = json_decode($results['question_choices'], true);
                } else {
                    $questions[$i]['question_choices'] = [];
                }
            }

            // merge questions to applicant
            $applicant['questions'] = $questions;
        }


        //return response format
        return [
            'rows' => $applicant,
            'total' => count($applicant)
        ];
    }

    /**
     * Links profile
     *
     * @param *int $applicantPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($applicantPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setProfileId($profilePrimary)
            ->insert('applicant_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $applicantPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($applicantPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setProfileId($profilePrimary)
            ->remove('applicant_profile');
    }

    /**
     * Unlinks All profile
     *
     * @param *int $applicantPrimary
     * @param *int $profilePrimary
     */
    public function unlinkAllProfile($applicantPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->remove('applicant_profile');
    }


    /**
     * Links form
     *
     * @param *int $applicantPrimary
     * @param *int $formPrimary
     */
    public function linkForm($applicantPrimary, $formPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setFormId($formPrimary)
            ->insert('applicant_form');
    }

    /**
     * Links post
     *
     * @param *int $applicantPrimary
     * @param *int $postPrimary
     */
    public function linkPost($applicantPrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setPostId($postPrimary)
            ->insert('applicant_post');
    }

    /**
     * Unlinks form
     *
     * @param *int $applicantPrimary
     * @param *int $formPrimary
     */
    public function unlinkForm($applicantPrimary, $formPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setFormId($formPrimary)
            ->remove('applicant_form');
    }

    /**
     * Unlinks post
     *
     * @param *int $applicantPrimary
     * @param *int $postPrimary
     */
    public function unlinkPost($applicantPrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setPostId($postPrimary)
            ->remove('applicant_post');
    }

    /**
     * Unlinks All form
     *
     * @param *int $applicantPrimary
     * @param *int $formPrimary
     */
    public function unlinkAllForm($applicantPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->remove('applicant_form');
    }

    /**
     * Links answer
     *
     * @param *int $applicantPrimary
     * @param *int $answerPrimary
     */
    public function linkAnswer($applicantPrimary, $answerPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setAnswerId($answerPrimary)
            ->insert('applicant_answer');
    }

    /**
     * Unlinks answer
     *
     * @param *int $applicantPrimary
     * @param *int $answerPrimary
     */
    public function unlinkAnswer($applicantPrimary, $answerPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->setAnswerId($answerPrimary)
            ->remove('applicant_answer');
    }

    /**
     * Unlinks All answer
     *
     * @param *int $applicantPrimary
     * @param *int $answerPrimary
     */
    public function unlinkAllAnswer($applicantPrimary)
    {
        return $this->resource
            ->model()
            ->setApplicantId($applicantPrimary)
            ->remove('applicant_answer');
    }

}
