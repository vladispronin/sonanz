const $ = id => document.getElementById(id)

const EXAMPLES = [
  { author: 'Radiohead',              track: 'Creep',                         album: 'OK Computer' },
  { author: 'Pink Floyd',             track: 'Comfortably Numb',              album: 'The Wall' },
  { author: 'The Beatles',            track: 'Come Together',                 album: 'Abbey Road' },
  { author: 'Nirvana',                track: 'Smells Like Teen Spirit',       album: 'Nevermind' },
  { author: 'Led Zeppelin',           track: 'Stairway to Heaven',            album: 'Led Zeppelin IV' },
  { author: 'David Bowie',            track: 'Heroes',                        album: 'Heroes' },
  { author: 'Joy Division',           track: 'Love Will Tear Us Apart',       album: 'Closer' },
  { author: 'The Cure',               track: 'Lovesong',                      album: 'Disintegration' },
  { author: 'Portishead',             track: 'Glory Box',                     album: 'Dummy' },
  { author: 'Massive Attack',         track: 'Teardrop',                      album: 'Mezzanine' },
  { author: 'Aphex Twin',             track: 'Windowlicker',                  album: 'Selected Ambient Works' },
  { author: 'Björk',                  track: 'Human Behaviour',               album: 'Debut' },
  { author: 'Sigur Rós',              track: 'Hoppípolla',                    album: 'Takk...' },
  { author: 'The National',           track: 'Bloodbuzz Ohio',                album: 'High Violet' },
  { author: 'LCD Soundsystem',        track: 'All My Friends',                album: 'Sound of Silver' },
  { author: 'Talking Heads',          track: 'This Must Be the Place',        album: 'Speaking in Tongues' },
  { author: 'Brian Eno',              track: 'Another Green World',           album: 'Another Green World' },
  { author: 'Nine Inch Nails',        track: 'Hurt',                          album: 'The Downward Spiral' },
  { author: 'Daft Punk',              track: 'Get Lucky',                     album: 'Random Access Memories' },
  { author: 'The Smashing Pumpkins',  track: 'Tonight, Tonight',              album: 'Mellon Collie and the Infinite Sadness' },
]

let token        = null
let titleType    = 'track'
let withMetadata = false
let jobs         = {}
let panelOpen    = false
let pollTimer    = null
let currentEx    = EXAMPLES[Math.floor(Math.random() * EXAMPLES.length)]


const TERMINAL = new Set(['completed', 'failed', 'cancelled'])

const STATUS_RU = {
  pending:    'в очереди',
  processing: 'в процессе',
  completed:  'завершено',
  failed:     'ошибка',
  cancelled:  'отменено',
}

const dom = {
  appCenter:    $('app-center'),
  author:       $('author'),
  title:        $('title'),
  titleLabel:   $('title-label'),
  toggleTrack:  $('toggle-track'),
  toggleAlbum:  $('toggle-album'),
  btnMeta:      $('btn-meta'),
  btnMetaInfo:  $('btn-meta-info'),
  metaTooltip:  $('meta-tooltip'),
  btnStart:     $('btn-start'),
  errMsg:       $('error-msg'),
  jobsPanel:    $('jobs-panel'),
  jobsList:     $('jobs-list'),
  jobsCount:    $('jobs-count'),
}

async function fetchToken() {
  const saved = localStorage.getItem('sonanz_token')
  if (saved) {
    token = saved
    return
  }
  await refreshToken()
}

async function refreshToken() {
  try {
    const res  = await fetch('/api/v1/token', { method: 'POST' })
    const data = await res.json()
    token = data.token
    localStorage.setItem('sonanz_token', token)
  } catch {
    showError('Не удалось подключиться к серверу')
  }
}

async function apiFetch(url, options = {}) {
  const res = await fetch(url, {
    ...options,
    headers: { ...options.headers, 'Authorization': `Bearer ${token}` },
  })

  if (res.status === 401) {
    localStorage.removeItem('sonanz_token')
    await refreshToken()
    return fetch(url, {
      ...options,
      headers: { ...options.headers, 'Authorization': `Bearer ${token}` },
    })
  }

  return res
}

