<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/utils/AutoModel.php';
require_once __DIR__ . '/middleware/auth.php';

require_once __DIR__ . '/entity/Source.php';
require_once __DIR__ . '/entity/Payable.php';
require_once __DIR__ . '/entity/BankAccount.php';
require_once __DIR__ . '/entity/AuditLog.php';
require_once __DIR__ . '/entity/User.php';

require_once __DIR__ . '/interfaces/RepositoryInterface.php';
require_once __DIR__ . '/interfaces/SourceRepositoryInterface.php';
require_once __DIR__ . '/interfaces/PayableRepositoryInterface.php';
require_once __DIR__ . '/interfaces/BankAccountRepositoryInterface.php';
require_once __DIR__ . '/interfaces/AuditLogRepositoryInterface.php';
require_once __DIR__ . '/interfaces/ReportStrategyInterface.php';
require_once __DIR__ . '/interfaces/EventListenerInterface.php';
require_once __DIR__ . '/interfaces/UserRepositoryInterface.php';

require_once __DIR__ . '/repository/SourceRepository.php';
require_once __DIR__ . '/repository/PayableRepository.php';
require_once __DIR__ . '/repository/BankAccountRepository.php';
require_once __DIR__ . '/repository/AuditLogRepository.php';
require_once __DIR__ . '/repository/UserRepository.php';

require_once __DIR__ . '/service/AuthService.php';
require_once __DIR__ . '/service/SourceService.php';
require_once __DIR__ . '/service/PayableService.php';
require_once __DIR__ . '/service/BankAccountService.php';
require_once __DIR__ . '/service/AuditLogService.php';

require_once __DIR__ . '/strategies/CashFlowReportStrategy.php';
require_once __DIR__ . '/strategies/ReconciliationReportStrategy.php';

require_once __DIR__ . '/events/EventDispatcher.php';
require_once __DIR__ . '/listeners/AuditListener.php';

require_once __DIR__ . '/controller/AuthController.php';
require_once __DIR__ . '/controller/SourceController.php';
require_once __DIR__ . '/controller/PayableController.php';
require_once __DIR__ . '/controller/BankAccountController.php';
require_once __DIR__ . '/controller/AuditLogController.php';

require_once __DIR__ . '/dto/request/SourceRequest.php';
require_once __DIR__ . '/dto/request/PayableRequest.php';
require_once __DIR__ . '/dto/request/BankAccountRequest.php';
require_once __DIR__ . '/dto/response/SourceResponse.php';
require_once __DIR__ . '/dto/response/PayableResponse.php';
require_once __DIR__ . '/dto/response/BankAccountResponse.php';
require_once __DIR__ . '/dto/response/AuditLogResponse.php';

require_once __DIR__ . '/interfaces/ReportStrategyInterface.php';
require_once __DIR__ . '/strategies/CashFlowReportStrategy.php';
require_once __DIR__ . '/strategies/ReconciliationReportStrategy.php';
require_once __DIR__ . '/controller/ReportsController.php';

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
    'reports' => (new ReportsController(
        new CashFlowReportStrategy(new SourceRepository(), new PayableRepository()),
        new ReconciliationReportStrategy(new BankAccountRepository())
    ))->handle(),
    default     => require 'views/404.php'
};