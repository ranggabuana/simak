# ==============================================================================
# AGEN PENARIK & PENGIRIM DATA DAPODIK BANJARNEGARA (REVERSE PUSH AGENT)
# Lokasi File : scripts/dapodik_agent.ps1
# Target      : Windows Server Kominfo (Tempat SQL Server 15.0 Datamart berada)
# ==============================================================================
param (
    [ValidateSet("Watch", "Once", "PushNow")]
    [string]$Mode = "Watch",

    [string]$SimakBaseUrl = "https://simak.sistemdata.id",
    [string]$SecretToken  = "SIMAK_BANJARNEGARA_DAPODIK_SECRET_2026",
    [string]$SqlServer    = "localhost",
    [string]$SqlDatabase  = "Datamart",
    [int]$BatchSize       = 250,
    [int]$PollSeconds     = 5
)

# Paksa TLS 1.2 agar kompatibel dengan HTTPS modern di Windows Server
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$Headers = @{
    "Authorization" = "Bearer $SecretToken"
    "X-Sync-Token"  = $SecretToken
    "Accept"        = "application/json"
    "Content-Type"  = "application/json; charset=utf-8"
}

function Get-SqlData([string]$Query) {
    $ConnStr = "Server=$SqlServer;Database=$SqlDatabase;Integrated Security=True;TrustServerCertificate=True;"
    $Conn = New-Object System.Data.SqlClient.SqlConnection($ConnStr)
    $Cmd = New-Object System.Data.SqlClient.SqlCommand($Query, $Conn)
    $Cmd.CommandTimeout = 300
    $Adapter = New-Object System.Data.SqlClient.SqlDataAdapter($Cmd)
    $Table = New-Object System.Data.DataTable
    $Conn.Open()
    [void]$Adapter.Fill($Table)
    $Conn.Close()

    $Rows = New-Object System.Collections.Generic.List[object]
    foreach ($Row in $Table.Rows) {
        $Obj = @{}
        foreach ($Col in $Table.Columns) {
            $Val = $Row[$Col.ColumnName]
            if ($Val -is [System.DBNull]) {
                $Obj[$Col.ColumnName] = $null
            } else {
                $Obj[$Col.ColumnName] = $Val.ToString()
            }
        }
        $Rows.Add($Obj)
    }
    return ,$Rows.ToArray()
}

function Send-Batches([int]$TaskId, [string]$Entity, [array]$Rows, [bool]$IsFinalEntity) {
    $Total = $Rows.Count
    if ($Total -eq 0) {
        if ($IsFinalEntity) {
            $Payload = @{
                task_id       = $TaskId
                entity        = $Entity
                data          = @()
                is_last_batch = $true
            } | ConvertTo-Json -Depth 5 -Compress
            Invoke-RestMethod -Uri "$SimakBaseUrl/api/dapodik-sync/receive-data" -Method Post -Headers $Headers -Body ([System.Text.Encoding]::UTF8.GetBytes($Payload)) | Out-Null
        }
        return
    }

    for ($i = 0; $i -lt $Total; $i += $BatchSize) {
        $End = [Math]::Min($i + $BatchSize - 1, $Total - 1)
        $Batch = $Rows[$i..$End]
        $IsLast = ($IsFinalEntity -and ($End -eq ($Total - 1)))

        $Payload = @{
            task_id       = $TaskId
            entity        = $Entity
            data          = $Batch
            is_last_batch = $IsLast
        } | ConvertTo-Json -Depth 6 -Compress

        Write-Host "  -> Mengirim batch $Entity ($($i + 1) s.d. $($End + 1) dari $Total baris)..." -ForegroundColor Cyan
        Invoke-RestMethod -Uri "$SimakBaseUrl/api/dapodik-sync/receive-data" -Method Post -Headers $Headers -Body ([System.Text.Encoding]::UTF8.GetBytes($Payload)) | Out-Null
    }
}

function Invoke-SyncCycle {
    try {
        $Check = Invoke-RestMethod -Uri "$SimakBaseUrl/api/dapodik-sync/check-task" -Method Get -Headers $Headers
        if (-not $Check.has_task) {
            return $false
        }

        $TaskId = [int]$Check.task_id
        $Target = [string]$Check.target
        Write-Host "`n[$(Get-Date -Format 'HH:mm:ss')] Ditemukan Tugas #$TaskId (Target: $Target). Mengeksekusi SQL View Banjarnegara..." -ForegroundColor Green

        if ($Target -eq "semua" -or $Target -eq "sekolah") {
            Write-Host "  [*] Membaca dbo.v_simak_sekolah_bna dari SQL Server..." -ForegroundColor Yellow
            $SekolahRows = Get-SqlData "SELECT * FROM dbo.v_simak_sekolah_bna"
            $IsFinal = ($Target -eq "sekolah")
            Send-Batches -TaskId $TaskId -Entity "sekolah" -Rows $SekolahRows -IsFinalEntity $IsFinal
        }

        if ($Target -eq "semua" -or $Target -eq "siswa") {
            Write-Host "  [*] Membaca dbo.v_simak_siswa_bna dari SQL Server..." -ForegroundColor Yellow
            $SiswaRows = Get-SqlData "SELECT * FROM dbo.v_simak_siswa_bna"
            $IsFinal = ($Target -eq "siswa")
            Send-Batches -TaskId $TaskId -Entity "siswa" -Rows $SiswaRows -IsFinalEntity $IsFinal
        }

        if ($Target -eq "semua" -or $Target -eq "guru") {
            Write-Host "  [*] Membaca dbo.v_simak_guru_bna dari SQL Server..." -ForegroundColor Yellow
            $GuruRows = Get-SqlData "SELECT * FROM dbo.v_simak_guru_bna"
            Send-Batches -TaskId $TaskId -Entity "guru" -Rows $GuruRows -IsFinalEntity $true
        }

        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Sinkronisasi Tugas #$TaskId selesai 100%!" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Gagal menghubungi SIMAK / SQL Server: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

Write-Host "=================================================================" -ForegroundColor Blue
Write-Host " AGEN SINKRONISASI DAPODIK BANJARNEGARA -> SIMAK PENDIDIKAN" -ForegroundColor White
Write-Host " Server SQL : $SqlServer ($SqlDatabase)" -ForegroundColor Gray
Write-Host " Target URL : $SimakBaseUrl" -ForegroundColor Gray
Write-Host " Mode Jalan : $Mode" -ForegroundColor Gray
Write-Host "=================================================================" -ForegroundColor Blue

if ($Mode -eq "Once") {
    [void](Invoke-SyncCycle)
} elseif ($Mode -eq "Watch") {
    Write-Host "Menunggu klik tombol 'Sinkron Dapodik' dari aplikasi SIMAK (Polling tiap $PollSeconds detik, tekan Ctrl+C untuk berhenti)..." -ForegroundColor Yellow
    while ($true) {
        [void](Invoke-SyncCycle)
        Start-Sleep -Seconds $PollSeconds
    }
}