function bindUI() {
  dom.toggleTrack.addEventListener('click', () => setMode('track'))
  dom.toggleAlbum.addEventListener('click', () => setMode('album'))
  dom.btnMeta.addEventListener('click', toggleMeta)
  dom.btnStart.addEventListener('click', startJob)
  dom.author.addEventListener('input', () => dom.author.classList.remove('invalid'))
  dom.title.addEventListener('input',  () => dom.title.classList.remove('invalid'))

  dom.btnMetaInfo.addEventListener('click', e => {
    e.stopPropagation()
    dom.metaTooltip.classList.toggle('visible')
  })
  document.addEventListener('click', () => dom.metaTooltip.classList.remove('visible'))
}

function applyPlaceholders() {
  dom.author.placeholder = currentEx.author
  dom.title.placeholder  = titleType === 'track' ? currentEx.track : currentEx.album
}

function setMode(mode) {
  titleType = mode
  dom.toggleTrack.classList.toggle('active', mode === 'track')
  dom.toggleAlbum.classList.toggle('active', mode === 'album')
  dom.titleLabel.textContent = mode === 'track' ? 'Трек' : 'Альбом'
  dom.title.placeholder      = mode === 'track' ? currentEx.track : currentEx.album
}

function toggleMeta() {
  withMetadata = !withMetadata
  dom.btnMeta.classList.toggle('active', withMetadata)
  dom.btnMeta.querySelector('.meta-icon').textContent = withMetadata ? '✓' : '+'
}

async function startJob() {
  if (!token) return showError('Нет соединения с сервером')

  const author = dom.author.value.trim()
  const title  = dom.title.value.trim()

  if (!author) { dom.author.classList.add('invalid'); return }
  if (!title)  { dom.title.classList.add('invalid');  return }

  dom.btnStart.disabled = true
  clearError()

  try {
    const res = await apiFetch('/api/v1/job', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ author, title, titleType, withMetadata }),
    })

    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const { id } = await res.json()

    jobs[id] = { id, status: 'pending', progress: 0, author, title, type: titleType, readyToDownload: false, downloaded: false }
    saveMeta(id, { author, title, type: titleType })
    prependJobEl(id)
    dom.jobsCount.textContent = Object.keys(jobs).length

    if (!panelOpen) {
      panelOpen = true
      dom.jobsPanel.classList.add('open')
      dom.appCenter.classList.add('shifted')
    }

    if (!pollTimer) {
      pollTimer = setInterval(pollJobs, 3000)
    }

  } catch {
    showError('Ошибка запуска задачи')
  } finally {
    dom.btnStart.disabled = false
  }
}

async function pollJobs() {
  if (!token) return
  try {
    const res = await apiFetch('/api/v1/job')
    if (!res.ok) return

    const list = await res.json()
    for (const item of list) {
      if (!jobs[item.id]) continue
      jobs[item.id].status   = item.status
      jobs[item.id].progress = item.progress
      updateJobEl(item.id)

      if (item.status === 'completed' && item.progress === 100 && !jobs[item.id].readyToDownload) {
        jobs[item.id].readyToDownload = true
        showDownloadBtn(item.id)
      }
    }
  } catch {
    // молчим при ошибке поллинга
  }

  const hasActive = Object.values(jobs).some(j => !TERMINAL.has(j.status))
  if (!hasActive) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

function showDownloadBtn(id) {
  const el = document.getElementById(`job-${id}`)
  if (!el) return
  const btn = el.querySelector('.btn-dl')
  btn.style.display = 'flex'
  btn.addEventListener('click', async () => {
    btn.disabled = true
    await downloadJob(id)
    btn.classList.add('downloaded')
    markDownloaded(id)
    btn.disabled = false
  })
}

async function downloadJob(id) {
  try {
    const res = await apiFetch(`/api/v1/job/${id}/download`)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)

    const blob = await res.blob()
    const ext  = jobs[id]?.type === 'album' ? 'zip' : 'mp3'
    const name = extractFilename(res, `${id}.${ext}`)
    const url  = URL.createObjectURL(blob)
    const a    = Object.assign(document.createElement('a'), { href: url, download: name })
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  } catch {
    if (jobs[id]) jobs[id].downloaded = false
  }
}

