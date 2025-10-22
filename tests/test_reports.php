<?php

// Test script to verify the new comprehensive report system
// This can be run in Laravel's tinker or as a test

use App\Services\ComprehensiveReportService;
use App\Http\Controllers\ComprehensiveReportController;

// Initialize the service
$reportService = new ComprehensiveReportService();

// Test generating a few reports to make sure they work
try {
    // Test 1: Generate employee master report
    echo "Testing Employee Master Report...\n";
    $masterReport = $reportService->generateEmployeeMasterReport();
    echo "✓ Employee Master Report generated successfully. Found " . count($masterReport['employees']) . " employees.\n\n";
    
    // Test 2: Generate employee directory report
    echo "Testing Employee Directory Report...\n";
    $directoryReport = $reportService->generateEmployeeDirectoryReport();
    echo "✓ Employee Directory Report generated successfully. Found " . count($directoryReport['employees']) . " employees.\n\n";
    
    // Test 3: Generate employee status report
    echo "Testing Employee Status Report...\n";
    $statusReport = $reportService->generateEmployeeStatusReport();
    echo "✓ Employee Status Report generated successfully. Found " . count($statusReport['employees_by_status']) . " status categories.\n\n";
    
    // Test 4: Generate payroll summary report
    echo "Testing Payroll Summary Report...\n";
    $payrollReport = $reportService->generatePayrollSummaryReport();
    echo "✓ Payroll Summary Report generated successfully. Found " . count($payrollReport['payroll_records']) . " payroll records.\n\n";
    
    // Test 5: Generate deduction summary report
    echo "Testing Deduction Summary Report...\n";
    $deductionReport = $reportService->generateDeductionSummaryReport();
    echo "✓ Deduction Summary Report generated successfully. Found " . count($deductionReport['deductions']) . " deductions.\n\n";
    
    // Test 6: Generate addition summary report
    echo "Testing Addition Summary Report...\n";
    $additionReport = $reportService->generateAdditionSummaryReport();
    echo "✓ Addition Summary Report generated successfully. Found " . count($additionReport['additions']) . " additions.\n\n";
    
    // Test 7: Generate promotion history report
    echo "Testing Promotion History Report...\n";
    $promotionReport = $reportService->generatePromotionHistoryReport();
    echo "✓ Promotion History Report generated successfully. Found " . count($promotionReport['promotions']) . " promotions.\n\n";
    
    // Test 8: Generate disciplinary report
    echo "Testing Disciplinary Action Report...\n";
    $disciplinaryReport = $reportService->generateDisciplinaryReport();
    echo "✓ Disciplinary Action Report generated successfully. Found " . count($disciplinaryReport['actions']) . " actions.\n\n";
    
    // Test 9: Generate retirement planning report
    echo "Testing Retirement Planning Report...\n";
    $retirementReport = $reportService->generateRetirementPlanningReport();
    echo "✓ Retirement Planning Report generated successfully. Found " . count($retirementReport['employees_approaching_retirement']) . " employees approaching retirement.\n\n";
    
    // Test 10: Generate loan status report
    echo "Testing Loan Status Report...\n";
    $loanReport = $reportService->generateLoanStatusReport();
    echo "✓ Loan Status Report generated successfully. Found " . count($loanReport['loans']) . " loans.\n\n";
    
    // Test 11: Generate department summary report
    echo "Testing Department Summary Report...\n";
    $deptReport = $reportService->generateDepartmentSummaryReport();
    echo "✓ Department Summary Report generated successfully. Found " . count($deptReport['departments']) . " departments.\n\n";
    
    // Test 12: Generate grade level summary report
    echo "Testing Grade Level Summary Report...\n";
    $gradeReport = $reportService->generateGradeLevelSummaryReport();
    echo "✓ Grade Level Summary Report generated successfully. Found " . count($gradeReport['grade_levels']) . " grade levels.\n\n";
    
    // Test 13: Generate audit trail report
    echo "Testing Audit Trail Report...\n";
    $auditReport = $reportService->generateAuditTrailReport();
    echo "✓ Audit Trail Report generated successfully. Found " . count($auditReport['activities']) . " activities.\n\n";
    
    // Test 14: Generate payroll analysis report
    echo "Testing Payroll Analysis Report...\n";
    $analysisReport = $reportService->generatePayrollAnalysisReport();
    echo "✓ Payroll Analysis Report generated successfully. Found " . count($analysisReport['payroll_records']) . " payroll records.\n\n";
    
    echo "\n🎉 All reports generated successfully! The new comprehensive report system is working correctly.\n";
    
} catch (Exception $e) {
    echo "❌ Error occurred while testing: " . $e->getMessage() . "\n";
}