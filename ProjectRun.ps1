param(
    [ValidateSet('up', 'dev', 'build', 'rebuild', 'down', 'status', 'logs', 'validate')]
    [string]$Mode = 'up'
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$envFile = Join-Path $projectRoot '.env'
$envExampleFile = Join-Path $projectRoot '.env.example'
$publicHotFile = Join-Path $projectRoot 'public\hot'
$composeCoreServices = @('app', 'nginx', 'postgres', 'redis', 'n8n_postgres', 'n8n', 'n8n_worker', 'ollama')
$composeDevServices = @($composeCoreServices + @('vite'))

function Write-Step {
    param([string]$Message)

    Write-Host "`n==> $Message" -ForegroundColor Cyan
}

function New-HexSecret {
    param([int]$Bytes = 32)

    $buffer = New-Object byte[] $Bytes
    [System.Security.Cryptography.RandomNumberGenerator]::Create().GetBytes($buffer)
    return ([System.BitConverter]::ToString($buffer)).Replace('-', '').ToLowerInvariant()
}

function Invoke-DockerCompose {
    param([string[]]$Arguments)

    & docker compose @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "docker compose fallo con codigo $LASTEXITCODE."
    }
}

function Get-EnvLines {
    if (-not (Test-Path $envFile)) {
        if (-not (Test-Path $envExampleFile)) {
            throw "No existe .env ni .env.example en la raiz del proyecto."
        }

        Copy-Item $envExampleFile $envFile
    }

    return @(Get-Content $envFile)
}

function Get-EnvValue {
    param(
        [string[]]$Lines,
        [string]$Key
    )

    $escapedKey = [regex]::Escape($Key)

    foreach ($line in $Lines) {
        if ($line -match "^$escapedKey=(.*)$") {
            return $Matches[1]
        }
    }

    return $null
}

function Set-EnvValue {
    param(
        [ref]$Lines,
        [string]$Key,
        [string]$Value
    )

    $escapedKey = [regex]::Escape($Key)
    $updated = $false
    $newLines = New-Object System.Collections.Generic.List[string]

    foreach ($line in $Lines.Value) {
        if ($line -match "^$escapedKey=") {
            $newLines.Add("$Key=$Value")
            $updated = $true
        }
        else {
            $newLines.Add($line)
        }
    }

    if (-not $updated) {
        $newLines.Add("$Key=$Value")
    }

    $Lines.Value = @($newLines)
}

function Save-EnvLines {
    param([string[]]$Lines)

    Set-Content -Path $envFile -Value $Lines -Encoding UTF8
}

function Test-DockerReady {
    & docker info *> $null

    if ($LASTEXITCODE -ne 0) {
        throw "Docker Desktop no esta disponible. Abre Docker Desktop y vuelve a ejecutar el script."
    }
}

function Wait-ForContainerState {
    param(
        [string]$ContainerName,
        [string]$DesiredState,
        [int]$TimeoutSeconds = 120
    )

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)

    while ((Get-Date) -lt $deadline) {
        $state = (& docker inspect --format "{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}" $ContainerName 2>$null)

        if ($LASTEXITCODE -eq 0 -and $state -eq $DesiredState) {
            return
        }

        Start-Sleep -Seconds 2
    }

    throw "El contenedor '$ContainerName' no alcanzo el estado '$DesiredState' dentro del tiempo esperado."
}

