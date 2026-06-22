(function() {
    'use strict';

    var currentPeriod = 'daily';
    var currentModule = '';
    var currentPage = 1;

    var statusEl = document.getElementById('reportStatus');
    var resultsEl = document.getElementById('reportResults');
    var tableBody = document.getElementById('activityTableBody');
    var statsContainer = document.getElementById('statsContainer');
    var showingInfo = document.getElementById('showingInfo');
    var pagination = document.getElementById('pagination');

    function showStatus(type, msg) {
        if (!statusEl) return;
        statusEl.className = 'alert alert-' + type + ' alert-dismissible fade show';
        statusEl.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        statusEl.style.display = 'block';
        setTimeout(function() { statusEl.style.display = 'none'; }, 5000);
    }

    // Tab switching
    var tabs = document.querySelectorAll('.report-tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].addEventListener('click', function() {
            for (var j = 0; j < tabs.length; j++) tabs[j].classList.remove('active');
            this.classList.add('active');
            currentPeriod = this.dataset.period;
            var dateRange = document.getElementById('dateRange');
            if (dateRange) dateRange.style.display = currentPeriod === 'custom' ? 'block' : 'none';
        });
    }

    // Apply custom date
    var applyBtn = document.getElementById('applyCustomBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            var sd = document.getElementById('startDate').value;
            var ed = document.getElementById('endDate').value;
            if (!sd && !ed) { showStatus('warning', 'Please select at least one date'); return; }
            fetchReport(1);
        });
    }

    // Generate report
    document.getElementById('generateBtn').addEventListener('click', function() {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        fetchReport(1, function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-magic me-1"></i>Generate Report';
        });
    });

    function fetchReport(page, callback) {
        currentPage = page || 1;
        var params = 'period=' + currentPeriod + '&page=' + currentPage;

        if (currentPeriod === 'custom') {
            var sd = document.getElementById('startDate').value;
            var ed = document.getElementById('endDate').value;
            if (sd) params += '&start_date=' + sd;
            if (ed) params += '&end_date=' + ed;
        }

        currentModule = document.getElementById('moduleFilter').value;
        if (currentModule) params += '&module=' + currentModule;

        fetch('../reports/get_data.php?' + params)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.status === 'success') {
                    displayResults(res);
                    resultsEl.style.display = 'block';
                } else {
                    showStatus('danger', res.message || 'Failed to fetch data');
                }
                if (callback) callback();
            })
            .catch(function(err) {
                showStatus('danger', 'Network error: ' + err.message);
                if (callback) callback();
            });
    }

    var actionMeta = [
        { match:function(k){return k==='Total'},                    icon:'fa-list',          bg:'rgba(0,144,231,0.15)', color:'#0090e7' },
        { match:function(k){return /^Added|^Created|^Submitted|^Approved|^Applied|^Approve/i.test(k)}, icon:'fa-plus-circle', bg:'rgba(0,210,91,0.15)', color:'#00d25b' },
        { match:function(k){return /^Updated|^Edited|^Changed|^Modified|^Update|^Edit/i.test(k)},     icon:'fa-edit',        bg:'rgba(0,210,255,0.15)', color:'#00d2ff' },
        { match:function(k){return /^Deleted|^Removed|^Cancelled|^Rejected|^Delete|^Remove|^Cancel|^Reject/i.test(k)}, icon:'fa-trash-alt', bg:'rgba(252,66,74,0.15)', color:'#fc424a' },
    ];

    function getMeta(key) {
        for (var i = 0; i < actionMeta.length; i++) {
            if (actionMeta[i].match(key)) return actionMeta[i];
        }
        return { icon:'fa-circle', bg:'rgba(163,166,183,0.15)', color:'#a3a6b7' };
    }

    function displayResults(res) {
        var data = res.data || [];
        var stats = res.stats || {};
        var actions = stats.actions || {};
        window.reportData = res;

        var html = '';
        // Total card
        var t = getMeta('Total');
        html += '<div class="stat-card"><div class="stat-icon" style="background:' + t.bg + ';color:' + t.color + '"><i class="fas ' + t.icon + '"></i></div><div class="stat-info"><span class="stat-label">Total Activities</span><span class="stat-value" style="color:' + t.color + '">' + (stats.total || 0) + '</span></div></div>';
        // Action cards (sorted)
        var keys = Object.keys(actions).sort();
        for (var i = 0; i < keys.length; i++) {
            var k = keys[i];
            var m = getMeta(k);
            html += '<div class="stat-card"><div class="stat-icon" style="background:' + m.bg + ';color:' + m.color + '"><i class="fas ' + m.icon + '"></i></div><div class="stat-info"><span class="stat-label">' + k + '</span><span class="stat-value" style="color:' + m.color + '">' + (actions[k] || 0) + '</span></div></div>';
        }
        statsContainer.innerHTML = html;

        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No activities found for this period</td></tr>';
            showingInfo.textContent = 'Showing 0 entries';
            pagination.innerHTML = '';
            return;
        }

        var html = '';
        for (var i = 0; i < data.length; i++) {
            var row = data[i];
            var actionClass = 'badge-info';
            var actionLower = (row.action_type || '').toLowerCase();
            if (['added','create','submitted','approved'].indexOf(actionLower) !== -1) actionClass = 'badge-success';
            else if (['updated','update','changed'].indexOf(actionLower) !== -1) actionClass = 'badge-primary';
            else if (['deleted','delete','removed','cancelled','rejected'].indexOf(actionLower) !== -1) actionClass = 'badge-danger';

            var dt = row.logged_at ? new Date(row.logged_at).toLocaleString('en-IN', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '-';
            html += '<tr>' +
                '<td class="text-nowrap">' + dt + '</td>' +
                '<td>' + esc(row.user_name) + '</td>' +
                '<td>' + esc(ucfirst(row.section_name)) + '</td>' +
                '<td><span class="' + actionClass + '">' + esc(row.action_type) + '</span></td>' +
                '<td>' + esc(row.details || '') + '</td>' +
            '</tr>';
        }
        tableBody.innerHTML = html;

        var start = ((res.page - 1) * 50) + 1;
        var end = Math.min(res.page * 50, stats.total);
        showingInfo.textContent = 'Showing ' + start + ' to ' + end + ' of ' + stats.total + ' entries';

        // Pagination
        if (res.totalPages <= 1) { pagination.innerHTML = ''; return; }
        var phtml = '';
        phtml += '<li class="page-item' + (res.page <= 1 ? ' disabled' : '') + '"><a class="page-link" href="#" data-page="' + (res.page - 1) + '">&laquo;</a></li>';
        for (var p = 1; p <= res.totalPages; p++) {
            if (p > 1 && p < res.totalPages && Math.abs(p - res.page) > 2) {
                if (Math.abs(p - res.page) > 3) continue;
                phtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            phtml += '<li class="page-item' + (p === res.page ? ' active' : '') + '"><a class="page-link" href="#" data-page="' + p + '">' + p + '</a></li>';
        }
        phtml += '<li class="page-item' + (res.page >= res.totalPages ? ' disabled' : '') + '"><a class="page-link" href="#" data-page="' + (res.page + 1) + '">&raquo;</a></li>';
        pagination.innerHTML = phtml;

        // Pagination click handlers
        var pageLinks = pagination.querySelectorAll('a.page-link');
        for (var k = 0; k < pageLinks.length; k++) {
            pageLinks[k].addEventListener('click', function(e) {
                e.preventDefault();
                var pg = parseInt(this.dataset.page);
                if (pg > 0) fetchReport(pg);
            });
        }
    }

    // Download PDF
    document.getElementById('downloadPdfBtn').addEventListener('click', function() {
        var params = 'period=' + currentPeriod;
        if (currentPeriod === 'custom') {
            var sd = document.getElementById('startDate').value;
            var ed = document.getElementById('endDate').value;
            if (sd) params += '&start_date=' + sd;
            if (ed) params += '&end_date=' + ed;
        }
        if (currentModule) params += '&module=' + currentModule;
        window.open('../reports/generate_pdf.php?' + params, '_blank');
    });

    // Export CSV
    document.getElementById('exportCsvBtn').addEventListener('click', function() {
        var params = 'period=' + currentPeriod;
        if (currentPeriod === 'custom') {
            var sd = document.getElementById('startDate').value;
            var ed = document.getElementById('endDate').value;
            if (sd) params += '&start_date=' + sd;
            if (ed) params += '&end_date=' + ed;
        }
        if (currentModule) params += '&module=' + currentModule;
        window.open('../reports/export_csv.php?' + params, '_blank');
    });

    // Email Report
    document.getElementById('emailReportBtn').addEventListener('click', function() {
        if (!Swal) { showStatus('danger', 'SweetAlert2 not loaded'); return; }

        Swal.fire({
            title: 'Send Report via Email',
            html:
                '<div style="text-align:left">' +
                '<label style="color:#a3a6b7;font-size:13px;margin-bottom:4px;display:block">Send To *</label>' +
                '<input id="swalEmail" class="swal2-input" type="email" value="rk7736806@gmail.com" placeholder="primary@email.com" style="width:100%;margin-bottom:12px;background:#1a1a2e;color:#fff;border:1px solid #2c2e3e">' +
                '<label style="color:#a3a6b7;font-size:13px;margin-bottom:4px;display:block">Additional Emails (optional)</label>' +
                '<input id="swalAdditionalEmails" class="swal2-input" type="text" placeholder="email2@example.com, email3@example.com" style="width:100%;margin-bottom:12px;background:#1a1a2e;color:#fff;border:1px solid #2c2e3e">' +
                '<label style="color:#a3a6b7;font-size:13px;margin-bottom:4px;display:block">Personal Message (optional)</label>' +
                '<textarea id="swalMessage" class="swal2-textarea" placeholder="Add a note..." style="width:100%;margin-bottom:8px;background:#1a1a2e;color:#fff;border:1px solid #2c2e3e;border-radius:8px;padding:10px;min-height:70px;font-family:inherit"></textarea>' +
                '<p style="color:#6c7293;font-size:12px;margin:0">&#128196; PDF report attached &bull; Summary stats in email body</p>' +
                '</div>',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-envelope me-1"></i> Send Email',
            cancelButtonText: 'Cancel',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary px-4',
                cancelButton: 'btn btn-outline-secondary px-4 ms-2'
            },
            preConfirm: function() {
                var email = document.getElementById('swalEmail').value.trim();
                var additional = document.getElementById('swalAdditionalEmails').value.trim();
                var message = document.getElementById('swalMessage').value.trim();
                if (!email) { Swal.showValidationMessage('Email is required'); return false; }
                return { email: email, additional_emails: additional, message: message };
            }
        }).then(function(result) {
            if (!result.isConfirmed || !result.value) return;
            var data = result.value;
            data.period = currentPeriod;
            if (currentPeriod === 'custom') {
                data.start_date = document.getElementById('startDate').value;
                data.end_date = document.getElementById('endDate').value;
            }
            data.module = currentModule;

            Swal.fire({
                title: 'Sending...',
                html: '<i class="fas fa-spinner fa-spin fa-2x" style="color:#0090e7"></i><p style="color:#a3a6b7;margin-top:10px">Sending report via email...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: function() {
                    var formData = new FormData();
                    formData.append('email', data.email);
                    formData.append('additional_emails', data.additional_emails);
                    formData.append('message', data.message);
                    formData.append('period', data.period);
                    formData.append('start_date', data.start_date || '');
                    formData.append('end_date', data.end_date || '');

                    fetch('../reports/email_report.php', { method: 'POST', body: formData })
                        .then(function(r) { return r.json(); })
                        .then(function(res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'Sent!', text: res.message, confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'btn btn-primary px-4' } });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Failed', text: res.message, confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'btn btn-danger px-4' } });
                            }
                        })
                        .catch(function(err) {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Network error: ' + err.message, confirmButtonText: 'OK', buttonsStyling: false, customClass: { confirmButton: 'btn btn-danger px-4' } });
                        });
                }
            });
        });
    });

    // Helper functions
    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function ucfirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Auto-load daily on page load
    document.addEventListener('DOMContentLoaded', function() {
        fetchReport(1);
    });
})();
