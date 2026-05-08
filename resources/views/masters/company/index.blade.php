<!DOCTYPE html>
<html>
<head>
    <title>Company Master</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-bearer-token" content="{{ $apiBearerToken }}">
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            background: #f3f2f1;
            color: #323130;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #edebe9;
            padding: 12px 0;
            flex-shrink: 0;
        }
        .logo { padding: 10px 16px 18px; border-bottom: 1px solid #edebe9; margin-bottom: 8px; font-weight: 700; }
        .label { padding: 10px 16px 4px; color: #8a8886; font-size: 11px; text-transform: uppercase; }
        .menu-link { display: block; padding: 10px 16px; color: #323130; text-decoration: none; border-radius: 8px; margin: 2px 8px; font-size: 14px; }
        .menu-link:hover { background: #f3f2f1; }
        .menu-link.active { background: #deecf9; color: #005a9e; }
        .sub { margin-left: 16px; padding-left: 8px; border-left: 2px solid #edebe9; }
        .main { flex: 1; padding: 12px 16px; overflow: auto; }
        .page-shell { border: 1px solid #edebe9; background: #fff; border-radius: 2px; overflow: hidden; }
        .command-bar { height: 44px; border-bottom: 1px solid #edebe9; background: #fff; display: flex; align-items: center; justify-content: space-between; padding: 0 12px; }
        .crumb { font-size: 12px; color: #605e5c; }
        .toolbar { margin-bottom: 12px; }
        .toolbar-row { display: flex; justify-content: flex-start; align-items: center; gap: 12px; }
        .title { margin: 0 0 4px; font-size: 24px; font-weight: 600; }
        .card {
            background: white;
            border-radius: 2px;
            border: 1px solid #edebe9;
            overflow: hidden;
        }
        .card-head { padding: 12px 14px; border-bottom: 1px solid #edebe9; font-size: 20px; font-weight: 600; }
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
        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            box-sizing: border-box;
        }
        button {
            background: #a4262c;
            color: white;
            border: 1px solid #a4262c;
            padding: 6px 10px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 12px;
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
        .back-link { text-decoration: none; display: inline-block; margin-top: 12px; font-size: 13px; }
        .action-btn {
            background: #a4262c;
            padding: 6px 10px;
            border-radius: 2px;
            font-size: 12px;
        }
    </style>
    @include('settings.rbac.partials.styles')
</head>
<body>
    @include('partials.global-company-selector')
    @php
        $companyCode = strtoupper((string) request()->query('company', ''));
        $companyQuery = $companyCode !== '' ? ['company' => $companyCode] : [];
    @endphp
    @include('settings.rbac.partials.sidebar')
    <main class="main">
        <div class="page-shell">
            <div class="command-bar">
                <div class="crumb">Masters / Company</div>
            </div>
            <div style="padding:12px;">
                <div class="toolbar">
                    <div class="toolbar-row">
                        <div><h1 class="title">Company Master</h1></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">Companies</div>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Company ID</th>
                                <th>Name</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" id="companies-loading">Loading companies...</td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="padding: 0 14px 12px;">
                        <a class="back-link" href="{{ route('dashboard', $companyQuery) }}">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        const companiesTbody = document.querySelector('tbody');
        const companiesApiUrl = '/api/companies';
        const apiBearerToken = document.querySelector('meta[name="api-bearer-token"]')?.content ?? '';
        const defaultHeaders = {
            Accept: 'application/json',
            Authorization: `Bearer ${apiBearerToken}`,
        };

        const formatDate = (value) => {
            if (!value) return '-';
            const dt = new Date(value);
            return dt.toLocaleString();
        };

        const loadCompanies = async () => {
            companiesTbody.innerHTML = '<tr><td colspan="5">Loading companies...</td></tr>';

            try {
                const response = await fetch(companiesApiUrl, {
                    headers: defaultHeaders
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch companies');
                }

                const payload = await response.json();
                const companies = payload.data || [];

                if (!companies.length) {
                    companiesTbody.innerHTML = '<tr><td colspan="5">No companies found. Create the first company above.</td></tr>';
                    return;
                }

                companiesTbody.innerHTML = companies.map((company, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${company.company_id ?? company.d365_id ?? '-'}</td>
                        <td>${company.name ?? '-'}</td>
                        <td>${formatDate(company.created_at)}</td>
                        <td>
                            <button class="action-btn" data-id="${company.id}">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } catch (error) {
                companiesTbody.innerHTML = '<tr><td colspan="5">Failed to load companies.</td></tr>';
            }
        };

        companiesTbody.addEventListener('click', async (event) => {
            if (!event.target.matches('.action-btn')) return;

            const companyId = event.target.getAttribute('data-id');
            const confirmed = window.confirm('Delete this company?');

            if (!confirmed) return;

            try {
                const response = await fetch(`${companiesApiUrl}/${companyId}`, {
                    method: 'DELETE',
                    headers: defaultHeaders
                });

                if (!response.ok) {
                    throw new Error('Delete failed');
                }

                await loadCompanies();
            } catch (error) {
                window.alert('Failed to delete company.');
            }
        });

        loadCompanies();
    </script>
</body>
</html>
