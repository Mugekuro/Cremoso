<?php
require_once __DIR__ . '/../includes/auth.php';
redirectIfNotAdmin();

$reportsData = [
    ['id' => 'RPT-001', 'type' => 'Daily Sales Report',    'location' => 'HQ Main',    'date' => '2024-01-15', 'status' => 'Completed'],
    ['id' => 'RPT-002', 'type' => 'Inventory Check',       'location' => 'Downtown',   'date' => '2024-01-14', 'status' => 'Pending'],
    ['id' => 'RPT-003', 'type' => 'Weekly Summary',        'location' => 'HQ Main',    'date' => '2024-01-13', 'status' => 'Completed'],
    ['id' => 'RPT-004', 'type' => 'Staff Performance',     'location' => 'Downtown',   'date' => '2024-01-12', 'status' => 'In Progress'],
    ['id' => 'RPT-005', 'type' => 'Monthly Analytics',     'location' => 'All Branches','date' => '2024-01-10', 'status' => 'Completed'],
    ['id' => 'RPT-006', 'type' => 'Customer Feedback',     'location' => 'HQ Main',    'date' => '2024-01-09', 'status' => 'Pending'],
    ['id' => 'RPT-007', 'type' => 'Sales by Channel',      'location' => 'All Branches','date' => '2024-01-08', 'status' => 'Completed'],
    ['id' => 'RPT-008', 'type' => 'Product Performance',   'location' => 'Downtown',   'date' => '2024-01-07', 'status' => 'In Progress'],
];

$completed = count(array_filter($reportsData, fn($r) => $r['status'] === 'Completed'));
$pending   = count(array_filter($reportsData, fn($r) => $r['status'] === 'Pending'));
$inprog    = count(array_filter($reportsData, fn($r) => $r['status'] === 'In Progress'));
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-file-alt"></i> Reports</h1>
        <div class="user-info">
            <i class="fas fa-calendar"></i>
            <span><?= date('F d, Y') ?></span>
        </div>
    </div>

    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-value"><?= count($reportsData) ?></div>
            <div class="stat-label">Total Reports</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-double"></i></div>
            <div class="stat-value"><?= $completed ?></div>
            <div class="stat-label">Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?= $pending ?></div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-value"><?= $inprog ?></div>
            <div class="stat-label">In Progress</div>
        </div>
    </div>

    <div class="data-table">
        <h3 style="padding: 20px 20px 0; color: var(--text-dark);">All Reports</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Report Type</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reportsData as $r): ?>
                <tr>
                    <td><strong><?= $r['id'] ?></strong></td>
                    <td><?= $r['type'] ?></td>
                    <td><i class="fas fa-map-marker-alt" style="color: var(--primary); margin-right: 6px;"></i><?= $r['location'] ?></td>
                    <td><?= date('M d, Y', strtotime($r['date'])) ?></td>
                    <td>
                        <span class="status-badge <?= $r['status']=='Completed' ? 'status-completed' : ($r['status']=='Pending' ? 'status-pending' : 'status-active') ?>">
                            <?= $r['status'] ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn-action btn-view" title="View Report" onclick="alert('Viewing report: <?= $r['id'] ?>')">
                            <i class="fas fa-eye"></i> <span>View</span>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>