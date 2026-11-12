-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 03:03 PM
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
-- Database: `finalprojectlab_hospitalsystem`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertNewAdmission` (IN `p_AdmissionID` CHAR(5), IN `p_AdmissionDate` DATE, IN `p_Diagnosis` VARCHAR(125), IN `p_PatientID` CHAR(5), IN `p_DoctorID` CHAR(5), IN `p_RoomNumber` CHAR(4))   BEGIN
    -- DischargeDate diisi NULL karena pasien baru masuk
    INSERT INTO admission (
        AdmissionID, AdmissionDate, DischargeDate, Diagnosis, PatientID, DoctorID, RoomNumber
    )
    VALUES (
        p_AdmissionID, p_AdmissionDate, NULL, p_Diagnosis, p_PatientID, p_DoctorID, p_RoomNumber
    );

    -- UPDATE status ruangan menjadi 'Occupied'
    UPDATE room
    SET RoomStatus = 'Occupied'
    WHERE RoomNumber = p_RoomNumber;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertNewAppointment` (IN `p_AppointmentID` CHAR(5), IN `p_AppointmentDate` DATE, IN `p_AppointmentNote` VARCHAR(1250), IN `p_PatientID` CHAR(5), IN `p_DoctorID` CHAR(5))   BEGIN
    -- Status awal janji temu selalu 'Scheduled'
    INSERT INTO appointment (
        AppointmentID, AppointmentDate, AppointmentNote, 
        AppointmentStatus, PatientID, DoctorID
    )
    VALUES (
        p_AppointmentID, p_AppointmentDate, p_AppointmentNote, 
        'Scheduled', p_PatientID, p_DoctorID
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertPatient` (IN `p_PatientID` CHAR(5), IN `p_PatientName` VARCHAR(125), IN `p_PatientGender` ENUM('Male','Female','Others'), IN `p_PatientDOB` DATE, IN `p_PatientPhone` VARCHAR(25), IN `p_PatientEmail` VARCHAR(250), IN `p_PatientEmergencyContact` VARCHAR(25), IN `p_PatientAddress` VARCHAR(2500), IN `p_WeightInKg` FLOAT, IN `p_HeightInCm` INT, IN `p_BloodType` ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-'), IN `p_Allergies` VARCHAR(1250))   BEGIN
    -- 1. Insert ke patientgeneralinfo
    INSERT INTO patientgeneralinfo (
        PatientID, PatientName, PatientGender, PatientDOB, PatientPhone, PatientEmail, 
        PatientEmergencyContact, PatientAddress
    )
    VALUES (
        p_PatientID, p_PatientName, p_PatientGender, p_PatientDOB, p_PatientPhone, p_PatientEmail, 
        p_PatientEmergencyContact, p_PatientAddress
    );

    -- 2. Insert ke patientmedicalinfo
    INSERT INTO patientmedicalinfo (
        PatientID, WeightInKg, HeightInCm, BloodType, Allergies, 
        ChronicConditions, PastSurgeries, CurrentMedications, SmokingStatus, AlcoholUse, DisabilityStatus, ExtraNote
    )
    VALUES (
        p_PatientID, p_WeightInKg, p_HeightInCm, p_BloodType, p_Allergies, 
        NULL, NULL, NULL, NULL, NULL, NULL, NULL -- Mengisi sisa kolom dengan NULL
    );
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateTotalAdmissionCost` (`p_AdmissionID` CHAR(5)) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    DECLARE total_cost DECIMAL(12,2);
    
    SELECT 
        SUM(m.MedicationPrice * 1) -- Asumsi 1 unit obat per resep
    INTO total_cost
    FROM admissionprescriptions ap
    JOIN medication m ON ap.MedicationID = m.MedicationID
    WHERE ap.AdmissionID = p_AdmissionID;

    RETURN IFNULL(total_cost, 0.00);
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `CalculateTotalAppointmentCost` (`p_AppointmentID` CHAR(5)) RETURNS DECIMAL(12,2) DETERMINISTIC BEGIN
    DECLARE total_cost DECIMAL(12,2);
    
    -- Menghitung total biaya dari semua obat yang diresepkan dalam Appointment ini
    SELECT 
        SUM(m.MedicationPrice * 1) -- Asumsi 1 unit obat digunakan per resep untuk Appointment
    INTO total_cost
    FROM appointmentprescription ap
    JOIN medication m ON ap.MedicationID = m.MedicationID
    WHERE ap.AppointmentID = p_AppointmentID;

    RETURN IFNULL(total_cost, 0.00);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admission`
--

CREATE TABLE `admission` (
  `AdmissionID` char(5) NOT NULL CHECK (`AdmissionID` regexp '^AD[0-9]{3}$'),
  `AdmissionDate` date NOT NULL,
  `DischargeDate` date DEFAULT NULL,
  `Diagnosis` varchar(125) NOT NULL,
  `PatientID` char(5) NOT NULL,
  `DoctorID` char(5) NOT NULL,
  `RoomNumber` char(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission`
--

INSERT INTO `admission` (`AdmissionID`, `AdmissionDate`, `DischargeDate`, `Diagnosis`, `PatientID`, `DoctorID`, `RoomNumber`) VALUES
('AD001', '2025-12-15', NULL, 'Acute Myocardial Infarction', 'PA001', 'DO001', '2005');

-- --------------------------------------------------------

--
-- Table structure for table `admissionbills`
--

CREATE TABLE `admissionbills` (
  `AdmissionBillsID` char(7) NOT NULL CHECK (`AdmissionBillsID` regexp '^ADBI[0-9]{3}$'),
  `TotalAmount` decimal(12,2) NOT NULL,
  `PaymentStatus` enum('Paid','Unpaid','Pending') NOT NULL,
  `PaymentMethod` enum('Cash','Card','Others') NOT NULL,
  `CreationDate` date NOT NULL,
  `StaffID` char(5) NOT NULL,
  `AdmissionID` char(5) NOT NULL,
  `AdmissionPrescriptionID` char(7) DEFAULT NULL,
  `BankName` varchar(125) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admissionbills`
--

INSERT INTO `admissionbills` (`AdmissionBillsID`, `TotalAmount`, `PaymentStatus`, `PaymentMethod`, `CreationDate`, `StaffID`, `AdmissionID`, `AdmissionPrescriptionID`, `BankName`) VALUES
('ADBI001', 15000.00, 'Unpaid', 'Card', '2025-12-16', 'ST002', 'AD001', 'ADPR001', 'Bank ABC');

-- --------------------------------------------------------

--
-- Table structure for table `admissionprescriptions`
--

CREATE TABLE `admissionprescriptions` (
  `AdmissionPrescriptionID` char(7) NOT NULL CHECK (`AdmissionPrescriptionID` regexp '^ADPR[0-9]{3}$'),
  `AdmissionDosageUsed` varchar(250) NOT NULL,
  `MedicationID` char(5) NOT NULL,
  `AdmissionID` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admissionprescriptions`
--

INSERT INTO `admissionprescriptions` (`AdmissionPrescriptionID`, `AdmissionDosageUsed`, `MedicationID`, `AdmissionID`) VALUES
('ADPR001', '1 tablet every 8 hours', 'ME002', 'AD001');

--
-- Triggers `admissionprescriptions`
--
DELIMITER $$
CREATE TRIGGER `UpdateStockAfterAdmission` AFTER INSERT ON `admissionprescriptions` FOR EACH ROW BEGIN
    -- Kurangi Stok berdasarkan MedicationID yang baru diresepkan
    UPDATE medication
    SET Stock = Stock - 1 -- Asumsi 1 unit obat digunakan per resep untuk Admission
    WHERE MedicationID = NEW.MedicationID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `AppointmentID` char(5) NOT NULL CHECK (`AppointmentID` regexp '^AP[0-9]{3}$'),
  `AppointmentDate` date NOT NULL,
  `AppointmentNote` varchar(1250) NOT NULL,
  `AppointmentStatus` enum('Scheduled','Completed','Cancelled') NOT NULL,
  `PatientID` char(5) NOT NULL,
  `DoctorID` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`AppointmentID`, `AppointmentDate`, `AppointmentNote`, `AppointmentStatus`, `PatientID`, `DoctorID`) VALUES
('AP001', '2025-12-20', 'Check-up rutin dan konsultasi diet.', 'Completed', 'PA002', 'DO002');

-- --------------------------------------------------------

--
-- Table structure for table `appointmentbills`
--

CREATE TABLE `appointmentbills` (
  `AppointmentBillsID` char(7) NOT NULL CHECK (`AppointmentBillsID` regexp '^APBI[0-9]{3}$'),
  `TotalAmount` decimal(12,2) NOT NULL,
  `PaymentStatus` enum('Paid','Unpaid','Pending') NOT NULL,
  `PaymentMethod` enum('Cash','Card','Others') NOT NULL,
  `CreationDate` date NOT NULL,
  `StaffID` char(5) NOT NULL,
  `AppointmentID` char(5) NOT NULL,
  `AppointmentPrescriptionID` char(7) DEFAULT NULL,
  `BankName` varchar(125) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointmentbills`
--

INSERT INTO `appointmentbills` (`AppointmentBillsID`, `TotalAmount`, `PaymentStatus`, `PaymentMethod`, `CreationDate`, `StaffID`, `AppointmentID`, `AppointmentPrescriptionID`, `BankName`) VALUES
('APBI001', 5000.00, 'Paid', 'Cash', '2025-12-20', 'ST001', 'AP001', 'APPR001', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `appointmentprescription`
--

CREATE TABLE `appointmentprescription` (
  `AppointmentPrescriptionID` char(7) NOT NULL CHECK (`AppointmentPrescriptionID` regexp '^APPR[0-9]{3}$'),
  `DosageDetail` varchar(125) NOT NULL,
  `Duration` varchar(125) NOT NULL,
  `MedicationID` char(5) NOT NULL,
  `AppointmentID` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointmentprescription`
--

INSERT INTO `appointmentprescription` (`AppointmentPrescriptionID`, `DosageDetail`, `Duration`, `MedicationID`, `AppointmentID`) VALUES
('APPR001', '1 tablet, 3 times a day', '7 days', 'ME001', 'AP001');

--
-- Triggers `appointmentprescription`
--
DELIMITER $$
CREATE TRIGGER `UpdateStockAfterAppointment` AFTER INSERT ON `appointmentprescription` FOR EACH ROW BEGIN
    -- Kurangi Stok berdasarkan MedicationID yang baru diresepkan
    UPDATE medication
    SET Stock = Stock - 1 -- Asumsi 1 unit obat digunakan per resep untuk Appointment
    WHERE MedicationID = NEW.MedicationID;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `doctordata`
--

CREATE TABLE `doctordata` (
  `DoctorID` char(5) NOT NULL CHECK (`DoctorID` regexp '^DO[0-9]{3}$'),
  `DoctorName` varchar(125) NOT NULL,
  `Specialization` varchar(125) NOT NULL,
  `DoctorPhone` varchar(25) NOT NULL,
  `DoctorEmail` varchar(250) NOT NULL,
  `DoctorAddress` varchar(2500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctordata`
--

INSERT INTO `doctordata` (`DoctorID`, `DoctorName`, `Specialization`, `DoctorPhone`, `DoctorEmail`, `DoctorAddress`) VALUES
('DO001', 'Dr. Budi Santoso', 'Cardiologist', '081234567890', 'budi.santoso@hospital.com', 'Jl. Merdeka No. 10, Jakarta'),
('DO002', 'Dr. Siti Aminah', 'Pediatrician', '089876543210', 'siti.aminah@hospital.com', 'Jl. Sudirman No. 25, Bandung');

-- --------------------------------------------------------

--
-- Table structure for table `medication`
--

CREATE TABLE `medication` (
  `MedicationID` char(5) NOT NULL CHECK (`MedicationID` regexp '^ME[0-9]{3}$'),
  `MedicationName` varchar(250) NOT NULL,
  `MedicationDescription` varchar(2500) NOT NULL,
  `Stock` int(11) NOT NULL CHECK (`Stock` >= 0),
  `MedicationPrice` decimal(12,2) NOT NULL,
  `Producer` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medication`
--

INSERT INTO `medication` (`MedicationID`, `MedicationName`, `MedicationDescription`, `Stock`, `MedicationPrice`, `Producer`) VALUES
('ME001', 'Paracetamol 500mg', 'Pain reliever and fever reducer.', 99, 5000.00, 'Pharma Jaya'),
('ME002', 'Amoxicillin 250mg', 'Antibiotic for bacterial infections.', 50, 15000.00, 'Medi Sehat');

-- --------------------------------------------------------

--
-- Table structure for table `patientgeneralinfo`
--

CREATE TABLE `patientgeneralinfo` (
  `PatientID` char(5) NOT NULL CHECK (`PatientID` regexp '^PA[0-9]{3}$'),
  `PatientName` varchar(125) NOT NULL,
  `PatientGender` enum('Male','Female','Others') NOT NULL,
  `PatientDOB` date NOT NULL,
  `PatientPhone` varchar(25) NOT NULL,
  `PatientEmail` varchar(250) DEFAULT NULL,
  `PatientEmergencyContact` varchar(25) NOT NULL,
  `PatientAddress` varchar(2500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patientgeneralinfo`
--

INSERT INTO `patientgeneralinfo` (`PatientID`, `PatientName`, `PatientGender`, `PatientDOB`, `PatientPhone`, `PatientEmail`, `PatientEmergencyContact`, `PatientAddress`) VALUES
('PA001', 'Agus Setiawan', 'Male', '1990-05-15', '081212121212', 'agus.setiawan@email.com', '081345454545', 'Perumahan Indah Blok A No. 12'),
('PA002', 'Dewi Puspita', 'Female', '1985-11-20', '087834343434', 'dewi.puspita@email.com', '087756565656', 'Apartemen Sejahtera Lantai 5, Unit 3B');

-- --------------------------------------------------------

--
-- Table structure for table `patientmedicalinfo`
--

CREATE TABLE `patientmedicalinfo` (
  `PatientID` char(5) NOT NULL,
  `WeightInKg` float NOT NULL,
  `HeightInCm` int(11) NOT NULL,
  `BloodType` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `Allergies` varchar(1250) DEFAULT NULL,
  `ChronicConditions` varchar(1250) DEFAULT NULL,
  `PastSurgeries` varchar(1250) DEFAULT NULL,
  `CurrentMedications` varchar(1250) DEFAULT NULL,
  `SmokingStatus` enum('Never','Former','Current') DEFAULT NULL,
  `AlcoholUse` enum('None','Occasional','Regular') DEFAULT NULL,
  `DisabilityStatus` varchar(1250) DEFAULT NULL,
  `ExtraNote` varchar(2500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patientmedicalinfo`
--

INSERT INTO `patientmedicalinfo` (`PatientID`, `WeightInKg`, `HeightInCm`, `BloodType`, `Allergies`, `ChronicConditions`, `PastSurgeries`, `CurrentMedications`, `SmokingStatus`, `AlcoholUse`, `DisabilityStatus`, `ExtraNote`) VALUES
('PA001', 75.5, 170, 'O+', 'Dust, Peanuts', 'Asthma', NULL, NULL, 'Former', 'Occasional', NULL, NULL),
('PA002', 60, 165, 'A-', 'None', NULL, NULL, 'Vitamin C', 'Never', 'None', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `RoomNumber` char(4) NOT NULL CHECK (`RoomNumber` regexp '^[0-9]{4}$'),
  `RoomType` varchar(25) NOT NULL,
  `RoomStatus` enum('Available','Occupied','Under Maintenance') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`RoomNumber`, `RoomType`, `RoomStatus`) VALUES
('1001', 'VIP', 'Available'),
('2005', 'Standard', 'Occupied');

-- --------------------------------------------------------

--
-- Table structure for table `staffdata`
--

CREATE TABLE `staffdata` (
  `StaffID` char(5) NOT NULL CHECK (`StaffID` regexp '^ST[0-9]{3}$'),
  `StaffName` varchar(125) NOT NULL,
  `StaffGender` enum('Male','Female','Others') NOT NULL,
  `StaffPhone` varchar(25) NOT NULL,
  `StaffEmail` varchar(250) NOT NULL,
  `StaffAddress` varchar(1250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffdata`
--

INSERT INTO `staffdata` (`StaffID`, `StaffName`, `StaffGender`, `StaffPhone`, `StaffEmail`, `StaffAddress`) VALUES
('ST001', 'Rina Wijaya', 'Female', '081122334455', 'rina.wijaya@hospital.com', 'Jl. Pahlawan No. 5, Surabaya'),
('ST002', 'Joko Susilo', 'Male', '085000111222', 'joko.susilo@hospital.com', 'Jl. Gajah Mada No. 8, Semarang');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admission`
--
ALTER TABLE `admission`
  ADD PRIMARY KEY (`AdmissionID`),
  ADD KEY `fk_ad_patient` (`PatientID`),
  ADD KEY `fk_ad_doctor` (`DoctorID`),
  ADD KEY `fk_ad_room` (`RoomNumber`);

--
-- Indexes for table `admissionbills`
--
ALTER TABLE `admissionbills`
  ADD PRIMARY KEY (`AdmissionBillsID`),
  ADD KEY `fk_adb_staff` (`StaffID`),
  ADD KEY `fk_adb_ad` (`AdmissionID`),
  ADD KEY `fk_adb_adpr` (`AdmissionPrescriptionID`);

--
-- Indexes for table `admissionprescriptions`
--
ALTER TABLE `admissionprescriptions`
  ADD PRIMARY KEY (`AdmissionPrescriptionID`),
  ADD KEY `fk_adpr_ad` (`AdmissionID`),
  ADD KEY `fk_adpr_med` (`MedicationID`);

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`AppointmentID`),
  ADD KEY `fk_ap_patient` (`PatientID`),
  ADD KEY `fk_ap_doctor` (`DoctorID`);

--
-- Indexes for table `appointmentbills`
--
ALTER TABLE `appointmentbills`
  ADD PRIMARY KEY (`AppointmentBillsID`),
  ADD KEY `fk_apb_staff` (`StaffID`),
  ADD KEY `fk_apb_ap` (`AppointmentID`),
  ADD KEY `fk_apb_appr` (`AppointmentPrescriptionID`);

--
-- Indexes for table `appointmentprescription`
--
ALTER TABLE `appointmentprescription`
  ADD PRIMARY KEY (`AppointmentPrescriptionID`),
  ADD KEY `fk_appr_ap` (`AppointmentID`),
  ADD KEY `fk_appr_med` (`MedicationID`);

--
-- Indexes for table `doctordata`
--
ALTER TABLE `doctordata`
  ADD PRIMARY KEY (`DoctorID`);

--
-- Indexes for table `medication`
--
ALTER TABLE `medication`
  ADD PRIMARY KEY (`MedicationID`);

--
-- Indexes for table `patientgeneralinfo`
--
ALTER TABLE `patientgeneralinfo`
  ADD PRIMARY KEY (`PatientID`);

--
-- Indexes for table `patientmedicalinfo`
--
ALTER TABLE `patientmedicalinfo`
  ADD PRIMARY KEY (`PatientID`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`RoomNumber`);

--
-- Indexes for table `staffdata`
--
ALTER TABLE `staffdata`
  ADD PRIMARY KEY (`StaffID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admission`
--
ALTER TABLE `admission`
  ADD CONSTRAINT `fk_ad_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `doctordata` (`DoctorID`),
  ADD CONSTRAINT `fk_ad_patient` FOREIGN KEY (`PatientID`) REFERENCES `patientgeneralinfo` (`PatientID`),
  ADD CONSTRAINT `fk_ad_room` FOREIGN KEY (`RoomNumber`) REFERENCES `room` (`RoomNumber`);

--
-- Constraints for table `admissionbills`
--
ALTER TABLE `admissionbills`
  ADD CONSTRAINT `fk_adb_ad` FOREIGN KEY (`AdmissionID`) REFERENCES `admission` (`AdmissionID`),
  ADD CONSTRAINT `fk_adb_adpr` FOREIGN KEY (`AdmissionPrescriptionID`) REFERENCES `admissionprescriptions` (`AdmissionPrescriptionID`),
  ADD CONSTRAINT `fk_adb_staff` FOREIGN KEY (`StaffID`) REFERENCES `staffdata` (`StaffID`);

--
-- Constraints for table `admissionprescriptions`
--
ALTER TABLE `admissionprescriptions`
  ADD CONSTRAINT `fk_adpr_ad` FOREIGN KEY (`AdmissionID`) REFERENCES `admission` (`AdmissionID`),
  ADD CONSTRAINT `fk_adpr_med` FOREIGN KEY (`MedicationID`) REFERENCES `medication` (`MedicationID`);

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `fk_ap_doctor` FOREIGN KEY (`DoctorID`) REFERENCES `doctordata` (`DoctorID`),
  ADD CONSTRAINT `fk_ap_patient` FOREIGN KEY (`PatientID`) REFERENCES `patientgeneralinfo` (`PatientID`);

--
-- Constraints for table `appointmentbills`
--
ALTER TABLE `appointmentbills`
  ADD CONSTRAINT `fk_apb_ap` FOREIGN KEY (`AppointmentID`) REFERENCES `appointment` (`AppointmentID`),
  ADD CONSTRAINT `fk_apb_appr` FOREIGN KEY (`AppointmentPrescriptionID`) REFERENCES `appointmentprescription` (`AppointmentPrescriptionID`),
  ADD CONSTRAINT `fk_apb_staff` FOREIGN KEY (`StaffID`) REFERENCES `staffdata` (`StaffID`);

--
-- Constraints for table `appointmentprescription`
--
ALTER TABLE `appointmentprescription`
  ADD CONSTRAINT `fk_appr_ap` FOREIGN KEY (`AppointmentID`) REFERENCES `appointment` (`AppointmentID`),
  ADD CONSTRAINT `fk_appr_med` FOREIGN KEY (`MedicationID`) REFERENCES `medication` (`MedicationID`);

--
-- Constraints for table `patientmedicalinfo`
--
ALTER TABLE `patientmedicalinfo`
  ADD CONSTRAINT `fk_pmi_patient` FOREIGN KEY (`PatientID`) REFERENCES `patientgeneralinfo` (`PatientID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
