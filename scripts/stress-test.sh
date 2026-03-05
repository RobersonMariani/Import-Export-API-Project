#!/usr/bin/env bash
set -uo pipefail

# ============================================================
#  Stress Test — Importações simultâneas
#  Gera N arquivos CSV com dados aleatórios e dispara
#  todas as importações em paralelo, monitorando o progresso.
#
#  Uso: ./scripts/stress-test.sh [NUM_IMPORTS] [ROWS_PER_CSV] [CONCURRENCY]
#  Ex:  ./scripts/stress-test.sh 100 100 50
# ============================================================

API_URL="${API_URL:-http://localhost:8080/api/v1}"
EMAIL="${EMAIL:-admin@example.com}"
PASSWORD="${PASSWORD:-password}"
NUM_IMPORTS="${1:-5}"
ROWS_PER_CSV="${2:-100}"
CONCURRENCY="${3:-50}"
POLL_INTERVAL=5
TMP_DIR=$(mktemp -d)

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

cleanup() {
  rm -rf "$TMP_DIR"
}
trap cleanup EXIT

log()  { echo -e "${CYAN}[$(date +%H:%M:%S)]${NC} $*"; }
ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
warn() { echo -e "${YELLOW}[!]${NC} $*"; }
fail() { echo -e "${RED}[ERRO]${NC} $*"; }

# ============================================================
#  Dados aleatórios
# ============================================================
FIRST_NAMES=("Ana" "Bruno" "Carla" "Diego" "Elena" "Felipe" "Gabriela" "Hugo"
  "Isabela" "João" "Karen" "Lucas" "Maria" "Neto" "Olivia" "Pedro"
  "Rafaela" "Samuel" "Tatiana" "Victor" "Wendy" "Xavier" "Yara" "Zeca"
  "Adriana" "Bernardo" "Clara" "Daniel" "Elisa" "Fernando")

LAST_NAMES=("Silva" "Santos" "Oliveira" "Souza" "Rodrigues" "Ferreira"
  "Almeida" "Costa" "Pereira" "Lima" "Gomes" "Ribeiro" "Martins"
  "Carvalho" "Araújo" "Nascimento" "Barbosa" "Moura" "Rocha" "Dias"
  "Correia" "Monteiro" "Mendes" "Vieira" "Castro" "Cardoso")

CITIES=("São Paulo,SP,01001-000" "Rio de Janeiro,RJ,22070-000"
  "Belo Horizonte,MG,30130-000" "Curitiba,PR,80020-000"
  "Porto Alegre,RS,90020-000" "Salvador,BA,40020-000"
  "Brasília,DF,70747-000" "Fortaleza,CE,60165-000"
  "Recife,PE,50030-000" "Manaus,AM,69010-000"
  "Goiânia,GO,74013-000" "Belém,PA,66017-000"
  "Florianópolis,SC,88010-000" "Campinas,SP,13015-000"
  "Vitória,ES,29010-000")

ROLES=("user" "user" "user" "user" "manager" "admin")

random_element() {
  local arr=("$@")
  echo "${arr[$((RANDOM % ${#arr[@]}))]}"
}

random_phone() {
  printf "%02d9%04d%04d" $((RANDOM % 90 + 11)) $((RANDOM % 10000)) $((RANDOM % 10000))
}

random_date() {
  local year=$((RANDOM % 30 + 1970))
  local month=$((RANDOM % 12 + 1))
  local day=$((RANDOM % 28 + 1))
  printf "%04d-%02d-%02d" "$year" "$month" "$day"
}

# ============================================================
#  Header
# ============================================================
echo ""
echo -e "${BOLD}${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${BLUE}║       STRESS TEST — Import Export API            ║${NC}"
echo -e "${BOLD}${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  Importações:       ${BOLD}${NUM_IMPORTS}${NC}"
echo -e "  Linhas por CSV:    ${BOLD}${ROWS_PER_CSV}${NC}"
echo -e "  Total registros:   ${BOLD}$((NUM_IMPORTS * ROWS_PER_CSV))${NC}"
echo -e "  Concorrência:      ${BOLD}${CONCURRENCY}${NC} uploads simultâneos"
echo -e "  API:               ${BOLD}${API_URL}${NC}"
echo ""

