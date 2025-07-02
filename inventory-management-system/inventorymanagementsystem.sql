-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 16, 2025 at 10:26 AM
-- Server version: 5.7.36
-- PHP Version: 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
CREATE DATABASE inventorymanagementsystem;
USE inventorymanagementsystem;
--
-- Database: `inventorymanagementsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `audittrails`
--

DROP TABLE IF EXISTS `audittrails`;
CREATE TABLE IF NOT EXISTS `audittrails` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(250) NOT NULL,
  `action` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=724 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `audittrails`
--

INSERT INTO `audittrails` (`id`, `datetime`, `username`, `action`) VALUES
(723, '2025-03-16 10:11:26', 'harzixuan', 'Logout'),
(722, '2025-03-16 10:09:21', 'harzixuan', 'Login'),
(721, '2025-03-12 14:23:26', 'harzixuan', 'Logout'),
(720, '2025-03-12 14:22:13', 'harzixuan', 'Login'),
(719, '2025-03-12 14:22:01', 'harzixuan', 'Logout'),
(718, '2025-03-12 14:21:30', 'harzixuan', 'Login'),
(717, '2025-03-12 14:21:15', 'harzixuan', 'Logout'),
(715, '2025-03-12 07:15:14', 'harzixuan', 'Logout'),
(716, '2025-03-12 14:19:32', 'harzixuan', 'Login'),
(714, '2025-03-12 07:14:31', 'harzixuan', 'Login'),
(711, '2024-10-14 11:19:11', 'harzixuan', 'Login'),
(712, '2025-03-12 07:08:46', 'harzixuan', 'Login'),
(713, '2025-03-12 07:14:10', 'harzixuan', 'Logout'),
(710, '2024-10-11 03:57:02', 'harzixuan', 'Login'),
(709, '2024-10-11 03:56:22', 'harzixuan', 'Login'),
(708, '2024-04-16 08:24:16', 'harzixuan', 'Login'),
(707, '2024-03-13 16:22:39', 'harzixuan', 'Logout'),
(706, '2024-03-13 16:04:40', 'harzixuan', 'Transfer 2 of Metal Spoon to Aeon Bukit Indah'),
(705, '2024-03-13 16:01:13', 'harzixuan', 'Add Product: diijd'),
(704, '2024-03-13 15:56:31', 'harzixuan', 'Add Product: hi'),
(703, '2024-03-13 15:55:25', 'harzixuan', 'Login'),
(702, '2023-12-30 16:54:48', 'harzixuan', 'Logout'),
(701, '2023-12-30 16:52:27', 'harzixuan', 'Login'),
(700, '2023-11-17 07:00:48', 'harzixuan', 'Login'),
(699, '2023-11-17 06:40:40', 'harzixuan', 'Logout'),
(698, '2023-11-17 05:35:16', 'harzixuan', 'Add Product: Gru Toy'),
(697, '2023-11-17 05:34:19', 'harzixuan', 'Delete Product: yhyh'),
(696, '2023-11-17 05:33:18', 'harzixuan', 'Add Product: yhyh'),
(695, '2023-11-17 05:15:48', 'harzixuan', 'Delete Product: M&G pencil'),
(694, '2023-11-17 05:13:59', 'harzixuan', 'Edit Product: Metal Spoon'),
(693, '2023-11-17 04:39:17', 'harzixuan', 'Login'),
(692, '2023-11-17 01:46:19', 'harzixuan', 'Login'),
(691, '2023-11-17 01:42:58', 'harzixuan', 'Logout'),
(690, '2023-11-17 01:42:46', 'harzixuan', 'Login'),
(689, '2023-11-16 17:52:41', 'harzixuan', 'Login'),
(688, '2023-11-16 17:49:28', 'harzixuan', 'Logout'),
(687, '2023-11-16 14:09:49', 'harzixuan', 'Login'),
(686, '2023-11-16 07:18:47', 'harzixuan', 'Login'),
(685, '2023-11-16 07:17:54', 'harzixuan', 'Logout'),
(684, '2023-11-16 07:17:48', 'harzixuan', 'Login'),
(683, '2023-11-16 07:17:38', 'harzixuan', 'Login'),
(682, '2023-11-16 07:17:24', 'harzixuan', 'Logout'),
(681, '2023-11-16 07:15:27', 'harzixuan', 'Login'),
(680, '2023-11-16 07:15:05', 'admin1', 'Logout'),
(679, '2023-11-16 07:12:55', 'admin1', 'Login'),
(678, '2023-11-16 07:12:48', 'harzixuan', 'Logout'),
(677, '2023-11-16 07:09:47', 'harzixuan', 'Transfer 20 of M&G pencil to Aeon Tebrau'),
(676, '2023-11-16 07:07:01', 'harzixuan', 'Add Product: Bag'),
(675, '2023-11-16 07:06:14', 'harzixuan', 'Add Product: Bag'),
(674, '2023-11-16 07:01:43', 'harzixuan', 'Login'),
(673, '2023-11-16 07:01:21', 'harzixuan', 'Login'),
(672, '2023-11-16 06:55:52', 'harzixuan', 'Logout'),
(671, '2023-11-16 06:44:37', 'harzixuan', 'Delete Product: Rice cooker'),
(670, '2023-11-16 06:32:38', 'harzixuan', 'Login'),
(669, '2023-11-16 06:32:22', 'harzixuan', 'Logout'),
(668, '2023-11-16 06:30:37', 'harzixuan', 'Delete Product: GRU TOY'),
(667, '2023-11-16 06:29:43', 'harzixuan', 'Transfer 3 of ArtLine MarkerPen to Aeon Tebrau'),
(666, '2023-11-16 06:25:48', 'harzixuan', 'Login'),
(665, '2023-11-16 06:08:50', 'harzixuan', 'Logout'),
(664, '2023-11-16 05:57:38', 'harzixuan', 'Delete Product: school bag'),
(663, '2023-11-16 05:57:34', 'harzixuan', 'Delete Product: school bag'),
(662, '2023-11-16 05:57:07', 'harzixuan', 'Add Product: GRU TOY'),
(661, '2023-11-16 05:19:44', 'harzixuan', 'Login'),
(660, '2023-11-16 04:09:08', 'rc', 'Logout'),
(659, '2023-11-16 03:34:04', 'rc', 'Delete Product: Gru Toy'),
(658, '2023-11-16 03:32:35', 'rc', 'Transfer 10 of Gru Toy to Aeon Tebrau'),
(657, '2023-11-16 03:30:11', 'rc', 'Add Product: school bag'),
(656, '2023-11-16 03:29:16', 'rc', 'Add Product: school bag'),
(655, '2023-11-16 03:25:42', 'rc', 'Login'),
(654, '2023-11-16 03:25:36', 'harzixuan', 'Logout'),
(653, '2023-11-16 03:24:26', 'harzixuan', 'Login'),
(652, '2023-11-16 03:24:03', 'harzixuan', 'Logout'),
(651, '2023-11-16 02:47:00', 'harzixuan', 'Login'),
(650, '2023-11-16 02:46:48', 'harzixuan', 'Logout'),
(649, '2023-11-16 02:45:10', 'harzixuan', 'Login'),
(648, '2023-11-16 02:44:38', 'harzixuan', 'Logout'),
(647, '2023-11-16 02:22:22', 'harzixuan', 'Transfer 10 of Metal Spoon to Aeon Tebrau'),
(646, '2023-11-16 02:19:23', 'harzixuan', 'Login'),
(645, '2023-11-16 02:18:57', 'harzixuan', 'Logout'),
(644, '2023-11-16 02:12:22', 'harzixuan', 'Login'),
(643, '2023-11-16 02:11:55', 'harzixuan', 'Logout'),
(642, '2023-11-16 02:11:53', 'harzixuan', 'Login'),
(641, '2023-11-16 02:11:42', 'harzixuan', 'Logout'),
(640, '2023-11-16 01:52:56', 'harzixuan', 'Login'),
(639, '2023-11-16 01:39:35', 'harzixuan', 'Login'),
(638, '2023-11-16 01:38:54', 'admin2', 'Logout'),
(637, '2023-11-16 01:32:43', 'admin2', 'Add Product: Rice cooker'),
(636, '2023-11-16 01:25:33', 'admin2', 'Add Product: Faber Castle earser'),
(635, '2023-11-16 01:24:00', 'admin2', 'Add Product: Disney lego'),
(634, '2023-11-16 01:21:17', 'admin2', 'Edit Product: Metal Spoon'),
(633, '2023-11-16 01:20:21', 'admin2', 'Add Product: Monkey toy'),
(632, '2023-11-16 01:19:00', 'admin2', 'Edit Product: Metal Spoon'),
(631, '2023-11-16 01:18:45', 'admin2', 'Edit Product: Metal Spoon'),
(630, '2023-11-16 01:18:32', 'admin2', 'Delete Product: Cat school bag'),
(629, '2023-11-16 01:17:13', 'admin2', 'Login'),
(628, '2023-11-16 01:17:03', 'admin2', 'Logout'),
(627, '2023-11-16 01:16:36', 'admin2', 'Edit Product: Pencil Case'),
(626, '2023-11-16 01:14:47', 'admin2', 'Edit Product: Amongus Pencil Case'),
(625, '2023-11-16 01:14:43', 'admin2', 'Edit Product: Cat school bag'),
(624, '2023-11-16 01:14:38', 'admin2', 'Edit Product: M&G pencil'),
(623, '2023-11-16 01:14:16', 'admin2', 'Edit Product: ArtLine MarkerPen'),
(622, '2023-11-16 01:13:50', 'admin2', 'Edit Product: ArtLine MarkerPen'),
(621, '2023-11-16 01:13:33', 'admin2', 'Add Product: Amongus Pencil Case'),
(620, '2023-11-16 01:04:53', 'admin2', 'Edit Product: Gru Toy'),
(619, '2023-11-16 01:04:22', 'admin2', 'Edit Product: Metal Spoon'),
(618, '2023-11-16 00:57:52', 'admin2', 'Transfer 50 of M&G pencil to Aeon Tebrau'),
(617, '2023-11-16 00:57:35', 'admin2', 'Transfer 42 of ArtLine MarkerPen to Aeon Bukit Indah'),
(616, '2023-11-16 00:55:01', 'admin2', 'Transfer 42 of ArtLine MarkerPen to Aeon Tebrau'),
(615, '2023-11-16 00:45:10', 'admin2', 'Transfer 2 of ArtLine MarkerPen to Aeon Tebrau'),
(614, '2023-11-16 00:44:01', 'admin2', 'Edit Product: Metal Spoon'),
(613, '2023-11-16 00:38:25', 'admin2', 'Login'),
(612, '2023-11-16 00:37:56', 'admin2', 'Logout'),
(611, '2023-11-16 00:37:39', 'admin2', 'Login'),
(610, '2023-11-16 00:37:36', 'admin2', 'Logout'),
(609, '2023-11-16 00:36:56', 'admin2', 'Transfer 10 of Gru Toy to Aeon Tebrau'),
(608, '2023-11-16 00:33:38', 'admin2', 'Login'),
(607, '2023-11-15 20:00:15', 'admin2', 'Transfer 4 of Cat school bag to Aeon Bukit Indah'),
(606, '2023-11-15 19:59:23', 'admin2', 'Edit Product: Gru Toy'),
(605, '2023-11-15 19:58:21', 'admin2', 'Transfer 7 of ArtLine MarkerPen to Aeon Bukit Indah'),
(604, '2023-11-15 19:57:04', 'admin2', 'Login'),
(603, '2023-11-15 19:56:54', 'admin2', 'Logout'),
(602, '2023-11-15 19:48:49', 'admin2', 'Add Product: Metal Spatula'),
(601, '2023-11-15 19:45:06', 'admin2', 'Transfer 10 of Metal Spoon to Aeon Tebrau'),
(600, '2023-11-15 16:58:11', 'admin2', 'Transfer 10 of Metal Spoon to Aeon Tebrau'),
(599, '2023-11-15 16:49:34', 'admin2', 'Edit Product: Gru Toy'),
(598, '2023-11-15 16:48:47', 'admin2', 'Edit Product: Metal Spoon'),
(597, '2023-11-15 16:48:38', 'admin2', 'Edit Product: Metal Spoon'),
(596, '2023-11-15 15:38:18', 'admin2', 'Edit Product: ArtLine MarkerPen'),
(595, '2023-11-15 15:38:13', 'admin2', 'Edit Product: ArtLine MarkerPen'),
(594, '2023-11-15 15:37:49', 'admin2', 'Add Product: Metal Spoon'),
(593, '2023-11-15 15:36:26', 'admin2', 'Edit Product: Gru Toy'),
(592, '2023-11-15 15:35:37', 'admin2', 'Add Product: Gru Toy'),
(591, '2023-11-15 15:32:25', 'admin2', 'Add Product: M&amp;G pencil'),
(590, '2023-11-15 15:27:09', 'admin2', 'Add Product: Cat school bag'),
(503, '2023-11-14 18:16:53', 'admin2', 'Transfer 0 of ded to area51'),
(504, '2023-11-14 18:20:11', 'admin2', 'Transfer 2 of frffr to area51'),
(505, '2023-11-14 18:20:21', 'admin2', 'Transfer 2 of fv55 to area51'),
(506, '2023-11-14 18:20:40', 'admin2', 'Transfer 6 of frffr to area51'),
(507, '2023-11-14 18:20:52', 'admin2', 'Transfer 3 of testing4 to area51'),
(508, '2023-11-14 18:22:21', 'admin2', 'Transfer 44 of ff to area51'),
(509, '2023-11-14 18:22:38', 'admin2', 'Transfer 27 of frffr to area51'),
(510, '2023-11-14 18:22:49', 'admin2', 'Transfer 3 of testing4 to area51'),
(511, '2023-11-14 18:23:09', 'admin2', 'Transfer 6 of tgtg to area51'),
(512, '2023-11-14 18:23:20', 'admin2', 'Transfer 5 of frffr to area51'),
(513, '2023-11-14 18:23:46', 'admin2', 'Transfer 4 of testing4 to area51'),
(514, '2023-11-14 18:27:35', 'admin2', 'Transfer 2 of frffr to area51'),
(515, '2023-11-14 18:27:54', 'admin2', 'Delete Product: ff'),
(516, '2023-11-14 18:28:01', 'admin2', 'Delete Product: ggt'),
(517, '2023-11-14 18:28:36', 'admin2', 'Add Product: rr'),
(518, '2023-11-14 18:31:08', 'admin2', 'Edit Product: ded'),
(519, '2023-11-14 18:31:19', 'admin2', 'Transfer 2 of ded to area51'),
(520, '2023-11-14 18:31:30', 'admin2', 'Edit Product: ded'),
(521, '2023-11-14 18:31:40', 'admin2', 'Transfer 44 of ded to area51'),
(522, '2023-11-14 18:34:58', 'admin2', 'Transfer 3 of dcggf to area51'),
(523, '2023-11-14 18:45:39', 'admin2', 'Logout'),
(524, '2023-11-15 03:05:56', 'admin2', 'Login'),
(525, '2023-11-15 04:59:21', 'admin2', 'Logout'),
(526, '2023-11-15 08:03:25', 'admin2', 'Login'),
(527, '2023-11-15 09:50:09', 'admin2', 'Delete Product: ded'),
(528, '2023-11-15 10:06:23', 'admin2', 'Edit Product: fv55'),
(529, '2023-11-15 10:09:25', 'admin2', 'Edit Product: rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr'),
(530, '2023-11-15 10:11:32', 'admin2', 'Edit Product: rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr'),
(531, '2023-11-15 10:12:00', 'admin2', 'Transfer 1 of rrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr to area52'),
(532, '2023-11-15 10:27:25', 'admin2', 'Edit Product: rrrrrrrrrrrrrrrrrrrr'),
(533, '2023-11-15 10:27:47', 'admin2', 'Transfer 2 of rrrrrrrrrrrrrrrrrrrr to area52'),
(534, '2023-11-15 10:28:21', 'admin2', 'Edit Product: rrrrrrrrrrrrrrrrrrrrrrrrrrrrrr'),
(535, '2023-11-15 10:28:31', 'admin2', 'Transfer 1 of rrrrrrrrrrrrrrrrrrrrrrrrrrrrrr to area52'),
(536, '2023-11-15 11:05:31', 'admin2', 'Logout'),
(537, '2023-11-15 11:05:34', 'admin2', 'Login'),
(538, '2023-11-15 11:09:57', 'admin2', 'Logout'),
(539, '2023-11-15 11:10:07', 'admin2', 'Login'),
(540, '2023-11-15 11:30:38', 'admin2', 'Login'),
(541, '2023-11-15 11:31:23', 'admin2', 'Logout'),
(542, '2023-11-15 11:31:29', 'admin2', 'Login'),
(543, '2023-11-15 11:33:29', 'admin2', 'Logout'),
(544, '2023-11-15 11:33:32', 'admin2', 'Login'),
(545, '2023-11-15 12:39:53', 'admin2', 'Logout'),
(546, '2023-11-15 12:41:46', 'admin2', 'Login'),
(547, '2023-11-15 12:41:47', 'admin2', 'Logout'),
(548, '2023-11-15 12:41:56', 'admin2', 'Login'),
(549, '2023-11-15 12:41:57', 'admin2', 'Logout'),
(550, '2023-11-15 12:43:25', 'admin2', 'Login'),
(551, '2023-11-15 12:43:33', 'admin2', 'Login'),
(552, '2023-11-15 12:43:34', 'admin2', 'Logout'),
(553, '2023-11-15 12:48:39', 'admin2', 'Login'),
(554, '2023-11-15 12:48:41', 'admin2', 'Logout'),
(555, '2023-11-15 12:48:50', 'admin2', 'Login'),
(556, '2023-11-15 12:48:52', 'admin2', 'Logout'),
(557, '2023-11-15 12:49:02', 'admin2', 'Login'),
(558, '2023-11-15 12:52:20', 'admin2', 'Login'),
(559, '2023-11-15 12:52:22', 'admin2', 'Logout'),
(560, '2023-11-15 12:52:32', 'admin2', 'Login'),
(561, '2023-11-15 12:52:33', 'admin2', 'Logout'),
(562, '2023-11-15 12:57:39', 'admin2', 'Login'),
(563, '2023-11-15 12:57:41', 'admin2', 'Logout'),
(564, '2023-11-15 12:57:58', 'admin2', 'Login'),
(565, '2023-11-15 13:00:05', 'admin2', 'Login'),
(566, '2023-11-15 13:00:28', 'admin2', 'Login'),
(567, '2023-11-15 13:01:00', 'admin2', 'Login'),
(568, '2023-11-15 13:31:23', 'admin2', 'Edit Product: frffr'),
(569, '2023-11-15 13:33:13', 'admin2', 'Delete Product: rrrrrrrrrrrrrrrrrrrrrrrrrrrrrr'),
(570, '2023-11-15 13:46:08', 'admin2', 'Logout'),
(571, '2023-11-15 13:46:23', 'admin3', 'Login'),
(572, '2023-11-15 13:48:22', 'admin3', 'Add Product: haha'),
(573, '2023-11-15 13:49:46', 'admin3', 'Add Product: hyhyh'),
(574, '2023-11-15 13:56:34', 'admin3', 'Add Product: hehehhea'),
(575, '2023-11-15 14:05:17', 'admin3', 'Edit Product: frffr'),
(576, '2023-11-15 14:05:36', 'admin3', 'Edit Product: frffr'),
(577, '2023-11-15 14:07:49', 'admin3', 'Edit Product: frffr'),
(578, '2023-11-15 14:08:06', 'admin3', 'Edit Product: frffr'),
(579, '2023-11-15 14:09:14', 'admin3', 'Edit Product: frffr'),
(580, '2023-11-15 14:10:17', 'admin3', 'Edit Product: hehehhea'),
(581, '2023-11-15 14:17:28', 'admin3', 'Edit Product: hehehhea'),
(582, '2023-11-15 14:17:36', 'admin3', 'Edit Product: hehehhea'),
(583, '2023-11-15 14:17:49', 'admin3', 'Edit Product: haha'),
(584, '2023-11-15 14:23:08', 'admin3', 'Add Product: ded'),
(585, '2023-11-15 14:55:44', 'admin3', 'Add Product: ArtLine MarkerPen'),
(586, '2023-11-15 14:56:39', 'admin3', 'Add Product: ArtLine MarkerPen'),
(587, '2023-11-15 15:15:37', 'admin3', 'Delete Product: hehehhea'),
(588, '2023-11-15 15:22:24', 'admin3', 'Logout'),
(589, '2023-11-15 15:22:30', 'admin2', 'Login');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `categoryid` varchar(250) NOT NULL,
  `categoryname` varchar(250) NOT NULL,
  `categorydescription` varchar(250) NOT NULL,
  PRIMARY KEY (`categoryid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`categoryid`, `categoryname`, `categorydescription`) VALUES
