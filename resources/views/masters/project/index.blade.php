<!DOCTYPE html>
<html>
<head>
    <title>Project Master</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-bearer-token" content="{{ $apiBearerToken }}">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            padding: 16px;
            background: #f3f2f1;
            color: #323130;
        }
        .header {
            background: #fff;
            color: #201f1e;
            padding: 14px 16px;
            border: 1px solid #edebe9;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .card {
            background: white;
            border-radius: 2px;
            border: 1px solid #edebe9;
            padding: 14px;
            margin-bottom: 12px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }
        label {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
            font-weight: 600;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            box-sizing: border-box;
        }
        button {
            background: #106ebe;
            color: white;
            border: 1px solid #106ebe;
            padding: 8px 12px;
            border-radius: 2px;
            cursor: pointer;
        }
        button.secondary {
            background: #fff;
            color: #323130;
            border-color: #8a8886;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            border-bottom: 1px solid #edebe9;
            padding: 10px 8px;
        }
        th {
            color: #605e5c;
            background: #faf9f8;
            font-weight: 600;
        }
        .error {
            color: #c0392b;
            font-size: 13px;
            margin-top: 4px;
        }
        .status {
            background: #e8f6ee;
            color: #1f7a48;
            padding: 10px;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        .back-link {
            text-decoration: none;
            display: inline-block;
            margin-top: 12px;
        }
        .action-btn {
            background: #a4262c;
            padding: 6px 10px;
            border-radius: 2px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @include('partials.global-company-selector')
    <div class="header">
        <h1>Project Master</h1>
        <button type="button" id="sync-d365-btn">Sync all from D365</button>
    </div>

    <div class="card">
        <h2>Add project</h2>
     
        <div id="form-status" class="status" style="display:none;"></div>
        <div id="form-errors" class="error" style="display:none;"></div>
        <form id="project-form">
            <div class="form-row">
                <div>
                    <label for="company_id">Company</label>
                    <select id="company_id" name="company_id" required>
                        <option value="">Loading companies…</option>
                    </select>
                </div>
                <div>
                    <label for="d365_id">D365 ID</label>
                    <input id="d365_id" name="d365_id" type="text" maxlength="100" required placeholder="Project id ">
                </div>
                <div>
                    <label for="name">Project name</label>
                    <input id="name" name="name" type="text" maxlength="255" required placeholder="Project name">
                </div>
            </div>
            <button type="submit">Save project</button>
        </form>
    </div>

    <div class="card">
        <h2>Filter by company</h2>
        <div class="form-row">
            <div>
                <label for="filter_company">Company</label>
                <select id="filter_company">
                    <option value="">All companies</option>
                </select>
            </div>
            <div style="display:flex;align-items:flex-end;">
                <button type="button" class="secondary" id="apply-filter">Apply filter</button>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Projects</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Company</th>
                    <th>D365 ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" id="projects-loading">Loading projects...</td>
                </tr>
            </tbody>
        </table>
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>
    <script>
        const projectsTbody = document.querySelectorAll('tbody')[0];
        const companySelect = document.getElementById('company_id');
        const filterSelect = document.getElementById('filter_company');
        const projectsApiUrl = '/api/projects';
        const companiesApiUrl = '/api/companies';
        const syncProjectsUrl = "{{ route('masters.project.sync') }}";
        const apiBearerToken = document.querySelector('meta[name="api-bearer-token"]')?.content ?? '';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const defaultHeaders = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            Authorization: `Bearer ${apiBearerToken}`,
        };

        const formatDate = (value) => {
            if (!value) return '-';
            return new Date(value).toLocaleString();
        };

        const setFormMessage = (el, text, show) => {
            el.textContent = text;
            el.style.display = show ? 'block' : 'none';
        };

        const loadCompaniesForSelects = async () => {
            try {
                const response = await fetch(companiesApiUrl, { headers: defaultHeaders });
                if (!response.ok) throw new Error('companies');
                const payload = await response.json();
                const companies = payload.data || [];
                const options = companies.length
                    ? companies.map((c) => `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.d365_id)})</option>`).join('')
                    : '<option value="">No companies — create in Company Master first</option>';
                companySelect.innerHTML = '<option value="">Select company</option>' + options;
                filterSelect.innerHTML = '<option value="">All companies</option>' + companies.map((c) =>
                    `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
            } catch {
                companySelect.innerHTML = '<option value="">Failed to load companies</option>';
            }
        };

        const escapeHtml = (s) => {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        };

        const loadProjects = async () => {
            projectsTbody.innerHTML = '<tr><td colspan="6">Loading projects...</td></tr>';
            const companyId = filterSelect.value;
            const url = companyId ? `${projectsApiUrl}?company_id=${encodeURIComponent(companyId)}` : projectsApiUrl;

            try {
                const response = await fetch(url, { headers: defaultHeaders });
                if (!response.ok) throw new Error('projects');
                const payload = await response.json();
                const projects = payload.data || [];

                if (!projects.length) {
                    projectsTbody.innerHTML = '<tr><td colspan="6">No projects found. Add one above.</td></tr>';
                    return;
                }

                projectsTbody.innerHTML = projects.map((p, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${escapeHtml(p.company?.name ?? '-')}</td>
                        <td>${escapeHtml(p.d365_id ?? '-')}</td>
                        <td>${escapeHtml(p.name ?? '-')}</td>
                        <td>${formatDate(p.created_at)}</td>
                        <td>
                            <button class="action-btn" data-id="${p.id}">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } catch {
                projectsTbody.innerHTML = '<tr><td colspan="6">Failed to load projects.</td></tr>';
            }
        };

        document.getElementById('project-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, '', false);

            const body = {
                company_id: parseInt(companySelect.value, 10),
                d365_id: document.getElementById('d365_id').value.trim(),
                name: document.getElementById('name').value.trim(),
            };

            if (!body.company_id) {
                setFormMessage(errEl, 'Please select a company.', true);
                return;
            }

            try {
                const response = await fetch(projectsApiUrl, {
                    method: 'POST',
                    headers: defaultHeaders,
                    body: JSON.stringify(body),
                });
                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const msg = payload.message || (payload.errors ? JSON.stringify(payload.errors) : 'Save failed');
                    setFormMessage(errEl, msg, true);
                    return;
                }

                setFormMessage(statusEl, 'Project created.', true);
                document.getElementById('d365_id').value = '';
                document.getElementById('name').value = '';
                await loadProjects();
            } catch {
                setFormMessage(errEl, 'Network error.', true);
            }
        });

        document.getElementById('apply-filter').addEventListener('click', loadProjects);

        document.getElementById('sync-d365-btn').addEventListener('click', async () => {
            const statusEl = document.getElementById('form-status');
            const errEl = document.getElementById('form-errors');
            setFormMessage(errEl, '', false);
            setFormMessage(statusEl, 'Syncing projects from D365...', true);

            try {
                const response = await fetch(syncProjectsUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.status) {
                    throw new Error(payload.message || payload.error || 'Sync failed');
                }

                setFormMessage(statusEl, payload.message || 'Projects synced from D365.', true);
                await loadCompaniesForSelects();
                await loadProjects();
            } catch (error) {
                setFormMessage(errEl, error.message || 'Failed to sync projects from D365.', true);
                setFormMessage(statusEl, '', false);
            }
        });

        projectsTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.action-btn')) return;
            const projectId = event.target.getAttribute('data-id');
            if (!window.confirm('Delete this project?')) return;

            try {
                const response = await fetch(`${projectsApiUrl}/${projectId}`, {
                    method: 'DELETE',
                    headers: defaultHeaders,
                });
                if (!response.ok) throw new Error('delete');
                await loadProjects();
            } catch {
                window.alert('Failed to delete project.');
            }
        });

        (async () => {
            await loadCompaniesForSelects();
            await loadProjects();
        })();
    </script>
</body>
</html>
