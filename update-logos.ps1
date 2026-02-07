# PowerShell script to update logo in all reports
$oldLogo = "iVBORw0KGgoAAAANSUhEUgAAAWEAAADGCAYAAAAxfSzH"
$newLogoFile = "c:\Users\Rowwww\Herd\hrm_payroll_system_design\kswb-logo-base64.txt"
$newLogo = Get-Content $newLogoFile -Raw

$files = @(
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\pdf\employee-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\pdf\deduction-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\pdf\addition-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\pdf\retired-employees-summary-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\new\pdf\employee-master-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\new\pdf\retirement-planning-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\new\pdf\department-summary-report.blade.php",
    "c:\Users\Rowwww\Herd\hrm_payroll_system_design\resources\views\reports\new\pdf\generic-report.blade.php"
)

$updatedCount = 0

foreach ($file in $files) {
    if (Test-Path $file) {
        $content = Get-Content $file -Raw
        if ($content -match $oldLogo) {
            # Replace the old base64 string with the new one
            # Find the full old logo string and replace with new
            $content = $content -replace "data:image/png;base64,$oldLogo[^""]*", "data:image/png;base64,$newLogo"
            Set-Content -Path $file -Value $content -NoNewline
            Write-Host "Updated: $file" -ForegroundColor Green
            $updatedCount++
        } else {
            Write-Host "No old logo found in: $file" -ForegroundColor Yellow
        }
    } else {
        Write-Host "File not found: $file" -ForegroundColor Red
    }
}

Write-Host "`nTotal files updated: $updatedCount" -ForegroundColor Cyan
