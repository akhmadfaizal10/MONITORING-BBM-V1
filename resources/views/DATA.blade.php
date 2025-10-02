@extends('app')

@section('title', 'Monitoring Dashboard')

@section('content')
  <div class="form-section">
    <h2 class="form-title">Select Company & Table</h2>
    <div class="form-row">
      <div class="form-group">
        <label for="companySelect">Company</label>
        <select id="companySelect"></select>
      </div>
      <div class="form-group">
        <label for="tableSelect">Table (Company / Per-NIK)</label>
        <select id="tableSelect"></select>
      </div>
    </div>

    <div class="button-group">
      <button class="btn btn-primary" id="loadBtn">
        <i class="fas fa-sync-alt"></i> Load
      </button>
      <button class="btn btn-secondary" id="exportBtn">
        <i class="fas fa-download"></i> Export
      </button>
      <button class="btn btn-warning" id="clearTableBtn">
        <i class="fas fa-eraser"></i> Clear
      </button>
      <button class="btn btn-danger" id="deleteTableBtn">
        <i class="fas fa-trash"></i> Delete Table
      </button>
      <button class="btn btn-danger" id="deleteCompanyBtn">
        <i class="fas fa-trash"></i> Delete Company
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
  const apiBase = "{{ url('/DATA/api') }}";
  let state = { table: null, page: 1, per_page: 50, total: 0, search: '' };

  async function fetchCompanies() {
    const r = await fetch(apiBase + '/companies');
    const arr = await r.json();
    const sel = document.getElementById('companySelect');
    sel.innerHTML = '<option value="">-- Select company --</option>';
    arr.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.table;
      opt.text = c.name;
      sel.appendChild(opt);
    });
  }

  async function fetchCompanyTables(companyTable) {
    const r = await fetch(apiBase + '/company-tables?company=' + encodeURIComponent(companyTable));
    const arr = await r.json();
    const sel = document.getElementById('tableSelect');
    sel.innerHTML = '<option value="">-- Select table --</option>';
    arr.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.text = t;
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

    const r = await fetch(`${apiBase}/clear-table?table=${encodeURIComponent(tbl)}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
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

    const r = await fetch(`${apiBase}/delete-table?table=${encodeURIComponent(tbl)}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const json = await r.json();
    alert(json.message || 'Operation completed');
    await fetchCompanies();
    document.getElementById('tableSelect').innerHTML = '<option value="">-- Select table --</option>';
    document.getElementById('tableContainer').innerHTML = '';
    document.getElementById('recordCount').textContent = 0;
  });

  // Delete company
  document.getElementById('deleteCompanyBtn').addEventListener('click', async () => {
    const company = document.getElementById('companySelect').value;
    if (!company) return alert('Please select a company first');
    if (!confirm(`Are you sure you want to DELETE all data & tables for company ${company}?`)) return;

    const r = await fetch(`${apiBase}/delete-company?company=${encodeURIComponent(company)}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const json = await r.json();
    alert(json.message || 'Operation completed');
    await fetchCompanies();
    document.getElementById('tableSelect').innerHTML = '<option value="">-- Select table --</option>';
    document.getElementById('tableContainer').innerHTML = '';
    document.getElementById('recordCount').textContent = 0;
  });

  // Init
  fetchCompanies();
</script>
@endpush
