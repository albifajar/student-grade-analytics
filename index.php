<?php
/**
 * Student Grade Analytics - Final Presentation Dashboard
 * ====================================================================
 * Project: Student Grade Analytics
 * Study Plan: Phase 7 — Build the Final Report
 * 
 * Flow:
 *  MySQL / Mock Data ──> Raw Arrays ──> Group ──> Aggregate ──> Sort & Rank ──> Clean HTML
 * ====================================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/analytics.php';

// -------------------------------------------------------------
// 1. Data Ingestion (Phase 1 & Phase 3)
// -------------------------------------------------------------
$pdo = getDatabaseConnection();
$sourceInfo = getDataSourceInfo($pdo);
$rawStudents = getRawStudents($pdo);
$rawGrades = getRawGrades($pdo);

// -------------------------------------------------------------
// 2. Pure PHP Array Pipeline (Phases 3 to 6)
// -------------------------------------------------------------
// Group grades by student ID without SQL JOIN
$groupedGrades = groupGradesByStudent($rawGrades);

// Aggregate scores, compute average, assign letter grade and status
$enrichedStudents = enrichStudentsWithGrades($rawStudents, $groupedGrades);

// Sort descending by average score using <=> and assign rankings
$rankedStudents = rankStudents($enrichedStudents);

// Compute Subject statistics and Class performance
$subjectStats = calculateSubjectStats($rawGrades);
$classStats = calculateClassStats($rankedStudents);
$kpis = calculateOverallKPIs($rankedStudents, $rawGrades);

// -------------------------------------------------------------
// 3. User Controls: Filters & Search (Phase 2 Filter Application)
// -------------------------------------------------------------
$filterClass  = isset($_GET['class']) ? trim((string)$_GET['class']) : 'all';
$filterStatus = isset($_GET['status']) ? trim((string)$_GET['status']) : 'all';
$searchQuery  = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$currentView  = isset($_GET['view']) ? trim((string)$_GET['view']) : 'dashboard';

// Apply filters to display list
$displayStudents = $rankedStudents;

// Filter by Class
if ($filterClass !== 'all' && $filterClass !== '') {
    $displayStudents = array_values(array_filter(
        $displayStudents,
        fn($s) => $s['class'] === $filterClass
    ));
}

// Filter by Status (Passed / Failed)
if ($filterStatus !== 'all' && $filterStatus !== '') {
    $displayStudents = array_values(array_filter(
        $displayStudents,
        fn($s) => $s['status'] === $filterStatus
    ));
}

// Search by Student Name or NIS
if ($searchQuery !== '') {
    $searchLower = mb_strtolower($searchQuery);
    $displayStudents = array_values(array_filter(
        $displayStudents,
        fn($s) => str_contains(mb_strtolower($s['name']), $searchLower) ||
                  str_contains(mb_strtolower($s['nis']), $searchLower)
    ));
}

// Extract unique classes for filter dropdown
$uniqueClasses = array_unique(array_column($rawStudents, 'class'));
sort($uniqueClasses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grade Analytics | PHP Array Manipulation Study</title>
    <meta name="description" content="Clean HTML5 dashboard for Student Grade Analytics powered by PHP Array Manipulation.">
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Main Navigation Bar -->
    <header class="app-header">
        <div class="container">
            <div class="header-content">
                <div class="brand-wrapper">
                    <div class="brand-logo" aria-hidden="true">📊</div>
                    <div>
                        <h1 class="brand-title">Student Grade Analytics</h1>
                        <p class="brand-subtitle">PHP Array Manipulation Study Case & Pipeline</p>
                    </div>
                </div>

                <div class="header-actions">
                    <div class="data-source-badge <?= htmlspecialchars($sourceInfo['type']) ?>" title="<?= htmlspecialchars($sourceInfo['detail']) ?>">
                        <span class="status-dot"></span>
                        <span><?= htmlspecialchars($sourceInfo['label']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="container">
        
        <!-- Navigation View Tabs -->
        <nav class="view-tabs-nav" aria-label="Views">
            <a href="?view=dashboard" class="tab-button <?= $currentView === 'dashboard' ? 'active' : '' ?>">
                <span>📊 Executive Report</span>
                <span class="tab-count"><?= count($rankedStudents) ?> Students</span>
            </a>
            <a href="?view=pipeline" class="tab-button <?= $currentView === 'pipeline' ? 'active' : '' ?>">
                <span>🎓 Study Pipeline Inspector</span>
                <span class="tab-count">7 Phases</span>
            </a>
            <a href="?view=database" class="tab-button <?= $currentView === 'database' ? 'active' : '' ?>">
                <span>🗄️ Database Setup &amp; SQL</span>
            </a>
        </nav>

        <?php if ($currentView === 'dashboard'): ?>
            <!-- ============================================================== -->
            <!-- DASHBOARD VIEW: Final Report (Phase 7)                        -->
            <!-- ============================================================== -->

            <!-- KPI Metric Summary Cards -->
            <section class="kpi-grid" aria-label="Key Performance Indicators">
                <div class="kpi-card indigo">
                    <div>
                        <div class="kpi-label">Total Students</div>
                        <div class="kpi-value numeric"><?= $kpis['total_students'] ?></div>
                        <div class="kpi-subtext">Across <?= count($classStats) ?> Classes</div>
                    </div>
                    <div class="kpi-icon-box">👥</div>
                </div>

                <div class="kpi-card emerald">
                    <div>
                        <div class="kpi-label">Overall Average</div>
                        <div class="kpi-value numeric"><?= number_format($kpis['overall_average'], 1) ?></div>
                        <div class="kpi-subtext"><?= $kpis['total_passed'] ?> Passed (<?= $kpis['pass_rate'] ?>%)</div>
                    </div>
                    <div class="kpi-icon-box">📈</div>
                </div>

                <div class="kpi-card amber">
                    <div>
                        <div class="kpi-label">Highest Score</div>
                        <div class="kpi-value numeric"><?= number_format($kpis['highest_score'], 1) ?></div>
                        <div class="kpi-subtext">
                            Top: <strong><?= htmlspecialchars($kpis['highest_student']['name'] ?? 'N/A') ?></strong>
                        </div>
                    </div>
                    <div class="kpi-icon-box">🏆</div>
                </div>

                <div class="kpi-card rose">
                    <div>
                        <div class="kpi-label">Lowest Score</div>
                        <div class="kpi-value numeric"><?= number_format($kpis['lowest_score'], 1) ?></div>
                        <div class="kpi-subtext">
                            <?= $kpis['total_failed'] ?> students below 75 threshold
                        </div>
                    </div>
                    <div class="kpi-icon-box">📉</div>
                </div>
            </section>

            <!-- Student Ranking Table Section -->
            <section class="content-section" aria-labelledby="rankings-title">
                <div class="section-header">
                    <div>
                        <h2 id="rankings-title" class="section-title">
                            <span>🏆</span> Student Ranking &amp; Report
                        </h2>
                        <p class="section-desc">Sorted descending by average score using PHP <code>usort()</code> and <code>&lt;=&gt;</code> spaceship operator.</p>
                    </div>

                    <!-- Filter Toolbar -->
                    <form method="GET" action="index.php" class="filter-toolbar">
                        <input type="hidden" name="view" value="dashboard">
                        
                        <div class="filter-group">
                            <label for="filter-class" class="filter-label">Class:</label>
                            <select id="filter-class" name="class" class="filter-select" onchange="this.form.submit()">
                                <option value="all">All Classes</option>
                                <?php foreach ($uniqueClasses as $cls): ?>
                                    <option value="<?= htmlspecialchars($cls) ?>" <?= $filterClass === $cls ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cls) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="filter-status" class="filter-label">Status:</label>
                            <select id="filter-status" name="status" class="filter-select" onchange="this.form.submit()">
                                <option value="all">All Status</option>
                                <option value="Passed" <?= $filterStatus === 'Passed' ? 'selected' : '' ?>>Passed (&gt;= 75)</option>
                                <option value="Failed" <?= $filterStatus === 'Failed' ? 'selected' : '' ?>>Failed (&lt; 75)</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <input type="text" name="search" class="search-input" placeholder="Search name or NIS..." value="<?= htmlspecialchars($searchQuery) ?>">
                            <button type="submit" class="btn-filter">Filter</button>
                            <?php if ($filterClass !== 'all' || $filterStatus !== 'all' || $searchQuery !== ''): ?>
                                <a href="?view=dashboard" class="btn-reset">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Rank</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Grades Breakdown</th>
                                <th class="numeric" style="text-align: right;">Average</th>
                                <th style="text-align: center;">Grade</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($displayStudents)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                                        No students found matching the selected filter criteria.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($displayStudents as $student): ?>
                                    <?php
                                        $rank = $student['rank'];
                                        $rankClass = 'rank-other';
                                        if ($rank === 1) $rankClass = 'rank-1';
                                        elseif ($rank === 2) $rankClass = 'rank-2';
                                        elseif ($rank === 3) $rankClass = 'rank-3';

                                        $gradeClass = 'grade-' . strtolower($student['grade']);
                                        $statusClass = $student['status'] === 'Passed' ? 'status-passed' : 'status-failed';
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="rank-badge <?= $rankClass ?>" title="Rank <?= $rank ?>">
                                                <?php if ($rank === 1): ?>🥇<?php elseif ($rank === 2): ?>🥈<?php elseif ($rank === 3): ?>🥉<?php else: ?><?= $rank ?><?php endif; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="student-name-cell">
                                                <span class="student-name"><?= htmlspecialchars($student['name']) ?></span>
                                                <span class="student-nis">NIS: <?= htmlspecialchars($student['nis']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="class-tag"><?= htmlspecialchars($student['class']) ?></span>
                                        </td>
                                        <td>
                                            <div class="subject-pills">
                                                <?php foreach ($student['grades'] as $g): ?>
                                                    <span class="subject-pill">
                                                        <span class="name"><?= htmlspecialchars($g['subject']) ?>:</span>
                                                        <span class="score numeric"><?= number_format($g['score'], 0) ?></span>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td class="numeric score-cell" style="text-align: right;">
                                            <?= number_format($student['average'], 1) ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="grade-badge <?= $gradeClass ?>">
                                                <?= htmlspecialchars($student['grade']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-pill <?= $statusClass ?>">
                                                <?= $student['status'] === 'Passed' ? '✓ Passed' : '✕ Failed' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Two Column Layout: Subject Stats & Class Breakdown -->
            <div class="analytics-two-col">
                
                <!-- Subject Statistics (Study 13) -->
                <section class="content-section" aria-labelledby="subjects-title">
                    <div class="section-header">
                        <div>
                            <h2 id="subjects-title" class="section-title">
                                <span>📚</span> Subject Statistics
                            </h2>
                            <p class="section-desc">Computed via PHP <code>array_reduce()</code>, <code>min()</code>, <code>max()</code></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Average Score</th>
                                    <th class="numeric" style="text-align: right;">Min</th>
                                    <th class="numeric" style="text-align: right;">Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectStats as $subject => $stats): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?= htmlspecialchars($subject) ?></td>
                                        <td>
                                            <div class="score-bar-wrapper">
                                                <div class="score-bar-track">
                                                    <div class="score-bar-fill" style="width: <?= min(100, max(0, $stats['average'])) ?>%;"></div>
                                                </div>
                                                <span class="numeric" style="font-weight: 700; width: 40px; text-align: right;">
                                                    <?= number_format($stats['average'], 1) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="numeric" style="text-align: right; color: var(--danger-text); font-weight: 600;">
                                            <?= number_format($stats['min'], 0) ?>
                                        </td>
                                        <td class="numeric" style="text-align: right; color: var(--success-text); font-weight: 600;">
                                            <?= number_format($stats['max'], 0) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Class Performance Breakdown (Study 14) -->
                <section class="content-section" aria-labelledby="classes-title">
                    <div class="section-header">
                        <div>
                            <h2 id="classes-title" class="section-title">
                                <span>🏫</span> Class Performance
                            </h2>
                            <p class="section-desc">Grouped by Class with pass rates &amp; top student</p>
                        </div>
                    </div>
                    <div class="class-cards-grid">
                        <?php foreach ($classStats as $className => $cData): ?>
                            <div class="class-card">
                                <div class="class-card-header">
                                    <span class="class-card-name"><?= htmlspecialchars($className) ?></span>
                                    <span class="class-tag"><?= $cData['total_students'] ?> Students</span>
                                </div>
                                <div class="class-card-stats">
                                    <div class="class-stat-row">
                                        <span class="label">Class Average:</span>
                                        <span class="val numeric"><?= number_format($cData['class_average'], 1) ?></span>
                                    </div>
                                    <div class="class-stat-row">
                                        <span class="label">Passing Rate:</span>
                                        <span class="val numeric" style="color: var(--success-text);">
                                            <?= $cData['pass_rate_percent'] ?>% (<?= $cData['passed_count'] ?>/<?= $cData['total_students'] ?>)
                                        </span>
                                    </div>
                                    <div class="class-stat-row">
                                        <span class="label">Top Performer:</span>
                                        <span class="val" style="color: var(--primary);">
                                            <?= htmlspecialchars($cData['top_student']['name'] ?? 'N/A') ?> (<?= number_format($cData['top_student']['average'] ?? 0, 1) ?>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>

        <?php elseif ($currentView === 'pipeline'): ?>
            <!-- ============================================================== -->
            <!-- STUDY PIPELINE VIEW: Educational breakdown of Phases 1 to 6    -->
            <!-- ============================================================== -->
            <div class="content-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">🎓 PHP Array Manipulation Journey</h2>
                        <p class="section-desc">Inspect how the raw data transitions through each phase of the study plan.</p>
                    </div>
                </div>

                <div class="study-phase-accordion">
                    
                    <!-- Phase 1 -->
                    <div class="study-phase-box">
                        <div class="phase-header">
                            <div>
                                <span class="phase-badge">Phase 1</span>
                                <span class="phase-title">Database &rarr; Raw PHP Arrays</span>
                                <div class="phase-desc">Fetch records using <code>fetchAll(PDO::FETCH_ASSOC)</code> into plain PHP arrays.</div>
                            </div>
                        </div>
                        <div class="phase-body">
                            <div class="code-snippet-box">
<pre><code>$students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);
$grades   = $pdo->query("SELECT * FROM grades")->fetchAll(PDO::FETCH_ASSOC);</code></pre>
                            </div>
                            <strong>First 3 Students Raw Array:</strong>
                            <div class="raw-output-preview"><?= htmlspecialchars(print_r(array_slice($rawStudents, 0, 3), true)) ?></div>
                        </div>
                    </div>

                    <!-- Phase 2 -->
                    <div class="study-phase-box">
                        <div class="phase-header">
                            <div>
                                <span class="phase-badge">Phase 2</span>
                                <span class="phase-title">Filter &amp; Transform</span>
                                <div class="phase-desc">Filtering by class using <code>array_filter()</code> and transforming scores with <code>array_map()</code>.</div>
                            </div>
                        </div>
                        <div class="phase-body">
                            <div class="code-snippet-box">
<pre><code>// Example: Filter only Class X-A
$filtered = array_filter($students, fn($s) => $s['class'] === 'X-A');</code></pre>
                            </div>
                            <strong>Filtered X-A Output (Sample):</strong>
                            <div class="raw-output-preview"><?= htmlspecialchars(print_r(array_slice(filterStudentsByClass($rawStudents, 'X-A'), 0, 3), true)) ?></div>
                        </div>
                    </div>

                    <!-- Phase 3 -->
                    <div class="study-phase-box">
                        <div class="phase-header">
                            <div>
                                <span class="phase-badge">Phase 3</span>
                                <span class="phase-title">Group Grades by Student ID</span>
                                <div class="phase-desc">Transforming flat grades into nested arrays using dynamic student keys without SQL JOIN.</div>
                            </div>
                        </div>
                        <div class="phase-body">
                            <div class="code-snippet-box">
<pre><code>$grouped = [];
foreach ($grades as $grade) {
    $grouped[$grade['student_id']][] = [
        'subject' => $grade['subject'],
        'score'   => (float)$grade['score']
    ];
}</code></pre>
                            </div>
                            <strong>Grouped Grades for Student ID #1 &amp; #2:</strong>
                            <div class="raw-output-preview"><?= htmlspecialchars(print_r(array_slice($groupedGrades, 0, 2, true), true)) ?></div>
                        </div>
                    </div>

                    <!-- Phase 4 -->
                    <div class="study-phase-box">
                        <div class="phase-header">
                            <div>
                                <span class="phase-badge">Phase 4</span>
                                <span class="phase-title">Aggregation &amp; Derived Dataset</span>
                                <div class="phase-desc">Calculating averages with <code>array_sum() / count()</code>, mapping letter grades and pass status.</div>
                            </div>
                        </div>
                        <div class="phase-body">
                            <div class="code-snippet-box">
<pre><code>$scores = array_column($grades, 'score');
$average = round(array_sum($scores) / count($scores), 1);
$grade = $average >= 90 ? 'A' : ($average >= 80 ? 'B' : ...);
$status = $average >= 75 ? 'Passed' : 'Failed';</code></pre>
                            </div>
                            <strong>Enriched Student Record (Derived Dataset):</strong>
                            <div class="raw-output-preview"><?= htmlspecialchars(print_r($enrichedStudents[0] ?? [], true)) ?></div>
                        </div>
                    </div>

                    <!-- Phase 5 -->
                    <div class="study-phase-box">
                        <div class="phase-header">
                            <div>
                                <span class="phase-badge">Phase 5</span>
                                <span class="phase-title">Sorting &amp; Ranking</span>
                                <div class="phase-desc">Using <code>usort()</code> with the spaceship operator <code>&lt;=&gt;</code> and assigning dynamic ranks.</div>
                            </div>
                        </div>
                        <div class="phase-body">
                            <div class="code-snippet-box">
<pre><code>usort($students, fn($a, $b) => $b['average'] &lt;=&gt; $a['average']);

$rank = 1;
foreach ($students as &amp;$student) {
    $student['rank'] = $rank++;
}</code></pre>
                            </div>
                            <strong>Top 3 Ranked Students:</strong>
                            <div class="raw-output-preview"><?= htmlspecialchars(print_r(array_slice($rankedStudents, 0, 3), true)) ?></div>
                        </div>
                    </div>

                    <!-- Phase 6 -->
                    <div class="study-phase-box">
                        <div class="phase-header">
                            <div>
                                <span class="phase-badge">Phase 6</span>
                                <span class="phase-title">Advanced Operations: Subjects &amp; Class Groups</span>
                                <div class="phase-desc">Subject statistics (<code>min()</code>, <code>max()</code>, <code>array_reduce()</code>) and Class analysis.</div>
                            </div>
                        </div>
                        <div class="phase-body">
                            <div class="code-snippet-box">
<pre><code>// Subject min, max, average
$subjectStats = calculateSubjectStats($rawGrades);

// Class grouping and pass rate calculation
$classStats = calculateClassStats($rankedStudents);</code></pre>
                            </div>
                            <strong>Subject Statistics Array:</strong>
                            <div class="raw-output-preview"><?= htmlspecialchars(print_r($subjectStats, true)) ?></div>
                        </div>
                    </div>

                </div>
            </div>

        <?php elseif ($currentView === 'database'): ?>
            <!-- ============================================================== -->
            <!-- DATABASE INTEGRATION VIEW: Steps & Scaffolding                -->
            <!-- ============================================================== -->
            <div class="content-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">🗄️ Database Integration Guide</h2>
                        <p class="section-desc">Follow these steps whenever you are ready to connect your live MySQL database.</p>
                    </div>
                </div>

                <div class="db-guide-box">
                    <div class="db-steps-list">
                        <div class="db-step-item">
                            <div class="step-number">1</div>
                            <div class="step-info">
                                <h4>Create your MySQL Database</h4>
                                <p>Create a new MySQL database (e.g. <code>student_analytics</code>) using MySQL CLI, phpMyAdmin, or TablePlus:</p>
                                <div class="code-snippet-box" style="margin-top: 0.5rem;">
<pre><code>CREATE DATABASE student_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="db-step-item">
                            <div class="step-number">2</div>
                            <div class="step-info">
                                <h4>Import <code>database.sql</code></h4>
                                <p>We have pre-created the full schema and seed data in <code>database.sql</code>. Execute it with:</p>
                                <div class="code-snippet-box" style="margin-top: 0.5rem;">
<pre><code>mysql -u root -p student_analytics &lt; database.sql</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="db-step-item">
                            <div class="step-number">3</div>
                            <div class="step-info">
                                <h4>Update Credentials in <code>db.php</code></h4>
                                <p>Open <code>db.php</code> and set your MySQL username and password constants:</p>
                                <div class="code-snippet-box" style="margin-top: 0.5rem;">
<pre><code>const DB_HOST = '127.0.0.1';
const DB_NAME = 'student_analytics';
const DB_USER = 'your_username';
const DB_PASS = 'your_password';</code></pre>
                                </div>
                                <p>Once updated, refresh this page and the badge in the top right will automatically switch from <strong>Built-in Mock Arrays</strong> to <strong>Live MySQL Database</strong>!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="container">
            <p>PHP Array Manipulation Study Plan &bull; Student Grade Analytics &bull; Designed with clean HTML5 &amp; Vanilla CSS</p>
        </div>
    </footer>

</body>
</html>