function Ensure-ProjectEnv {
    $lines = Get-EnvLines

    $defaults = [ordered]@{
        'N8N_TIMEZONE'                    = 'Europe/Madrid'
        'N8N_HOST'                        = 'localhost'
        'N8N_PROTOCOL'                    = 'http'
        'N8N_EDITOR_BASE_URL'             = 'http://localhost:5678/'
        'N8N_WEBHOOK_URL'                 = 'http://localhost:5678/'
        'N8N_POSTGRES_DB'                 = 'n8n'
        'N8N_POSTGRES_USER'               = 'n8n'
        'N8N_QUEUE_REDIS_DB'              = '2'
        'N8N_QUEUE_REDIS_PREFIX'          = 'newradiochi_n8n'
        'N8N_BASE_URL'                    = 'http://n8n:5678'
        'N8N_HOST_URL'                    = 'http://127.0.0.1:5678'
        'OLLAMA_BASE_URL'                 = 'http://ollama:11434'
        'OLLAMA_HOST_URL'                 = 'http://127.0.0.1:11434'
        'N8N_PROJECT_DB_READONLY_HOST'    = 'postgres'
        'N8N_PROJECT_DB_READONLY_PORT'    = '5432'
        'N8N_PROJECT_DB_READONLY_USER'    = 'radiochi_n8n_ro'
    }

    foreach ($entry in $defaults.GetEnumerator()) {
        if ([string]::IsNullOrWhiteSpace((Get-EnvValue -Lines $lines -Key $entry.Key))) {
            Set-EnvValue -Lines ([ref]$lines) -Key $entry.Key -Value $entry.Value
        }
    }

    if ([string]::IsNullOrWhiteSpace((Get-EnvValue -Lines $lines -Key 'N8N_ENCRYPTION_KEY'))) {
        Set-EnvValue -Lines ([ref]$lines) -Key 'N8N_ENCRYPTION_KEY' -Value (New-HexSecret)
    }

    if ([string]::IsNullOrWhiteSpace((Get-EnvValue -Lines $lines -Key 'N8N_POSTGRES_PASSWORD'))) {
        Set-EnvValue -Lines ([ref]$lines) -Key 'N8N_POSTGRES_PASSWORD' -Value (New-HexSecret)
    }

    if ([string]::IsNullOrWhiteSpace((Get-EnvValue -Lines $lines -Key 'N8N_PROJECT_DB_READONLY_PASSWORD'))) {
        Set-EnvValue -Lines ([ref]$lines) -Key 'N8N_PROJECT_DB_READONLY_PASSWORD' -Value (New-HexSecret)
    }

    $dbDatabase = Get-EnvValue -Lines $lines -Key 'DB_DATABASE'

    if (-not [string]::IsNullOrWhiteSpace($dbDatabase) -and [string]::IsNullOrWhiteSpace((Get-EnvValue -Lines $lines -Key 'N8N_PROJECT_DB_READONLY_DATABASE'))) {
        Set-EnvValue -Lines ([ref]$lines) -Key 'N8N_PROJECT_DB_READONLY_DATABASE' -Value $dbDatabase
    }

    Save-EnvLines -Lines $lines

    return $lines
}

