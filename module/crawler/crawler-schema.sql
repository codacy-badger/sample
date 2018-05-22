--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `log_id` int(11) UNSIGNED NOT NULL,
  `log_message` varchar(255) NOT NULL,
  `log_link` varchar(255) NOT NULL,
  `log_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `webpage`
--

CREATE TABLE `webpage` (
  `webpage_id` int(11) UNSIGNED NOT NULL,
  `webpage_root` varchar(255) NOT NULL,
  `webpage_link` text NOT NULL,
  `webpage_type` varchar(255) NOT NULL,
  `webpage_created` datetime NOT NULL,
  `webpage_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `website`
--

CREATE TABLE `website` (
  `website_id` int(10) UNSIGNED NOT NULL,
  `website_name` varchar(255) NOT NULL,
  `website_root` varchar(255) NOT NULL,
  `website_start` varchar(255) NOT NULL,
  `website_crop` varchar(255) NOT NULL DEFAULT '0,0',
  `website_settings` json NOT NULL,
  `website_active` int(1) UNSIGNED NOT NULL DEFAULT '0',
  `website_status` varchar(255) NOT NULL DEFAULT 'IDLE',
  `website_created` datetime NOT NULL,
  `website_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `worker`
--

CREATE TABLE `worker` (
  `worker_id` varchar(255) NOT NULL,
  `worker_root` varchar(255) DEFAULT NULL,
  `worker_link` text,
  `worker_status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `worker_created` datetime NOT NULL,
  `worker_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `webpage`
--
ALTER TABLE `webpage`
  ADD PRIMARY KEY (`webpage_id`);

--
-- Indexes for table `website`
--
ALTER TABLE `website`
  ADD PRIMARY KEY (`website_id`),
  ADD UNIQUE KEY `website_root` (`website_root`);

--
-- Indexes for table `worker`
--
ALTER TABLE `worker`
  ADD PRIMARY KEY (`worker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `log_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `webpage`
--
ALTER TABLE `webpage`
  MODIFY `webpage_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
  --
  -- AUTO_INCREMENT for table `website`
  --
  ALTER TABLE `website`
    MODIFY `website_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
