<?php
/**
 * Core PHP Array Manipulation & Analytics Library
 * ====================================================================
 * Project: Student Grade Analytics
 * 
 * This file implements pure PHP array operations according to the study plan:
 *  - Phase 2: foreach, filter (manual & array_filter), transform (array_map)
 *  - Phase 3: Group grades by student_id (nested arrays & dynamic keys)
 *  - Phase 4: Aggregation (array_sum, count, grade letter mapping, pass/fail status)
 *  - Phase 5: Sorting & Ranking (usort, spaceship operator <=>, rank assignment)
 *  - Phase 6: Advanced operations (highest/lowest, subject stats with array_reduce, class grouping)
 *  - Phase 7: Combine everything for the final report
 * ====================================================================
 */

declare(strict_types=1);

/**
 * Phase 2 — Study 3: Filter Students by Class
 * Demonstrates both array_filter() and manual foreach equivalent.
 * 
 * @param array<int, array> $students
 * @param string $className e.g. 'X-A', 'X-B', or 'all'
 * @return array
 */
function filterStudentsByClass(array $students, string $className = 'all'): array
{
    if ($className === 'all' || trim($className) === '') {
        return $students;
    }

    // Modern functional array_filter (re-indexed with array_values):
    return array_values(array_filter(
        $students,
        fn(array $student): bool => $student['class'] === $className
    ));
}

/**
 * Phase 2 — Study 3 (Alternative): Filter Students by Class using manual foreach loop.
 * Included for pedagogical comparison.
 * 
 * @param array<int, array> $students
 * @param string $className
 * @return array
 */
function filterStudentsByClassManual(array $students, string $className): array
{
    $filtered = [];
    foreach ($students as $student) {
        if ($student['class'] === $className) {
            $filtered[] = $student;
        }
    }
    return $filtered;
}

/**
 * Phase 2 — Study 4 & Phase 4 — Study 9: Convert numerical score to letter grade.
 * 
 *  90 - 100 : A
 *  80 - 89.9: B
 *  70 - 79.9: C
 *  < 70     : D
 * 
 * @param float $score
 * @return string
 */
function getScoreGrade(float $score): string
{
    if ($score >= 90.0) {
        return 'A';
    }
    if ($score >= 80.0) {
        return 'B';
    }
    if ($score >= 70.0) {
        return 'C';
    }
    return 'D';
}

/**
 * Phase 4 — Study 9: Determine Pass/Fail Status based on score threshold.
 *  >= 75 : Passed
 *  < 75  : Failed
 * 
 * @param float $score
 * @param float $passingThreshold
 * @return string 'Passed'|'Failed'
 */
function getPassStatus(float $score, float $passingThreshold = 75.0): string
{
    return $score >= $passingThreshold ? 'Passed' : 'Failed';
}

/**
 * Phase 3 — Study 6: Group grades by student_id
 * 
 * Transforms:
 * [
 *   ['student_id' => 1, 'subject' => 'Math', 'score' => 96.0],
 *   ['student_id' => 1, 'subject' => 'English', 'score' => 95.0],
 *   ['student_id' => 2, 'subject' => 'Math', 'score' => 90.0]
 * ]
 * 
 * Into:
 * [
 *   1 => [
 *     ['subject' => 'Math', 'score' => 96.0],
 *     ['subject' => 'English', 'score' => 95.0],
 *   ],
 *   2 => [
 *     ['subject' => 'Math', 'score' => 90.0]
 *   ]
 * ]
 * 
 * @param array<int, array{student_id: int, subject: string, score: float}> $grades
 * @return array<int, list<array{subject: string, score: float}>>
 */
function groupGradesByStudent(array $grades): array
{
    $grouped = [];

    foreach ($grades as $gradeRow) {
        $studentId = $gradeRow['student_id'];

        // If this student_id does not exist yet in our grouped array, initialize an empty list
        if (!isset($grouped[$studentId])) {
            $grouped[$studentId] = [];
        }

        // Append the subject and score entry
        $grouped[$studentId][] = [
            'subject' => $gradeRow['subject'],
            'score'   => (float) $gradeRow['score'],
            'grade'   => getScoreGrade((float) $gradeRow['score']),
        ];
    }

    return $grouped;
}

