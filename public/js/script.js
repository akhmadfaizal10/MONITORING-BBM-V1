// Variabel `companyData` sudah disediakan oleh file Blade dari Controller.
// Strukturnya adalah Object, Contoh: { "Pt Agro Mandiri": [ {vehicle...}, ... ] }

let selectedCompany = null;

// Objek Ikon
const icons = {
  building: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
  car: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2m-6 0h4"/></svg>',
  alert: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
  check: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  // --- IKON DROPLET DIPERBAIKI ---
  droplet: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a7 7 0 0 0 7-7c0-3.87-7-13-7-13s-7 9.13-7 13a7 7 0 0 0 7 7z" /></svg>',
  trending: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>',
  chevronLeft: '<svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>',
  creditCard: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
  activity: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
  // --- IKON FUEL DIPERBAIKI ---
  fuel: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v5a3 3 0 003 3h1a3 3 0 003-3v-5a3 3 0 00-3-3h-1a3 3 0 00-3 3zm0 0V9a3 3 0 013-3h1a3 3 0 013 3v2m-6 0h6m-3 5v5m-3-13h.01" /></svg>',
  clock: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
  shield: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
  alertCircle: '<svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
};

function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function getUniqueCompanies() {
    return Object.keys(companyData);
}

function getCompanyStats(companyName) {
    const companyVehicles = companyData[companyName] || [];
    const stats = { total: companyVehicles.length, normal: 0, refuel: 0, alerts: 0 };
    companyVehicles.forEach(v => {
        if (v.status === 'normal') stats.normal++;
        else if (v.status === 'refuel') stats.refuel++;
        else if (v.status === 'theft' || v.status === 'plugged_theft') stats.alerts++;
    });
    return stats;
}

function getStatusConfig(status) {
    const statusKey = capitalizeFirstLetter((status || '').replace('_', ' '));
    const configs = {
        'Normal': { class: 'normal', icon: icons.check },
        'Refuel': { class: 'refuel', icon: icons.droplet },
        'Theft': { class: 'theft', icon: icons.alert },
        'Plugged Theft': { class: 'plugged-theft', icon: icons.shield },
    };
    return configs[statusKey] || { class: 'unknown', icon: icons.alertCircle };
}

function getFuelBarClass(fuelLevel) {
    const level = parseInt(fuelLevel);
    if (isNaN(level)) return 'low';
    if (level > 50) return 'high';
    if (level > 25) return 'medium';
    return 'low';
}

function renderCompanies() {
    const companies = getUniqueCompanies();
    const companiesView = document.getElementById('companiesView');
    companiesView.innerHTML = companies.map((company, index) => {
        const stats = getCompanyStats(company);
        const hasAlerts = stats.alerts > 0;
        return `
            <div class="card" onclick="selectCompany('${company}')" style="animation-delay: ${index * 0.1}s">
                <div class="card-header"><div class="card-header-content"><div class="card-header-left"><div class="card-icon">${icons.building}</div><h2 class="card-title">${company}</h2></div>${hasAlerts ? `<div class="alert-icon">${icons.alert}</div>` : ''}</div></div>
                <div class="card-body">
                    <div class="total-vehicles"><div class="total-vehicles-left">${icons.car}<span>Total Vehicles</span></div><div class="total-vehicles-right"><span>${stats.total}</span>${icons.trending}</div></div>
                    <div class="stats-grid">
                        <div class="stat-card normal"><div class="stat-icon">${icons.check}</div><div class="stat-label">Normal</div><div class="stat-value">${stats.normal}</div></div>
                        <div class="stat-card refuel"><div class="stat-icon">${icons.droplet}</div><div class="stat-label">Refuel</div><div class="stat-value">${stats.refuel}</div></div>
                        <div class="stat-card alerts"><div class="stat-icon">${icons.alert}</div><div class="stat-label">Alerts</div><div class="stat-value">${stats.alerts}</div></div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function renderTable() {
    const filteredVehicles = companyData[selectedCompany] || [];
    const tableView = document.getElementById('tableView');
    const stats = getCompanyStats(selectedCompany);
    tableView.innerHTML = `
        <div class="table-card">
            <div class="table-header"><div class="table-header-left">${icons.car}<span>Fleet Overview - ${selectedCompany}</span></div><span class="badge">${filteredVehicles.length} Vehicles</span></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th><div>${icons.creditCard} NIK</div></th><th><div>${icons.car} Vehicle ID</div></th><th><div>${icons.activity} Status</div></th><th><div>${icons.fuel} Fuel Level</div></th><th><div>${icons.clock} Last Update</div></th></tr></thead>
                    <tbody>
                        ${filteredVehicles.map((v, index) => {
                            const statusConfig = getStatusConfig(v.status);
                            const statusText = capitalizeFirstLetter((v.status || '').replace('_', ' '));
                            const fuelClass = getFuelBarClass(v.fuel_level);
                            const fuelLevelText = v.fuel_level !== null ? `${v.fuel_level} L` : 'N/A';
                            return `
                                <tr style="animation: fadeInUp 0.3s ease-out ${index * 0.05}s backwards">
                                    <td><span class="code-badge">${v.nik}</span></td>
                                    <td><span class="code-badge">${v.vehicle_id}</span></td>
                                    <td><span class="status-badge ${statusConfig.class}">${statusConfig.icon} ${statusText}</span></td>
                                    <td><div class="fuel-level"><div class="fuel-bar-container"><div class="fuel-bar ${fuelClass}" style="width: ${fuelLevelText}"></div></div><span class="fuel-text">${fuelLevelText}</span></div></td>
                                    <td><div class="last-update"><div class="update-indicator"></div> ${v.recorded_at}</div></td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        <div class="footer-stats">
            <div class="footer-stat-card total"><div class="footer-stat-header"><div class="footer-stat-icon">${icons.car}</div><div class="footer-stat-label">Total Vehicles</div></div><div class="footer-stat-value">${stats.total}</div></div>
            <div class="footer-stat-card normal"><div class="footer-stat-header"><div class="footer-stat-icon">${icons.check}</div><div class="footer-stat-label">Normal</div></div><div class="footer-stat-value">${stats.normal}</div></div>
            <div class="footer-stat-card refuel"><div class="footer-stat-header"><div class="footer-stat-icon">${icons.droplet}</div><div class="footer-stat-label">Need Refuel</div></div><div class="footer-stat-value">${stats.refuel}</div></div>
            <div class="footer-stat-card alerts"><div class="footer-stat-header"><div class="footer-stat-icon">${icons.alert}</div><div class="footer-stat-label">Alerts</div></div><div class="footer-stat-value">${stats.alerts}</div></div>
        </div>
    `;
}

function selectCompany(company) {
    selectedCompany = company;
    document.getElementById('companiesView').classList.add('hidden');
    document.getElementById('tableView').classList.remove('hidden');
    const backButton = document.getElementById('backButton');
    backButton.innerHTML = `<button class="back-button" onclick="goBack()">${icons.chevronLeft}</button>`;
    backButton.classList.remove('hidden');
    document.getElementById('headerSubtitle').textContent = `Viewing fleet data for ${company}`;
    renderTable();
}

function goBack() {
    selectedCompany = null;
    document.getElementById('tableView').classList.add('hidden');
    document.getElementById('companiesView').classList.remove('hidden');
    document.getElementById('backButton').classList.add('hidden');
    document.getElementById('headerSubtitle').textContent = 'Real-time fleet tracking • Select a company to begin';
}

document.addEventListener('DOMContentLoaded', () => {
    renderCompanies();
});