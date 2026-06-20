<?php
session_start();

require_once 'config/Database.php';
require_once 'utils/AutoModel.php';
require_once 'middleware/auth.php';

// ─── MODELS ──────────────────────────────────────
require_once 'models/Source.php';
require_once 'models/Payable.php';
require_once 'models/BankAccount.php';
require_once 'models/AuditLog.php';
require_once 'models/User.php';              // ← here

// ─── INTERFACES ──────────────────────────────────
require_once 'interfaces/RepositoryInterface.php';
require_once 'interfaces/SourceRepositoryInterface.php';
require_once 'interfaces/PayableRepositoryInterface.php';
require_once 'interfaces/BankAccountRepositoryInterface.php';
require_once 'interfaces/AuditLogRepositoryInterface.php';
require_once 'interfaces/ReportStrategyInterface.php';
require_once 'interfaces/EventListenerInterface.php';
require_once 'interfaces/UserRepositoryInterface.php';  // ← here

// ─── REPOSITORIES ────────────────────────────────
require_once 'repositories/SourceRepository.php';
require_once 'repositories/PayableRepository.php';
require_once 'repositories/BankAccountRepository.php';
require_once 'repositories/AuditLogRepository.php';
require_once 'repositories/UserRepository.php';         // ← here

// ─── SERVICES ────────────────────────────────────
require_once 'services/AuthService.php';                // ← here
require_once 'services/SourceService.php';
require_once 'services/PayableService.php';
require_once 'services/BankAccountService.php';
require_once 'services/AuditLogService.php';
require_once 'services/ReportService.php';

// ─── STRATEGIES ──────────────────────────────────
require_once 'strategies/CashFlowReportStrategy.php';
require_once 'strategies/ReconciliationReportStrategy.php';

// ─── EVENTS ──────────────────────────────────────
require_once 'events/EventDispatcher.php';
require_once 'listeners/AuditListener.php';

// ─── CONTROLLERS ─────────────────────────────────
require_once 'controllers/AuthController.php';
require_once 'controllers/SourceController.php';
require_once 'controllers/PayableController.php';
require_once 'controllers/BankController.php';
require_once 'controllers/ReportController.php';
require_once 'controllers/AuditController.php';

// ─── DTO ─────────────────────────────────────────
require_once 'dto/request/SourceRequest.php';
require_once 'dto/request/PayableRequest.php';
require_once 'dto/request/BankAccountRequest.php';
require_once 'dto/response/SourceResponse.php';
require_once 'dto/response/PayableResponse.php';
require_once 'dto/response/BankAccountResponse.php';
require_once 'dto/response/AuditLogResponse.php';

// ─── ROUTING ─────────────────────────────────────
$page        = $_GET['page'] ?? 'login';
$publicPages = ['login'];

if (!in_array($page, $publicPages, true)) {
    requireAuth();
}

match($page) {
    'login'     => (new AuthController())->handle(),
    'dashboard' => require 'views/dashboard.php',
    'sources'   => (new SourceController())->handle(),
    'payables'  => (new PayableController())->handle(),
    'banks'     => (new BankAccountController())->handle(),
    'audit'     => (new AuditController())->handle(),
    default     => require 'views/404.php'
};