('C03', 'Shoes', 'More clothes'),
('COOKING', 'Cooking tools', 'Cooking equipment or tool'),
('TOY01', 'Toy', 'Many toy, endless creative possibilities.'),
('S01', 'Stationary', 'Stationery product and more');

-- --------------------------------------------------------

--
-- Table structure for table `companyprofile`
--

DROP TABLE IF EXISTS `companyprofile`;
CREATE TABLE IF NOT EXISTS `companyprofile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `profilepicture` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `companyprofile`
--

INSERT INTO `companyprofile` (`id`, `name`, `profilepicture`) VALUES
(1, 'WinG', 'picture/CompanyLogo.png');

-- --------------------------------------------------------

--
-- Table structure for table `deliverorder`
--

DROP TABLE IF EXISTS `deliverorder`;
CREATE TABLE IF NOT EXISTS `deliverorder` (
  `id` int(250) NOT NULL AUTO_INCREMENT,
  `productid` varchar(250) NOT NULL,
  `name` varchar(250) NOT NULL,
  `quantity` int(250) NOT NULL,
  `unitprice` int(255) NOT NULL,
  `location` varchar(200) NOT NULL,
  `addeddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `deliverorder`
--

INSERT INTO `deliverorder` (`id`, `productid`, `name`, `quantity`, `unitprice`, `location`, `addeddate`) VALUES
(52, 'C02', 'Metal Spoon', 2, 3, 'Aeon Bukit Indah', '2024-03-13 16:04:40'),
(51, 'P01', 'M&G pencil', 20, 5, 'Aeon Tebrau', '2023-11-16 07:09:47'),
(50, 'MP01', 'ArtLine MarkerPen', 3, 2, 'Aeon Tebrau', '2023-11-16 06:29:43'),
(49, 'TOY01', 'Gru Toy', 10, 10, 'Aeon Tebrau', '2023-11-16 03:32:35'),
(48, 'C02', 'Metal Spoon', 10, 3, 'Aeon Tebrau', '2023-11-16 02:22:22'),
(47, 'P01', 'M&G pencil', 50, 5, 'Aeon Tebrau', '2023-11-16 00:57:52'),
(46, 'MP02', 'ArtLine MarkerPen', 42, 2, 'Aeon Bukit Indah', '2023-11-16 00:57:35'),
(45, 'MP01', 'ArtLine MarkerPen', 42, 2, 'Aeon Tebrau', '2023-11-16 00:55:01'),
(44, 'MP01', 'ArtLine MarkerPen', 2, 2, 'Aeon Tebrau', '2023-11-16 00:45:10'),
(43, 'TOY01', 'Gru Toy', 10, 10, 'Aeon Tebrau', '2023-11-16 00:36:56'),
(42, 'SB01', 'Cat school bag', 4, 49, 'Aeon Bukit Indah', '2023-11-15 20:00:15'),
(41, 'MP02', 'ArtLine MarkerPen', 7, 2, 'Aeon Bukit Indah', '2023-11-15 19:58:21'),
(40, 'C02', 'Metal Spoon', 10, 3, 'Aeon Tebrau', '2023-11-15 19:45:06'),
(39, 'C02', 'Metal Spoon', 10, 3, 'Aeon Tebrau', '2023-11-15 16:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `id` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `quantity` int(20) NOT NULL,
  `unitprice` decimal(20,2) NOT NULL,
  `variant` varchar(200) DEFAULT NULL,
  `description` varchar(300) NOT NULL,
  `category` varchar(200) NOT NULL,
  `status` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL,
  `addeddate` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `quantity`, `unitprice`, `variant`, `description`, `category`, `status`, `image`, `addeddate`) VALUES
