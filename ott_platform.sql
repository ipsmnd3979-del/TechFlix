-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 02, 2025 at 07:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ott_platform`
--

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) NOT NULL,
  `target_url` varchar(500) DEFAULT NULL,
  `type` enum('home','movie','tv_show','promotional') DEFAULT 'home',
  `is_active` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `description`, `image_url`, `target_url`, `type`, `is_active`, `display_order`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(4, 'Kota Factory', NULL, '../assets/uploads/banners/banner_1761957960_69055848a0096.png', NULL, 'tv_show', 1, 0, NULL, NULL, '2025-11-01 00:46:00', '2025-11-01 00:46:00'),
(5, 'Devera', NULL, '../assets/uploads/banners/banner_1761962998_69056bf66a459.png', NULL, '', 1, 0, NULL, NULL, '2025-11-01 02:09:58', '2025-11-01 02:09:58'),
(6, 'Kurukshetra', NULL, '../assets/uploads/banners/banner_1761963044_69056c24915fa.png', NULL, 'movie', 1, 0, NULL, NULL, '2025-11-01 02:10:44', '2025-11-01 02:10:44');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Action', 'High-energy content with exciting sequences'),
(2, 'Sci-Fi', 'Futuristic and scientific fiction content'),
(3, 'Adventure', 'Exciting journeys and explorations'),
(4, 'Comedy', 'Funny and entertaining content'),
(5, 'Drama', 'Emotional and character-driven stories'),
(6, 'Fantasy', 'Magical and imaginative worlds'),
(7, 'Horror', 'Scary and suspenseful content'),
(8, 'Romance', 'Love stories and relationships'),
(9, 'Documentary', 'Real-life stories and facts'),
(10, 'Animation', 'Animated content for all ages');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--
-- Create a new table with the same structure but without foreign key constraints
CREATE TABLE `content_new` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('movie','tv_show','kids') NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `release_year` year(4) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `content_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `featured` tinyint(1) DEFAULT 0,
  `tags` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert all the data from the old table
