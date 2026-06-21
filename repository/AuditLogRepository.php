<?php
require_once 'config/Database.php';

class AuditLogRepository implements AuditLogRepositoryInterface{
    private PDO $db;

    public function __construct(){
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array {
        $stmt = $this->db->prepare("SELECT * FROM audit_log");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM audit_log WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByModule(string $module): array {
        $stmt = $this->db->prepare("SELECT * FROM audit_log WHERE module = ?");
        $stmt->execute([$module]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByDateRange(string $dateFrom, string $dateTo): array {
        $stmt = $this->db->prepare("
            SELECT * FROM audit_log
            WHERE created_at >= :date_from 
            AND created_at < DATE_ADD(:date_to, INTERVAL 1 DAY)
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([
            ':date_from' => $dateFrom,
            ':date_to' => $dateTo
        ]);
        
        return $stmt->fetchAll();
    }

    public function findByAction(string $action): array {
        $stmt = $this->db->prepare("SELECT * FROM audit_log WHERE action = ?");
        $stmt->execute([$action]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function log(AuditLog $auditLog): void {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO audit_log (
                    user_id, action, module, record_id,
                    old_value, new_value, ip_address
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $auditLog->user_id,
                $auditLog->action,
                $auditLog->module,
                $auditLog->record_id,
                $auditLog->old_value ? json_encode($auditLog->old_value) : null,
                $auditLog->new_value ? json_encode($auditLog->new_value) : null,
                $auditLog->ip_address ?? $_SERVER['REMOTE_ADDR'] ?? null
            ]);

            $this->db->commit();

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new RuntimeException("Audit log failed: " . $e->getMessage(), 0, $e);
        }
    }
}