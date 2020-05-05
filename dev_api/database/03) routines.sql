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

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_DailyAttendance`(IN `input_date` DATE)
BEGIN 
        select hr_employee.employee_id,hr_employee.photo,CONCAT(COALESCE(hr_employee.first_name,''),' ',COALESCE(hr_employee.last_name,'')) AS fullName,department_name,
        hr_view_employee_in_out_data.employee_attendance_id,hr_view_employee_in_out_data.finger_print_id,hr_view_employee_in_out_data.date,hr_view_employee_in_out_data.working_time,
        DATE_FORMAT(hr_view_employee_in_out_data.in_time,'%h:%i %p') AS in_time,DATE_FORMAT(hr_view_employee_in_out_data.out_time,'%h:%i %p') AS out_time, 
        TIME_FORMAT( hr_work_shift.late_count_time, '%H:%i:%s' ) as lateCountTime,
        (SELECT CASE WHEN DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time),'%H:%i:00')  > lateCountTime
        THEN 'Yes' 
        ELSE 'No' END) AS  ifLate,
        (SELECT CASE WHEN TIMEDIFF((DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time),'%H:%i:%s')),hr_work_shift.late_count_time)  > '0'
        THEN TIMEDIFF((DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time),'%H:%i:%s')),hr_work_shift.late_count_time) 
        ELSE '00:00:00' END) AS  totalLateTime,
        TIMEDIFF((DATE_FORMAT(hr_work_shift.`end_time`,'%H:%i:%s')),hr_work_shift.`start_time`) AS workingHour
        from hr_employee
        inner join hr_view_employee_in_out_data on hr_view_employee_in_out_data.finger_print_id = hr_employee.finger_id
        inner join hr_department on hr_department.department_id = hr_employee.department_id
        JOIN hr_work_shift on hr_work_shift.work_shift_id = hr_employee.work_shift_id
        where `status`=1 AND `date`=input_date GROUP BY hr_view_employee_in_out_data.finger_print_id ORDER BY employee_attendance_id DESC;        
    END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_calculateEmployeeLeaveBalance`(IN `employeeId` INT(10), IN `leaveTypeId` INT(10))
BEGIN  
        SELECT SUM(number_of_day) AS totalNumberOfDays FROM hr_leave_application WHERE employee_id=employeeId AND leave_type_id=leaveTypeId and status = 2
        AND (approve_date  BETWEEN DATE_FORMAT(NOW(),'%Y-01-01') AND DATE_FORMAT(NOW(),'%Y-12-31'));
    END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_getWeeklyHoliday`()
BEGIN
        select day_name from  hr_weekly_holiday where status=1;
    END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_monthlyAttendance`(IN `employeeId` INT(10), IN `from_date` DATE, IN `to_date` DATE)
BEGIN 
        select hr_employee.employee_id,CONCAT(COALESCE(hr_employee.first_name,''),' ',COALESCE(hr_employee.last_name,'')) AS fullName,department_name,
        hr_view_employee_in_out_data.finger_print_id,hr_view_employee_in_out_data.date,hr_view_employee_in_out_data.working_time,
        DATE_FORMAT(hr_view_employee_in_out_data.in_time,'%h:%i %p') AS in_time,DATE_FORMAT(hr_view_employee_in_out_data.out_time,'%h:%i %p') AS out_time, 
        TIME_FORMAT( hr_work_shift.late_count_time, '%H:%i:%s' ) as lateCountTime,
        (SELECT CASE WHEN DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time),'%H:%i:00')  > lateCountTime
        THEN 'Yes' 
        ELSE 'No' END) AS  ifLate,
        (SELECT CASE WHEN TIMEDIFF((DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time),'%H:%i:%s')),hr_work_shift.late_count_time)  > '0'
        THEN TIMEDIFF((DATE_FORMAT(MIN(hr_view_employee_in_out_data.in_time),'%H:%i:%s')),hr_work_shift.late_count_time) 
        ELSE '00:00:00' END) AS  totalLateTime,
        TIMEDIFF((DATE_FORMAT(hr_work_shift.`end_time`,'%H:%i:%s')),hr_work_shift.`start_time`) AS workingHour
        from hr_employee
        inner join hr_view_employee_in_out_data on hr_view_employee_in_out_data.finger_print_id = hr_employee.finger_id
        inner join hr_department on hr_department.department_id = hr_employee.department_id
        JOIN hr_work_shift on hr_work_shift.work_shift_id = hr_employee.work_shift_id
        where `status`=1 
        AND `date` between from_date and to_date and employee_id=employeeId
        GROUP BY hr_view_employee_in_out_data.date,hr_view_employee_in_out_data.`finger_print_id`;
    END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_getHoliday`(IN `fromDate` DATE, IN `toDate` DATE)
BEGIN 
        SELECT from_date,to_date FROM hr_holiday_details WHERE from_date >= fromDate AND to_date <=toDate;
    END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_getEmployeeInfo`(IN `employeeId` INT(10))
BEGIN
        SELECT hr_employee.*,user.`user_name` FROM hr_employee 
        INNER JOIN `users` ON `users`.`id` = hr_employee.`user_id`
        WHERE employee_id = employeeId;
    END$$
DELIMITER ;

COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