/**
 * Phase 4 — Study 7, 8, 9: Combine Students with Grades & Aggregate Performance
 * 
 * Enriches student array with:
 *  - subjects: list of subjects with scores
 *  - scores: flat array of numbers
 *  - total_score: sum of all scores (array_sum)
 *  - average: rounded to 1 or 2 decimals
 *  - grade: letter grade based on average
 *  - status: 'Passed' or 'Failed'
 * 
 * @param array<int, array> $students
 * @param array<int, array> $groupedGrades
 * @return array<int, array>
 */
function enrichStudentsWithGrades(array $students, array $groupedGrades): array
{
    $enriched = [];

    foreach ($students as $student) {
        $studentId = $student['id'];
        $grades = $groupedGrades[$studentId] ?? [];

        // Extract raw numeric scores
        $scores = array_column($grades, 'score');
        $subjectsCount = count($scores);

        // Phase 4 — Study 7: Calculate average using array_sum() and count()
        $totalScore = array_sum($scores);
        $average = $subjectsCount > 0 ? round($totalScore / $subjectsCount, 1) : 0.0;

        // Phase 4 — Study 9: Add Grade & Status
        $gradeLetter = getScoreGrade($average);
        $status = getPassStatus($average);

        $enriched[] = [
            'id'             => $student['id'],
            'nis'            => $student['nis'],
            'name'           => $student['name'],
            'class'          => $student['class'],
            'grades'         => $grades,
            'scores'         => $scores,
            'total_score'    => $totalScore,
            'subjects_count' => $subjectsCount,
            'average'        => $average,
            'grade'          => $gradeLetter,
            'status'         => $status,
        ];
    }

    return $enriched;
}

/**
 * Phase 5 — Study 10 & 11: Sort Students by Average Score & Generate Rankings
 * 
 * Uses usort() and the spaceship operator (<=>) in descending order.
 * Afterwards, loops to assign dynamic 1-based 'rank' index.
 * 
 * @param array<int, array> $students
 * @return array<int, array>
 */
function rankStudents(array $students): array
{
    // Clone array to prevent mutating the original in-place
    $sorted = $students;

    // Study 10: usort with spaceship operator <=>
    usort($sorted, function (array $a, array $b): int {
        // Primary sort: Average score descending (higher is better)
        if ($b['average'] !== $a['average']) {
            return $b['average'] <=> $a['average'];
        }
        // Secondary tiebreaker: Total score descending
        if ($b['total_score'] !== $a['total_score']) {
            return $b['total_score'] <=> $a['total_score'];
        }
        // Tertiary tiebreaker: Name alphabetical ascending
        return strcmp($a['name'], $b['name']);
    });

    // Study 11: Generate Ranking
    $ranked = [];
    $currentRank = 1;

    foreach ($sorted as $student) {
        $student['rank'] = $currentRank++;
        $ranked[] = $student;
    }

    return $ranked;
}

/**
 * Phase 6 — Study 13: Subject Statistics
 * 
 * Groups scores by subject and produces:
 * [
 *   'Math' => [
 *      'average' => 82.4,
 *      'min'     => 58.0,
 *      'max'     => 96.0,
 *      'count'   => 20
 *   ],
 *   ...
 * ]
 * 
 * Demonstrates dynamic grouping, min(), max(), array_sum(), and count().
 * 
 * @param array<int, array{student_id: int, subject: string, score: float}> $rawGrades
 * @return array<string, array{average: float, min: float, max: float, count: int, scores: list<float>}>
 */
function calculateSubjectStats(array $rawGrades): array
{
    // 1. Group scores by subject
    $subjectGroups = [];
    foreach ($rawGrades as $row) {
        $subject = $row['subject'];
        if (!isset($subjectGroups[$subject])) {
            $subjectGroups[$subject] = [];
        }
        $subjectGroups[$subject][] = (float) $row['score'];
    }

    // 2. Calculate statistics for each subject
    $stats = [];
    foreach ($subjectGroups as $subject => $scores) {
        $count = count($scores);
        $total = array_sum($scores);
        $avg = $count > 0 ? round($total / $count, 1) : 0.0;
        $min = !empty($scores) ? min($scores) : 0.0;
        $max = !empty($scores) ? max($scores) : 0.0;

        $stats[$subject] = [
            'subject' => $subject,
            'average' => $avg,
            'min'     => $min,
            'max'     => $max,
            'count'   => $count,
            'scores'  => $scores,
        ];
    }

    // Sort subjects alphabetically
    ksort($stats);

    return $stats;
}

