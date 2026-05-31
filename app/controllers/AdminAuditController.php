<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/AuditLog.php';

class AdminAuditController extends BaseController
{
    private AuditLog $auditModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->auditModel = new AuditLog();
    }

    public function index()
    {
        $total = $this->auditModel->countAll();
        $paging = paginate($this->db, "SELECT COUNT(*) AS c FROM audit_log", [], 30);
        $logs = $this->auditModel->getRecent($paging['limit'], $paging['offset']);

        $this->view('admin/audit/index', [
            'logs'        => $logs,
            'paging'      => $paging,
            'active_page' => 'audit',
            'page_title'  => 'Audit Log',
        ], 'admin');
    }
}
