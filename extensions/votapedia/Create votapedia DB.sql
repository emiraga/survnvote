-- phpMyAdmin SQL Dump
-- version 2.8.2
-- http://www.phpmyadmin.net
-- Host: localhost
-- Generation Time: May 10, 2007 at 05:02 PM
-- Server version: 5.0.22
-- PHP Version: 5.1.4
-- Database: `voting`

-- Table structure for table `csiro_number`

CREATE TABLE `csiro_number` (
  `teleID` tinyint(4) unsigned NOT NULL default '0',
  `telenumber` varchar(50) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


-- Table structure for table `errorcode`
CREATE TABLE `errorcode` (
  `errorCode` tinyint(4) NOT NULL,
  `errorreason` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- Dumping data for table `errorcode`
INSERT INTO `errorcode` VALUES (0, 'OK                                                                                                  ');
INSERT INTO `errorcode` VALUES (1, 'No survey is in this time period                                                                    ');
INSERT INTO `errorcode` VALUES (2, 'No choice match receiving number                                                                    ');
INSERT INTO `errorcode` VALUES (3, 'More than 1 choices match the same receiving number                                                 ');
INSERT INTO `errorcode` VALUES (4, 'Repeated voting                                                                                     ');
INSERT INTO `errorcode` VALUES (5, 'Invalid telephone is forbidden                                                                      ');

-- Table structure for table `fruitname`
CREATE TABLE `fruitname` (
  `ID` smallint(5) unsigned NOT NULL auto_increment,
  `Name` varchar(100) NOT NULL,
  `Taken` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=812 ;
-- Dumping data for table `fruitname`
INSERT INTO `fruitname` VALUES (1, 'Abaca', 1);
INSERT INTO `fruitname` VALUES (2, 'Abiu', 1);
INSERT INTO `fruitname` VALUES (3, 'Abyssinian Banana', 1);
INSERT INTO `fruitname` VALUES (4, 'Abyssinian Gooseberry', 1);
INSERT INTO `fruitname` VALUES (5, 'Acerola', 1);
INSERT INTO `fruitname` VALUES (6, 'Achiote', 1);
INSERT INTO `fruitname` VALUES (7, 'Achira', 1);
INSERT INTO `fruitname` VALUES (8, 'African Apricot', 1);
INSERT INTO `fruitname` VALUES (9, 'African Breadfruit', 1);
INSERT INTO `fruitname` VALUES (10, 'African Gooseberry', 1);
INSERT INTO `fruitname` VALUES (11, 'African Honeysuckle', 1);
INSERT INTO `fruitname` VALUES (12, 'African Horned Cucumber', 1);
INSERT INTO `fruitname` VALUES (13, 'African Locust', 1);
INSERT INTO `fruitname` VALUES (14, 'African Oil Palm', 1);
INSERT INTO `fruitname` VALUES (15, 'African Plum', 1);
INSERT INTO `fruitname` VALUES (18, 'Akee', 1);
INSERT INTO `fruitname` VALUES (19, 'Allspice', 1);
INSERT INTO `fruitname` VALUES (20, 'Almond', 1);
INSERT INTO `fruitname` VALUES (21, 'Alpine Strawberry', 1);
INSERT INTO `fruitname` VALUES (22, 'Alupag', 1);
INSERT INTO `fruitname` VALUES (23, 'Amazon Tree-Grape', 1);
INSERT INTO `fruitname` VALUES (24, 'Ambarella', 1);
INSERT INTO `fruitname` VALUES (25, 'Ambra', 1);
INSERT INTO `fruitname` VALUES (26, 'American Black Currant', 1);
INSERT INTO `fruitname` VALUES (27, 'American Black Gooseberry', 1);
INSERT INTO `fruitname` VALUES (28, 'American Chestnut', 1);
INSERT INTO `fruitname` VALUES (29, 'American Crab Apple', 1);
INSERT INTO `fruitname` VALUES (30, 'American Crab Apple', 1);
INSERT INTO `fruitname` VALUES (31, 'American Cranberry', 1);
INSERT INTO `fruitname` VALUES (32, 'American Cranberry Bush', 1);
INSERT INTO `fruitname` VALUES (33, 'American Dewberry', 1);
INSERT INTO `fruitname` VALUES (34, 'American Elderberry', 1);
INSERT INTO `fruitname` VALUES (35, 'American Hazelnut', 1);
INSERT INTO `fruitname` VALUES (36, 'American Persimmon', 1);
INSERT INTO `fruitname` VALUES (37, 'American Plum', 1);
INSERT INTO `fruitname` VALUES (38, 'Amra', 1);
INSERT INTO `fruitname` VALUES (39, 'Amur River Grape', 1);
INSERT INTO `fruitname` VALUES (40, 'Ananasnaja', 1);
INSERT INTO `fruitname` VALUES (41, 'Andean Blackberry', 1);
INSERT INTO `fruitname` VALUES (42, 'Annatto', 1);
INSERT INTO `fruitname` VALUES (43, 'Annona Asiatic', 1);
INSERT INTO `fruitname` VALUES (44, 'Anonilla', 1);
INSERT INTO `fruitname` VALUES (45, 'Appalachian Tea', 1);
INSERT INTO `fruitname` VALUES (46, 'Apple', 1);
INSERT INTO `fruitname` VALUES (47, 'Apple Guava', 1);
INSERT INTO `fruitname` VALUES (48, 'Apple Rose', 1);
INSERT INTO `fruitname` VALUES (49, 'Appleberry', 1);
INSERT INTO `fruitname` VALUES (50, 'Apricot', 1);
INSERT INTO `fruitname` VALUES (51, 'Arabian Coffee', 1);
INSERT INTO `fruitname` VALUES (52, 'Arctic Beauty', 1);
INSERT INTO `fruitname` VALUES (53, 'Arkurbal', 1);
INSERT INTO `fruitname` VALUES (54, 'Asian Pear', 1);
INSERT INTO `fruitname` VALUES (55, 'Atemoya', 1);
INSERT INTO `fruitname` VALUES (56, 'Australian Almond', 1);
INSERT INTO `fruitname` VALUES (57, 'Australian Brush Cherry', 1);
INSERT INTO `fruitname` VALUES (58, 'Autumn Oleaster', 1);
INSERT INTO `fruitname` VALUES (59, 'Autumn Olive', 1);
INSERT INTO `fruitname` VALUES (60, 'Avocado', 1);
INSERT INTO `fruitname` VALUES (61, 'Azarole', 1);
INSERT INTO `fruitname` VALUES (62, 'Babaco', 1);
INSERT INTO `fruitname` VALUES (63, 'Bacae', 1);
INSERT INTO `fruitname` VALUES (64, 'Bacuri', 1);
INSERT INTO `fruitname` VALUES (65, 'Bacuripari', 1);
INSERT INTO `fruitname` VALUES (66, 'Bacury-Pary', 1);
INSERT INTO `fruitname` VALUES (67, 'Bael Fruit', 1);
INSERT INTO `fruitname` VALUES (68, 'Baked Apple Berry', 1);
INSERT INTO `fruitname` VALUES (69, 'Bakupari', 1);
INSERT INTO `fruitname` VALUES (70, 'Bakuri', 1);
INSERT INTO `fruitname` VALUES (71, 'Banana', 1);
INSERT INTO `fruitname` VALUES (72, 'Banana Passion Fruit', 1);
INSERT INTO `fruitname` VALUES (73, 'Banana Passion Fruit', 1);
INSERT INTO `fruitname` VALUES (74, 'Barbados Cherry', 1);
INSERT INTO `fruitname` VALUES (75, 'Barbados Gooseberry', 1);
INSERT INTO `fruitname` VALUES (76, 'Barbados Gooseberry', 1);
INSERT INTO `fruitname` VALUES (77, 'Barberry', 1);
INSERT INTO `fruitname` VALUES (78, 'Batoko', 1);
INSERT INTO `fruitname` VALUES (79, 'Bay Tree', 0);
INSERT INTO `fruitname` VALUES (80, 'Bay Tree', 0);
INSERT INTO `fruitname` VALUES (81, 'Beach Cherry', 0);
INSERT INTO `fruitname` VALUES (82, 'Beach Plum', 0);
INSERT INTO `fruitname` VALUES (83, 'Beach Strawberry', 0);
INSERT INTO `fruitname` VALUES (84, 'Bearss Lime', 0);
INSERT INTO `fruitname` VALUES (85, 'Bee Bee Raspberry', 0);
INSERT INTO `fruitname` VALUES (86, 'Belimbing', 0);
INSERT INTO `fruitname` VALUES (87, 'Bell Apple', 0);
INSERT INTO `fruitname` VALUES (88, 'Bengal Quince', 0);
INSERT INTO `fruitname` VALUES (89, 'Ber', 0);
INSERT INTO `fruitname` VALUES (90, 'Betel Nut', 0);
INSERT INTO `fruitname` VALUES (91, 'Bigay', 0);
INSERT INTO `fruitname` VALUES (92, 'Bignai', 0);
INSERT INTO `fruitname` VALUES (93, 'Bignay', 0);
INSERT INTO `fruitname` VALUES (94, 'Bilimbi', 0);
INSERT INTO `fruitname` VALUES (95, 'Billy Goat Plum', 0);
INSERT INTO `fruitname` VALUES (96, 'Biriba', 0);
INSERT INTO `fruitname` VALUES (97, 'Black Apricot', 0);
INSERT INTO `fruitname` VALUES (98, 'Black Cherry', 0);
INSERT INTO `fruitname` VALUES (99, 'Black Choke', 0);
INSERT INTO `fruitname` VALUES (100, 'Black Current', 0);
INSERT INTO `fruitname` VALUES (101, 'Black Elderberry', 0);
INSERT INTO `fruitname` VALUES (102, 'Black Haw', 0);
INSERT INTO `fruitname` VALUES (103, 'Black Huckleberry', 0);
INSERT INTO `fruitname` VALUES (104, 'Black Mulberry', 0);
INSERT INTO `fruitname` VALUES (105, 'Black Persimmon', 0);
INSERT INTO `fruitname` VALUES (106, 'Black Persimmon', 0);
INSERT INTO `fruitname` VALUES (107, 'Black Sapote', 0);
INSERT INTO `fruitname` VALUES (108, 'Black Tamarind', 0);
INSERT INTO `fruitname` VALUES (109, 'Black Walnut', 0);
INSERT INTO `fruitname` VALUES (110, 'Black/White Pepper', 0);
INSERT INTO `fruitname` VALUES (111, 'Blackberry', 0);
INSERT INTO `fruitname` VALUES (112, 'Blackberry Jam-Fruit', 0);
INSERT INTO `fruitname` VALUES (113, 'Blackcap', 0);
INSERT INTO `fruitname` VALUES (114, 'Blood Banana', 0);
INSERT INTO `fruitname` VALUES (115, 'Blue Bean Shrub', 0);
INSERT INTO `fruitname` VALUES (116, 'Blue Lilly Pilly', 0);
INSERT INTO `fruitname` VALUES (117, 'Blue Passion Flower', 0);
INSERT INTO `fruitname` VALUES (118, 'Blue Taro', 0);
INSERT INTO `fruitname` VALUES (119, 'Blueberry', 0);
INSERT INTO `fruitname` VALUES (120, 'Bokhara Plum', 0);
INSERT INTO `fruitname` VALUES (121, 'Bower Vine', 0);
INSERT INTO `fruitname` VALUES (122, 'Box Blueberry', 0);
INSERT INTO `fruitname` VALUES (123, 'Boysenberry', 0);
INSERT INTO `fruitname` VALUES (124, 'Bramble', 0);
INSERT INTO `fruitname` VALUES (125, 'Brazil Nut', 0);
INSERT INTO `fruitname` VALUES (126, 'Brazilian Guava', 0);
INSERT INTO `fruitname` VALUES (127, 'Breadfruit (seedless)', 0);
INSERT INTO `fruitname` VALUES (128, 'Breadfruit', 0);
INSERT INTO `fruitname` VALUES (129, 'Breadnut (seeded Breadfruit)', 0);
INSERT INTO `fruitname` VALUES (130, 'Breadnut (seeded Breadfruit)', 0);
INSERT INTO `fruitname` VALUES (131, 'Breadroot', 0);
INSERT INTO `fruitname` VALUES (132, 'Brier Rose', 0);
INSERT INTO `fruitname` VALUES (133, 'Brush Cherry', 0);
INSERT INTO `fruitname` VALUES (134, 'Bu annona', 0);
INSERT INTO `fruitname` VALUES (135, 'Buah Susu', 0);
INSERT INTO `fruitname` VALUES (136, 'Buddha''s Hand Citron', 0);
INSERT INTO `fruitname` VALUES (137, 'Buffalo Berry', 0);
INSERT INTO `fruitname` VALUES (138, 'Buffalo Berry', 0);
INSERT INTO `fruitname` VALUES (139, 'Buffalo Current', 0);
INSERT INTO `fruitname` VALUES (140, 'Buffalo Currant', 0);
INSERT INTO `fruitname` VALUES (141, 'Buffalo Thorn', 0);
INSERT INTO `fruitname` VALUES (142, 'Bullock''s heart', 0);
INSERT INTO `fruitname` VALUES (143, 'Bunchosia', 0);
INSERT INTO `fruitname` VALUES (144, 'Buni', 0);
INSERT INTO `fruitname` VALUES (145, 'Bunya-Bunya', 0);
INSERT INTO `fruitname` VALUES (146, 'Burdekin Plum', 0);
INSERT INTO `fruitname` VALUES (147, 'Bush Butter', 0);
INSERT INTO `fruitname` VALUES (148, 'Butternut', 0);
INSERT INTO `fruitname` VALUES (149, 'Button Mangosteen', 0);
INSERT INTO `fruitname` VALUES (150, 'Cabinet Cherry', 0);
INSERT INTO `fruitname` VALUES (151, 'Cacao', 0);
INSERT INTO `fruitname` VALUES (152, 'Cactus', 0);
INSERT INTO `fruitname` VALUES (153, 'Cactus', 0);
INSERT INTO `fruitname` VALUES (154, 'Caimito', 0);
INSERT INTO `fruitname` VALUES (155, 'Caimo', 0);
INSERT INTO `fruitname` VALUES (156, 'Calamondin', 0);
INSERT INTO `fruitname` VALUES (157, 'California Bay', 0);
INSERT INTO `fruitname` VALUES (158, 'California Wild Grape', 0);
INSERT INTO `fruitname` VALUES (159, 'Calubura', 0);
INSERT INTO `fruitname` VALUES (160, 'Camocamo', 0);
INSERT INTO `fruitname` VALUES (161, 'Camu Camu', 0);
INSERT INTO `fruitname` VALUES (162, 'Canadian Blackberry', 0);
INSERT INTO `fruitname` VALUES (163, 'Canadian Elderberry', 0);
INSERT INTO `fruitname` VALUES (164, 'Canary Island Date Palm', 0);
INSERT INTO `fruitname` VALUES (165, 'Candlenut', 0);
INSERT INTO `fruitname` VALUES (166, 'Canistel', 0);
INSERT INTO `fruitname` VALUES (167, 'Cannon-ball Tree', 0);
INSERT INTO `fruitname` VALUES (168, 'Cape Gooseberry', 0);
INSERT INTO `fruitname` VALUES (169, 'Caper', 0);
INSERT INTO `fruitname` VALUES (170, 'Capulin Cherry', 0);
INSERT INTO `fruitname` VALUES (171, 'Carambola', 0);
INSERT INTO `fruitname` VALUES (172, 'Carob', 0);
INSERT INTO `fruitname` VALUES (173, 'Carpathian Walnut', 0);
INSERT INTO `fruitname` VALUES (174, 'Cas', 0);
INSERT INTO `fruitname` VALUES (175, 'Casana', 0);
INSERT INTO `fruitname` VALUES (176, 'Cascara', 0);
INSERT INTO `fruitname` VALUES (177, 'Cashew', 0);
INSERT INTO `fruitname` VALUES (178, 'Cassabanana', 0);
INSERT INTO `fruitname` VALUES (179, 'Cat''s Eye', 0);
INSERT INTO `fruitname` VALUES (180, 'Catalina Cherry', 0);
INSERT INTO `fruitname` VALUES (181, 'Cattley Guava', 0);
INSERT INTO `fruitname` VALUES (182, 'Ceriman', 0);
INSERT INTO `fruitname` VALUES (183, 'Ceylon Date Palm', 0);
INSERT INTO `fruitname` VALUES (184, 'Ceylon Gooseberry', 0);
INSERT INTO `fruitname` VALUES (185, 'Champedek', 0);
INSERT INTO `fruitname` VALUES (186, 'Changshou Kumquat', 0);
INSERT INTO `fruitname` VALUES (187, 'Charicuela', 0);
INSERT INTO `fruitname` VALUES (188, 'Chaste Tree', 0);
INSERT INTO `fruitname` VALUES (189, 'Chayote', 0);
INSERT INTO `fruitname` VALUES (190, 'Che', 0);
INSERT INTO `fruitname` VALUES (191, 'Chempedale', 0);
INSERT INTO `fruitname` VALUES (192, 'Cherapu', 0);
INSERT INTO `fruitname` VALUES (193, 'Cheremai', 0);
INSERT INTO `fruitname` VALUES (194, 'Cherimoya', 0);
INSERT INTO `fruitname` VALUES (195, 'Cherry of the Rio Grande', 0);
INSERT INTO `fruitname` VALUES (196, 'Chess Apple', 0);
INSERT INTO `fruitname` VALUES (197, 'Chia Ye', 0);
INSERT INTO `fruitname` VALUES (198, 'Chicle Tree', 0);
INSERT INTO `fruitname` VALUES (199, 'Chico Sapote', 0);
INSERT INTO `fruitname` VALUES (200, 'Chico Mamey', 0);
INSERT INTO `fruitname` VALUES (201, 'Chilean Guava', 0);
INSERT INTO `fruitname` VALUES (202, 'Chilean Hazel', 0);
INSERT INTO `fruitname` VALUES (203, 'Chilean Wine Palm', 0);
INSERT INTO `fruitname` VALUES (204, 'China Chestnut', 0);
INSERT INTO `fruitname` VALUES (205, 'Chincopin', 0);
INSERT INTO `fruitname` VALUES (206, 'Chinese Asian Pear', 0);
INSERT INTO `fruitname` VALUES (207, 'Chinese Chestnut', 0);
INSERT INTO `fruitname` VALUES (208, 'Chinese Date', 0);
INSERT INTO `fruitname` VALUES (209, 'Chinese Date Palm', 0);
INSERT INTO `fruitname` VALUES (210, 'Chinese Egg Gooseberry', 0);
INSERT INTO `fruitname` VALUES (211, 'Chinese Gooseberry', 0);
INSERT INTO `fruitname` VALUES (212, 'Chinese Hackberry', 0);
INSERT INTO `fruitname` VALUES (213, 'Chinese Jello', 0);
INSERT INTO `fruitname` VALUES (214, 'Chinese Mulberry', 0);
INSERT INTO `fruitname` VALUES (215, 'Chinese Olive', 0);
INSERT INTO `fruitname` VALUES (216, 'Chinese Pear', 0);
INSERT INTO `fruitname` VALUES (217, 'Chinese Raisin Tree', 0);
INSERT INTO `fruitname` VALUES (218, 'Chinese Taro', 0);
INSERT INTO `fruitname` VALUES (219, 'Chinese White Pear', 0);
INSERT INTO `fruitname` VALUES (220, 'Chinese White Pear', 0);
INSERT INTO `fruitname` VALUES (221, 'Chinquapin', 0);
INSERT INTO `fruitname` VALUES (222, 'Chitra', 0);
INSERT INTO `fruitname` VALUES (223, 'Chocolate Pudding Fruit', 0);
INSERT INTO `fruitname` VALUES (224, 'Chokecherry', 0);
INSERT INTO `fruitname` VALUES (225, 'Chupa-Chupa', 0);
INSERT INTO `fruitname` VALUES (226, 'Ciku', 0);
INSERT INTO `fruitname` VALUES (227, 'Cimarrona', 0);
INSERT INTO `fruitname` VALUES (228, 'Cinnamon', 0);
INSERT INTO `fruitname` VALUES (229, 'Cinnamon', 0);
INSERT INTO `fruitname` VALUES (230, 'Ciruela', 0);
INSERT INTO `fruitname` VALUES (231, 'Ciruela Verde', 0);
INSERT INTO `fruitname` VALUES (232, 'Ciruelo', 0);
INSERT INTO `fruitname` VALUES (233, 'Ciruelo', 0);
INSERT INTO `fruitname` VALUES (234, 'Citron', 0);
INSERT INTO `fruitname` VALUES (235, 'Clove', 0);
INSERT INTO `fruitname` VALUES (236, 'Clove Currant', 0);
INSERT INTO `fruitname` VALUES (237, 'Clove Currant', 0);
INSERT INTO `fruitname` VALUES (238, 'Cochin-goraka', 0);
INSERT INTO `fruitname` VALUES (239, 'Cocoa', 0);
INSERT INTO `fruitname` VALUES (240, 'Cocona', 0);
INSERT INTO `fruitname` VALUES (241, 'Coconut Palm', 0);
INSERT INTO `fruitname` VALUES (242, 'Cocoplum', 0);
INSERT INTO `fruitname` VALUES (243, 'Coffee Berry', 0);
INSERT INTO `fruitname` VALUES (244, 'Columbian Walnut', 0);
INSERT INTO `fruitname` VALUES (245, 'Cometure', 0);
INSERT INTO `fruitname` VALUES (246, 'Commercial Banana', 0);
INSERT INTO `fruitname` VALUES (247, 'Commercial Banana', 0);
INSERT INTO `fruitname` VALUES (248, 'Common Currant', 0);
INSERT INTO `fruitname` VALUES (249, 'Common Guava', 0);
INSERT INTO `fruitname` VALUES (250, 'Common Juniper', 0);
INSERT INTO `fruitname` VALUES (251, 'Conch Apple', 0);
INSERT INTO `fruitname` VALUES (252, 'Coontie', 0);
INSERT INTO `fruitname` VALUES (253, 'Cornelian Cherry', 0);
INSERT INTO `fruitname` VALUES (254, 'Corosol', 0);
INSERT INTO `fruitname` VALUES (255, 'Corozo', 0);
INSERT INTO `fruitname` VALUES (256, 'Costa Rica Guava', 0);
INSERT INTO `fruitname` VALUES (257, 'Cotopriz', 0);
INSERT INTO `fruitname` VALUES (258, 'Country Walnut', 0);
INSERT INTO `fruitname` VALUES (259, 'Coyo', 0);
INSERT INTO `fruitname` VALUES (260, 'Crabapple', 0);
INSERT INTO `fruitname` VALUES (261, 'Cranberry', 0);
INSERT INTO `fruitname` VALUES (262, 'Cranberry Bush', 0);
INSERT INTO `fruitname` VALUES (263, 'Crato Passion Fruit', 0);
INSERT INTO `fruitname` VALUES (264, 'Creeping Blueberry', 0);
INSERT INTO `fruitname` VALUES (265, 'Cuachilote', 0);
INSERT INTO `fruitname` VALUES (266, 'Cuban Mangosteen', 0);
INSERT INTO `fruitname` VALUES (267, 'Cuban Spinach', 0);
INSERT INTO `fruitname` VALUES (268, 'Cupu-Assu', 0);
INSERT INTO `fruitname` VALUES (269, 'Currant', 0);
INSERT INTO `fruitname` VALUES (270, 'Currant Tomato', 0);
INSERT INTO `fruitname` VALUES (271, 'Curry Leaf Tree', 0);
INSERT INTO `fruitname` VALUES (272, 'Curuba', 0);
INSERT INTO `fruitname` VALUES (273, 'Custard Apple', 0);
INSERT INTO `fruitname` VALUES (274, 'Custard Apple', 0);
INSERT INTO `fruitname` VALUES (275, 'Dalison', 0);
INSERT INTO `fruitname` VALUES (276, 'Dalo', 0);
INSERT INTO `fruitname` VALUES (277, 'Damson Plum', 0);
INSERT INTO `fruitname` VALUES (278, 'Damson Plum', 0);
INSERT INTO `fruitname` VALUES (279, 'Dangleberry', 0);
INSERT INTO `fruitname` VALUES (280, 'Darling Plum', 0);
INSERT INTO `fruitname` VALUES (281, 'Dasheen', 0);
INSERT INTO `fruitname` VALUES (282, 'Date Palm', 0);
INSERT INTO `fruitname` VALUES (283, 'Date Plum', 0);
INSERT INTO `fruitname` VALUES (284, 'David Peach', 0);
INSERT INTO `fruitname` VALUES (285, 'Davidson''s Plum', 0);
INSERT INTO `fruitname` VALUES (286, 'Desert Date', 0);
INSERT INTO `fruitname` VALUES (287, 'Desert Hackberry', 0);
INSERT INTO `fruitname` VALUES (288, 'Dewberry', 0);
INSERT INTO `fruitname` VALUES (289, 'Downy Myrtle', 0);
INSERT INTO `fruitname` VALUES (290, 'Dragon''s Eye', 0);
INSERT INTO `fruitname` VALUES (291, 'Duku', 0);
INSERT INTO `fruitname` VALUES (292, 'Durian', 0);
INSERT INTO `fruitname` VALUES (293, 'Durian Belanda', 0);
INSERT INTO `fruitname` VALUES (294, 'Dwarf Paw Paw', 0);
INSERT INTO `fruitname` VALUES (295, 'Early Blueberry', 0);
INSERT INTO `fruitname` VALUES (296, 'East Indian Wine Palm', 0);
INSERT INTO `fruitname` VALUES (297, 'Ecuador Walnut', 0);
INSERT INTO `fruitname` VALUES (298, 'Eddo', 0);
INSERT INTO `fruitname` VALUES (299, 'Edible Dogwood', 0);
INSERT INTO `fruitname` VALUES (300, 'Edible Hibiscus', 0);
INSERT INTO `fruitname` VALUES (301, 'Eggfruit', 0);
INSERT INTO `fruitname` VALUES (302, 'Egyptian Carissa', 0);
INSERT INTO `fruitname` VALUES (303, 'Elderberry', 0);
INSERT INTO `fruitname` VALUES (304, 'Elephant Apple', 0);
INSERT INTO `fruitname` VALUES (305, 'Elephant Apple', 0);
INSERT INTO `fruitname` VALUES (306, 'Emblic', 0);
INSERT INTO `fruitname` VALUES (307, 'Engkala', 0);
INSERT INTO `fruitname` VALUES (308, 'English Walnut', 0);
INSERT INTO `fruitname` VALUES (309, 'Escobillo', 0);
INSERT INTO `fruitname` VALUES (310, 'Ethiopian Black Banana', 0);
INSERT INTO `fruitname` VALUES (311, 'Etrog Citron', 0);
INSERT INTO `fruitname` VALUES (312, 'European Black Currant', 0);
INSERT INTO `fruitname` VALUES (313, 'European Chestnut', 0);
INSERT INTO `fruitname` VALUES (314, 'European Crab Apple', 0);
INSERT INTO `fruitname` VALUES (315, 'European Elderberry', 0);
INSERT INTO `fruitname` VALUES (316, 'European Gooseberry', 0);
INSERT INTO `fruitname` VALUES (317, 'European Grape', 0);
INSERT INTO `fruitname` VALUES (318, 'European Hackberry', 0);
INSERT INTO `fruitname` VALUES (319, 'European Hazelnut', 0);
INSERT INTO `fruitname` VALUES (320, 'European Mountain Ash', 0);
INSERT INTO `fruitname` VALUES (321, 'Evergreen Blackberry', 0);
INSERT INTO `fruitname` VALUES (322, 'Evergreen Huckleberry', 0);
INSERT INTO `fruitname` VALUES (323, 'False Mangosteen', 0);
INSERT INTO `fruitname` VALUES (324, 'Farkleberry', 0);
INSERT INTO `fruitname` VALUES (325, 'Feijoa', 0);
INSERT INTO `fruitname` VALUES (326, 'Fig', 0);
INSERT INTO `fruitname` VALUES (327, 'Fijian Longan', 0);
INSERT INTO `fruitname` VALUES (328, 'Filbert', 0);
INSERT INTO `fruitname` VALUES (329, 'Finger Lime', 0);
INSERT INTO `fruitname` VALUES (330, 'Flatwoods Plum', 0);
INSERT INTO `fruitname` VALUES (331, 'Florida Arrowroot', 0);
INSERT INTO `fruitname` VALUES (332, 'Florida Cherry', 0);
INSERT INTO `fruitname` VALUES (333, 'Florida Evergreen Blueberry', 0);
INSERT INTO `fruitname` VALUES (334, 'Florida Gooseberry', 0);
INSERT INTO `fruitname` VALUES (335, 'Floridamia Nut Palm', 0);
INSERT INTO `fruitname` VALUES (336, 'Flying Dragon', 0);
INSERT INTO `fruitname` VALUES (337, 'Fox Grape', 0);
INSERT INTO `fruitname` VALUES (338, 'Fragrant Granadilla', 0);
INSERT INTO `fruitname` VALUES (339, 'French Peanut', 0);
INSERT INTO `fruitname` VALUES (340, 'Fried Egg Tree', 0);
INSERT INTO `fruitname` VALUES (341, 'Fruit Salad Plant', 0);
INSERT INTO `fruitname` VALUES (342, 'Fukushu Kumquat', 0);
INSERT INTO `fruitname` VALUES (343, 'Galangale', 0);
INSERT INTO `fruitname` VALUES (344, 'Galumpi', 0);
INSERT INTO `fruitname` VALUES (345, 'Gamboge', 0);
INSERT INTO `fruitname` VALUES (346, 'Gandaria', 0);
INSERT INTO `fruitname` VALUES (347, 'Genip (Genipe)', 0);
INSERT INTO `fruitname` VALUES (348, 'Genipap', 0);
INSERT INTO `fruitname` VALUES (349, 'Giant Granadilla', 0);
INSERT INTO `fruitname` VALUES (350, 'Giant Sunflower', 0);
INSERT INTO `fruitname` VALUES (351, 'Ginger', 0);
INSERT INTO `fruitname` VALUES (352, 'Ginkgo Nut', 0);
INSERT INTO `fruitname` VALUES (353, 'Ginseng', 0);
INSERT INTO `fruitname` VALUES (354, 'Goatnut', 0);
INSERT INTO `fruitname` VALUES (355, 'Golden Apple', 0);
INSERT INTO `fruitname` VALUES (356, 'Golden Plum', 0);
INSERT INTO `fruitname` VALUES (357, 'Golden Spoon', 0);
INSERT INTO `fruitname` VALUES (358, 'Gooseberry', 0);
INSERT INTO `fruitname` VALUES (359, 'Goumi', 0);
INSERT INTO `fruitname` VALUES (360, 'Governor''s Plum', 0);
INSERT INTO `fruitname` VALUES (361, 'Granada', 0);
INSERT INTO `fruitname` VALUES (362, 'Granadilla', 0);
INSERT INTO `fruitname` VALUES (363, 'Grape-leaved Passion Fruit', 0);
INSERT INTO `fruitname` VALUES (364, 'Grapefruit', 0);
INSERT INTO `fruitname` VALUES (365, 'Grauda', 0);
INSERT INTO `fruitname` VALUES (366, 'Green Almond', 0);
INSERT INTO `fruitname` VALUES (367, 'Green Gram', 0);
INSERT INTO `fruitname` VALUES (368, 'Green Sapote', 0);
INSERT INTO `fruitname` VALUES (369, 'Grosella', 0);
INSERT INTO `fruitname` VALUES (370, 'Ground Cherry', 0);
INSERT INTO `fruitname` VALUES (371, 'Ground Cherry', 0);
INSERT INTO `fruitname` VALUES (372, 'Gru-gru Palm', 0);
INSERT INTO `fruitname` VALUES (373, 'Grumichama', 0);
INSERT INTO `fruitname` VALUES (374, 'Grumixameira', 0);
INSERT INTO `fruitname` VALUES (375, 'Guabiroba', 0);
INSERT INTO `fruitname` VALUES (376, 'Guabiroba', 0);
INSERT INTO `fruitname` VALUES (377, 'Guajilote', 0);
INSERT INTO `fruitname` VALUES (378, 'Guama', 0);
INSERT INTO `fruitname` VALUES (379, 'Guamo', 0);
INSERT INTO `fruitname` VALUES (380, 'Guanabana', 0);
INSERT INTO `fruitname` VALUES (381, 'Guatemalan Avocado', 0);
INSERT INTO `fruitname` VALUES (382, 'Guava', 0);
INSERT INTO `fruitname` VALUES (383, 'Guava Berry', 0);
INSERT INTO `fruitname` VALUES (384, 'Guavira Mi', 0);
INSERT INTO `fruitname` VALUES (385, 'Guayo', 0);
INSERT INTO `fruitname` VALUES (386, 'Guiana Chestnut', 0);
INSERT INTO `fruitname` VALUES (387, 'Gumi', 0);
INSERT INTO `fruitname` VALUES (388, 'Guyaba', 0);
INSERT INTO `fruitname` VALUES (389, 'Habbel', 0);
INSERT INTO `fruitname` VALUES (390, 'Hackberry', 0);
INSERT INTO `fruitname` VALUES (391, 'Hardy Kiwi', 0);
INSERT INTO `fruitname` VALUES (392, 'Harendog', 0);
INSERT INTO `fruitname` VALUES (393, 'Hawthorn', 0);
INSERT INTO `fruitname` VALUES (394, 'Hazelnut', 0);
INSERT INTO `fruitname` VALUES (395, 'Hedgerow Rose', 0);
INSERT INTO `fruitname` VALUES (396, 'Herbert River Cherry', 0);
INSERT INTO `fruitname` VALUES (397, 'Highbush Blueberry', 0);
INSERT INTO `fruitname` VALUES (398, 'Highbush Cranberry', 0);
INSERT INTO `fruitname` VALUES (399, 'Hilama', 0);
INSERT INTO `fruitname` VALUES (400, 'Hog Plum', 0);
INSERT INTO `fruitname` VALUES (401, 'Hog Plum', 0);
INSERT INTO `fruitname` VALUES (402, 'Hog Plum', 0);
INSERT INTO `fruitname` VALUES (403, 'Hog Plum', 0);
INSERT INTO `fruitname` VALUES (404, 'Hog Plum', 0);
INSERT INTO `fruitname` VALUES (405, 'Hondapara Tree', 0);
INSERT INTO `fruitname` VALUES (406, 'Honey Locust', 0);
INSERT INTO `fruitname` VALUES (407, 'Horse Mango', 0);
INSERT INTO `fruitname` VALUES (408, 'Horseradish Tree', 0);
INSERT INTO `fruitname` VALUES (409, 'Hottentot Fig', 0);
INSERT INTO `fruitname` VALUES (410, 'Husk Tomato', 0);
INSERT INTO `fruitname` VALUES (411, 'Hybrid Plantains', 0);
INSERT INTO `fruitname` VALUES (412, 'Ice Cream Bean', 0);
INSERT INTO `fruitname` VALUES (413, 'Ichang', 0);
INSERT INTO `fruitname` VALUES (414, 'Ichang Lemon', 0);
INSERT INTO `fruitname` VALUES (415, 'Ilama', 0);
INSERT INTO `fruitname` VALUES (416, 'Ilang Ilang', 0);
INSERT INTO `fruitname` VALUES (417, 'Imbe', 0);
INSERT INTO `fruitname` VALUES (418, 'Imbu', 0);
INSERT INTO `fruitname` VALUES (419, 'India Date', 0);
INSERT INTO `fruitname` VALUES (420, 'Indian Almond', 0);
INSERT INTO `fruitname` VALUES (421, 'Indian Almond', 0);
INSERT INTO `fruitname` VALUES (422, 'Indian Fig', 0);
INSERT INTO `fruitname` VALUES (423, 'Indian Jujube', 0);
INSERT INTO `fruitname` VALUES (424, 'Indian Prune', 0);
INSERT INTO `fruitname` VALUES (425, 'Indian Rhododendrom', 0);
INSERT INTO `fruitname` VALUES (426, 'Indian Snakework', 0);
INSERT INTO `fruitname` VALUES (427, 'Indian Turnip', 0);
INSERT INTO `fruitname` VALUES (428, 'Indian Wampi', 0);
INSERT INTO `fruitname` VALUES (429, 'Jaboticaba', 0);
INSERT INTO `fruitname` VALUES (430, 'Jackfruit', 0);
INSERT INTO `fruitname` VALUES (431, 'Jakfruit', 0);
INSERT INTO `fruitname` VALUES (432, 'Jamaica Cherry', 0);
INSERT INTO `fruitname` VALUES (433, 'Jamaican Honeysuckle', 0);
INSERT INTO `fruitname` VALUES (434, 'Jamberry', 0);
INSERT INTO `fruitname` VALUES (435, 'Jambolan', 0);
INSERT INTO `fruitname` VALUES (436, 'Jamfruit', 0);
INSERT INTO `fruitname` VALUES (437, 'Japanese Chestnut', 0);
INSERT INTO `fruitname` VALUES (438, 'Japanese Fiber Banana', 0);
INSERT INTO `fruitname` VALUES (439, 'Japanese Hackberry', 0);
INSERT INTO `fruitname` VALUES (440, 'Japanese Medlar', 0);
INSERT INTO `fruitname` VALUES (441, 'Japanese Pepper Leaf', 0);
INSERT INTO `fruitname` VALUES (442, 'Japanese Persimmon', 0);
INSERT INTO `fruitname` VALUES (443, 'Japanese Plum', 0);
INSERT INTO `fruitname` VALUES (444, 'Japanese Plum', 0);
INSERT INTO `fruitname` VALUES (445, 'Japanese Plum', 0);
INSERT INTO `fruitname` VALUES (446, 'Japanese Quince', 0);
INSERT INTO `fruitname` VALUES (447, 'Japanese Raisin Tree', 0);
INSERT INTO `fruitname` VALUES (448, 'Japanese Rose', 0);
INSERT INTO `fruitname` VALUES (449, 'Japanese Tea Bush', 0);
INSERT INTO `fruitname` VALUES (450, 'Japanese Yew', 0);
INSERT INTO `fruitname` VALUES (451, 'Japanese Yew', 0);
INSERT INTO `fruitname` VALUES (452, 'Java Almond', 0);
INSERT INTO `fruitname` VALUES (453, 'Java Apple', 0);
INSERT INTO `fruitname` VALUES (454, 'Java Olive', 0);
INSERT INTO `fruitname` VALUES (455, 'Java Plum', 0);
INSERT INTO `fruitname` VALUES (456, 'Javanese Almond', 0);
INSERT INTO `fruitname` VALUES (457, 'Jelly Palm', 0);
INSERT INTO `fruitname` VALUES (458, 'Jerusalem Artichoke', 0);
INSERT INTO `fruitname` VALUES (459, 'Jicama', 0);
INSERT INTO `fruitname` VALUES (460, 'Jojoba', 0);
INSERT INTO `fruitname` VALUES (461, 'Jostaberry', 0);
INSERT INTO `fruitname` VALUES (462, 'Jujube', 0);
INSERT INTO `fruitname` VALUES (463, 'Juneberry', 0);
INSERT INTO `fruitname` VALUES (464, 'Juneberry', 0);
INSERT INTO `fruitname` VALUES (465, 'Kaffir Lime', 0);
INSERT INTO `fruitname` VALUES (466, 'Kaffir Orange', 0);
INSERT INTO `fruitname` VALUES (467, 'Kaffir Plum', 0);
INSERT INTO `fruitname` VALUES (468, 'Kaffir Plum', 0);
INSERT INTO `fruitname` VALUES (469, 'Kaki', 0);
INSERT INTO `fruitname` VALUES (470, 'Kalo', 0);
INSERT INTO `fruitname` VALUES (471, 'Kangaroo Apple', 0);
INSERT INTO `fruitname` VALUES (472, 'Karanda', 0);
INSERT INTO `fruitname` VALUES (473, 'Karanda Nut', 0);
INSERT INTO `fruitname` VALUES (474, 'Kashun', 0);
INSERT INTO `fruitname` VALUES (475, 'Katmon', 0);
INSERT INTO `fruitname` VALUES (476, 'Kava Kava', 0);
INSERT INTO `fruitname` VALUES (477, 'Kawa', 0);
INSERT INTO `fruitname` VALUES (478, 'Kawakawa', 0);
INSERT INTO `fruitname` VALUES (479, 'Kei Apple', 0);
INSERT INTO `fruitname` VALUES (480, 'Ken''s Red', 0);
INSERT INTO `fruitname` VALUES (481, 'Kenaf', 0);
INSERT INTO `fruitname` VALUES (482, 'Kepel (Keppel) Apple', 0);
INSERT INTO `fruitname` VALUES (483, 'Ketembilla', 0);
INSERT INTO `fruitname` VALUES (484, 'Ketoepa', 0);
INSERT INTO `fruitname` VALUES (485, 'Khirni', 0);
INSERT INTO `fruitname` VALUES (486, 'King Orange', 0);
INSERT INTO `fruitname` VALUES (487, 'Kitembilla', 0);
INSERT INTO `fruitname` VALUES (488, 'Kivai Muk', 0);
INSERT INTO `fruitname` VALUES (489, 'Kiwano', 0);
INSERT INTO `fruitname` VALUES (490, 'Kiwifruit', 0);
INSERT INTO `fruitname` VALUES (491, 'Kokuwa', 0);
INSERT INTO `fruitname` VALUES (492, 'Kola Nut', 0);
INSERT INTO `fruitname` VALUES (493, 'Kola Nut', 0);
INSERT INTO `fruitname` VALUES (494, 'Kolomikta', 0);
INSERT INTO `fruitname` VALUES (495, 'Koorkup', 0);
INSERT INTO `fruitname` VALUES (496, 'Koshum', 0);
INSERT INTO `fruitname` VALUES (497, 'Kuko', 0);
INSERT INTO `fruitname` VALUES (498, 'Kumquat', 0);
INSERT INTO `fruitname` VALUES (499, '"Kuwini', 0);
INSERT INTO `fruitname` VALUES (500, 'Kwai Muk', 0);
INSERT INTO `fruitname` VALUES (501, 'Lady Apple', 0);
INSERT INTO `fruitname` VALUES (502, 'Lakoocha', 0);
INSERT INTO `fruitname` VALUES (503, 'Langsat', 0);
INSERT INTO `fruitname` VALUES (504, 'Lanzone', 0);
INSERT INTO `fruitname` VALUES (505, 'Largo Lulo', 0);
INSERT INTO `fruitname` VALUES (506, 'Lemon', 0);
INSERT INTO `fruitname` VALUES (507, 'Lemon Grass', 0);
INSERT INTO `fruitname` VALUES (508, 'Lemon Guava', 0);
INSERT INTO `fruitname` VALUES (509, 'Lemon Vine', 0);
INSERT INTO `fruitname` VALUES (510, 'Liberian Coffee', 0);
INSERT INTO `fruitname` VALUES (511, 'Lilly-pilly', 0);
INSERT INTO `fruitname` VALUES (512, 'Lime', 0);
INSERT INTO `fruitname` VALUES (513, 'Limeberry', 0);
INSERT INTO `fruitname` VALUES (514, 'Ling Nut', 0);
INSERT INTO `fruitname` VALUES (515, 'Lingaro', 0);
INSERT INTO `fruitname` VALUES (516, 'Lingonberry', 0);
INSERT INTO `fruitname` VALUES (517, 'Lipote', 0);
INSERT INTO `fruitname` VALUES (518, 'Lipstick Tree', 0);
INSERT INTO `fruitname` VALUES (519, 'Litchee', 0);
INSERT INTO `fruitname` VALUES (520, 'Litchi', 0);
INSERT INTO `fruitname` VALUES (521, 'Llama: see Ilama', 0);
INSERT INTO `fruitname` VALUES (522, 'Longan', 0);
INSERT INTO `fruitname` VALUES (523, 'Loquat', 0);
INSERT INTO `fruitname` VALUES (524, 'Louvi', 0);
INSERT INTO `fruitname` VALUES (525, 'Love Apple', 0);
INSERT INTO `fruitname` VALUES (526, 'Love-in-a-mist', 0);
INSERT INTO `fruitname` VALUES (527, 'Lovi-Lovi', 0);
INSERT INTO `fruitname` VALUES (528, 'Lowbush Blueberry', 0);
INSERT INTO `fruitname` VALUES (529, 'Lowbush Blueberry', 0);
INSERT INTO `fruitname` VALUES (530, 'Lowbush Blueberry', 0);
INSERT INTO `fruitname` VALUES (531, 'Lucma', 0);
INSERT INTO `fruitname` VALUES (532, 'Lucmo', 0);
INSERT INTO `fruitname` VALUES (533, 'Lucuma', 0);
INSERT INTO `fruitname` VALUES (534, 'Lulita', 0);
INSERT INTO `fruitname` VALUES (535, 'Luma', 0);
INSERT INTO `fruitname` VALUES (536, 'Lychee', 0);
INSERT INTO `fruitname` VALUES (550, 'Mabolo', 0);
INSERT INTO `fruitname` VALUES (551, 'Mabulo', 0);
INSERT INTO `fruitname` VALUES (552, 'Macadamia Nut', 0);
INSERT INTO `fruitname` VALUES (553, 'Macadamia Nut', 0);
INSERT INTO `fruitname` VALUES (554, 'Madagascar Olive', 0);
INSERT INTO `fruitname` VALUES (555, 'Madagascar Plum', 0);
INSERT INTO `fruitname` VALUES (556, 'Madagascar Plum', 0);
INSERT INTO `fruitname` VALUES (557, 'Madrono', 0);
INSERT INTO `fruitname` VALUES (558, 'Magnolia Vine', 0);
INSERT INTO `fruitname` VALUES (559, 'Maidehair Tree', 0);
INSERT INTO `fruitname` VALUES (560, 'Makopa', 0);
INSERT INTO `fruitname` VALUES (561, 'Makrut', 0);
INSERT INTO `fruitname` VALUES (562, 'Malabar Chestnut', 0);
INSERT INTO `fruitname` VALUES (563, 'Malabar Melathstome', 0);
INSERT INTO `fruitname` VALUES (564, 'Malay Apple', 0);
INSERT INTO `fruitname` VALUES (565, 'Malay Jujube', 0);
INSERT INTO `fruitname` VALUES (566, 'Mamey', 0);
INSERT INTO `fruitname` VALUES (567, 'Mamey Colorado', 0);
INSERT INTO `fruitname` VALUES (568, 'Mamey Sapote', 0);
INSERT INTO `fruitname` VALUES (569, 'Mammee Apple', 0);
INSERT INTO `fruitname` VALUES (570, 'Mamoncillo', 0);
INSERT INTO `fruitname` VALUES (571, 'Mandarin Orange', 0);
INSERT INTO `fruitname` VALUES (572, 'Mangaba', 0);
INSERT INTO `fruitname` VALUES (573, 'Mango', 0);
INSERT INTO `fruitname` VALUES (574, 'Mangosteen', 0);
INSERT INTO `fruitname` VALUES (575, 'Manis', 0);
INSERT INTO `fruitname` VALUES (576, 'Manmohpan', 0);
INSERT INTO `fruitname` VALUES (577, 'Mape', 0);
INSERT INTO `fruitname` VALUES (578, 'Maprang', 0);
INSERT INTO `fruitname` VALUES (579, 'Maprang', 0);
INSERT INTO `fruitname` VALUES (580, 'Marang', 0);
INSERT INTO `fruitname` VALUES (581, 'Marany Nut', 0);
INSERT INTO `fruitname` VALUES (582, 'Marking Nut', 0);
INSERT INTO `fruitname` VALUES (583, 'Marmalade Box', 0);
INSERT INTO `fruitname` VALUES (584, 'Marmalade Plum', 0);
INSERT INTO `fruitname` VALUES (585, 'Marsh Nut', 0);
INSERT INTO `fruitname` VALUES (586, 'Martin', 0);
INSERT INTO `fruitname` VALUES (587, 'Martinique Plum', 0);
INSERT INTO `fruitname` VALUES (588, 'Marula', 0);
INSERT INTO `fruitname` VALUES (589, 'Marumi Kumquat', 0);
INSERT INTO `fruitname` VALUES (590, 'Marvala Plum', 0);
INSERT INTO `fruitname` VALUES (591, 'Matasano', 0);
INSERT INTO `fruitname` VALUES (592, 'Mate', 0);
INSERT INTO `fruitname` VALUES (593, 'Matrimony Vine', 0);
INSERT INTO `fruitname` VALUES (594, 'Mauritius Raspberry', 0);
INSERT INTO `fruitname` VALUES (595, 'May Cherry', 0);
INSERT INTO `fruitname` VALUES (596, 'Mayan Breadnut', 0);
INSERT INTO `fruitname` VALUES (597, 'Mayhaw', 0);
INSERT INTO `fruitname` VALUES (598, 'Maypop', 0);
INSERT INTO `fruitname` VALUES (599, 'Medlar', 0);
INSERT INTO `fruitname` VALUES (600, 'Medlar', 0);
INSERT INTO `fruitname` VALUES (601, 'Meiwa Kumquat', 0);
INSERT INTO `fruitname` VALUES (602, 'Mexican Avocado', 0);
INSERT INTO `fruitname` VALUES (603, 'Mexican Barberry', 0);
INSERT INTO `fruitname` VALUES (604, 'Mexican Breadfruit', 0);
INSERT INTO `fruitname` VALUES (605, 'Mexican Calabash', 0);
INSERT INTO `fruitname` VALUES (606, 'Mexican Lime', 0);
INSERT INTO `fruitname` VALUES (607, 'Meyer Lemon', 0);
INSERT INTO `fruitname` VALUES (608, 'Michurin Actinidia', 0);
INSERT INTO `fruitname` VALUES (609, 'Miner''s Lettuce', 0);
INSERT INTO `fruitname` VALUES (610, 'Miracle Fruit', 0);
INSERT INTO `fruitname` VALUES (611, 'Mississippi Honeyberry', 0);
INSERT INTO `fruitname` VALUES (612, 'Missouri Currant', 0);
INSERT INTO `fruitname` VALUES (613, 'Missouri Currant', 0);
INSERT INTO `fruitname` VALUES (614, 'Mocambo', 0);
INSERT INTO `fruitname` VALUES (615, 'Monkey Jack', 0);
INSERT INTO `fruitname` VALUES (616, 'Monkey Nut', 0);
INSERT INTO `fruitname` VALUES (617, 'Monkey Pot', 0);
INSERT INTO `fruitname` VALUES (618, 'Monkey Pot', 0);
INSERT INTO `fruitname` VALUES (619, 'Monkey Pot', 0);
INSERT INTO `fruitname` VALUES (620, 'Monkey Puzzle Tree', 0);
INSERT INTO `fruitname` VALUES (621, 'Monkey Tamarind', 0);
INSERT INTO `fruitname` VALUES (622, 'Monos Plum', 0);
INSERT INTO `fruitname` VALUES (623, 'Monstera', 0);
INSERT INTO `fruitname` VALUES (624, 'Montesa Granadilla', 0);
INSERT INTO `fruitname` VALUES (625, 'Moosewood', 0);
INSERT INTO `fruitname` VALUES (626, 'Moosewood', 0);
INSERT INTO `fruitname` VALUES (627, 'Moosewood', 0);
INSERT INTO `fruitname` VALUES (628, 'Mora de Castilla', 0);
INSERT INTO `fruitname` VALUES (629, 'Moreton Bay Chestnut', 0);
INSERT INTO `fruitname` VALUES (630, 'Moringa', 0);
INSERT INTO `fruitname` VALUES (631, 'Mountain Apple', 0);
INSERT INTO `fruitname` VALUES (632, 'Mountain Papaya', 0);
INSERT INTO `fruitname` VALUES (633, 'Mountain soursop', 0);
INSERT INTO `fruitname` VALUES (634, 'Mowha', 0);
INSERT INTO `fruitname` VALUES (635, 'Mulberry', 0);
INSERT INTO `fruitname` VALUES (636, 'Mundu', 0);
INSERT INTO `fruitname` VALUES (637, 'Musk Strawberry', 0);
INSERT INTO `fruitname` VALUES (638, 'Myrobalan', 0);
INSERT INTO `fruitname` VALUES (639, 'Myrobalan', 0);
INSERT INTO `fruitname` VALUES (640, 'Myrobalan Plum', 0);
INSERT INTO `fruitname` VALUES (641, 'Myrtle', 0);
INSERT INTO `fruitname` VALUES (642, 'Mysore Black Raspberry', 0);
INSERT INTO `fruitname` VALUES (643, 'Nagami Kumquat', 0);
INSERT INTO `fruitname` VALUES (644, 'Namnam', 0);
INSERT INTO `fruitname` VALUES (645, 'Nance', 0);
INSERT INTO `fruitname` VALUES (646, 'Nanking Cherry', 0);
INSERT INTO `fruitname` VALUES (647, 'Naranjilla', 0);
INSERT INTO `fruitname` VALUES (648, 'Natal Orange', 0);
INSERT INTO `fruitname` VALUES (649, 'Natal Plum', 0);
INSERT INTO `fruitname` VALUES (650, 'Natal Plum', 0);
INSERT INTO `fruitname` VALUES (651, 'Nauclea', 0);
INSERT INTO `fruitname` VALUES (652, 'Nectarine', 0);
INSERT INTO `fruitname` VALUES (653, 'Neem Tree', 0);
INSERT INTO `fruitname` VALUES (654, 'Nervosa', 0);
INSERT INTO `fruitname` VALUES (655, 'New Zealand Spinach', 0);
INSERT INTO `fruitname` VALUES (656, 'Night Blooming Cereus', 0);
INSERT INTO `fruitname` VALUES (657, 'Night-Blooming Cereus', 0);
INSERT INTO `fruitname` VALUES (658, 'Nipa Palm', 0);
INSERT INTO `fruitname` VALUES (659, 'Nipple Fruit', 0);
INSERT INTO `fruitname` VALUES (660, 'Nispero', 0);
INSERT INTO `fruitname` VALUES (661, 'Nutmeg', 0);
INSERT INTO `fruitname` VALUES (662, 'Ogeechee Lime or Plum', 0);
INSERT INTO `fruitname` VALUES (663, 'Okari Nut', 0);
INSERT INTO `fruitname` VALUES (664, 'Okari Nut', 0);
INSERT INTO `fruitname` VALUES (665, 'Okra', 0);
INSERT INTO `fruitname` VALUES (666, 'Olallie Berry', 0);
INSERT INTO `fruitname` VALUES (667, 'Olosapo', 0);
INSERT INTO `fruitname` VALUES (668, 'Orange', 0);
INSERT INTO `fruitname` VALUES (669, 'Orangeberry', 0);
INSERT INTO `fruitname` VALUES (670, 'Oregon Crab Apple', 0);
INSERT INTO `fruitname` VALUES (671, 'Oregon Grape', 0);
INSERT INTO `fruitname` VALUES (672, 'Oregon Grape', 0);
INSERT INTO `fruitname` VALUES (673, 'Organpipe Cactus', 0);
INSERT INTO `fruitname` VALUES (674, 'Oriental Cashew', 0);
INSERT INTO `fruitname` VALUES (675, 'Oriental Cherry', 0);
INSERT INTO `fruitname` VALUES (676, 'Oriental Persimmon', 0);
INSERT INTO `fruitname` VALUES (677, 'Oswego Tea', 0);
INSERT INTO `fruitname` VALUES (678, 'Otaheite Apple', 0);
INSERT INTO `fruitname` VALUES (679, 'Otaheite Chestnut', 0);
INSERT INTO `fruitname` VALUES (680, 'Otaheite Gooseberry', 0);
INSERT INTO `fruitname` VALUES (681, 'Otaheite Orange', 0);
INSERT INTO `fruitname` VALUES (682, 'Otaheite Rangpur', 0);
INSERT INTO `fruitname` VALUES (683, 'Otaite Orange', 0);
INSERT INTO `fruitname` VALUES (684, 'Oval Kumquat', 0);
INSERT INTO `fruitname` VALUES (685, 'Oyster Nut', 0);
INSERT INTO `fruitname` VALUES (686, 'Oyster Plant', 0);
INSERT INTO `fruitname` VALUES (687, 'Pacay', 0);
INSERT INTO `fruitname` VALUES (688, 'Paco', 0);
INSERT INTO `fruitname` VALUES (689, 'Pacura', 0);
INSERT INTO `fruitname` VALUES (690, 'Palestine Sweet Lime', 0);
INSERT INTO `fruitname` VALUES (691, 'Palm Fig', 0);
INSERT INTO `fruitname` VALUES (692, 'Palmyra Palm', 0);
INSERT INTO `fruitname` VALUES (693, 'Pama', 0);
INSERT INTO `fruitname` VALUES (694, 'Panama Berry', 0);
INSERT INTO `fruitname` VALUES (695, 'Panama Nut', 0);
INSERT INTO `fruitname` VALUES (696, 'Pandang', 0);
INSERT INTO `fruitname` VALUES (697, 'Pandanus', 0);
INSERT INTO `fruitname` VALUES (698, 'Paniala', 0);
INSERT INTO `fruitname` VALUES (699, 'Papache', 0);
INSERT INTO `fruitname` VALUES (700, 'Papaya', 0);
INSERT INTO `fruitname` VALUES (701, 'Para Guava', 0);
INSERT INTO `fruitname` VALUES (702, 'Paradise Nut', 0);
INSERT INTO `fruitname` VALUES (703, 'Paraguay Tea', 0);
INSERT INTO `fruitname` VALUES (704, 'Paterno', 0);
INSERT INTO `fruitname` VALUES (705, 'Paw Paw See note.', 0);
INSERT INTO `fruitname` VALUES (706, 'Paw Paw See note.', 0);
INSERT INTO `fruitname` VALUES (707, 'Peach', 0);
INSERT INTO `fruitname` VALUES (708, 'Peach Palm', 0);
INSERT INTO `fruitname` VALUES (709, 'Peach Tomato', 0);
INSERT INTO `fruitname` VALUES (710, 'Peanut', 0);
INSERT INTO `fruitname` VALUES (711, 'Peanut Butter Fruit', 0);
INSERT INTO `fruitname` VALUES (712, 'Pear', 0);
INSERT INTO `fruitname` VALUES (713, 'Pecan', 0);
INSERT INTO `fruitname` VALUES (714, 'Pedalai', 0);
INSERT INTO `fruitname` VALUES (715, 'Pejibaye', 0);
INSERT INTO `fruitname` VALUES (716, 'Pepino', 0);
INSERT INTO `fruitname` VALUES (717, 'Pepino Dulce', 0);
INSERT INTO `fruitname` VALUES (718, 'Pero do Campo', 0);
INSERT INTO `fruitname` VALUES (719, 'Persian Lime', 0);
INSERT INTO `fruitname` VALUES (720, 'Persian Mulberry', 0);
INSERT INTO `fruitname` VALUES (721, 'Persian Walnut', 0);
INSERT INTO `fruitname` VALUES (722, 'Persimmon', 0);
INSERT INTO `fruitname` VALUES (723, 'Phalsa Cherry', 0);
INSERT INTO `fruitname` VALUES (724, 'Philippine Tea', 0);
INSERT INTO `fruitname` VALUES (725, 'Phillippine Fig', 0);
INSERT INTO `fruitname` VALUES (726, 'Phillippine Palm', 0);
INSERT INTO `fruitname` VALUES (727, 'Pickle Fruit', 0);
INSERT INTO `fruitname` VALUES (728, 'Pili Nut', 0);
INSERT INTO `fruitname` VALUES (729, 'Pili Nut', 0);
INSERT INTO `fruitname` VALUES (730, 'Pimenta', 0);
INSERT INTO `fruitname` VALUES (731, 'Pimento', 0);
INSERT INTO `fruitname` VALUES (732, 'Pin Cushion Tree', 0);
INSERT INTO `fruitname` VALUES (733, 'Pindo Palm', 0);
INSERT INTO `fruitname` VALUES (734, 'Pineapple', 0);
INSERT INTO `fruitname` VALUES (735, 'Pineapple Guava', 0);
INSERT INTO `fruitname` VALUES (736, 'Pink Banana', 0);
INSERT INTO `fruitname` VALUES (737, 'Pinguin', 0);
INSERT INTO `fruitname` VALUES (738, 'Pinyon Pine', 0);
INSERT INTO `fruitname` VALUES (739, 'Pistachio', 0);
INSERT INTO `fruitname` VALUES (740, 'Pitahaya', 0);
INSERT INTO `fruitname` VALUES (741, 'Pitanga', 0);
INSERT INTO `fruitname` VALUES (742, 'Pitaya', 0);
INSERT INTO `fruitname` VALUES (743, 'Pitaya', 0);
INSERT INTO `fruitname` VALUES (744, 'Pitomba', 0);
INSERT INTO `fruitname` VALUES (745, 'Plantain', 0);
INSERT INTO `fruitname` VALUES (746, 'Plum', 0);
INSERT INTO `fruitname` VALUES (747, 'Plum Mango', 0);
INSERT INTO `fruitname` VALUES (748, 'Poha', 0);
INSERT INTO `fruitname` VALUES (749, 'Pollia', 0);
INSERT INTO `fruitname` VALUES (750, 'Polynesian Chestnut', 0);
INSERT INTO `fruitname` VALUES (751, 'Pomegranate', 0);
INSERT INTO `fruitname` VALUES (752, 'Pond apple', 0);
INSERT INTO `fruitname` VALUES (753, 'Poshte', 0);
INSERT INTO `fruitname` VALUES (754, 'Potato Tree', 0);
INSERT INTO `fruitname` VALUES (755, 'Prairie Potato', 0);
INSERT INTO `fruitname` VALUES (756, 'Puerto Rican Guava', 0);
INSERT INTO `fruitname` VALUES (757, 'Pulasan', 0);
INSERT INTO `fruitname` VALUES (758, 'Pummelo', 0);
INSERT INTO `fruitname` VALUES (759, 'Purple Calabash Tomato', 0);
INSERT INTO `fruitname` VALUES (760, 'Purple Granadilla', 0);
INSERT INTO `fruitname` VALUES (761, 'Purple Ground Cherry', 0);
INSERT INTO `fruitname` VALUES (762, 'Purple Mombin', 0);
INSERT INTO `fruitname` VALUES (763, 'Purple Passion Fruit', 0);
INSERT INTO `fruitname` VALUES (764, 'Purpurea', 0);
INSERT INTO `fruitname` VALUES (765, 'Quandong', 0);
INSERT INTO `fruitname` VALUES (766, 'Quandong', 0);
INSERT INTO `fruitname` VALUES (767, 'Queen Sago', 0);
INSERT INTO `fruitname` VALUES (768, 'Queensland Nut', 0);
INSERT INTO `fruitname` VALUES (769, 'Quince', 0);
INSERT INTO `fruitname` VALUES (770, 'Quinine', 0);
INSERT INTO `fruitname` VALUES (771, 'Rabbiteye Blueberry', 0);
INSERT INTO `fruitname` VALUES (772, 'Raisin Tree', 0);
INSERT INTO `fruitname` VALUES (773, 'Rambai', 0);
INSERT INTO `fruitname` VALUES (774, 'Rambai', 0);
INSERT INTO `fruitname` VALUES (775, 'Rambai Utan', 0);
INSERT INTO `fruitname` VALUES (776, 'Rambeh', 0);
INSERT INTO `fruitname` VALUES (777, 'Rambutan', 0);
INSERT INTO `fruitname` VALUES (778, 'Ramontchi', 0);
INSERT INTO `fruitname` VALUES (779, 'Rangpur Lime', 0);
INSERT INTO `fruitname` VALUES (780, 'Raspberry', 0);
INSERT INTO `fruitname` VALUES (781, 'Raspberry Jam Fruit', 0);
INSERT INTO `fruitname` VALUES (782, 'Rata', 0);
INSERT INTO `fruitname` VALUES (783, 'Rata', 0);
INSERT INTO `fruitname` VALUES (784, 'Red Bay', 0);
INSERT INTO `fruitname` VALUES (785, 'Red Currant', 0);
INSERT INTO `fruitname` VALUES (786, 'Red Guava', 0);
INSERT INTO `fruitname` VALUES (787, 'Red Huckleberry', 0);
INSERT INTO `fruitname` VALUES (788, 'Red Ironwood', 0);
INSERT INTO `fruitname` VALUES (789, 'Red Mombin', 0);
INSERT INTO `fruitname` VALUES (790, 'Red Mulberry', 0);
INSERT INTO `fruitname` VALUES (791, 'Red Princess', 0);
INSERT INTO `fruitname` VALUES (792, 'Red Strawberry Guava', 0);
INSERT INTO `fruitname` VALUES (793, 'Rinon', 0);
INSERT INTO `fruitname` VALUES (794, 'River Plum', 0);
INSERT INTO `fruitname` VALUES (795, 'Riverflat', 0);
INSERT INTO `fruitname` VALUES (796, 'Robusta Coffee', 0);
INSERT INTO `fruitname` VALUES (797, 'Rose Apple', 0);
INSERT INTO `fruitname` VALUES (798, 'Roselle', 0);
INSERT INTO `fruitname` VALUES (799, 'Rough Lemon', 0);
INSERT INTO `fruitname` VALUES (800, 'Rough Shell Macadamia', 0);
INSERT INTO `fruitname` VALUES (801, 'Round Kumquat', 0);
INSERT INTO `fruitname` VALUES (802, 'Roundleaf Serviceberry', 0);
INSERT INTO `fruitname` VALUES (803, 'Rowan', 0);
INSERT INTO `fruitname` VALUES (804, 'Rowanberry', 0);
INSERT INTO `fruitname` VALUES (805, 'Ruffled Tomato', 0);
INSERT INTO `fruitname` VALUES (806, 'Rukam', 0);
INSERT INTO `fruitname` VALUES (807, 'Rum Berry', 0);
INSERT INTO `fruitname` VALUES (808, 'Rum Cherry', 0);
INSERT INTO `fruitname` VALUES (809, 'Runealma Plum', 0);
INSERT INTO `fruitname` VALUES (810, 'Russian Mulberry', 0);
INSERT INTO `fruitname` VALUES (811, 'Russian Olive', 0);

-- Table structure for table `fruitname2`
CREATE TABLE `fruitname2` (
  `no` int(11) NOT NULL,
  `fruitname` varchar(30) NOT NULL,
  `taken` tinyint(4) NOT NULL,
  PRIMARY KEY  (`no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- Dumping data for table `fruitname2`
INSERT INTO `fruitname2` VALUES (90, 'Bear', 0);
INSERT INTO `fruitname2` VALUES (91, 'Bee', 1);
INSERT INTO `fruitname2` VALUES (92, 'Belimb', 0);
INSERT INTO `fruitname2` VALUES (93, 'Bell', 0);
INSERT INTO `fruitname2` VALUES (94, 'Berry', 0);
INSERT INTO `fruitname2` VALUES (95, 'Berray', 0);
INSERT INTO `fruitname2` VALUES (96, 'Berris', 0);
INSERT INTO `fruitname2` VALUES (97, 'Berrty', 0);
INSERT INTO `fruitname2` VALUES (98, 'Betel', 9);
INSERT INTO `fruitname2` VALUES (99, 'Bigay', 9);
INSERT INTO `fruitname2` VALUES (100, 'Bignai', 9);
INSERT INTO `fruitname2` VALUES (101, 'Bignay', 9);
INSERT INTO `fruitname2` VALUES (102, 'Bilimbi', 9);
INSERT INTO `fruitname2` VALUES (103, 'Biriba', 10);
INSERT INTO `fruitname2` VALUES (104, 'Biribay', 0);
INSERT INTO `fruitname2` VALUES (105, 'Blackberry', 9);
INSERT INTO `fruitname2` VALUES (106, 'Blackbert', 0);
INSERT INTO `fruitname2` VALUES (107, 'Blackcap', 10);
INSERT INTO `fruitname2` VALUES (108, 'Bluebean ', 0);
INSERT INTO `fruitname2` VALUES (109, 'Blue Taro', 9);
INSERT INTO `fruitname2` VALUES (110, 'Blueberry', 9);
INSERT INTO `fruitname2` VALUES (111, 'Blueberra', 0);
INSERT INTO `fruitname2` VALUES (112, 'Bluebell', 0);
INSERT INTO `fruitname2` VALUES (113, 'Bokhara', 9);
INSERT INTO `fruitname2` VALUES (114, 'Bower', 9);
INSERT INTO `fruitname2` VALUES (115, 'Boysen', 11);
INSERT INTO `fruitname2` VALUES (116, 'Bramble', 9);
INSERT INTO `fruitname2` VALUES (117, 'Breadfruit', 0);
INSERT INTO `fruitname2` VALUES (118, 'Bread', 0);
INSERT INTO `fruitname2` VALUES (119, 'Breadnut', 0);
INSERT INTO `fruitname2` VALUES (120, 'Breadknot', 0);
INSERT INTO `fruitname2` VALUES (121, 'Breadroot', 9);
INSERT INTO `fruitname2` VALUES (122, 'Brier ', 10);
INSERT INTO `fruitname2` VALUES (123, 'Buah', 1);
INSERT INTO `fruitname2` VALUES (124, 'Bunch', 0);
INSERT INTO `fruitname2` VALUES (125, 'Bunchosia', 0);
INSERT INTO `fruitname2` VALUES (126, 'Buni', 9);
INSERT INTO `fruitname2` VALUES (127, 'Bunya', 0);
INSERT INTO `fruitname2` VALUES (128, 'Burdekin ', 9);
INSERT INTO `fruitname2` VALUES (129, 'Butter', 0);
INSERT INTO `fruitname2` VALUES (130, 'Butternut', 9);
INSERT INTO `fruitname2` VALUES (131, 'Button ', 9);
INSERT INTO `fruitname2` VALUES (132, 'Cacao', 9);
INSERT INTO `fruitname2` VALUES (133, 'Cactus', 9);
INSERT INTO `fruitname2` VALUES (134, 'Cactus', 9);
INSERT INTO `fruitname2` VALUES (135, 'Caimito', 11);
INSERT INTO `fruitname2` VALUES (136, 'Caimo', 9);
INSERT INTO `fruitname2` VALUES (137, 'Calamondin', 9);
INSERT INTO `fruitname2` VALUES (138, 'Calubura', 9);
INSERT INTO `fruitname2` VALUES (139, 'Camocamo', 9);
INSERT INTO `fruitname2` VALUES (140, 'Camu', 2);
INSERT INTO `fruitname2` VALUES (141, 'Canary', 9);
INSERT INTO `fruitname2` VALUES (142, 'Candlenut', 9);
INSERT INTO `fruitname2` VALUES (143, 'Canistel', 9);
INSERT INTO `fruitname2` VALUES (144, 'Cape ', 9);
INSERT INTO `fruitname2` VALUES (145, 'Caper', 9);
INSERT INTO `fruitname2` VALUES (146, 'Capulin ', 10);
INSERT INTO `fruitname2` VALUES (147, 'Carambola', 9);
INSERT INTO `fruitname2` VALUES (148, 'Carissa', 9);
INSERT INTO `fruitname2` VALUES (149, 'Carob', 9);
INSERT INTO `fruitname2` VALUES (150, 'Carpathian ', 9);
INSERT INTO `fruitname2` VALUES (151, 'Cas', 10);
INSERT INTO `fruitname2` VALUES (152, 'Casana', 9);
INSERT INTO `fruitname2` VALUES (153, 'Cascara', 9);
INSERT INTO `fruitname2` VALUES (154, 'Cashew', 9);
INSERT INTO `fruitname2` VALUES (155, 'Cassava', 9);
INSERT INTO `fruitname2` VALUES (156, 'Catal', 0);
INSERT INTO `fruitname2` VALUES (157, 'Catalina', 2);
INSERT INTO `fruitname2` VALUES (158, 'Cattley ', 9);
INSERT INTO `fruitname2` VALUES (159, 'Ceriman', 9);
INSERT INTO `fruitname2` VALUES (160, 'Ceylo', 1);
INSERT INTO `fruitname2` VALUES (161, 'Ceylone', 0);
INSERT INTO `fruitname2` VALUES (162, 'Champedek', 0);
INSERT INTO `fruitname2` VALUES (163, 'Changshou ', 0);
INSERT INTO `fruitname2` VALUES (164, 'Charicuela', 1);
INSERT INTO `fruitname2` VALUES (165, 'Chaste ', 0);
INSERT INTO `fruitname2` VALUES (166, 'Chayote', 0);
INSERT INTO `fruitname2` VALUES (167, 'Che', 0);
INSERT INTO `fruitname2` VALUES (168, 'Chempedale', 1);
INSERT INTO `fruitname2` VALUES (169, 'Cherapu', 0);
INSERT INTO `fruitname2` VALUES (170, 'Cheremai', 0);
INSERT INTO `fruitname2` VALUES (171, 'Cherimoya', 0);
INSERT INTO `fruitname2` VALUES (172, 'Cherryroot', 0);
INSERT INTO `fruitname2` VALUES (173, 'Cherryblo', 0);
INSERT INTO `fruitname2` VALUES (174, 'Chessapple', 1);
INSERT INTO `fruitname2` VALUES (175, 'Chestnull', 1);
INSERT INTO `fruitname2` VALUES (176, 'Chestav', 0);
INSERT INTO `fruitname2` VALUES (177, 'Chestken', 0);
INSERT INTO `fruitname2` VALUES (178, 'Chiaye', 1);
INSERT INTO `fruitname2` VALUES (179, 'Chicle', 0);
INSERT INTO `fruitname2` VALUES (180, 'Chico', 0);
INSERT INTO `fruitname2` VALUES (181, 'Chilean', 0);
INSERT INTO `fruitname2` VALUES (182, 'Chincopin', 0);
INSERT INTO `fruitname2` VALUES (183, 'Chinquapin', 1);
INSERT INTO `fruitname2` VALUES (184, 'Chitra', 0);
INSERT INTO `fruitname2` VALUES (185, 'Chocolate ', 0);
INSERT INTO `fruitname2` VALUES (186, 'Choke', 0);
INSERT INTO `fruitname2` VALUES (187, 'Chokey', 0);
INSERT INTO `fruitname2` VALUES (188, 'Chokecherry', 2);
INSERT INTO `fruitname2` VALUES (189, 'Chupa', 0);
INSERT INTO `fruitname2` VALUES (190, 'Ciku', 1);
INSERT INTO `fruitname2` VALUES (191, 'Cimarrona', 0);
INSERT INTO `fruitname2` VALUES (192, 'Cinnamon', 0);
INSERT INTO `fruitname2` VALUES (193, 'Cinnamen', 0);
INSERT INTO `fruitname2` VALUES (194, 'Ciruela', 0);
INSERT INTO `fruitname2` VALUES (195, 'Cirueler', 0);
INSERT INTO `fruitname2` VALUES (196, 'Ciruelo', 0);
INSERT INTO `fruitname2` VALUES (197, 'Ciruet', 0);
INSERT INTO `fruitname2` VALUES (198, 'Citron', 0);
INSERT INTO `fruitname2` VALUES (199, 'Citront', 0);
INSERT INTO `fruitname2` VALUES (200, 'Clove', 0);
INSERT INTO `fruitname2` VALUES (201, 'Clovet', 1);
INSERT INTO `fruitname2` VALUES (202, 'Clover', 0);
INSERT INTO `fruitname2` VALUES (203, 'Cochin', 0);
INSERT INTO `fruitname2` VALUES (204, 'Cocoa', 0);
INSERT INTO `fruitname2` VALUES (205, 'Cocona', 0);
INSERT INTO `fruitname2` VALUES (206, 'Coconut ', 1);
INSERT INTO `fruitname2` VALUES (207, 'Cocoplum', 0);
INSERT INTO `fruitname2` VALUES (208, 'Coffee ', 0);
INSERT INTO `fruitname2` VALUES (209, 'Columbian ', 0);
INSERT INTO `fruitname2` VALUES (210, 'Cometure', 0);
INSERT INTO `fruitname2` VALUES (211, 'Conch ', 1);
INSERT INTO `fruitname2` VALUES (212, 'Coontie', 0);
INSERT INTO `fruitname2` VALUES (213, 'Cornelian ', 0);
INSERT INTO `fruitname2` VALUES (214, 'Corosol', 0);
INSERT INTO `fruitname2` VALUES (215, 'Corozo', 0);
INSERT INTO `fruitname2` VALUES (216, 'Cotopriz', 0);
INSERT INTO `fruitname2` VALUES (217, 'Country', 0);
INSERT INTO `fruitname2` VALUES (218, 'Coyo', 2);
INSERT INTO `fruitname2` VALUES (219, 'Crabapple', 1);
INSERT INTO `fruitname2` VALUES (220, 'Crabap', 0);
INSERT INTO `fruitname2` VALUES (221, 'Cranberry', 0);
INSERT INTO `fruitname2` VALUES (222, 'Cranbert', 0);
INSERT INTO `fruitname2` VALUES (223, 'Crato', 0);
INSERT INTO `fruitname2` VALUES (224, 'Cuachilote', 0);
INSERT INTO `fruitname2` VALUES (225, 'Cupu', 0);
INSERT INTO `fruitname2` VALUES (226, 'Currant', 0);
INSERT INTO `fruitname2` VALUES (227, 'Curranty', 0);
INSERT INTO `fruitname2` VALUES (228, 'Curranton', 0);
INSERT INTO `fruitname2` VALUES (229, 'Curranter', 0);
INSERT INTO `fruitname2` VALUES (230, 'Current', 1);
INSERT INTO `fruitname2` VALUES (231, 'Curry ', 0);
INSERT INTO `fruitname2` VALUES (232, 'Curuba', 1);
INSERT INTO `fruitname2` VALUES (233, 'Custard', 0);
INSERT INTO `fruitname2` VALUES (234, 'Custar', 1);
INSERT INTO `fruitname2` VALUES (235, 'Dalison', 1);
INSERT INTO `fruitname2` VALUES (236, 'Dalo', 1);
INSERT INTO `fruitname2` VALUES (237, 'Damson', 0);
INSERT INTO `fruitname2` VALUES (238, 'Damser', 0);
INSERT INTO `fruitname2` VALUES (239, 'Dangleberry', 0);
INSERT INTO `fruitname2` VALUES (240, 'Darling', 0);
INSERT INTO `fruitname2` VALUES (241, 'Dasheen', 0);
INSERT INTO `fruitname2` VALUES (242, 'Date', 0);
INSERT INTO `fruitname2` VALUES (243, 'Datepalm', 0);
INSERT INTO `fruitname2` VALUES (244, 'Dateplum', 0);
INSERT INTO `fruitname2` VALUES (245, 'David', 1);
INSERT INTO `fruitname2` VALUES (246, 'Desert', 1);
INSERT INTO `fruitname2` VALUES (247, 'Dewberry', 0);
INSERT INTO `fruitname2` VALUES (248, 'Dogwood', 1);
INSERT INTO `fruitname2` VALUES (249, 'Downy', 0);
INSERT INTO `fruitname2` VALUES (250, 'Duku', 0);
INSERT INTO `fruitname2` VALUES (251, 'Durian', 1);
INSERT INTO `fruitname2` VALUES (252, 'Duria', 0);
INSERT INTO `fruitname2` VALUES (253, 'Dwarf ', 0);
INSERT INTO `fruitname2` VALUES (254, 'Early ', 0);
INSERT INTO `fruitname2` VALUES (255, 'Eddo', 0);
INSERT INTO `fruitname2` VALUES (256, 'Eggfruit', 1);
INSERT INTO `fruitname2` VALUES (257, 'Elderber', 0);
INSERT INTO `fruitname2` VALUES (258, 'Elderberry', 0);
INSERT INTO `fruitname2` VALUES (259, 'Elderbert', 0);
INSERT INTO `fruitname2` VALUES (260, 'Elderbet', 1);
INSERT INTO `fruitname2` VALUES (261, 'Elderbar', 0);
INSERT INTO `fruitname2` VALUES (262, 'Elephant ', 0);
INSERT INTO `fruitname2` VALUES (263, 'Elepha', 0);
INSERT INTO `fruitname2` VALUES (264, 'Emblic', 0);
INSERT INTO `fruitname2` VALUES (265, 'Engkala', 0);
INSERT INTO `fruitname2` VALUES (266, 'Escobillo', 0);
INSERT INTO `fruitname2` VALUES (267, 'Etrog', 0);
INSERT INTO `fruitname2` VALUES (268, 'Gooseberry', 0);
INSERT INTO `fruitname2` VALUES (269, 'Gooseber', 0);
INSERT INTO `fruitname2` VALUES (270, 'Grape', 1);
INSERT INTO `fruitname2` VALUES (271, 'Guava', 0);
INSERT INTO `fruitname2` VALUES (272, 'Guavac', 0);
INSERT INTO `fruitname2` VALUES (273, 'Hackberry', 11);
INSERT INTO `fruitname2` VALUES (274, 'Hackbert', 0);
INSERT INTO `fruitname2` VALUES (275, 'Haw', 0);
INSERT INTO `fruitname2` VALUES (276, 'Hazel', 0);
INSERT INTO `fruitname2` VALUES (277, 'Hazelnut', 0);
INSERT INTO `fruitname2` VALUES (278, 'Hibiscus', 0);
INSERT INTO `fruitname2` VALUES (279, 'Jamfruit', 2);
INSERT INTO `fruitname2` VALUES (280, 'Juniper', 0);
INSERT INTO `fruitname2` VALUES (281, 'Lilly', 1);
INSERT INTO `fruitname2` VALUES (282, 'Mamey', 1);
INSERT INTO `fruitname2` VALUES (283, 'Mangosteen', 0);
INSERT INTO `fruitname2` VALUES (284, 'Mulberry', 0);
INSERT INTO `fruitname2` VALUES (285, 'Nut', 0);
INSERT INTO `fruitname2` VALUES (286, 'Passion ', 0);
INSERT INTO `fruitname2` VALUES (287, 'Passiona', 0);
INSERT INTO `fruitname2` VALUES (288, 'Paw', 0);
INSERT INTO `fruitname2` VALUES (289, 'Peach', 0);
INSERT INTO `fruitname2` VALUES (290, 'Pepper', 0);
INSERT INTO `fruitname2` VALUES (291, 'Persimmon', 0);
INSERT INTO `fruitname2` VALUES (292, 'Persim', 0);
INSERT INTO `fruitname2` VALUES (293, 'Plum', 0);
INSERT INTO `fruitname2` VALUES (294, 'Quince', 0);
INSERT INTO `fruitname2` VALUES (295, 'Quincer', 0);
INSERT INTO `fruitname2` VALUES (296, 'Rica', 0);
INSERT INTO `fruitname2` VALUES (297, 'Sapote', 0);
INSERT INTO `fruitname2` VALUES (298, 'Sapoten', 0);
INSERT INTO `fruitname2` VALUES (299, 'Spinach', 0);
INSERT INTO `fruitname2` VALUES (300, 'Tamarind', 1);
INSERT INTO `fruitname2` VALUES (301, 'Thorn', 1);
INSERT INTO `fruitname2` VALUES (302, 'Tomato', 1);
INSERT INTO `fruitname2` VALUES (303, 'Walnull', 0);
INSERT INTO `fruitname2` VALUES (304, 'Walnut', 0);
INSERT INTO `fruitname2` VALUES (305, 'Walker', 0);
INSERT INTO `fruitname2` VALUES (306, 'Walknot', 0);
INSERT INTO `fruitname2` VALUES (307, 'Wildgrape', 0);
INSERT INTO `fruitname2` VALUES (308, 'Wildgap', 0);
INSERT INTO `fruitname2` VALUES (309, 'Winepalm', 2);
INSERT INTO `fruitname2` VALUES (324, 'Arrowrot', 1);
INSERT INTO `fruitname2` VALUES (325, 'Arrowken', 1);
INSERT INTO `fruitname2` VALUES (326, 'Arrowert', 0);
INSERT INTO `fruitname2` VALUES (327, 'Arrowart', 2);
INSERT INTO `fruitname2` VALUES (328, 'Farkleberry', 1);
INSERT INTO `fruitname2` VALUES (329, 'Feijoa', 1);
INSERT INTO `fruitname2` VALUES (330, 'Fig', 0);
INSERT INTO `fruitname2` VALUES (331, 'Filbert', 0);
INSERT INTO `fruitname2` VALUES (332, 'Floridamia', 1);
INSERT INTO `fruitname2` VALUES (333, 'Floridam', 0);
INSERT INTO `fruitname2` VALUES (334, 'Floridan', 1);
INSERT INTO `fruitname2` VALUES (335, 'Galangale', 0);
INSERT INTO `fruitname2` VALUES (336, 'Galanga', 0);
INSERT INTO `fruitname2` VALUES (337, 'Galanger', 0);
INSERT INTO `fruitname2` VALUES (338, 'Galumpi', 0);
INSERT INTO `fruitname2` VALUES (339, 'Gamboge', 0);
INSERT INTO `fruitname2` VALUES (340, 'Gandaria', 0);
INSERT INTO `fruitname2` VALUES (341, 'Genip', 0);
INSERT INTO `fruitname2` VALUES (342, 'Genipap', 1);
INSERT INTO `fruitname2` VALUES (343, 'Genipe', 0);
INSERT INTO `fruitname2` VALUES (344, 'Ginger', 0);
INSERT INTO `fruitname2` VALUES (345, 'Ginkgo', 0);
INSERT INTO `fruitname2` VALUES (346, 'Ginseng', 0);
INSERT INTO `fruitname2` VALUES (347, 'Goatnut', 0);
INSERT INTO `fruitname2` VALUES (348, 'Gold', 1);
INSERT INTO `fruitname2` VALUES (349, 'Golden', 0);
INSERT INTO `fruitname2` VALUES (350, 'Golder', 0);
INSERT INTO `fruitname2` VALUES (351, 'Goumi', 1);
INSERT INTO `fruitname2` VALUES (352, 'Goumill', 0);
INSERT INTO `fruitname2` VALUES (353, 'Goumer', 0);
INSERT INTO `fruitname2` VALUES (354, 'Granada', 0);
INSERT INTO `fruitname2` VALUES (355, 'Granad', 1);
INSERT INTO `fruitname2` VALUES (356, 'Granar', 0);
INSERT INTO `fruitname2` VALUES (357, 'Granadilla', 0);
INSERT INTO `fruitname2` VALUES (358, 'Granadillo', 1);
INSERT INTO `fruitname2` VALUES (359, 'Granadia', 1);
INSERT INTO `fruitname2` VALUES (360, 'Granadiler', 1);
INSERT INTO `fruitname2` VALUES (361, 'Granadera', 1);
INSERT INTO `fruitname2` VALUES (362, 'Granadill', 0);
INSERT INTO `fruitname2` VALUES (363, 'Grapefruit', 0);
INSERT INTO `fruitname2` VALUES (364, 'Grauda', 0);
INSERT INTO `fruitname2` VALUES (365, 'Grosella', 0);
INSERT INTO `fruitname2` VALUES (366, 'Groseller', 1);
INSERT INTO `fruitname2` VALUES (367, 'Grosell', 0);
INSERT INTO `fruitname2` VALUES (368, 'Grose', 0);
INSERT INTO `fruitname2` VALUES (369, 'Grumichama', 0);
INSERT INTO `fruitname2` VALUES (370, 'Grumixameira', 0);
INSERT INTO `fruitname2` VALUES (371, 'Guabiroba', 2);
INSERT INTO `fruitname2` VALUES (372, 'Guabiro', 0);
INSERT INTO `fruitname2` VALUES (373, 'Guajilote', 0);
INSERT INTO `fruitname2` VALUES (374, 'Guama', 1);
INSERT INTO `fruitname2` VALUES (375, 'Guamo', 2);
INSERT INTO `fruitname2` VALUES (376, 'Guanaba', 1);
INSERT INTO `fruitname2` VALUES (377, 'Guanabana', 0);
INSERT INTO `fruitname2` VALUES (378, 'Guanaber', 0);
INSERT INTO `fruitname2` VALUES (379, 'Guanabat', 1);
INSERT INTO `fruitname2` VALUES (380, 'Guanabell', 0);
INSERT INTO `fruitname2` VALUES (381, 'Guayo', 0);
INSERT INTO `fruitname2` VALUES (382, 'Guiana ', 1);
INSERT INTO `fruitname2` VALUES (383, 'Gumi', 0);
INSERT INTO `fruitname2` VALUES (384, 'Guyaba', 0);
INSERT INTO `fruitname2` VALUES (385, 'Habbel', 1);
INSERT INTO `fruitname2` VALUES (386, 'Hackberry', 11);
INSERT INTO `fruitname2` VALUES (387, 'Hardy', 9);
INSERT INTO `fruitname2` VALUES (388, 'Harendog', 0);
INSERT INTO `fruitname2` VALUES (389, 'Hawthorn', 0);
INSERT INTO `fruitname2` VALUES (390, 'Hazelnut', 0);
INSERT INTO `fruitname2` VALUES (391, 'Hedgerow', 0);
INSERT INTO `fruitname2` VALUES (392, 'Hedgerot', 0);
INSERT INTO `fruitname2` VALUES (393, 'Hedgerose', 0);
INSERT INTO `fruitname2` VALUES (394, 'Hedgeroot', 0);
INSERT INTO `fruitname2` VALUES (395, 'Hilama', 0);
INSERT INTO `fruitname2` VALUES (396, 'Hogplum', 1);
INSERT INTO `fruitname2` VALUES (397, 'Hoglum', 0);
INSERT INTO `fruitname2` VALUES (398, 'Hogum', 2);
INSERT INTO `fruitname2` VALUES (399, 'Hogmer', 0);
INSERT INTO `fruitname2` VALUES (400, 'Hogger', 0);
INSERT INTO `fruitname2` VALUES (401, 'Hondapara', 1);
INSERT INTO `fruitname2` VALUES (402, 'Honeycust', 0);
INSERT INTO `fruitname2` VALUES (403, 'Horango', 0);
INSERT INTO `fruitname2` VALUES (404, 'Horserad', 1);
INSERT INTO `fruitname2` VALUES (405, 'Horseradish', 0);
INSERT INTO `fruitname2` VALUES (406, 'Hottentot', 0);
INSERT INTO `fruitname2` VALUES (407, 'Hotten', 0);
INSERT INTO `fruitname2` VALUES (408, 'Husk ', 1);
INSERT INTO `fruitname2` VALUES (409, 'Ichang', 1);
INSERT INTO `fruitname2` VALUES (410, 'Ichan', 0);
INSERT INTO `fruitname2` VALUES (411, 'Ilama', 0);
INSERT INTO `fruitname2` VALUES (412, 'Ilang', 0);
INSERT INTO `fruitname2` VALUES (413, 'Imbe', 0);
INSERT INTO `fruitname2` VALUES (414, 'Imbu', 0);
INSERT INTO `fruitname2` VALUES (415, 'Jaboticaba', 1);
INSERT INTO `fruitname2` VALUES (416, 'Jaboty', 0);
INSERT INTO `fruitname2` VALUES (417, 'Jabotica', 1);
INSERT INTO `fruitname2` VALUES (418, 'Jaboticab', 1);
INSERT INTO `fruitname2` VALUES (419, 'Jabotiken', 0);
INSERT INTO `fruitname2` VALUES (420, 'Jackfruit', 0);
INSERT INTO `fruitname2` VALUES (421, 'Jakfruit', 0);
INSERT INTO `fruitname2` VALUES (422, 'Jamberry', 0);
INSERT INTO `fruitname2` VALUES (423, 'Jambert', 0);
INSERT INTO `fruitname2` VALUES (424, 'Jambell', 1);
INSERT INTO `fruitname2` VALUES (425, 'Jambolan', 0);
INSERT INTO `fruitname2` VALUES (426, 'Jamfruit', 2);
INSERT INTO `fruitname2` VALUES (427, 'Jujube', 1);
INSERT INTO `fruitname2` VALUES (428, 'Jujuba', 0);
INSERT INTO `fruitname2` VALUES (429, 'Lime', 0);
INSERT INTO `fruitname2` VALUES (430, 'Limon', 0);
INSERT INTO `fruitname2` VALUES (431, 'Longan', 0);
INSERT INTO `fruitname2` VALUES (432, 'Rhodod', 1);
INSERT INTO `fruitname2` VALUES (433, 'Snakework', 0);
INSERT INTO `fruitname2` VALUES (434, 'Sunflower', 0);
INSERT INTO `fruitname2` VALUES (435, 'Turnip', 2);
INSERT INTO `fruitname2` VALUES (436, 'Wampi', 0);

-- Table structure for table `incomingcall`
CREATE TABLE `incomingcall` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `caller` varchar(20) NOT NULL,
  `receiver` varchar(20) NOT NULL,
  `callDate` datetime NOT NULL,
  `errorCode` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=15188 ;

-- Table structure for table `incomingsms`
CREATE TABLE `incomingsms` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `sms` varchar(255) character set latin1 collate latin1_bin NOT NULL,
  `sendingtime` datetime NOT NULL,
  `sender` varchar(20) character set latin1 collate latin1_bin NOT NULL,
  `errorCode` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=502754 ;

-- Table structure for table `outgoingsms`
CREATE TABLE `outgoingsms` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `phone` varchar(20) NOT NULL,
  `sms` varchar(255) NOT NULL,
  `entryTime` datetime default NULL,
  `sentTime` datetime default NULL,
  `sent` tinyint(4) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1068 ;

-- Table structure for table `page`
CREATE TABLE `page` (
  `pageID` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(512) NOT NULL,
  `startTime` datetime NOT NULL,
  `endTime` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `author` varchar(20) NOT NULL,
  `phone` varchar(20) default NULL,
  `createTime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `invalidAllowed` tinyint(1) NOT NULL default '0',
  `smsRequired` tinyint(1) NOT NULL default '0',
  `teleVoteAllowed` smallint(1) NOT NULL default '1',
  `anonymousAllowed` tinyint(1) NOT NULL default '1',
  `showGraph` tinyint(1) NOT NULL default '1',
  `displayTop` tinyint(4) NOT NULL default '0',
  `surveyType` tinyint(4) NOT NULL default '1',
  `votesAllowed` tinyint(8) unsigned NOT NULL default '1',
  `subtractWrong` tinyint(1) NOT NULL default '0',
  `eventID` varchar(11) default NULL,
  PRIMARY KEY  (`pageID`),
  KEY `pageID` (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=743 ;

-- Table structure for table `presentation`
CREATE TABLE `presentation` (
  `surveyID` int(10) unsigned NOT NULL,
  `presentationID` tinyint(10) unsigned NOT NULL,
  `presentation` varchar(1000) NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `mark` tinyint(4) NOT NULL default '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `quizresultsms`
CREATE TABLE `quizresultsms` (
  `pageID` varchar(128) NOT NULL,
  `user` varchar(255) character set latin1 collate latin1_bin default NULL,
  `mobile` tinytext NOT NULL,
  `sender` varchar(255) character set latin1 collate latin1_bin NOT NULL,
  `content` varchar(512) character set latin1 collate latin1_bin NOT NULL,
  `senttime` timestamp NOT NULL default CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='stores quiz result SMS';

-- Table structure for table `survey`
CREATE TABLE `survey` (
  `pageID` int(11) NOT NULL,
  `surveyID` int(11) unsigned NOT NULL auto_increment,
  `question` varchar(4000) NOT NULL,
  `answer` tinyint(4) NOT NULL default '0',
  `points` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`surveyID`),
  KEY `pageID` (`pageID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=25758 ;

-- Table structure for table `surveychoice`
CREATE TABLE `surveychoice` (
  `surveyID` int(11) NOT NULL,
  `choiceID` tinyint(4) unsigned NOT NULL default '1',
  `choice` varchar(400) NOT NULL,
  `receiver` varchar(20) default NULL,
  `points` tinyint(4) NOT NULL default '0',
  `SMS` varchar(200) NOT NULL default 'none',
  `vote` int(11) NOT NULL default '0',
  KEY `surveyID` (`surveyID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `surveyrecord`
CREATE TABLE `surveyrecord` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `voterID` varchar(50) NOT NULL default 'unknown',
  `surveyID` int(11) NOT NULL,
  `presentationID` tinyint(4) NOT NULL default '0',
  `choiceID` tinyint(4) NOT NULL,
  `voteDate` datetime NOT NULL,
  `voteType` varchar(6) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `i_surveyrecord` (`surveyID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=10852 ;

-- Table structure for table `t_1`
CREATE TABLE `t_1` (
  `teleID` tinyint(4) unsigned NOT NULL auto_increment,
  `telenumber` varchar(50) NOT NULL,
  PRIMARY KEY  (`teleID`),
  KEY `telenumber` (`telenumber`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=201 ;
-- Dumping data for table `t_1`
INSERT INTO `t_1` VALUES (101, '7300');
INSERT INTO `t_1` VALUES (102, '7301');
INSERT INTO `t_1` VALUES (103, '7302');
INSERT INTO `t_1` VALUES (104, '7303');
INSERT INTO `t_1` VALUES (105, '7304');
INSERT INTO `t_1` VALUES (106, '7305');
INSERT INTO `t_1` VALUES (107, '7306');
INSERT INTO `t_1` VALUES (108, '7307');
INSERT INTO `t_1` VALUES (109, '7308');
INSERT INTO `t_1` VALUES (110, '7309');
INSERT INTO `t_1` VALUES (111, '7310');
INSERT INTO `t_1` VALUES (112, '7311');
INSERT INTO `t_1` VALUES (113, '7312');
INSERT INTO `t_1` VALUES (114, '7313');
INSERT INTO `t_1` VALUES (115, '7314');
INSERT INTO `t_1` VALUES (116, '7315');
INSERT INTO `t_1` VALUES (117, '7316');
INSERT INTO `t_1` VALUES (118, '7317');
INSERT INTO `t_1` VALUES (119, '7318');
INSERT INTO `t_1` VALUES (120, '7319');
INSERT INTO `t_1` VALUES (121, '7320');
INSERT INTO `t_1` VALUES (122, '7321');
INSERT INTO `t_1` VALUES (123, '7322');
INSERT INTO `t_1` VALUES (124, '7323');
INSERT INTO `t_1` VALUES (125, '7324');
INSERT INTO `t_1` VALUES (126, '7325');
INSERT INTO `t_1` VALUES (127, '7326');
INSERT INTO `t_1` VALUES (128, '7327');
INSERT INTO `t_1` VALUES (129, '7328');
INSERT INTO `t_1` VALUES (130, '7329');
INSERT INTO `t_1` VALUES (131, '7330');
INSERT INTO `t_1` VALUES (132, '7331');
INSERT INTO `t_1` VALUES (133, '7332');
INSERT INTO `t_1` VALUES (134, '7333');
INSERT INTO `t_1` VALUES (135, '7334');
INSERT INTO `t_1` VALUES (136, '7335');
INSERT INTO `t_1` VALUES (137, '7336');
INSERT INTO `t_1` VALUES (138, '7337');
INSERT INTO `t_1` VALUES (139, '7338');
INSERT INTO `t_1` VALUES (140, '7339');
INSERT INTO `t_1` VALUES (141, '7340');
INSERT INTO `t_1` VALUES (142, '7341');
INSERT INTO `t_1` VALUES (143, '7342');
INSERT INTO `t_1` VALUES (144, '7343');
INSERT INTO `t_1` VALUES (145, '7344');
INSERT INTO `t_1` VALUES (146, '7345');
INSERT INTO `t_1` VALUES (147, '7346');
INSERT INTO `t_1` VALUES (148, '7347');
INSERT INTO `t_1` VALUES (149, '7348');
INSERT INTO `t_1` VALUES (150, '7349');
INSERT INTO `t_1` VALUES (151, '7350');
INSERT INTO `t_1` VALUES (152, '7351');
INSERT INTO `t_1` VALUES (153, '7352');
INSERT INTO `t_1` VALUES (154, '7353');
INSERT INTO `t_1` VALUES (155, '7354');
INSERT INTO `t_1` VALUES (156, '7355');
INSERT INTO `t_1` VALUES (157, '7356');
INSERT INTO `t_1` VALUES (158, '7357');
INSERT INTO `t_1` VALUES (159, '7358');
INSERT INTO `t_1` VALUES (160, '7359');
INSERT INTO `t_1` VALUES (161, '7360');
INSERT INTO `t_1` VALUES (162, '7361');
INSERT INTO `t_1` VALUES (163, '7362');
INSERT INTO `t_1` VALUES (164, '7363');
INSERT INTO `t_1` VALUES (165, '7364');
INSERT INTO `t_1` VALUES (166, '7365');
INSERT INTO `t_1` VALUES (167, '7366');
INSERT INTO `t_1` VALUES (168, '7367');
INSERT INTO `t_1` VALUES (169, '7368');
INSERT INTO `t_1` VALUES (170, '7369');
INSERT INTO `t_1` VALUES (171, '7370');
INSERT INTO `t_1` VALUES (172, '7371');
INSERT INTO `t_1` VALUES (173, '7372');
INSERT INTO `t_1` VALUES (174, '7373');
INSERT INTO `t_1` VALUES (175, '7374');
INSERT INTO `t_1` VALUES (176, '7375');
INSERT INTO `t_1` VALUES (177, '7376');
INSERT INTO `t_1` VALUES (178, '7377');
INSERT INTO `t_1` VALUES (179, '7378');
INSERT INTO `t_1` VALUES (180, '7379');
INSERT INTO `t_1` VALUES (181, '7380');
INSERT INTO `t_1` VALUES (182, '7381');
INSERT INTO `t_1` VALUES (183, '7382');
INSERT INTO `t_1` VALUES (184, '7383');
INSERT INTO `t_1` VALUES (185, '7384');
INSERT INTO `t_1` VALUES (186, '7385');
INSERT INTO `t_1` VALUES (187, '7386');
INSERT INTO `t_1` VALUES (188, '7387');
INSERT INTO `t_1` VALUES (189, '7388');
INSERT INTO `t_1` VALUES (190, '7389');
INSERT INTO `t_1` VALUES (191, '7390');
INSERT INTO `t_1` VALUES (192, '7391');
INSERT INTO `t_1` VALUES (193, '7392');
INSERT INTO `t_1` VALUES (194, '7393');
INSERT INTO `t_1` VALUES (195, '7394');
INSERT INTO `t_1` VALUES (196, '7395');
INSERT INTO `t_1` VALUES (197, '7396');
INSERT INTO `t_1` VALUES (198, '7397');
INSERT INTO `t_1` VALUES (199, '7398');
INSERT INTO `t_1` VALUES (200, '7399');
INSERT INTO `t_1` VALUES (1, '81161800');
INSERT INTO `t_1` VALUES (2, '81161801');
INSERT INTO `t_1` VALUES (3, '81161802');
INSERT INTO `t_1` VALUES (4, '81161803');
INSERT INTO `t_1` VALUES (5, '81161804');
INSERT INTO `t_1` VALUES (6, '81161805');
INSERT INTO `t_1` VALUES (7, '81161806');
INSERT INTO `t_1` VALUES (8, '81161807');
INSERT INTO `t_1` VALUES (9, '81161808');
INSERT INTO `t_1` VALUES (10, '81161809');
INSERT INTO `t_1` VALUES (11, '81161810');
INSERT INTO `t_1` VALUES (12, '81161811');
INSERT INTO `t_1` VALUES (13, '81161812');
INSERT INTO `t_1` VALUES (14, '81161813');
INSERT INTO `t_1` VALUES (15, '81161814');
INSERT INTO `t_1` VALUES (16, '81161815');
INSERT INTO `t_1` VALUES (17, '81161816');
INSERT INTO `t_1` VALUES (18, '81161817');
INSERT INTO `t_1` VALUES (19, '81161818');
INSERT INTO `t_1` VALUES (20, '81161819');
INSERT INTO `t_1` VALUES (21, '81161820');
INSERT INTO `t_1` VALUES (22, '81161821');
INSERT INTO `t_1` VALUES (23, '81161822');
INSERT INTO `t_1` VALUES (24, '81161823');
INSERT INTO `t_1` VALUES (25, '81161824');
INSERT INTO `t_1` VALUES (26, '81161825');
INSERT INTO `t_1` VALUES (27, '81161826');
INSERT INTO `t_1` VALUES (28, '81161827');
INSERT INTO `t_1` VALUES (29, '81161828');
INSERT INTO `t_1` VALUES (30, '81161829');
INSERT INTO `t_1` VALUES (31, '81161830');
INSERT INTO `t_1` VALUES (32, '81161831');
INSERT INTO `t_1` VALUES (33, '81161832');
INSERT INTO `t_1` VALUES (34, '81161833');
INSERT INTO `t_1` VALUES (35, '81161834');
INSERT INTO `t_1` VALUES (36, '81161835');
INSERT INTO `t_1` VALUES (37, '81161836');
INSERT INTO `t_1` VALUES (38, '81161837');
INSERT INTO `t_1` VALUES (39, '81161838');
INSERT INTO `t_1` VALUES (40, '81161839');
INSERT INTO `t_1` VALUES (41, '81161840');
INSERT INTO `t_1` VALUES (42, '81161841');
INSERT INTO `t_1` VALUES (43, '81161842');
INSERT INTO `t_1` VALUES (44, '81161843');
INSERT INTO `t_1` VALUES (45, '81161844');
INSERT INTO `t_1` VALUES (46, '81161845');
INSERT INTO `t_1` VALUES (47, '81161846');
INSERT INTO `t_1` VALUES (48, '81161847');
INSERT INTO `t_1` VALUES (49, '81161848');
INSERT INTO `t_1` VALUES (50, '81161849');
INSERT INTO `t_1` VALUES (51, '81161850');
INSERT INTO `t_1` VALUES (52, '81161851');
INSERT INTO `t_1` VALUES (53, '81161852');
INSERT INTO `t_1` VALUES (54, '81161853');
INSERT INTO `t_1` VALUES (55, '81161854');
INSERT INTO `t_1` VALUES (56, '81161855');
INSERT INTO `t_1` VALUES (57, '81161856');
INSERT INTO `t_1` VALUES (58, '81161857');
INSERT INTO `t_1` VALUES (59, '81161858');
INSERT INTO `t_1` VALUES (60, '81161859');
INSERT INTO `t_1` VALUES (61, '81161860');
INSERT INTO `t_1` VALUES (62, '81161861');
INSERT INTO `t_1` VALUES (63, '81161862');
INSERT INTO `t_1` VALUES (64, '81161863');
INSERT INTO `t_1` VALUES (65, '81161864');
INSERT INTO `t_1` VALUES (66, '81161865');
INSERT INTO `t_1` VALUES (67, '81161866');
INSERT INTO `t_1` VALUES (68, '81161867');
INSERT INTO `t_1` VALUES (69, '81161868');
INSERT INTO `t_1` VALUES (70, '81161869');
INSERT INTO `t_1` VALUES (71, '81161870');
INSERT INTO `t_1` VALUES (72, '81161871');
INSERT INTO `t_1` VALUES (73, '81161872');
INSERT INTO `t_1` VALUES (74, '81161873');
INSERT INTO `t_1` VALUES (75, '81161874');
INSERT INTO `t_1` VALUES (76, '81161875');
INSERT INTO `t_1` VALUES (77, '81161876');
INSERT INTO `t_1` VALUES (78, '81161877');
INSERT INTO `t_1` VALUES (79, '81161878');
INSERT INTO `t_1` VALUES (80, '81161879');
INSERT INTO `t_1` VALUES (81, '81161880');
INSERT INTO `t_1` VALUES (82, '81161881');
INSERT INTO `t_1` VALUES (83, '81161882');
INSERT INTO `t_1` VALUES (84, '81161883');
INSERT INTO `t_1` VALUES (85, '81161884');
INSERT INTO `t_1` VALUES (86, '81161885');
INSERT INTO `t_1` VALUES (87, '81161886');
INSERT INTO `t_1` VALUES (88, '81161887');
INSERT INTO `t_1` VALUES (89, '81161888');
INSERT INTO `t_1` VALUES (90, '81161889');
INSERT INTO `t_1` VALUES (91, '81161890');
INSERT INTO `t_1` VALUES (92, '81161891');
INSERT INTO `t_1` VALUES (93, '81161892');
INSERT INTO `t_1` VALUES (94, '81161893');
INSERT INTO `t_1` VALUES (95, '81161894');
INSERT INTO `t_1` VALUES (96, '81161895');
INSERT INTO `t_1` VALUES (97, '81161896');
INSERT INTO `t_1` VALUES (98, '81161897');
INSERT INTO `t_1` VALUES (99, '81161898');
INSERT INTO `t_1` VALUES (100, '81161899');

-- Table structure for table `telenumber`
CREATE TABLE `telenumber` (
  `teleID` tinyint(4) unsigned NOT NULL default '0',
  `telenumber` varchar(50) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- Dumping data for table `telenumber`
INSERT INTO `telenumber` VALUES (1, '81161800');
INSERT INTO `telenumber` VALUES (2, '81161801');
INSERT INTO `telenumber` VALUES (3, '81161802');
INSERT INTO `telenumber` VALUES (4, '81161803');
INSERT INTO `telenumber` VALUES (5, '81161804');
INSERT INTO `telenumber` VALUES (6, '81161805');
INSERT INTO `telenumber` VALUES (7, '81161806');
INSERT INTO `telenumber` VALUES (8, '81161807');
INSERT INTO `telenumber` VALUES (9, '81161808');
INSERT INTO `telenumber` VALUES (10, '81161809');
INSERT INTO `telenumber` VALUES (11, '81161810');
INSERT INTO `telenumber` VALUES (12, '81161811');
INSERT INTO `telenumber` VALUES (13, '81161812');
INSERT INTO `telenumber` VALUES (14, '81161813');
INSERT INTO `telenumber` VALUES (15, '81161814');
INSERT INTO `telenumber` VALUES (16, '81161815');
INSERT INTO `telenumber` VALUES (17, '81161816');
INSERT INTO `telenumber` VALUES (18, '81161817');
INSERT INTO `telenumber` VALUES (19, '81161818');
INSERT INTO `telenumber` VALUES (20, '81161819');
INSERT INTO `telenumber` VALUES (21, '81161820');
INSERT INTO `telenumber` VALUES (22, '81161821');
INSERT INTO `telenumber` VALUES (23, '81161822');
INSERT INTO `telenumber` VALUES (24, '81161823');
INSERT INTO `telenumber` VALUES (25, '81161824');
INSERT INTO `telenumber` VALUES (26, '81161825');
INSERT INTO `telenumber` VALUES (27, '81161826');
INSERT INTO `telenumber` VALUES (28, '81161827');
INSERT INTO `telenumber` VALUES (29, '81161828');
INSERT INTO `telenumber` VALUES (30, '81161829');
INSERT INTO `telenumber` VALUES (31, '81161830');
INSERT INTO `telenumber` VALUES (32, '81161831');
INSERT INTO `telenumber` VALUES (33, '81161832');
INSERT INTO `telenumber` VALUES (34, '81161833');
INSERT INTO `telenumber` VALUES (35, '81161834');
INSERT INTO `telenumber` VALUES (36, '81161835');
INSERT INTO `telenumber` VALUES (37, '81161836');
INSERT INTO `telenumber` VALUES (38, '81161837');
INSERT INTO `telenumber` VALUES (39, '81161838');
INSERT INTO `telenumber` VALUES (40, '81161839');
INSERT INTO `telenumber` VALUES (41, '81161840');
INSERT INTO `telenumber` VALUES (42, '81161841');
INSERT INTO `telenumber` VALUES (43, '81161842');
INSERT INTO `telenumber` VALUES (44, '81161843');
INSERT INTO `telenumber` VALUES (45, '81161844');
INSERT INTO `telenumber` VALUES (46, '81161845');
INSERT INTO `telenumber` VALUES (47, '81161846');
INSERT INTO `telenumber` VALUES (48, '81161847');
INSERT INTO `telenumber` VALUES (49, '81161848');
INSERT INTO `telenumber` VALUES (50, '81161849');
INSERT INTO `telenumber` VALUES (51, '81161850');
INSERT INTO `telenumber` VALUES (52, '81161851');
INSERT INTO `telenumber` VALUES (53, '81161852');
INSERT INTO `telenumber` VALUES (54, '81161853');
INSERT INTO `telenumber` VALUES (55, '81161854');
INSERT INTO `telenumber` VALUES (56, '81161855');
INSERT INTO `telenumber` VALUES (57, '81161856');
INSERT INTO `telenumber` VALUES (58, '81161857');
INSERT INTO `telenumber` VALUES (59, '81161858');
INSERT INTO `telenumber` VALUES (60, '81161859');
INSERT INTO `telenumber` VALUES (61, '81161860');
INSERT INTO `telenumber` VALUES (62, '81161861');
INSERT INTO `telenumber` VALUES (63, '81161862');
INSERT INTO `telenumber` VALUES (64, '81161863');
INSERT INTO `telenumber` VALUES (65, '81161864');
INSERT INTO `telenumber` VALUES (66, '81161865');
INSERT INTO `telenumber` VALUES (67, '81161866');
INSERT INTO `telenumber` VALUES (68, '81161867');
INSERT INTO `telenumber` VALUES (69, '81161868');
INSERT INTO `telenumber` VALUES (70, '81161869');
INSERT INTO `telenumber` VALUES (71, '81161870');
INSERT INTO `telenumber` VALUES (72, '81161871');
INSERT INTO `telenumber` VALUES (73, '81161872');
INSERT INTO `telenumber` VALUES (74, '81161873');
INSERT INTO `telenumber` VALUES (75, '81161874');
INSERT INTO `telenumber` VALUES (76, '81161875');
INSERT INTO `telenumber` VALUES (77, '81161876');
INSERT INTO `telenumber` VALUES (78, '81161877');
INSERT INTO `telenumber` VALUES (79, '81161878');
INSERT INTO `telenumber` VALUES (80, '81161879');
INSERT INTO `telenumber` VALUES (81, '81161880');
INSERT INTO `telenumber` VALUES (82, '81161881');
INSERT INTO `telenumber` VALUES (83, '81161882');
INSERT INTO `telenumber` VALUES (84, '81161883');
INSERT INTO `telenumber` VALUES (85, '81161884');
INSERT INTO `telenumber` VALUES (86, '81161885');
INSERT INTO `telenumber` VALUES (87, '81161886');
INSERT INTO `telenumber` VALUES (88, '81161887');
INSERT INTO `telenumber` VALUES (89, '81161888');
INSERT INTO `telenumber` VALUES (90, '81161889');
INSERT INTO `telenumber` VALUES (91, '81161890');
INSERT INTO `telenumber` VALUES (92, '81161891');
INSERT INTO `telenumber` VALUES (93, '81161892');
INSERT INTO `telenumber` VALUES (94, '81161893');
INSERT INTO `telenumber` VALUES (95, '81161894');
INSERT INTO `telenumber` VALUES (96, '81161895');
INSERT INTO `telenumber` VALUES (97, '81161896');
INSERT INTO `telenumber` VALUES (98, '81161897');
INSERT INTO `telenumber` VALUES (99, '81161898');

-- Table structure for table `test`
CREATE TABLE `test` (
  `pageID` int(11) unsigned NOT NULL default '0',
  `title` varchar(512) NOT NULL default '',
  `subtractWrong` tinyint(1) NOT NULL default '0',
  `author` varchar(20) NOT NULL default '',
  `surveyID` int(11) unsigned NOT NULL default '0',
  `question` varchar(4000) NOT NULL default '',
  `answer` tinyint(4) NOT NULL default '0',
  `points` tinyint(4) NOT NULL default '0',
  `choice` varchar(400) NOT NULL default ''
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Table structure for table `textresponsesms`
CREATE TABLE `textresponsesms` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `sms` varchar(255) character set latin1 collate latin1_bin NOT NULL,
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `sender` varchar(20) character set latin1 collate latin1_bin NOT NULL,
  `surveyid` int(11) NOT NULL default '0',
  `username` varchar(255) character set latin1 collate latin1_bin default NULL,
  `realname` varchar(255) character set latin1 collate latin1_bin default NULL,
  `acceptedTime` datetime default '0000-00-00 00:00:00',
  `accepted` tinyint(3) unsigned NOT NULL default '0',
  UNIQUE KEY `id` (`id`),
  KEY `surveyid` (`surveyid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=399434 ;

-- Table structure for table `typecode`
CREATE TABLE `typecode` (
  `typecode` tinyint(4) NOT NULL,
  `typeName` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- Dumping data for table `typecode`
INSERT INTO `typecode` VALUES (1, 'Simple Survey');
INSERT INTO `typecode` VALUES (2, 'Quiz');
INSERT INTO `typecode` VALUES (3, 'Rank Expositions');
INSERT INTO `typecode` VALUES (4, 'Questionnaire');
INSERT INTO `typecode` VALUES (5, 'Text Response');

-- Table structure for table `view_current_survey`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_current_survey` AS select sql_no_cache  `page`.`pageID` AS `pageID`, `page`.`title` AS `title`, `page`.`startTime` AS `startTime`, `page`.`endTime` AS `endTime`, `page`.`duration` AS `duration`, `page`.`author` AS `author`, `page`.`phone` AS `phone`, `page`.`createTime` AS `createTime`, `page`.`invalidAllowed` AS `invalidAllowed`, `page`.`smsRequired` AS `smsRequired`, `page`.`teleVoteAllowed` AS `teleVoteAllowed`, `page`.`anonymousAllowed` AS `anonymousAllowed`, `page`.`surveyType` AS `surveyType`, `page`.`displayTop` AS `displayTop`, `page`.`votesAllowed` AS `votesAllowed`, `survey`.`surveyID` AS `surveyID`, `survey`.`question` AS `question`, `survey`.`answer` AS `answer`, `survey`.`points` AS `points`, `surveychoice`.`choiceID` AS `choiceID`, `surveychoice`.`choice` AS `choice`, `surveychoice`.`receiver` AS `receiver`, `surveychoice`.`SMS` AS `sms`, `surveychoice`.`vote` AS `vote`, `presentation`.`presentationID` AS `presentationID`, `presentation`.`active` AS `active` from ((( `page` join  `survey` on(( `page`.`pageID` =  `survey`.`pageID`))) left join  `surveychoice` on(( `survey`.`surveyID` =  `surveychoice`.`surveyID`))) left join  `presentation` on((( `survey`.`surveyID` =  `presentation`.`surveyID`) and ( `presentation`.`active` = 1)))) where (( `page`.`startTime` < now()) and ( `page`.`endTime` > now()));

-- Table structure for table `view_cs2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_cs2` AS select  `surveychoice`.`surveyID` AS `surveyID`, `surveychoice`.`choiceID` AS `choiceID`, `surveychoice`.`choice` AS `choice`, `surveychoice`.`receiver` AS `receiver`, `surveychoice`.`points` AS `points`, `surveychoice`.`SMS` AS `SMS`, `surveychoice`.`vote` AS `vote` from  `surveychoice` where ( `surveychoice`.`surveyID` = _latin1'24548');

-- Table structure for table `view_sr1`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_sr1` AS select  `surveyrecord`.`surveyID` AS `surveyID`, `surveyrecord`.`voterID` AS `voterID`, `surveyrecord`.`choiceID` AS `choiceID` from  `surveyrecord` where ( `surveyrecord`.`surveyID` = _latin1'24779');

-- Table structure for table `view_sr2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_sr2` AS select  `surveyrecord`.`surveyID` AS `surveyID`, `surveyrecord`.`voterID` AS `voterID`, `surveyrecord`.`choiceID` AS `choiceID` from  `surveyrecord` where ( `surveyrecord`.`surveyID` = _latin1'24548');

-- Table structure for table `view_join`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_join` AS select `view_sr1`.`voterID` AS `voterID`,`view_sr1`.`choiceID` AS `choiceID`,`view_sr2`.`voterID` AS `votB`,`view_sr2`.`choiceID` AS `choiB` from ( `view_sr2` left join  `view_sr1` on((`view_sr1`.`voterID` = `view_sr2`.`voterID`)));

-- Table structure for table `view_join2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_join2` AS select `view_join`.`voterID` AS `voterID`,`view_join`.`choiceID` AS `choiceID`,`view_join`.`votB` AS `votB`,`view_cs2`.`choiceID` AS `choiB` from ( `view_cs2` left join  `view_join` on((`view_join`.`choiB` = `view_cs2`.`choiceID`)));

-- Table structure for table `view_available_telephone`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_available_telephone` AS select sql_no_cache  `telenumber`.`telenumber` AS `telenumber` from  `telenumber` where not(`telenumber` in (select sql_no_cache `view_current_survey`.`receiver` AS `receiver` from  `view_current_survey` where (`view_current_survey`.`receiver` is not null)));

-- Table structure for table `view_avphone2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_avphone2` AS select sql_no_cache  `telenumber`.`telenumber` AS `telenumber` from  `telenumber` where not(`telenumber` in (select sql_no_cache `view_current_survey`.`receiver` AS `receiver` from  `view_current_survey` where (`view_current_survey`.`receiver` is not null)));

-- Table structure for table `view_avphone8`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_avphone8` AS select sql_no_cache `view_available_telephone`.`telenumber` AS `telenumber` from  `view_available_telephone`;

-- Table structure for table `view_choi`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_choi` AS select `view_join2`.`votB` AS `votB`,`view_join2`.`choiB` AS `choiB` from  `view_join2` where isnull(`view_join2`.`choiceID`);

-- Table structure for table `view_usermobile`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_usermobile` AS select `wikidb`.`user`.`user_name` AS `user_name`,`wikidb`.`user`.`user_mobilephone` AS `user_mobilephone`,`wikidb`.`user`.`user_real_name` AS `user_real_name` from `wikidb`.`user`;

-- Table structure for table `view_usermobile_only`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_usermobile_only` AS select `view_usermobile`.`user_name` AS `user_name`,`view_usermobile`.`user_mobilephone` AS `user_mobilephone`,`view_usermobile`.`user_real_name` AS `user_real_name` from  `view_usermobile` where (`view_usermobile`.`user_mobilephone` <> _latin1'');

-- Table structure for table `view_freetext`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_freetext` AS select  `incomingsms`.`ID` AS `ID`, `incomingsms`.`sms` AS `sms`, `incomingsms`.`sendingtime` AS `sendingtime`, `incomingsms`.`sender` AS `sender`, `incomingsms`.`errorCode` AS `surveyid`,`view_usermobile_only`.`user_name` AS `user_name`,`view_usermobile_only`.`user_real_name` AS `user_real_name` from ( `incomingsms` left join  `view_usermobile_only` on(( `incomingsms`.`sender` = `view_usermobile_only`.`user_mobilephone`))) where not(`errorcode` in (select  `errorcode`.`errorCode` AS `errorcode` from  `errorcode`));

-- Table structure for table `view_liatchoice`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatchoice` AS select  `surveychoice`.`surveyID` AS `surveyID`, `surveychoice`.`choiceID` AS `choiceID`, `surveychoice`.`choice` AS `choice`, `surveychoice`.`receiver` AS `receiver`, `surveychoice`.`points` AS `points`, `surveychoice`.`SMS` AS `SMS`, `surveychoice`.`vote` AS `vote` from  `surveychoice` where (( `surveychoice`.`surveyID` = _utf8'24734') or ( `surveychoice`.`surveyID` = _utf8'24735')) order by  `surveychoice`.`receiver` desc;

-- Table structure for table `view_liatvoter`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatvoter` AS select  `surveyrecord`.`ID` AS `ID`, `surveyrecord`.`voterID` AS `voterID`, `surveyrecord`.`surveyID` AS `surveyID`, `surveyrecord`.`presentationID` AS `presentationID`, `surveyrecord`.`choiceID` AS `choiceID`, `surveyrecord`.`voteDate` AS `voteDate`, `surveyrecord`.`voteType` AS `voteType` from  `surveyrecord` where (( `surveyrecord`.`surveyID` = 24734) and ( `surveyrecord`.`choiceID` = _utf8'24'));

-- Table structure for table `view_liatvoter2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatvoter2` AS select  `surveyrecord`.`ID` AS `ID`, `surveyrecord`.`voterID` AS `voterID`, `surveyrecord`.`surveyID` AS `surveyID`, `surveyrecord`.`presentationID` AS `presentationID`, `surveyrecord`.`choiceID` AS `choiceID`, `surveyrecord`.`voteDate` AS `voteDate`, `surveyrecord`.`voteType` AS `voteType` from  `surveyrecord` where (( `surveyrecord`.`surveyID` = 24734) and ( `surveyrecord`.`choiceID` = _utf8'2'));

-- Table structure for table `view_liatvoter3`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatvoter3` AS select  `surveyrecord`.`ID` AS `ID`, `surveyrecord`.`voterID` AS `voterID`, `surveyrecord`.`surveyID` AS `surveyID`, `surveyrecord`.`presentationID` AS `presentationID`, `surveyrecord`.`choiceID` AS `choiceID`, `surveyrecord`.`voteDate` AS `voteDate`, `surveyrecord`.`voteType` AS `voteType` from  `surveyrecord` where (( `surveyrecord`.`surveyID` = 24734) and ( `surveyrecord`.`choiceID` = _utf8'26'));

-- Table structure for table `view_liatincom`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatincom` AS select  `incomingcall`.`caller` AS `caller`, `incomingcall`.`receiver` AS `receiver`, `incomingcall`.`callDate` AS `callDate`,`view_liatvoter`.`voterID` AS `voterID`,`view_liatvoter`.`choiceID` AS `choiceID`,`view_liatvoter`.`voteDate` AS `voteDate` from ( `view_liatvoter` join  `incomingcall` on((`view_liatvoter`.`voterID` =  `incomingcall`.`caller`))) where ( `incomingcall`.`callDate` > _utf8'2006-10-31 07:00:00');

-- Table structure for table `view_liatincom2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatincom2` AS select  `incomingcall`.`caller` AS `caller`, `incomingcall`.`receiver` AS `receiver`, `incomingcall`.`callDate` AS `callDate`,`view_liatvoter2`.`voterID` AS `voterID`,`view_liatvoter2`.`voteDate` AS `voteDate` from ( `view_liatvoter2` join  `incomingcall` on(( `incomingcall`.`caller` = `view_liatvoter2`.`voterID`))) where ( `incomingcall`.`callDate` > _utf8'2006-10-31 07:00:00');

-- Table structure for table `view_liatincom3`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_liatincom3` AS select  `incomingcall`.`caller` AS `caller`, `incomingcall`.`receiver` AS `receiver`, `incomingcall`.`callDate` AS `callDate`,`view_liatvoter3`.`voterID` AS `voterID`,`view_liatvoter3`.`voteDate` AS `voteDate` from ( `view_liatvoter3` join  `incomingcall` on(( `incomingcall`.`caller` = `view_liatvoter3`.`voterID`))) where ( `incomingcall`.`callDate` > _utf8'2006-10-31 07:00:00');

-- Table structure for table `view_perpage`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_perpage` AS select  `survey`.`pageID` AS `pageID`, `survey`.`surveyID` AS `surveyID`, `survey`.`question` AS `question`, `survey`.`answer` AS `answer`, `survey`.`points` AS `points` from  `survey` where ( `survey`.`pageID` = _latin1'573');

-- Table structure for table `view_multsurvey`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_multsurvey` AS select  `surveychoice`.`choice` AS `choice`, `surveychoice`.`receiver` AS `receiver` from ( `surveychoice` join  `view_perpage` on((`view_perpage`.`surveyID` =  `surveychoice`.`surveyID`)));

-- Table structure for table `view_persurvey`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_persurvey` AS select  `surveychoice`.`surveyID` AS `surveyID`, `surveychoice`.`choiceID` AS `choiceID`, `surveychoice`.`choice` AS `choice`, `surveychoice`.`receiver` AS `receiver`, `surveychoice`.`points` AS `points`, `surveychoice`.`SMS` AS `SMS`, `surveychoice`.`vote` AS `vote` from  `surveychoice` where ( `surveychoice`.`surveyID` = _latin1'24734');

-- Table structure for table `view_presentation_survey`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_presentation_survey` AS select  `page`.`pageID` AS `pageID`, `survey`.`surveyID` AS `surveyID`, `survey`.`question` AS `question`, `surveychoice`.`choiceID` AS `choiceID`, `surveychoice`.`choice` AS `choice`, `surveychoice`.`points` AS `points`, `presentation`.`presentationID` AS `presentationID`, `presentation`.`presentation` AS `presentation` from ((( `page` join  `survey`) join  `surveychoice`) join  `presentation`) where (( `page`.`surveyType` = 3) and ( `page`.`pageID` =  `survey`.`pageID`) and ( `survey`.`surveyID` =  `surveychoice`.`surveyID`) and ( `presentation`.`surveyID` =  `survey`.`surveyID`));

-- Table structure for table `view_presentation_votes`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_presentation_votes` AS select  `surveyrecord`.`surveyID` AS `surveyID`, `surveyrecord`.`presentationID` AS `presentationID`, `surveyrecord`.`choiceID` AS `choiceID`,count( `surveyrecord`.`choiceID`) AS `votes` from  `surveyrecord` where ( `surveyrecord`.`presentationID` <> 0) group by  `surveyrecord`.`surveyID`, `surveyrecord`.`presentationID`, `surveyrecord`.`choiceID`;

-- Table structure for table `view_presentation_survey_mark`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_presentation_survey_mark` AS select `view_presentation_survey`.`pageID` AS `pageID`,`view_presentation_survey`.`surveyID` AS `surveyID`,`view_presentation_survey`.`presentationID` AS `presentationid`,`view_presentation_survey`.`presentation` AS `presentation`,sum((`view_presentation_votes`.`votes` * `view_presentation_survey`.`points`)) AS `marks` from ( `view_presentation_survey` left join  `view_presentation_votes` on(((`view_presentation_survey`.`surveyID` = `view_presentation_votes`.`surveyID`) and (`view_presentation_survey`.`choiceID` = `view_presentation_votes`.`choiceID`) and (`view_presentation_survey`.`presentationID` = `view_presentation_votes`.`presentationID`)))) group by `view_presentation_survey`.`surveyID`,`view_presentation_survey`.`presentationID`;

-- Table structure for table `view_presentation_page_mark`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_presentation_page_mark` AS select `view_presentation_survey_mark`.`pageID` AS `pageID`,`view_presentation_survey_mark`.`presentationid` AS `presentationID`,`view_presentation_survey_mark`.`presentation` AS `presentation`,avg(`view_presentation_survey_mark`.`marks`) AS `marks`,std(`view_presentation_survey_mark`.`marks`) AS `std` from  `view_presentation_survey_mark` group by `view_presentation_survey_mark`.`pageID`,`view_presentation_survey_mark`.`presentationid`;

-- Table structure for table `view_quiz`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz` AS select  `page`.`pageID` AS `pageID`, `page`.`title` AS `title`, `page`.`subtractWrong` AS `subtractWrong`, `page`.`author` AS `author`, `survey`.`surveyID` AS `surveyID`, `survey`.`question` AS `question`, `survey`.`answer` AS `answer`, `survey`.`points` AS `points`, `surveychoice`.`choice` AS `choice` from (( `page` join  `survey`) join  `surveychoice`) where (( `page`.`surveyType` = 2) and ( `page`.`pageID` =  `survey`.`pageID`) and ( `survey`.`surveyID` =  `surveychoice`.`surveyID`) and ( `survey`.`answer` =  `surveychoice`.`choiceID`));

-- Table structure for table `view_quiz_result_detail`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz_result_detail` AS select `view_quiz`.`pageID` AS `pageid`,`view_quiz`.`title` AS `title`,`view_quiz`.`subtractWrong` AS `subtractWrong`, `surveyrecord`.`surveyID` AS `surveyID`,`view_quiz`.`question` AS `question`,`view_quiz`.`answer` AS `correctAnswerID`,`view_quiz`.`choice` AS `correctAnswer`, `surveyrecord`.`voterID` AS `voterid`,`view_quiz`.`points` AS `points`, `surveyrecord`.`choiceID` AS `chosenAnswerid`, `surveyrecord`.`voteDate` AS `votedate` from ( `view_quiz` left join  `surveyrecord` on((`view_quiz`.`surveyID` =  `surveyrecord`.`surveyID`))) order by `view_quiz`.`pageID`, `surveyrecord`.`voterID`, `surveyrecord`.`surveyID`;

-- Table structure for table `view_quiz_result_normal`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz_result_normal` AS select `view_quiz_result_detail`.`pageid` AS `pageid`,`view_quiz_result_detail`.`voterid` AS `voterid`,sum(`view_quiz_result_detail`.`points`) AS `marks` from  `view_quiz_result_detail` where (`view_quiz_result_detail`.`correctAnswerID` = `view_quiz_result_detail`.`chosenAnswerid`) group by `view_quiz_result_detail`.`pageid`,`view_quiz_result_detail`.`voterid`;

-- Table structure for table `view_quiz_result_subtract`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_quiz_result_subtract` AS select `view_quiz_result_detail`.`pageid` AS `pageid`,`view_quiz_result_detail`.`subtractWrong` AS `subtractWrong`,`view_quiz_result_detail`.`voterid` AS `voterid`,sum(((-(1) * `view_quiz_result_detail`.`subtractWrong`) * `view_quiz_result_detail`.`points`)) AS `marks` from `view_quiz_result_detail` where (`view_quiz_result_detail`.`correctAnswerID` <> `view_quiz_result_detail`.`chosenAnswerid`) group by `view_quiz_result_detail`.`pageid`,`view_quiz_result_detail`.`voterid`;

-- Table structure for table `view_quiz_result_union`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz_result_union` AS select `view_quiz_result_normal`.`pageid` AS `pageid`,`view_quiz_result_normal`.`voterid` AS `voterid`,`view_quiz_result_normal`.`marks` AS `marks` from  `view_quiz_result_normal` union select `view_quiz_result_subtract`.`pageid` AS `pageid`,`view_quiz_result_subtract`.`voterid` AS `voterid`,`view_quiz_result_subtract`.`marks` AS `marks` from  `view_quiz_result_subtract`;

-- Table structure for table `view_quiz_result_by_voterid`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz_result_by_voterid` AS select `view_quiz_result_union`.`pageid` AS `pageid`,`view_quiz_result_union`.`voterid` AS `voterID`,sum(`view_quiz_result_union`.`marks`) AS `marks` from  `view_quiz_result_union` group by `view_quiz_result_union`.`pageid`,`view_quiz_result_union`.`voterid`;

-- Table structure for table `view_quiz_result`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz_result` AS select `view_quiz_result_by_voterid`.`pageid` AS `pageid`,`view_quiz_result_by_voterid`.`voterID` AS `phone`,`view_quiz_result_by_voterid`.`marks` AS `marks`,`view_usermobile_only`.`user_name` AS `voterid`,`view_usermobile_only`.`user_real_name` AS `realname` from ( `view_quiz_result_by_voterid` left join  `view_usermobile_only` on((`view_quiz_result_by_voterid`.`voterID` = `view_usermobile_only`.`user_mobilephone`)));

-- Table structure for table `view_quiz_result_allwrong`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_quiz_result_allwrong` AS select `view_quiz_result_subtract`.`pageid` AS `pageid`,`view_quiz_result_subtract`.`voterid` AS `voterid`,(`view_quiz_result_subtract`.`marks` * `view_quiz_result_subtract`.`subtractWrong`) AS `marks` from  `view_quiz_result_subtract` where not(`view_quiz_result_subtract`.`voterid` in (select `view_quiz_result_normal`.`voterid` AS `voterid` from  `view_quiz_result_normal` where (`view_quiz_result_normal`.`pageid` = `view_quiz_result_subtract`.`pageid`)));

-- Table structure for table `view_recent_call2`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_recent_call2` AS select  `surveyrecord`.`voterID` AS `voterID`, `surveyrecord`.`voteDate` AS `voteDate`, `survey`.`question` AS `question`, `survey`.`pageID` AS `pageID`, `survey`.`surveyID` AS `surveyid` from ( `surveyrecord` join  `survey` on(( `surveyrecord`.`surveyID` =  `survey`.`surveyID`))) where ( `surveyrecord`.`voteType` = _latin1'call');

-- Table structure for table `view_recent_call`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_recent_call` AS select `view_recent_call2`.`voterID` AS `voterID`,`view_recent_call2`.`voteDate` AS `voteDate`, `page`.`title` AS `title` from ( `view_recent_call2` join  `page` on(( `page`.`pageID` = `view_recent_call2`.`pageID`)));

-- Table structure for table `view_recent_call3`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_recent_call3` AS select `view_recent_call2`.`voterID` AS `voterID`,`view_recent_call2`.`voteDate` AS `voteDate`,`view_recent_call2`.`question` AS `question`,`view_recent_call2`.`pageID` AS `pageID`,`view_recent_call2`.`surveyid` AS `surveyid`,`view_usermobile`.`user_name` AS `user_name`,`view_usermobile`.`user_mobilephone` AS `user_mobilephone`,`view_usermobile`.`user_real_name` AS `user_real_name` from ( `view_recent_call2` join  `view_usermobile` on((`view_recent_call2`.`voterID` = `view_usermobile`.`user_mobilephone`)));

-- Table structure for table `view_survey`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW  `view_survey` AS select  `page`.`pageID` AS `pageID`, `page`.`title` AS `title`, `page`.`startTime` AS `startTime`, `page`.`endTime` AS `endTime`, `page`.`duration` AS `duration`, `page`.`author` AS `author`, `page`.`phone` AS `phone`, `page`.`createTime` AS `createTime`, `page`.`invalidAllowed` AS `invalidAllowed`, `page`.`smsRequired` AS `smsRequired`, `page`.`teleVoteAllowed` AS `teleVoteAllowed`, `page`.`anonymousAllowed` AS `anonymousAllowed`, `page`.`showGraph` AS `showGraph`, `page`.`displayTop` AS `displayTop`, `page`.`surveyType` AS `surveyType`, `page`.`votesAllowed` AS `votesAllowed`, `page`.`subtractWrong` AS `subtractWrong`, `survey`.`surveyID` AS `surveyid` from ( `page` join  `survey`) where ( `page`.`pageID` =  `survey`.`pageID`);
