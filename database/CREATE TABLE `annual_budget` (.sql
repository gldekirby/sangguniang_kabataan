CREATE TABLE `annual_budget` (
  `id` int(11) NOT NULL,
  `fund_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `allocated_amount` decimal(12,2) NOT NULL,
  `fiscal_year` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `budget_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `fund_sources` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `projects` (
  `id` int(11) NOT NULL auto increment,
  `reference_code` varchar(20) DEFAULT NULL,
  `project_name` text NOT NULL,
  `implementing_office` varchar(100) DEFAULT 'Sangguniang Kabataan',
  `start_date` varchar(20) DEFAULT 'January',
  `end_date` varchar(20) DEFAULT 'December',
  `expected_output` text DEFAULT NULL,
  `funding_source` varchar(50) DEFAULT 'GF - 10%SK',
  `personal_services` int(11) DEFAULT NULL,
  `mooe` decimal(12,2) DEFAULT 0.00,
  `capital_outlay` decimal(12,2) DEFAULT 0.00,
  `total_cost` decimal(12,2) GENERATED ALWAYS AS (`personal_services` + `mooe` + `capital_outlay`) STORED,
  `sector` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `budget_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;