/**
 * Phase 6 — Study 14: Group by Class and Calculate Class Performance
 * 
 * Groups ranked students by class, then computes:
 *  - class_average
 *  - total_students
 *  - passed_count
 *  - pass_rate_percent
 *  - top_student (highest scorer in the class)
 * 
 * @param array<int, array> $rankedStudents
 * @return array<string, array>
 */
function calculateClassStats(array $rankedStudents): array
{
    $classGroups = [];

    // Group students by class
    foreach ($rankedStudents as $student) {
        $class = $student['class'];
        if (!isset($classGroups[$class])) {
            $classGroups[$class] = [];
        }
        $classGroups[$class][] = $student;
    }

    ksort($classGroups);

    $classStats = [];
    foreach ($classGroups as $className => $studentsInClass) {
        $count = count($studentsInClass);
        $averages = array_column($studentsInClass, 'average');
        $classAvg = $count > 0 ? round(array_sum($averages) / $count, 1) : 0.0;

        $passedStudents = array_filter($studentsInClass, fn($s) => $s['status'] === 'Passed');
        $passedCount = count($passedStudents);
        $passRate = $count > 0 ? round(($passedCount / $count) * 100, 1) : 0.0;

        // Since studentsInClass is from $rankedStudents, first one is highest in this class!
        $topStudent = $studentsInClass[0] ?? null;

        $classStats[$className] = [
            'class_name'        => $className,
            'total_students'    => $count,
            'class_average'     => $classAvg,
            'passed_count'      => $passedCount,
            'failed_count'      => $count - $passedCount,
            'pass_rate_percent' => $passRate,
            'top_student'       => $topStudent,
            'students'          => $studentsInClass,
        ];
    }

    return $classStats;
}

/**
 * Phase 6 — Study 12 & Phase 7: Calculate Overall Executive KPIs
 * 
 * Produces:
 *  - total_students
 *  - overall_average
 *  - highest_average_student
 *  - lowest_average_student
 *  - highest_single_score
 *  - lowest_single_score
 *  - total_passed
 *  - total_failed
 *  - pass_rate
 * 
 * @param array<int, array> $rankedStudents
 * @param array<int, array> $rawGrades
 * @return array
 */
function calculateOverallKPIs(array $rankedStudents, array $rawGrades): array
{
    $totalStudents = count($rankedStudents);
    if ($totalStudents === 0) {
        return [
            'total_students'  => 0,
            'overall_average' => 0.0,
            'highest_student' => null,
            'lowest_student'  => null,
            'highest_score'   => 0.0,
            'lowest_score'    => 0.0,
            'total_passed'    => 0,
            'total_failed'    => 0,
            'pass_rate'       => 0.0,
        ];
    }

    $allAverages = array_column($rankedStudents, 'average');
    $overallAverage = round(array_sum($allAverages) / $totalStudents, 1);

    // Ranked array is sorted descending by average:
    $highestStudent = $rankedStudents[0] ?? null;
    $lowestStudent  = $rankedStudents[$totalStudents - 1] ?? null;

    // Single subject scores:
    $rawScores = array_column($rawGrades, 'score');
    $highestScore = !empty($rawScores) ? max($rawScores) : 0.0;
    $lowestScore  = !empty($rawScores) ? min($rawScores) : 0.0;

    $passedCount = count(array_filter($rankedStudents, fn($s) => $s['status'] === 'Passed'));
    $failedCount = $totalStudents - $passedCount;
    $passRate    = round(($passedCount / $totalStudents) * 100, 1);

    return [
        'total_students'   => $totalStudents,
        'overall_average'  => $overallAverage,
        'highest_student'  => $highestStudent,
        'lowest_student'   => $lowestStudent,
        'highest_score'    => $highestScore,
        'lowest_score'     => $lowestScore,
        'total_passed'     => $passedCount,
        'total_failed'     => $failedCount,
        'pass_rate'        => $passRate,
    ];
}
