<?php 

return [
    'client' => [
        'name'                 => 'Test Island',
        'abbrev'               => 'ZZ',
        'show_skill_key_steps' => false,
        // testmaster data imported into state, searching before this date will be in legacy region
        'data_cutoff'          => '1900-01-01'
    ],

    // The "call this number for help" phone number
    'helpPhone' => '1.800.393.8664',

    // if user email ends in this suffix, they will be forced to change email
    'tempEmailSuffix' => 'temp.hdmaster.com',

    // Allow observers to lock testevents within this amount of days before test date
    'observerLockPeriod' => 3,

    // How far to offset affixed sidebars by default
    // Setting this to '0' causes sidebar to jump when there are .alerts on 
    // visible and the user clicks anywhere on the page, '1' fixes the issue
    'affixOffset' => 50,

    // Exclude aliasing classes -- used in state apps to specify class overrides
    'excludeAliases' => [
        // 'Actor',                   
        // 'Ada',                     
        // 'Admin',                   
        // 'Agency',                  
        // 'Booking',                 
        // 'Certification',           
        // 'Distractor',              
        // 'Enemy',                   
        // 'Exam',                    
        // 'Facility',                
        // 'InputField',              
        // 'Instructor',              
        // 'Observer',                
        // 'Pendingevent',            
        // 'Permission',              
        // 'Person',                  
        // 'PrintProfile',            
        // 'Proctor',                 
        // 'Role',                    
        // 'Skillattempt',            
        // 'Skillexam',               
        // 'Skilltask',               
        // 'SkilltaskResponse',       
        // 'SkilltaskSetup',          
        // 'SkilltaskStep',           
        // 'Skilltest',               
        // 'Stat',                    
        // 'Student',                 
        // 'StudentExamEligibility',  
        // 'StudentSkillEligibility', 
        // 'StudentTraining',         
        // 'Subject',                 
        // 'Testattempt',             
        // 'Testevent',               
        // 'Testform',                
        // 'Testitem',                
        // 'Testplan',                
        // 'Training',                
        // 'User',                    
        // 'UserRepository',          
        // 'Vocab'                   
    ],

    // Certfications
    'certification' => [
        // date at which a certification is no longer valid/counted
        'lapsed' => date('Y-m-t', strtotime('-2 years')),
        'show'   => true
    ],

    // Scheduled commands
    'commands' => [
        'set_test_lock' => [
            'paper_test_lock'    => '+1 week',
            'nonpaper_test_lock' => '+1 day'
        ],
    ],
    
    // Testevents + Calendar View
    'events' => [
        'calendarColors' => [
            
            // Past events
            'past' => '#ccc',

            // Generic color-coding by discipline
            // ID's are hard-coded here so each event doesn't have to query, but 
            // the legend is built dynamically so the names will match up 
            'disciplines' => [
                1 => '#245871',
                2 => '#00a65a',
            ]
        ],


        // Time of day graded tests will be released to students
        'release_results' => '18:00',

        // All legacy placeholder events will use this as test_date
        'legacy_test_date' => '1900-01-01'
    ],

    // Knowledge testing
    'knowledge' => [
        'options' => 'abcde',
        'sources' => ['Knowledge', 'Comprehension', 'Application'],
    ],

    // Pagination
    'pagination' => [
        // The default number of items per page
        'default' => 50
    ],

    // Scantron connection for paper testing
    'scan' => [
        'login'    => 'TmU.2013',
        'pin'      => 'jt*901.jeN'.date('mdY'),
        'offset_v' => 0.15,
        'offset_h' => 0.015,
        'client_numbers'  => [
            'or' => 1,
            'zz' => 99 // testing state / workbench
        ]
    ],

    // Testplan defaults
    'testplans' => [
        'timelimit'           => 60,
        'minimum_score'       => 70,
        'readinglevel'        => 5.8,
        'readinglevel_max'    => 6.0,
        'reliability'         => 0.8,
        'reliability_max'     => 0.9,
        'pvalue'              => 0.73,
        'pvalue_max'          => 0.93,
        'difficulty'          => -0.75,
        'difficulty_max'      => -0.85,
        'discrimination'      => 0.9,
        'discrimination_max'  => 1.1,
        'guessing'            => 0.35,
        'guessing_max'        => 0.45,
        'cutscore'            => 0.74,
        'cutscore_max'        => 0.76,
        'target_theta'        => -0.8,
        'pbs'                 => 0.13,
        'item_pvalue'         => 0.3,
        'item_pvalue_max'     => 0.97,
        'max_attempts'        => 100,
        'max_pvalue_attempts' => 500
    ],
];