('C02', 'Metal Spoon', 8, '3.00', '', 'Cooking tools, stainless steel spoons', 'Cooking tools', 'Active', 'picture/spoonx.jpg', '2023-11-15 07:37:49'),
('MP01', 'ArtLine MarkerPen', 9, '2.00', 'Red', 'Precision, vibrant colors, quick-drying, waterproof, ergonomic design.', 'Stationary', 'Active', 'picture/Artline 100 Permanent Marker Pen - Red.jpeg', '2023-11-15 06:55:44'),
('MP02', 'ArtLine MarkerPen', 7, '2.00', 'Blue', 'Precision, vibrant colors, quick-drying, waterproof, ergonomic design.', 'Stationary', 'Active', 'picture/artline-100-marker-pen-blue.jpg', '2023-11-15 06:56:39'),
('TOY05', 'Gru Toy', 10, '10.00', 'Gru', 'Gru toy despicable me', 'Toy', 'Active', 'picture/gru.jpg', '2023-11-16 21:35:16'),
('TOY02', 'Monkey toy', 99, '50.00', '', 'Squish easily, made of rubber', 'Toy', 'Active', 'picture/monke.jpeg', '2023-11-15 17:20:21'),
('COO2', 'Metal Spatula', 6, '10.00', '', 'Made of stainless steel', 'Cooking tools', 'Active', 'picture/spatula.jpg', '2023-11-15 11:48:49'),
('PC01', 'Pencil Case', 79, '3.00', '', 'Among us theme pencil case, black color', 'Stationary', 'Active', 'picture/Pencil case.jpeg', '2023-11-15 17:13:33'),
('654f', 'hi', 3, '44.00', '', 'd', 'Shoes', 'Active', 'picture/apple juice.jpg', '2024-03-13 07:56:31'),
('TOY03', 'Disney lego', 77, '20.00', '', 'Goofy and donald duck disney lego', 'Toy', 'Active', 'picture/disney lego.jpg', '2023-11-15 17:24:00'),
('S04', 'Faber Castle earser', 50, '2.00', '', 'Faber Casttle Dust Free Earser', 'Stationary', 'Active', 'picture/FABER-CASTELL.jpg', '2023-11-15 17:25:33'),
('BAG01', 'Bag', 20, '50.00', 'blue', 'cat blur bag', 'Stationary', 'Active', 'picture/cat school bag.jpg', '2023-11-15 23:06:14'),
('BAG02', 'Bag', 20, '50.00', 'yellow', 'cat blur bag', 'Stationary', 'Active', 'picture/Emoji school bag.jpg', '2023-11-15 23:07:01'),
('ji', 'diijd', 2, '55.00', 'red', 'w', 'Shoes', 'Active', 'picture/17 kills.png', '2024-03-13 08:01:13');

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
CREATE TABLE IF NOT EXISTS `location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `address` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `name`, `address`) VALUES
(3, 'Aeon Tebrau', 'Ã†ON Mall Tebrau City, S15 level 2, Centre Court, Aeon Tebrau City Shopping Centre, Persiaran Desa Tebrau, Taman Desa Tebrau, 81100 Johor Bahru, Johor'),
(6, 'Aeon Bukit Indah', 'Grand Mezzanine, 8, Jalan Indah 15/2, Taman Bukit Indah, 81200 Johor Bahru, Johor'),
(7, 'new location', '109, Jalan Putra Squre, Johor Bahru, Johor');

-- --------------------------------------------------------

--
-- Table structure for table `loginouthistory`
--

DROP TABLE IF EXISTS `loginouthistory`;
CREATE TABLE IF NOT EXISTS `loginouthistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(11) NOT NULL,
  `login` timestamp NOT NULL,
  `logout` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `loginouthistory`
