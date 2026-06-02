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
        $total = $this->activityModel->countAll();
        $paging = paginate($this->db, "SELECT COUNT(*) AS c FROM activity_log", [], 30);
        $logs = $this->activityModel->getRecent($paging['limit'], $paging['offset']);

        $this->view('admin/activity_log/index', [
            'logs'        => $logs,
            'paging'      => $paging,
            'active_page' => 'activity_log',
            'page_title'  => 'Activity Log',
        ], 'admin');
    }
}
