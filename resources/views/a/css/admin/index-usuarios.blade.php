
@push('styles')
<style>
    .status-active {
        background: linear-gradient(135deg, #10B981, #059669);
        color: white;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
    }
    .status-inactive {
        background: linear-gradient(135deg, #EF4444, #DC2626);
        color: white;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
    }
    .status-no-plan {
        background: linear-gradient(135deg, #6B7280, #4B5563);
        color: white;
        box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
    }
    .status-admin {
        background: linear-gradient(135deg, #8B5CF6, #7C3AED);
        color: white;
        box-shadow: 0 2px 4px rgba(139, 92, 246, 0.2);
    }
    
    .card-glass {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3B82F6, #1D4ED8);
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
    }
    
    .btn-secondary {
        background: linear-gradient(135deg, #F3F4F6, #E5E7EB);
        color: #374151;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .search-input {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .search-input:focus {
        border-color: #3B82F6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        transform: translateY(-1px);
    }
    
    .table-row {
        transition: all 0.2s ease;
    }
    
    .table-row:hover {
        background: linear-gradient(135deg, #F8FAFC, #F1F5F9);
        transform: translateX(4px);
    }
    
    .action-btn {
        transition: all 0.2s ease;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .action-btn:hover {
        transform: translateY(-1px);
    }
    
    .stats-card {
        background: linear-gradient(135deg, #FFFFFF, #F8FAFC);
        border: 1px solid #E2E8F0;
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .icon-gradient {
        background: linear-gradient(135deg, #3B82F6, #8B5CF6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-up {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .table-container {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
    }

    .users-green-table {
        --users-green: #7da533;
        --users-green-dark: #638524;
        --users-green-soft: #f1f7e8;
        --users-green-hover: #e6f0d8;
        --users-turquoise: #117e8c;
        background: #ffffff;
        border: 1px solid #d8e3c7;
        border-radius: 16px;
        box-shadow: 0 9px 24px rgba(91, 121, 38, 0.12);
    }

    .users-green-table > div {
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .users-green-table .users-table {
        border-collapse: collapse;
        width: 100%;
    }

    .users-green-table thead,
    .users-green-table thead tr,
    .users-green-table thead th {
        background: var(--users-green) !important;
    }

    .users-green-table thead th {
        color: #ffffff !important;
        border-right: 1px solid rgba(255, 255, 255, 0.3) !important;
        border-bottom: 0 !important;
        letter-spacing: 0.055em;
    }

    .users-green-table tbody {
        background: #ffffff !important;
    }

    .users-green-table tbody tr:nth-child(odd) {
        background: #ffffff !important;
    }

    .users-green-table tbody tr:nth-child(even) {
        background: var(--users-green-soft) !important;
    }

    .users-green-table tbody tr:hover {
        background: var(--users-green-hover) !important;
    }

    .users-green-table tbody td {
        border-right: 1px solid #d8e3c7 !important;
        border-bottom: 1px solid #dfe8d1 !important;
    }

    .users-green-table thead th:last-child,
    .users-green-table tbody td:last-child {
        border-right: 0 !important;
    }

    .users-green-table tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    .users-green-table .users-avatar {
        background: linear-gradient(135deg, var(--users-green), var(--users-turquoise));
    }

    .users-green-table .users-role-badge {
        color: var(--users-green-dark);
        background: #edf4e4;
        border-color: #cedfb4;
    }

    .users-green-table .user-action {
        transition: transform 0.2s ease, background-color 0.2s ease;
    }

    .users-green-table .user-action:hover {
        transform: translateY(-1px);
    }

    .users-green-table .user-action-view {
        color: var(--users-green-dark);
        background: #edf4e4;
    }

    .users-green-table .user-action-view:hover {
        background: #dfeccb;
    }

    .users-green-table .user-action-edit {
        color: #0d6975;
        background: #e6f4f5;
    }

    .users-green-table .user-action-edit:hover {
        background: #d2eaec;
    }
</style>
@endpush
