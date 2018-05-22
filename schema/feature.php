<?php //-->
return [
    'singular' => 'Feature',
    'plural' => 'Features',
    'primary' => 'feature_id',
    'active' => 'feature_active',
    'created' => 'feature_created',
    'updated' => 'feature_updated',
    'relations' => [],
    'fields' => [
        'feature_name' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'form' => [
                'label' => 'Name',
                'type' => 'input',
            ],
            'list' => [
                'label' => 'Name'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Name is required'
                ]
            ]
        ],
        'feature_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'required' => true,
                'index' => true
            ],
            'form' => [
                'label' => 'Title',
                'type' => 'input',
            ],
            'list' => [
                'label' => 'Title'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Title is required'
                ]
            ]
        ],
        'feature_type' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
                'index' => true,
                'required' => true,
                'default' => 'industry'
            ],
            'form' => [
                'label' => 'Type',
                'type' => 'radios',
                'options' => [
                    'position' => 'Position',
                    'location' => 'Location',
                    'industry' => 'industry'
                ]
            ],
            'list' => [
                'label' => 'Type'
            ],
            'validation' => [
                [
                    'method' => 'one',
                    'message' => 'Must choose a feature type',
                    'parameters' => [
                        'position',
                        'location',
                        'industry'
                    ]
                ]
            ]
        ],
        'feature_color' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 10,
                'default' => '#CA1551'
            ],
            'form' => [
                'label' => 'Color',
                'type' => 'text'
            ],
            'validation' => [
                [
                    'method' => 'regexp',
                    'message' => 'Must be valid hexadecimal color',
                    'parameters' => '#^\#[0-9A-F]{6}#'
                ]
            ]
        ],
        'feature_image' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
            ],
            'form' => [
                'label' => 'Image',
                'type' => 'image-field',
                'attributes' => [
                    'data-do' => 'image-field',
                ]
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Image is required'
                ]
            ]
        ],
        'feature_meta_title' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 255,
            ],
            'form' => [
                'label' => 'Meta Title',
                'type' => 'text'
            ]
        ],
        'feature_meta_description' => [
            'sql' => [
                'type' => 'text',
            ],
            'form' => [
                'label' => 'Meta Description',
                'type' => 'textarea'
            ]
        ],
        'feature_keywords' => [
            'sql' => [
                'type' => 'json'
            ],
            'form' => [
                'label' => 'Keywords',
                'type' => 'tag-field'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Keywords are required'
                ]
            ]
        ],
        'feature_slug' => [
            'sql' => [
                'type' => 'varchar',
                'length' => 50
            ],
            'form' => [
                'label' => 'Slug',
                'type' => 'input'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Slug is required'
                ],
                [
                    'method' => 'regexp',
                    'message' => 'Must consist of lowercase and dashes only',
                    'parameters' => '#[a-z0-9]+(?:-[a-z0-9]+)*#'
                ]
            ]    
        ],
        'feature_detail' => [
            'sql' => [
                'type' => 'text'
            ],
            'form' => [
                'label' => 'Detail',
                'type' => 'textarea'
            ],
            'validation' => [
                [
                    'method' => 'required',
                    'message' => 'Detail is required'
                ]
            ]
        ],
        'feature_links' => [
            'sql' => [
                'type' => 'json',
            ],
            'form' => [
                'label' => 'Links',
                'type' => 'tag-field'
            ]
        ],
    ],
    'fixtures' => [
        [
            'feature_id' => 1,
            'feature_name' =>  'Bicol Region',
            'feature_color' => '#CA1551',
            'feature_slug' => 'Jobs-In-Bicol',
            'feature_title' => 'Bicol',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/bicol.jpg",
            'feature_meta_title' =>  'Job opportunities in Bicol on Jobayan',
            'feature_meta_description' => 'Find all job listings in Bicol and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Bicol Region that designates in Region V and comprises six provinces, expanded in terms of economic status in 2016 by 5.7% with major contribution of the region industry and services. Although the industry slowed down as compared to its expansion ie the previous year, services increased yet similarly at slower growth. The effect of services from the increased job hiring available in the region, made the largest contributor to the region\'s percent economic performance. Since the beginning of the new administration, the region is continuously adjusting that affects its performance. The Transportation, Storage, and Communication growth slowed down in 2015, as well as Trade and Repair of Motor Vehicles, Motorcycles, Personal and Household Goods. Also, as the growth on home-based business and online shop increased, more jobs became open for search. As per the Regional Development Plan 2017-2022, the region attains a "Matatag, Maginhawa, at Panatag na buhay para sa lahat".',
            'feature_keywords' => json_encode([
                    'bicol jobs', 
                    'bicol career', 
                    'job search', 
                    'job hiring', 
                    'job listing'
                ]),
            'feature_links' => json_encode([
                    'https://psa.gov.ph/regional-accounts/grdp/highlights',
                    'http://nro5.neda.gov.ph/2017/05/11/neda-region-5-statement-on-the-bicol-economy-in-2016/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 2,
            'feature_name' => 'Cagayan Valley',
            'feature_color' => '#FB4D3D',
            'feature_slug' => 'Jobs-In-Cagayan-Valley',
            'feature_title' => 'Cagayan Valley',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/cagayan-valley.jpg",
            'feature_meta_title' =>  'Job opportunities in Cagayan Valley on Jobayan',
            'feature_meta_description' => 'Find all job listings in Cagayan Valley and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Cagayan Valley that designates as Region II and composes of five provinces, lies mostly in a large valley in Northeastern Luzon. The economy grew by 4.1% in 2015 but landed on the third slowest growing regions in the country in 2016. The slower growth is attributed in the slowdown ofthe Service sector and the decline in the Agriculture, Hunting, Forestry, and Fisheries (AHFF) sector. Nevertheless, the Service sector remains to be the biggest contributor to the economy making it more available for job seeker to search for opportunity in the service sector. Despite questioning the identity of the region as Service region, it is still considered as agricultural for its position as a top producer of rice and corn.',
            'feature_keywords' => json_encode([
                    'cagayan valley jobs', 
                    'cagayan valley career', 
                    'job search', 'job hiring', 
                    'job listing'
                ]),
            'feature_links' => json_encode([
                    'http://countrystat.psa.gov.ph/?cont=16&r=2',
                    'http://northernforum.net/cagayan-valley-posts-3-3-growth-in-economy-but/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 3,
            'feature_name' => 'Calabarzon Region',
            'feature_color' => '#C54B7F',
            'feature_slug' => 'Jobs-In-Calabarzon',
            'feature_title' => 'Calabarzon',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/calabarzon.jpg",
            'feature_meta_title' =>  'Job opportunities in Calabarzon on Jobayan',
            'feature_meta_description' => 'Find all job listings in Calabarzon and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'CALABARZON that designates as Region IV-A and composes of five provinces, is located in Southern Luzon and is second most densely populated region. According to Philippine Statistics Authority, the total number of people employed in this region is 5,085,000 and is mostly absorbed by the agriculture sector. The number of people employed in this sector is 649,000, and 78.74% is comprised by male workers. This brings the number of job hiring a factor of their growth. The three major economic sectors are agriculture, industry, and service, and the jobs bring the region to their economic status in CALABARZON. Despite the slower economic growth last year, CALABARZON was still able to contribute 16.8% in the overall Philippine growth in 2016.',
            'feature_keywords' => json_encode([
                    'calabarzon jobs', 
                    'calabarzon career', 
                    'job search', 
                    'job hiring', 
                    'job listing'
                ]),
            'feature_links' => json_encode([
                    'http://thefilipinoconnection.net/calabarzon-economy-grew-4-8-percent-in-2016/',
                    'http://countrystat.psa.gov.ph/?cont=16&r=4',
                    'https://businessmirror.com.ph/sustaining-high-economic-growth-remains-a-challenge-in-calabarzon/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 4,
            'feature_name' => 'Cebu City',
            'feature_color' => '#345995',
            'feature_slug' => 'Jobs-In-Cebu-City',
            'feature_title' => 'Cebu City',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/cebu-city.jpg",
            'feature_meta_title' =>  'Job opportunities in Cebu City on Jobayan',
            'feature_meta_description' => 'Find all job listings in Cebu City and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Cebu is the capital city of Central Visayas Region that is consisting of a main island and 167 surrounding islands. Cebu is the biggest contributor to the economy of Central Visayas coming from both private and public sectors. Tourism and Business Process Outsourcing sectors are the region\'s strengths since both dollar earners and BPO is a source of market for real estate and hotel according to economist. Given that, Cebu is a good place to search jobs especially that people who work in these sectors earn more and tend to have more spending power. In the coming years, most economists in the country predict that the growth in Cebu continue to be positive.',
            'feature_keywords' => json_encode([
                'cebu jobs', 
                'cebu career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://cebudailynews.inquirer.net/89251/89251',
                    'http://www.philstar.com:8080/cebu-business/2017/01/24/1665394/investments-propel-philippine-economy-2017'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 5,
            'feature_name' => 'Central Luzon',
            'feature_color' => '#434D70',
            'feature_slug' => 'Jobs-In-Central-Luzon',
            'feature_title' => 'Central Luzon',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/central-luzon.jpg",
            'feature_meta_title' =>  'Job opportunities in Central Luzon on Jobayan',
            'feature_meta_description' => 'Find all job listings in Central Luzon and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Central Luzon is strategically located between Northern Luzon and National Capital Region. It has the highest economic growth from 2011 to 2016 at 9.5%, and is primarily driven by the manufacturing industry. Given this, many job listings are open for search in this sector, adding to the 4,126,000 of the total employment in this region. GRDP measures the economic performance and sees to it the relative contribution of Agriculture, Hunting, Forestry, and Fishing (AHFF) as the major economic industries; and the significant increase of revenue of these sectors is the major contributor to the growth rate of the economy in the region in general. Thus, remaining one of the top regional sharers to the national economy according to PSA.',
            'feature_keywords' => json_encode([ 
                'luzon jobs', 
                'luzon career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://www.sunstar.com.ph/pampanga/business/2017/05/10/central-luzon-posts-highest-economic-growth-rate-6-years-541146',
                    'http://countrystat.psa.gov.ph/?cont=16&r=3'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 6,
            'feature_name' => 'Central Visayas',
            'feature_color' => '#888800',
            'feature_slug' => 'Jobs-In-Central-Visayas',
            'feature_title' => 'Central Visayas',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/central-visayas.jpg",
            'feature_meta_title' =>  'Job opportunities in Central Visayas on Jobayan',
            'feature_meta_description' => 'Find all job listings in Central Visayas and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Central Visayas is located at the center of the Philippines archipelago between islands of Luzon and Mindanao, and is composed of four island provinces. The economy of Central Visayas grew by 4.9% in 2015, and remained as one of the fastest growing regions in the country together with Eastern Visayas, Central Luzon, and Davao Region. The major contributors to the economy are the services sector with biggest contribution, and the industry sector with highest growth. Services sector grew higher than the Agriculture, Forestry, and Fisheries sector; thus, making the job search more favorable in the region; although 27.9% of the total employment of 3,215,000 persons in the region comes from agriculture sector.',
            'feature_keywords' => json_encode([
                'visayas jobs', 
                'visayas career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://cebudailynews.inquirer.net/131796/central-visayas-economy-grew-8-8-percent-2016',
                    'http://countrystat.psa.gov.ph/?cont=16&r=7'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 7,
            'feature_name' => 'Cordillera Region',
            'feature_color' => '#410B13',
            'feature_slug' => 'Jobs-In-Cordillera',
            'feature_title' => 'Cordillera',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/cordillera.jpg",
            'feature_meta_title' =>  'Job opportunities in Cordillera on Jobayan',
            'feature_meta_description' => 'Find all job listings in Cordillera and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Cordillera or Cordillera Administrative Region (CAR) was established through Executive Order No. 220 by former President Corazon Aquino during her term. The region is composed of seven provinces. With its people, the total employment in CAR is 759,000 only. The region\'s economy increased by 2.1% from 2015 to 2016, but still lower compared to the economic growth of 4% few years ago. The decline in Agriculture, Hunting, Forestry, and Fishing (AHFF) was the factor for this slowdown in the economy. While services and industry sectors contribute to the total output of the region, the percentage share of the first increased, however the latter decreased. Given that in terms of employment in this region, job hiring continues for the people to build career and for the region and the country to attain economic growth.',
            'feature_keywords' => json_encode([
                'cordillera jobs', 
                'cordillera career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://countrystat.psa.gov.ph/?cont=16&r=14',
                    'http://baguioheraldexpressonline.com/cordillera-economy-up/ '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 8,
            'feature_name' => 'Davao Region',
            'feature_color' => '#FF7400',
            'feature_slug' => 'Jobs-In-Davao',
            'feature_title' => 'Davao',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/davao.jpg",
            'feature_meta_title' =>  'Job opportunities in Davao on Jobayan',
            'feature_meta_description' => 'Find all job listings in Davao and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Davao located in Mindanao is the largest city, most populous aside from Metro Manila, and highly urbanized. In terms of employment and businesses, 42,000 business owners in Davao City renewed their permits at the start of the year and more permits are coming while others are already on process, giving more employment readily available for the people of Davao. According to the acting assistant city treasurer, they have collected a total of Php883.5 Million of taxes in 2017, and expecting more this 2018. This allows more job hiring opportunities in Davao to maintain or improve the employment and economic status of the region.',
            'feature_keywords' => json_encode([
                'davao jobs', 
                'davao career', 
                'job search', 
                'job hiring',
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://davaotoday.com/main/economy/over-40000-davao-city-businesses-up-for-renewal/',
                    'http://www.sunstar.com.ph/davao/local-news/2017/05/05/94-growth-recorded-davao-regions-economy-540149'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 9,
            'feature_name' => 'Eastern Visayas',
            'feature_color' => '#483C43',
            'feature_slug' => 'Jobs-In-Eastern-Visayas',
            'feature_title' => 'Eastern Visayas',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/eastern-visayas.jpg",
            'feature_meta_title' =>  'Job opportunities in Eastern Visayas on Jobayan',
            'feature_meta_description' => 'Find all job listings in Eastern Visayas and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Eastern Visayas or Region VIII is composed of two main islands, Leyte and Samar, and are consisted of six provinces. In terms of the economy, there are seven cities that help it grow stronger. These are Borongan, Baybay, Ormoc, Tacloban, Calbayog, Catbalogan, and Maasin. According to 2016 GRDP, Eastern Visayas is the fastest growing economy among all regions in the Philippines. This comes from construction, the fastest growing; followed by manufacturing, and then financial intermediation subsectors. The construction sector is registered the fastest due to Yolanda reconstruction projects from public to private sectors. This allows more people to search companies from the construction industry and regain from the occurrence in the region.',
            'feature_keywords' => json_encode([
                'visayas jobs', 
                'visayas career',
                'job search',
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://nro8.neda.gov.ph/2017/05/15/eastern-visayas-economy-fastest-growing-in-the-country-in-2016/',
                    'https://philippinescities.com/region-8-eastern-visayas/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 10,
            'feature_name' => 'Ilocos Region',
            'feature_color' => '#1A8A00',
            'feature_slug' => 'Jobs-In-Ilocos',
            'feature_title' => 'Ilocos',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/ilocos.jpg",
            'feature_meta_title' =>  'Job opportunities in Ilocos on Jobayan',
            'feature_meta_description' => 'Find all job listings in Ilocos and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Ilocos Region or Region I is located at the northwest part of Luzon, near Cordillera Administrative Region, Cagayan Valley, Central Luzon, and West Philippine Sea. Its economy contributed 3.1% to the country\'s GDP in 2015, though Agriculture, Hunting, Forestry, and Fishing (AHFF) decreased by 1.3% in 2015. In 2016, the economy of the region accelerated due to faster performance in the industry and services sectors. This allows more jobs and career growth because of the opportunities given in the region. More growth in the economy comes from construction; mining and quarrying; and electricity, gas, and water supply (EGWS) which all grew faster than the previous year. Overall, industry and services was able to contribute the biggest share, while AHFF was able to share the lowest.',
            'feature_keywords' => json_encode([ 
                 'ilocos jobs', 
                 'ilocos career', 
                 'job search', 
                 'job hiring', 
                 'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://countrystat.psa.gov.ph/?cont=16&r=1',
                    'https://www.ilocosnews.com/2017/05/05/economy-of-ilocos-region-accelerates-by-8-4-percent/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 11,
            'feature_name' => 'Metro Manila',
            'feature_color' => '#1B8394',
            'feature_slug' => 'Jobs-In-Manila',
            'feature_title' => 'Manila',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/manila.jpg",
            'feature_meta_title' =>  'Job opportunities in Manila on Jobayan',
            'feature_meta_description' => 'Find all job listings in Manila and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Manila or City of Manila is the capital of the Philippines, and is considered to be the most densely populated city in the world. Its economy is multi-faceted where the productions of chemicals, textiles, rope, coconut oil, and shoes are manufactured in metropolitan area; as well as food and tobacco processing. The activities in services and construction industries increased the employment rate of the area bringing 5.2 million people employed in Metro Manila. In this city, the common use of the English language gives people the advantage in job hiring for international trade, business process outsourcing, and customer service industries.',
            'feature_keywords' => json_encode([
                'manila jobs', 
                'manila career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'https://www.internations.org/manila-expats/guide/working-in-manila-15838',
                    'http://www.philstar.com/business/2017/05/05/1696723/metro-manila-economic-growth-quickens-7.5-2016',
                    'http://www.city-data.com/world-cities/Manila-Economy.html'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 12,
            'feature_name' => 'Mindanao Region',
            'feature_color' => '#874122',
            'feature_slug' => 'Jobs-In-Mindanao',
            'feature_title' => 'Mindanao',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/mindanao.jpg",
            'feature_meta_title' =>  'Job opportunities in Mindanao on Jobayan',
            'feature_meta_description' => 'Find all job listings in Mindanao and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Mindanao located in the southern part of the Philippines and is surrounded by four seas namely Bohol, Philippine, Celebes, and Sulu. Its economy continues to grow despite the crisis happening in the region because of the policies, programs, and projects that are continually being processed. The government continues to pursue under the Philippine Development Plan in bringing progress and higher quality of life for Filipinos. Given this, people are provided jobs to search in order to attain the goal that they have for the region. Agriculture and trade sectors continue to be the major factors of growth of the economy. This was driven by the strong production of sugarcane, banana, pineapple, tobacco, peanut, monggo, cassava, tomato, garlic, onion, eggplant, and rubber that Mindanao has.',
            'feature_keywords' => json_encode([
                'mindanao jobs', 
                'mindanao career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://news.abs-cbn.com/business/05/27/17/economy-remains-stable-despite-crisis-in-mindanao',
                    'http://mindanaotimes.net/davao-region-ranks-5th-in-economic-growth/',
                    'https://www.britannica.com/place/Mindanao'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 13,
            'feature_name' => 'Western Visayas',
            'feature_color' => '#CA3415',
            'feature_slug' => 'Jobs-In-Western-Visayas',
            'feature_title' => 'Western Visayas',
            'feature_type' => 'location', 
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/western-visayas.jpg",
            'feature_meta_title' =>  'Job opportunities in Western Visayas on Jobayan',
            'feature_meta_description' => 'Find all job listings in Western Visayas and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Western Visayas, designated as Region VI, consists of six provinces namely Aklan, Antique, Capiz, Guimaras, Iloilo, and Negros Occidental; and two highly urbanized cities, Bacolod and Iloilo. Due to rapid urbanization and increase of residential sector, the region is becoming more attractive. Its economy increased with 6.1% growth rate, yet slower compared to 2015. The economic activities of the private sector and the strong performance of the services sectors will continue to be the drivers and top contributors of growth. The employment in this region remains to be positive considering a good number of new part time jobs, while the Business Process Outsourcing remains to be an employment generator for the region.',
            'feature_keywords' => json_encode([
                'visayas jobs', 
                'visayas career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://nro6.neda.gov.ph/western-visayas-economy-grows-6-1-in-2016/',
                    'http://www.neda.gov.ph/2015/08/25/western-visayas-economy-accelerates-sectors-expand/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 14,
            'feature_name' => 'Cagayan De Oro',
            'feature_color' => '#239A7E',
            'feature_slug' => 'Jobs-In-Cdo',
            'feature_title' => 'Cagayan De Oro',
            'feature_type' => 'location',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/location/cdo.jpg",
            'feature_meta_title' =>  'Job opportunities in Cagayan De Oro on Jobayan',
            'feature_meta_description' => 'Find all job listings in Cagayan De Oro and get directly connected by clicking interested only on Jobayan',
            'feature_detail' => 'Cagayan de Oro is the regional center and the capital city of the province of Misamis Oriental and is considered to be the regional shopping center serving 5 provinces of Northern Mindanao. This brings a lot of opportunity in the region with the available job listing in different areas of the region. Many multi-national companies such as seaport and land transportation infrastructures and economic zones, air transportation facilities, and other economic support structures want to invest in Cagayan de Oro because of its stability of location, quality and quantity of labor force, and economic stability that support them. Since the region developed, Internet has experienced significant growth. Hence, more jobs as well in the companies of service providers.',
            'feature_keywords' => json_encode([
                'cdo jobs', 
                'cdo career', 
                'job search', 
                'job hiring', 
                'job listing'
            ]),
            'feature_links' => json_encode([
                    'http://cagayandeoro.gov.ph/',
                    'http://aboutcagayandeoro.com/economy-of-cagayan-de-oro-is-in-boom/',
                    'http://www.kagay-an.com/cagayan-de-oro-sparks-region-x-as-most-competitive-city-in-the-philippines/',
                    'http://www.boi.gov.ph/files/investment%20fact%20sheet/Cagayan%20De%20Oro.pdf'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 15,
            'feature_name' => 'Accounting and Finance',
            'feature_color' => '#CA1551',
            'feature_slug' => 'Accounting-Finance-Jobs',
            'feature_title' => 'Accounting and Finance',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/accounting-finance-jobs.jpg",
            'feature_meta_title' => 'Accounting and Finance Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to finance analysis, budget,
                collections, payroll, fraud, and protection',
            'feature_keywords' => json_encode([
                    'accounting jobs',
                    'finance jobs',
                    'accounting career',
                    'finance career',
                    'job hiring'
                ]),
            'feature_detail' => 'Accounting and Finance on Jobayan Accounting and Finance is mainly focused on the financial health of a business that  includes managing cash flows, risks, and value. It is also hugely  involved in the world of business and commerce. Some of the  responsibilities of an accountant and/or financier is to provide  information about the business\' finance by researching,  analyzing data, and collecting information about the business\'  current financial status to be able to produce balance sheets,  profit and loss statements, and other reports.  <br />  <br />  Explore more possibilities by taking a look at different financial jobs and accounting jobs that is currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://hiring.monster.com/hr/hr-best-practices/recruiting-hiring-advice/job-descriptions/accountant-job-description-sample.aspx',
                    'https://www.accounting-degree.org/what-is-accounting/',
                    'https://en.wikipedia.org/wiki/Finance'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 16,
            'feature_name' => 'Administration and Coordination',
            'feature_color' => '#FB4D3D',
            'feature_slug' => 'Administration-Coordination-Jobs',
            'feature_title' => 'Administration and Coordination',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/administration-coordination-jobs.jpg",
            'feature_meta_title' => 'Administration and Coordination Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs that require organizing and
                implementing projects or supporting general office functions',
            'feature_keywords' => json_encode([
                    'administration jobs',
                    'administration career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Administrative and Coordinator jobs are one of the most commonly listed job positions in the business, and employers can be found in a wide range of industries. Some of the responsibilities of a coordinator and/or someone in an administrative position is to provide office services, activities, and operations by executing admin systems, strategies, and policies. It is also their duty to create, revise, and maintain administrative systems, develop reporting procedures, and implementing cost reductions. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different administrative jobs and coordinator jobs that is currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://resources.workable.com/office-administrator-job-description',
                    'https://www.totaljobs.com/careers-advice/job-profile/admin-jobs'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 17,
            'feature_name' => 'Architecture and Engineering',
            'feature_color' => '#C54B7F',
            'feature_slug' => 'Architecture-Engineering-Jobs',
            'feature_title' => 'Architecture and Engineering',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/architecture-engineering-jobs.jpg",
            'feature_meta_title' => 'Architecture and Engineering Jobs on Jobayan',
            'feature_meta_description' => 'Jobs relating to designing framework or models
                and implementing innovations in complex systems and infrastructure',
            'feature_keywords' => json_encode([
                    'architecture jobs',
                    'architecture career',
                    'engineering jobs',
                    'engineering career',
                    'job hiring'
                ]),
            'feature_detail' => 'Architects and Engineers both works in the construction industry. The knowledge and understanding of engineers and architects is vastly important for project developments as they possess knowledge and disciplines that deals with the process of creating structures, such as buildings, airports, trains, churches and houses. <br /> <br /> Both architects and engineers have their own significant and critical role that are necessary in any construction jobs and they rely on one another to be able to perform and achieve a specific task. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different architectural jobs and engineering jobs that is currently being offered here in the Philippines.',             
				'feature_links' => json_encode([
					'https://www.snagajob.com/job-descriptions/engineer/',
                    'https://creativepool.com/articles/jobdescriptions/architect-job-description'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 18,
            'feature_name' => 'Customer Service',
            'feature_color' => '#239A7E',
            'feature_slug' => 'Customer-Service-Jobs',
            'feature_title' => 'Customer Service',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/customer-service-jobs.jpg",
            'feature_meta_title' => 'Customer Service Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to Customer Service providing information
                and resolving problems on product or service',
            'feature_keywords' => json_encode([
                    'customer service jobs',
                    'customer service career',
                    'customer support jobs',
                    'customer support career',
                    'job hiring'
                ]),
            'feature_detail' => 'Customer service representatives are the ones responsible in assisting customers before, during and after sales. Representatives usually give assistance to customers or the public for their complaints, orders, errors, account questions, billing, cancelations, and other queries. <br /> <br /> The specific duties of customer service representatives may vary depending on what kind of company they work for. Service representatives who work for utility and communication companies usually help customers with problems about their service, such as outages. Those who work in banks answer to customer queries about their accounts. Some representatives make changes to customers\' accounts, such as updating contact numbers, emails, or canceling their orders. Selling may not be their main responsibility, some representatives may help to generate sales leads while providing customers with information about a certain product or service. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different customer service jobs that is currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'http://www.americasjobexchange.com/customer-service-representative-job-description',
                    'https://www.topresume.com/career-advice/customer-service-representative-job-description'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 19,
            'feature_name' => 'Education and Training',
            'feature_color' => '#345995',
            'feature_slug' => 'Education-Training-Jobs',
            'feature_title' => 'Education and Training',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/education-training-jobs.jpg",
            'feature_meta_title' => 'Education and Training Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to design, development
                and deployment of curriculum or modules in private and public institutions',
            'feature_keywords' => json_encode([
                    'education jobs',
                    'education career',
                    'training jobs',
                    'training career',
                    'job hiring'
                ]),
            'feature_detail' => 'Education and Training jobs are mainly focused on teaching skills and knowledge to employees or students. They are focused on making plans, organizing and implementing it in an appropriate instructional program in a good learning environment that guides and supports employees to develop new skills for work or leisure or for student to fulfill their academic potential. It is their duty to provide or teach a various range of skill or knowledge to a specified unit in a company or a school. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different education jobs and training jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://www.totaljobs.com/careers-advice/job-profile/education-jobs/education-job-descriptions',
                    'https://www.snagajob.com/job-descriptions/teacher/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 20,
            'feature_name' => 'General Services',
            'feature_color' => '#434D70',
            'feature_slug' => 'General-Services-Jobs',
            'feature_title' => 'General Services',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/general-services-jobs.jpg",
            'feature_meta_title' => 'General Services Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to providing routine
                general services support or coordinating a variety of work',
            'feature_keywords' => json_encode([
                    'service jobs',
                    'service career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'General Services\' responsibilities are to provide a wide range of procedural services support such as clerical services, managing equipment, regular maintenance and/or repair, materials handling, logistical assistance, routine security and/or customer services, and/or other related duties in line with day-to-day requirements of the specified department. Some duties of the people in this industry is to carry out different materials related to such areas as shipping and receiving, warehousing and inventory control, and/or property management. They may be asked to operate different items of light and/or heavy equipment as well for routine site maintenance, removal, and/or repair activities. </br /> </br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different general service jobs that is currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://www.carteretcountync.gov/DocumentCenter/Home/View/1933'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 21,
            'feature_name' => 'Health and Medical',
            'feature_color' => '#888800',
            'feature_slug' => 'Health-Medical-Jobs',
            'feature_title' => 'Health and Medical',
            'feature_type' => 'position',
            'feature_image'=> "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/health-medical-jobs.jpg",
            'feature_meta_title' => 'Health and Medical Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to promotion of wellness
                and overall health of the public',
            'feature_keywords' => json_encode([
                    'health care jobs',
                    'health care career',
                    'medical jobs',
                    'medical career',
                    'job hiring'
                ]),
            'feature_detail' => 'Health and Medical professionals are one of the in demand everywhere in the world. Their knowledge extent from the diagnosis of medical conditions, disorders, diseases, to preventative health habits and cosmetic care. Their specific responsibilities differ greatly depending on their area of specialism. Some of the generic duties of the job are undertaking patients\' consultations and physical examinations, providing general pre- and post-operative care, perform surgical procedures, and collaborate daily with co-staffs including doctors, non-medical staff, and other healthcare professionals to provide patients with exceptional care. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different health and medical jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://www.mightyrecruiter.com/job-descriptions/healthcare-support-workers/',
                    'https://resources.workable.com/job-descriptions/healthcare-job-descriptions/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 22,
            'feature_name' => 'Hospitality and Tourism',
            'feature_color' => '#410B13',
            'feature_slug' => 'Hospitality-Tourism-Jobs',
            'feature_title' => 'Hospitality and Tourism',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/hospitality-tourism-jobs.jpg",
            'feature_meta_title' => 'Hospitality and Tourism Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to food and services in
                restaurants, resorts and hotels and catering businesses',
            'feature_keywords' => json_encode([
                    'hospitality jobs',
                    'hospitality career',
                    'tourism jobs',
                    'tourism career',
                    'job hiring'
                ]),
            'feature_detail' => 'Hospitality and Tourism professionals are one of the essentials in all businesses. Every organization or business needs to keep their customer or members happy. It is one of their responsibilities to keep every one engaged for the business to develop and prosper. Numerous types of hospitality and tourism careers are responsible in managing the day-to-day operations of an establishment including staff administration, budget reviews and ensuring guests\' comfort and satisfaction. This industry deals with other areas such as retail shops, fitness centers, spas, etc. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different hospitality and tourism jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://en.wikipedia.org/wiki/Hospitality_service',
                    'https://resources.workable.com/hospitality-manager-job-description',
                    'https://www.linkedin.com/pulse/20141016155813-131795743-what-do-the-words-hospitality-and-customer-service-mean-to-you/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 23,
            'feature_name' => 'Human Resources',
            'feature_color' => '#FF7400',
            'feature_slug' => 'Human-Resources-Jobs',
            'feature_title' => 'Human Resources',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/human-resources-jobs.jpg",
            'feature_meta_title' => 'Human Resources Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to employee selection,
                on-boarding, payroll and other HR functions',
            'feature_keywords' => json_encode([
                    'hr jobs',
                    'hr career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Human Resource professionals deals with one of the most valuable assets of any company, its people. Some of their responsibilities are recruiting, screening, interviewing and placing new hires; counseling professionals on candidate selection, analyzing and conducting exit interviews. They may also operate employee relations, payroll, maintain and inform employee about their benefits program and trainings. Some of their daily duties include explaining human resources policies, procedures, laws, and standards to new and existing employees. They are also the one who informs employees about their job duties, schedules, working conditions, promotion opportunities, etc. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different human resource jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://www.ziprecruiter.com/blog/human-resources-specialist-job-description-sample-template/',
                    'https://resources.workable.com/hr-specialist-job-description'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 24,
            'feature_name' => 'IT and Software',
            'feature_color' => '#483C43',
            'feature_slug' => 'It-Software-Jobs',
            'feature_title' => 'IT and Software',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/it-software-jobs.jpg",
            'feature_meta_title' => 'IT and Software Jobs on Jobayan',
            'feature_meta_description' => 'Find all Jobs relating to administration,
                creation and maintenance of computer or software',
            'feature_keywords' => json_encode([
                    'it jobs',
                    'it career',
                    'programming jobs',
                    'programming career',
                    'job hiring'
                ]),
            'feature_detail' => 'IT and Programming professionals are essential to the modern economics and everyday life. Developers, programmers and software engineers are no longer unique to the tech companies. They are now at work in all areas of the economy. Information Technology specialists mainly design, operate and/or maintain technology products. It is their responsibility to manage networks, software development and database administration. They may also provide technical support to business\' employees and train non-technical employees about their business\' information systems. Programmers on the other hand, is focused on writing codes to create software programs. Through the use of various computer languages such as C++ and Java. Overall, it is their duty to write codes and operate it into a language the computer can understand. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different customer IT and programming jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'http://www.modis.com/clients/salary-guide/job-categories/',
                    'https://www.roberthalf.com/positions-we-place/technology-roles',
                    'https://www.indeed.com/hire/job-description/computer-programmer',
                    'https://www.truity.com/career-profile/computer-programmer'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 25,
            'feature_name' => 'Legal Services',
            'feature_color' => '#1A8A00',
            'feature_slug' => 'Legal-Services-Jobs',
            'feature_title' => 'Legal Services',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/legal-services-jobs.jpg",
            'feature_meta_title' => 'Legal Services Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to providing legal
                services and assistance',
            'feature_keywords' => json_encode([
                    'legal jobs',
                    'legal career',
                    'legal services jobs',
                    'legal services career',
                    'job hiring'
                ]),
            'feature_detail' => 'Legal Services professionals such as Lawyers are the ones who provides advise clients about business transactions, claim liability, lawsuits, or legal right and obligations; Interpret laws, rulings and regulations for individuals and businesses; Counsel to senior management and officers regarding business contracts, government requirements, and trademark protections or intellectual property. Paralegals on the other hand, are involved in legal works, and are sourced for both public and private sectors. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different legal services jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://job-descriptions.careerplanner.com/Lawyers.cfm',
                    'https://targetjobs.co.uk/careers-advice/job-descriptions/280559-legal-executive-job-description',
                    'https://www.truity.com/career-profile/paralegal-or-legal-assistant',
                    'https://www.totaljobs.com/careers-advice/job-profile/legal-jobs/paralegal-job-description'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 26,
            'feature_name' => 'Management and Consultancy',
            'feature_color' => '#1B8394',
            'feature_slug' => 'Management-Consultancy-Jobs',
            'feature_title' => 'Management and Consultancy',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/management-consultancy-jobs.jpg",
            'feature_meta_title' => 'Management and Consultancy Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to providing expertise in
                developing and implementing processes to improve business performance',
            'feature_keywords' => json_encode([
                    'management jobs',
                    'management career',
                    'consultant jobs',
                    'consultant career',
                    'job hiring'
                ]),
            'feature_detail' => 'Management industry professionals are responsible for planning, directing, and overseeing operations and financial status of a corporation or a business unit, department, area, or an operating unit within a company. Their duty also covers planning and maintaining work systems, procedures, and polices that allow and motivate people to reach the most advantageous performance. <br /> <br /> Consultancy industry professionals are the one who provides analysis of the current culture or practices of an organization and make recommendations for advancements. These professionals regularly specialize in one area of business, such as human resources. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different management & consultancy jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://resources.workable.com/business-manager-job-description',
                    'https://www.snagajob.com/job-descriptions/consultant/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 27,
            'feature_name' => 'Manufacturing and Production',
            'feature_color' => '#874122',
            'feature_slug' => 'Manufacturing-Production-Jobs',
            'feature_title' => 'Manufacturing and Production',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/manufacturing-production-jobs.jpg",
            'feature_meta_title' => 'Manufacturing and Production Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to processing materials or
                components such as metal, mineral, wood, rubber, textile or food into a product',
            'feature_keywords' => json_encode([
                    'manufacturing jobs',
                    'manufacturing career',
                    'production jobs',
                    'production career',
                    'job hiring'
                ]),
            'feature_detail' => 'Manufacturing professionals deals with raw materials. They transform it with the use of machineries to make it into a ready good. Manufacturing professionals are usually the one operating and maintaining machineries in a warehouse or a factory. They are also involved with the preparation of items for distribution, assembling and checking product parts and guarantee that all equipment runs smoothly and assisting in the shipment of items. Production professionals on the other hand, mainly focus on transforming available materials into finished products. They may use these materials with or without the help of machineries. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different manufacturing and production jobs that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://job-descriptions.careerplanner.com/Manufacturing-Production-Technician.cfm',
                    'https://www.thebalance.com/manufacturing-job-titles-2061501',
                    'https://www.indeed.com/hire/job-description/production-worker'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 28,
            'feature_name' => 'Media and Creatives',
            'feature_color' => '#CA3415',
            'feature_slug' => 'Media-Creatives-Jobs',
            'feature_title' => 'Media and Creatives',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/media-creatives-jobs.jpg",
            'feature_meta_title' => 'Media and Creatives Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to designing and delivering
                messages via different forms of media and managing client public relations',
            'feature_keywords' => json_encode([
                    'media jobs',
                    'media career',
                    'creative jobs',
                    'creative career',
                    'job hiring'
                ]),
            'feature_detail' => 'Media and the creatives industry is easily the most contested professions around. With hundreds of entry-level jobs available. It regularly attracts thousands of applicants. One of the most popular jobs in this industry is journalism. Journalists discover different things and share it to the world. Another popular job that is essential to this day and age are the creative professionals behind the website designs or TV shows. Some of their responsibilities may include creating and designing logos, marketing collaterals, drawing and animation, and visual effects. <br /> <br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different jobs in media and creatives that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://resources.workable.com/job-descriptions/media-job-descriptions/',
                    'http://www.paladinstaff.com/jobs/careers/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 29,
            'feature_name' => 'Safety and Security',
            'feature_color' => '#6500C4',
            'feature_slug' => 'Safety-Security-Jobs',
            'feature_title' => 'Safety and Security',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/safety-security-jobs.jpg",
            'feature_meta_title' => 'Safety and Security Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to enforcing laws and
                implementing programs to protect and maintain public, environmental safety',
            'feature_keywords' => json_encode([
                    'safety jobs',
                    'safety career',
                    'security jobs',
                    'security career',
                    'job hiring'
                ]),
            'feature_detail' => 'Safety & Security professionals from police officers and security guards to private investigators take on some of the toughest roles in any society. Professionals from this industry maintains safe and secure environment for customers and employees by patrolling and monitoring premises and personnel. Some of the responsibilities of people in this industry is to observe for signs of crime, controls traffic by directing drivers, prevents loses and damage by reporting irregularities and unusual occurrences to the management/employers, and perform first aid or CPR. <br /><br /> There is a wide range of positions available in the employment market! Explore more possibilities by taking a look at different jobs in safety and security that are currently being offered here in the Philippines.',
            'feature_links' => json_encode([
                    'https://resources.workable.com/security-guard-job-description',
                    'https://www.ziprecruiter.com/blog/security-guard-job-description-sample-template/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 30,
            'feature_name' => 'Sales and Marketing',
            'feature_color' => '#A50104',
            'feature_slug' => 'Sales-Marketing-Jobs',
            'feature_title' => 'Sales and Marketing',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/sales-marketing-jobs.jpg",
            'feature_meta_title' => 'Sales and Marketing Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to promoting a product, service or idea, acquiring new customers while growing business with existing ones',
            'feature_keywords' => json_encode([
                    'sales jobs',
                    'sales career',
                    'marketing jobs',
                    'marketing career',
                    'job hiring'
                ]),
            'feature_detail' => 'Sales and Marketing has a substantial role in every business. The roles reflect the strength of the products or brand in achieving the sales performance required by the company. When roles are defined, targets are achieved and objectives are taken. People in marketing position help companies build their image, sell products, and run promotions on different media platforms. This kind of job is needed nearly all industries because it helps develop strategies, communications, client relationships, and manage product or brand. Given this, more opportunities are open for this line of job especially today when more job titles are available due to explosion of platforms in the Internet. These positions include SEO Manager, Social Media Editor or Social Media Manager.',
            'feature_links' => json_encode([
                    'https://www.thebalance.com/marketing-job-titles-2061535',
                    'http://smallbusiness.chron.com/roles-responsibilities-sales-marketing-team-65580.html '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 31,
            'feature_name' => 'Science',
            'feature_color' => '#494949',
            'feature_slug' => 'Science-Jobs',
            'feature_title' => 'Science',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/science-jobs.jpg",
            'feature_meta_title' => 'Science Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs requiring expertise in the sciences for the purpose of education, research and development',
            'feature_keywords' => json_encode([
                    'science jobs',
                    'science career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Job positions that involve science are very much significant because how would a person imagine a world without science. There are diseases, technology, and environment that need to be understood. The people who work in science careers are responsible for these for they could explain the activities in the world scientifically. Jobs in science are categorized as academic, government, and industrial, and so there are many job opportunities in this sector. For job seekers who are more advanced, they may consider the high paying jobs for scientists. Those include the positions of Biochemist or Biophysicist, Chemist, Conservationist, Environmental Scientist, Environmental Science and Protection Technician, Forensic Scientist, Geoscientist, Hydrologist, and Medical Scientist.',
            'feature_links' => json_encode([
                    'https://www.thebalance.com/science-job-titles-2061506',
                    'https://www.thebalance.com/science-careers-525645'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
                
        ],
        [
            'feature_id' => 32,
            'feature_name' => 'Skilled Trade',
            'feature_color' => '#E54B4B',
            'feature_slug' => 'Skilled-Trade-Jobs',
            'feature_title' => 'Skilled Trade',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/skilled-trade-jobs.jpg",
            'feature_meta_title' => 'Skilled Trade Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to finance analysis, budget, collections, payroll, fraud, and protection.',
            'feature_keywords' => json_encode([
                    'trade jobs',
                    'skill trade career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Skilled trades specialize in occupation that requires work experience but not necessarily a bachelor\'s degree. According to Department of Labor and Employment, they continue to study the supply of skilled workers in the country to prevent shortage. For job seekers, it is necessary to develop and improve sufficient skills in order to get a job in the country and not compromise the deployment of skilled workers abroad. Due to availability of job positions in the country, there are a lot of opportunities for the seekers to get hired for skilled trade. The job positions that experience shortage but could improve include Environmental Planner, Fisheries, Technologist, Geologist, Guidance Counselor, Librarian (licensed), Medical Technologist, Sanitary Engineer, Computer Numerical Control Machinist, Assembly Technician (Servo-actuator/ Valve), Test Technician, Pilot, and Aircraft Mechanic.',
            'feature_links' => json_encode([
                    'http://tucp.org.ph/2014/01/which-occupations-in-the-philippines-lack-skilled-workers/',
                    'http://beta.philstar.com/business/2017/05/14/1699993/dole-cites-tightening-supply-skilled-workers',
                    'http://business.inquirer.net/237457/shortage-skilled-labor'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 33,
            'feature_name' => 'Supply Chain',
            'feature_color' => '#012622',
            'feature_slug' => 'Supply-Chain-Jobs',
            'feature_title' => 'Supply Chain',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/supply-chain-jobs.jpg",
            'feature_meta_title' => 'Supply Chain Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to procurement, logistics and delivery of goods dometically or internationally',
            'feature_keywords' => json_encode([
                    'supply chain jobs',
                    'supply chain career',
                    'logistics jobs',
                    'logistics career',
                    'job hiring'
                ]),
            'feature_detail' => 'Supply chain is a process that overviews the consumer product life cycle from sourcing raw materials to delivery in different points of sale. It is essential in this industry to satisfy the needs of the consumers. Therefore, more jobs exist for this position in order to carry those needs. People who seek job must consider its fast-paced and challenging work in the industry because of the high volume and speed of production. The industry requires careful management to ensure quality and consistency of the products. Being able to work in a team must also be considered since there are communications and contacts with people overseas. Therefore, it is essential for workers to be skillful to build career in Supply Chain.',
            'feature_links' => json_encode([
                    'http://www.supplychain247.com/article/job_description_and_salary_supply_chain_management/',
                    'https://targetjobs.co.uk/career-sectors/consumer-goods-and-fmcg/advice/288665-supply-chain-area-of-work',
                    'http://www.sgs.ph/en/training-services/supply-chain-and-manufacturing/supply-chain-training '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 34,
            'feature_name' => 'Writing and Content',
            'feature_color' => '#2E4052',
            'feature_slug' => 'Writing-Content-Jobs',
            'feature_title' => 'Writing and Content',
            'feature_type' => 'position',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/writing-content-jobs.jpg",
            'feature_meta_title' => 'Writing and Content Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to developing content based on market interest or a specific subject matter',
            'feature_keywords' => json_encode([
                    'writing jobs',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Content writing is providing relevant information about products and services. There are many opportunities in this position especially now that more businesses consider websites or online works to build their company and relying more in content marketing. However, it takes skills to be able to work in this position since people today learn more about the products and services through what they read, therefore a content writer must be able to convince or at least capture the attention of the consumers through writing. There are tone and styles that get the perfect writing, as well as content that engage consumers. Job seekers who are passionate about these could consider this work for freelance and build career around it.',
            'feature_links' => json_encode([
                    'https://www.freelancer.ph/jobs/content-writing/'
                ]), 
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 35,
            'feature_name' => 'Agriculture and Mining',
            'feature_color' => '#CA1551',
            'feature_slug' => 'Jobs-In-Agriculture-Mining',
            'feature_title' => 'Agriculture and Mining',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/agriculture-mining.jpg",
            'feature_meta_title' => 'Agriculture and Mining Jobs on Jobayan',
            'feature_meta_description' => 'Find all agriculture and mining jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'agriculture jobs',
                    'mining jobs',
                    'agriculture career',
                    'mining career',
                    'job search'
                ]),
            'feature_detail' => 'Agriculture and mining are linked through mined inputs, land and water resources, and workers, and is evidently growing in some areas as a result of mining. Agriculture was able to increase in growth with a percentage of 5.28% in the first quarter of 2017 after several declines on the previous quarters. Workers in the agriculture sector comprised the second largest group with 25.5% of the total employed in January 2017, while Industry sector made the smallest group with 17.4% of the total employed. Among the occupation groups, skilled agricultural, forestry, and fishery worker made up 13.4% of the total employed in January 2017. This gives more job opportunity in the industry that shows development but continues to improve in the country.',
            'feature_links' => json_encode([
                    'https://psa.gov.ph/content/performance-philippine-agriculture-january-march-2017',
                    'https://psa.gov.ph/content/employment-rate-january-2017-estimated-934-percent',
                    'http://www.miningfacts.org/economy/how-does-large-scale-mining-affect-agriculture/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 36,
            'feature_name' => 'BPO and Call Center',
            'feature_color' => '#FB4D3D',
            'feature_slug' => 'Jobs-In-Bpo',
            'feature_title' => 'BPO and Call Center',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/bpo.jpg",
            'feature_meta_title' => 'BPO and Call Center Jobs on Jobayan',
            'feature_meta_description' => 'Find all BPO and call center jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'bpo jobs',
                    'call center jobs',
                    'bpo career',
                    'call center career',
                    'job search'
                ]),
            'feature_detail' => 'Business Process Outsourcing is a subset of outsourcing that involves a particular business to a third-party service provider. Its growth in the Philippines is phenomenal. For more than a decade, the industry has grown massively with 20% per year. In 2015, the Philippines has already hit 1.2 million employees in BPO industry. In 2016, the BPO industry was able to reach revenue of more that $22 Billion. It continues to expand and could become one of the main drivers of the country\'s economy. They are even expanding outside Metro Manila, and so this gives more Filipino jobs in the industry especially that they have shifted to multi-channel offering from pure voice services.',
            'feature_links' => json_encode([
                    'http://www.hpoutsourcinginc.com/how-big-is-the-philippines-outsourcing-industry/',
                    'https://www.taskus.com/glossary/business-process-outsourcing-bpo/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 37,
            'feature_name' => 'Construction',
            'feature_color' => '#C54B7F',
            'feature_slug' => 'Jobs-In-Construction',
            'feature_title' => 'Construction',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/construction.jpg",
            'feature_meta_title' => 'Construction Jobs on Jobayan',
            'feature_meta_description' => 'Find all construction jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'construction jobs',
                    'construction career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Construction industry plays an important part in the Philippines\' economic growth. Its annual growth rate is 12% during the period of 2012-2016, and continues to grow over the forecast period 2017-2021. This growth will be supported by the Philippine development plan. Its share of total employment in the country is seen growing, and expected to employ around 5.8 million workers by 2022. The construction industry continues to be one of the biggest contributors to the employment in the country. The strong performance of construction industry is the reason for the growth of industry sector in general. This gives people the opportunity to search job in this sector because of the positive growth it contributes to the economy.',
            'feature_links' => json_encode([
                    'http://beta.philstar.com/business/2017/04/24/1688252/construction-seen-fuel-phl-growth#Ce4jH8AbIDV4KCFA.99',
                    'https://www.medgadget.com/2017/10/construction-in-philippines-market-2017-current-and-future-plans.html '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 38,
            'feature_name' => 'Finance and Insurance',
            'feature_color' => '#239A7E',
            'feature_slug' => 'Jobs-In-Finance-Insurance',
            'feature_title' => 'Finance and Insurance',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/finance-insurance.jpg",
            'feature_meta_title' => 'Finance and Insurance Jobs on Jobayan',
            'feature_meta_description' => 'Find all finance and insurance jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'finance jobs',
                    'finance career',
                    'insurance jobs',
                    'insurance career',
                    'job search'
                ]),
            'feature_detail' => 'Philippine banking industry or the entire banking sector is supervised by the Central bank of the Philippines. This industry always plays an important role in sustaining the growth of the country\'s economy. The country\'s banking sector is at an early stage of development. It is expected to continue growing this year and the coming years by its low interest rates, asset quality, and profitability. The Philippine banks are expected to continue expanding with loan growth that is about to hit 15% to 17%. In terms of its employment, Banking/Finance is one of the highest paying jobs/industries in the country for Managers/Assistant Managers, Supervisors, and Junior Executives, bringing more opportunity for people to build career in this industry.',
            'feature_links' => json_encode([
                    'http://bworldonline.com/phl-banks-continue-expanding-2017-2018/',
                    'https://www.businesswire.com/news/home/20171005006089/en/Philippines-Banking-Sector-Report-20172018---Research',
                    'http://www.thesummitexpress.com/2017/02/top-10-highest-paying-jobs-in-the-philippines-2017.html'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 39,
            'feature_name' => 'Health Care',
            'feature_color' => '#345995',
            'feature_slug' => 'Jobs-In-Healthcare',
            'feature_title' => 'Health Care',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/healthcare.jpg",
            'feature_meta_title' => 'Health Care Jobs on Jobayan',
            'feature_meta_description' => 'Find all health care jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'health care jobs',
                    'health care career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Healthcare industry is an integration and aggregation of sectors that provides goods and services for patients. Cost of hospital care hadn\'t been going down and so it remains sustainable in terms of business; and over the last decade, the healthcare
                business has changed for the better. The industry helps in the
                growth of outsourcing industry; especially there are 120,000 full
                time employees in this sector. This makes the job search in the
                country better for its contribution to the economic status as
                well as the rise of the other sectors. As the healthcare industry
                continues to grow, more jobs in this sector are offered for
                people to be given opportunity to work in the country.',
            'feature_links' => json_encode([
                    'http://beta.philstar.com/business/2017/09/19/1740691/healthcare-business',
                    'https://www.prnewswire.com/news-releases/philippines-hospitals-market-2017-2021---increasing-penetration-of-health-insurance-to-drive-the-market-300505329.html',
                    'http://news.abs-cbn.com/business/10/23/17/healthcare-demand-seen-to-drive-bpo-growth'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 40,
            'feature_name' => 'Hospitality',
            'feature_color' => '#434D70',
            'feature_slug' => 'Jobs-In-Hospitality',
            'feature_title' => 'Hospitality',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/hospitality.jpg",
            'feature_meta_title' => 'Hospitality Jobs on Jobayan',
            'feature_meta_description' => 'Find all hospitality jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'hospitality jobs',
                    'hospitality career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Hospitality industry is a broad category of service industry and tourism industry. For the early months of 2017, visitor arrivals had an increased of 19.60% compared to the previous year; leading to the increased demand for hotel rooms to meet the needs of the tourists. Certain occupations with the industry get higher demand as well like waiters and waitresses. With this, the performance growth of the industry was expected to rise by 2017, and to lead up by the year 2021. Indeed, 2017 was a great year for the hospitality industry. The rise of tourism and the domino effect it has on the hotel gives job seekers the opportunity to build their career in hospitality.',
            'feature_links' => json_encode([
                    'http://www.tourism.gov.ph/pages/industryperformance.aspx',
                    'http://www.jll.com.ph/philippines/en-gb/news/360/rp-hotel-hospitality-industry-seen-to-flourish-as-tourism-spikes-in-q1-2017%E2%80%8B',
                    'http://www.lgcassociates.com/2017/02/01/2017-hospitality-industry-employment-forecast/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 41,
            'feature_name' => 'Human Resources',
            'feature_color' => '#888800',
            'feature_slug' => 'Jobs-In-Human-Resources',
            'feature_title' => 'Human Resources',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/human-resources.jpg",
            'feature_meta_title' => 'Human Resources Jobs on Jobayan',
            'feature_meta_description' => 'Find all human resources jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'human resources jobs',
                    'human resources career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Human resource is the central part of every organization. It manages all aspects related to its personnel. That includes duties to maximize the satisfaction of a business and its employees with their jobs. Because more and more companies are outsourcing their HR department because of its cost savings, outsourcing human resources functions to the Philippines is becoming a trend. The people in the industry require certain skills that can manage the employees of a company. Given this, job seekers who are seeking to build career in this industry must know the importance of their job and that almost every company needs human resource person to manage its people. That gives more demand to work in the industry.',
            'feature_links' => json_encode([
                    'http://www.vault.com/industries-professions/industries/human-resources.aspx',
                    'http://www.staffvirtual.com/outsourcing-human-resources-to-the-philippines/',
                    'https://www.payscale.com/research/PH/Job=Human_Resources_(HR)_Officer/Salary'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 42,
            'feature_name' => 'Logistics',
            'feature_color' => '#410B13',
            'feature_slug' => 'Jobs-In-Logistics',
            'feature_title' => 'Logistics',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/logistics.jpg",
            'feature_meta_title' => 'Logistics Jobs on Jobayan',
            'feature_meta_description' => 'Find all logistics jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'logistics jobs',
                    'logistics career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Logistics is the flow of management in order to meet the requirements of customers or corporations. According to the article of Business Mirror, the Philippines had the highest logistics cost as a percentage of sales at 27.16%. This was broken down to transportation, warehousing, inventory carrying, and logistics administration. The percentage is compared to Thailand with the lowest, Vietnam, and Indonesia. In terms of the ranking from the previous years like in 2016 World Bank\'s Logistics Performance Index, the Philippines ranked 71 among 160 countries with a score of 2.86 in World Bank\'s logistics report. Philippines, together with other ASEAN countries remain competitive. Since logistics is a detailed organization and implementation of a complex operation, people seeking job from the industry must have enough skill to manage the operation.',
            'feature_links' => json_encode([
                    'https://businessmirror.com.ph/high-logistics-cost-in-philippines/',
                    'http://www.sunstar.com.ph/cebu/business/2017/04/21/assessing-phs-logistics-sector-537547'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 43,
            'feature_name' => 'Work Abroad and POEA',
            'feature_color' => '#CA3415',
            'feature_slug' => 'Jobs-In-Poea',
            'feature_title' => 'Work Abroad and POEA',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/poea.jpg",
            'feature_meta_title' => 'Work Abroad and POEA Jobs on Jobayan',
            'feature_meta_description' => 'Find all work abroad and POEA jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'work abroad jobs',
                    'poea jobs',
                    'work abroad career',
                    'poea career',
                    'job search'
                ]),
            'feature_detail' => 'Filipinos working abroad, according to 2016 data, has already reached around 2.2 million. The biggest share of OFWs comes from CALABARZON, followed by National Capital Region, Central Luzon, and then Cordillera as the least sharer. In this industry, more females are employed abroad than males; and those females are generally young ranging 25-39 years old while males are at an average of 45 years old. The leading destinations to work abroad are the countries in Middle East, specifically Saudi Arabia, United Arab Emirates, Kuwait, and Qatar. Because of the opportunities that were given to OFWs, they were able to remit a total of 203 Billion pesos even for few months in 2016. With all these, some Filipinos in the country continue to seek opportunity abroad to earn more, as well as build career outside the country.',
            'feature_links' => json_encode([
                    'https://psa.gov.ph/statistics/survey/labor-and-employment/survey-overseas-filipinos'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 44,
            'feature_name' => 'Real Estate',
            'feature_color' => '#FF7400',
            'feature_slug' => 'Jobs-In-Real-Estate',
            'feature_title' => 'Real Estate',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/real-estate.jpg",
            'feature_meta_title' => 'Real Estate Jobs on Jobayan',
            'feature_meta_description' => 'Find all real estate jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'real estate jobs',
                    'real estate career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Real Estate is the air rights above the land and underground rights below the land. This includes the property, land, and building. Real estate is all about location; hence, the affordability for the market. The industry has a lot of employment opportunities to build great careers, with the ample challenges yet highly rewarding in the end. Some job seekers should also consider getting into this industry for it continues to soar in the coming years and the growth is expected to sustain. As long as the person who works for this industry has the ability to create contacts to solidify the networking that can build strong relationship with the clients, the business can survive.',
            'feature_links' => json_encode([
                    'http://business.inquirer.net/222573/real-estate-prospects-2017',
                    'https://www.thebalance.com/real-estate-what-it-is-and-how-it-works-3305882',
                    'https://www.workitdaily.com/real-estate-sector-career-benefits/'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 45,
            'feature_name' => 'Restaurant',
            'feature_color' => '#483C43',
            'feature_slug' => 'Jobs-In-Restaurant',
            'feature_title' => 'Restaurant',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/restaurants.jpg",
            'feature_meta_title' => 'Restaurant Jobs on Jobayan',
            'feature_meta_description' => 'Find all real estate jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'restaurant jobs',
                    'restaurant career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Restaurant Industry provides food and drink such as fast food establishments, cafés and coffee shops, mainstream restaurants, and fine dining. This industry is one of the fastest growing businesses because of the growing demand for convenience that has led them to expand. The entry of foreign-branded restaurants also contributed to the growth of the industry for it provides opportunities for exports of food and beverage products to the Philippines. The expansion continues as more and more hotels and shopping malls are opened throughout the country. This allows more opportunities for hiring to job seekers who want to work in the industry because of its continuous growth; hence, the availability of hotels and restaurants in the country.',
            'feature_links' => json_encode([
                    'https://business.mb.com.ph/2017/01/01/restaurant-business-as-sunrise-sector/',
                    'http://www.ifexphilippines.com/en/General-Info/Philippine-Food-Industry',
                    'https://warwick.ac.uk/fac/soc/ier/ngrf/lmifuturetrends/sectorscovered/hospitality/sectorinfo/subsectors/restaurant/ '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 46,
            'feature_name' => 'Retail',
            'feature_color' => '#1A8A00',
            'feature_slug' => 'Jobs-In-Retail',
            'feature_title' => 'Retail',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/retail.jpg",
            'feature_meta_title' => 'Retail Jobs on Jobayan',
            'feature_meta_description' => 'Find all retail jobs and get directly
                connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                'retail jobs',
                'retail career',
                'job search',
                'job hiring',
                'job listing'
            ]),
            'feature_detail' => 'Retail industry is where employees are the face of the brand through services to customers, boosting the customer experience through them. These employees have become an asset that drives a number of retail trends. Its growth comes from the strong consumer demand and economic expansion in the country. Other more signs of rising retail online hiring trends is becoming the largest online recruitment of any sector, followed by business process outsourcing. The SM, Ayala Group, and JG Summit are just 3 of the business groups that have built empires on retail; and their malls bring more opportunity for people seeking employment, as well as for businesses of this sector to grow.',
            'feature_links' => json_encode([
                    'https://www2.deloitte.com/us/en/pages/consumer-business/articles/retail-distribution-industry-outlook.html',
                    'http://www.bworldonline.com/content.php?section=Economy&title=positive-outlook-for-retail-in-the-philippines-&id=147901',
                    'https://www.rappler.com/business/178607-philippines-retail-industry-ecommerce',
                    'https://q2hrs.com/excellent-employee-experience-drives-retail-industry/ '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 47,
            'feature_name' => 'Start Up',
            'feature_color' => '#1B8394',
            'feature_slug' => 'Jobs-In-Startup',
            'feature_title' => 'Start Up',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/startup.jpg",
            'feature_meta_title' => 'Start Up Jobs on Jobayan',
            'feature_meta_description' => 'Find all start up jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                'start up jobs',
                'start up career',
                'job search',
                'job hiring',
                'job listing'
            ]),
            'feature_detail' => 'Startup industry is typically a newly emerged, fast-growing business that is built around innovative products, services, processes, or platforms. The startup culture is starting to grow in the country, building opportunities and training for young entrepreneurs. According to some entrepreneurs and investors, the Philippines could be the next startup hub and investment hub in Asia since more Venture Capitalists from Singapore, Japan, and the US are entering the country. Filipino startups are expanding regionally. This gives entrepreneurs the motivation to seek opportunities for growth. The job seekers who are aiming to work in this industry could also grab the kind of opportunity to grow in their career together with a startup company. ',
            'feature_links' => json_encode([
                    'https://www.kalibrr.com/discover/industry/startupph',
                    'https://manilarecruitment.com/manila-recruitment-articles-advice/philippines-next-startup-hub-asia/ '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 48,
            'feature_name' => 'Technology',
            'feature_color' => '#874122',
            'feature_slug' => 'Jobs-In-Tech',
            'feature_title' => 'Technology',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/industry/tech.jpg",
            'feature_meta_title' => 'Technology Jobs on Jobayan',
            'feature_meta_description' => 'Find all tech jobs and get directly connected by clicking interested only on Jobayan',
            'feature_keywords' => json_encode([
                    'tech jobs',
                    'tech career',
                    'job search',
                    'job hiring',
                    'job listing'
                ]),
            'feature_detail' => 'Technology industry is the category relating to research, development of technologically based goods and services. This sector revolves around the manufacturing of electronics, software, computers, products, and services relating to information technology. The Philippines is becoming much advanced in terms of tech startup ecosystem, following the footsteps of Singapore. Not only Filipinos consuming much technology that even dubbed them as the texting capital, social media capital, and now made to the list of selfie capitals of the world; the country have also brought creators and innovators from hundred thousands of graduates of information technology. This brings a lot of opportunity for job seekers to grow in their career in technology industry for the positive innovations they bring to the country.',
            'feature_links' => json_encode([
                    'https://www.rappler.com/brandrap/tech-and-innovation/142409-filipino-tech-innovations',
                    'https://manilarecruitment.com/manila-recruitment-articles-advice/philippines-tech-startup-community-growing/',
                    'http://beta.philstar.com/business/2017/12/03/1764997/employment-and-new-technology',
                    'https://www.investopedia.com/terms/t/technology_sector.asp'
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ],
        [
            'feature_id' => 49,
            'feature_name' => 'Sales and Marketing',
            'feature_color' => '#A50104',
            'feature_slug' => 'Sales-Marketing-Jobs',
            'feature_title' => 'Communications',
            'feature_type' => 'industry',
            'feature_image' => "https://s3-ap-southeast-1.amazonaws.com/jobayan/images/featured/position/sales-marketing-jobs.jpg",
            'feature_meta_title' => 'Sales and Marketing Jobs on Jobayan',
            'feature_meta_description' => 'Find all jobs relating to promoting a product, service or idea, acquiring new customers while growing business with existing ones',
            'feature_keywords' => json_encode([
                    'sales jobs',
                    'sales career',
                    'marketing jobs',
                    'marketing career',
                    'job hiring'
                ]),
            'feature_detail' => 'Sales and Marketing has a substantial role in every business. The roles reflect the strength of the products or brand in achieving the sales performance required by the company. When roles are defined, targets are achieved and objectives are taken. People in marketing position help companies build their image, sell products, and run promotions on different media platforms. This kind of job is needed nearly all industries because it helps develop strategies, communications, client relationships, and manage product or brand. Given this, more opportunities are open for this line of job especially today when more job titles are available due to explosion of platforms in the Internet. These positions include SEO Manager, Social Media Editor or Social Media Manager.',
            'feature_links' => json_encode([
                    'https://www.thebalance.com/marketing-job-titles-2061535',
                    'http://smallbusiness.chron.com/roles-responsibilities-sales-marketing-team-65580.html '
                ]),
            'feature_created' => date('Y-m-d h:i:s'),
            'feature_updated' => date('Y-m-d h:i:s')
        ]
    ]
];