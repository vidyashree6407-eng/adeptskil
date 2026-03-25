$OriginalPath =  'c:\Users\MANJUNATH B G\adeptskil\courses.html'
$BackupPath = 'c:\Users\MANJUNATH B G\adeptskil\courses_backup.html'

# Backup current version
Copy-Item $OriginalPath $BackupPath -Force

# Try to restore from git
cd 'c:\Users\MANJUNATH B G\adeptskil'

# Check git log
Write-Host "Checking git history..."
$gitlog = git log --oneline -5
Write-Host $gitlog

# Try restoring from 5 commits ago
Write-Host "Attempting to restore from HEAD~5..."
git checkout HEAD~5 -- courses.html 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Successfully restored courses.html from HEAD~5"
} else {
    Write-Host "✗ Failed to restore from HEAD~5, trying HEAD~10..."
    git checkout HEAD~10 -- courses.html 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Successfully restored from HEAD~10"
    } else {
        Write-Host "✗ Failed, restored from backup instead"
        Copy-Item $BackupPath $OriginalPath -Force
    }
}

Get-Item $OriginalPath | Select-Object FullName, Length
