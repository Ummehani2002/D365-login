<style>
        .rbac-page-intro {
            margin-bottom: 14px;
            font-size: 13px;
            color: #605e5c;
            max-width: 720px;
            line-height: 1.45;
        }
        #listChrome {
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 2px;
            margin-bottom: 16px;
        }
        .d365-title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 12px 20px;
            padding: 14px 16px 10px;
            border-bottom: 1px solid #edebe9;
        }
        .d365-page-h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 400;
            color: #323130;
            letter-spacing: -0.02em;
        }
        .d365-view-dd {
            min-width: 200px;
            padding: 5px 28px 5px 8px;
            font-size: 13px;
            font-family: inherit;
            border: 1px solid #8a8886;
            border-radius: 2px;
            background: #fff;
            color: #323130;
        }
        .d365-cmd-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 2px;
            padding: 6px 12px 8px;
            border-bottom: 1px solid #edebe9;
            background: linear-gradient(to bottom, #faf9f8 0%, #fff 100%);
        }
        .d365-cmd {
            padding: 6px 12px;
            font-size: 13px;
            font-family: inherit;
            color: #323130;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 2px;
            cursor: pointer;
        }
        .d365-cmd:hover:not(:disabled) {
            background: #edebe9;
        }
        .d365-cmd:disabled {
            opacity: .45;
            cursor: not-allowed;
        }
        .d365-cmd-active {
            background: #edebe9 !important;
            font-weight: 600;
            border-color: #c8c6c4 !important;
        }
        .d365-cmd-primary {
            border-color: #0078d4 !important;
            color: #0078d4 !important;
            font-weight: 600;
        }
        .d365-cmd-primary:hover:not(:disabled) {
            background: #deecf9 !important;
        }
        .d365-cmd-danger {
            border-color: #a4262c !important;
            color: #a4262c !important;
        }
        .d365-cmd-sep {
            width: 1px;
            height: 18px;
            background: #edebe9;
            margin: 0 6px;
        }
        .d365-filter-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-bottom: 1px solid #edebe9;
            background: #faf9f8;
            flex-wrap: wrap;
        }
        .d365-filter-row label {
            font-size: 12px;
            color: #605e5c;
            white-space: nowrap;
        }
        .d365-filter-row input[type="search"],
        .d365-filter-row select.d365-company-filter {
            padding: 7px 10px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            font-size: 13px;
            font-family: inherit;
            background: #fff;
        }
        .d365-filter-row input[type="search"] {
            flex: 1;
            max-width: 420px;
            min-width: 120px;
        }
        .d365-filter-row select.d365-company-filter {
            min-width: 220px;
        }
        .table-card {
            overflow: auto;
        }
        table.users-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table.users-grid th {
            text-align: left;
            padding: 8px 12px;
            border-bottom: 1px solid #edebe9;
            background: #fff;
            color: #605e5c;
            font-weight: 600;
            white-space: nowrap;
            border-right: 1px solid #f3f2f1;
        }
        table.users-grid th:last-child { border-right: none; }
        table.users-grid td {
            padding: 8px 12px;
            border-bottom: 1px solid #edebe9;
            vertical-align: middle;
            border-right: 1px solid #f3f2f1;
        }
        table.users-grid td:last-child { border-right: none; }
        table.users-grid tbody tr {
            cursor: pointer;
            background: #fff;
        }
        table.users-grid tbody tr:nth-child(even) {
            background: #faf9f8;
        }
        table.users-grid tbody tr:hover {
            background: #f3f2f1;
        }
        table.users-grid tbody tr.selected {
            background: #deecf9;
        }
        table.users-grid .td-radio {
            width: 40px;
            text-align: center;
            vertical-align: middle;
        }
        table.users-grid .td-radio input {
            cursor: pointer;
        }
        .user-id-link,
        a.view-inline-link {
            color: #0078d4;
            font-weight: 600;
            text-decoration: underline;
        }
        a.view-inline-link:hover {
            color: #106ebe;
        }
        .muted {
            color: #8a8886;
            font-size: 12px;
        }
        #detailSection.detail-card {
            margin-top: 0;
            background: #fff;
            border: 1px solid #edebe9;
            border-radius: 2px;
            padding: 0;
            overflow: hidden;
        }
        .detail-top {
            padding: 12px 16px 0;
            border-bottom: 1px solid #edebe9;
            background: #faf9f8;
        }
        .d365-bc {
            font-size: 12px;
            color: #605e5c;
            margin-bottom: 6px;
        }
        .d365-record-title {
            margin: 0 0 10px;
            font-size: 22px;
            font-weight: 400;
            color: #323130;
        }
        .detail-top .d365-cmd-bar {
            margin: 0 -16px;
            padding-left: 16px;
            padding-right: 16px;
            border-bottom: none;
            background: #faf9f8;
        }
        .d365-section {
            border-bottom: 1px solid #edebe9;
        }
        .d365-section:last-of-type {
            border-bottom: none;
        }
        .d365-section-head {
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #323130;
            background: #fff;
            border-bottom: 1px solid #edebe9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .d365-caret {
            font-size: 10px;
            color: #605e5c;
        }
        .d365-section-body {
            padding: 16px 18px 20px;
            background: #fff;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px 24px;
        }
        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .field label {
            display: block;
            font-size: 12px;
            color: #605e5c;
            margin-bottom: 4px;
        }
        .field label .req { color: #a4262c; margin-left: 2px; }
        .field input, .field select {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #8a8886;
            border-radius: 2px;
            font-size: 13px;
            font-family: inherit;
            box-sizing: border-box;
            background: #fff;
        }
        .field input.d365-readonly-display {
            background: #f3f2f1;
            color: #323130;
            border-color: #edebe9;
        }
        .field input:disabled, .field select:disabled {
            background: #f3f2f1;
            color: #605e5c;
        }
        .roles-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .roles-toolbar button {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 2px;
            border: 1px solid #8a8886;
            background: #fff;
            cursor: pointer;
            font-family: inherit;
        }
        .roles-toolbar button.primary {
            border-color: #0078d4;
            color: #0078d4;
            font-weight: 600;
        }
        .roles-toolbar button:disabled {
            opacity: .45;
            cursor: not-allowed;
        }
        .role-checks {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 280px;
            overflow: auto;
            padding: 10px 12px;
            border: 1px solid #edebe9;
            border-radius: 2px;
            background: #faf9f8;
        }
        .role-checks label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            cursor: pointer;
        }
        .role-readonly-list {
            margin: 0;
            padding-left: 18px;
            font-size: 13px;
            color: #323130;
            line-height: 1.55;
        }
        .btn-save {
            background: #0078d4;
            color: #fff !important;
            border-color: #0078d4 !important;
        }
        .btn-save:hover:not(:disabled) {
            background: #106ebe !important;
        }
        .btn-save:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
        .btn-secondary {
            background: #fff;
            border: 1px solid #8a8886 !important;
            color: #323130 !important;
        }
        .btn-edit {
            background: #fff;
            border: 1px solid #0078d4 !important;
            color: #0078d4 !important;
            font-weight: 600;
        }
        .flash-error {
            padding: 10px 12px;
            background: #fde7e9;
            border: 1px solid #a4262c;
            color: #323130;
            font-size: 13px;
            border-radius: 2px;
            margin-bottom: 14px;
            display: none;
        }
        .flash-error.visible { display: block; }
        .empty-hint {
            padding: 28px 16px;
            text-align: center;
            color: #8a8886;
            font-size: 13px;
        }
</style>
