<h1><u>TABLES</u></h1>

<h3>ADMIN:</h3>
CREATE TABLE `admin` (
  `SL_NO` int(11) NOT NULL AUTO_INCREMENT,
  `EMAIL` varchar(100) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `PASSWORD` varchar(16) NOT NULL,
  `TIMESTAMP` bigint(20) DEFAULT NULL
);

<h3>APARTMENT DETAILS:</h3>
CREATE TABLE `apartment_details` (
  `BLOCK` varchar(5) NOT NULL,
  `APT_NUM` varchar(5) NOT NULL,
  `BHK` int(11) DEFAULT NULL
);

<h3>COMPLAINTS:</h3>
CREATE TABLE `complaints` (
  `COMPLAINT_ID` int(11) NOT NULL AUTO_INCREMENT,
  `APT_BLOCK` varchar(5) NOT NULL,
  `APT_NUM` varchar(5) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `SUBJECT` varchar(15) NOT NULL,
  `COMP_BODY` text NOT NULL,
  `DATE_FILED` date NOT NULL,
  `COMP_STATUS` varchar(25) NOT NULL DEFAULT 'NOT RESOLVED',
  `TIMESTAMP` bigint(20) NOT NULL
);

<h3>COMP_RESOLUTION:</h3>
CREATE TABLE `comp_resolution` (
  `COMPLAINT_ID` int(11) NOT NULL,
  `COMP_SUBJECT` text NOT NULL,
  `COMP_HANDLER` varchar(150) NOT NULL,
  `HANDLER_PHONE` bigint(20) NOT NULL,
  `TIMESTAMP` bigint(20) NOT NULL
);

<h3>FORMERRESIDENT:</h3>
CREATE TABLE `formerresident` (
  `RES_ID` int(11) NOT NULL,
  `NAME` varchar(150) NOT NULL,
  `PHONE_NO` bigint(20) NOT NULL,
  `EMAILID` varchar(150) NOT NULL DEFAULT 'OPTED OUT',
  `FEEDBACK` text,
  `EXITTIMESTAMP` bigint(20) DEFAULT NULL,
  `DURATIONTIMESTAMP` bigint(20) DEFAULT NULL
);