function Ensure-ReadonlyRoleForN8N {
    param([string[]]$EnvLines)

    $webDb = Get-EnvValue -Lines $EnvLines -Key 'DB_DATABASE'
    $webUser = Get-EnvValue -Lines $EnvLines -Key 'DB_USERNAME'
    $readonlyUser = Get-EnvValue -Lines $EnvLines -Key 'N8N_PROJECT_DB_READONLY_USER'
    $readonlyPassword = Get-EnvValue -Lines $EnvLines -Key 'N8N_PROJECT_DB_READONLY_PASSWORD'

    if ([string]::IsNullOrWhiteSpace($webDb) -or [string]::IsNullOrWhiteSpace($webUser)) {
        throw "No se encontraron DB_DATABASE o DB_USERNAME en .env para configurar el acceso readonly de n8n."
    }

    $sqlCommands = @(
        (@'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = '{0}') THEN
        CREATE ROLE {0} LOGIN PASSWORD '{1}';
    ELSE
        ALTER ROLE {0} WITH LOGIN PASSWORD '{1}';
    END IF;
END
$$;
'@ -f $readonlyUser, $readonlyPassword),
        "GRANT CONNECT ON DATABASE $webDb TO $readonlyUser;",
        "GRANT USAGE ON SCHEMA public TO $readonlyUser;",
        "GRANT SELECT ON ALL TABLES IN SCHEMA public TO $readonlyUser;",
        "ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO $readonlyUser;"
    )

    foreach ($sql in $sqlCommands) {
        Invoke-DockerCompose -Arguments @('exec', '-T', 'postgres', 'psql', '-U', $webUser, '-d', $webDb, '-v', 'ON_ERROR_STOP=1', '-c', $sql)
    }
}

function Ensure-LaravelDependencies {
    $hasAutoload = $true
    & docker compose exec -T app sh -lc "test -f /var/www/html/vendor/autoload.php"

    if ($LASTEXITCODE -ne 0) {
        $hasAutoload = $false
    }

    if (-not $hasAutoload) {
        Write-Step "Instalando dependencias Composer dentro del contenedor app"
        Invoke-DockerCompose -Arguments @('exec', '-T', 'app', 'composer', 'install')
    }
}

function Ensure-FrontendDependencies {
    $frontendDepsAreFresh = $true
    & docker compose exec -T app sh -lc "test -d /var/www/html/node_modules && test -f /var/www/html/node_modules/.package-lock-stamp && test ! /var/www/html/package-lock.json -nt /var/www/html/node_modules/.package-lock-stamp"

    if ($LASTEXITCODE -ne 0) {
        $frontendDepsAreFresh = $false
    }

    if (-not $frontendDepsAreFresh) {
        Write-Step "Instalando dependencias frontend dentro del contenedor app"
        Invoke-DockerCompose -Arguments @('exec', '-T', 'app', 'sh', '-lc', 'npm ci && touch /var/www/html/node_modules/.package-lock-stamp')
    }
}

function Ensure-FrontendBuild {
    param([bool]$ForceBuild = $false)

    $buildIsFresh = $true
    & docker compose exec -T app sh -lc "test -f /var/www/html/public/build/manifest.json && test ! /var/www/html/package-lock.json -nt /var/www/html/public/build/manifest.json && test ! /var/www/html/package.json -nt /var/www/html/public/build/manifest.json && test ! /var/www/html/vite.config.js -nt /var/www/html/public/build/manifest.json && test -z `$(find /var/www/html/resources -type f -newer /var/www/html/public/build/manifest.json -print -quit)"

    if ($LASTEXITCODE -ne 0) {
        $buildIsFresh = $false
    }

    if ($ForceBuild -or -not $buildIsFresh) {
        Write-Step "Generando assets frontend dentro del contenedor app"
        Invoke-DockerCompose -Arguments @('exec', '-T', 'app', 'npm', 'run', 'build')
    }
}

function Clear-StaleViteHotFile {
    if (-not (Test-Path $publicHotFile)) {
        return
    }

    $hotTarget = ''

    try {
        $hotTarget = ((Get-Content -Path $publicHotFile -TotalCount 1 -ErrorAction Stop) | Select-Object -First 1).Trim()
    }
    catch {
        $hotTarget = ''
    }

    Write-Step "Eliminando marcador HMR obsoleto de Vite"

    if ([string]::IsNullOrWhiteSpace($hotTarget)) {
        Write-Host "Se elimina public/hot para forzar el uso de assets compilados." -ForegroundColor Yellow
    }
    else {
        Write-Host "Se elimina public/hot apuntando a $hotTarget para forzar el uso de assets compilados." -ForegroundColor Yellow
    }

    Remove-Item -Path $publicHotFile -Force
}

function Stop-ViteDevService {
    & docker compose stop vite *> $null
    $global:LASTEXITCODE = 0
}

function Ensure-LaravelAppKeyAndMigrations {
    param([string[]]$EnvLines)

    $appKey = Get-EnvValue -Lines $EnvLines -Key 'APP_KEY'

    if ([string]::IsNullOrWhiteSpace($appKey)) {
        Write-Step "Generando APP_KEY"
        Invoke-DockerCompose -Arguments @('exec', '-T', 'app', 'php', 'artisan', 'key:generate', '--force')
    }

    Write-Step "Ejecutando migraciones Laravel"
    Invoke-DockerCompose -Arguments @('exec', '-T', 'app', 'php', 'artisan', 'migrate', '--force')
}

function Assert-HttpStatus {
    param(
        [string]$Url,
        [int[]]$AllowedStatusCodes
    )

    try {
        $response = Invoke-WebRequest -Uri $Url -UseBasicParsing -MaximumRedirection 0 -TimeoutSec 20
        $statusCode = [int]$response.StatusCode
    }
    catch {
        if ($_.Exception.Response) {
            $statusCode = [int]$_.Exception.Response.StatusCode.value__
        }
        else {
            throw "No se pudo validar la URL '$Url'. Error: $($_.Exception.Message)"
        }
    }

    if ($AllowedStatusCodes -notcontains $statusCode) {
        throw "La URL '$Url' devolvio HTTP $statusCode y se esperaba uno de: $($AllowedStatusCodes -join ', ')."
    }
}

function Wait-ForHttpStatus {
    param(
        [string]$Url,
        [int[]]$AllowedStatusCodes,
        [int]$TimeoutSeconds = 120
    )

    $deadline = (Get-Date).AddSeconds($TimeoutSeconds)

    while ((Get-Date) -lt $deadline) {
        try {
            Assert-HttpStatus -Url $Url -AllowedStatusCodes $AllowedStatusCodes
            return
        }
        catch {
            Start-Sleep -Seconds 2
        }
    }

    throw "La URL '$Url' no devolvio ninguno de los estados esperados ($($AllowedStatusCodes -join ', ')) dentro del tiempo esperado."
}

function Invoke-ValidationChecklist {
    Write-Step "Validando docker-compose"
    Invoke-DockerCompose -Arguments @('config')

    Write-Step "Validando estado del stack"
    Invoke-DockerCompose -Arguments @('ps')

    Write-Step "Validando HTTP publico"
    Assert-HttpStatus -Url 'http://127.0.0.1:8080' -AllowedStatusCodes @(200)

    Write-Step "Validando HTTP admin"
    Assert-HttpStatus -Url 'http://127.0.0.1:8080/backoffice/login' -AllowedStatusCodes @(200)

    Write-Step "Ejecutando tests Laravel"
    Invoke-DockerCompose -Arguments @('exec', '-T', 'app', 'php', 'artisan', 'test')
}

Write-Step "Validando Docker Desktop"
Test-DockerReady

Write-Step "Preparando variables locales del proyecto"
$envLines = Ensure-ProjectEnv
$validateOnly = $false
$forceFrontendBuild = $false
$useViteDevServer = $false

Send-DebugEvent -HypothesisId 'A' -Message 'ProjectRun started.' -Data @{
    mode = $Mode
    hotFilePresent = (Test-Path $publicHotFile)
    manifestPresent = (Test-Path (Join-Path $projectRoot 'public\build\manifest.json'))
}

switch ($Mode) {
    'validate' {
        $validateOnly = $true
        Invoke-DockerCompose -Arguments (@('up', '-d') + $composeCoreServices)
    }
    'dev' {
        $useViteDevServer = $true

        if (Test-Path $publicHotFile) {
            Remove-Item -Path $publicHotFile -Force
        }

        Write-Step "Levantando el stack con Vite HMR"
        Invoke-DockerCompose -Arguments (@('up', '-d') + $composeDevServices)
    }
    'down' {
        Write-Step "Deteniendo el stack"
        Invoke-DockerCompose -Arguments @('down', '--remove-orphans')
        exit 0
    }
    'status' {
        Write-Step "Mostrando estado del stack"
        Invoke-DockerCompose -Arguments @('ps')
        exit 0
    }
    'logs' {
        Write-Step "Mostrando logs del stack"
        Invoke-DockerCompose -Arguments @('logs', '--tail', '200')
        exit 0
    }
    'rebuild' {
        Write-Step "Reconstruyendo el stack sin borrar volumenes"
        Invoke-DockerCompose -Arguments @('down', '--remove-orphans')
        Invoke-DockerCompose -Arguments (@('up', '-d', '--build') + $composeCoreServices)
        $forceFrontendBuild = $true
    }
    'build' {
        Write-Step "Levantando el stack con build"
        Invoke-DockerCompose -Arguments (@('up', '-d', '--build') + $composeCoreServices)
        $forceFrontendBuild = $true
    }
    default {
        Write-Step "Levantando el stack"
        Invoke-DockerCompose -Arguments (@('up', '-d') + $composeCoreServices)
    }
}

Write-Step "Esperando a PostgreSQL principal"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_postgres' -DesiredState 'healthy'

Write-Step "Esperando a Redis"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_redis' -DesiredState 'healthy'

Write-Step "Esperando a PostgreSQL de n8n"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_n8n_postgres' -DesiredState 'healthy'

Write-Step "Esperando a la aplicacion web"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_app' -DesiredState 'running'

Write-Step "Esperando a n8n"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_n8n' -DesiredState 'running'

Write-Step "Esperando a n8n worker"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_n8n_worker' -DesiredState 'running'

Write-Step "Esperando a Ollama"
Wait-ForContainerState -ContainerName 'newradiochiwebsite26_ollama' -DesiredState 'running'

if ($useViteDevServer) {
    Write-Step "Esperando a Vite dev server"
    Wait-ForContainerState -ContainerName 'newradiochiwebsite26_vite' -DesiredState 'running'
    Wait-ForHttpStatus -Url 'http://127.0.0.1:5173/@vite/client' -AllowedStatusCodes @(200)
}
else {
    Stop-ViteDevService
    Clear-StaleViteHotFile
}

Ensure-LaravelDependencies
Ensure-FrontendDependencies
Ensure-LaravelAppKeyAndMigrations -EnvLines $envLines

Write-Step "Creando acceso readonly desde n8n a la base principal del proyecto"
Ensure-ReadonlyRoleForN8N -EnvLines $envLines

if ($useViteDevServer) {
    Send-DebugEvent -HypothesisId 'B' -Message 'ProjectRun confirmed Vite HMR mode.' -Data @{
        mode = $Mode
        hotFilePresent = (Test-Path $publicHotFile)
    }
}
else {
    Ensure-FrontendBuild -ForceBuild $forceFrontendBuild
}

if ($validateOnly) {
    Invoke-ValidationChecklist
    exit 0
}

Send-DebugEvent -HypothesisId 'A' -Message 'ProjectRun completed frontend preparation.' -Data @{
    mode = $Mode
    hotFilePresent = (Test-Path $publicHotFile)
    manifestPresent = (Test-Path (Join-Path $projectRoot 'public\build\manifest.json'))
    forceFrontendBuild = $forceFrontendBuild
}

Write-Host "`nProyecto levantado." -ForegroundColor Green
Write-Host "Web:    http://127.0.0.1:8080"
if ($useViteDevServer) {
    Write-Host "Vite:   http://127.0.0.1:5173 (HMR activo)"
}
Write-Host "n8n:    http://127.0.0.1:5678"
Write-Host "Ollama: http://127.0.0.1:11434"
Write-Host ""
Write-Host "Base principal del proyecto: postgres / $((Get-EnvValue -Lines $envLines -Key 'DB_DATABASE'))"
Write-Host "Base separada de n8n:        n8n_postgres / $((Get-EnvValue -Lines $envLines -Key 'N8N_POSTGRES_DB'))"
Write-Host "Redis compartido:            redis (Laravel usa DB 0/1, n8n usa DB $((Get-EnvValue -Lines $envLines -Key 'N8N_QUEUE_REDIS_DB')))"
