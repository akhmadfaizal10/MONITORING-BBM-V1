@push('styles')
<style>
.icon { width: 18px; height: 18px; stroke-width: 2; }

.card {
    border-radius: 1rem;
    transition: all 0.3s ease;
}
.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.card-header {
    background: linear-gradient(135deg, #1e293b, #334155);
    color: #fff;
    border: none;
}

.bg-soft-success { background-color: rgba(25,135,84,.1); }
.bg-soft-primary { background-color: rgba(13,110,253,.1); }
.bg-soft-danger  { background-color: rgba(220,53,69,.1); }
.bg-soft-warning { background-color: rgba(255,193,7,.1); }

.table thead th {
    background-color: #f8fafc;
    font-weight: 600;
}
</style>
@endpush