--

INSERT INTO `loginouthistory` (`id`, `username`, `login`, `logout`) VALUES
(147, 'harzixuan', '2025-03-16 10:09:21', '2025-03-16 10:11:26'),
(146, 'harzixuan', '2025-03-12 14:22:13', '2025-03-12 14:23:26'),
(145, 'harzixuan', '2025-03-12 14:21:30', '2025-03-12 14:22:01'),
(144, 'harzixuan', '2025-03-12 14:19:32', '2025-03-12 14:21:15'),
(143, 'harzixuan', '2025-03-12 07:14:31', '2025-03-12 07:15:14'),
(142, 'harzixuan', '2025-03-12 07:08:46', '2025-03-12 07:14:10'),
(141, 'harzixuan', '2024-03-13 15:55:25', '2024-03-13 16:22:39'),
(140, 'harzixuan', '2023-12-30 16:52:27', '2023-12-30 16:54:48'),
(139, 'harzixuan', '2023-11-17 04:39:17', '2023-11-17 06:40:40'),
(138, 'harzixuan', '2023-11-17 01:42:46', '2023-11-17 01:42:58'),
(137, 'harzixuan', '2023-11-16 14:09:49', '2023-11-16 17:49:28'),
(136, 'harzixuan', '2023-11-16 07:17:48', '2023-11-16 07:17:54'),
(135, 'harzixuan', '2023-11-16 07:15:27', '2023-11-16 07:17:24'),
(134, 'admin1', '2023-11-16 07:12:55', '2023-11-16 07:15:05'),
(133, 'harzixuan', '2023-11-16 07:01:43', '2023-11-16 07:12:48'),
(132, 'harzixuan', '2023-11-16 06:32:38', '2023-11-16 06:55:52'),
(131, 'harzixuan', '2023-11-16 06:25:48', '2023-11-16 06:32:22'),
(130, 'harzixuan', '2023-11-16 05:19:44', '2023-11-16 06:08:50'),
(129, 'rc', '2023-11-16 03:25:42', '2023-11-16 04:09:08'),
(128, 'harzixuan', '2023-11-16 03:24:26', '2023-11-16 03:25:36'),
(127, 'harzixuan', '2023-11-16 02:47:00', '2023-11-16 03:24:03'),
(126, 'harzixuan', '2023-11-16 02:45:10', '2023-11-16 02:46:48'),
(125, 'harzixuan', '2023-11-16 02:19:23', '2023-11-16 02:44:38'),
(124, 'harzixuan', '2023-11-16 02:12:22', '2023-11-16 02:18:57'),
(123, 'harzixuan', '2023-11-16 02:11:53', '2023-11-16 02:11:55'),
(122, 'harzixuan', '2023-11-16 01:52:56', '2023-11-16 02:11:42'),
(121, 'admin2', '2023-11-16 01:17:13', '2023-11-16 01:38:54'),
(120, 'admin2', '2023-11-16 00:38:25', '2023-11-16 01:17:03'),
(119, 'admin2', '2023-11-16 00:37:39', '2023-11-16 00:37:56'),
(118, 'admin2', '2023-11-16 00:33:38', '2023-11-16 00:37:36'),
(117, 'admin2', '2023-11-15 15:22:30', '2023-11-15 19:56:54');

