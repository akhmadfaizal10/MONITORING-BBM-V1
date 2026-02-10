@extends('app')

@section('title', 'DATA VIEW')
@section('page_title', 'DATA VIEW')
@push('styles')
    <link rel="stylesheet" href="{{ asset('style.css') }}">
@endpush

@include('partials.style')

@section('content')
<div class="container-fluid px-4 pt-4">

    {{-- ===== FILTER SECTION ===== --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header">
            <h5 class="mb-0">Select Company & Table</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Company</label>
                    <select id="companySelect" class="form-select"></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Table (Company / Per-NIK)</label>
                    <select id="tableSelect" class="form-select"></select>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button class="btn btn-primary" id="loadBtn">
                    <i class="bi bi-arrow-repeat me-1"></i> Load
                </button>
                <button class="btn btn-secondary" id="exportBtn">
                    <i class="bi bi-download me-1"></i> Export
                </button>
                <button class="btn btn-warning text-white" id="clearTableBtn">
                    <i class="bi bi-eraser me-1"></i> Clear
                </button>
                <button class="btn btn-danger" id="deleteTableBtn">
                    <i class="bi bi-trash me-1"></i> Delete Table
                </button>
                <button class="btn btn-danger" id="deleteCompanyBtn">
                    <i class="bi bi-building-x me-1"></i> Delete Company
                </button>
            </div>
        </div>
    </div>

    {{-- ===== DATA SECTION ===== --}}
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Data (<span id="recordCount">0</span> records)
            </h5>
            <input type="text"
                   id="searchBox"
                   class="form-control w-auto"
                   placeholder="Search records..." style="display:none;">
        </div>

        <div class="card-body">
            <div class="table-responsive" id="tableContainer">
                <div class="text-center text-muted py-5">
                    Pilih company & table untuk melihat data
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <button class="btn btn-outline-secondary" id="prevPage">Previous</button>
                <span class="fw-semibold" id="pageInfo">Page 1</span>
                <button class="btn btn-outline-secondary" id="nextPage">Next</button>
            </div>
        </div>
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