function extractFilename(res, fallback) {
  const cd = res.headers.get('Content-Disposition') || ''

  const star = cd.match(/filename\*\s*=\s*UTF-8''([^;\n]+)/i)
  if (star) {
    try {
      return decodeURIComponent(star[1].trim()) || fallback
    } catch {
      // битый percent-encoding — падаем на обычный filename ниже
    }
  }

  const m = cd.match(/filename\s*=\s*((['"]).*?\2|[^;\n]*)/i)
  return m ? m[1].replace(/['"]/g, '').trim() || fallback : fallback
}

function prependJobEl(id) {
  const job = jobs[id]
  const el  = document.createElement('div')
  el.className = 'job-item'
  el.id = `job-${id}`
  el.innerHTML = `
    <div class="job-main">
      <div class="job-info">
        <div class="job-header">
          <div class="job-name-group">
            <span class="job-name">${esc(job.author)} — ${esc(job.title)}</span>
            <span class="job-type">${job.type === 'album' ? 'альбом' : 'трек'}</span>
          </div>
          <span class="job-badge job-badge-${job.status}">${STATUS_RU[job.status] ?? job.status}</span>
        </div>
        <div class="job-progress-row">
          <div class="job-bar">
            <div class="job-bar-fill" style="width:${job.progress}%"></div>
          </div>
          <span class="job-pct">${job.progress}%</span>
        </div>
      </div>
      <button class="btn-dl" style="display:none" title="Скачать">
        <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
          <path d="M7.5 2v7.5M4.5 7l3 3 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M2.5 13h10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </button>
    </div>
  `
  dom.jobsList.prepend(el)
}

function updateJobEl(id) {
  const job = jobs[id]
  const el  = document.getElementById(`job-${id}`)
  if (!el) return

  const badge = el.querySelector('.job-badge')
  badge.className   = `job-badge job-badge-${job.status}`
  badge.textContent = STATUS_RU[job.status] ?? job.status

  const fill = el.querySelector('.job-bar-fill')
  fill.style.width = `${job.progress}%`
  fill.classList.toggle('done', job.progress === 100)

  el.querySelector('.job-pct').textContent = `${job.progress}%`
}

function showError(msg) {
  dom.errMsg.textContent = msg
  clearTimeout(showError._t)
  showError._t = setTimeout(clearError, 4000)
}

function clearError() { dom.errMsg.textContent = '' }

function esc(s) {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
}

function saveMeta(id, meta) {
  const stored = JSON.parse(localStorage.getItem('sonanz_jobs') || '{}')
  stored[id] = meta
  localStorage.setItem('sonanz_jobs', JSON.stringify(stored))
}

function markDownloaded(id) {
  const stored = JSON.parse(localStorage.getItem('sonanz_jobs') || '{}')
  if (stored[id]) {
    stored[id].downloaded = true
    localStorage.setItem('sonanz_jobs', JSON.stringify(stored))
  }
}

function loadMeta() {
  return JSON.parse(localStorage.getItem('sonanz_jobs') || '{}')
}

async function restoreJobs() {
  const meta = loadMeta()
  if (!Object.keys(meta).length) return

  const res = await apiFetch('/api/v1/job')
  if (!res.ok) return

  const list = await res.json()
  if (!list.length) return

  for (const item of list) {
    const m = meta[item.id]
    if (!m) continue
    jobs[item.id] = { ...m, id: item.id, status: item.status, progress: item.progress, readyToDownload: false, downloaded: false }
    prependJobEl(item.id)
    updateJobEl(item.id)
    if (item.status === 'completed' && item.progress === 100) {
      jobs[item.id].readyToDownload = true
      showDownloadBtn(item.id)
      if (m.downloaded) {
        document.getElementById(`job-${item.id}`)?.querySelector('.btn-dl')?.classList.add('downloaded')
      }
    }
  }

  if (Object.keys(jobs).length) {
    panelOpen = true
    dom.jobsPanel.classList.add('open')
    dom.appCenter.classList.add('shifted')
    dom.jobsCount.textContent = Object.keys(jobs).length

    const hasActive = Object.values(jobs).some(j => !TERMINAL.has(j.status))
    if (hasActive) pollTimer = setInterval(pollJobs, 3000)
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  applyPlaceholders()
  bindUI()
  await fetchToken()
  await restoreJobs()
})
