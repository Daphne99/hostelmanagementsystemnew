SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cute_brains`
--
CREATE DATABASE IF NOT EXISTS `cute_brains` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `cute_brains`;

DROP TABLE IF EXISTS `academic_year`;
CREATE TABLE `academic_year` (
  `id` int(10) UNSIGNED NOT NULL,
  `yearTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `isDefault` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `assignments`;
CREATE TABLE `assignments` (
  `id` int(250) NOT NULL,
  `classId` text NOT NULL,
  `sectionId` text NOT NULL,
  `subjectId` int(250) NOT NULL,
  `teacherId` int(250) NOT NULL,
  `AssignTitle` varchar(250) NOT NULL,
  `AssignDescription` text,
  `AssignFile` varchar(250) NOT NULL,
  `AssignDeadLine` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `assignments_answers`;
CREATE TABLE `assignments_answers` (
  `id` int(250) NOT NULL,
  `assignmentId` int(250) NOT NULL,
  `userId` int(250) NOT NULL,
  `fileName` varchar(250) NOT NULL,
  `userNotes` text NOT NULL,
  `userTime` int(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` int(250) NOT NULL,
  `classId` int(250) NOT NULL,
  `subjectId` int(250) NOT NULL,
  `date` varchar(250) NOT NULL,
  `studentId` int(250) NOT NULL,
  `status` int(1) NOT NULL,
  `in_time` varchar(20) NOT NULL,
  `out_time` varchar(20) NOT NULL,
  `attNotes` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `state_id` mediumint(8) UNSIGNED NOT NULL,
  `state_code` varchar(255) NOT NULL,
  `country_id` mediumint(8) UNSIGNED NOT NULL,
  `country_code` char(2) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '2013-12-31 17:31:01',
  `updated_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `flag` tinyint(1) NOT NULL DEFAULT '1',
  `wikiDataId` varchar(255) DEFAULT NULL COMMENT 'Rapid API GeoDB Cities'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes` (
  `id` int(250) NOT NULL,
  `className` varchar(250) NOT NULL,
  `classTeacher` varchar(250) DEFAULT NULL,
  `classAcademicYear` int(250) NOT NULL,
  `classSubjects` text,
  `dormitoryId` int(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `class_schedule`;
CREATE TABLE `class_schedule` (
  `id` int(250) NOT NULL,
  `classId` int(250) NOT NULL,
  `sectionId` int(250) NOT NULL,
  `subjectId` int(250) NOT NULL,
  `dayOfWeek` varchar(10) NOT NULL,
  `teacherId` int(250) NOT NULL,
  `startTime` varchar(20) NOT NULL,
  `endTime` varchar(20) NOT NULL,
  `is_break` enum('yes','no') DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `convert_files_api_secret_keys`;
CREATE TABLE `convert_files_api_secret_keys` (
  `id` int(11) NOT NULL,
  `secret_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `iso3` char(3) DEFAULT NULL,
  `iso2` char(2) DEFAULT NULL,
  `phonecode` varchar(255) DEFAULT NULL,
  `capital` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `flag` tinyint(1) NOT NULL DEFAULT '1',
  `wikiDataId` varchar(255) DEFAULT NULL COMMENT 'Rapid API GeoDB Cities'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(250) NOT NULL,
  `eventTitle` varchar(250) NOT NULL,
  `eventDescription` longtext,
  `eventCreator` int(250) NOT NULL,
  `eventFor` varchar(10) DEFAULT NULL,
  `participants` longtext,
  `enentPlace` varchar(250) DEFAULT NULL,
  `eventImage` text NOT NULL,
  `fe_active` int(1) NOT NULL,
  `eventDate` varchar(250) NOT NULL,
  `eventEndDate` varchar(250) DEFAULT NULL,
  `eventSeenMembers` longtext,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `exams_list`;
CREATE TABLE `exams_list` (
  `id` int(250) NOT NULL,
  `examTitle` varchar(250) NOT NULL,
  `examDescription` text,
  `examDate` varchar(250) NOT NULL,
  `examEndDate` int(250) NOT NULL,
  `main_pass_marks` varchar(50) DEFAULT NULL,
  `main_max_marks` varchar(50) DEFAULT NULL,
  `examClasses` text NOT NULL,
  `examMarksheetColumns` text NOT NULL,
  `examAcYear` int(250) NOT NULL,
  `examSchedule` text NOT NULL,
  `school_term_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `examterms`;
CREATE TABLE `examterms` (
  `id` int(10) UNSIGNED NOT NULL,
  `term` int(12) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `unitestMarks` int(250) NOT NULL,
  `examMarks` int(250) NOT NULL,
  `examId` int(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `exam_marks`;
CREATE TABLE `exam_marks` (
  `id` int(250) NOT NULL,
  `examId` int(250) NOT NULL,
  `classId` int(250) NOT NULL,
  `subjectId` varchar(250) DEFAULT NULL,
  `studentId` int(250) NOT NULL,
  `examMark` text NOT NULL,
  `totalMarks` varchar(250) NOT NULL,
  `markComments` text,
  `school_term_id` int(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` int(11) NOT NULL,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `grade_levels`;
CREATE TABLE `grade_levels` (
  `id` int(10) UNSIGNED NOT NULL,
  `gradeName` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `gradeDescription` text COLLATE utf8_unicode_ci,
  `gradePoints` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `gradeFrom` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `gradeTo` varchar(250) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `homeworks`;
CREATE TABLE `homeworks` (
  `id` int(250) NOT NULL,
  `classId` text NOT NULL,
  `sectionId` text NOT NULL,
  `subjectId` int(250) NOT NULL,
  `teacherId` int(250) NOT NULL,
  `homeworkTitle` varchar(250) NOT NULL,
  `homeworkDescription` longtext,
  `homeworkFile` varchar(250) NOT NULL,
  `homeworkDate` varchar(250) NOT NULL,
  `homeworkSubmissionDate` int(250) NOT NULL,
  `homeworkEvaluationDate` int(250) NOT NULL,
  `studentsCompleted` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `hostel`;
CREATE TABLE `hostel` (
  `id` int(10) UNSIGNED NOT NULL,
  `hostelTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `hostelType` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `hostelAddress` text COLLATE utf8_unicode_ci NOT NULL,
  `hostelManager` text COLLATE utf8_unicode_ci NOT NULL,
  `managerPhoto` text COLLATE utf8_unicode_ci NOT NULL,
  `managerContact` text COLLATE utf8_unicode_ci NOT NULL,
  `hostelNotes` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `hr_allowance`;
CREATE TABLE `hr_allowance` (
  `allowance_id` int(10) UNSIGNED NOT NULL,
  `allowance_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowance_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_of_basic` int(11) NOT NULL,
  `limit_per_month` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_bonus_setting`;
CREATE TABLE `hr_bonus_setting` (
  `bonus_setting_id` int(10) UNSIGNED NOT NULL,
  `festival_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_of_bonus` int(11) NOT NULL,
  `bonus_type` enum('Gross','Basic') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_branch`;
CREATE TABLE `hr_branch` (
  `branch_id` int(10) UNSIGNED NOT NULL,
  `branch_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_company_address_settings`;
CREATE TABLE `hr_company_address_settings` (
  `company_address_setting_id` int(10) UNSIGNED NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_deduction`;
CREATE TABLE `hr_deduction` (
  `deduction_id` int(10) UNSIGNED NOT NULL,
  `deduction_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deduction_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_of_basic` int(11) NOT NULL,
  `limit_per_month` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_department`;
CREATE TABLE `hr_department` (
  `department_id` int(10) UNSIGNED NOT NULL,
  `department_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_designation`;
CREATE TABLE `hr_designation` (
  `designation_id` int(10) UNSIGNED NOT NULL,
  `designation_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_earn_leave_rule`;
CREATE TABLE `hr_earn_leave_rule` (
  `earn_leave_rule_id` int(10) UNSIGNED NOT NULL,
  `for_month` int(11) NOT NULL,
  `day_of_earn_leave` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee`;
CREATE TABLE `hr_employee` (
  `employee_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `finger_id` int(11) NOT NULL,
  `department_id` int(10) UNSIGNED NOT NULL,
  `designation_id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `supervisor_id` int(11) DEFAULT NULL,
  `work_shift_id` int(10) UNSIGNED NOT NULL,
  `pay_grade_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `hourly_salaries_id` int(10) UNSIGNED DEFAULT '0',
  `email` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `date_of_joining` date NOT NULL,
  `date_of_leaving` date DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `religion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `emergency_contacts` text COLLATE utf8mb4_unicode_ci,
  `phone` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `permanent_status` tinyint(4) NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_attendance`;
CREATE TABLE `hr_employee_attendance` (
  `employee_attendance_id` int(10) UNSIGNED NOT NULL,
  `finger_print_id` int(11) NOT NULL,
  `in_out_time` datetime NOT NULL,
  `check_type` text COLLATE utf8mb4_unicode_ci,
  `verify_code` bigint(20) DEFAULT NULL,
  `sensor_id` text COLLATE utf8mb4_unicode_ci,
  `Memoinfo` text COLLATE utf8mb4_unicode_ci,
  `WorkCode` text COLLATE utf8mb4_unicode_ci,
  `sn` text COLLATE utf8mb4_unicode_ci,
  `UserExtFmt` int(11) DEFAULT NULL,
  `mechine_sl` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_attendance_approve`;
CREATE TABLE `hr_employee_attendance_approve` (
  `employee_attendance_approve_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `finger_print_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `in_time` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `out_time` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_hour` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approve_working_hour` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_award`;
CREATE TABLE `hr_employee_award` (
  `employee_award_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `award_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gift_item` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `month` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_bonus`;
CREATE TABLE `hr_employee_bonus` (
  `employee_bonus_id` int(10) UNSIGNED NOT NULL,
  `bonus_setting_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_salary` int(11) NOT NULL,
  `basic_salary` int(11) NOT NULL,
  `bonus_amount` int(11) NOT NULL,
  `tax` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_education_qualification`;
CREATE TABLE `hr_employee_education_qualification` (
  `employee_education_qualification_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `institute` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `board_university` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cgpa` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `passing_year` year(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_experience`;
CREATE TABLE `hr_employee_experience` (
  `employee_experience_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `organization_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `skill` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsibility` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_performance`;
CREATE TABLE `hr_employee_performance` (
  `employee_performance_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_employee_performance_details`;
CREATE TABLE `hr_employee_performance_details` (
  `employee_performance_details_id` int(10) UNSIGNED NOT NULL,
  `employee_performance_id` int(10) UNSIGNED NOT NULL,
  `performance_criteria_id` int(10) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_holiday`;
CREATE TABLE `hr_holiday` (
  `holiday_id` int(10) UNSIGNED NOT NULL,
  `holiday_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_holiday_details`;
CREATE TABLE `hr_holiday_details` (
  `holiday_details_id` int(10) UNSIGNED NOT NULL,
  `holiday_id` int(10) UNSIGNED NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_hourly_salaries`;
CREATE TABLE `hr_hourly_salaries` (
  `hourly_salaries_id` int(10) UNSIGNED NOT NULL,
  `hourly_grade` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hourly_rate` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_interview`;
CREATE TABLE `hr_interview` (
  `interview_id` int(10) UNSIGNED NOT NULL,
  `job_applicant_id` int(10) UNSIGNED NOT NULL,
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `interview_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_job`;
CREATE TABLE `hr_job` (
  `job_id` int(10) UNSIGNED NOT NULL,
  `job_title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_end_date` date NOT NULL,
  `publish_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_job_applicant`;
CREATE TABLE `hr_job_applicant` (
  `job_applicant_id` int(10) UNSIGNED NOT NULL,
  `job_id` int(10) UNSIGNED NOT NULL,
  `applicant_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applicant_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` int(11) NOT NULL,
  `cover_letter` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attached_resume` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_date` date NOT NULL,
  `status` tinyint(4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_leave_application`;
CREATE TABLE `hr_leave_application` (
  `leave_application_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `application_from_date` date NOT NULL,
  `application_to_date` date NOT NULL,
  `application_date` date NOT NULL,
  `number_of_day` int(11) NOT NULL,
  `approve_date` date DEFAULT NULL,
  `reject_date` date DEFAULT NULL,
  `approve_by` int(11) DEFAULT NULL,
  `reject_by` int(11) DEFAULT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'status(1,2,3) = Pending,Approve,Reject',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_leave_type`;
CREATE TABLE `hr_leave_type` (
  `leave_type_id` int(10) UNSIGNED NOT NULL,
  `leave_type_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_of_day` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_menus`;
CREATE TABLE `hr_menus` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `action` int(11) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_menu_permission`;
CREATE TABLE `hr_menu_permission` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_modules`;
CREATE TABLE `hr_modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_class` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_notice`;
CREATE TABLE `hr_notice` (
  `notice_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `publish_date` date NOT NULL,
  `attach_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_pay_grade`;
CREATE TABLE `hr_pay_grade` (
  `pay_grade_id` int(10) UNSIGNED NOT NULL,
  `pay_grade_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_salary` int(11) NOT NULL,
  `percentage_of_basic` int(11) NOT NULL,
  `basic_salary` int(11) NOT NULL,
  `overtime_rate` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_pay_grade_to_allowance`;
CREATE TABLE `hr_pay_grade_to_allowance` (
  `pay_grade_to_allowance_id` int(10) UNSIGNED NOT NULL,
  `pay_grade_id` int(11) NOT NULL,
  `allowance_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_pay_grade_to_deduction`;
CREATE TABLE `hr_pay_grade_to_deduction` (
  `pay_grade_to_deduction_id` int(10) UNSIGNED NOT NULL,
  `pay_grade_id` int(11) NOT NULL,
  `deduction_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_performance_category`;
CREATE TABLE `hr_performance_category` (
  `performance_category_id` int(10) UNSIGNED NOT NULL,
  `performance_category_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_performance_criteria`;
CREATE TABLE `hr_performance_criteria` (
  `performance_criteria_id` int(10) UNSIGNED NOT NULL,
  `performance_category_id` int(10) UNSIGNED NOT NULL,
  `performance_criteria_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_print_head_settings`;
CREATE TABLE `hr_print_head_settings` (
  `print_head_setting_id` int(10) UNSIGNED NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_promotion`;
CREATE TABLE `hr_promotion` (
  `promotion_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `promotion_by` int(10) UNSIGNED NOT NULL,
  `current_department` int(10) UNSIGNED NOT NULL,
  `current_designation` int(10) UNSIGNED NOT NULL,
  `current_pay_grade` int(11) NOT NULL,
  `current_salary` int(11) NOT NULL,
  `promoted_pay_grade` int(10) UNSIGNED NOT NULL,
  `new_salary` int(11) NOT NULL,
  `promoted_department` int(10) UNSIGNED NOT NULL,
  `promoted_designation` int(10) UNSIGNED NOT NULL,
  `promotion_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_role`;
CREATE TABLE `hr_role` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_salary_deduction_for_late_attendance`;
CREATE TABLE `hr_salary_deduction_for_late_attendance` (
  `salary_deduction_for_late_attendance_id` int(10) UNSIGNED NOT NULL,
  `for_days` int(11) NOT NULL,
  `day_of_salary_deduction` int(11) NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_salary_details`;
CREATE TABLE `hr_salary_details` (
  `salary_details_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month_of_salary` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic_salary` int(11) NOT NULL DEFAULT '0',
  `total_allowance` int(11) NOT NULL DEFAULT '0',
  `total_deduction` int(11) NOT NULL DEFAULT '0',
  `total_late` int(11) NOT NULL DEFAULT '0',
  `total_late_amount` int(11) NOT NULL DEFAULT '0',
  `total_absence` int(11) NOT NULL DEFAULT '0',
  `total_absence_amount` int(11) NOT NULL DEFAULT '0',
  `overtime_rate` int(11) NOT NULL DEFAULT '0',
  `total_over_time_hour` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `total_overtime_amount` int(11) NOT NULL DEFAULT '0',
  `hourly_rate` int(11) NOT NULL DEFAULT '0',
  `total_present` int(11) NOT NULL DEFAULT '0',
  `total_leave` int(11) NOT NULL DEFAULT '0',
  `total_working_days` int(11) NOT NULL DEFAULT '0',
  `tax` int(11) NOT NULL DEFAULT '0',
  `gross_salary` int(11) NOT NULL DEFAULT '0',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `per_day_salary` int(11) NOT NULL DEFAULT '0',
  `taxable_salary` int(11) NOT NULL DEFAULT '0',
  `net_salary` int(11) NOT NULL DEFAULT '0',
  `working_hour` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_salary_details_to_allowance`;
CREATE TABLE `hr_salary_details_to_allowance` (
  `salary_details_to_allowance_id` int(10) UNSIGNED NOT NULL,
  `salary_details_id` int(11) NOT NULL,
  `allowance_id` int(11) NOT NULL,
  `amount_of_allowance` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_salary_details_to_deduction`;
CREATE TABLE `hr_salary_details_to_deduction` (
  `salary_details_to_deduction_id` int(10) UNSIGNED NOT NULL,
  `salary_details_id` int(11) NOT NULL,
  `deduction_id` int(11) NOT NULL,
  `amount_of_deduction` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_salary_details_to_leave`;
CREATE TABLE `hr_salary_details_to_leave` (
  `salary_details_to_leave_id` int(10) UNSIGNED NOT NULL,
  `salary_details_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `num_of_day` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_tax_rule`;
CREATE TABLE `hr_tax_rule` (
  `tax_rule_id` int(10) UNSIGNED NOT NULL,
  `amount` int(11) NOT NULL,
  `percentage_of_tax` int(11) NOT NULL,
  `amount_of_tax` int(11) NOT NULL,
  `gender` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_termination`;
CREATE TABLE `hr_termination` (
  `termination_id` int(10) UNSIGNED NOT NULL,
  `terminate_to` int(10) UNSIGNED NOT NULL,
  `terminate_by` int(10) UNSIGNED NOT NULL,
  `termination_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notice_date` date NOT NULL,
  `termination_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `hr_training_info`;
CREATE TABLE `hr_training_info` (
  `training_info_id` int(10) UNSIGNED NOT NULL,
  `training_type_id` int(10) UNSIGNED NOT NULL,
  `employee_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `hr_training_type`;
CREATE TABLE `hr_training_type` (
  `training_type_id` int(10) UNSIGNED NOT NULL,
  `training_type_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `hr_user`;
CREATE TABLE `hr_user` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `user_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_view_employee_in_out_data`;
CREATE TABLE `hr_view_employee_in_out_data` (
`employee_attendance_id` int(10) unsigned
,`finger_print_id` int(11)
,`in_time` datetime
,`out_time` varchar(19)
,`date` varchar(10)
,`working_time` time
);

DROP TABLE IF EXISTS `hr_warning`;
CREATE TABLE `hr_warning` (
  `warning_id` int(10) UNSIGNED NOT NULL,
  `warning_to` int(10) UNSIGNED NOT NULL,
  `warning_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warning_by` int(10) UNSIGNED NOT NULL,
  `warning_date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_weekly_holiday`;
CREATE TABLE `hr_weekly_holiday` (
  `week_holiday_id` int(10) UNSIGNED NOT NULL,
  `day_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `hr_work_shift`;
CREATE TABLE `hr_work_shift` (
  `work_shift_id` int(10) UNSIGNED NOT NULL,
  `shift_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_count_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') CHARACTER SET utf8 DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `languageTitle` text COLLATE utf8_unicode_ci NOT NULL,
  `languageUniversal` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `languagePhrases` text COLLATE utf8_unicode_ci NOT NULL,
  `isRTL` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `marksheet`;
CREATE TABLE `marksheet` (
  `id` int(250) NOT NULL,
  `sheetName` varchar(750) DEFAULT NULL,
  `class_id` int(250) NOT NULL,
  `section_id` int(250) NOT NULL,
  `payload` longtext,
  `status` enum('created','processing','completed','failed') NOT NULL,
  `created_by` int(250) NOT NULL,
  `comments` text,
  `is_speical` enum('yes','no') DEFAULT 'no',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `marksheet_students`;
CREATE TABLE `marksheet_students` (
  `id` int(250) NOT NULL,
  `file_name` varchar(750) DEFAULT NULL,
  `class_id` int(250) NOT NULL,
  `section_id` int(250) NOT NULL,
  `student_id` int(250) NOT NULL,
  `marksheet_id` int(250) NOT NULL,
  `payload` longtext,
  `rank` int(25) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `media_albums`;
CREATE TABLE `media_albums` (
  `id` int(10) UNSIGNED NOT NULL,
  `albumTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `albumDescription` text COLLATE utf8_unicode_ci NOT NULL,
  `albumImage` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `albumParent` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `media_items`;
CREATE TABLE `media_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `albumId` int(11) NOT NULL DEFAULT '0',
  `mediaType` int(11) NOT NULL,
  `mediaURL` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `mediaURLThumb` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mediaTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `mediaDescription` text COLLATE utf8_unicode_ci NOT NULL,
  `mediaDate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` int(250) NOT NULL,
  `messageId` int(250) NOT NULL,
  `userId` int(250) NOT NULL,
  `fromId` int(250) NOT NULL,
  `toId` int(250) NOT NULL,
  `messageText` text NOT NULL,
  `dateSent` varchar(250) NOT NULL,
  `enable_reply` tinyint(1) NOT NULL DEFAULT '1',
  `is_grouped` enum('yes','no') DEFAULT 'no',
  `threadId` int(250) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `messages_list`;
CREATE TABLE `messages_list` (
  `id` int(250) NOT NULL,
  `userId` int(250) NOT NULL,
  `toId` int(250) NOT NULL,
  `lastMessage` varchar(250) NOT NULL,
  `lastMessageDate` varchar(250) NOT NULL,
  `messageStatus` int(1) NOT NULL,
  `enable_reply` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `messages_list_grouped`;
CREATE TABLE `messages_list_grouped` (
  `id` int(250) NOT NULL,
  `userId` int(250) NOT NULL,
  `recipients_ids` text,
  `lastMessage` varchar(250) DEFAULT NULL,
  `lastMessageDate` varchar(250) DEFAULT NULL,
  `messageStatus` tinyint(1) NOT NULL,
  `enable_reply` tinyint(1) NOT NULL DEFAULT '1',
  `messages_ids` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `mm_uploads`;
CREATE TABLE `mm_uploads` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_orig_name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `file_uploaded_name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_mime` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `file_uploaded_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `mob_notifications`;
CREATE TABLE `mob_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `notifTo` text COLLATE utf8_unicode_ci NOT NULL,
  `notifToIds` text COLLATE utf8_unicode_ci NOT NULL,
  `notifData` text COLLATE utf8_unicode_ci NOT NULL,
  `notifDate` int(11) NOT NULL,
  `notifSender` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `newsboard`;
CREATE TABLE `newsboard` (
  `id` int(10) UNSIGNED NOT NULL,
  `newsTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `newsText` text COLLATE utf8_unicode_ci NOT NULL,
  `newsCreator` int(250) NOT NULL,
  `newsFor` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `participants` longtext CHARACTER SET utf8,
  `newsDate` int(11) NOT NULL,
  `newsImage` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `fe_active` int(11) NOT NULL,
  `creationDate` int(11) NOT NULL,
  `newsSeenMembers` longtext CHARACTER SET utf8,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `notifications_mob_history`;
CREATE TABLE `notifications_mob_history` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(350) DEFAULT NULL,
  `user_id` varchar(255) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(255) DEFAULT NULL,
  `payload_id` varchar(255) DEFAULT NULL,
  `is_seen` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `online_exams`;
CREATE TABLE `online_exams` (
  `id` int(10) UNSIGNED NOT NULL,
  `examTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `examDescription` text COLLATE utf8_unicode_ci,
  `examClass` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `sectionId` text COLLATE utf8_unicode_ci NOT NULL,
  `examTeacher` int(11) NOT NULL,
  `examSubject` int(11) NOT NULL,
  `examDate` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `exAcYear` int(11) NOT NULL,
  `ExamEndDate` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `examTimeMinutes` int(11) NOT NULL DEFAULT '0',
  `examDegreeSuccess` int(11) NOT NULL,
  `ExamShowGrade` int(11) NOT NULL DEFAULT '0',
  `random_questions` int(11) NOT NULL,
  `examQuestion` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `online_exams_grades`;
CREATE TABLE `online_exams_grades` (
  `id` int(10) UNSIGNED NOT NULL,
  `examId` int(11) NOT NULL,
  `studentId` int(11) NOT NULL,
  `examQuestionsAnswers` text COLLATE utf8_unicode_ci,
  `examGrade` int(11) DEFAULT NULL,
  `examDate` varchar(250) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `online_exams_questions`;
CREATE TABLE `online_exams_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_text` text COLLATE utf8_unicode_ci NOT NULL,
  `question_type` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `question_answers` text COLLATE utf8_unicode_ci NOT NULL,
  `question_mark` double NOT NULL,
  `question_image` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `question_subject` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `is_shared` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `paymentTitle` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `paymentDescription` text COLLATE utf8_unicode_ci,
  `paymentStudent` int(11) NOT NULL,
  `paymentRows` text COLLATE utf8_unicode_ci NOT NULL,
  `paymentAmount` double NOT NULL,
  `paymentDiscount` double NOT NULL,
  `paymentDiscounted` double NOT NULL,
  `paidAmount` double NOT NULL DEFAULT '0',
  `paymentStatus` int(11) NOT NULL,
  `paymentDate` int(11) NOT NULL,
  `dueDate` int(11) NOT NULL,
  `dueNotified` int(11) NOT NULL DEFAULT '0',
  `paymentUniqid` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paymentSuccessDetails` text COLLATE utf8_unicode_ci,
  `paidMethod` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paidTime` int(11) DEFAULT NULL,
  `discount_id` int(11) NOT NULL,
  `fine_amount` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `paymentscollection`;
CREATE TABLE `paymentscollection` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoiceId` int(11) NOT NULL,
  `collectionAmount` double NOT NULL,
  `collectionDate` int(11) NOT NULL,
  `collectionMethod` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `collectionNote` text COLLATE utf8_unicode_ci NOT NULL,
  `collectedBy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `payment_multi_invoices`;
CREATE TABLE `payment_multi_invoices` (
  `id` int(11) NOT NULL,
  `invoice_ids` text COLLATE utf8_unicode_ci NOT NULL,
  `payment_amounts` text COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_title` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `role_description` text COLLATE utf8_unicode_ci NOT NULL,
  `def_for` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `role_permissions` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `school_terms`;
CREATE TABLE `school_terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id` int(250) NOT NULL,
  `sectionName` varchar(250) NOT NULL,
  `sectionTitle` varchar(250) NOT NULL,
  `classId` int(250) NOT NULL,
  `classTeacherId` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `fieldName` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `fieldValue` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `shedule_terms`;
CREATE TABLE `shedule_terms` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `allow` int(11) DEFAULT NULL,
  `classId` int(11) DEFAULT NULL,
  `subjectId` int(11) DEFAULT NULL,
  `year` varchar(255) DEFAULT NULL,
  `examId` int(11) DEFAULT NULL,
  `term` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `states`;
CREATE TABLE `states` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_id` mediumint(8) UNSIGNED NOT NULL,
  `country_code` char(2) NOT NULL,
  `fips_code` varchar(255) DEFAULT NULL,
  `iso2` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `flag` tinyint(1) NOT NULL DEFAULT '1',
  `wikiDataId` varchar(255) DEFAULT NULL COMMENT 'Rapid API GeoDB Cities'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

DROP TABLE IF EXISTS `student_academic_years`;
CREATE TABLE `student_academic_years` (
  `id` int(10) UNSIGNED NOT NULL,
  `studentId` int(11) NOT NULL,
  `academicYearId` int(11) NOT NULL,
  `classId` int(11) NOT NULL,
  `sectionId` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `student_categories`;
CREATE TABLE `student_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `cat_title` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `cat_desc` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `student_docs`;
CREATE TABLE `student_docs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_title` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `file_name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `file_notes` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `student_types`;
CREATE TABLE `student_types` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `subject`;
CREATE TABLE `subject` (
  `id` int(250) NOT NULL,
  `subjectTitle` varchar(250) NOT NULL,
  `teacherId` text,
  `passGrade` varchar(250) NOT NULL,
  `finalGrade` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `subject_videos`;
CREATE TABLE `subject_videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `sub_category1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sub_category2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link` text COLLATE utf8_unicode_ci NOT NULL,
  `is_english` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `sub_subjects`;
CREATE TABLE `sub_subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `subjectTitle` varchar(250) NOT NULL,
  `passGrade` varchar(250) NOT NULL,
  `finalGrade` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ta_auth_tokens`;
CREATE TABLE `ta_auth_tokens` (
  `auth_identifier` int(11) NOT NULL,
  `public_key` varchar(96) COLLATE utf8_unicode_ci NOT NULL,
  `private_key` varchar(96) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `transportation`;
CREATE TABLE `transportation` (
  `id` int(250) NOT NULL,
  `transportTitle` varchar(250) NOT NULL,
  `routeDetails` text,
  `vehicles_list` text NOT NULL,
  `transportFare` varchar(250) NOT NULL,
  `routeDistance` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `transport_vehicles`;
CREATE TABLE `transport_vehicles` (
  `id` int(11) NOT NULL,
  `plate_number` varchar(250) NOT NULL,
  `vehicle_color` varchar(250) DEFAULT NULL,
  `vehicle_model` varchar(250) DEFAULT NULL,
  `driver_name` varchar(250) DEFAULT NULL,
  `driver_photo` varchar(250) DEFAULT NULL,
  `driver_license` varchar(250) DEFAULT NULL,
  `driver_contact` text,
  `vehicle_notes` text,
  `vehicle_type` int(11) DEFAULT NULL,
  `assistant_name` varchar(250) DEFAULT NULL,
  `assistant_photo` varchar(250) DEFAULT NULL,
  `assistant_license` varchar(250) DEFAULT NULL,
  `assistant_contact` text,
  `stoppagesList` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(250) NOT NULL,
  `username` varchar(250) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(100) NOT NULL,
  `remember_token` varchar(250) NOT NULL,
  `fullName` varchar(250) NOT NULL,
  `role` varchar(10) NOT NULL,
  `role_perm` int(250) NOT NULL,
  `department` int(250) NOT NULL,
  `designation` int(250) NOT NULL,
  `activated` int(1) NOT NULL DEFAULT '1',
  `studentRollId` varchar(250) DEFAULT NULL,
  `admission_number` varchar(250) NOT NULL,
  `admission_date` int(250) NOT NULL,
  `bioId` varchar(255) DEFAULT NULL,
  `std_category` int(250) NOT NULL,
  `auth_session` text NOT NULL,
  `birthday` int(250) NOT NULL DEFAULT '0',
  `gender` varchar(10) DEFAULT NULL,
  `address` text,
  `phoneNo` varchar(250) DEFAULT NULL,
  `mobileNo` varchar(250) DEFAULT NULL,
  `studentAcademicYear` int(250) NOT NULL,
  `studentClass` int(250) DEFAULT '0',
  `studentSection` int(250) NOT NULL DEFAULT '0',
  `studentType` int(11) DEFAULT NULL,
  `religion` varchar(250) NOT NULL,
  `nationality` varchar(255) DEFAULT NULL,
  `birthPlace` varchar(255) DEFAULT NULL,
  `parentProfession` varchar(250) DEFAULT NULL,
  `parentOf` text NOT NULL,
  `photo` varchar(250) DEFAULT '',
  `isLeaderBoard` text NOT NULL,
  `restoreUniqId` varchar(250) NOT NULL,
  `transport` int(250) NOT NULL DEFAULT '0',
  `transport_vehicle` int(250) DEFAULT '0',
  `hostel` int(250) DEFAULT '0',
  `room` int(250) DEFAULT '0',
  `medical` longtext NOT NULL,
  `user_position` varchar(250) NOT NULL,
  `defLang` int(10) NOT NULL DEFAULT '0',
  `defTheme` varchar(20) NOT NULL,
  `salary_type` varchar(250) NOT NULL,
  `salary_base_id` int(250) NOT NULL,
  `comVia` text NOT NULL,
  `father_info` text NOT NULL,
  `mother_info` text NOT NULL,
  `biometric_id` int(250) NOT NULL,
  `library_id` varchar(250) NOT NULL,
  `account_active` int(1) NOT NULL DEFAULT '1',
  `updated_at` date NOT NULL,
  `customPermissionsType` varchar(10) DEFAULT NULL,
  `customPermissions` text NOT NULL,
  `firebase_token` longtext NOT NULL,
  `corres_address` longtext,
  `perma_address` longtext,
  `previous_data` longtext
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `hr_view_employee_in_out_data`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hr_view_employee_in_out_data`  AS  select `hr_employee_attendance`.`employee_attendance_id` AS `employee_attendance_id`,`hr_employee_attendance`.`finger_print_id` AS `finger_print_id`,min(`hr_employee_attendance`.`in_out_time`) AS `in_time`,if((count(`hr_employee_attendance`.`in_out_time`) > 1),max(`hr_employee_attendance`.`in_out_time`),'') AS `out_time`,date_format(`hr_employee_attendance`.`in_out_time`,'%Y-%m-%d') AS `date`,timediff(max(`hr_employee_attendance`.`in_out_time`),min(`hr_employee_attendance`.`in_out_time`)) AS `working_time` from `hr_employee_attendance` group by date_format(`hr_employee_attendance`.`in_out_time`,'%Y-%m-%d'),`hr_employee_attendance`.`finger_print_id` ;

ALTER TABLE `academic_year` ADD PRIMARY KEY (`id`);
ALTER TABLE `assignments` ADD PRIMARY KEY (`id`);
ALTER TABLE `assignments_answers` ADD PRIMARY KEY (`id`);
ALTER TABLE `attendance` ADD PRIMARY KEY (`id`);
ALTER TABLE `cities` ADD PRIMARY KEY (`id`), ADD KEY `cities_test_ibfk_1` (`state_id`), ADD KEY `cities_test_ibfk_2` (`country_id`);
ALTER TABLE `classes` ADD PRIMARY KEY (`id`);
ALTER TABLE `class_schedule` ADD PRIMARY KEY (`id`);
ALTER TABLE `convert_files_api_secret_keys` ADD PRIMARY KEY (`id`);
ALTER TABLE `countries` ADD PRIMARY KEY (`id`);
ALTER TABLE `events` ADD PRIMARY KEY (`id`);
ALTER TABLE `exams_list` ADD PRIMARY KEY (`id`);
ALTER TABLE `examterms` ADD PRIMARY KEY (`id`);
ALTER TABLE `exam_marks` ADD PRIMARY KEY (`id`);
ALTER TABLE `failed_jobs` ADD PRIMARY KEY (`id`);
ALTER TABLE `grade_levels` ADD PRIMARY KEY (`id`);
ALTER TABLE `homeworks` ADD PRIMARY KEY (`id`);
ALTER TABLE `hostel` ADD PRIMARY KEY (`id`);
ALTER TABLE `hr_allowance` ADD PRIMARY KEY (`allowance_id`);
ALTER TABLE `hr_bonus_setting` ADD PRIMARY KEY (`bonus_setting_id`);
ALTER TABLE `hr_branch` ADD PRIMARY KEY (`branch_id`), ADD UNIQUE KEY `branch_branch_name_unique` (`branch_name`);
ALTER TABLE `hr_company_address_settings` ADD PRIMARY KEY (`company_address_setting_id`);
ALTER TABLE `hr_deduction` ADD PRIMARY KEY (`deduction_id`);
ALTER TABLE `hr_department` ADD PRIMARY KEY (`department_id`), ADD UNIQUE KEY `department_department_name_unique` (`department_name`);
ALTER TABLE `hr_designation` ADD PRIMARY KEY (`designation_id`), ADD UNIQUE KEY `designation_designation_name_unique` (`designation_name`);
ALTER TABLE `hr_earn_leave_rule` ADD PRIMARY KEY (`earn_leave_rule_id`);
ALTER TABLE `hr_employee` ADD PRIMARY KEY (`employee_id`), ADD UNIQUE KEY `employee_finger_id_unique` (`finger_id`), ADD UNIQUE KEY `employee_email_unique` (`email`);
ALTER TABLE `hr_employee_attendance` ADD PRIMARY KEY (`employee_attendance_id`);
ALTER TABLE `hr_employee_attendance_approve` ADD PRIMARY KEY (`employee_attendance_approve_id`);
ALTER TABLE `hr_employee_award` ADD PRIMARY KEY (`employee_award_id`);
ALTER TABLE `hr_employee_bonus` ADD PRIMARY KEY (`employee_bonus_id`);
ALTER TABLE `hr_employee_education_qualification` ADD PRIMARY KEY (`employee_education_qualification_id`);
ALTER TABLE `hr_employee_experience` ADD PRIMARY KEY (`employee_experience_id`);
ALTER TABLE `hr_employee_performance` ADD PRIMARY KEY (`employee_performance_id`);
ALTER TABLE `hr_employee_performance_details` ADD PRIMARY KEY (`employee_performance_details_id`);
ALTER TABLE `hr_holiday` ADD PRIMARY KEY (`holiday_id`), ADD UNIQUE KEY `holiday_holiday_name_unique` (`holiday_name`);
ALTER TABLE `hr_holiday_details` ADD PRIMARY KEY (`holiday_details_id`);
ALTER TABLE `hr_hourly_salaries` ADD PRIMARY KEY (`hourly_salaries_id`);
ALTER TABLE `hr_interview` ADD PRIMARY KEY (`interview_id`);
ALTER TABLE `hr_job` ADD PRIMARY KEY (`job_id`);
ALTER TABLE `hr_job_applicant` ADD PRIMARY KEY (`job_applicant_id`);
ALTER TABLE `hr_leave_application` ADD PRIMARY KEY (`leave_application_id`);
ALTER TABLE `hr_leave_type` ADD PRIMARY KEY (`leave_type_id`), ADD UNIQUE KEY `leave_type_leave_type_name_unique` (`leave_type_name`);
ALTER TABLE `hr_menus` ADD PRIMARY KEY (`id`);
ALTER TABLE `hr_menu_permission` ADD PRIMARY KEY (`id`);
ALTER TABLE `hr_modules` ADD PRIMARY KEY (`id`);
ALTER TABLE `hr_notice` ADD PRIMARY KEY (`notice_id`);
ALTER TABLE `hr_pay_grade` ADD PRIMARY KEY (`pay_grade_id`), ADD UNIQUE KEY `pay_grade_pay_grade_name_unique` (`pay_grade_name`);
ALTER TABLE `hr_pay_grade_to_allowance` ADD PRIMARY KEY (`pay_grade_to_allowance_id`);
ALTER TABLE `hr_pay_grade_to_deduction` ADD PRIMARY KEY (`pay_grade_to_deduction_id`);
ALTER TABLE `hr_performance_category` ADD PRIMARY KEY (`performance_category_id`), ADD UNIQUE KEY `performance_category_performance_category_name_unique` (`performance_category_name`);
ALTER TABLE `hr_performance_criteria` ADD PRIMARY KEY (`performance_criteria_id`);
ALTER TABLE `hr_print_head_settings` ADD PRIMARY KEY (`print_head_setting_id`);
ALTER TABLE `hr_promotion` ADD PRIMARY KEY (`promotion_id`);
ALTER TABLE `hr_role` ADD PRIMARY KEY (`role_id`), ADD UNIQUE KEY `role_role_name_unique` (`role_name`);
ALTER TABLE `hr_salary_deduction_for_late_attendance` ADD PRIMARY KEY (`salary_deduction_for_late_attendance_id`);
ALTER TABLE `hr_salary_details` ADD PRIMARY KEY (`salary_details_id`);
ALTER TABLE `hr_salary_details_to_allowance` ADD PRIMARY KEY (`salary_details_to_allowance_id`);
ALTER TABLE `hr_salary_details_to_deduction` ADD PRIMARY KEY (`salary_details_to_deduction_id`);
ALTER TABLE `hr_salary_details_to_leave` ADD PRIMARY KEY (`salary_details_to_leave_id`);
ALTER TABLE `hr_tax_rule` ADD PRIMARY KEY (`tax_rule_id`);
ALTER TABLE `hr_termination` ADD PRIMARY KEY (`termination_id`);
ALTER TABLE `hr_training_info` ADD PRIMARY KEY (`training_info_id`);
ALTER TABLE `hr_training_type` ADD PRIMARY KEY (`training_type_id`), ADD UNIQUE KEY `training_type_training_type_name_unique` (`training_type_name`);
ALTER TABLE `hr_user` ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `user_user_name_unique` (`user_name`);
ALTER TABLE `hr_warning` ADD PRIMARY KEY (`warning_id`);
ALTER TABLE `hr_weekly_holiday` ADD PRIMARY KEY (`week_holiday_id`), ADD UNIQUE KEY `weekly_holiday_day_name_unique` (`day_name`);
ALTER TABLE `hr_work_shift` ADD PRIMARY KEY (`work_shift_id`);
ALTER TABLE `jobs` ADD PRIMARY KEY (`id`), ADD KEY `jobs_queue_reserved_reserved_at_index` (`queue`,`reserved`,`reserved_at`);
ALTER TABLE `languages` ADD PRIMARY KEY (`id`);
ALTER TABLE `marksheet` ADD PRIMARY KEY (`id`);
ALTER TABLE `marksheet_students` ADD PRIMARY KEY (`id`);
ALTER TABLE `media_albums` ADD PRIMARY KEY (`id`);
ALTER TABLE `media_items` ADD PRIMARY KEY (`id`);
ALTER TABLE `messages` ADD PRIMARY KEY (`id`);
ALTER TABLE `messages_list` ADD PRIMARY KEY (`id`);
ALTER TABLE `messages_list_grouped` ADD PRIMARY KEY (`id`);
ALTER TABLE `mm_uploads` ADD PRIMARY KEY (`id`);
ALTER TABLE `mob_notifications` ADD PRIMARY KEY (`id`);
ALTER TABLE `newsboard` ADD PRIMARY KEY (`id`);
ALTER TABLE `notifications_mob_history` ADD PRIMARY KEY (`id`);
ALTER TABLE `online_exams` ADD PRIMARY KEY (`id`);
ALTER TABLE `online_exams_grades` ADD PRIMARY KEY (`id`);
ALTER TABLE `online_exams_questions` ADD PRIMARY KEY (`id`);
ALTER TABLE `payments` ADD PRIMARY KEY (`id`);
ALTER TABLE `paymentscollection` ADD PRIMARY KEY (`id`);
ALTER TABLE `payment_multi_invoices` ADD PRIMARY KEY (`id`);
ALTER TABLE `roles` ADD PRIMARY KEY (`id`);
ALTER TABLE `school_terms` ADD PRIMARY KEY (`id`);
ALTER TABLE `sections` ADD PRIMARY KEY (`id`);
ALTER TABLE `settings` ADD PRIMARY KEY (`id`);
ALTER TABLE `shedule_terms` ADD PRIMARY KEY (`id`);
ALTER TABLE `states` ADD PRIMARY KEY (`id`), ADD KEY `country_region` (`country_id`);
ALTER TABLE `student_academic_years` ADD PRIMARY KEY (`id`);
ALTER TABLE `student_categories` ADD PRIMARY KEY (`id`);
ALTER TABLE `student_docs` ADD PRIMARY KEY (`id`);
ALTER TABLE `student_types` ADD PRIMARY KEY (`id`);
ALTER TABLE `subject` ADD PRIMARY KEY (`id`);
ALTER TABLE `subject_videos` ADD PRIMARY KEY (`id`);
ALTER TABLE `sub_subjects` ADD PRIMARY KEY (`id`);
ALTER TABLE `ta_auth_tokens` ADD PRIMARY KEY (`auth_identifier`,`public_key`,`private_key`);
ALTER TABLE `transportation` ADD PRIMARY KEY (`id`);
ALTER TABLE `transport_vehicles` ADD PRIMARY KEY (`id`);
ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD KEY `id` (`id`);

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
