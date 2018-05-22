INSERT INTO research (research_location, research_position, research_created, research_updated) VALUES ('{
                \"manila\": {
                    \"unemployment_rate\": \".3\",
                    \"hiring_rate\": \"10\",
                    \"top_positions\": 
                        [   
                            \"Project Manager\",
                            \"Web Developer\"
                        ],
                    \"top_companies\": 
                        [
                            1,
                            2
                        ],
                    \"salary_range\": \"5000\",
                    \"ad_space\": [
                        1,
                        2
                    ]
                }
            }', '
                {
                    \"Web Developer\": {
                        \"average_salary\": \"10000\",
                        \"salary_range\": \"10000\",
                        \"top_companies\":
                            [
                                1,
                                2
                            ],
                        \"job_details\": \"Web Developer Details\",
                        \"qualifications\": \"College Graduate\",
                        \"ad_space\": [
                                1,
                                2
                            ],
                        \"location\":  {
                            \"manila\": {
                                \"average_salary\": \"10000\",
                                \"seeker_count\": \"10\",
                                \"salary_range\": \"10000\",
                                \"top_companies\":
                                    [
                                        1,
                                        2
                                    ],
                                \"ad_space\":  
                                    [
                                        1,
                                        2
                                    ]
                            }
                        }
                    }
                }', '2017-11-20 11:07:47', '2017-11-20 11:07:47');