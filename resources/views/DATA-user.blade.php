@extends('app')

@section('title', 'DATA VIEW')
@section('page_title', 'DATA VIEW')

@push('styles')
    <link rel="stylesheet" href="{{ asset('style.css') }}">
@endpush

@section('content')
  <div class="form-section">
    <h2 class="form-title">Select Company & Table</h2>
    <div class="form-row">
      <div class="form-group">
        <label for="companySelect">Company</label>
        <select id="companySelect" disabled>
          <option value="">-- Memuat... --</option>
        </select>
      </div>
      <div class="form-group">
        <label for="tableSelect">Table (Company / Per-NIK)</label>
        <select id="tableSelect">
          <option value="">-- Pilih Table --</option>
        </select>
      </div>
    </div>

    <div class="button-group">
      <button class="btn btn-primary" id="loadBtn">
        Load
      </button>
      <button class="btn btn-secondary" id="exportBtn">
        Export
      </button>
      <button class="btn btn-warning" id="clearTableBtn">
        Clear
      </button>
      <button class="btn btn-danger" id="deleteTableBtn">
        Delete Table
      </button>
      <button class="btn btn-danger" id="deleteCompanyBtn">
        Delete Company
      </button>
    </div>
  </div>

  <div class="data-section">
    <div class="data-header">
      <h2>Data (<span id="recordCount">0</span> records)</h2>
      <input type="text" id="searchBox" class="search-box" placeholder="Search records...">
    </div>

    <div class="table-container" id="tableContainer"></div>

    <div class="pagination">
      <button class="btn btn-secondary" id="prevPage">Previous</button>
      <span class="page-info" id="pageInfo">Page 1</span>
      <button class="btn btn-secondary" id="nextPage">Next</button>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // GANTI API KE USER-ONLY
  const apiBase = "/data-user";  // ← HANYA INI YANG DIUBAH
  let state = { table: null, page: 1, per_page: 50, total: 0, search: '' };

  // Load perusahaan user (otomatis)
  async function fetchCompanies() {
    const r = await fetch(apiBase + '/company');
    const company = await r.json();
    const sel = document.getElementById('companySelect');
    sel.innerHTML = `<option value="${company.table}">${company.name}</option>`;
    sel.disabled = true; // Tidak bisa pilih lain
    await fetchCompanyTables(company.table);
  }

  // Load tabel NIK dari perusahaan user
  async function fetchCompanyTables(companyTable) {
    const r = await fetch(apiBase + '/tables');
    const arr = await r.json();
    const sel = document.getElementById('tableSelect');
    sel.innerHTML = '<option value="">-- Pilih Table --</option>';
    arr.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t.table;
      opt.text = t.nik;
      sel.appendChild(opt);
    });
  }

  async function loadData() {
    const tbl = document.getElementById('tableSelect').value;
    if (!tbl) { alert('Please select a table first'); return; }
    state.table = tbl;
    state.page = 1;
    await renderTable();
  }

  async function renderTable() {
    const container = document.getElementById('tableContainer');
    const url = `${apiBase}/table-data?table=${encodeURIComponent(state.table)}&page=${state.page}&per_page=${state.per_page}&search=${encodeURIComponent(state.search)}`;
    const r = await fetch(url);
    const json = await r.json();
    state.total = json.total;
    document.getElementById('recordCount').textContent = json.total;

    if (json.data.length === 0) {
      container.innerHTML = '<div class="empty-state"><h3>No data available</h3><p>Try adjusting your search or filters.</p></div>';
      return;
    }

    const keys = Object.keys(json.data[0]);
    const thead = `<thead><tr>${keys.map(k => `<th>${escapeHtml(k)}</th>`).join('')}</tr></thead>`;
    const tbody = `<tbody>${json.data.map(row => `<tr>${keys.map(k => `<td>${escapeHtml(String(row[k] ?? ''))}</td>`).join('')}</tr>`).join('')}</tbody>`;
    container.innerHTML = `<table>${thead}${tbody}</table>`;
    const totalPages = Math.ceil(state.total / state.per_page) || 1;
    document.getElementById('pageInfo').textContent = `Page ${state.page} of ${totalPages}`;
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }

  // Event Listeners
  document.getElementById('companySelect').addEventListener('change', (e) => {
    const val = e.target.value;
    if (val) fetchCompanyTables(val);
  });
  document.getElementById('loadBtn').addEventListener('click', loadData);
  document.getElementById('searchBox').addEventListener('input', (e) => {
    state.search = e.target.value;
    clearTimeout(window._searchTimer);
    window._searchTimer = setTimeout(() => renderTable(), 400);
  });
  document.getElementById('prevPage').addEventListener('click', () => {
    if (state.page > 1) { state.page--; renderTable(); }
  });
  document.getElementById('nextPage').addEventListener('click', () => {
    const max = Math.ceil(state.total / state.per_page) || 1;
    if (state.page < max) { state.page++; renderTable(); }
  });
  document.getElementById('exportBtn').addEventListener('click', () => {
    const tbl = document.getElementById('tableSelect').value;
    if (!tbl) return alert('Please select a table first');
    window.location = `${apiBase}/export?table=${encodeURIComponent(tbl)}`;
  });

  // Clear table
  document.getElementById('clearTableBtn').addEventListener('click', async () => {
    const tbl = document.getElementById('tableSelect').value;
    if (!tbl) return alert('Please select a table first');
    if (!confirm(`Are you sure you want to clear all data from table ${tbl}?`)) return;

    const r = await fetch(`${apiBase}/clear-table`, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
      },
      body: JSON.stringify({ table: tbl })
    });
    const json = await r.json();
    alert(json.message || 'Operation completed');
    await renderTable();
  });

  // Delete table
  document.getElementById('deleteTableBtn').addEventListener('click', async () => {
    const tbl = document.getElementById('tableSelect').value;
    if (!tbl) return alert('Please select a table first');
    if (!confirm(`Are you sure you want to DELETE table ${tbl}?`)) return;

    const r = await fetch(`${apiBase}/delete-table`, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
      },
      body: JSON.stringify({ table: tbl })
    });
    const json = await r.json();
    alert(json.message || 'Operation completed');
    await fetchCompanyTables(document.getElementById('companySelect').value);
    document.getElementById('tableContainer').innerHTML = '';
    document.getElementById('recordCount').textContent = 0;
  });

  // Delete company (hanya milik user)
  document.getElementById('deleteCompanyBtn').addEventListener('click', async () => {
    const company = document.getElementById('companySelect').value;
    if (!company) return alert('Company tidak tersedia');
    if (!confirm(`Are you sure you want to DELETE all data & tables for your company?`)) return;

    const r = await fetch(`${apiBase}/delete-company`, {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
      },
      body: JSON.stringify({ company })
    });
    const json = await r.json();
    alert(json.message || 'Operation completed');
    location.reload();
  });

  // Init
  fetchCompanies();
</script>
@endpush