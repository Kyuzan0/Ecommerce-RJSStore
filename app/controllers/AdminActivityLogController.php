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
        // 1. Gather filters
        $filters = [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'role'   => isset($_GET['role']) ? trim($_GET['role']) : '',
            'action' => isset($_GET['action']) ? trim($_GET['action']) : '',
            'date'   => isset($_GET['date']) ? trim($_GET['date']) : '',
            'ip'     => isset($_GET['ip']) ? trim($_GET['ip']) : '',
        ];

        // Validate role filter
        if (!in_array($filters['role'], ['admin', 'customer', 'system'])) {
            $filters['role'] = '';
        }
        
        // Validate date filter
        if (!in_array($filters['date'], ['today', 'week', 'month'])) {
            $filters['date'] = '';
        }

        // 2. Fetch metadata & filter options
        $role_counts = $this->activityModel->getRoleCounts();
        $distinct_actions = $this->activityModel->getDistinctActions();
        $distinct_ips = $this->activityModel->getDistinctIps();
        $summary_stats = $this->activityModel->getSummaryStats();
        $activity_trend = $this->activityModel->getActivityTrend();

        // 3. Paginate
        $total_filtered = $this->activityModel->countAll($filters);
        
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per_page = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        if (!in_array($per_page, [10, 50, 100])) {
            $per_page = 10;
        }
        $total_pages = max(1, (int) ceil($total_filtered / $per_page));
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;

        $paging = [
            'page'        => $page,
            'per_page'    => $per_page,
            'total'       => $total_filtered,
            'total_pages' => $total_pages,
            'offset'      => $offset,
            'limit'       => $per_page,
        ];

        // 4. Fetch records
        $logs = $this->activityModel->getRecent($paging['limit'], $paging['offset'], $filters);

        // 5. Render view
        $this->view('admin/activity_log/index', [
            'logs'             => $logs,
            'paging'           => $paging,
            'active_page'      => 'activity_log',
            'page_title'       => 'Activity Log',
            'filters'          => $filters,
            'current_role'     => $filters['role'],
            'role_counts'      => $role_counts,
            'distinct_actions' => $distinct_actions,
            'distinct_ips'     => $distinct_ips,
            'summary_stats'    => $summary_stats,
            'activity_trend'   => $activity_trend,
        ], 'admin');
    }
}