INSERT INTO `content_new` (`id`, `title`, `description`, `type`, `category_id`, `release_year`, `duration`, `rating`, `poster_image`, `thumbnail`, `trailer_url`, `content_url`, `status`, `featured`, `tags`, `created_at`) VALUES
(36, 'Saiyaara', 'When a talented musician and a passionate writer fall for each other, burgeoning success and a devastating medical diagnosis threaten to pull them apart.', 'movie', NULL, '2025', '125', 7.6, NULL, '../assets/uploads/thumbnails/thumbnail_1761951980_690540eca15a5.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:06:20'),
(37, 'Laila Majnu', 'Two lovers are forced apart because of their family rivalry. Years later, their intense feelings start to consume them in unexpected ways.', 'movie', NULL, '2025', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952049_690541315e26e.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:07:29'),
(38, 'Hi Papa (Hindi)', 'A 6-year-old with cystic fibrosis recruits the help of an enigmatic new friend to convince her single dad to tell her about her mother.', 'movie', NULL, '2024', '121', 8.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952119_69054177210d1.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:08:39'),
(39, 'Mahavatar Narsimha', 'Believing himself to be invincible, a demon vows revenge on Lord Vishnu for his brother''s death, but his devout son stands in the way.', 'movie', NULL, '2024', '155', 5.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952176_690541b044af2.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:09:36'),
(40, 'RRR (Hindi)', 'A fearless warrior on a perilous mission comes face to face with a steely cop serving British forces in this epic saga set in pre-independent India.', 'movie', NULL, '2024', '120', 9.1, NULL, '../assets/uploads/thumbnails/thumbnail_1761952231_690541e778440.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:10:31'),
(41, 'Jawan', 'A prison warden recruits inmates to commit outrageous crimes that shed light on corruption and injustice — and that lead him to an unexpected reunion.', 'movie', NULL, '2023', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952279_6905421741980.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:11:19'),
(42, 'Radhe Shyam (Hindi)', 'Convinced he isn''t destined for love, a renowned palmist must question everything he believes when he falls for a doctor with an uncertain future.', 'movie', NULL, '2022', '122', 6.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952404_69054294bdac6.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:13:24'),
(43, 'OG', 'Trained to fight in Japan, a man''s peaceful life in exile abruptly ends when trouble pulls him back to Mumbai to help those he swore to protect.', 'movie', NULL, '2025', '155', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952527_6905430facbe7.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:15:27'),
(44, 'Animal', 'The fate of a violently contested kingdom hangs on the fraught bond between two friends-turned-foes in this saga of power, bloodshed and betrayal.', 'movie', NULL, '2023', '110', 7.7, NULL, '../assets/uploads/thumbnails/thumbnail_1761952570_6905433adc09c.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:16:10'),
(45, 'Pushpa 2', 'As his smuggling empire grows, a brazen Pushpa longs for power and respect on his vengeful journey, while facing old rivals and new.', 'movie', NULL, '2025', '140', 8.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952612_69054364ea09c.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:16:52'),
(46, 'Devara', 'A mighty warrior takes a stand against the criminal deeds of his village, while his mild-mannered son grows up to walk his own path in this epic drama.', 'movie', NULL, '2024', '130', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952691_690543b3ec41a.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:18:11'),
(47, 'Saripodhaa Sanivaaram', 'An ordinary insurance agent channeling his rage into brutal vigilantism on Saturdays targets a vengeful cop who terrorizes his village.', 'movie', NULL, '2024', '121', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952852_69054454b6de4.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:20:52'),
(48, 'Waltair Veerayya', 'Desperate to nab a wanted criminal on the run, a cop turns to the formidable Waltair Veerayya, a fisherman with a notorious streak, for help.', 'movie', NULL, '2024', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761952919_690544974fb19.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:21:59'),
(49, 'Aadikeshava', 'A young man gets a job at a cosmetics company and falls for its glamorous owner. But soon, unforeseen events begin to unfurl his family''s dark past.', 'movie', NULL, '2023', '120', 7.1, NULL, '../assets/uploads/thumbnails/thumbnail_1761952980_690544d4a37bf.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:23:00'),
(50, 'Kurukshetra', 'Told through unique perspectives over 18 days of war, this animated series depicts the epic battle of Kurukshetra between the Pandavas and the Kauravas.', 'tv_show', NULL, '2025', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953097_69054549a4eec.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:24:57'),
(52, 'Lucifer', 'Bored with being the Lord of Hell, the devil relocates to Los Angeles, where he opens a nightclub and forms a connection with a homicide detective.', 'tv_show', NULL, '2021', '120', 5.2, NULL, '../assets/uploads/thumbnails/thumbnail_1761953220_690545c4e1eed.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:27:00'),
(53, 'The Ba***ds of Bollywood', 'In this high-stakes drama, an ambitious outsider and his friends navigate the chaotic, larger-than-life, yet uncertain world of Bollywood.', 'tv_show', NULL, '2025', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953304_690546186a19d.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:28:24'),
(54, 'Rana Naidu', 'Rana Naidu is the go-to problem solver for the rich and famous. But when his father is released from jail, the one mess he can''t handle may be his own.', 'tv_show', NULL, '2025', '150', 7.9, NULL, '../assets/uploads/thumbnails/thumbnail_1761953350_6905464670ae2.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:29:10'),
(55, 'Kota Factory', 'In a city of coaching centers known to train India''s finest collegiate minds, an earnest but unexceptional student and his friends navigate campus life.', 'tv_show', NULL, '2024', '120', 7.4, NULL, '../assets/uploads/thumbnails/thumbnail_1761953393_69054671304d1.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:29:53'),
(56, 'She', 'An undercover assignment to expose a drug ring becomes a timid Mumbai constable''s road to empowerment as she realizes her dormant sexuality''s potential.\r\nStarring: Aaditi Pohankar, Vijay Varma, and Vishwas Kini', 'tv_show', NULL, '2022', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953432_6905469865829.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:30:32'),
(57, 'Lost in Space', 'After crash-landing on an alien planet, the Robinson family fights against all odds to survive and escape. But they''re surrounded by hidden dangers.', 'tv_show', NULL, '2024', '120', 8.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953499_690546dbac30c.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:31:39'),
(58, 'Hellbound', 'Unearthly beings deliver bloody condemnations, sending individuals to hell and giving rise to a religious group founded on the idea of divine justice.', 'tv_show', NULL, '2024', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953557_69054715a3305.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:32:37'),
(59, 'Tribhuvan Mishra CA Topper', 'A banking crisis forces CA topper Tribhuvan Mishra to take up sex work. Will this dual existence alleviate his troubles — or create entirely new ones?', 'tv_show', NULL, '2024', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953604_69054744f31c5.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:33:25'),
(60, 'The Game', 'A career-driven game developer fights back against misogynistic expectations after she becomes the target of brutal attacks online and in real life.', 'tv_show', NULL, '2025', '120', 7.7, NULL, '../assets/uploads/thumbnails/thumbnail_1761953952_690548a05f323.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:39:12'),
(61, 'Sacred Games', 'A link in their pasts leads an honest cop to a fugitive gang boss, whose cryptic warning spurs the officer on a quest to save Mumbai from cataclysm.', 'movie', NULL, '2019', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761953997_690548cd342d2.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:39:57'),
(62, 'Bard of Blood', 'Years after a disastrous job in Balochistan, a former Indian spy must confront his past when he returns to lead an unsanctioned hostage-rescue mission.', 'tv_show', NULL, '2019', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761954054_6905490609164.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:40:54'),
(63, 'The Tom and Jerry Show', 'Always up to their old shenanigans, Tom and Jerry solve crimes as detectives, bedevil witches Beatie and Hildie, and forever torment each other.', 'kids', NULL, '2019', '90', 7.0, NULL, '../assets/uploads/thumbnails/thumbnail_1761954299_690549fbc0af0.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:44:59'),
(64, 'Kung Fu Panda', 'Legendary warrior Po teams up with an elite English knight on a global quest to rescue magical weapons, restore his reputation — and save the world!', 'kids', NULL, '2023', '90', 7.0, NULL, '../assets/uploads/thumbnails/thumbnail_1761954361_69054a3907ff6.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:46:01'),
(65, 'The Boss Baby', 'Framed for a corporate crime, an adult Ted Templeton turns back into the Boss Baby to live undercover with his brother, Tim, posing as one of his kids.', 'kids', NULL, '2023', '90', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761954530_69054ae235100.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:48:50'),
(66, 'Phantom Pups', 'A young boy and his family move into a haunted home, where he meets three adorable ghost pups and tries to help them turn back into real dogs.', 'kids', NULL, '2022', '90', 5.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761954607_69054b2f98354.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:50:07'),
(67, 'Thelma the Unicorn', 'A singing pony who dreams of stardom finds instant fame when she transforms into a sparkly unicorn — but becoming a celebrity is one wild ride.', 'kids', NULL, '2024', '90', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761954923_69054c6bcee22.png', NULL, NULL, 'published', 0, NULL, '2025-10-31 23:55:23'),
(68, 'Kalki', 'The future of those in the dystopian city of Kasi is altered when the destined arrival of Lord Vishnu''s final avatar launches a war against darkness.', 'movie', NULL, '2024', '125', 8.7, NULL, '../assets/uploads/thumbnails/thumbnail_1761956502_690552968841d.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:21:42'),
(69, 'Morbius', 'A biochemist with a rare blood disease in search of a cure injects himself with a dangerous serum that gives him super strength and a thirst for blood.', 'tv_show', NULL, '2022', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761956661_690553351c345.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:24:21'),
(70, 'Passengers', 'After waking from his hibernation decades ahead of schedule, a lonely guy on an interplanetary journey faces an ethical dilemma over another passenger.', 'movie', NULL, '2021', '153', 7.9, NULL, '../assets/uploads/thumbnails/thumbnail_1761956811_690553cb799ae.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:26:51'),
(71, 'Uglies', 'In a futuristic dystopia with enforced beauty standards, a teen awaiting mandatory cosmetic surgery embarks on a journey to find her missing friend.', 'tv_show', NULL, '2024', '125', 7.1, NULL, '../assets/uploads/thumbnails/thumbnail_1761956954_6905545a56007.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:29:14'),
(72, 'Red Notice', 'An FBI profiler pursuing the world''s most wanted art thief becomes his reluctant partner in crime to catch an elusive crook who''s always one step ahead.', 'tv_show', NULL, '2021', '120', 7.5, NULL, '../assets/uploads/thumbnails/thumbnail_1761957005_6905548d5664b.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:30:05'),
(73, 'The Adam Project', 'After accidentally crash-landing in 2022, time-traveling fighter pilot Adam Reed teams up with his 12-year-old self on a mission to save the future.', 'tv_show', NULL, '2022', '141', 8.8, NULL, '../assets/uploads/thumbnails/thumbnail_1761957073_690554d156876.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:31:13'),
(74, 'Atlas', 'A brilliant counterterrorism analyst with a deep distrust of AI discovers it might be her only hope when a mission to capture a renegade robot goes awry.', 'tv_show', NULL, '2024', '140', 8.7, NULL, '../assets/uploads/thumbnails/thumbnail_1761957242_6905557ad5332.png', NULL, NULL, 'published', 0, NULL, '2025-11-01 00:34:02');

-- Set up primary key and auto increment
ALTER TABLE `content_new`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `content_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
-- --------------------------------------------------------


--
-- Table structure for table `content_with_videos`
--


-- Create a new table with video URLs pre-populated
CREATE TABLE `content_with_videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('movie','tv_show','kids') NOT NULL,
  `category_id` int(11) DEFAULT 1,
  `release_year` year(4) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `content_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `featured` tinyint(1) DEFAULT 0,
  `tags` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert data with working video URLs
INSERT INTO `content_with_videos` (`id`, `title`, `description`, `type`, `category_id`, `release_year`, `duration`, `rating`, `thumbnail`, `content_url`, `featured`) VALUES
(36, 'Saiyaara', 'When a talented musician and a passionate writer fall for each other, burgeoning success and a devastating medical diagnosis threaten to pull them apart.', 'movie', 4, '2025', '125', 7.6, '../assets/uploads/thumbnails/thumbnail_1761951980_690540eca15a5.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 1),
(37, 'Laila Majnu', 'Two lovers are forced apart because of their family rivalry. Years later, their intense feelings start to consume them in unexpected ways.', 'movie', 4, '2025', '120', 7.5, '../assets/uploads/thumbnails/thumbnail_1761952049_690541315e26e.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 0),
(38, 'Hi Papa (Hindi)', 'A 6-year-old with cystic fibrosis recruits the help of an enigmatic new friend to convince her single dad to tell her about her mother.', 'movie', 2, '2024', '121', 8.5, '../assets/uploads/thumbnails/thumbnail_1761952119_69054177210d1.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 0),
(39, 'Mahavatar Narsimha', 'Believing himself to be invincible, a demon vows revenge on Lord Vishnu for his brother''s death, but his devout son stands in the way.', 'movie', 8, '2024', '155', 5.5, '../assets/uploads/thumbnails/thumbnail_1761952176_690541b044af2.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4', 0),
(40, 'RRR (Hindi)', 'A fearless warrior on a perilous mission comes face to face with a steely cop serving British forces in this epic saga set in pre-independent India.', 'movie', 1, '2024', '120', 9.1, '../assets/uploads/thumbnails/thumbnail_1761952231_690541e778440.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 1),
(41, 'Jawan', 'A prison warden recruits inmates to commit outrageous crimes that shed light on corruption and injustice — and that lead him to an unexpected reunion.', 'movie', 1, '2023', '120', 7.5, '../assets/uploads/thumbnails/thumbnail_1761952279_6905421741980.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 0),
(42, 'Radhe Shyam (Hindi)', 'Convinced he isn''t destined for love, a renowned palmist must question everything he believes when he falls for a doctor with an uncertain future.', 'movie', 4, '2022', '122', 6.5, '../assets/uploads/thumbnails/thumbnail_1761952404_69054294bdac6.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 0),
(43, 'OG', 'Trained to fight in Japan, a man''s peaceful life in exile abruptly ends when trouble pulls him back to Mumbai to help those he swore to protect.', 'movie', 1, '2025', '155', 7.5, '../assets/uploads/thumbnails/thumbnail_1761952527_6905430facbe7.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4', 0),
(44, 'Animal', 'The fate of a violently contested kingdom hangs on the fraught bond between two friends-turned-foes in this saga of power, bloodshed and betrayal.', 'movie', 5, '2023', '110', 7.7, '../assets/uploads/thumbnails/thumbnail_1761952570_6905433adc09c.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 1),
(45, 'Pushpa 2', 'As his smuggling empire grows, a brazen Pushpa longs for power and respect on his vengeful journey, while facing old rivals and new.', 'movie', 1, '2025', '140', 8.5, '../assets/uploads/thumbnails/thumbnail_1761952612_69054364ea09c.png', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4', 1);

-- Add primary key
ALTER TABLE `content_with_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `content_with_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



--
-- Table structure for table `episodes`
--

CREATE TABLE `episodes` (
  `id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `episode_number` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `episode_url` varchar(500) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media_files`
--

CREATE TABLE `media_files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `filetype` varchar(100) NOT NULL,
  `filesize` int(11) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subscription_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `season_number` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `episode_count` int(11) DEFAULT 0,
  `release_date` date DEFAULT NULL,
  `poster_image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('active','cancelled','expired') DEFAULT 'active',
  `payment_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) NOT NULL,
  `max_screens` int(11) DEFAULT 1,
  `video_quality` enum('SD','HD','4K') DEFAULT 'SD',
  `ad_free` tinyint(1) DEFAULT 0,
  `download_limit` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `role` enum('user','admin','superadmin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_picture`, `first_name`, `last_name`, `role`, `created_at`, `last_login`, `status`) VALUES
(11, 'pabitraswain', 'pabitraswain@gmail.com', '$2y$10$vPh4DAgpMgjEgbe5jG24xOkRhP/mz/1ipiDfS7Tsz0vtXsoglasKa', 'assets/uploads/profiles/profile_11_1761950232.jpg', NULL, NULL, 'user', '2025-10-31 22:35:46', '2025-11-01 20:31:26', 'active'),
(12, 'pstg093979', 'pstg093979@gmail.com', '$2y$10$bjVm5K3RJnFSj2k.asia0eKBoFnSLq8BseMrUbqiIg13zT1BZoXNi', 'assets/uploads/profiles/profile_12_1761997626.jpg', NULL, NULL, 'user', '2025-11-01 10:31:14', '2025-11-01 10:31:18', 'active'),
(13, 'Nirmal', 'nirmalkusahu@gmail.com', '$2y$10$ovmaYjq8.e3J30u6x2WgIutEboEf/N7wJggHFM2PdgoDZhSsHJJhC', NULL, NULL, NULL, 'user', '2025-11-01 10:34:44', '2025-11-01 10:34:51', 'active'),
(14, 'Pratikn', 'pratikkumarnayak4@gmail.com', '$2y$10$WAbnc21daukxUk8NPb9Jz.dgGriktO.Kro.BfFsf57PEt/LC2I32m', NULL, NULL, NULL, 'user', '2025-11-01 10:36:37', '2025-11-01 10:36:51', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_methods`
--

CREATE TABLE `user_payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_type` enum('credit_card','paypal') NOT NULL,
  `card_last_four` varchar(4) DEFAULT NULL,
  `card_brand` varchar(20) DEFAULT NULL,
  `expiry_month` int(11) DEFAULT NULL,
  `expiry_year` int(11) DEFAULT NULL,
  `paypal_email` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `status` enum('active','canceled','expired','pending') DEFAULT 'pending',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `viewing_history`
--

CREATE TABLE `viewing_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `watched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `duration_watched` int(11) DEFAULT 0,
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

CREATE TABLE `watchlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watch_history`
--

CREATE TABLE `watch_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content_id` int(11) NOT NULL,
  `last_watched` timestamp NOT NULL DEFAULT current_timestamp(),
  `progress` int(11) DEFAULT 0 COMMENT 'Watch progress in seconds'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `episodes`
--
ALTER TABLE `episodes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `season_id` (`season_id`);

--
-- Indexes for table `media_files`
--
ALTER TABLE `media_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `subscription_id` (`subscription_id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_favorite` (`user_id`,`content_id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `viewing_history`
--
ALTER TABLE `viewing_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_watchlist` (`user_id`,`content_id`),
  ADD KEY `content_id` (`content_id`);

--
-- Indexes for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_content` (`user_id`,`content_id`),
  ADD KEY `content_id` (`content_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `episodes`
--
ALTER TABLE `episodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `media_files`
--
ALTER TABLE `media_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `viewing_history`
--
ALTER TABLE `viewing_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `watch_history`
--
ALTER TABLE `watch_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `episodes`
--
ALTER TABLE `episodes`
  ADD CONSTRAINT `episodes_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `media_files`
--
ALTER TABLE `media_files`
  ADD CONSTRAINT `media_files_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`subscription_id`) REFERENCES `user_subscriptions` (`id`);

--
-- Constraints for table `seasons`
--
ALTER TABLE `seasons`
  ADD CONSTRAINT `seasons_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD CONSTRAINT `user_payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD CONSTRAINT `user_subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_subscriptions_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `watch_history`
--
ALTER TABLE `watch_history`
  ADD CONSTRAINT `watch_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `watch_history_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
