-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Aug 26, 2024 at 07:49 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `demandium_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `balance_pending` decimal(24,2) NOT NULL DEFAULT '0.00',
  `received_balance` decimal(24,2) NOT NULL DEFAULT '0.00',
  `account_payable` decimal(24,2) NOT NULL DEFAULT '0.00',
  `account_receivable` decimal(24,2) NOT NULL DEFAULT '0.00',
  `total_withdrawn` decimal(24,2) NOT NULL DEFAULT '0.00',
  `total_expense` decimal(24,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `added_to_carts`
--

CREATE TABLE `added_to_carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `addon_settings`
--

CREATE TABLE `addon_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_values` longtext COLLATE utf8mb4_unicode_ci,
  `test_values` longtext COLLATE utf8mb4_unicode_ci,
  `settings_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'live',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addon_settings`
--

INSERT INTO `addon_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`, `additional_data`) VALUES
('070c6bbd-d777-11ed-96f4-0c7a158e4469', 'twilio', '{\"gateway\":\"twilio\",\"mode\":\"live\",\"status\":\"0\",\"sid\":\"data\",\"messaging_service_sid\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', '{\"gateway\":\"twilio\",\"mode\":\"live\",\"status\":\"0\",\"sid\":\"data\",\"messaging_service_sid\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', 'sms_config', 'live', 0, NULL, '2023-08-12 07:01:29', NULL),
('070c766c-d777-11ed-96f4-0c7a158e4469', '2factor', '{\"gateway\":\"2factor\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\"}', '{\"gateway\":\"2factor\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\"}', 'sms_config', 'live', 0, NULL, '2023-08-12 07:01:36', NULL),
('0d8a9308-d6a5-11ed-962c-0c7a158e4469', 'mercadopago', '{\"gateway\":\"mercadopago\",\"mode\":\"test\",\"status\":\"0\",\"access_token\":\"data\",\"public_key\":\"data\"}', '{\"gateway\":\"mercadopago\",\"mode\":\"test\",\"status\":\"0\",\"access_token\":\"data\",\"public_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-27 11:57:11', '{\"gateway_title\":null,\"gateway_image\":\"2023-04-12-64367be3b7b6a.png\"}'),
('0d8a9e49-d6a5-11ed-962c-0c7a158e4469', 'liqpay', '{\"gateway\":\"liqpay\",\"mode\":\"test\",\"status\":\"0\",\"private_key\":\"data\",\"public_key\":\"data\"}', '{\"gateway\":\"liqpay\",\"mode\":\"test\",\"status\":\"0\",\"private_key\":\"data\",\"public_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:32:31', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('101befdf-d44b-11ed-8564-0c7a158e4469', 'paypal', '{\"gateway\":\"paypal\",\"mode\":\"test\",\"status\":\"0\",\"client_id\":\"data\",\"client_secret\":\"data\"}', '{\"gateway\":\"paypal\",\"mode\":\"test\",\"status\":\"0\",\"client_id\":\"data\",\"client_secret\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 03:41:32', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('133d9647-cabb-11ed-8fec-0c7a158e4469', 'hyper_pay', '{\"gateway\":\"hyper_pay\",\"mode\":\"test\",\"status\":\"0\",\"entity_id\":\"data\",\"access_code\":\"data\"}', '{\"gateway\":\"hyper_pay\",\"mode\":\"test\",\"status\":\"0\",\"entity_id\":\"data\",\"access_code\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:32:42', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('1821029f-d776-11ed-96f4-0c7a158e4469', 'msg91', '{\"gateway\":\"msg91\",\"mode\":\"live\",\"status\":\"0\",\"template_id\":\"data\",\"auth_key\":\"data\"}', '{\"gateway\":\"msg91\",\"mode\":\"live\",\"status\":\"0\",\"template_id\":\"data\",\"auth_key\":\"data\"}', 'sms_config', 'live', 0, NULL, '2023-08-12 07:01:48', NULL),
('18210f2b-d776-11ed-96f4-0c7a158e4469', 'nexmo', '{\"gateway\":\"nexmo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"\",\"api_secret\":\"\",\"token\":\"\",\"from\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"nexmo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"\",\"api_secret\":\"\",\"token\":\"\",\"from\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, '2023-04-10 02:14:44', NULL),
('18fbb21f-d6ad-11ed-962c-0c7a158e4469', 'foloosi', '{\"gateway\":\"foloosi\",\"mode\":\"test\",\"status\":\"0\",\"merchant_key\":\"data\"}', '{\"gateway\":\"foloosi\",\"mode\":\"test\",\"status\":\"0\",\"merchant_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:34:33', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('2767d142-d6a1-11ed-962c-0c7a158e4469', 'paytm', '{\"gateway\":\"paytm\",\"mode\":\"test\",\"status\":\"0\",\"merchant_key\":\"data\",\"merchant_id\":\"data\",\"merchant_website_link\":\"data\"}', '{\"gateway\":\"paytm\",\"mode\":\"test\",\"status\":\"0\",\"merchant_key\":\"data\",\"merchant_id\":\"data\",\"merchant_website_link\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-22 06:30:55', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('3201d2e6-c937-11ed-a424-0c7a158e4469', 'amazon_pay', '{\"gateway\":\"amazon_pay\",\"mode\":\"test\",\"status\":\"0\",\"pass_phrase\":\"data\",\"access_code\":\"data\",\"merchant_identifier\":\"data\"}', '{\"gateway\":\"amazon_pay\",\"mode\":\"test\",\"status\":\"0\",\"pass_phrase\":\"data\",\"access_code\":\"data\",\"merchant_identifier\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:36:07', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('4593b25c-d6a1-11ed-962c-0c7a158e4469', 'paytabs', '{\"gateway\":\"paytabs\",\"mode\":\"test\",\"status\":\"0\",\"profile_id\":\"data\",\"server_key\":\"data\",\"base_url\":\"data\"}', '{\"gateway\":\"paytabs\",\"mode\":\"test\",\"status\":\"0\",\"profile_id\":\"data\",\"server_key\":\"data\",\"base_url\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:34:51', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('4e9b8dfb-e7d1-11ed-a559-0c7a158e4469', 'bkash', '{\"gateway\":\"bkash\",\"mode\":\"test\",\"status\":\"0\",\"app_key\":\"data\",\"app_secret\":\"data\",\"username\":\"data\",\"password\":\"data\"}', '{\"gateway\":\"bkash\",\"mode\":\"test\",\"status\":\"0\",\"app_key\":\"data\",\"app_secret\":\"data\",\"username\":\"data\",\"password\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:39:42', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('544a24a4-c872-11ed-ac7a-0c7a158e4469', 'fatoorah', '{\"gateway\":\"fatoorah\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"data\"}', '{\"gateway\":\"fatoorah\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:36:24', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('58c1bc8a-d6ac-11ed-962c-0c7a158e4469', 'ccavenue', '{\"gateway\":\"ccavenue\",\"mode\":\"test\",\"status\":\"0\",\"merchant_id\":\"data\",\"working_key\":\"data\",\"access_code\":\"data\"}', '{\"gateway\":\"ccavenue\",\"mode\":\"test\",\"status\":\"0\",\"merchant_id\":\"data\",\"working_key\":\"data\",\"access_code\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 03:42:38', '{\"gateway_title\":null,\"gateway_image\":\"2023-04-13-643783f01d386.png\"}'),
('5e2d2ef9-d6ab-11ed-962c-0c7a158e4469', 'thawani', '{\"gateway\":\"thawani\",\"mode\":\"test\",\"status\":\"0\",\"public_key\":\"data\",\"private_key\":\"data\"}', '{\"gateway\":\"thawani\",\"mode\":\"test\",\"status\":\"0\",\"public_key\":\"data\",\"private_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:50:40', '{\"gateway_title\":null,\"gateway_image\":\"2023-04-13-64378f9856f29.png\"}'),
('60cc83cc-d5b9-11ed-b56f-0c7a158e4469', 'sixcash', '{\"gateway\":\"sixcash\",\"mode\":\"test\",\"status\":\"0\",\"public_key\":\"data\",\"secret_key\":\"data\",\"merchant_number\":\"data\",\"base_url\":\"data\"}', '{\"gateway\":\"sixcash\",\"mode\":\"test\",\"status\":\"0\",\"public_key\":\"data\",\"secret_key\":\"data\",\"merchant_number\":\"data\",\"base_url\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:16:17', '{\"gateway_title\":null,\"gateway_image\":\"2023-04-12-6436774e77ff9.png\"}'),
('68579846-d8e8-11ed-8249-0c7a158e4469', 'alphanet_sms', '{\"gateway\":\"alphanet_sms\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"alphanet_sms\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('6857a2e8-d8e8-11ed-8249-0c7a158e4469', 'sms_to', '{\"gateway\":\"sms_to\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\",\"sender_id\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"sms_to\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\",\"sender_id\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('74c30c00-d6a6-11ed-962c-0c7a158e4469', 'hubtel', '{\"gateway\":\"hubtel\",\"mode\":\"test\",\"status\":\"0\",\"account_number\":\"data\",\"api_id\":\"data\",\"api_key\":\"data\"}', '{\"gateway\":\"hubtel\",\"mode\":\"test\",\"status\":\"0\",\"account_number\":\"data\",\"api_id\":\"data\",\"api_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:37:43', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('74e46b0a-d6aa-11ed-962c-0c7a158e4469', 'tap', '{\"gateway\":\"tap\",\"mode\":\"test\",\"status\":\"0\",\"secret_key\":\"data\"}', '{\"gateway\":\"tap\",\"mode\":\"test\",\"status\":\"0\",\"secret_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:50:09', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('761ca96c-d1eb-11ed-87ca-0c7a158e4469', 'swish', '{\"gateway\":\"swish\",\"mode\":\"test\",\"status\":\"0\",\"number\":\"data\"}', '{\"gateway\":\"swish\",\"mode\":\"test\",\"status\":\"0\",\"number\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:17:02', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('7b1c3c5f-d2bd-11ed-b485-0c7a158e4469', 'payfast', '{\"gateway\":\"payfast\",\"mode\":\"test\",\"status\":\"0\",\"merchant_id\":\"data\",\"secured_key\":\"data\"}', '{\"gateway\":\"payfast\",\"mode\":\"test\",\"status\":\"0\",\"merchant_id\":\"data\",\"secured_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:18:13', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('8592417b-d1d1-11ed-a984-0c7a158e4469', 'esewa', '{\"gateway\":\"esewa\",\"mode\":\"test\",\"status\":\"0\",\"merchantCode\":\"data\"}', '{\"gateway\":\"esewa\",\"mode\":\"test\",\"status\":\"0\",\"merchantCode\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:17:38', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('9162a1dc-cdf1-11ed-affe-0c7a158e4469', 'viva_wallet', '{\"gateway\":\"viva_wallet\",\"mode\":\"test\",\"status\":\"0\",\"client_id\": \"\",\"client_secret\": \"\"}\n', '{\"gateway\":\"viva_wallet\",\"mode\":\"test\",\"status\":\"0\",\"client_id\": \"\",\"client_secret\": \"\"}', 'payment_config', 'test', 0, NULL, NULL, NULL),
('998ccc62-d6a0-11ed-962c-0c7a158e4469', 'stripe', '{\"gateway\":\"stripe\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"published_key\":\"data\"}', '{\"gateway\":\"stripe\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"published_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:18:55', '{\"gateway_title\":\"Stripe\",\"gateway_image\":null}'),
('a3313755-c95d-11ed-b1db-0c7a158e4469', 'iyzi_pay', '{\"gateway\":\"iyzi_pay\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"data\",\"secret_key\":\"data\",\"base_url\":\"data\"}', '{\"gateway\":\"iyzi_pay\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"data\",\"secret_key\":\"data\",\"base_url\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:20:02', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('a76c8993-d299-11ed-b485-0c7a158e4469', 'momo', '{\"gateway\":\"momo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\",\"api_user\":\"data\",\"subscription_key\":\"data\"}', '{\"gateway\":\"momo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\",\"api_user\":\"data\",\"subscription_key\":\"data\"}', 'payment_config', 'live', 0, NULL, '2023-08-30 04:19:28', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('a8608119-cc76-11ed-9bca-0c7a158e4469', 'moncash', '{\"gateway\":\"moncash\",\"mode\":\"test\",\"status\":\"0\",\"client_id\":\"data\",\"secret_key\":\"data\"}', '{\"gateway\":\"moncash\",\"mode\":\"test\",\"status\":\"0\",\"client_id\":\"data\",\"secret_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:47:34', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('ad5af1c1-d6a2-11ed-962c-0c7a158e4469', 'razor_pay', '{\"gateway\":\"razor_pay\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"api_secret\":\"data\"}', '{\"gateway\":\"razor_pay\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"api_secret\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:47:00', '{\"gateway_title\":\"Razor pay\",\"gateway_image\":null}'),
('ad5b02a0-d6a2-11ed-962c-0c7a158e4469', 'senang_pay', '{\"gateway\":\"senang_pay\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":\"https:\\/\\/url\\/return-senang-pay\",\"secret_key\":\"data\",\"merchant_id\":\"data\"}', '{\"gateway\":\"senang_pay\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":\"https:\\/\\/url\\/return-senang-pay\",\"secret_key\":\"data\",\"merchant_id\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-27 09:58:57', '{\"gateway_title\":\"Senang pay\",\"gateway_image\":null}'),
('b6c333f6-d8e9-11ed-8249-0c7a158e4469', 'akandit_sms', '{\"gateway\":\"akandit_sms\",\"mode\":\"live\",\"status\":0,\"username\":\"\",\"password\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"akandit_sms\",\"mode\":\"live\",\"status\":0,\"username\":\"\",\"password\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('b6c33c87-d8e9-11ed-8249-0c7a158e4469', 'global_sms', '{\"gateway\":\"global_sms\",\"mode\":\"live\",\"status\":0,\"user_name\":\"\",\"password\":\"\",\"from\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"global_sms\",\"mode\":\"live\",\"status\":0,\"user_name\":\"\",\"password\":\"\",\"from\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('b8992bd4-d6a0-11ed-962c-0c7a158e4469', 'paymob_accept', '{\"gateway\":\"paymob_accept\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"\",\"iframe_id\":\"\",\"integration_id\":\"\", \"hmac\": \"\"}', '{\"gateway\":\"paymob_accept\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"\",\"iframe_id\":\"\",\"integration_id\":\"\", \"hmac\": \"\"}', 'payment_config', 'test', 0, NULL, NULL, NULL),
('c41c0dcd-d119-11ed-9f67-0c7a158e4469', 'maxicash', '{\"gateway\":\"maxicash\",\"mode\":\"test\",\"status\":\"0\",\"merchantId\":\"data\",\"merchantPassword\":\"data\"}', '{\"gateway\":\"maxicash\",\"mode\":\"test\",\"status\":\"0\",\"merchantId\":\"data\",\"merchantPassword\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:49:15', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('c9249d17-cd60-11ed-b879-0c7a158e4469', 'pvit', '{\"gateway\":\"pvit\",\"mode\":\"test\",\"status\":\"0\",\"mc_tel_merchant\": \"\",\"access_token\": \"\", \"mc_merchant_code\": \"\"}', '{\"gateway\":\"pvit\",\"mode\":\"test\",\"status\":\"0\",\"mc_tel_merchant\": \"\",\"access_token\": \"\", \"mc_merchant_code\": \"\"}', 'payment_config', 'test', 0, NULL, NULL, NULL),
('cb0081ce-d775-11ed-96f4-0c7a158e4469', 'releans', '{\"gateway\":\"releans\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\",\"from\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"releans\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\",\"from\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, '2023-04-10 02:14:44', NULL),
('d4f3f5f1-d6a0-11ed-962c-0c7a158e4469', 'flutterwave', '{\"gateway\":\"flutterwave\",\"mode\":\"live\",\"status\":\"1\",\"secret_key\":\"data\",\"public_key\":\"data\",\"hash\":\"data\"}', '{\"gateway\":\"flutterwave\",\"mode\":\"live\",\"status\":\"1\",\"secret_key\":\"data\",\"public_key\":\"data\",\"hash\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:41:03', '{\"gateway_title\":\"Flutterwave\",\"gateway_image\":null}'),
('d822f1a5-c864-11ed-ac7a-0c7a158e4469', 'paystack', '{\"gateway\":\"paystack\",\"mode\":\"live\",\"status\":\"1\",\"callback_url\":\"https:\\/\\/api.paystack.co\",\"public_key\":\"data\",\"secret_key\":\"data\",\"merchant_email\":\"data\"}', '{\"gateway\":\"paystack\",\"mode\":\"live\",\"status\":\"1\",\"callback_url\":\"https:\\/\\/api.paystack.co\",\"public_key\":\"data\",\"secret_key\":\"data\",\"merchant_email\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 04:20:45', '{\"gateway_title\":\"Paystack\",\"gateway_image\":null}'),
('daec8d59-c893-11ed-ac7a-0c7a158e4469', 'xendit', '{\"gateway\":\"xendit\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"data\"}', '{\"gateway\":\"xendit\",\"mode\":\"test\",\"status\":\"0\",\"api_key\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:35:46', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('dc0f5fc9-d6a5-11ed-962c-0c7a158e4469', 'worldpay', '{\"gateway\":\"worldpay\",\"mode\":\"test\",\"status\":\"0\",\"OrgUnitId\":\"data\",\"jwt_issuer\":\"data\",\"mac\":\"data\",\"merchantCode\":\"data\",\"xml_password\":\"data\"}', '{\"gateway\":\"worldpay\",\"mode\":\"test\",\"status\":\"0\",\"OrgUnitId\":\"data\",\"jwt_issuer\":\"data\",\"mac\":\"data\",\"merchantCode\":\"data\",\"xml_password\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-12 06:35:26', '{\"gateway_title\":null,\"gateway_image\":\"\"}'),
('e0450278-d8eb-11ed-8249-0c7a158e4469', 'signal_wire', '{\"gateway\":\"signal_wire\",\"mode\":\"live\",\"status\":0,\"project_id\":\"\",\"token\":\"\",\"space_url\":\"\",\"from\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"signal_wire\",\"mode\":\"live\",\"status\":0,\"project_id\":\"\",\"token\":\"\",\"space_url\":\"\",\"from\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('e0450b40-d8eb-11ed-8249-0c7a158e4469', 'paradox', '{\"gateway\":\"paradox\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\"}', '{\"gateway\":\"paradox\",\"mode\":\"live\",\"status\":0,\"api_key\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('ea346efe-cdda-11ed-affe-0c7a158e4469', 'ssl_commerz', '{\"gateway\":\"ssl_commerz\",\"mode\":\"live\",\"status\":\"1\",\"store_id\":\"data\",\"store_password\":\"data\"}', '{\"gateway\":\"ssl_commerz\",\"mode\":\"live\",\"status\":\"1\",\"store_id\":\"data\",\"store_password\":\"data\"}', 'payment_config', 'test', 0, NULL, '2023-08-30 03:43:49', '{\"gateway_title\":\"Ssl commerz\",\"gateway_image\":null}'),
('eed88336-d8ec-11ed-8249-0c7a158e4469', 'hubtel', '{\"gateway\":\"hubtel\",\"mode\":\"live\",\"status\":0,\"sender_id\":\"\",\"client_id\":\"\",\"client_secret\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"hubtel\",\"mode\":\"live\",\"status\":0,\"sender_id\":\"\",\"client_id\":\"\",\"client_secret\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('f149c546-d8ea-11ed-8249-0c7a158e4469', 'viatech', '{\"gateway\":\"viatech\",\"mode\":\"live\",\"status\":0,\"api_url\":\"\",\"api_key\":\"\",\"sender_id\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"viatech\",\"mode\":\"live\",\"status\":0,\"api_url\":\"\",\"api_key\":\"\",\"sender_id\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 0, NULL, NULL, NULL),
('f149cd9c-d8ea-11ed-8249-0c7a158e4469', '019_sms', '{\"gateway\":\"019_sms\",\"mode\":\"live\",\"status\":0,\"password\":\"\",\"username\":\"\",\"username_for_token\":\"\",\"sender\":\"\",\"otp_template\":\"\"}', '{\"gateway\":\"019_sms\",\"mode\":\"live\",\"status\":0,\"password\":\"\",\"username\":\"\",\"username_for_token\":\"\",\"sender\":\"\",\"otp_template\":\"\"}', 'sms_config', 'live', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `advertisements`
--

CREATE TABLE `advertisements` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `readable_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'video_promotion, profile_promotion',
  `is_paid` tinyint(4) NOT NULL DEFAULT '0',
  `start_date` datetime NOT NULL DEFAULT '2024-05-20 20:36:19',
  `end_date` datetime NOT NULL DEFAULT '2024-05-20 20:36:19',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending, approved, running, expired, denied, paused, canceled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_updated` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advertisement_attachments`
--

CREATE TABLE `advertisement_attachments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `advertisement_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_extension_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'promotional_video, provider_profile_image, provider_cover_image',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advertisement_notes`
--

CREATE TABLE `advertisement_notes` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `advertisement_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'paused, denied',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `advertisement_settings`
--

CREATE TABLE `advertisement_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `advertisement_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_details`
--

CREATE TABLE `bank_details` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acc_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acc_holder_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `routing_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `banner_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resource_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bonuses`
--

CREATE TABLE `bonuses` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bonus_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `bonus_amount_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bonus_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `minimum_add_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `maximum_bonus_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `readable_id` bigint(20) NOT NULL,
  `customer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_booking_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `total_tax_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `total_discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `service_schedule` datetime DEFAULT NULL,
  `service_address_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serviceman_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_campaign_discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `total_coupon_discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_checked` tinyint(1) NOT NULL DEFAULT '0',
  `additional_charge` decimal(24,2) NOT NULL DEFAULT '0.00',
  `additional_tax_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `additional_discount_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `additional_campaign_discount_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `removed_coupon_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `evidence_photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `booking_otp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `extra_fee` decimal(24,3) NOT NULL DEFAULT '0.000',
  `total_referral_discount_amount` decimal(24,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_additional_information`
--

CREATE TABLE `booking_additional_information` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_details`
--

CREATE TABLE `booking_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_cost` decimal(24,3) NOT NULL DEFAULT '0.000',
  `quantity` int(11) NOT NULL DEFAULT '1',
  `discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `tax_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `total_cost` decimal(24,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `campaign_discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `overall_coupon_discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_details_amounts`
--

CREATE TABLE `booking_details_amounts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_details_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_unit_cost` decimal(24,2) NOT NULL DEFAULT '0.00',
  `service_quantity` int(11) NOT NULL DEFAULT '0',
  `service_tax` decimal(24,2) NOT NULL DEFAULT '0.00',
  `discount_by_admin` decimal(24,2) NOT NULL DEFAULT '0.00',
  `discount_by_provider` decimal(24,2) NOT NULL DEFAULT '0.00',
  `coupon_discount_by_admin` decimal(24,2) NOT NULL DEFAULT '0.00',
  `coupon_discount_by_provider` decimal(24,2) NOT NULL DEFAULT '0.00',
  `campaign_discount_by_admin` decimal(24,2) NOT NULL DEFAULT '0.00',
  `campaign_discount_by_provider` decimal(24,2) NOT NULL DEFAULT '0.00',
  `admin_commission` decimal(24,2) NOT NULL DEFAULT '0.00',
  `provider_earning` decimal(24,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_offline_payments`
--

CREATE TABLE `booking_offline_payments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_name` text COLLATE utf8mb4_unicode_ci,
  `customer_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_partial_payments`
--

CREATE TABLE `booking_partial_payments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_with` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `due_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_schedule_histories`
--

CREATE TABLE `booking_schedule_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `schedule` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_status_histories`
--

CREATE TABLE `booking_status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `business_settings`
--

CREATE TABLE `business_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `key_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_values` longtext COLLATE utf8mb4_unicode_ci,
  `test_values` longtext COLLATE utf8mb4_unicode_ci,
  `settings_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'live',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `business_settings`
--

INSERT INTO `business_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`) VALUES
('0098459d-9115-4c58-a6f8-becc65d3cb97', 'service_section_image', '\"2023-08-31-64ef2e45070e7.png\"', '\"2023-08-31-64ef2e45070e7.png\"', 'landing_images', 'live', 0, '2022-10-03 17:37:21', '2023-08-30 18:55:49'),
('01b2c108-18fe-4ad0-8693-04be1bfa59aa', 'cancellation_policy', '\"<p>Demo cancellation policy<\\/p>\"', NULL, 'pages_setup', 'live', 0, '2022-08-06 03:54:38', '2022-10-04 11:10:03'),
('03840466-ae1f-429f-90f1-6b724953f4e4', 'referral_discount_validity', '0', '0', 'customer_config', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('053c7a7b-75f0-4e01-9961-5e81fdd001f6', 'email_verification', '1', '1', 'service_setup', 'live', 1, '2022-07-21 11:59:22', '2022-08-13 07:35:03'),
('078c60ff-a05f-4386-88c4-9b48e98dc7dd', 'releans', '{\"gateway\":\"releans\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', '{\"gateway\":\"releans\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', 'sms_config', 'live', 1, '2022-06-08 06:44:58', '2022-10-04 16:26:11'),
('08aa2f9f-e830-4190-87a0-1d57267ab023', 'booking_edit_service_quantity_increase', '{\"booking_edit_service_quantity_increase_status\":\"1\",\"booking_edit_service_quantity_increase_message\":\"Booking Edit Service Quantity Increase\"}', '{\"booking_edit_service_quantity_increase_status\":\"1\",\"booking_edit_service_quantity_increase_message\":\"Booking Edit Service Quantity Increase\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('0a2e5ef2-4e4e-40b1-8ea6-9455a3549891', 'forget_password_verification_method', '\"email\"', '\"email\"', 'business_information', 'live', 1, '2023-05-29 16:22:38', '2023-05-29 16:22:38'),
('0b0ac068-a661-4e13-ab25-858b9f9bab9d', 'currency_code', '\"USD\"', '\"USD\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:24'),
('0bb540c9-9ec6-4977-9284-be250d5b2fc5', 'maintenance_message_setup', '{\"business_number\":1,\"business_email\":1,\"maintenance_message\":\"We are Cooking Up Something Special!\",\"message_body\":\"Sorry for the inconvenience! We are currently undergoing scheduled maintenance to improve our services. We will be back shortly. Thank you for your patience.\"}', NULL, 'maintenance_mode', 'live', 1, '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('0c1bcdd4-d93e-4b4a-9ef9-4b5a15153bdb', 'bidding_post_validity', '0', '0', 'bidding_system', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('0d08217a-f52d-4ce2-a63c-88cd1445193f', 'provider_can_cancel_booking', '\"0\"', '\"0\"', 'provider_config', 'live', 0, '2022-07-20 06:04:17', '2023-08-30 13:09:23'),
('0e2d1635-6f80-4ea0-876b-11f09f16abb8', 'referral_earning', '{\"referral_earning_status\":\"1\",\"referral_earning_message\":\"Refferal Earning\"}', '{\"referral_earning_status\":\"1\",\"referral_earning_message\":\"Refferal Earning\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('0f993fad-b7ba-4fdc-b394-5c34fd37ed68', 'widthdraw_request_deny', '{\"widthdraw_request_deny_status\":\"1\",\"widthdraw_request_deny_message\":\"Withdraw Request Deny\"}', '{\"widthdraw_request_deny_status\":\"1\",\"widthdraw_request_deny_message\":\"Withdraw Request Deny\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('11c73e6c-f776-49f5-8857-f46fd08f5bcd', 'additional_charge_fee_amount', '0', '0', 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('11d4f10d-2881-4c7d-baa6-e00ff603bc4b', 'social_media', '[{\"id\":\"c96083cd-cbb0-415b-a215-b77da4c293c9\",\"media\":\"facebook\",\"link\":\"https:\\/\\/www.facebook.com\"},{\"id\":\"8cc641e5-019d-4760-b912-efa39d937635\",\"media\":\"instagram\",\"link\":\"https:\\/\\/www.instagram.com\"},{\"id\":\"0f104f7c-f0cb-4933-b35b-c04f829a682d\",\"media\":\"linkedin\",\"link\":\"https:\\/\\/www.linkedin.com\"},{\"id\":\"793d22b1-aa6a-48c3-a654-47dc799532a7\",\"media\":\"youtube\",\"link\":\"https:\\/\\/www.youtube.com\"}]', '[{\"id\":\"c96083cd-cbb0-415b-a215-b77da4c293c9\",\"media\":\"facebook\",\"link\":\"https:\\/\\/www.facebook.com\"},{\"id\":\"8cc641e5-019d-4760-b912-efa39d937635\",\"media\":\"instagram\",\"link\":\"https:\\/\\/www.instagram.com\"},{\"id\":\"0f104f7c-f0cb-4933-b35b-c04f829a682d\",\"media\":\"linkedin\",\"link\":\"https:\\/\\/www.linkedin.com\"},{\"id\":\"793d22b1-aa6a-48c3-a654-47dc799532a7\",\"media\":\"youtube\",\"link\":\"https:\\/\\/www.youtube.com\"}]', 'landing_social_media', 'live', 0, '2023-08-30 19:27:38', '2023-08-30 19:28:46'),
('11dddc2e-2b89-4bcc-9d48-1d3071dc37d6', 'booking_complete', '{\"booking_complete_status\":\"1\",\"booking_complete_message\":\"Booking Complete\"}', '{\"booking_complete_status\":\"1\",\"booking_complete_message\":\"Booking Complete\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('14290db3-1876-44a3-a70d-1410d753d673', 'campaign_cost_bearer', '{\"bearer\":\"provider\",\"admin_percentage\":0,\"provider_percentage\":100,\"type\":\"campaign\"}', '{\"bearer\":\"provider\",\"admin_percentage\":0,\"provider_percentage\":100,\"type\":\"campaign\"}', 'promotional_setup', 'live', 1, '2023-01-22 17:33:48', '2023-01-22 17:33:48'),
('14eaf75b-68f2-412b-8062-189cff9582cc', 'app_url_appstore', '\"\\/\"', '\"\\/\"', 'landing_button_and_links', 'live', 1, '2022-10-03 16:00:01', '2022-10-04 16:22:24'),
('16212625-a1cb-428e-b201-1975495a32cc', 'provider_self_registration', '\"0\"', '\"0\"', 'provider_config', 'live', 0, '2022-07-21 11:59:22', '2023-08-30 13:09:23'),
('16ee9973-8d7f-4984-9357-3490fdb8fe04', 'otp', '{\"otp_status\":\"1\",\"otp_message\":\"Confirmation OTP\"}', '{\"otp_status\":\"1\",\"otp_message\":\"Confirmation OTP\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('18ff5091-1416-4b68-8a11-4a4db54d94bc', 'rating_review', '{\"push_notification_rating_review\":\"1\",\"email_rating_review\":\"1\"}', '{\"push_notification_rating_review\":\"1\",\"email_rating_review\":\"1\"}', 'notification_settings', 'live', 1, '2022-06-06 12:41:28', '2022-08-16 07:43:35'),
('193e005b-a715-4f6f-97cc-2554377b1f28', 'app_url_playstore', '\"\\/\"', '\"\\/\"', 'landing_button_and_links', 'live', 1, '2022-10-03 16:00:01', '2022-10-04 16:22:24'),
('1a4ff5aa-2956-458d-96e6-e0847635910c', 'referral_earning_first_booking', '{\"referral_earning_first_booking_status\":\"1\",\"referral_earning_first_booking_message\":\"Referral Earning First Booking\"}', '{\"referral_earning_first_booking_status\":\"1\",\"referral_earning_first_booking_message\":\"Referral Earning First Booking\"}', 'customer_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('1acfd678-38f4-4aab-8431-7f066e8de7f2', 'phone_verification', '0', '0', 'service_setup', 'live', 1, '2023-05-29 16:22:38', '2023-05-29 16:22:38'),
('1bb8c72b-20b2-4b89-82d3-c2ead7cf0675', 'serviceman_can_edit_booking', '0', '0', 'serviceman_config', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('1bc292a4-4244-4eb2-8760-5c0bd4d5e236', 'default_commission', '\"20\"', '\"20\"', 'business_information', 'live', 1, '2022-08-18 09:14:58', '2022-08-24 02:53:43'),
('1bfbbb5c-5786-491e-89ed-d366b2a84e30', 'customer_notification_for_provider_bid_withdraw', '{\"customer_notification_for_provider_bid_withdraw_status\":\"1\",\"customer_notification_for_provider_bid_withdraw_message\":\"Customer notification for provider bid withdraw\"}', '{\"customer_notification_for_provider_bid_withdraw_status\":\"1\",\"customer_notification_for_provider_bid_withdraw_message\":\"Customer notification for provider bid withdraw\"}', 'customer_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('1c7e7c69-dd9d-4e7b-9379-b5314bb6ec58', 'testimonial', '[{\"id\":\"94f7a473-e9fb-48ec-8bd3-46a0229b3aef\",\"name\":\"Mike\",\"designation\":\"Designer\",\"review\":\"Thank you! That was very helpful! The Service men were very professionals & very caution about safety\",\"image\":\"2023-08-31-64ef36f7190ba.png\"}]', '[{\"id\":\"94f7a473-e9fb-48ec-8bd3-46a0229b3aef\",\"name\":\"Mike\",\"designation\":\"Designer\",\"review\":\"Thank you! That was very helpful! The Service men were very professionals & very caution about safety\",\"image\":\"2023-08-31-64ef36f7190ba.png\"}]', 'landing_testimonial', 'live', 1, '2022-10-03 16:54:42', '2023-08-30 19:33:13'),
('1cbe46a3-9f79-47e1-af47-0868c3da2727', 'booking_edit_service_quantity_decrease', '{\"booking_edit_service_quantity_decrease_status\":\"1\",\"booking_edit_service_quantity_decrease_message\":\"Booking Edit Service Quantity Decrease\"}', '{\"booking_edit_service_quantity_decrease_status\":\"1\",\"booking_edit_service_quantity_decrease_message\":\"Booking Edit Service Quantity Decrease\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('1e8316f2-660a-44c5-b24c-97320ae212d0', 'booking_service_complete', '{\"booking_service_complete_status\":\"1\",\"booking_service_complete_message\":\"Booking Service successfully complete done\"}', '{\"booking_service_complete_status\":\"1\",\"booking_service_complete_message\":\"Booking Service successfully complete done\"}', 'notification_messages', 'live', 1, '2022-06-06 12:41:28', '2022-09-14 17:44:04'),
('26b9dcd8-b8cd-4cc0-9ab4-2705993c31a4', 'booking_cancel', '{\"booking_cancel_status\":\"1\",\"booking_cancel_message\":\"Booking Cancel\"}', '{\"booking_cancel_status\":\"1\",\"booking_cancel_message\":\"Booking Cancel\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('27663114-d661-4eab-9eb5-6852bb3b7c7c', 'booking_edit_service_remove', '{\"booking_edit_service_remove_status\":\"1\",\"booking_edit_service_remove_message\":\"Booking Edit Service Remove\"}', '{\"booking_edit_service_remove_status\":\"1\",\"booking_edit_service_remove_message\":\"Booking Edit Service Remove\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('2b0ce7e1-ba62-437c-9f5b-cd9ccd12ea1d', 'booking_accepted', '{\"booking_accepted_status\":\"1\",\"booking_accepted_message\":\"Booking Accepted\"}', '{\"booking_accepted_status\":\"1\",\"booking_accepted_message\":\"Booking Accepted\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('2c0c6614-1990-4f6f-9c2d-c4481b8c18ff', 'service_complete_photo_evidence', '0', '0', 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('2dd9ca52-ebd2-45b4-9d38-30a6a16b44ca', 'top_title', '\"Customer Statisfaciton is our main moto\"', '\"Customer Statisfaciton is our main moto\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('2dfa523c-65b0-478b-aa31-cf024151e8d9', 'serviceman_assign', '{\"serviceman_assign_status\":\"1\",\"serviceman_assign_message\":\"Serviceman Assign\"}', '{\"serviceman_assign_status\":\"1\",\"serviceman_assign_message\":\"Serviceman Assign\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('303e1187-edf1-4e6c-9a0e-e5f57efe3013', 'booking_otp', '0', '0', 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('30d0ed00-b027-45bb-aaee-ae5982e8c97b', 'add_to_fund_wallet', '0', '0', 'customer_config', 'live', 1, '2023-12-28 14:00:58', '2023-12-28 14:00:58'),
('31c5e759-3d31-4522-8ed7-2a067c623c68', 'booking_place', '{\"booking_place_status\":\"1\",\"booking_place_message\":\"Booking Service successfully placed\"}', '{\"booking_place_status\":\"1\",\"booking_place_message\":\"Booking Service successfully placed\"}', 'notification_messages', 'live', 1, '2022-06-06 12:41:28', '2022-10-04 16:23:49'),
('330878fe-66b8-4c86-b9f3-c69d7b7ab394', 'partial_payment', '0', '0', 'service_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('35fdbc13-5505-4f08-a480-9a7922fde375', 'senang_pay', '{\"gateway\":\"senang_pay\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":\"https:\\/\\/url\\/return-senang-pay\",\"secret_key\":\"data\",\"merchant_id\":\"data\"}', '{\"gateway\":\"senang_pay\",\"mode\":\"live\",\"status\":\"0\",\"callback_url\":\"https:\\/\\/url\\/return-senang-pay\",\"secret_key\":\"data\",\"merchant_id\":\"data\"}', 'payment_config', 'live', 0, '2022-06-09 07:21:16', '2022-10-04 16:28:53'),
('382abfc4-4742-4080-9f17-350bbc57d813', 'booking_cancel', '{\"booking_cancel_status\":\"0\",\"booking_cancel_message\":\"Booking Cancel Successfully\"}', '{\"booking_cancel_status\":\"0\",\"booking_cancel_message\":\"Booking Cancel Successfully\"}', 'notification_messages', 'live', 0, '2022-06-06 12:41:28', '2022-09-14 20:11:36'),
('38c100f5-dc8c-4cf0-af94-855b607f6504', 'min_payable_amount', '0', '0', 'provider_config', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('3a9cf40c-c7ec-481c-8a79-5f33b154a561', 'email_config', '{\"mailer_name\":\"name\",\"host\":\"mail.host.com\",\"driver\":\"smtp\",\"port\":\"587\",\"user_name\":\"demo@6am.one\",\"email_id\":\"email@email.com\",\"encryption\":\"tls\",\"password\":\"password\"}', '{\"mailer_name\":\"name\",\"host\":\"mail.host.com\",\"driver\":\"smtp\",\"port\":\"587\",\"user_name\":\"demo@6am.one\",\"email_id\":\"email@email.com\",\"encryption\":\"tls\",\"password\":\"password\"}', 'email_config', 'live', 1, '2022-06-07 12:32:47', '2022-10-04 16:25:45'),
('3b0b4644-9ba9-48e9-8622-59426198e3b9', 'time_zone', '\"Pacific\\/Fakaofo\"', '\"Pacific\\/Fakaofo\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-09-14 06:58:36'),
('3cee349b-0095-4eec-8da3-5db955242f7b', 's3_storage_credentials', '\"{\\\"key\\\":\\\"\\\",\\\"secret\\\":\\\"\\\",\\\"region\\\":\\\"\\\",\\\"bucket\\\":\\\"\\\",\\\"url\\\":\\\"\\\",\\\"endpoint\\\":\\\"\\\",\\\"path\\\":\\\"\\\"}\"', '\"{\\\"key\\\":\\\"\\\",\\\"secret_credential\\\":\\\"\\\",\\\"region\\\":\\\"\\\",\\\"bucket\\\":\\\"\\\",\\\"url\\\":\\\"\\\",\\\"endpoint\\\":\\\"\\\",\\\"path\\\":\\\"\\\"}\"', 'storage_settings', 'live', 1, '2024-07-07 16:15:01', '2024-07-07 16:15:01'),
('3d51e47f-da99-4757-8392-4d6ab9e37fc3', 'refund', '{\"refund_status\":\"1\",\"refund_message\":\"Refund\"}', '{\"refund_status\":\"1\",\"refund_message\":\"Refund\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('3d8dfac5-f187-45ee-a3ba-17fa75224bab', 'booking_edit_service_quantity_increase', '{\"booking_edit_service_quantity_increase_status\":\"1\",\"booking_edit_service_quantity_increase_message\":\"Booking Edit Service Quantity Increase\"}', '{\"booking_edit_service_quantity_increase_status\":\"1\",\"booking_edit_service_quantity_increase_message\":\"Booking Edit Service Quantity Increase\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('3dd386b8-066c-48c3-af22-f3f197347ea3', 'customer_wallet', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16'),
('3e0fe0fa-e697-437e-a0a9-9ff4316f4d39', 'about_us_description', '\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s,\"', '\"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s,\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('3e1a01ef-ec49-40a7-93b1-a15499028e32', 'bidding_status', '0', '0', 'bidding_system', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('3e4c8b9f-28a2-4f72-b3b5-8b5808121dc8', 'recaptcha', '{\"party_name\":\"recaptcha\",\"status\":\"0\",\"site_key\":\"apikey\",\"secret_key\":\"apikey\"}', '{\"party_name\":\"recaptcha\",\"status\":\"0\",\"site_key\":\"apikey\",\"secret_key\":\"apikey\"}', 'third_party', 'live', 0, '2022-07-25 10:57:25', '2022-10-04 16:24:50'),
('4007291b-5078-42ba-9051-fe9c991b9b2f', 'mid_title', '\"SERVICE WE PROVIDE FOR YOU\"', '\"SERVICE WE PROVIDE FOR YOU\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('43c89209-172b-47a1-851a-efb90f848aa0', 'top_image_1', '\"2023-08-31-64ef29058d28b.png\"', '\"2023-08-31-64ef29058d28b.png\"', 'landing_images', 'live', 0, '2022-10-03 16:06:10', '2023-08-30 18:33:25'),
('45721749-c637-498c-ad0b-5d369a2d6425', 'cookies_text', '\"lorem ipsum dollar\"', '\"lorem ipsum dollar\"', 'business_information', 'live', 1, '2023-02-23 00:25:16', '2023-08-30 19:38:59'),
('45825020-d72d-4d7c-8444-72b790507705', 'referral_discount_validity_type', '\"day\"', '\"day\"', 'customer_config', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('47817d69-8ec9-4730-b81f-838c4fc9d533', 'top_image_3', '\"2023-08-31-64ef2a3b9f089.png\"', '\"2023-08-31-64ef2a3b9f089.png\"', 'landing_images', 'live', 0, '2022-10-03 16:06:15', '2023-08-30 18:38:35'),
('47cbf32d-06bf-40ae-980c-3053e7f1a4ad', 'ongoing_booking', '{\"ongoing_booking_status\":\"1\",\"ongoing_booking_message\":\"Ongoing Booking\"}', '{\"ongoing_booking_status\":\"1\",\"ongoing_booking_message\":\"Ongoing Booking\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('49146edb-2e7a-4280-ac19-de35c2f894f9', 'min_booking_amount', '0', '0', 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('49697d57-c686-4d30-9398-9c5533595b7f', 'provider_bid_request_denied', '{\"provider_bid_request_denied_status\":\"1\",\"provider_bid_request_denied_message\":\"Provider Bid Request Denied\"}', '{\"provider_bid_request_denied_status\":\"1\",\"provider_bid_request_denied_message\":\"Provider Bid Request Denied\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('4ad0c0ce-157c-46b2-b93d-261ca4f1a575', 'top_image_4', '\"2023-08-31-64ef2a53b2182.png\"', '\"2023-08-31-64ef2a53b2182.png\"', 'landing_images', 'live', 0, '2022-10-03 16:07:26', '2023-08-30 18:38:59'),
('4cdc5c2e-054d-485b-a906-f0c6032880db', 'subscription', '{\"push_notification_subscription\":\"1\",\"email_subscription\":\"1\"}', '{\"push_notification_subscription\":\"1\",\"email_subscription\":\"1\"}', 'notification_settings', 'live', 1, '2022-06-06 12:41:28', '2022-08-16 07:43:35'),
('4d69c64d-7956-4fd5-bdc0-59062152899d', 'max_booking_amount', '0', '0', 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('4dc4001c-2294-4ccf-8f02-8e6457cb260e', 'provider_suspend', '{\"provider_suspend_status\":\"1\",\"provider_suspend_message\":\"Provider Suspend\"}', '{\"provider_suspend_status\":\"1\",\"provider_suspend_message\":\"Provider Suspend\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('4dcb5aae-3a76-4754-b23a-286726a60d61', 'business_logo', '\"2023-08-31-64ef387568527.png\"', '\"2023-08-31-64ef387568527.png\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2023-08-30 19:39:17'),
('4ddd1410-e290-4e3f-a66c-ee70f86368db', 'bottom_title', '\"GET ALL UPDATES & EXCITING NEWS\"', '\"GET ALL UPDATES & EXCITING NEWS\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 16:14:45'),
('4e1af097-855e-43b8-ae10-0a0039039706', 'booking_accepted', '{\"booking_accepted_status\":\"0\",\"booking_accepted_message\":\"Booking Service successfully complete done\"}', '{\"booking_accepted_status\":\"0\",\"booking_accepted_message\":\"Booking Service successfully complete done\"}', 'notification_messages', 'live', 0, '2022-06-06 12:41:28', '2022-10-04 16:23:59'),
('4e6fda04-d5b3-4ddd-a086-3c6567346e5c', 'tnc_update', '{\"push_notification_tnc_update\":\"0\",\"email_tnc_update\":0}', '{\"push_notification_tnc_update\":\"0\",\"email_tnc_update\":0}', 'notification_settings', 'live', 1, '2022-06-06 12:41:28', '2022-10-04 16:23:28'),
('4e816e5e-ea8d-4d0e-bb57-5015c57be62a', 'system_language', '[{\"id\":1,\"name\":\"english\",\"direction\":\"ltr\",\"code\":\"en\",\"status\":1,\"default\":true}]', '[{\"id\":1,\"name\":\"english\",\"direction\":\"ltr\",\"code\":\"en\",\"status\":1,\"default\":true}]', 'business_information', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('514c7284-cab8-42a2-9f48-82e779d7afdd', 'direct_provider_booking', '\"0\"', '\"0\"', 'business_information', 'live', 1, '2023-08-30 19:38:42', '2023-08-30 19:38:42'),
('539b1d42-0730-418d-83b0-46911e42cc39', 'privacy_policy', '\"\\\"<p>Demo privacy policy<\\/p>\\\"\"', NULL, 'pages_setup', 'live', 1, '2022-08-06 04:00:09', '2022-09-08 14:37:37'),
('54b42ef4-2f17-4424-aebc-7e5490b97557', 'bottom_description', '\"Subcribe to out newsletters to receive all the latest activty we provide for you\"', '\"Subcribe to out newsletters to receive all the latest activty we provide for you\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 16:14:45'),
('54b77f94-c818-41cc-afff-9200213a541f', 'booking_edit_service_remove', '{\"booking_edit_service_remove_status\":\"1\",\"booking_edit_service_remove_message\":\"Booking Edit Service Remove\"}', '{\"booking_edit_service_remove_status\":\"1\",\"booking_edit_service_remove_message\":\"Booking Edit Service Remove\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('5675c00e-9686-4032-96f9-ce4631b9960a', 'speciality', '[{\"id\":\"c3392f65-51ea-460a-b503-41a6e986971e\",\"title\":\"Speciality\",\"description\":\"Speciality description\",\"image\":\"2023-08-31-64ef3bcbbeb55.png\"}]', '[{\"id\":\"c3392f65-51ea-460a-b503-41a6e986971e\",\"title\":\"Speciality\",\"description\":\"Speciality description\",\"image\":\"2023-08-31-64ef3bcbbeb55.png\"}]', 'landing_speciality', 'live', 1, '2022-10-03 18:13:41', '2023-08-30 19:53:45'),
('57b3d923-139a-430b-a7b8-2c0797110cfd', 'sms_verification', '1', '1', 'service_setup', 'live', 1, '2022-07-21 11:59:22', '2022-08-13 07:35:03'),
('5961e8be-4f8a-413b-a041-4aad8a4b0f59', 'email_config_status', '1', '1', 'email_config', 'live', 1, '2024-07-13 10:35:13', '2024-07-13 10:35:13'),
('59d855e6-06ad-487a-9ca1-bbd8d3db2dfa', 'stripe', '{\"gateway\":\"stripe\",\"mode\":\"test\",\"status\":\"1\",\"api_key\":\"data\",\"published_key\":\"data\"}', '{\"gateway\":\"stripe\",\"mode\":\"test\",\"status\":\"1\",\"api_key\":\"data\",\"published_key\":\"data\"}', 'payment_config', 'test', 1, '2022-06-09 05:41:48', '2022-10-04 16:28:57'),
('59f6e3d4-382f-47e6-b973-2c1cb2054016', 'digital_payment', '1', '1', 'service_setup', 'live', 1, '2023-05-29 16:22:38', '2023-05-29 16:22:38'),
('5b7601e2-d34f-45ac-9f69-6b01585c5001', 'advertisement_resumed', '{\"advertisement_resumed_status\":\"1\",\"advertisement_resumed_message\":\"Advertisement Resumed\"}', '{\"advertisement_resumed_status\":\"1\",\"advertisement_resumed_message\":\"Advertisement Resumed\"}', 'provider_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('5d8972d5-ae9b-47cd-b76c-011923ea8e54', 'booking_edit_service_remove', '{\"booking_edit_service_remove_status\":\"1\",\"booking_edit_service_remove_message\":\"Booking Edit Service Remove\"}', '{\"booking_edit_service_remove_status\":\"1\",\"booking_edit_service_remove_message\":\"Booking Edit Service Remove\"}', 'serviceman_notification', 'live', 1, '2023-12-28 14:00:58', '2023-12-28 14:00:58'),
('5e470acd-7935-4394-ab56-008fdeb65029', 'third_party', '{\"party_name\":\"push_notification\",\"server_key\":\"56789fghjk\"}', '{\"party_name\":\"push_notification\",\"server_key\":\"56789fghjk\"}', 'third_party', 'live', 1, '2022-06-08 10:57:43', '2022-06-08 10:57:43'),
('6170b133-2556-4eb0-b5bf-3352566e5c83', 'booking_ongoing', '{\"booking_ongoing_status\":\"0\",\"booking_ongoing_message\":\"Booking Service successfully complete done\"}', '{\"booking_ongoing_status\":\"0\",\"booking_ongoing_message\":\"Booking Service successfully complete done\"}', 'notification_messages', 'live', 0, '2022-10-04 16:24:02', '2022-10-04 16:24:02'),
('63eb11ab-b34d-4bfc-a55f-e807c1974f41', 'advertisement_denied', '{\"advertisement_denied_status\":\"1\",\"advertisement_denied_message\":\"Advertisement Denied\"}', '{\"advertisement_denied_status\":\"1\",\"advertisement_denied_message\":\"Advertisement Denied\"}', 'provider_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('65e9c5de-c37d-4dc8-bc6a-011784784176', 'partial_payment_combinator', '\"all\"', '\"all\"', 'service_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('668cdb3b-63a4-4ad4-ac35-407c811576b6', 'flutterwave', '{\"gateway\":\"flutterwave\",\"mode\":\"test\",\"status\":\"1\",\"secret_key\":\"data\",\"public_key\":\"data\",\"hash\":\"data\"}', '{\"gateway\":\"flutterwave\",\"mode\":\"test\",\"status\":\"1\",\"secret_key\":\"data\",\"public_key\":\"data\",\"hash\":\"data\"}', 'payment_config', 'test', 1, '2022-09-03 08:47:46', '2022-10-04 16:29:07'),
('694b2b7f-24a9-43b7-abaa-f73736e5873d', 'serviceman_assign', '{\"serviceman_assign_status\":\"1\",\"serviceman_assign_message\":\"Serviceman Assign\"}', '{\"serviceman_assign_status\":\"1\",\"serviceman_assign_message\":\"Serviceman Assign\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('698dadfe-91b6-4b1b-9799-c6620f4bb0dc', 'booking_edit_service_quantity_increase', '{\"booking_edit_service_quantity_increase_status\":\"1\",\"booking_edit_service_quantity_increase_message\":\"Booking Edit Service Quantity Increase\"}', '{\"booking_edit_service_quantity_increase_status\":\"1\",\"booking_edit_service_quantity_increase_message\":\"Booking Edit Service Quantity Increase\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('6a46c913-5d22-48bb-85ba-161defad284e', 'add_fund_wallet_bonus', '{\"add_fund_wallet_bonus_status\":\"1\",\"add_fund_wallet_bonus_message\":\"Add Fund Wallet Bonus\"}', '{\"add_fund_wallet_bonus_status\":\"1\",\"add_fund_wallet_bonus_message\":\"Add Fund Wallet Bonus\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('6b208b3b-b474-4010-ab1c-57a708ef1a0d', 'customer_notification_for_provider_bid_offer', '{\"customer_notification_for_provider_bid_offer_status\":\"1\",\"customer_notification_for_provider_bid_offer_message\":\"customer notification for provider bid offer\"}', '{\"customer_notification_for_provider_bid_offer_status\":\"1\",\"customer_notification_for_provider_bid_offer_message\":\"customer notification for provider bid offer\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('6c8aa862-cd24-489c-bcc7-b5d0361c6b18', 'advanced_booking_restriction_value', '3', '3', 'booking_setup', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('6d3c9600-0fd9-4ebe-a740-22c21c90f619', 'pp_update', '{\"push_notification_pp_update\":\"0\",\"email_pp_update\":0}', '{\"push_notification_pp_update\":\"0\",\"email_pp_update\":0}', 'notification_settings', 'live', 1, '2022-06-06 12:41:28', '2022-10-04 16:23:30'),
('6f654cb0-3fda-47ff-9084-e2f5db1fcc94', 'referral_based_new_user_discount', '0', '0', 'customer_config', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('6fcf21de-e1c8-4bd4-ab2c-dff708e79277', 'currency_symbol_position', '\"left\"', '\"left\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-09-14 06:07:12'),
('7014ab21-7c89-4f6f-9e63-05806292802f', 'customer_loyalty_point', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16'),
('72189cb6-4608-4311-8b0c-25ef39265566', 'advertisement_approved', '{\"advertisement_approved_status\":\"1\",\"advertisement_approved_message\":\"Advertisement Approved\"}', '{\"advertisement_approved_status\":\"1\",\"advertisement_approved_message\":\"Advertisement Approved\"}', 'provider_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('73a38ec7-5b60-402f-a17b-6e308c1b3cf7', 'provider_can_edit_booking', '0', '0', 'provider_config', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('749dc8fa-b2f0-4236-a400-f52fe3b8193b', 'refund_policy', '\"<p>Demo refund policy<\\/p>\"', NULL, 'pages_setup', 'live', 1, '2022-08-06 04:02:38', '2022-09-08 14:37:27'),
('790b2df0-f099-48b2-999e-b74d48a2fe07', 'ongoing_booking', '{\"ongoing_booking_status\":\"1\",\"ongoing_booking_message\":\"Ongoing Booking\"}', '{\"ongoing_booking_status\":\"1\",\"ongoing_booking_message\":\"Ongoing Booking\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('7a097aeb-c48d-4243-9217-85baf4dfdb0d', 'advertisement_created_by_admin', '{\"advertisement_created_by_admin_status\":\"1\",\"advertisement_created_by_admin_message\":\"Advertisement Created By Admin\"}', '{\"advertisement_created_by_admin_status\":\"1\",\"advertisement_created_by_admin_message\":\"Advertisement Created By Admin\"}', 'provider_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('7a2d5afd-8957-41c2-8269-14cdce19f432', 'privacy_and_policy_update', '{\"privacy_and_policy_update_status\":\"1\",\"privacy_and_policy_update_message\":\"Privacy And Policy Update\"}', '{\"privacy_and_policy_update_status\":\"1\",\"privacy_and_policy_update_message\":\"Privacy And Policy Update\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('7a834fe4-6d3e-48c9-a707-aa8f4e849375', 'header_background', '\"#e3f2fc\"', '\"#e3f2fc\"', 'landing_background', 'live', 0, '2023-08-30 19:23:47', '2023-08-30 19:23:47'),
('7be92742-c947-4786-ae8c-848798da7b08', 'referral_discount_amount', '0', '0', 'customer_config', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('7d347217-930c-4e03-a696-063fd2c80baa', 'maintenance_system_setup', '[]', NULL, 'maintenance_mode', 'live', 1, '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('7eb3ac41-7c85-4877-8eae-6580c2ad6daa', 'subscription_vat', '0', '0', 'subscription_Setting', 'live', 1, '2024-07-09 18:57:16', '2024-07-09 18:57:16'),
('7f266d17-6867-499f-bad6-9a8b55ab3ff1', 'order', '{\"push_notification_order\":\"1\",\"email_order\":\"1\"}', '{\"push_notification_order\":\"1\",\"email_order\":\"1\"}', 'notification_settings', 'live', 1, '2022-06-06 12:41:28', '2022-07-23 07:08:34'),
('7fe3d581-d7c3-46e1-b804-c97efefcfeba', 'suspend_on_exceed_cash_limit_provider', '0', '0', 'provider_config', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('815f1823-9a67-4248-a62c-17eed4388745', 'service_request_deny', '{\"service_request_deny_status\":\"1\",\"service_request_deny_message\":\"Service Request Reject\"}', '{\"service_request_deny_status\":\"1\",\"service_request_deny_message\":\"Service Request Reject\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('81fa30e8-24d5-485b-90fc-a4995718b91e', 'footer_background', '\"#001f35\"', '\"#001f35\"', 'landing_background', 'live', 0, '2023-08-30 19:23:47', '2023-08-30 19:24:38'),
('843ace86-ec16-46fa-8260-ba1d1e4e0eff', 'maintenance_mode', '0', NULL, 'maintenance_mode', 'live', 1, '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('848492c2-b2c9-4fed-b486-77db4ff141ae', 'msg91', '{\"gateway\":\"msg91\",\"mode\":\"live\",\"status\":\"0\",\"template_id\":\"data\",\"auth_key\":\"data\"}', '{\"gateway\":\"msg91\",\"mode\":\"live\",\"status\":\"0\",\"template_id\":\"data\",\"auth_key\":\"data\"}', 'sms_config', 'live', 0, '2022-06-08 09:06:49', '2022-10-04 16:26:16'),
('85dc4d4a-d25c-492f-b030-17723bcbf95b', 'booking_place', '{\"booking_place_status\":\"1\",\"booking_place_message\":\"Booking Place\"}', '{\"booking_place_status\":\"1\",\"booking_place_message\":\"Booking Place\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('873986dd-541b-4648-bd32-edac7504143e', 'nexmo', '{\"gateway\":\"nexmo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\",\"api_secret\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', '{\"gateway\":\"nexmo\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\",\"api_secret\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', 'sms_config', 'live', 0, '2022-06-08 07:19:18', '2022-10-04 16:26:27'),
('894ed562-3c8f-4ff6-9e92-5c524c2e4802', 'advanced_booking_restriction_type', '\"hour\"', '\"hour\"', 'booking_setup', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('89969f42-1340-4e1e-9d78-153495bf5c5f', 'firebase_otp_verification', '{\"party_name\":\"firebase_otp_verification\",\"status\":0,\"web_api_key\":\"\"}', NULL, 'third_party', 'live', 0, '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('89d237b1-861e-4066-9cb6-c9adfd672726', 'schedule_booking', '1', '1', 'service_setup', 'live', 1, '2022-07-20 06:04:14', '2022-08-13 07:35:03'),
('8c296af0-65c5-43f9-aa4a-854cbbf19148', 'pagination_limit', '\"20\"', '\"20\"', 'business_information', 'live', 1, '2022-09-05 10:06:02', '2022-10-04 16:21:24'),
('8d6b0e16-f753-4833-8f79-7d049a50deb7', 'registration_description', '\"Become e provider & Start your own business online with on demand service platform\"', '\"Become e provider & Start your own business online with on demand service platform\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('8dddf0eb-2021-4d16-9d84-687603f71285', 'coupon_cost_bearer', '{\"bearer\":\"provider\",\"admin_percentage\":0,\"provider_percentage\":100,\"type\":\"coupon\"}', '{\"bearer\":\"provider\",\"admin_percentage\":0,\"provider_percentage\":100,\"type\":\"coupon\"}', 'promotional_setup', 'live', 1, '2023-01-22 17:33:48', '2023-01-22 17:33:48'),
('8e6dfa1d-1b51-456e-a35a-ae58eb81baee', 'additional_charge_label_name', NULL, NULL, 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('8fa88ebf-5aac-4ea5-b87d-aff994f77d0e', 'provider_self_delete', '1', '1', 'provider_config', 'live', 1, '2023-11-28 15:33:20', '2023-11-28 15:33:20'),
('916547ea-8840-4616-9b52-78f6159b1ca6', 'schedule_booking_time_restriction', '1', '1', 'booking_setup', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('92043fa7-f54f-4733-9dbf-3719970a5b62', 'paytm', '{\"gateway\":\"paytm\",\"mode\":\"test\",\"status\":\"1\",\"merchant_key\":\"data\",\"merchant_id\":\"data\",\"merchant_website_link\":\"data\"}', '{\"gateway\":\"paytm\",\"mode\":\"test\",\"status\":\"1\",\"merchant_key\":\"data\",\"merchant_id\":\"data\",\"merchant_website_link\":\"data\"}', 'payment_config', 'test', 1, '2022-06-09 07:21:49', '2022-10-04 16:29:15'),
('925c9c9e-d2a4-41bc-b9d2-ff16fed80ce3', 'body_background', '\"#fcfcfc\"', '\"#fcfcfc\"', 'landing_background', 'live', 0, '2023-08-30 19:23:47', '2023-08-30 19:25:22'),
('9288358a-cab7-4c5c-b342-75b7ab17c29a', 'business_phone', '\"000000\"', '\"000000\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:24'),
('92ba62c6-17e3-4f30-a99b-0ee6dfe46628', 'booking_edit_service_quantity_decrease', '{\"booking_edit_service_quantity_decrease_status\":\"1\",\"booking_edit_service_quantity_decrease_message\":\"Booking Edit Service Quantity Decrease\"}', '{\"booking_edit_service_quantity_decrease_status\":\"1\",\"booking_edit_service_quantity_decrease_message\":\"Booking Edit Service Quantity Decrease\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('93670ab4-e54d-46ea-a8ed-101cb968208e', 'about_us_title', '\"WHO WE ARE\"', '\"WHO WE ARE\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('94f715e7-164d-4e05-bb64-e3547d94a5e4', 'business_address', '\"Dhaka Bangladesh\"', '\"Dhaka Bangladesh\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:24'),
('973e0fe3-b6ff-4156-aa9a-78e52e069527', 'booking', '{\"push_notification_booking\":\"0\",\"email_booking\":0}', '{\"push_notification_booking\":\"0\",\"email_booking\":0}', 'notification_settings', 'live', 1, '2022-07-28 04:31:15', '2022-10-04 16:23:32'),
('98a973f9-0ec8-4fe5-b31d-d04c045bab41', 'referral_value_per_currency_unit', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16'),
('98e2f206-5314-4fc6-9625-95568bbc2771', 'serviceman_can_cancel_booking', '0', '0', 'serviceman_config', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('998b0da0-268b-4354-8855-a40f266a2c84', 'referral_code_used', '{\"referral_code_used_status\":\"1\",\"referral_code_used_message\":\"Referral code used\"}', '{\"referral_code_used_status\":\"1\",\"referral_code_used_message\":\"Referral code used\"}', 'customer_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('9a961fd3-b032-4260-a9dd-93ad7c0b76b8', 'paystack', '{\"gateway\":\"paystack\",\"mode\":\"test\",\"status\":\"1\",\"callback_url\":\"https:\\/\\/api.paystack.co\",\"public_key\":\"data\",\"secret_key\":\"data\",\"merchant_email\":\"data\"}', '{\"gateway\":\"paystack\",\"mode\":\"test\",\"status\":\"1\",\"callback_url\":\"https:\\/\\/api.paystack.co\",\"public_key\":\"data\",\"secret_key\":\"data\",\"merchant_email\":\"data\"}', 'payment_config', 'test', 1, '2022-06-09 06:12:45', '2022-10-04 16:29:25'),
('9ba99828-e514-4b9a-89f7-a07daad49b2c', 'free_trial_period', '7', '7', 'subscription_Setting', 'live', 1, '2024-07-09 18:57:16', '2024-07-09 18:57:16'),
('9ec59242-4fd2-4f54-898c-4b4c60270ecd', 'business_favicon', '\"2022-10-04-633bfb21640b9.png\"', '\"2022-10-04-633bfb21640b9.png\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:37'),
('9f9f1855-6566-49d4-8ca8-f033a8a2c107', 'add_fund_wallet', '{\"add_fund_wallet_status\":\"1\",\"add_fund_wallet_message\":\"Add Fund Wallet\"}', '{\"add_fund_wallet_status\":\"1\",\"add_fund_wallet_message\":\"Add Fund Wallet\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('9ff4b51f-0edd-4a33-ab15-bc79f7542ecd', 'about_us', '\"<p>hello world hero greatth weh fvaaafawefdsdsdsd<\\/p>\"', NULL, 'pages_setup', 'live', 1, '2022-08-04 13:04:19', '2022-10-04 11:57:25'),
('a0910af1-fee3-4527-957b-e46b40ad5ed2', 'bid_offers_visibility_for_providers', '0', '0', 'bidding_system', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('a38599a7-fb8f-4f3c-a955-b975cdd8fae5', 'customer_referral_earning', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16'),
('a6cd4791-0276-4fa4-b2a1-13d3d5a8f232', 'twilio', '{\"gateway\":\"twilio\",\"mode\":\"live\",\"status\":\"0\",\"sid\":\"data\",\"messaging_service_sid\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', '{\"gateway\":\"twilio\",\"mode\":\"live\",\"status\":\"0\",\"sid\":\"data\",\"messaging_service_sid\":\"data\",\"token\":\"data\",\"from\":\"data\",\"otp_template\":\"data\"}', 'sms_config', 'live', 0, '2022-06-08 07:03:02', '2022-10-04 16:26:39'),
('a8c1f49a-be3b-4609-8242-37142ff05acd', 'currency_decimal_point', '\"2\"', '\"2\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:24'),
('a9c93141-a7c9-473b-ac46-b3dadc7b067f', 'razor_pay', '{\"gateway\":\"razor_pay\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"api_secret\":\"data\"}', '{\"gateway\":\"razor_pay\",\"mode\":\"live\",\"status\":\"1\",\"api_key\":\"data\",\"api_secret\":\"data\"}', 'payment_config', 'live', 1, '2022-06-09 07:46:29', '2022-10-04 16:29:32'),
('ab04b341-d3ae-4c45-9e35-813dd01f22b6', 'max_cash_in_hand_limit_provider', '0', '0', 'provider_config', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('aba6da13-a238-4042-97f6-78972dccfdd0', 'instant_booking', '1', '1', 'booking_setup', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('ae03b974-b953-4c6d-98c4-6eb0f231d16d', 'customized_booking_request_delete', '{\"customized_booking_request_delete_status\":\"1\",\"customized_booking_request_delete_message\":\"Customized Booking Request Delete\"}', '{\"customized_booking_request_delete_status\":\"1\",\"customized_booking_request_delete_message\":\"Customized Booking Request Delete\"}', 'customer_notification', 'live', 1, '2024-03-13 09:19:39', '2024-03-13 09:19:39'),
('af729332-d8ec-4822-854a-5f54e10a9061', 'sslcommerz', '{\"gateway\":\"sslcommerz\",\"mode\":\"test\",\"status\":\"1\",\"store_id\":\"data\",\"store_password\":\"data\"}', '{\"gateway\":\"sslcommerz\",\"mode\":\"test\",\"status\":\"1\",\"store_id\":\"data\",\"store_password\":\"data\"}', 'payment_config', 'test', 1, '2022-06-09 03:19:38', '2022-10-04 16:29:39'),
('afba0ef3-7b60-4638-b2c0-2dac02f2119e', 'booking_schedule_time_change', '{\"booking_schedule_time_change_status\":\"1\",\"booking_schedule_time_change_message\":\"Booking schedule time change\"}', '{\"booking_schedule_time_change_status\":\"1\",\"booking_schedule_time_change_message\":\"Booking schedule time change\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('afe56fb4-02b6-4cc7-bec8-752012dd2c87', 'provider_commision', '1', '1', 'provider_config', 'live', 1, '2024-07-09 13:20:42', '2024-07-09 13:20:42'),
('aff0213c-bcb2-42e0-98bb-295262683ccf', 'booking_edit_service_add', '{\"booking_edit_service_add_status\":\"1\",\"booking_edit_service_add_message\":\"Booking Edit Service Add\"}', '{\"booking_edit_service_add_status\":\"1\",\"booking_edit_service_add_message\":\"Booking Edit Service Add\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('b004a67d-df30-4a85-9ec0-54774cc2e616', 'min_loyalty_point_to_transfer', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16'),
('b0bf5be1-183a-4dbe-86f6-bd3ff2b9c68a', 'push_notification', '{\"party_name\":\"push_notification\",\"server_key\":\"apikey\"}', '{\"party_name\":\"push_notification\",\"server_key\":\"apikey\"}', 'third_party', 'live', 0, '2022-07-16 04:56:01', '2022-10-04 16:24:43'),
('b9525ca3-9c9e-432c-a2bb-a9f41c8a6b68', 'time_format', '\"24h\"', '\"24h\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-08-18 10:28:09'),
('ba08f7c0-a233-43f5-a801-cd727e3e7de9', 'admin_order_notification', '1', '1', 'service_setup', 'live', 1, '2022-07-20 06:04:23', '2022-08-13 07:35:03'),
('bb1f5b40-88b8-45ac-a28e-53c32bd5c4de', 'deadline_warning_message', '\"Your subscription ending soon. Please  renew to continue access\"', '\"Your subscription ending soon. Please  renew to continue access\"', 'subscription_Setting', 'live', 1, '2024-07-09 18:57:16', '2024-07-09 18:57:16'),
('bbfd087c-eaa5-4868-8a69-3be87720ae86', 'top_description', '\"LARGEST BOOKING SERVICE PLATEFORM\"', '\"LARGEST BOOKING SERVICE PLATEFORM\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('bc4ed6e1-2c7e-4c95-8c7f-fe547a317c1e', 'about_us_image', '\"2023-08-31-64ef2c4244964.png\"', '\"2023-08-31-64ef2c4244964.png\"', 'landing_images', 'live', 0, '2022-10-03 17:37:45', '2023-08-30 18:47:14'),
('bd122993-e4d6-427a-be42-713b3e9612a3', 'booking_cancel', '{\"booking_cancel_status\":\"1\",\"booking_cancel_message\":\"Booking Cancel\"}', '{\"booking_cancel_status\":\"1\",\"booking_cancel_message\":\"Booking Cancel\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('bde4ffab-e63d-435d-8854-46e4cfa49daf', 'booking_edit_service_add', '{\"booking_edit_service_add_status\":\"1\",\"booking_edit_service_add_message\":\"Booking Edit Service Add\"}', '{\"booking_edit_service_add_status\":\"1\",\"booking_edit_service_add_message\":\"Booking Edit Service Add\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('be65566f-dbbd-4f9c-b451-e93101d195c0', 'maintenance_duration_setup', '{\"maintenance_duration\":\"until_change\",\"start_date\":null,\"end_date\":null}', NULL, 'maintenance_mode', 'live', 1, '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('be927e91-0b1f-44b1-9ad8-73022910f717', 'new_service_request_arrived', '{\"new_service_request_arrived_status\":\"1\",\"new_service_request_arrived_message\":\"New Service Request Arrived\"}', '{\"new_service_request_arrived_status\":\"1\",\"new_service_request_arrived_message\":\"New Service Request Arrived\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('bef3c996-55b2-4371-8fe0-7204931d7478', 'usage_time', '70', '70', 'subscription_Setting', 'live', 1, '2024-07-09 18:57:16', '2024-07-09 18:57:16'),
('bf085f4a-1f5d-4ff2-b4de-2038ef70ad79', 'serviceman_assign', '{\"serviceman_assign_status\":\"1\",\"serviceman_assign_message\":\"Serviceman Assign\"}', '{\"serviceman_assign_status\":\"1\",\"serviceman_assign_message\":\"Serviceman Assign\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('bf12c77c-5274-408c-aa5f-f056db9ac3b8', 'provider_suspension_remove', '{\"provider_suspension_remove_status\":\"1\",\"provider_suspension_remove_message\":\"Provider Suspension removed\"}', '{\"provider_suspension_remove_status\":\"1\",\"provider_suspension_remove_message\":\"Provider Suspension removed\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('bf2b55c8-3ba0-4087-97b9-af101a5258d1', 'guest_checkout', '0', '0', 'service_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('c58ecce7-598f-4568-aa15-d875dfa12232', 'terms_and_conditions', '\"<p>Demo terms and conditions<\\/p>\"', NULL, 'pages_setup', 'live', 0, '2022-08-06 04:02:24', '2022-10-04 11:11:05'),
('c5d7ea4e-d51b-408f-8e57-58186d7d62f6', 'booking_edit_service_add', '{\"booking_edit_service_add_status\":\"1\",\"booking_edit_service_add_message\":\"Booking Edit Service Add\"}', '{\"booking_edit_service_add_status\":\"1\",\"booking_edit_service_add_message\":\"Booking Edit Service Add\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('c6331403-a1cd-4899-8d68-24a2ac323499', 'offline_payment_approved', '{\"offline_payment_approved_status\":\"1\",\"offline_payment_approved_message\":\"Offline Payment Approved\"}', '{\"offline_payment_approved_status\":\"1\",\"offline_payment_approved_message\":\"Offline Payment Approved\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('c68a9f47-7504-4fc9-b4f6-6a5aa274e4b8', 'business_name', '\"company\"', '\"company\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:19:55'),
('c8dd2bcf-44e5-41e4-a121-fe4cc6f44e33', 'discount_cost_bearer', '{\"bearer\":\"provider\",\"admin_percentage\":0,\"provider_percentage\":100,\"type\":\"discount\"}', '{\"bearer\":\"provider\",\"admin_percentage\":0,\"provider_percentage\":100,\"type\":\"coupon\"}', 'promotional_setup', 'live', 1, '2023-01-22 17:33:48', '2023-01-22 17:33:48'),
('cc84f94d-743d-4551-8981-2fec5a750e93', 'booking_complete', '{\"booking_complete_status\":\"1\",\"booking_complete_message\":\"Booking Complete\"}', '{\"booking_complete_status\":\"1\",\"booking_complete_message\":\"Booking Complete\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('cccc6210-ceb8-431a-a676-e4ad322ef32d', 'loyalty_point', '{\"loyalty_point_status\":\"1\",\"loyalty_point_message\":\"Loyalty Point\"}', '{\"loyalty_point_status\":\"1\",\"loyalty_point_message\":\"Loyalty Point\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('cd0a7610-8c1a-49b1-a007-44560f69b7d8', 'storage_connection_type', '\"local\"', '\"local\"', 'storage_settings', 'live', 1, '2024-07-07 16:15:01', '2024-07-07 16:15:01'),
('cd206dd3-6d91-4608-8bc5-9be80cbf2e42', 'top_sub_title', '\"Get all services from one App.\"', '\"Get all services from one App.\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('cfb57339-dc26-48f0-9815-896aebee243f', 'features', '[{\"id\":\"54fc434a-b953-4b90-97e7-c6d7fae28f41\",\"title\":\"GET YOUR SERVICE 24\\/7\",\"sub_title\":\"Visit our app and select your location to see available services near you\",\"image_1\":\"2023-08-31-64ef2e92e47c3.png\",\"image_2\":\"2023-08-31-64ef2e92e8b41.png\"}]', '[{\"id\":\"54fc434a-b953-4b90-97e7-c6d7fae28f41\",\"title\":\"GET YOUR SERVICE 24\\/7\",\"sub_title\":\"Visit our app and select your location to see available services near you\",\"image_1\":\"2023-08-31-64ef2e92e47c3.png\",\"image_2\":\"2023-08-31-64ef2e92e8b41.png\"}]', 'landing_features', 'live', 1, '2022-10-03 17:26:57', '2023-08-30 18:58:36'),
('d0c18642-cfa4-4178-8e25-a3a58a6cdff3', 'admin_payable', '{\"admin_payable_status\":\"1\",\"admin_payable_message\":\"Admin Payable\"}', '{\"admin_payable_status\":\"1\",\"admin_payable_message\":\"Admin Payable\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('d27c7746-520f-470d-a3e0-6ac0b427ae61', 'registration_title', '\"REGISTER AS PROVIDER\"', '\"REGISTER AS PROVIDER\"', 'landing_text_setup', 'live', 0, '2022-10-03 15:36:11', '2022-10-03 15:36:11'),
('d2b531d9-4cf1-4f7c-b96f-395856f5003d', 'google_map', '{\"party_name\":\"google_map\",\"map_api_key_client\":\"apikey\",\"map_api_key_server\":\"apikey\"}', '{\"party_name\":\"google_map\",\"map_api_key_client\":\"apikey\",\"map_api_key_server\":\"apikey\"}', 'third_party', 'live', 0, '2022-09-14 19:49:51', '2022-10-04 16:24:39'),
('d3ed9c30-4ce1-4c43-8016-e3a51cd74206', 'booking_schedule_time_change', '{\"booking_schedule_time_change_status\":\"1\",\"booking_schedule_time_change_message\":\"Booking Schedule Time Change\"}', '{\"booking_schedule_time_change_status\":\"1\",\"booking_schedule_time_change_message\":\"Booking Schedule Time Change\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('d42c3f11-c43f-48ab-849e-b950d90aea3d', 'widthdraw_request_approve', '{\"widthdraw_request_approve_status\":\"1\",\"widthdraw_request_approve_message\":\"Withdraw Request Approve\"}', '{\"widthdraw_request_approve_status\":\"1\",\"widthdraw_request_approve_message\":\"Withdraw Request Approve\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('d5ff36a8-634b-42a1-ab49-08375eeb21ac', 'footer_text', '\"All rights reserved By @ company\"', '\"All rights reserved By @ company\"', 'business_information', 'live', 1, '2022-09-05 10:06:02', '2022-10-04 16:21:24'),
('d683b185-6f67-4cd0-94f7-14d85f8da03a', 'booking_additional_charge', '0', '0', 'booking_setup', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23');
INSERT INTO `business_settings` (`id`, `key_name`, `live_values`, `test_values`, `settings_type`, `mode`, `is_active`, `created_at`, `updated_at`) VALUES
('d8a4c244-0e6c-4511-a156-93db22a66b7e', 'phone_number_visibility_for_chatting', '\"0\"', '\"0\"', 'business_information', 'live', 1, '2023-02-23 00:25:16', '2023-08-30 19:38:42'),
('d9ecd0f6-aa05-42fe-b539-990618fca55f', 'booking_edit_service_quantity_decrease', '{\"booking_edit_service_quantity_decrease_status\":\"1\",\"booking_edit_service_quantity_decrease_message\":\"Booking Edit Service Quantity Decrease\"}', '{\"booking_edit_service_quantity_decrease_status\":\"1\",\"booking_edit_service_quantity_decrease_message\":\"Booking Edit Service Quantity Decrease\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('db386429-6982-4f46-82f9-08ec70f13f57', 'maximum_withdraw_amount', '\"1\"', '\"1\"', 'business_information', 'live', 1, '2023-01-22 17:33:48', '2023-08-30 19:38:42'),
('dbd7d22a-299e-49be-869e-efdcaf8ee7e4', 'web_url', '\"\\/\"', '\"\\/\"', 'landing_button_and_links', 'live', 1, '2022-10-03 16:00:01', '2022-10-04 16:22:24'),
('dbf71089-a769-4025-b971-307c08a6b455', 'service_man_can_cancel_booking', '\"0\"', '\"0\"', 'service_setup', 'live', 0, '2022-07-20 06:04:21', '2022-10-04 16:00:21'),
('dcb1d44b-f536-43ab-80f8-ea115f008abe', 'advertisement_paused', '{\"advertisement_paused_status\":\"1\",\"advertisement_paused_message\":\"Advertisement Paused\"}', '{\"advertisement_paused_status\":\"1\",\"advertisement_paused_message\":\"Advertisement Paused\"}', 'provider_notification', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('e28b7af4-2284-40bf-b28b-84baad4dc1a7', 'top_image_2', '\"2023-08-31-64ef2a26b54e0.png\"', '\"2023-08-31-64ef2a26b54e0.png\"', 'landing_images', 'live', 0, '2022-10-03 16:06:00', '2023-08-30 18:38:14'),
('e2bcae59-8095-4b14-8faf-ebe4a1a867cb', 'booking_ongoing', '{\"booking_ongoing_status\":\"1\",\"booking_ongoing_message\":\"Booking Ongoing\"}', '{\"booking_ongoing_status\":\"1\",\"booking_ongoing_message\":\"Booking Ongoing\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('e5362c47-db82-4437-86d8-626bfa78e264', 'booking_complete', '{\"booking_complete_status\":\"1\",\"booking_complete_message\":\"Booking Complete\"}', '{\"booking_complete_status\":\"1\",\"booking_complete_message\":\"Booking Complete\"}', 'serviceman_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('e79f70a2-7580-422c-9d07-8dc47e3961bc', 'free_trial_type', '\"day\"', '\"day\"', 'subscription_Setting', 'live', 1, '2024-07-09 18:57:16', '2024-07-09 18:57:16'),
('e80a7c3d-9371-4959-8463-c67973a42e56', 'business_email', '\"email@tech.com\"', '\"email@tech.com\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:24'),
('e82e188c-d7de-478b-a83b-26c086b0576d', '2factor', '{\"gateway\":\"2factor\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\"}', '{\"gateway\":\"2factor\",\"mode\":\"live\",\"status\":\"0\",\"api_key\":\"data\"}', 'sms_config', 'live', 0, '2022-06-08 08:56:14', '2022-10-04 16:26:44'),
('e83290d3-d06b-47dd-b83c-5c8876ed0bd0', 'apple_login', '{\"party_name\":\"apple_login\",\"status\":0,\"client_id\":null,\"team_id\":null,\"key_id\":null,\"service_file\":null}', '{\"party_name\":\"apple_login\",\"status\":0,\"client_id\":null,\"team_id\":null,\"key_id\":null,\"service_file\":null}', 'third_party', 'live', 1, '2023-08-30 13:09:23', '2023-08-30 13:09:23'),
('ea0c3ccd-6db7-4b34-8f21-d0eb637cc47c', 'minimum_withdraw_amount', '\"1\"', '\"1\"', 'business_information', 'live', 1, '2023-01-22 17:33:48', '2023-08-30 19:38:42'),
('ea71998b-2399-44cc-8949-1786e753eb9c', 'country_code', '\"AS\"', '\"AS\"', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-10-04 16:21:24'),
('eb2430a8-8d49-4bbe-b19d-1de8bd80be64', 'cash_after_service', '1', '1', 'service_setup', 'live', 1, '2023-05-29 16:22:38', '2023-05-29 16:22:38'),
('eb38f917-771c-48b8-8f65-dad3394a2b1b', 'schedule_booking', '1', '1', 'booking_setup', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('eb59a509-e7c2-499b-9c44-57554cbfe015', 'language_code', '[\"Bengali\",\"English\",\"Arabic\",\"Abkhaz\",\"Afar\",\"Akan\",\"Albanian\",\"Amharic\"]', '[\"Bengali\",\"English\",\"Arabic\",\"Abkhaz\",\"Afar\",\"Akan\",\"Albanian\",\"Amharic\"]', 'business_information', 'live', 1, '2022-06-14 09:39:24', '2022-07-23 07:26:01'),
('ee36e0ac-f41f-4e14-a77c-54e933939a72', 'referral_discount_type', '\"flat\"', '\"flat\"', 'customer_config', 'live', 1, '2024-05-20 14:36:21', '2024-05-20 14:36:21'),
('eeca1881-9a28-4be9-9c27-95f4e84e6aca', 'loyalty_point_value_per_currency_unit', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16'),
('f290631d-bf48-45a8-90fc-98060f71d828', 'booking_accepted', '{\"booking_accepted_status\":\"1\",\"booking_accepted_message\":\"Booking Accepted\"}', '{\"booking_accepted_status\":\"1\",\"booking_accepted_message\":\"Booking Accepted\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('f4dd708c-4430-4bf1-8f10-797619fbdb7e', 'provider_subscription', '0', '0', 'provider_config', 'live', 1, '2024-07-09 13:20:42', '2024-07-09 13:20:42'),
('f82d1978-8ab9-4ad3-a84f-050faa17436f', 'service_request_approve', '{\"service_request_approve_status\":\"1\",\"service_request_approve_message\":\"Service Request Review\"}', '{\"service_request_approve_status\":\"1\",\"service_request_approve_message\":\"Service Request Review\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('f99e20b3-dfb1-431f-891b-7d78c413e964', 'booking_refund', '{\"booking_refund_status\":1,\"booking_refund_message\":\"Booking Refund Successfully\"}', '{\"booking_refund_status\":1,\"booking_refund_message\":\"Booking Refund Successfully\"}', 'notification_messages', 'live', 1, '2022-06-06 12:41:28', '2022-09-05 15:17:05'),
('fb2792f7-d2f9-4560-83e0-61a8ed5d4aed', 'booking_cancel', '{\"booking_cancel_status\":\"1\",\"booking_cancel_message\":\"Booking Cancel\"}', '{\"booking_cancel_status\":\"1\",\"booking_cancel_message\":\"Booking Cancel\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('fc50433d-6dff-4a85-adaa-9bf1a27fc9ad', 'customized_booking_request', '{\"customized_booking_request_status\":\"1\",\"customized_booking_request_message\":\"Customized Booking Request\"}', '{\"customized_booking_request_status\":\"1\",\"customized_booking_request_message\":\"Customized Booking Request\"}', 'customer_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('fc7e71f7-c5d1-4c8a-aec4-ccbee608314d', 'deadline_warning', '5', '5', 'subscription_Setting', 'live', 1, '2024-07-09 18:57:16', '2024-07-09 18:57:16'),
('fc9ca7b4-4f21-4d45-a0cb-04693ebee4dc', 'provider_section_image', '\"2022-10-04-633bfb7cc79de.png\"', '\"2022-10-04-633bfb7cc79de.png\"', 'landing_images', 'live', 0, '2022-10-03 17:17:01', '2022-10-04 16:23:08'),
('fccca07e-50a5-4a6e-b509-5840a14fbc03', 'booking_schedule_time_change', '{\"booking_schedule_time_change_status\":\"1\",\"booking_schedule_time_change_message\":\"Booking schedule time change\"}', '{\"booking_schedule_time_change_status\":\"1\",\"booking_schedule_time_change_message\":\"Booking schedule time change\"}', 'provider_notification', 'live', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('fe386570-fee3-46bd-a9eb-1b34be33b7d6', 'loyalty_point_percentage_per_booking', '0', '0', 'customer_config', 'live', 1, '2023-02-23 00:25:16', '2023-02-23 00:25:16');

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `campaign_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `thumbnail` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'def.png',
  `discount_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_cost` decimal(24,2) NOT NULL DEFAULT '0.00',
  `quantity` int(11) NOT NULL DEFAULT '1',
  `discount_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_discount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `campaign_discount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `total_cost` decimal(24,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `image`, `position`, `description`, `is_active`, `is_featured`, `created_at`, `updated_at`) VALUES
('5e5c0fdb-9ad7-4075-bcc1-d7523efde8c6', '0', 'Test category', '2023-08-31-64ef394fcd699.png', 1, NULL, 1, 1, '2023-08-30 19:42:55', '2023-08-30 19:43:10'),
('b4fcd6d4-ba80-4e20-9ee2-f07a8b1466c6', '5e5c0fdb-9ad7-4075-bcc1-d7523efde8c6', 'Test Sub category', '2023-08-31-64ef3983e4faa.png', 2, 'This is a fake description', 1, 0, '2023-08-30 19:43:47', '2023-08-30 19:43:47');

-- --------------------------------------------------------

--
-- Table structure for table `category_zone`
--

CREATE TABLE `category_zone` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_zone`
--

INSERT INTO `category_zone` (`id`, `category_id`, `zone_id`, `created_at`, `updated_at`) VALUES
(1, '5e5c0fdb-9ad7-4075-bcc1-d7523efde8c6', 'a1614dbe-4732-11ee-9702-dee6e8d77be4', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `channel_conversations`
--

CREATE TABLE `channel_conversations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `channel_lists`
--

CREATE TABLE `channel_lists` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:guid)',
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `channel_users`
--

CREATE TABLE `channel_users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_files`
--

CREATE TABLE `conversation_files` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversation_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stored_file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coupon_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_customers`
--

CREATE TABLE `coupon_customers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coupon_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `data_settings`
--

CREATE TABLE `data_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data_settings`
--

INSERT INTO `data_settings` (`id`, `key`, `value`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
('0689bba0-0de2-4820-902a-5b4840af7fe9', 'bottom_title', 'GET ALL UPDATES & EXCITING NEWS', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('11c9071f-70e2-412b-ba73-73da0ae9f7cb', 'refund_policy', '<p>Test Refund Policy</p>', 'pages_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('2787bb42-2e6a-43a4-81f1-7aee084f7083', 'bottom_description', 'Subcribe to out newsletters to receive all the latest activty we provide for you', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('42a986a9-831b-4789-9ae4-26b8284a2017', 'top_title', 'Customer Statisfaciton is our main moto', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('514e21aa-ae56-447e-9f9d-a34d4995bb9e', 'cancellation_policy', '<p>Test Cancellation Policy</p>', 'pages_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('7f249215-5ee0-4a39-872e-3b3bd2634278', 'about_us', '<p>Test About Us</p>', 'pages_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('875a27cc-5456-4573-a4af-cdf9d2e15c16', 'registration_title', 'REGISTER AS PROVIDER', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('95b1d2d7-b656-4ea6-9347-79909a94e7dd', 'registration_description', 'Become e provider & Start your own business online with on demand service platform', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('af772e44-2cea-4396-bb82-9dbc13eb8691', 'top_sub_title', 'Get all services from one App.', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('c1f90ba8-3432-4478-8f35-7c992fa10aaf', 'privacy_policy', '<p>Test Privacy Policy</p>', 'pages_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('c60d816a-3100-4d3e-8aba-013a0ef13639', 'about_us_description', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s,', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('f4df4ad8-96be-4f99-a14e-6dc7c82035f8', 'top_description', 'LARGEST BOOKING SERVICE PLATEFORM', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('f4e72cf5-7e3f-40e0-a7b0-1185c62a65fa', 'about_us_title', 'WHO WE ARE', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('f64ffa14-fb1c-47e6-8736-0da45af39785', 'terms_and_conditions', '<p>Test Terms & Conditions</p>', 'pages_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21'),
('f6516965-15da-4fd6-805d-7788c0121a5f', 'mid_title', 'SERVICE WE PROVIDE FOR YOU', 'landing_text_setup', 1, '2023-11-28 15:33:21', '2023-11-28 15:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `discount_amount_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `min_purchase` decimal(24,3) NOT NULL DEFAULT '0.000',
  `max_discount_amount` decimal(24,3) NOT NULL DEFAULT '0.000',
  `limit_per_user` int(11) NOT NULL DEFAULT '0',
  `promotion_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'discount',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` date NOT NULL DEFAULT '2022-04-04',
  `end_date` date NOT NULL DEFAULT '2022-04-04',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discount_types`
--

CREATE TABLE `discount_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `discount_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_wise_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_role_accesses`
--

CREATE TABLE `employee_role_accesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_view` tinyint(4) NOT NULL DEFAULT '1',
  `can_add` tinyint(4) NOT NULL DEFAULT '0',
  `can_update` tinyint(4) NOT NULL DEFAULT '0',
  `can_delete` tinyint(4) NOT NULL DEFAULT '0',
  `can_export` tinyint(4) NOT NULL DEFAULT '0',
  `can_manage_status` tinyint(4) NOT NULL DEFAULT '0',
  `can_approve_or_deny` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `can_assign_serviceman` tinyint(4) NOT NULL DEFAULT '0',
  `can_give_feedback` tinyint(4) NOT NULL DEFAULT '0',
  `can_take_backup` tinyint(4) NOT NULL DEFAULT '0',
  `can_change_status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_role_sections`
--

CREATE TABLE `employee_role_sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question` text COLLATE utf8mb4_unicode_ci,
  `answer` text COLLATE utf8mb4_unicode_ci,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorite_providers`
--

CREATE TABLE `favorite_providers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorite_services`
--

CREATE TABLE `favorite_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `current_language_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ignored_posts`
--

CREATE TABLE `ignored_posts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_features`
--

CREATE TABLE `landing_page_features` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landing_page_features`
--

INSERT INTO `landing_page_features` (`id`, `title`, `sub_title`, `image_1`, `image_2`, `created_at`, `updated_at`) VALUES
('12696663-098f-432a-8b1a-f192ea05c7a6', 'GET YOUR SERVICE 24/7', 'Visit our app and select your location to see available services near you', '2023-08-31-64ef2e92e47c3.png', '2023-08-31-64ef2e92e8b41.png', '2023-12-28 14:00:58', '2023-12-28 14:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_specialities`
--

CREATE TABLE `landing_page_specialities` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landing_page_specialities`
--

INSERT INTO `landing_page_specialities` (`id`, `title`, `description`, `image`, `created_at`, `updated_at`) VALUES
('dcfe3c2b-9a84-4621-ba8a-34ea95dcb21f', 'Speciality', 'Speciality description', '2023-08-31-64ef3bcbbeb55.png', '2023-12-28 14:00:58', '2023-12-28 14:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `landing_page_testimonials`
--

CREATE TABLE `landing_page_testimonials` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landing_page_testimonials`
--

INSERT INTO `landing_page_testimonials` (`id`, `name`, `designation`, `review`, `image`, `created_at`, `updated_at`) VALUES
('455f102c-97eb-42f8-bb97-473eb6887eef', 'Mike', 'Designer', 'Thank you! That was very helpful! The Service men were very professionals & very caution about safety', '2023-08-31-64ef36f7190ba.png', '2023-12-28 14:00:58', '2023-12-28 14:00:58');

-- --------------------------------------------------------

--
-- Table structure for table `login_setups`
--

CREATE TABLE `login_setups` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_setups`
--

INSERT INTO `login_setups` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
('403a221c-00c1-4f65-b1b9-831ffd5c1cd0', 'phone_verification', '1', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('621ee6e7-1853-40ed-b0d0-1eeaf62ef0ab', 'social_media_for_login', '{\"google\":0,\"facebook\":0,\"apple\":0}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('7e0179d8-ed99-4b2b-837f-7946e640014d', 'login_options', '{\"manual_login\":1,\"otp_login\":0,\"social_media_login\":0}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('f02f5773-d655-4b50-bb9a-a06c7ce08505', 'email_verification', '1', '2024-08-25 14:24:01', '2024-08-25 14:24:01');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_point_transactions`
--

CREATE TABLE `loyalty_point_transactions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit` decimal(24,2) NOT NULL DEFAULT '0.00',
  `debit` decimal(24,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(24,2) NOT NULL DEFAULT '0.00',
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_100000_create_password_resets_table', 1),
(2, '2016_06_01_000001_create_oauth_auth_codes_table', 1),
(3, '2016_06_01_000002_create_oauth_access_tokens_table', 1),
(4, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1),
(5, '2016_06_01_000004_create_oauth_clients_table', 1),
(6, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1),
(7, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(8, '2022_02_28_094005_create_users_table', 1),
(9, '2022_02_28_094802_create_roles_table', 1),
(10, '2022_02_28_094823_create_user_roles_table', 1),
(11, '2022_03_01_092248_create_modules_table', 1),
(12, '2022_03_01_093500_create_role_modules_table', 1),
(13, '2022_03_05_085155_create_zones_table', 1),
(14, '2022_03_06_035439_create_categories_table', 1),
(15, '2022_03_06_042053_create_category_zone_table', 1),
(16, '2022_03_06_091813_create_discounts_table', 1),
(17, '2022_03_06_092202_create_services_table', 1),
(18, '2022_03_06_094413_create_variations_table', 1),
(19, '2022_03_07_063157_create_discount_types_table', 1),
(21, '2022_03_07_065305_create_provider_sub_category_table', 1),
(22, '2022_03_07_090055_create_coupons_table', 1),
(23, '2022_03_07_110744_create_campaigns_table', 1),
(24, '2022_03_08_052530_create_banners_table', 1),
(25, '2022_03_08_090735_create_transactions_table', 1),
(26, '2022_03_10_074138_create_accounts_table', 1),
(27, '2022_05_09_122054_add_variant_key_in_variation', 2),
(28, '2022_05_12_100348_create_faqs_table', 3),
(29, '2022_05_18_041330_discount_table_col_modify', 4),
(30, '2022_05_21_035041_add_coupon_type', 5),
(31, '2022_05_22_120123_add_banner_redirection_link', 6),
(33, '2022_05_24_043332_remove_and_reformat_urder_table_col', 8),
(34, '2022_03_07_064337_create_providers_table', 9),
(35, '2022_05_25_054015_create_business_settings_table', 10),
(36, '2022_06_05_061932_create_bookings_table', 11),
(37, '2022_06_05_063828_create_booking_details_table', 11),
(38, '2022_06_05_065027_create_booking_status_histories_table', 11),
(39, '2022_06_05_065040_create_booking_schedule_histories_table', 11),
(40, '2022_06_08_070555_add_status_col_toRole', 12),
(41, '2022_06_11_074614_category_sub_added_booking', 13),
(42, '2022_06_11_110610_create_user_zones_table', 13),
(43, '2022_06_12_034552_create_user_addresses_table', 13),
(44, '2022_06_13_120346_add_column_is_approved_to_provider_table', 14),
(45, '2022_06_14_104816_create_bank_details_table', 15),
(46, '2022_06_15_025832_role_table_customization', 16),
(47, '2022_06_15_043227_create_subscribed_services_table', 16),
(48, '2022_06_16_060054_tnx_add', 17),
(49, '2022_06_16_060137_acc_add', 18),
(51, '2022_06_18_052537_create_reviews_table', 19),
(52, '2022_06_18_095222_create_withdraw_requests_table', 20),
(53, '2022_06_16_094936_create_servicemen_table', 21),
(54, '2022_06_19_063119_add_serviceman_col', 22),
(55, '2022_06_20_085647_add_col_to_serviceman', 23),
(56, '2022_06_22_082434_create_carts_table', 24),
(57, '2022_06_22_121556_create_cart_service_infos_table', 24),
(58, '2022_06_22_090257_column_add_to_withdraw_request_table', 25),
(59, '2022_07_03_065118_add_zone_id_in_providers', 26),
(61, '2022_07_17_064031_add_addres_type', 27),
(62, '2022_07_17_071324_add_addres_type1', 27),
(63, '2022_07_19_040550_change-col-name', 28),
(64, '2022_07_03_095424_create_push_notifications_table', 29),
(65, '2022_07_21_050907_pass_reset_table_col_add', 30),
(66, '2022_07_21_054008_pass_reset_table_col_add1', 30),
(67, '2022_07_21_104205_add_booking_id_col', 31),
(68, '2022_07_24_051517_add_cus_col_in_review', 32),
(69, '2022_07_31_093836_create_channel_lists_table', 33),
(70, '2022_07_31_093916_create_channel_users_table', 33),
(71, '2022_07_31_094036_create_channel_conversations_table', 33),
(72, '2022_07_31_104246_create_conversation_files_table', 33),
(73, '2022_07_31_113436_add_new_col_campaign', 33),
(74, '2022_08_02_054322_update_col_type', 34),
(75, '2022_08_06_031433_add_col_in_booking_table', 35),
(76, '2022_08_06_031649_add_col_in_booking_details_table', 35),
(77, '2022_08_06_045001_remove_col_from_user', 36),
(78, '2022_08_21_031258_add_col_to_channel_list', 37),
(79, '2022_08_21_033729_add_col_to_channel_user_table', 37),
(80, '2022_08_23_060744_col_add_to_tnx_table', 38),
(81, '2022_08_28_044249_col_change_to_business_settings_table', 39),
(82, '2022_08_31_070329_col_add_to_booking_details_table', 40),
(83, '2022_09_01_135800_create_user_verifications_table', 41),
(84, '2022_09_12_062925_col_add_to_booking_table', 42),
(85, '2022_09_17_185044_add_col_to_bank_destails', 43),
(86, '2022_09_21_235326_col_add_to_withdraw_requests_table', 44),
(87, '2022_10_03_175305_add_zone_id_in_address', 44),
(88, '2022_11_21_175412_add_col_to_withdraw_requests_table', 45),
(89, '2022_11_21_230747_create_withdrawal_methods_table', 45),
(90, '2022_11_29_232809_create_booking_details_amounts_table', 45),
(91, '2022_12_05_184417_col_add_to_services_table', 45),
(92, '2022_12_06_002432_create_recent_views_table', 45),
(93, '2022_12_08_201359_create_recent_searches_table', 45),
(94, '2022_12_26_115139_add_col_to_accounts_table', 45),
(95, '2023_01_16_152849_add_col_to_booking_details_amounts_table', 45),
(96, '2023_01_24_230519_add_col_to_users_table', 46),
(97, '2023_01_25_195038_add_col_to_transactions_table', 46),
(98, '2023_01_26_174101_Create_loyalty_point_transactions_table', 46),
(99, '2023_01_27_001826_add_col_to_categories_table', 46),
(100, '2023_01_29_011739_create_tags_table', 46),
(101, '2023_01_29_162753_create_table_service_tag', 46),
(102, '2023_02_02_231012_create_service_requests_table', 46),
(103, '2023_02_05_200352_create_added_to_carts_table', 46),
(104, '2023_02_05_214409_create_visited_services_table', 46),
(105, '2023_02_05_225314_create_searched_data_table', 46),
(106, '2023_02_08_174014_add_provider_id_to_carts_table', 46),
(107, '2023_04_29_185100_create_posts_table', 47),
(108, '2023_04_29_185107_create_post_additional_instructions_table', 47),
(109, '2023_04_29_185114_create_post_bids_table', 47),
(110, '2023_04_29_185127_create_ignored_posts_table', 47),
(111, '2023_05_08_161525_add_col_to_services_table', 47),
(112, '2023_05_16_130004_add_col_to_providers_table', 47),
(113, '2023_05_16_231127_create_coupon_customers_table', 47),
(115, '2023_05_21_095745_add_col_to_users_table', 47),
(116, '2023_05_21_101102_add_col_to_user_verifications_table', 47),
(117, '2023_05_29_184809_add_col_to_posts_table', 48),
(118, '2023_05_30_102205_add_additional_charge_col_to_bookings_table', 48),
(119, '2023_05_31_103005_add_col_to_bookings_table', 49),
(120, '2023_05_17_144146_add_col_to_posts_table', 50),
(121, '2023_06_11_221500_add_removed_coupon_amount_col_in_booking_table', 51),
(122, '2023_06_26_154947_add_column_to_bookings_table', 51),
(123, '2023_07_12_105915_add_is_guest_column_to_carts_table', 51),
(124, '2023_07_12_105941_add_is_guest_column_to_added_to_carts_table', 51),
(125, '2023_07_12_110032_add_is_guest_column_to_bookings_table', 51),
(126, '2023_07_13_094305_add_is_guest_column_to_transactions_table', 51),
(127, '2023_07_13_094337_add_is_guest_column_to_booking_schedule_histories_table', 51),
(128, '2023_07_13_094413_add_is_guest_column_to_booking_status_histories_table', 51),
(129, '2023_07_13_105419_create_guests_table', 51),
(130, '2023_07_13_180332_add_is_guest_column_to_added_to_user_addresses_table', 51),
(131, '2023_07_17_142048_add_is_verified_col_to_bookings_table', 51),
(132, '2023_07_17_184044_add_is_guest_col_to_posts_table', 51),
(133, '2023_07_19_111811_add_house_and_street_col_in_user_addresses_table', 51),
(134, '2023_07_29_133252_create_booking_partial_payments_table', 51),
(135, '2023_08_06_152659_create_offline_payments_table', 51),
(136, '2023_08_07_153312_create_booking_offline_payments_table', 51),
(137, '2023_08_08_094402_create_bonuses_table', 51),
(138, '2023_08_10_182955_add_fee_column_to_bookings_table', 51),
(139, '2023_08_24_132317_create_provider_settings_table', 51),
(140, '2023_10_31_171211_create_translations_table', 52),
(141, '2023_11_07_182712_create_landing_page_features_table', 52),
(142, '2023_11_08_092558_create_landing_page_specialities_table', 52),
(143, '2023_11_08_094847_create_landing_page_testimonials_table', 52),
(144, '2023_11_08_174249_add_current_language_to_users_table', 52),
(145, '2023_11_08_174418_add_current_language_to_guests_table', 52),
(146, '2023_11_16_110101_create_data_settings_table', 52),
(147, '2023_11_19_112426_add_is_suspended_col_to_providers_table', 52),
(148, '2023_11_19_155434_add_soft_deletes_to_providers_table', 52),
(149, '2023_12_20_135725_add_service_availability_to_providers_table', 53),
(150, '2024_01_28_080833_create_booking_additional_information_table', 54),
(151, '2024_01_29_115651_create_post_additional_information_table', 54),
(152, '2024_02_10_183058_rename_file_name_col_to_conversation_files_table', 54),
(153, '2024_02_10_184243_add_original_file_name_col_to_conversation_files_table', 54),
(154, '2024_02_15_143856_make_col_nullable_to_service_requests_table', 54),
(155, '2024_02_29_140738_add_col_to_booking_offline_payments_table', 54),
(156, '2024_03_20_053908_create_favorite_providers_table', 55),
(157, '2024_03_20_134756_create_favorite_services_table', 55),
(158, '2024_03_27_102042_add_total_referral_discount_amount_to_bookings_table', 55),
(159, '2024_04_02_102509_create_push_notification_users_table', 55),
(160, '2024_04_22_132349_create_advertisements_table', 55),
(161, '2024_04_22_134754_create_advertisement_attachments_table', 55),
(162, '2024_04_22_181536_create_advertisement_notes_table', 55),
(163, '2024_04_25_160923_create_advertisement_settings_table', 55),
(164, '2024_04_29_133239_create_role_accesses_table', 55),
(165, '2024_04_29_135809_create_role_sections_table', 55),
(166, '2024_05_02_114745_create_employee_role_sections_table', 55),
(167, '2024_05_02_114900_create_employee_role_accesses_table', 55),
(168, '2024_05_09_141125_drop_col_form_roles_tabel', 55),
(169, '2024_05_09_142158_drop_col_form_employee_role_section_tabel', 55),
(170, '2024_05_09_142555_drop_role_modules_tabel', 55),
(171, '2024_05_09_142749_drop_user_roles_tabel', 55),
(172, '2024_05_09_143102_drop_role_section_tabel', 55),
(173, '2024_05_13_112445_add_is_updated_to_advertisements_table', 55),
(174, '2024_05_13_155906_add_to_col_role_accesses_tabel', 55),
(175, '2024_05_13_162125_add_to_col_employee_role_accesses_tabel', 55),
(176, '2024_05_21_105847_create_subscription_packages_table', 56),
(177, '2024_05_21_105930_create_subscription_package_features_table', 56),
(178, '2024_05_21_105958_create_subscription_package_limits_table', 56),
(179, '2024_05_22_152625_create_storages_table', 56),
(180, '2024_05_26_100825_create_package_subscribers_table', 56),
(181, '2024_05_26_100851_create_package_subscriber_features_table', 56),
(182, '2024_05_26_100918_create_package_subscriber_limits_table', 56),
(183, '2024_05_28_170817_create_package_subscriber_logs_table', 56),
(184, '2024_06_02_173558_add_to_col_package_subscriber_table', 56),
(185, '2024_06_04_142029_rename_col_package_subscriber_features_tabel', 56),
(186, '2024_06_06_160149_add_to_col_transaction_id_to_package_subscriber_logs_tabel', 56),
(187, '2024_06_11_115322_add_to_col_package_subscription_tabel', 56),
(188, '2024_06_12_152132_add_is_notified_in_package_subscribers', 56),
(189, '2024_06_25_113732_create_subscription_booking_types_table', 56),
(190, '2024_07_11_130225_create_subscription_subscriber_bookings_table', 57),
(191, '2024_08_01_085757_create_login_setups_table', 58),
(192, '2024_08_04_171331_create_notification_setups_table', 58),
(193, '2024_08_06_204700_create_provider_notification_setups_table', 58);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_setups`
--

CREATE TABLE `notification_setups` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_setups`
--

INSERT INTO `notification_setups` (`id`, `user_type`, `title`, `sub_title`, `key`, `value`, `created_at`, `updated_at`) VALUES
('10b69552-ad4f-45ed-9b90-5cf6a4faa567', 'provider', 'Terms & Conditions', 'Choose how the provider will get notified of Terms & Conditions Update by the admin', 'terms_&_conditions_update', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('13af7141-50e0-49f7-9f5d-4817868f7b74', 'provider', 'Verification', 'Choose how the provider will get notified of Email/Phone Verification and password recovery OTP sent via Email/Phone', 'verification', '{\"email\":1,\"notification\":null,\"sms\":1}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('2a3c95eb-7a13-4a8a-8557-1a57b3e678c0', 'user', 'Refer & earn', 'Choose how the customer will get notified of refer code use, first booking completion, and get cashback as a reward', 'refer_earn', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('32aa144f-0d86-43c8-93d4-8c649ef0ae30', 'provider', 'System Update', 'Choose how the provider will get notified of System Updates by the admin', 'system_update', '{\"email\":1,\"notification\":null,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('3b56cfeb-7e9b-454f-8fad-813fb255b6d7', 'provider', 'Transaction', 'Choose how the provider will get notified of suspend on exceeding cash in hand balance', 'transaction', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('3c1757b1-21fd-42ab-9371-71a9ed4c27a6', 'user', 'Verification', 'Choose how the customer will get notified of Email/Phone Verification and password recovery OTP sent via Email/Phone', 'verification', '{\"email\":1,\"notification\":null,\"sms\":1}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('525804e0-4985-4ff5-8d14-dc12c72636c5', 'user', 'Privacy policy update', 'Choose how the customer will get notified of Privacy policy updates by the admin', 'privacy_policy_update', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('55e98f67-b05c-4419-9ba7-fd2451b346cb', 'user', 'Loyalty point', 'Choose how the customer will get notified of earning loyalty points as a reward', 'loyality_point', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('57afaffe-b68d-48ed-9494-a7af0000620c', 'provider', 'Booking', 'Choose how the providers will get notified of new bookings, Booking Edits, Booking Status updates, Schedule time changes, and withdrawal request', 'booking', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('59c019e4-1f65-42e3-a1ec-ff1b0e7ae408', 'user', 'Wallet', 'Choose how the customer will get notified of getting a bonus & Wallet balance from admin or add funds by himself', 'wallet', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('628c0ac6-9339-48de-8ea7-03ee57333fca', 'serviceman', 'Booking', 'Choose how the serviceman will get notified of new booking assign, Booking Edits, Booking Status updates, Schedule time changes', 'booking', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('64b779e6-835d-429c-9ad3-854e5b504385', 'user', 'Chatting', 'Choose how the customer will get notified of message reply, attachment received', 'chatting', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('69545df6-cf1a-4021-84b3-be6ed84bd294', 'provider', 'Registration', 'Choose how the provider will get notified of new registration approval or deny', 'registration', '{\"email\":1,\"notification\":null,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('8754b5d8-00a7-4926-bf4e-c993c84162ce', 'user', 'Terms & Conditions', 'Choose how the customer will get notified of Terms & Conditions Update by the admin', 'terms_&_conditions_update', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('8dd14f5b-331a-4b84-8721-f3d26e8d7c18', 'provider', 'Subscription', 'Choose how the provider will get notified of the Subscription plan subscribe, shifted, renewed, canceled & updated.', 'subscription', '{\"email\":1,\"notification\":null,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('8fe761a7-7201-4942-9bf5-2797f22e37d2', 'user', 'Registration', 'Choose how the customer will get notified when the admin registers the customer to the system', 'registration', '{\"email\":1,\"notification\":null,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('9090438b-a017-4175-a97a-cc969c8c4f27', 'provider', 'Chatting', 'Choose how the provider will get notified of message reply, attachment received', 'chatting', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('9a074e1a-e3b3-4b8f-b7c4-2cc142e66170', 'serviceman', 'Verification', 'Choose how the serviceman will get notified of password recovery OTP sent via Email/Phone', 'verification', '{\"email\":1,\"notification\":null,\"sms\":1}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('a094b687-cafb-47d2-855e-8ec70f87aa67', 'provider', 'Advertisement', 'Choose how the provider will get notified of advertisement requests accept, deny, run ads, expire ads, etc.', 'advertisement', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('b01ecf87-2912-4227-90c5-9a99c4f632db', 'serviceman', 'Privacy policy update', 'Choose how the serviceman  will get notified of Privacy policy updates by the admin', 'privacy_policy_update', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('d9e4cc60-afe8-4160-b536-9eecda300b92', 'serviceman', 'Terms & Conditions Update', 'Choose how the serviceman will get notified of Terms & Conditions Update by the admin', 'terms_&_conditions_update', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('ed11392c-cbba-468e-aeec-a6fdc28f1f5c', 'provider', 'Privacy policy update', 'Choose how the provider will get notified of Privacy policy updates by the admin', 'privacy_policy_update', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('edaadfd7-864e-4070-a71f-527f676df127', 'serviceman', 'Chatting', 'Choose how the serviceman will get notified of message reply, attachment received', 'chatting', '{\"email\":null,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01'),
('f2e260a1-325b-4471-af26-cdc7cb43f899', 'user', 'Booking', 'Choose how the customer will get notified of all the bookings they placed in the system', 'booking', '{\"email\":1,\"notification\":1,\"sms\":null}', '2024-08-25 14:24:01', '2024-08-25 14:24:01');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

CREATE TABLE `oauth_clients` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
('95faaac6-c1d2-4d4c-beb1-04196dd2fa8e', NULL, 'Laravel Personal Access Client', '75kQskqekdipFpesfWZZv85qPo2cT8aMsyWgsIrQ', NULL, 'http://localhost', 1, 0, 0, '2022-04-04 02:13:15', '2022-04-04 02:13:15'),
('95faaac6-c56a-4873-a880-79d252d65ab1', NULL, 'Laravel Password Grant Client', 'hnFqAvObupsF3BXW4T6MxD4IhvKCZPRzyIqEFciB', 'users', 'http://localhost', 0, 1, 0, '2022-04-04 02:13:15', '2022-04-04 02:13:15');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, '95faaac6-c1d2-4d4c-beb1-04196dd2fa8e', '2022-04-04 02:13:15', '2022-04-04 02:13:15');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offline_payments`
--

CREATE TABLE `offline_payments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `customer_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_subscribers`
--

CREATE TABLE `package_subscribers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_package_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_subscriber_log_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_price` decimal(24,2) NOT NULL DEFAULT '0.00',
  `package_start_date` timestamp NULL DEFAULT NULL,
  `package_end_date` timestamp NULL DEFAULT NULL,
  `trial_duration` int(11) NOT NULL DEFAULT '0',
  `vat_percentage` double(8,2) NOT NULL DEFAULT '0.00',
  `vat_amount` double(8,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_canceled` tinyint(4) NOT NULL DEFAULT '0',
  `payment_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_notified` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_subscriber_features`
--

CREATE TABLE `package_subscriber_features` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_subscriber_log_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_subscriber_limits`
--

CREATE TABLE `package_subscriber_limits` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_package_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_limited` tinyint(1) NOT NULL DEFAULT '1',
  `limit_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_subscriber_logs`
--

CREATE TABLE `package_subscriber_logs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_package_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_price` decimal(24,2) NOT NULL DEFAULT '0.00',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `vat_percentage` double(8,2) NOT NULL DEFAULT '0.00',
  `vat_amount` double(8,2) NOT NULL DEFAULT '0.00',
  `payment_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `primary_transaction_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_requests`
--

CREATE TABLE `payment_requests` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payer_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `gateway_callback_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `success_hook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_hook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `additional_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `payer_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `external_redirect_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_information` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `attribute_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_platform` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_description` text COLLATE utf8mb4_unicode_ci,
  `booking_schedule` datetime DEFAULT NULL,
  `is_booked` tinyint(1) NOT NULL DEFAULT '0',
  `is_checked` tinyint(1) NOT NULL DEFAULT '0',
  `customer_user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_address_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_additional_information`
--

CREATE TABLE `post_additional_information` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_additional_instructions`
--

CREATE TABLE `post_additional_instructions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `post_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_bids`
--

CREATE TABLE `post_bids` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offered_price` decimal(24,2) NOT NULL DEFAULT '0.00',
  `provider_note` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person_phone` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `service_man_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `service_capacity_per_day` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `rating_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `avg_rating` double(8,4) NOT NULL DEFAULT '0.0000',
  `commission_status` tinyint(1) NOT NULL DEFAULT '0',
  `commission_percentage` double(8,4) NOT NULL DEFAULT '0.0000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coordinates` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `service_availability` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_notification_setups`
--

CREATE TABLE `provider_notification_setups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_setup_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_settings`
--

CREATE TABLE `provider_settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_values` longtext COLLATE utf8mb4_unicode_ci,
  `test_values` longtext COLLATE utf8mb4_unicode_ci,
  `settings_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'live',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_sub_category`
--

CREATE TABLE `provider_sub_category` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_notifications`
--

CREATE TABLE `push_notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_ids` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_users` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '["customer"]',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `push_notification_users`
--

CREATE TABLE `push_notification_users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `push_notification_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_searches`
--

CREATE TABLE `recent_searches` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keyword` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recent_views`
--

CREATE TABLE `recent_views` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_service_view` int(11) NOT NULL DEFAULT '0',
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_category_view` int(11) NOT NULL DEFAULT '0',
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_sub_category_view` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_rating` int(11) NOT NULL DEFAULT '1',
  `review_comment` text COLLATE utf8mb4_unicode_ci,
  `review_images` text COLLATE utf8mb4_unicode_ci,
  `booking_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `customer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_accesses`
--

CREATE TABLE `role_accesses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `section_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_view` tinyint(4) NOT NULL DEFAULT '1',
  `can_add` tinyint(4) NOT NULL DEFAULT '0',
  `can_update` tinyint(4) NOT NULL DEFAULT '0',
  `can_delete` tinyint(4) NOT NULL DEFAULT '0',
  `can_export` tinyint(4) NOT NULL DEFAULT '0',
  `can_manage_status` tinyint(4) NOT NULL DEFAULT '0',
  `can_approve_or_deny` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `can_assign_serviceman` tinyint(4) NOT NULL DEFAULT '0',
  `can_give_feedback` tinyint(4) NOT NULL DEFAULT '0',
  `can_take_backup` tinyint(4) NOT NULL DEFAULT '0',
  `can_change_status` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `searched_data`
--

CREATE TABLE `searched_data` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response_data_count` int(11) NOT NULL DEFAULT '0',
  `volume` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `servicemen`
--

CREATE TABLE `servicemen` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax` decimal(24,3) NOT NULL DEFAULT '0.000',
  `order_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `rating_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `avg_rating` double(8,4) NOT NULL DEFAULT '0.0000',
  `min_bidding_price` decimal(24,3) NOT NULL DEFAULT '0.000',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `short_description`, `description`, `cover_image`, `thumbnail`, `category_id`, `sub_category_id`, `tax`, `order_count`, `is_active`, `rating_count`, `avg_rating`, `min_bidding_price`, `deleted_at`, `created_at`, `updated_at`) VALUES
('f4d44b69-9b28-4c3c-8956-1c223367dd3a', 'Test Service', 'This is a fake short description for the test service.', '<p>This is a fake long description for test service. In the real service, valid informations will be given here.</p>', '2023-08-31-64ef3a3e696a2.png', '2023-08-31-64ef3a3e6d6c0.png', '5e5c0fdb-9ad7-4075-bcc1-d7523efde8c6', 'b4fcd6d4-ba80-4e20-9ee2-f07a8b1466c6', '10.000', 0, 1, 0, 0.0000, '97.000', NULL, '2023-08-30 19:46:54', '2023-08-30 19:46:54');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:guid)',
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'pending,accepted,denied',
  `admin_feedback` text COLLATE utf8mb4_unicode_ci,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_tag`
--

CREATE TABLE `service_tag` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `storages`
--

CREATE TABLE `storages` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_column` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `storage_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribed_services`
--

CREATE TABLE `subscribed_services` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_category_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_subscribed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_booking_types`
--

CREATE TABLE `subscription_booking_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_packages`
--

CREATE TABLE `subscription_packages` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(24,2) NOT NULL DEFAULT '0.00',
  `duration` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_package_features`
--

CREATE TABLE `subscription_package_features` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscription_package_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_package_limits`
--

CREATE TABLE `subscription_package_limits` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscription_package_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_limited` tinyint(1) NOT NULL DEFAULT '1',
  `limit_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_subscriber_bookings`
--

CREATE TABLE `subscription_subscriber_bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `provider_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_subscriber_log_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tag` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_trx_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `booking_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trx_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debit` decimal(24,2) NOT NULL DEFAULT '0.00',
  `credit` decimal(24,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(24,2) NOT NULL DEFAULT '0.00',
  `from_user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `from_user_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_user_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_note` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translations`
--

CREATE TABLE `translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `translationable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `translationable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identification_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identification_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'nid',
  `identification_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '[]',
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'male',
  `profile_image` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default.png',
  `fcm_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_phone_verified` tinyint(1) NOT NULL DEFAULT '0',
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `user_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `wallet_balance` decimal(24,3) NOT NULL DEFAULT '0.000',
  `loyalty_point` decimal(24,3) NOT NULL DEFAULT '0.000',
  `ref_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `login_hit_count` tinyint(4) NOT NULL DEFAULT '0',
  `is_temp_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `temp_block_time` timestamp NULL DEFAULT NULL,
  `current_language_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `address_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_guest` tinyint(1) NOT NULL DEFAULT '0',
  `house` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `floor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_verifications`
--

CREATE TABLE `user_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `identity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identity_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `hit_count` tinyint(4) NOT NULL DEFAULT '0',
  `is_temp_blocked` tinyint(1) NOT NULL DEFAULT '0',
  `temp_block_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_zones`
--

CREATE TABLE `user_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variations`
--

CREATE TABLE `variations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `variant` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(24,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variations`
--

INSERT INTO `variations` (`id`, `variant`, `variant_key`, `service_id`, `zone_id`, `price`, `created_at`, `updated_at`) VALUES
(1, 'Variant 1', 'Variant-1', 'f4d44b69-9b28-4c3c-8956-1c223367dd3a', 'a1614dbe-4732-11ee-9702-dee6e8d77be4', '100.000', '2023-08-30 19:46:54', '2023-08-30 19:46:54');

-- --------------------------------------------------------

--
-- Table structure for table `visited_services`
--

CREATE TABLE `visited_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_methods`
--

CREATE TABLE `withdrawal_methods` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method_fields` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_requests`
--

CREATE TABLE `withdraw_requests` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_updated_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(24,2) NOT NULL DEFAULT '0.00',
  `request_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `withdrawal_method_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `withdrawal_method_fields` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `coordinates` polygon DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`id`, `name`, `coordinates`, `is_active`, `created_at`, `updated_at`) VALUES
('a1614dbe-4732-11ee-9702-dee6e8d77be4', 'All over the World', 0x0000000001030000000100000025000000de577956da4566c05ffb2847ee105140de57795612f065c0f4b1e90cbee75040de577956227765c054d9a52e5ab25040de577956123c65c059bde101cc885040de5779563a5f65c0c59ef914fa635040de5779561a9d65c0958caee6411a5040de57795642ed65c05754a92b533e5040de5779560a4366c0ac30798df16a5040de577956d23e66c06bbe60bb649f504022a886a9157f664044ccf8a6df91504022a886a9d5736640616fece9b340504022a886a9f562664027dbc3505a504f408b31538b82356440d6f5637e0f4e4c408b31538b62b162400f243ffb874b1ac08b31538be21e6640d1c08f519a4e43c08b31538b022d6540cb0b0984db1047c08b31538b229861406e3c7181204342c01563a61685145d403c18fa5e73ad40c09ec54c2d8a634640756126aaa37138c03c8b995a142b3440ccd3e164b0e540c0319d59e9ba3151c04883e9781b1b4bc0319d59e97a8e52c0937dad3429d548c0319d59e97a2651c0c48214170a0f33c0319d59e9baa753c02b9648092dfb20c0eb9c59e9ba7459c0b584594c36e63240eb9c59e93aa45ec0918fdd9dcaa6434075ceac741d8a63c02ea401df41044d4075ceac745dd064c08073b254e767504075ceac749dae64c08ed0c2d00d28514075ceac74bd8f63c0a529db2454bf5140eb9c59e97aea5fc01b075ce0df735140319d59e93abe53c0e8f8976fb6a754403c8b995a141739405ee27cdd06ed53409ec54c2d8a3e4d4029474755d4c152401563a616c54f5a400a438459e4e652408b31538b62fd6140527bedec1df55140de577956da4566c05ffb2847ee105140, 1, '2023-08-30 12:41:41', '2023-08-30 12:41:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `added_to_carts`
--
ALTER TABLE `added_to_carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `addon_settings`
--
ALTER TABLE `addon_settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_settings_id_index` (`id`);

--
-- Indexes for table `advertisements`
--
ALTER TABLE `advertisements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `advertisement_attachments`
--
ALTER TABLE `advertisement_attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `advertisement_notes`
--
ALTER TABLE `advertisement_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `advertisement_settings`
--
ALTER TABLE `advertisement_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_details`
--
ALTER TABLE `bank_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bonuses`
--
ALTER TABLE `bonuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_additional_information`
--
ALTER TABLE `booking_additional_information`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_details`
--
ALTER TABLE `booking_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_details_amounts`
--
ALTER TABLE `booking_details_amounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_offline_payments`
--
ALTER TABLE `booking_offline_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_partial_payments`
--
ALTER TABLE `booking_partial_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_schedule_histories`
--
ALTER TABLE `booking_schedule_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_status_histories`
--
ALTER TABLE `booking_status_histories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `business_settings`
--
ALTER TABLE `business_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_zone`
--
ALTER TABLE `category_zone`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_conversations`
--
ALTER TABLE `channel_conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_lists`
--
ALTER TABLE `channel_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `channel_users`
--
ALTER TABLE `channel_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversation_files`
--
ALTER TABLE `conversation_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupon_customers`
--
ALTER TABLE `coupon_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_settings`
--
ALTER TABLE `data_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discount_types`
--
ALTER TABLE `discount_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_role_accesses`
--
ALTER TABLE `employee_role_accesses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_role_sections`
--
ALTER TABLE `employee_role_sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorite_providers`
--
ALTER TABLE `favorite_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorite_services`
--
ALTER TABLE `favorite_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ignored_posts`
--
ALTER TABLE `ignored_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_page_features`
--
ALTER TABLE `landing_page_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_page_specialities`
--
ALTER TABLE `landing_page_specialities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `landing_page_testimonials`
--
ALTER TABLE `landing_page_testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_setups`
--
ALTER TABLE `login_setups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `loyalty_point_transactions`
--
ALTER TABLE `loyalty_point_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_setups`
--
ALTER TABLE `notification_setups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `offline_payments`
--
ALTER TABLE `offline_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_subscribers`
--
ALTER TABLE `package_subscribers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_subscriber_features`
--
ALTER TABLE `package_subscriber_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_subscriber_limits`
--
ALTER TABLE `package_subscriber_limits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_subscriber_logs`
--
ALTER TABLE `package_subscriber_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_additional_information`
--
ALTER TABLE `post_additional_information`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_additional_instructions`
--
ALTER TABLE `post_additional_instructions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_bids`
--
ALTER TABLE `post_bids`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `provider_notification_setups`
--
ALTER TABLE `provider_notification_setups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `provider_settings`
--
ALTER TABLE `provider_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `provider_sub_category`
--
ALTER TABLE `provider_sub_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_notifications`
--
ALTER TABLE `push_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_notification_users`
--
ALTER TABLE `push_notification_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recent_searches`
--
ALTER TABLE `recent_searches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recent_views`
--
ALTER TABLE `recent_views`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_accesses`
--
ALTER TABLE `role_accesses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `searched_data`
--
ALTER TABLE `searched_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `servicemen`
--
ALTER TABLE `servicemen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_tag`
--
ALTER TABLE `service_tag`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `storages`
--
ALTER TABLE `storages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `storages_model_id_index` (`model_id`),
  ADD KEY `storages_model_column_index` (`model_column`);

--
-- Indexes for table `subscribed_services`
--
ALTER TABLE `subscribed_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_booking_types`
--
ALTER TABLE `subscription_booking_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_packages`
--
ALTER TABLE `subscription_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_package_features`
--
ALTER TABLE `subscription_package_features`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_package_limits`
--
ALTER TABLE `subscription_package_limits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_subscriber_bookings`
--
ALTER TABLE `subscription_subscriber_bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `translations`
--
ALTER TABLE `translations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translations_translationable_id_index` (`translationable_id`),
  ADD KEY `translations_locale_index` (`locale`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_zones`
--
ALTER TABLE `user_zones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `variations`
--
ALTER TABLE `variations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `visited_services`
--
ALTER TABLE `visited_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdrawal_methods`
--
ALTER TABLE `withdrawal_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `zones_name_unique` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `added_to_carts`
--
ALTER TABLE `added_to_carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_additional_information`
--
ALTER TABLE `booking_additional_information`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_details`
--
ALTER TABLE `booking_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_schedule_histories`
--
ALTER TABLE `booking_schedule_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_status_histories`
--
ALTER TABLE `booking_status_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_zone`
--
ALTER TABLE `category_zone`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discount_types`
--
ALTER TABLE `discount_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_role_accesses`
--
ALTER TABLE `employee_role_accesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_role_sections`
--
ALTER TABLE `employee_role_sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorite_providers`
--
ALTER TABLE `favorite_providers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorite_services`
--
ALTER TABLE `favorite_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=194;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_additional_information`
--
ALTER TABLE `post_additional_information`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provider_notification_setups`
--
ALTER TABLE `provider_notification_setups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provider_sub_category`
--
ALTER TABLE `provider_sub_category`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_accesses`
--
ALTER TABLE `role_accesses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_tag`
--
ALTER TABLE `service_tag`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_booking_types`
--
ALTER TABLE `subscription_booking_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_subscriber_bookings`
--
ALTER TABLE `subscription_subscriber_bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translations`
--
ALTER TABLE `translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_verifications`
--
ALTER TABLE `user_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_zones`
--
ALTER TABLE `user_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `variations`
--
ALTER TABLE `variations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visited_services`
--
ALTER TABLE `visited_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
