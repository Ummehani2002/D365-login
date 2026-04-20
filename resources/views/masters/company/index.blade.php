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
    <div class="header">
        <h1>Company Master</h1>
    </div>

    <div class="card">
        <h2>Add company</h2>
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif
        <form method="post" action="{{ route('masters.company.store') }}">
            @csrf
            <div class="form-row">
                <div>
                    <label for="d365_id">D365 ID</label>
                    <input id="d365_id" name="d365_id" type="text" value="{{ old('d365_id') }}" placeholder="e.g. C001" required maxlength="100">
                </div>
                <div>
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Company name" required maxlength="255">
                </div>
            </div>
            <button type="submit" style="background:#106ebe;border-color:#106ebe;">Save company</button>
        </form>
    </div>

    <div class="card">
        <h2>Companies</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>D365 ID</th>
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
        <a class="back-link" href="{{ route('dashboard') }}">Back to Dashboard</a>
    </div>
    <script>
        const companiesTbody = document.querySelector('tbody');
        const companiesApiUrl = '/api/companies';
        const apiBearerToken = document.querySelector('meta[name="api-bearer-token"]')?.content ?? '';
        let activeApiToken = apiBearerToken;

        const defaultHeaders = () => {
            const headers = { Accept: 'application/json' };
            if (activeApiToken) {
                headers.Authorization = `Bearer ${activeApiToken}`;
            }

            return headers;
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
                    headers: defaultHeaders()
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
                        <td>${company.d365_id ?? '-'}</td>
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
                    headers: defaultHeaders()
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