# ============================================================
#  1. Autenticação
# ============================================================
log "Autenticando como ${BOLD}${EMAIL}${NC}..."

LOGIN_RESPONSE=$(curl -s -X POST "${API_URL}/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\": \"${EMAIL}\", \"password\": \"${PASSWORD}\"}")

TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
  fail "Falha na autenticação. Resposta: $LOGIN_RESPONSE"
  exit 1
fi

ok "Autenticado com sucesso"

# ============================================================
#  2. Gerar CSVs
# ============================================================
log "Gerando ${BOLD}${NUM_IMPORTS}${NC} arquivos CSV com ${BOLD}${ROWS_PER_CSV}${NC} linhas cada..."

GLOBAL_COUNTER=0

for i in $(seq 1 "$NUM_IMPORTS"); do
  CSV_FILE="${TMP_DIR}/import_${i}.csv"
  echo "name,email,password,phone,address,city,state,zip_code,birth_date,role" > "$CSV_FILE"

  for j in $(seq 1 "$ROWS_PER_CSV"); do
    GLOBAL_COUNTER=$((GLOBAL_COUNTER + 1))
    first=$(random_element "${FIRST_NAMES[@]}")
    last=$(random_element "${LAST_NAMES[@]}")
    name="${first} ${last}"
    email="stress.${GLOBAL_COUNTER}.$(date +%s%N | cut -c1-13)@test.com"
    phone=$(random_phone)
    city_data=$(random_element "${CITIES[@]}")
    city=$(echo "$city_data" | cut -d',' -f1)
    state=$(echo "$city_data" | cut -d',' -f2)
    zip=$(echo "$city_data" | cut -d',' -f3)
    role=$(random_element "${ROLES[@]}")
    bdate=$(random_date)
    echo "${name},${email},Password123!,${phone},Rua Teste ${GLOBAL_COUNTER},${city},${state},${zip},${bdate},${role}" >> "$CSV_FILE"
  done
done

ok "CSVs gerados em ${TMP_DIR}"

# ============================================================
#  3. Disparar importações com concorrência controlada
# ============================================================
log "Disparando ${BOLD}${NUM_IMPORTS}${NC} importações (${BOLD}${CONCURRENCY}${NC} simultâneas)..."

START_TIME=$(date +%s)
ACTIVE_PIDS=()
COMPLETED_UPLOADS=0
FAILED_UPLOADS=0

upload_one() {
  local idx=$1
  local csv_file="${TMP_DIR}/import_${idx}.csv"
  local result_file="${TMP_DIR}/result_${idx}.json"
  local max_retries=10
  local attempt=0

  while [ $attempt -lt $max_retries ]; do
    attempt=$((attempt + 1))

    http_code=$(curl -s -w "%{http_code}" -o "$result_file" \
      --connect-timeout 30 --max-time 120 \
      -X POST "${API_URL}/imports" \
      -H "Authorization: Bearer ${TOKEN}" \
      -H "Accept: application/json" \
      -F "file=@${csv_file}" 2>/dev/null)

    if [ "$http_code" = "429" ]; then
      local base_wait=$((attempt * 2))
      local jitter=$((RANDOM % 3))
      sleep $((base_wait + jitter))
      continue
    fi

    if [ "$http_code" = "200" ] || [ "$http_code" = "201" ] || [ "$http_code" = "202" ]; then
      return 0
    fi

    if [ "$http_code" = "000" ]; then
      sleep $((attempt + RANDOM % 3))
      continue
    fi

    sleep 1
  done

  fail "Import #${idx} falhou após ${max_retries} tentativas (último HTTP: ${http_code})"
  return 1
}