-- --------------------------------------------------------

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `mainid` int(250) NOT NULL AUTO_INCREMENT,
  `id` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unitprice` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `status` varchar(250) NOT NULL,
  `image` varchar(250) NOT NULL,
  `variant` varchar(255) NOT NULL,
  `Action` varchar(250) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mainid`)
) ENGINE=MyISAM AUTO_INCREMENT=269 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `report`
--

INSERT INTO `report` (`mainid`, `id`, `name`, `quantity`, `unitprice`, `description`, `category`, `status`, `image`, `variant`, `Action`, `date`) VALUES
(268, 'C02', 'Metal Spoon', 2, '3.00', '', 'Transfer product to Aeon Bukit Indah', 'Active', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', '2024-03-13 16:04:40'),
(267, 'ji', 'diijd', 2, '55.00', '', 'Shoes', 'Active', 'picture/17 kills.png', 'red', 'Added to inventory', '2024-03-13 16:01:13'),
(266, '654f', 'hi', 3, '44.00', '', 'Shoes', 'Active', 'picture/apple juice.jpg', '', 'Added to inventory', '2024-03-13 15:56:31'),
(265, 'TOY05', 'Gru Toy', 10, '10.00', '', 'Toy', 'Active', 'picture/gru.jpg', 'Gru', 'Added to inventory', '2023-11-17 05:35:16'),
(264, '7654', 'yhyh', 77, '66.00', '', 'Cooking tools', 'Active', 'picture/gru.jpg', 'hi', 'Added to inventory', '2023-11-17 05:33:18'),
(263, 'C02', 'Metal Spoon', 10, '3.00', '', 'Cooking tools', 'Active', 'picture/spoonx.jpg', '', 'Quantity changed from 2 to 10', '2023-11-17 05:13:59'),
(262, 'P01', 'M&G pencil', 20, '5.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 07:09:47'),
(261, 'BAG02', 'Bag', 20, '50.00', '', 'Stationary', 'Active', 'picture/Emoji school bag.jpg', 'yellow', 'Added to inventory', '2023-11-16 07:07:01'),
(260, 'BAG01', 'Bag', 20, '50.00', '', 'Stationary', 'Active', 'picture/cat school bag.jpg', 'blue', 'Added to inventory', '2023-11-16 07:06:14'),
(258, 'TOY01', 'GRU TOY', 99, '10.00', '', 'Toy', 'Active', 'picture/gru.jpg', '', 'Added to inventory', '2023-11-16 05:57:07'),
(259, 'MP01', 'ArtLine MarkerPen', 3, '2.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 06:29:43'),
(257, 'TOY01', 'Gru Toy', 10, '10.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 03:32:35'),
(255, 'SB1', 'school bag', 10, '20.00', '', 'Stationary', 'Active', 'picture/cat school bag.jpg', 'blue', 'Added to inventory', '2023-11-16 03:29:16'),
(256, 'SB02', 'school bag', 10, '20.00', '', 'Stationary', 'Active', 'picture/Emoji school bag.jpg', 'Yellow', 'Added to inventory', '2023-11-16 03:30:11'),
(254, 'C02', 'Metal Spoon', 10, '3.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 02:22:22'),
(253, 'CO03', 'Rice cooker', 70, '40.00', '', 'Cooking tools', 'Active', 'picture/Rice cooker.jpg', '', 'Added to inventory', '2023-11-16 01:32:43'),
(251, 'TOY03', 'Disney lego', 77, '20.00', '', 'Toy', 'Active', 'picture/disney lego.jpg', '', 'Added to inventory', '2023-11-16 01:24:00'),
(252, 'S04', 'Faber Castle earser', 50, '2.00', '', 'Stationary', 'Active', 'picture/FABER-CASTELL.jpg', '', 'Added to inventory', '2023-11-16 01:25:33'),
(250, 'C02', 'Metal Spoon', 12, '3.00', '', 'Cooking tools', 'Active', 'picture/spoonx.jpg', '', 'Product edited', '2023-11-16 01:21:17'),
(249, 'TOY02', 'Monkey toy', 99, '50.00', '', 'Toy', 'Active', 'picture/monke.jpeg', '', 'Added to inventory', '2023-11-16 01:20:21'),
(247, 'C02', 'Metal Spoon', 12, '3.00', '', 'Toy', 'Active', 'picture/Emoji school bag.jpg', '', 'Product edited', '2023-11-16 01:18:45'),
(248, 'C02', 'Metal Spoon', 12, '3.00', '', 'Toy', 'Active', 'picture/spoonx.jpg', '', 'Product edited', '2023-11-16 01:19:00'),
(246, 'PC01', 'Pencil Case', 79, '3.00', '', 'Stationary', 'Active', 'picture/Pencil case.jpeg', '', 'Product edited', '2023-11-16 01:16:36'),
(245, 'PC25', 'Amongus Pencil Case', 79, '22.00', '', 'Stationary', 'Active', 'picture/Pencil case.jpeg', '', 'Product edited', '2023-11-16 01:14:47'),
(244, 'SB01', 'Cat school bag', 5, '49.00', '', 'Stationary', 'Active', 'picture/cat school bag.jpg', '', 'Product edited', '2023-11-16 01:14:43'),
(243, 'P01', 'M&G pencil', 50, '5.00', '', 'Stationary', 'Active', 'picture/M&G 2B Pencil.jpg', '', 'Product edited', '2023-11-16 01:14:38'),
(242, 'MP02', 'ArtLine MarkerPen', 7, '2.00', '', 'Stationary', 'Active', 'picture/artline-100-marker-pen-blue.jpg', 'Blue', 'Product edited', '2023-11-16 01:14:16'),
(241, 'MP01', 'ArtLine MarkerPen', 12, '2.00', '', 'Stationary', 'Active', 'picture/Artline 100 Permanent Marker Pen - Red.jpeg', 'Red', 'Product edited', '2023-11-16 01:13:50'),
(240, 'PC25', 'Amongus Pencil Case', 79, '22.00', '', 'Stationery', 'Active', 'picture/Pencil case.jpeg', '', 'Added to inventory', '2023-11-16 01:13:33'),
(239, 'TOY01', 'Gru Toy', 21, '10.00', '', 'Toy', 'Active', 'picture/gru.jpg', '', 'Quantity changed from 40 to 21', '2023-11-16 01:04:53'),
(238, 'C02', 'Metal Spoon', 12, '3.00', '', 'Toy', 'Active', 'picture/spoonx.jpg', '', 'Quantity changed from 20 to 12', '2023-11-16 01:04:22'),
(237, 'P01', 'M&G pencil', 50, '5.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 00:57:52'),
(236, 'MP02', 'ArtLine MarkerPen', 42, '2.00', '', 'Transfer product to Aeon Bukit Indah', 'Active', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', '2023-11-16 00:57:35'),
(235, 'MP01', 'ArtLine MarkerPen', 42, '2.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 00:55:01'),
(234, 'MP01', 'ArtLine MarkerPen', 2, '2.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 00:45:10'),
(233, 'C02', 'Metal Spoon', 20, '3.00', '', 'Toy', 'Active', 'picture/spoonx.jpg', '', 'Quantity changed from 10 to 20', '2023-11-16 00:44:01'),
(232, 'TOY01', 'Gru Toy', 10, '10.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-16 00:36:56'),
(231, 'SB01', 'Cat school bag', 4, '49.00', '', 'Transfer product to Aeon Bukit Indah', 'Active', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', '2023-11-15 20:00:15'),
(230, 'TOY01', 'Gru Toy', 50, '10.00', '', 'Toy', 'Active', 'picture/gru.jpg', '', 'Quantity changed from 242 to 50', '2023-11-15 19:59:23'),
(229, 'MP02', 'ArtLine MarkerPen', 7, '2.00', '', 'Transfer product to Aeon Bukit Indah', 'Active', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', 'Transfer product to Aeon Bukit Indah', '2023-11-15 19:58:21'),
(228, 'COO2', 'Metal Spatula', 6, '10.00', '', 'Cooking tools', 'Active', 'picture/spatula.jpg', '', 'Added to inventory', '2023-11-15 19:48:49'),
(227, 'C02', 'Metal Spoon', 10, '3.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-15 19:45:06'),
(226, 'C02', 'Metal Spoon', 10, '3.00', '', 'Transfer product to Aeon Tebrau', 'Active', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', 'Transfer product to Aeon Tebrau', '2023-11-15 16:58:11'),
(225, 'TOY01', 'Gru Toy', 242, '10.00', '', 'Toy', 'Active', 'picture/gru.jpg', '', 'Product edited', '2023-11-15 16:49:34'),
(224, 'C02', 'Metal Spoon', 30, '3.00', '', 'Toy', 'Active', 'picture/spoonx.jpg', '', 'Product edited', '2023-11-15 16:48:47'),
(223, 'C02', 'Metal Spoon', 30, '3.00', '', '', 'Active', 'picture/spoonx.jpg', 'metal', 'Product edited', '2023-11-15 16:48:38'),
(214, 'MP01', 'ArtLine MarkerPen', 56, '2.00', '', 'meow', 'Active', 'picture/Artline 100 Permanent Marker Pen - Red.jpeg', 'Red', 'Added to inventory', '2023-11-15 14:55:44'),
(215, 'MP02', 'ArtLine MarkerPen', 56, '2.00', '', 'meow', 'Active', 'picture/artline-100-marker-pen-blue.jpg', 'Blue', 'Added to inventory', '2023-11-15 14:56:39'),
(216, 'SB01', 'Cat school bag', 9, '49.00', '', 'Stationery', 'Active', 'picture/cat school bag.jpg', '', 'Added to inventory', '2023-11-15 15:27:09'),
(217, 'P01', 'M&amp;G pencil', 100, '5.00', '', 'Stationery', 'Active', 'picture/M&G 2B Pencil.jpg', '', 'Added to inventory', '2023-11-15 15:32:25'),
(218, 'DOLL01', 'Gru Toy', 242, '50.00', '', 'Toys', 'Active', 'picture/gru.jpg', '', 'Added to inventory', '2023-11-15 15:35:37'),
(219, 'TOY01', 'Gru Toy', 242, '10.00', '', 'Toys', 'Active', 'picture/gru.jpg', '', 'Product edited', '2023-11-15 15:36:26'),
(220, 'C02', 'Metal Spoon', 30, '3.00', '', 'Toys', 'Active', 'picture/spoonx.jpg', '', 'Added to inventory', '2023-11-15 15:37:49'),
(221, 'MP01', 'ArtLine MarkerPen', 56, '2.00', '', 'Stationery', 'Active', 'picture/Artline 100 Permanent Marker Pen - Red.jpeg', 'Red', 'Product edited', '2023-11-15 15:38:13'),
(222, 'MP02', 'ArtLine MarkerPen', 56, '2.00', '', 'Stationery', 'Active', 'picture/artline-100-marker-pen-blue.jpg', 'Blue', 'Product edited', '2023-11-15 15:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `passw` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `passw`, `name`) VALUES
(26, 'admin1', '$2y$10$QywZo8z/lgMkJDHpVx1nXu40/bAQv4BXB31nm.bvMMtlkPcl9Zafy', 'admin1'),
(24, 'harzixuan', '$2y$10$RrOZNJeKFrNN7a80pwc7Jurxd/tT7DgXZXpnz59NcZKx9H9mUbMuK', 'harzixuan');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
