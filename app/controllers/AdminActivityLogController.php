<?php
require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class AdminActivityLogController extends BaseController
{
    private ActivityLog $activityModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->activityModel = new ActivityLog();
    }

    public function index()
    {
        $current_role = isset($_GET['role']) ? trim($_GET['role']) : '';
        if (!in_array($current_role, ['admin', 'customer', 'system'])) {
            $current_role = '';
        }

        // Get filter counts for UI
        $role_counts = $this->activityModel->getRoleCounts();

        // Build query for pagination count
        $countQuery = "SELECT COUNT(*) AS c FROM activity_log a";
        $countParams = [];
        if ($current_role === 'system') {
            $countQuery .= " WHERE a.user_id = 0";
        } elseif ($current_role !== '') {
            $countQuery .= " JOIN users u ON a.user_id = u.id WHERE u.role = ?";
            $countParams[] = $current_role;
        }

        $paging = paginate($this->db, $countQuery, $countParams, 30);
        $logs = $this->activityModel->getRecent($paging['limit'], $paging['offset'], $current_role);

        $this->view('admin/activity_log/index', [
            'logs'         => $logs,
            'paging'       => $paging,
            'active_page'  => 'activity_log',
            'page_title'   => 'Activity Log',
            'current_role' => $current_role,
            'role_counts'  => $role_counts,
        ], 'admin');
    }
}