<h3>GUEST:</h3>
CREATE TABLE `guest` (
  `GUID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL,
  `APT_BLOCK` varchar(10) NOT NULL,
  `APT_NUM` varchar(5) NOT NULL,
  `REASON` text NOT NULL,
  `PHONE` varchar(15) NOT NULL,
  `DATE_OE` varchar(15) NOT NULL,
  `TIME_OE` varchar(15) NOT NULL,
  `TIMESTAMP` bigint(20) NOT NULL
);

<h3>RESIDENT:</h3>
CREATE TABLE `resident` (
  `RES_ID` int(11) NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(5) NOT NULL,
  `FULLNAME` varchar(100) NOT NULL,
  `LNAME` varchar(100) NOT NULL,
  `DOB` varchar(15) NOT NULL,
  `PHONE_NO` bigint(20) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `PREV_ADDRESS` varchar(100) NOT NULL,
  `PREFERRED_BLOCK` varchar(3) NOT NULL,
  `PREFERRED_APT` varchar(3) NOT NULL,
  `REG_TIMESTAMP` bigint(20) NOT NULL
);

<br /><br />

<h1><u>DATABASE INDICES</u></h1>

ALTER TABLE `admin`
  ADD PRIMARY KEY (`SL_NO`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

ALTER TABLE `apartment_details`
  ADD PRIMARY KEY (`BLOCK`,`APT_NUM`);

ALTER TABLE `complaints`
  ADD PRIMARY KEY (`COMPLAINT_ID`),
  ADD KEY `Apt_Const` (`APT_BLOCK`,`APT_NUM`);

ALTER TABLE `comp_resolution`
  ADD PRIMARY KEY (`COMPLAINT_ID`);

ALTER TABLE `formerresident`
  ADD UNIQUE KEY `RES_ID` (`RES_ID`);

ALTER TABLE `guest`
  ADD PRIMARY KEY (`GUID`),
  ADD KEY `GID` (`APT_BLOCK`,`APT_NUM`);

ALTER TABLE `resident`
  ADD PRIMARY KEY (`RES_ID`),
  ADD UNIQUE KEY `PREFERRED_BLOCK` (`PREFERRED_BLOCK`,`PREFERRED_APT`);

<br /><br />

<h1><u>DATABASE CONSTRAINTS</u></h1>

ALTER TABLE `complaints`
  ADD CONSTRAINT `Apt_Const` FOREIGN KEY (`APT_BLOCK`,`APT_NUM`) REFERENCES `resident` (`preferred_block`, `preferred_apt`) ON DELETE CASCADE;

ALTER TABLE `comp_resolution`
  ADD CONSTRAINT `CRID` FOREIGN KEY (`COMPLAINT_ID`) REFERENCES `complaints` (`complaint_id`) ON DELETE CASCADE;

ALTER TABLE `guest`
  ADD CONSTRAINT `GID` FOREIGN KEY (`APT_BLOCK`,`APT_NUM`) REFERENCES `resident` (`preferred_block`, `preferred_apt`) ON DELETE CASCADE;

ALTER TABLE `resident`
  ADD CONSTRAINT `BLAPT` FOREIGN KEY (`PREFERRED_BLOCK`,`PREFERRED_APT`) REFERENCES `apartment_details` (`block`, `apt_num`) ON DELETE CASCADE;

<br /><br />

<h1><u>STORED PROCEDURES</u></h1>

DELIMITER $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `check_insert` (IN `BLOCK` VARCHAR(5), IN `APTNUM` VARCHAR(5), OUT `RESULT` INT)  BEGIN
	IF EXISTS(SELECT 1 FROM RESIDENT WHERE PREFERRED_BLOCK = BLOCK AND PREFERRED_APT = APTNUM) THEN
    	SET RESULT = -1;
    ELSE
    	SET RESULT = 1;
    END IF;
END$$

DELIMITER ;

<br /><br />

<h1><u>DATABASE TRIGGERS</u></h1>

<h3>TRIGGER THAT DELETES A RECORD FROM COMP_RESOLUTION WHEN THAT COMPLAINT HAS BEEN SUCCESFULLY RESOLVED</h3>
DELIMITER $$
CREATE TRIGGER `comp_on_resolution` AFTER UPDATE ON `complaints` FOR EACH ROW BEGIN
    IF EXISTS (SELECT 1 FROM complaints WHERE OLD.COMP_STATUS = 'SCHEDULED FOR RESOLUTION' AND NEW.COMP_STATUS = 'RESOLVED') THEN
    	DELETE FROM comp_resolution WHERE COMPLAINT_ID = OLD.COMPLAINT_ID;
    END IF; 
    END
$$
DELIMITER ;


<h3>TRIGGER THAT CHANGES THE COMP_STATUS IN APARTMENTS.COMPLAINTS WHEN ADMIN SETS IT FOR RESOLUTION</h3>
DELIMITER $$
CREATE TRIGGER `comp_on_resolve` AFTER INSERT ON `comp_resolution` FOR EACH ROW BEGIN
    	UPDATE complaints SET COMP_STATUS = "SCHEDULED FOR RESOLUTION", TIMESTAMP = NEW.TIMESTAMP WHERE COMPLAINT_ID = NEW.COMPLAINT_ID;
    END
$$
DELIMITER ;
 
<h3>TRIGGER THAT ADDS A RESIDENT’S RECORD TO APARTMENTS.FORMERRESIDENTS WHEN HIS RECORD IS DELETED FROM APARTMENTS.RESIDENT</h3>
DELIMITER $$
CREATE TRIGGER `resident_on_delete` AFTER DELETE ON `resident` FOR EACH ROW BEGIN
	INSERT INTO formerresident(RES_ID,NAME,PHONE_NO) VALUES(OLD.RES_ID,OLD.FULLNAME,OLD.PHONE_NO);
END
$$
DELIMITER ;