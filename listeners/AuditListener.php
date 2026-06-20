<?php
require_once 'interfaces/EventListenerInterface.php';
require_once 'interfaces/AuditLogRepositoryInterface.php';
require_once 'models/AuditLog.php';

class AuditListener implements EventListenerInterface {
    private AuditLogRepositoryInterface $auditRepo;

    public function __construct(AuditLogRepositoryInterface $auditRepo) {
        $this->auditRepo = $auditRepo;
    }

    public function handle(string $event, array $payload): void {
        if (empty($payload['user_id']))   throw new InvalidArgumentException("Missing user_id in payload");
        if (empty($payload['action']))    throw new InvalidArgumentException("Missing action in payload");
        if (empty($payload['module']))    throw new InvalidArgumentException("Missing module in payload");
        if (empty($payload['record_id'])) throw new InvalidArgumentException("Missing record_id in payload");

        $this->auditRepo->log(new AuditLog([
            'user_id'    => (int) $payload['user_id'],
            'action'     => strtoupper($payload['action']),
            'module'     => strtoupper($payload['module']),
            'record_id'  => (int) $payload['record_id'],
            'old_value'  => isset($payload['old_value']) ? json_encode($payload['old_value']) : null,
            'new_value'  => isset($payload['new_value']) ? json_encode($payload['new_value']) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]));
    }
}