for i in $(seq 1 "$NUM_IMPORTS"); do
  upload_one "$i" &
  ACTIVE_PIDS+=($!)

  if [ ${#ACTIVE_PIDS[@]} -ge "$CONCURRENCY" ]; then
    for pid in "${ACTIVE_PIDS[@]}"; do
      wait "$pid" 2>/dev/null
      if [ $? -eq 0 ]; then
        COMPLETED_UPLOADS=$((COMPLETED_UPLOADS + 1))
      else
        FAILED_UPLOADS=$((FAILED_UPLOADS + 1))
      fi
    done
    ACTIVE_PIDS=()
  fi
done

for pid in "${ACTIVE_PIDS[@]}"; do
  wait "$pid" 2>/dev/null
  if [ $? -eq 0 ]; then
    COMPLETED_UPLOADS=$((COMPLETED_UPLOADS + 1))
  else
    FAILED_UPLOADS=$((FAILED_UPLOADS + 1))
  fi
done

UPLOAD_TIME=$(( $(date +%s) - START_TIME ))

echo ""
log "Upload concluído em ${BOLD}${UPLOAD_TIME}s${NC}"

IMPORT_IDS=()
for i in $(seq 1 "$NUM_IMPORTS"); do
  RESULT_FILE="${TMP_DIR}/result_${i}.json"
  IMPORT_ID=$(cat "$RESULT_FILE" 2>/dev/null | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)

  if [ -n "$IMPORT_ID" ]; then
    IMPORT_IDS+=("$IMPORT_ID")
    ok "Import #${i}: ${IMPORT_ID:0:8}..."
  else
    fail "Import #${i}: falha ao criar"
    error_msg=$(cat "$RESULT_FILE" 2>/dev/null | grep -o '"message":"[^"]*"' | head -1 | cut -d'"' -f4)
    [ -n "$error_msg" ] && echo -e "    ${RED}→ ${error_msg}${NC}"
  fi
done

if [ ${#IMPORT_IDS[@]} -eq 0 ]; then
  fail "Nenhuma importação foi criada com sucesso"
  exit 1
fi

echo ""
log "Total criadas: ${BOLD}${#IMPORT_IDS[@]}/${NUM_IMPORTS}${NC} (${FAILED_UPLOADS} falhas de upload)"

# ============================================================
#  4. Monitorar progresso
# ============================================================
echo ""
echo -e "${BOLD}${BLUE}──────────────────────────────────────────────────${NC}"
echo -e "${BOLD} Monitorando progresso...${NC}"
echo -e "${BOLD}${BLUE}──────────────────────────────────────────────────${NC}"
echo ""

completed_count=0
failed_count=0
total_count=${#IMPORT_IDS[@]}

while true; do
  completed_count=0
  failed_count=0
  all_done=true

  for idx in "${!IMPORT_IDS[@]}"; do
    import_id="${IMPORT_IDS[$idx]}"
    num=$((idx + 1))

    http_code=""
    response=""
    for attempt in 1 2 3 4 5; do
      full_response=$(curl -s -w "\n%{http_code}" -X GET "${API_URL}/imports/${import_id}" \
        -H "Authorization: Bearer ${TOKEN}" \
        -H "Accept: application/json" 2>/dev/null)
      http_code=$(echo "$full_response" | tail -1)
      response=$(echo "$full_response" | sed '$d')

      if [ "$http_code" = "429" ]; then
        sleep "$((attempt))"
        continue
      fi
      break
    done

    if [ "$http_code" = "429" ]; then
      all_done=false
      echo -e "  ${YELLOW}⏳${NC} #${num} ${import_id:0:8}  [rate limited — aguardando]"
      continue
    fi

    status=$(echo "$response" | grep -o '"status":"[^"]*"' | head -1 | cut -d'"' -f4)
    total=$(echo "$response" | grep -o '"total_records":[0-9]*' | head -1 | cut -d':' -f2)
    success=$(echo "$response" | grep -o '"success_count":[0-9]*' | head -1 | cut -d':' -f2)
    failed_rows=$(echo "$response" | grep -o '"failure_count":[0-9]*' | head -1 | cut -d':' -f2)
    pct_raw=$(echo "$response" | grep -o '"progress":[0-9]*' | head -1 | cut -d':' -f2)

    total=${total:-0}
    success=${success:-0}
    failed_rows=${failed_rows:-0}
    processed=$((success + failed_rows))
    pct=${pct_raw:-0}

    case "$status" in
      completed)
        completed_count=$((completed_count + 1))
        status_icon="${GREEN}✓${NC}"
        ;;
      failed)
        failed_count=$((failed_count + 1))
        status_icon="${RED}✗${NC}"
        ;;
      partial)
        completed_count=$((completed_count + 1))
        status_icon="${YELLOW}~${NC}"
        ;;
      processing)
        all_done=false
        status_icon="${YELLOW}⟳${NC}"
        ;;
      queued)
        all_done=false
        status_icon="${CYAN}◦${NC}"
        ;;
      *)
        all_done=false
        status_icon="?"
        ;;
    esac

    bar_width=20
    filled=$((pct * bar_width / 100))
    empty=$((bar_width - filled))
    bar=$(printf "%${filled}s" | tr ' ' '█')
    bar_empty=$(printf "%${empty}s" | tr ' ' '░')

    echo -e "  ${status_icon} #${num} ${import_id:0:8}  [${BLUE}${bar}${NC}${bar_empty}] ${pct}%  ${processed}/${total}  ${GREEN}ok:${success}${NC} ${RED}err:${failed_rows}${NC}  (${status})"

    sleep 0.1
  done

  ELAPSED=$(( $(date +%s) - START_TIME ))

  echo ""
  echo -e "  ${BOLD}Progresso: ${completed_count}/${total_count} concluídos | ${failed_count} falhas | Tempo: ${ELAPSED}s${NC}"

  if [ "$all_done" = true ]; then
    break
  fi

  echo -e "  ${CYAN}Próxima atualização em ${POLL_INTERVAL}s...${NC}"
  echo -e "${BLUE}──────────────────────────────────────────────────${NC}"
  sleep "$POLL_INTERVAL"
