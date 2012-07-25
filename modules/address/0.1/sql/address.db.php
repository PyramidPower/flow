
-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE IF NOT EXISTS `address` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `addressOne` text NOT NULL,
  `addressTwo` text NOT NULL,
  `addressThree` text NOT NULL,
  `postcode_id` bigint(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `area`
--

CREATE TABLE IF NOT EXISTS `area` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) NOT NULL,
  `object_id` bigint(11) NOT NULL,
  `postcode_id` bigint(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=53 ;

