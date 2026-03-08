echo "This script must be run in the project root!!!"
echo "This script must be run in the project root!!!"
echo "This script must be run in the project root!!!"
echo "This script must be run in the project root!!!"
echo "This script must be run in the project root!!!"

echo "Kill all processes on port 8000"

$port = 8000
$connections = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue

if ($connections) {
    $connections | ForEach-Object {
        $processId = $_.OwningProcess
        try {
            Stop-Process -Id $processId -Force
            Write-Host "Killed process $processId on port $port"
        } catch {
            Write-Host "Failed to kill process ${processId}: $_"
        }
    }
} else {
    Write-Host "No processes found on port $port"
}


echo "Start symfony server"

symfony server:start --allow-all-ip --port=8000