done

# ============================================================
#  5. Relatório final
# ============================================================
END_TIME=$(date +%s)
TOTAL_ELAPSED=$((END_TIME - START_TIME))

echo ""
echo -e "${BOLD}${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BOLD}${BLUE}║              RELATÓRIO FINAL                     ║${NC}"
echo -e "${BOLD}${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

total_success=0
total_failed_rows=0
total_processed=0

for import_id in "${IMPORT_IDS[@]}"; do
  response=$(curl -s -X GET "${API_URL}/imports/${import_id}" \
    -H "Authorization: Bearer ${TOKEN}" \
    -H "Accept: application/json" 2>/dev/null)

  success=$(echo "$response" | grep -o '"success_count":[0-9]*' | head -1 | cut -d':' -f2)
  failed_rows=$(echo "$response" | grep -o '"failure_count":[0-9]*' | head -1 | cut -d':' -f2)

  s=${success:-0}
  f=${failed_rows:-0}
  total_success=$((total_success + s))
  total_failed_rows=$((total_failed_rows + f))
  total_processed=$((total_processed + s + f))
done

echo -e "  ${BOLD}Importações:${NC}        ${total_count}"
echo -e "  ${GREEN}Concluídas:${NC}         ${completed_count}"
echo -e "  ${RED}Falhas:${NC}             ${failed_count}"
echo ""
echo -e "  ${BOLD}Registros:${NC}"
echo -e "  ${CYAN}Total esperado:${NC}     $((NUM_IMPORTS * ROWS_PER_CSV))"
echo -e "  ${BOLD}Total processado:${NC}   ${total_processed}"
echo -e "  ${GREEN}Sucesso:${NC}            ${total_success}"
echo -e "  ${RED}Erros:${NC}              ${total_failed_rows}"
echo ""
echo -e "  ${BOLD}Performance:${NC}"
echo -e "  ${CYAN}Tempo upload:${NC}       ${UPLOAD_TIME}s"
echo -e "  ${CYAN}Tempo total:${NC}        ${TOTAL_ELAPSED}s"

if [ "$TOTAL_ELAPSED" -gt 0 ] && [ "$total_processed" -gt 0 ]; then
  rate=$((total_processed / TOTAL_ELAPSED))
  echo -e "  ${CYAN}Registros/segundo:${NC}  ~${rate}"
fi

echo ""

if [ "$failed_count" -eq 0 ] && [ "$total_failed_rows" -eq 0 ]; then
  echo -e "  ${GREEN}${BOLD}✓ Todos os registros foram importados com sucesso!${NC}"
elif [ "$failed_count" -eq 0 ]; then
  echo -e "  ${YELLOW}${BOLD}~ Importações concluídas com ${total_failed_rows} registros com erro${NC}"
else
  echo -e "  ${RED}${BOLD}✗ ${failed_count} importação(ões) falharam${NC}"
fi

echo ""
